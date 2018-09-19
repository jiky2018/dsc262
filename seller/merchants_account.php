<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_account_tab_menu()
{
	global $_LANG;
	$account_curr = 0;
	$deposit_curr = 0;
	$topup_curr = 0;
	$detail_curr = 0;
	$account_log_curr = 0;
	$frozen_money_curr = 0;
	$account_log_list_curr = 0;
	$tab_menu = array();

	if ($_REQUEST['act_type'] == 'account') {
		$account_curr = 1;
	}
	else if ($_REQUEST['act_type'] == 'deposit') {
		$deposit_curr = 1;
	}
	else {
		if ($_REQUEST['act_type'] == 'topup' || $_REQUEST['act_type'] == 'topup_pay') {
			$topup_curr = 1;
		}
		else if ($_REQUEST['act_type'] == 'detail') {
			$detail_curr = 1;
		}
		else if ($_REQUEST['act_type'] == 'account_log') {
			$account_log_curr = 1;
		}
		else if ($_REQUEST['act_type'] == 'frozen_money') {
			$frozen_money_curr = 1;
		}
		else if ($_REQUEST['act_type'] == 'account_log_list') {
			$account_log_list_curr = 1;
		}
	}

	$tab_menu[] = array('curr' => $account_curr, 'text' => $_LANG['01_seller_account'], 'href' => 'merchants_account.php?act=account_manage&act_type=account');
	$tab_menu[] = array('curr' => $deposit_curr, 'text' => $_LANG['02_seller_deposit'], 'href' => 'merchants_account.php?act=account_manage&act_type=deposit');
	$tab_menu[] = array('curr' => $topup_curr, 'text' => $_LANG['03_top_up'], 'href' => 'merchants_account.php?act=account_manage&act_type=topup');
	$tab_menu[] = array('curr' => $detail_curr, 'text' => $_LANG['04_seller_detail'], 'href' => 'merchants_account.php?act=account_manage&act_type=detail');
	$tab_menu[] = array('curr' => $account_log_curr, 'text' => $_LANG['05_seller_account_log'], 'href' => 'merchants_account.php?act=account_manage&act_type=account_log');
	$tab_menu[] = array('curr' => $frozen_money_curr, 'text' => $_LANG['title_frozen_money'], 'href' => 'merchants_account.php?act=account_manage&act_type=frozen_money');
	$tab_menu[] = array('curr' => $account_log_list_curr, 'text' => $_LANG['fund_details'], 'href' => 'merchants_account.php?act=account_manage&act_type=account_log_list');
	return $tab_menu;
}

echo ' ';
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/lib_order.php';
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

admin_priv('seller_account');
if (!isset($_REQUEST['submit_act']) && $_REQUEST['act'] != 'checkorder') {
	if (!isset($_REQUEST['act_type'])) {
		$Loaction = 'merchants_account.php?act=account_manage&act_type=account';
		ecs_header('Location: ' . $Loaction . "\n");
	}

	$tab_menu = get_account_tab_menu();
	$smarty->assign('tab_menu', $tab_menu);
}

if ($_REQUEST['act'] == 'account_manage') {
	$smarty->assign('primary_cat', $_LANG['17_merchants']);
	$smarty->assign('full_page', 1);
	$users_real = get_users_real($adminru['ru_id'], 1);

	if ($users_real) {
		$users_real['front_of_id_card'] = get_image_path($users_real['real_id'], $users_real['front_of_id_card']);
		$users_real['reverse_of_id_card'] = get_image_path($users_real['real_id'], $users_real['reverse_of_id_card']);
	}

	$smarty->assign('real', $users_real);
	assign_query_info();

	if ($_REQUEST['act_type'] == 'account') {
		$smarty->assign('ur_here', $_LANG['01_seller_account']);

		if (0 < intval($_CFG['sms_signin'])) {
			$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
			$smarty->assign('sms_security_code', $sms_security_code);
			$smarty->assign('enabled_sms_signin', 1);
		}

		if (!$users_real) {
			$smarty->assign('form_act', 'insert');
		}
		else {
			$smarty->assign('form_act', 'update');
		}

		$smarty->display('merchants_account.dwt');
	}
	else if ($_REQUEST['act_type'] == 'deposit') {
		$smarty->assign('ur_here', $_LANG['02_seller_deposit']);

		if (!$users_real) {
			$link[0] = array('href' => 'merchants_account.php?act=account_manage&act_type=account', 'text' => $_LANG['01_seller_account']);
			sys_msg($_LANG['account_noll'], 2, $link);
		}
		else if ($users_real['review_status'] != 1) {
			$link[0] = array('href' => 'merchants_account.php?act=account_manage&act_type=account', 'text' => $_LANG['01_seller_account']);
			sys_msg($_LANG['label_status'], 2, $link);
		}

		$smarty->assign('form_act', 'deposit_insert');
		$seller_shopinfo = get_seller_shopinfo($adminru['ru_id'], array('seller_money'));
		$smarty->assign('seller_shopinfo', $seller_shopinfo);
		$smarty->display('merchants_deposit.dwt');
	}
	else if ($_REQUEST['act_type'] == 'topup') {
		$smarty->assign('ur_here', $_LANG['03_top_up']);
		$smarty->assign('form_act', 'topup_insert');
		$payment_list = available_payment_list(0);

		foreach ($payment_list as $key => $payment) {
			if (substr($payment['pay_code'], 0, 4) == 'pay_') {
				unset($payment_list[$key]);
				continue;
			}
		}

		$smarty->assign('pay', $payment_list);
		$seller_shopinfo = get_seller_shopinfo($adminru['ru_id'], array('seller_money'));
		$smarty->assign('seller_shopinfo', $seller_shopinfo);
		$user_money = $db->getOne('SELECT user_money FROM ' . $ecs->table('users') . ' WHERE user_id=\'' . $adminru['ru_id'] . '\'');
		$smarty->assign('user_money', $user_money);
		$smarty->display('merchants_topup.dwt');
	}
	else if ($_REQUEST['act_type'] == 'detail') {
		$smarty->assign('ur_here', $_LANG['04_seller_detail']);
		$list = get_account_log_list($adminru['ru_id'], array(2, 3, 4, 5));
		$log_list = $list['log_list'];
		$page_count_arr = seller_page($list, $_REQUEST['page']);
		$smarty->assign('page_count_arr', $page_count_arr);
		$smarty->assign('log_list', $log_list);
		$smarty->assign('filter', $list['filter']);
		$smarty->assign('record_count', $list['record_count']);
		$smarty->assign('page_count', $list['page_count']);
		$smarty->display('merchants_detail.dwt');
	}
	else if ($_REQUEST['act_type'] == 'account_log') {
		$smarty->assign('ur_here', $_LANG['05_seller_account_log']);
		$list = get_account_log_list($adminru['ru_id'], array(1, 4, 5));
		$log_list = $list['log_list'];
		$page_count_arr = seller_page($list, $_REQUEST['page']);
		$smarty->assign('page_count_arr', $page_count_arr);
		$smarty->assign('log_list', $log_list);
		$smarty->assign('filter', $list['filter']);
		$smarty->assign('record_count', $list['record_count']);
		$smarty->assign('page_count', $list['page_count']);
		$smarty->display('merchants_account_log.dwt');
	}
	else if ($_REQUEST['act_type'] == 'frozen_money') {
		$smarty->assign('ur_here', $_LANG['title_frozen_money']);
		$seller_shopinfo = get_seller_shopinfo($adminru['ru_id'], array('frozen_money'));
		$smarty->assign('seller_shopinfo', $seller_shopinfo);
		$smarty->display('merchants_frozen_money.dwt');
	}
	else if ($_REQUEST['act_type'] == 'topup_pay') {
		$smarty->assign('ur_here', $_LANG['03_top_up']);
		include_once ROOT_PATH . 'includes/lib_payment.php';
		$smarty->assign('primary_cat', $_LANG['17_merchants']);
		$log_id = isset($_REQUEST['log_id']) ? intval($_REQUEST['log_id']) : 0;
		$sql = 'SELECT * FROM ' . $ecs->table('seller_account_log') . (' WHERE log_id = \'' . $log_id . '\' LIMIT 1');
		$account_log = $db->getRow($sql);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('pay_log') . (' WHERE order_id = \'' . $log_id . '\' AND order_type = \'') . PAY_TOPUP . '\' LIMIT 1';
		$pay_log = $db->getRow($sql);
		$payment_info = array();
		$payment_info = payment_info($account_log['pay_id']);
		$payment = unserialize_config($payment_info['pay_config']);
		$payment_info['pay_fee'] = pay_fee($account_log['pay_id'], $account_log['amount'], 0);
		$apply_info['order_amount'] = $account_log['amount'] + $payment_info['pay_fee'];
		$apply_info['order_sn'] = $account_log['apply_sn'];
		$apply_info['user_id'] = $account_log['ru_id'];
		$apply_info['surplus_amount'] = $account_log['amount'];
		$apply_info['log_id'] = $pay_log['log_id'];

		if ($payment_info['pay_code'] == 'balance') {
			$user_money = $db->getOne('SELECT user_money FROM ' . $ecs->table('users') . ' WHERE user_id=\'' . $account_log['ru_id'] . '\'');

			if ($account_log['amount'] <= $user_money) {
				$sql = ' UPDATE ' . $ecs->table('seller_shopinfo') . ' SET seller_money = seller_money + ' . $account_log['amount'] . ' WHERE ru_id = \'' . $account_log['ru_id'] . '\'';
				$db->query($sql);
				$sql = ' UPDATE ' . $ecs->table('seller_account_log') . ' SET is_paid = 1, pay_time = \'' . gmtime() . ('\' WHERE log_id = \'' . $log_id . '\'');
				$db->query($sql);
				require ROOT_PATH . 'includes/lib_clips.php';
				$sql = ' UPDATE ' . $ecs->table('users') . ' SET user_money = user_money - ' . $account_log['amount'] . ' WHERE user_id = \'' . $account_log['ru_id'] . '\'';
				$db->query($sql);
				$change_desc = $_LANG['label_seller_topup'] . $account_log['apply_sn'];
				$user_account_log = array('user_id' => $account_log['ru_id'], 'user_money' => '-' . $account_log['amount'], 'change_desc' => $change_desc, 'process_type' => 0, 'payment' => $payment_info['pay_name'], 'change_time' => gmtime(), 'change_type' => 1);
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('account_log'), $user_account_log, 'INSERT');
				$sql = 'UPDATE ' . $ecs->table('pay_log') . ('SET is_paid = 1 WHERE order_id = \'' . $log_id . '\' AND order_type = \'') . PAY_TOPUP . '\'';
				$db->query($sql);
				$change_desc = '【' . $_SESSION['seller_name'] . '】' . $_LANG['seller_change_desc'];
				$log = array('user_id' => $account_log['ru_id'], 'user_money' => $account_log['amount'], 'change_time' => gmtime(), 'change_desc' => $change_desc, 'change_type' => 1);
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_account_log'), $log, 'INSERT');
				$link[0] = array('href' => 'merchants_account.php?act=account_manage&act_type=topup', 'text' => $_LANG['topup_account_ok']);
				sys_msg($_LANG['deposit_account_ok'], 0, $link);
			}
			else {
				sys_msg('您的余额已不足,请选择其他付款方式!');
			}
		}
		else {
			include_once ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php';
			$pay_obj = new $payment_info['pay_code']();
			$payment_info['pay_button'] = $pay_obj->get_code($apply_info, $payment);
		}

		$smarty->assign('grade_type', 2);
		$smarty->assign('apply_id', $log_id);
		$smarty->assign('payment', $payment_info);
		$smarty->assign('order', $apply_info);
		$smarty->assign('amount', $account_log['amount']);
		$smarty->display('seller_done.dwt');
	}
	else if ($_REQUEST['act_type'] = 'account_log_list') {
		$smarty->assign('ur_here', $_LANG['04_seller_detail']);
		$smarty->assign('full_page', 1);
		$list = get_seller_account_log();
		$page_count_arr = seller_page($list, $_REQUEST['page']);
		$smarty->assign('page_count_arr', $page_count_arr);
		$list['filter']['act_type'] = 'account_log_list';
		$smarty->assign('log_list', $list['log_list']);
		$smarty->assign('filter', $list['filter']);
		$smarty->assign('record_count', $list['record_count']);
		$smarty->assign('page_count', $list['page_count']);
		$smarty->assign('act_type', 'account_log_list');
		assign_query_info();
		$smarty->display('account_log_list.dwt');
	}
}
else if ($_REQUEST['act'] == 'checkorder') {
	$is_paid = 0;
	$apply_id = isset($_GET['apply_id']) ? intval($_GET['apply_id']) : 0;
	$sql = 'SELECT is_paid FROM ' . $ecs->table('seller_account_log') . (' WHERE log_id = \'' . $apply_id . '\' LIMIT 1');
	$is_paid = $db->getOne($sql);

	if ($is_paid == 1) {
		$json = array('code' => 1);
		exit(json_encode($json));
	}
	else {
		$json = array('code' => 0);
		exit(json_encode($json));
	}
}
else if ($_REQUEST['act'] == 'query') {
	if ($_REQUEST['act_type'] == 'detail') {
		$list = get_account_log_list($adminru['ru_id'], array(2, 3, 4, 5));
		$fetch = 'merchants_detail';
	}
	else if ($_REQUEST['act_type'] == 'account_log') {
		$list = get_account_log_list($adminru['ru_id'], array(1, 4));
		$fetch = 'merchants_account_log';
	}

	if ($_REQUEST['act_type'] == 'detail' || $_REQUEST['act_type'] == 'account_log') {
		$page_count_arr = seller_page($list, $_REQUEST['page']);
		$smarty->assign('page_count_arr', $page_count_arr);
		$smarty->assign('log_list', $list['log_list']);
		$smarty->assign('filter', $list['filter']);
		$smarty->assign('record_count', $list['record_count']);
		$smarty->assign('page_count', $list['page_count']);
		$sort_flag = sort_flag($list['filter']);
		$smarty->assign($sort_flag['tag'], $sort_flag['img']);
		make_json_result($smarty->fetch($fetch . '.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
	}
	else if ($_REQUEST['act_type'] == 'account_log_list') {
		$list = get_seller_account_log();
		$page_count_arr = seller_page($list, $_REQUEST['page']);
		$smarty->assign('page_count_arr', $page_count_arr);
		$list['filter']['act_type'] = 'account_log_list';
		$smarty->assign('log_list', $list['log_list']);
		$smarty->assign('filter', $list['filter']);
		$smarty->assign('record_count', $list['record_count']);
		$smarty->assign('page_count', $list['page_count']);
		$smarty->assign('act_type', 'account_log_list');
		assign_query_info();
		make_json_result($smarty->fetch('account_log_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
	}
}
else if ($_REQUEST['act'] == 'account_edit') {
	include_once ROOT_PATH . '/includes/cls_image.php';
	$image = new cls_image($_CFG['bgcolor']);
	$is_insert = isset($_REQUEST['form_act']) ? trim($_REQUEST['form_act']) : '';
	$other['real_name'] = isset($_REQUEST['real_name']) ? addslashes(trim($_REQUEST['real_name'])) : '';
	$other['self_num'] = isset($_REQUEST['self_num']) ? addslashes(trim($_REQUEST['self_num'])) : '';
	$other['bank_name'] = isset($_REQUEST['bank_name']) ? addslashes(trim($_REQUEST['bank_name'])) : '';
	$other['bank_card'] = isset($_REQUEST['bank_card']) ? addslashes(trim($_REQUEST['bank_card'])) : '';
	$other['bank_mobile'] = isset($_REQUEST['mobile_phone']) ? addslashes(trim($_REQUEST['mobile_phone'])) : '';
	$other['mobile_code'] = isset($_REQUEST['mobile_code']) ? intval($_REQUEST['mobile_code']) : '';
	$other['user_type'] = 1;
	$other['user_id'] = $adminru['ru_id'];
	$link[0] = array('href' => 'merchants_account.php?act=account_manage&act_type=account', 'text' => $_LANG['01_seller_account']);

	if ($_SESSION['sms_mobile_code'] != $other['mobile_code']) {
		sys_msg($_LANG['mobile_code_error'], 0, $link);
		exit();
	}

	if (isset($_FILES['front_of_id_card']['error']) && $_FILES['front_of_id_card']['error'] == 0 || !isset($_FILES['front_of_id_card']['error']) && isset($_FILES['front_of_id_card']['tmp_name']) && $_FILES['front_of_id_card']['tmp_name'] != 'none') {
		$front_name = $image->upload_image($_FILES['front_of_id_card'], 'idcard');
		get_oss_add_file(array($front_name));
	}

	if (!empty($_FILES['reverse_of_id_card']['size'])) {
		$reverse_name = $image->upload_image($_FILES['reverse_of_id_card'], 'idcard');
		get_oss_add_file(array($reverse_name));
	}

	$other['front_of_id_card'] = $front_name;
	$other['reverse_of_id_card'] = $reverse_name;

	if ($is_insert == 'insert') {
		$other['add_time'] = gmtime();
		$db->autoExecute($ecs->table('users_real'), $other, 'INSERT');
	}
	else {
		$other['review_status'] = 0;
		$db->autoExecute($ecs->table('users_real'), $other, 'UPDTAE', 'user_id = \'' . $adminru['ru_id'] . '\' AND user_type = 1');
	}

	sys_msg($is_insert ? $_LANG['add_account_ok'] : $_LANG['edit_account_ok'], 0, $link);
}
else if ($_REQUEST['act'] == 'deposit_insert') {
	$other['amount'] = isset($_REQUEST['deposit']) ? floatval(trim($_REQUEST['deposit'])) : 0;
	$other['frozen_money'] = $other['amount'];
	$other['seller_note'] = isset($_REQUEST['deposit_note']) ? addslashes(trim($_REQUEST['deposit_note'])) : 0;
	$other['real_id'] = isset($_REQUEST['real_id']) ? intval($_REQUEST['real_id']) : 0;
	$other['add_time'] = gmtime();
	$other['log_type'] = 1;
	$other['ru_id'] = $adminru['ru_id'];
	$other['deposit_mode'] = isset($_REQUEST['deposit_mode']) ? intval($_REQUEST['deposit_mode']) : 0;
	$db->autoExecute($ecs->table('seller_account_log'), $other, 'INSERT');
	log_seller_account_change($other['ru_id'], '-' . $other['amount'], $other['amount']);
	merchants_account_log($other['ru_id'], '-' . $other['amount'], $other['frozen_money'], '【' . $_SESSION['seller_name'] . '】' . $_LANG['02_seller_deposit']);
	$link[0] = array('href' => 'merchants_account.php?act=account_manage&act_type=account_log', 'text' => $_LANG['05_seller_account_log']);
	sys_msg($_LANG['deposit_account_ok'], 0, $link);
}
else if ($_REQUEST['act'] == 'unfreeze') {
	$other['frozen_money'] = isset($_REQUEST['frozen_money']) ? floatval(trim($_REQUEST['frozen_money'])) : 0;
	$other['seller_note'] = isset($_REQUEST['topup_note']) ? addslashes(trim($_REQUEST['topup_note'])) : '';
	$other['seller_note'] = '【' . $_SESSION['seller_name'] . '】' . $other['seller_note'];
	$other['add_time'] = gmtime();
	$other['log_type'] = 5;
	$other['ru_id'] = $adminru['ru_id'];
	$db->autoExecute($ecs->table('seller_account_log'), $other, 'INSERT');
	log_seller_account_change($other['ru_id'], 0, '-' . $other['frozen_money']);
	merchants_account_log($other['ru_id'], 0, '-' . $other['frozen_money'], '【' . $_SESSION['seller_name'] . '】' . $_LANG['apply_for_account']);
	$link[0] = array('href' => 'merchants_account.php?act=account_manage&act_type=account_log', 'text' => $_LANG['05_seller_account_log']);
	sys_msg($_LANG['deposit_account_ok'], 0, $link);
}
else if ($_REQUEST['act'] == 'topup_insert') {
	include_once ROOT_PATH . '/includes/cls_image.php';
	include_once ROOT_PATH . '/includes/lib_clips.php';
	$image = new cls_image($_CFG['bgcolor']);
	$nowTime = gmtime();
	$other['amount'] = isset($_REQUEST['topup_account']) ? floatval(trim($_REQUEST['topup_account'])) : 0;
	$other['seller_note'] = isset($_REQUEST['topup_note']) ? addslashes(trim($_REQUEST['topup_note'])) : 0;
	$other['pay_id'] = isset($_REQUEST['pay_id']) ? intval($_REQUEST['pay_id']) : 0;
	$other['add_time'] = $nowTime;
	$other['log_type'] = 3;
	$other['ru_id'] = $adminru['ru_id'];
	$certificate_img = isset($_FILES['certificate_img']) ? $_FILES['certificate_img'] : array();

	if ($certificate_img['name']) {
		$other['certificate_img'] = $image->upload_image('', 'seller_account', '', 1, $certificate_img['name'], $certificate_img['type'], $certificate_img['tmp_name'], $certificate_img['error'], $certificate_img['size']);
	}

	$other['apply_sn'] = get_order_sn();
	$other['pay_time'] = $nowTime;
	$db->autoExecute($ecs->table('seller_account_log'), $other, 'INSERT');
	$log_id = $db->insert_id();
	insert_pay_log($log_id, $other['amount'], PAY_TOPUP);
	$Loaction = 'merchants_account.php?act=account_manage&act_type=topup_pay&log_id=' . $log_id;
	ecs_header('Location: ' . $Loaction . "\n");
}
else if ($_REQUEST['act'] == 'del_pay') {
	$nowTime = gmtime();
	$log_id = isset($_REQUEST['log_id']) ? intval($_REQUEST['log_id']) : 0;
	$sql = 'DELETE FROM ' . $ecs->table('seller_account_log') . (' WHERE log_id = \'' . $log_id . '\'');
	$db->query($sql);
	$Loaction = 'merchants_account.php?act=account_manage&act_type=detail';
	ecs_header('Location: ' . $Loaction . "\n");
}

?>
