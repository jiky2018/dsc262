<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_user_surplus($user_id)
{
	$sql = 'SELECT user_money, frozen_money FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
	$res = $GLOBALS['db']->getRow($sql);

	if (!$res) {
		$res['user_money'] = 0;
		$res['frozen_money'] = 0;
	}

	return $res;
}

function update_user_account($id, $amount, $admin_note, $is_paid)
{
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_account') . ' SET ' . ('admin_user  = \'' . $_SESSION['admin_name'] . '\', ') . ('amount      = \'' . $amount . '\', ') . 'paid_time   = \'' . gmtime() . '\', ' . ('admin_note  = \'' . $admin_note . '\', ') . ('is_paid     = \'' . $is_paid . '\' WHERE id = \'' . $id . '\'');
	return $GLOBALS['db']->query($sql);
}

function account_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['user_id'] = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['process_type'] = isset($_REQUEST['process_type']) ? intval($_REQUEST['process_type']) : 0;
		$filter['payment'] = empty($_REQUEST['payment']) ? '' : trim($_REQUEST['payment']);
		$filter['is_paid'] = isset($_REQUEST['is_paid']) ? intval($_REQUEST['is_paid']) : -1;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['start_date'] = empty($_REQUEST['start_date']) ? '' : local_strtotime($_REQUEST['start_date']);
		$filter['end_date'] = empty($_REQUEST['end_date']) ? '' : local_strtotime($_REQUEST['end_date']) + 86400;
		$filter['add_start_date'] = empty($_REQUEST['add_start_date']) ? '' : (0 < strpos($_REQUEST['add_start_date'], '-') ? local_strtotime($_REQUEST['add_start_date']) : $_REQUEST['add_start_date']);
		$filter['add_end_date'] = empty($_REQUEST['add_end_date']) ? '' : (0 < strpos($_REQUEST['add_end_date'], '-') ? local_strtotime($_REQUEST['add_end_date']) : $_REQUEST['add_end_date']);
		$where = ' WHERE 1 ';

		if (0 < $filter['user_id']) {
			$where .= ' AND ua.user_id = \'' . $filter['user_id'] . '\' ';
		}

		if ($filter['process_type'] != -1) {
			$where .= ' AND ua.process_type = \'' . $filter['process_type'] . '\' ';
		}
		else {
			$where .= ' AND ua.process_type ' . db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN));
		}

		if ($filter['payment']) {
			$where .= ' AND ua.payment = \'' . $filter['payment'] . '\' ';
		}

		if ($filter['is_paid'] != -1) {
			$where .= ' AND ua.is_paid = \'' . $filter['is_paid'] . '\' ';
		}

		if ($filter['add_start_date']) {
			$where .= ' AND ua.add_time >= \'' . $filter['add_start_date'] . '\'';
		}

		if ($filter['add_end_date']) {
			$where .= ' AND ua.add_time <= \'' . $filter['add_end_date'] . '\'';
		}

		$leftJoin = '';

		if ($filter['keywords']) {
			$where .= ' AND ((u.user_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\')' . ' OR (u.email LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\') ' . ' OR (u.mobile_phone LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\')) ';
			$leftJoin = 'LEFT JOIN' . $GLOBALS['ecs']->table('users') . ' AS u ON ua.user_id = u.user_id';
		}

		if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
			$where .= 'AND paid_time >= ' . $filter['start_date'] . ' AND paid_time < \'' . $filter['end_date'] . '\'';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('user_account') . ' AS ua ' . $leftJoin . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT ua.*, u.user_name FROM ' . $GLOBALS['ecs']->table('user_account') . ' AS ua LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON ua.user_id = u.user_id' . $where . 'ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ', ' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$list = $GLOBALS['db']->getAll($sql);

	foreach ($list as $key => $value) {
		$list[$key]['surplus_amount'] = price_format(abs($value['amount']), false);
		$list[$key]['add_date'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
		$list[$key]['process_type_name'] = $GLOBALS['_LANG']['surplus_type_' . $value['process_type']];
	}

	$arr = array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('surplus_manage');
	$user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$process_type = isset($_REQUEST['process_type']) && !empty($_REQUEST['process_type']) ? intval($_REQUEST['process_type']) : 0;
	$is_paid = !empty($_REQUEST['is_paid']) ? intval($_REQUEST['is_paid']) : 0;
	$payment = array();
	$sql = 'SELECT pay_id, pay_name FROM ' . $ecs->table('payment') . ' WHERE enabled = 1 AND pay_code != \'cod\' ORDER BY pay_id';
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		$payment[$row['pay_name']] = $row['pay_name'];
	}

	$smarty->assign('process_type_' . $process_type, 'selected="selected"');

	if (isset($_REQUEST['is_paid'])) {
		$smarty->assign('is_paid_' . $is_paid, 'selected="selected"');
	}

	$smarty->assign('ur_here', $_LANG['09_user_account']);
	$smarty->assign('id', $user_id);
	$smarty->assign('payment_list', $payment);
	$smarty->assign('action_link', array('text' => $_LANG['surplus_add'], 'href' => 'user_account.php?act=add&process_type=' . $process_type));
	$list = account_list();
	$smarty->assign('list', $list['list']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->display('user_account_list.dwt');
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		admin_priv('surplus_manage');
		$process_type = isset($_REQUEST['process_type']) && !empty($_REQUEST['process_type']) ? intval($_REQUEST['process_type']) : 0;
		$ur_here = $_REQUEST['act'] == 'add' ? $_LANG['surplus_add'] : $_LANG['surplus_edit'];
		$form_act = $_REQUEST['act'] == 'add' ? 'insert' : 'update';
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$user_account = array();
		$payment = array();
		$sql = 'SELECT pay_id, pay_name FROM ' . $ecs->table('payment') . ' WHERE enabled = 1 AND pay_code != \'cod\' ORDER BY pay_id';
		$res = $db->query($sql);
		$idx = 0;

		while ($row = $db->fetchRow($res)) {
			$row['pay_name'] = strip_tags($row['pay_name']);
			$payment[$idx]['pay_id'] = $row['pay_id'];
			$payment[$idx]['pay_name'] = $row['pay_name'];
			$idx++;
		}

		if ($_REQUEST['act'] == 'edit') {
			$user_account = $db->getRow('SELECT * FROM ' . $ecs->table('user_account') . (' WHERE id = \'' . $id . '\''));
			$user_account['amount'] = str_replace('-', '', $user_account['amount']);
			$sql = 'SELECT user_name FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_account['user_id'] . '\'');
			$user_name = $db->getOne($sql);
		}
		else {
			$surplus_type = '';
			$user_name = '';
		}

		if ($user_account && $user_account['pay_id'] == 0 && $user_account['payment']) {
			$sql = 'SELECT pay_id FROM ' . $GLOBALS['ecs']->table('payment') . ' WHERE pay_name = \'' . $user_account['payment'] . '\' AND enabled = 1';
			$pay_id = $GLOBALS['db']->getOne($sql, true);
			$user_account['pay_id'] = $pay_id;
		}

		if ($_REQUEST['act'] == 'add') {
			$user_account['process_type'] = $process_type;
		}

		$smarty->assign('ur_here', $ur_here);
		$smarty->assign('form_act', $form_act);
		$smarty->assign('payment_list', $payment);
		$smarty->assign('action', $_REQUEST['act']);
		$smarty->assign('user_surplus', $user_account);
		$smarty->assign('user_name', $user_name);

		if ($_REQUEST['act'] == 'add') {
			$href = 'user_account.php?act=list';
		}
		else {
			$href = 'user_account.php?act=list&' . list_link_postfix();
		}

		$smarty->assign('action_link', array('href' => $href, 'text' => $_LANG['09_user_account']));
		assign_query_info();
		$smarty->display('user_account_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
			admin_priv('surplus_manage');
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$is_paid = !empty($_POST['is_paid']) ? intval($_POST['is_paid']) : 0;
			$amount = !empty($_POST['amount']) ? floatval($_POST['amount']) : 0;
			$process_type = !empty($_POST['process_type']) ? intval($_POST['process_type']) : 0;
			$user_name = !empty($_POST['user_id']) ? addslashes(trim($_POST['user_id'])) : '';
			$admin_note = !empty($_POST['admin_note']) ? trim($_POST['admin_note']) : '';
			$user_note = !empty($_POST['user_note']) ? trim($_POST['user_note']) : '';
			$pay_id = !empty($_POST['pay_id']) ? trim($_POST['pay_id']) : '';
			$user_id = $db->getOne('SELECT user_id FROM ' . $ecs->table('users') . (' WHERE user_name = \'' . $user_name . '\''));

			if ($user_id == 0) {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
				sys_msg($_LANG['username_not_exist'], 0, $link);
			}

			if ($process_type == 1) {
				$user_surplus = get_user_surplus($user_id);

				if ($user_surplus['user_money'] < $amount) {
					$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
					sys_msg($_LANG['surplus_amount_error'], 0, $link);
				}
			}

			if ($_REQUEST['act'] == 'insert') {
				if ($process_type == 1) {
					$amount = -1 * $amount;
				}

				$sql = 'SELECT pay_name FROM ' . $GLOBALS['ecs']->table('payment') . (' WHERE pay_id = \'' . $pay_id . '\'');
				$payment = $GLOBALS['db']->getOne($sql, true);
				$other = array('user_id' => $user_id, 'admin_user' => $_SESSION['admin_name'], 'amount' => $amount, 'add_time' => gmtime(), 'paid_time' => gmtime(), 'admin_note' => $admin_note, 'user_note' => $user_note, 'process_type' => $process_type, 'payment' => $payment, 'pay_id' => $pay_id, 'is_paid' => $is_paid);
				$db->autoExecute($ecs->table('user_account'), $other, 'INSERT');
				$id = $db->insert_id();
			}
			else {
				$sql = 'UPDATE ' . $ecs->table('user_account') . ' SET ' . ('admin_note   = \'' . $admin_note . '\', ') . ('user_note    = \'' . $user_note . '\', ') . ('payment      = \'' . $payment . '\' ') . ('WHERE id      = \'' . $id . '\'');
				$db->query($sql);
			}

			if ($is_paid == 1) {
				$change_desc = 0 < $amount ? $_LANG['surplus_type_0'] : $_LANG['surplus_type_1'];
				$change_type = 0 < $amount ? ACT_SAVING : ACT_DRAWING;
				log_account_change($user_id, $amount, 0, 0, 0, $change_desc, $change_type);
			}

			if ($process_type == 0 && $is_paid == 0) {
				include_once ROOT_PATH . 'includes/lib_order.php';
				$payment_info = array();
				$payment_info = $db->getRow('SELECT * FROM ' . $ecs->table('payment') . (' WHERE pay_name = \'' . $payment . '\' AND enabled = \'1\''));
				$pay_fee = pay_fee($payment_info['pay_id'], $amount, 0);
				$total_fee = $pay_fee + $amount;
				$sql = 'INSERT INTO ' . $ecs->table('pay_log') . ' (order_id, order_amount, order_type, is_paid)' . (' VALUES (\'' . $id . '\', \'' . $total_fee . '\', \'') . PAY_SURPLUS . '\', 0)';
				$db->query($sql);
			}

			if ($_REQUEST['act'] == 'update') {
				admin_log($user_name, 'edit', 'user_surplus');
			}
			else {
				admin_log($user_name, 'add', 'user_surplus');
			}

			if ($_REQUEST['act'] == 'insert') {
				$href = 'user_account.php?act=list';
			}
			else {
				$href = 'user_account.php?act=list&' . list_link_postfix();
			}

			$link[0]['text'] = $_LANG['back_list'];
			$link[0]['href'] = $href;
			$link[1]['text'] = $_LANG['continue_add'];
			$link[1]['href'] = 'user_account.php?act=add';
			sys_msg($_LANG['attradd_succed'], 0, $link);
		}
		else if ($_REQUEST['act'] == 'check') {
			admin_priv('surplus_manage');
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

			if ($id == 0) {
				ecs_header("Location: user_account.php?act=list\n");
				exit();
			}

			$account = array();
			$account = $db->getRow('SELECT * FROM ' . $ecs->table('user_account') . (' WHERE id = \'' . $id . '\''));
			$account['add_time'] = local_date($_CFG['time_format'], $account['add_time']);
			if ($account['process_type'] == 1 || $account['payment'] == '银行汇款/转帐') {
				$sql = 'SELECT real_name, bank_card, self_num, bank_name  FROM ' . $ecs->table('users_real') . (' WHERE user_id = \'' . $account['user_id'] . '\'');
				$account['real'] = $db->getRow($sql);
				$account['processType'] = 1;
			}

			$account['fields'] = $db->getRow('SELECT * FROM ' . $ecs->table('user_account_fields') . (' WHERE user_id = \'' . $account['user_id'] . '\' and account_id=\'' . $id . '\''));

			if ($account['process_type'] == 0) {
				$process_type = $_LANG['surplus_type_0'];
			}
			else if ($account['process_type'] == 1) {
				$process_type = $_LANG['surplus_type_1'];
			}
			else if ($account['process_type'] == 2) {
				$process_type = $_LANG['surplus_type_2'];
			}
			else {
				$process_type = $_LANG['surplus_type_3'];
			}

			$sql = 'SELECT user_name, mobile_phone FROM ' . $ecs->table('users') . ' WHERE user_id = \'' . $account['user_id'] . '\' LIMIT 1';
			$user_info = $db->getRow($sql);
			$sql = 'SELECT * FROM ' . $ecs->table('users_real') . ' WHERE user_id = \'' . $account['user_id'] . '\' LIMIT 1';
			$users_real = $db->getRow($sql);
			$smarty->assign('ur_here', $_LANG['check']);
			$account['user_note'] = htmlspecialchars($account['user_note']);
			$smarty->assign('surplus', $account);
			$smarty->assign('users_real', $users_real);
			$smarty->assign('process_type', $process_type);
			$smarty->assign('user_info', $user_info);
			$smarty->assign('id', $id);
			$smarty->assign('action_link', array('text' => $_LANG['09_user_account'], 'href' => 'user_account.php?act=list&' . list_link_postfix()));
			assign_query_info();
			$smarty->display('user_account_check.dwt');
		}
		else if ($_REQUEST['act'] == 'action') {
			admin_priv('surplus_manage');
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$is_paid = isset($_POST['is_paid']) ? intval($_POST['is_paid']) : 0;
			$admin_note = isset($_POST['admin_note']) ? trim($_POST['admin_note']) : '';
			if ($id == 0 || empty($admin_note)) {
				ecs_header("Location: user_account.php?act=list\n");
				exit();
			}

			$account = array();
			$account = $db->getRow('SELECT * FROM ' . $ecs->table('user_account') . (' WHERE id = \'' . $id . '\''));
			$amount = $account['amount'];
			$user_surplus = get_user_surplus($account['user_id']);

			if ($account['is_paid'] == 0) {
				$user_info = array();
				if ($is_paid == 1 && 0 < intval($account['user_id'])) {
					$sql = 'SELECT mobile_phone, email,user_name FROM' . $ecs->table('users') . ' WHERE user_id = \'' . $account['user_id'] . '\' LIMIT 1';
					$user_info = $db->getRow($sql);
				}

				$smsParams = array('user_name' => $user_info['user_name'], 'username' => $user_info['user_name'], 'user_money' => $user_surplus['user_money'] + $amount, 'usermoney' => $user_surplus['user_money'] + $amount, 'op_time' => local_date('Y-m-d H:i:s', gmtime()), 'optime' => local_date('Y-m-d H:i:s', gmtime()), 'add_time' => local_date('Y-m-d H:i:s', $account['add_time']), 'addtime' => local_date('Y-m-d H:i:s', $account['add_time']), 'examine' => '通过', 'fmt_amount' => $amount, 'fmtamount' => $amount, 'mobile_phone' => $user_info['mobile_phone'] ? $user_info['mobile_phone'] : '', 'mobilephone' => $user_info['mobile_phone'] ? $user_info['mobile_phone'] : '');
				if ($is_paid == 1 && $account['process_type'] == 1) {
					$fmt_amount = str_replace('-', '', $amount);

					if ($user_surplus['frozen_money'] < $fmt_amount) {
						$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
						sys_msg($_LANG['surplus_frozen_error'], 0, $link);
					}

					update_user_account($id, $amount, $admin_note, $is_paid);
					log_account_change($account['user_id'], 0, $amount + $account['deposit_fee'], 0, 0, '【' . $_LANG['surplus_type_1'] . '-' . $_LANG['offline_transfer'] . '】' . $admin_note, ACT_DRAWING);
					$order_note = !empty($account['admin_note']) ? explode('：', $account['admin_note']) : array();
					if ($order_note && isset($order_note[1]) && $order_note[1]) {
						require_once ROOT_PATH . 'includes/lib_order.php';
						$order = order_info(0, $order_note[1]);

						if ($order['ru_id']) {
							$adminru = get_admin_ru_id();
							$change_desc = '操作员：【' . $adminru['user_name'] . '】，订单退款【' . $order['order_sn'] . '】' . $refund_note;
							$log = array('user_id' => $order['ru_id'], 'user_money' => $amount, 'change_time' => gmtime(), 'change_desc' => $change_desc, 'change_type' => 2);
							$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_account_log'), $log, 'INSERT');
							$sql = 'UPDATE ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' SET seller_money = seller_money + \'' . $log['user_money'] . '\' WHERE ru_id = \'' . $order['ru_id'] . '\'';
							$GLOBALS['db']->query($sql);
						}
					}

					$smsParams['user_money'] = $user_surplus['user_money'] + $amount;
					$smsParams['usermoney'] = $user_surplus['user_money'] + $amount;
					$smsParams['process_type'] = $_LANG['surplus_type_1'];
					$smsParams['processtype'] = $_LANG['surplus_type_1'];
					if ($GLOBALS['_CFG']['user_account_code'] == '1' && $user_info['mobile_phone'] != '') {
						if ($GLOBALS['_CFG']['sms_type'] == 0) {
							huyi_sms($smsParams, 'user_account_code');
						}
						else if (1 <= $GLOBALS['_CFG']['sms_type']) {
							$result = sms_ali($smsParams, 'user_account_code');

							if ($result) {
								$resp = $GLOBALS['ecs']->ali_yu($result);
							}
							else {
								sys_msg('阿里大鱼短信配置异常', 1);
							}
						}
					}

					if ($user_info['email'] != '') {
						$tpl = get_mail_template('user_account_code');
						$smarty->assign('smsParams', $smsParams);
						$content = $smarty->fetch('str:' . $tpl['template_content']);
						send_mail($_CFG['shop_name'], $user_info['email'], $tpl['template_subject'], $content, $tpl['is_html']);
					}
				}
				else {
					if ($is_paid == 1 && $account['process_type'] == 0) {
						update_user_account($id, $amount, $admin_note, $is_paid);
						log_account_change($account['user_id'], $amount, 0, 0, 0, '【' . $_LANG['user_name'] . $_LANG['surplus_type_0'] . '】' . $admin_note, ACT_SAVING);
						$smsParams['user_money'] = $user_surplus['user_money'] + $amount;
						$smsParams['usermoney'] = $user_surplus['user_money'] + $amount;
						$smsParams['process_type'] = $_LANG['surplus_type_0'];
						$smsParams['processtype'] = $_LANG['surplus_type_0'];
						if ($GLOBALS['_CFG']['user_account_code'] == '1' && $user_info['mobile_phone'] != '') {
							if ($GLOBALS['_CFG']['sms_type'] == 0) {
								$huyi = huyi_sms($smsParams, 'user_account_code');
							}
							else if (1 <= $GLOBALS['_CFG']['sms_type']) {
								$result = sms_ali($smsParams, 'user_account_code');

								if ($result) {
									$resp = $GLOBALS['ecs']->ali_yu($result);
								}
								else {
									sys_msg('阿里大鱼短信配置异常', 1);
								}
							}
						}

						if ($user_info['email'] != '') {
							$tpl = get_mail_template('user_account_code');
							$smarty->assign('smsParams', $smsParams);
							$content = $smarty->fetch('str:' . $tpl['template_content']);
							send_mail($_CFG['shop_name'], $user_info['email'], $tpl['template_subject'], $content, $tpl['is_html']);
						}
					}
					else {
						if ($is_paid == 0 || $is_paid == 2) {
							if ($is_paid == 2) {
								$set = 'is_paid = 2';
							}
							else {
								$set = 'is_paid = 0';
							}

							$sql = 'UPDATE ' . $ecs->table('user_account') . ' SET ' . ('admin_user    = \'' . $_SESSION['admin_name'] . '\', ') . ('admin_note    = \'' . $admin_note . '\', ') . $set . (' WHERE id = \'' . $id . '\'');
							$db->query($sql);
						}
					}
				}

				admin_log('(' . addslashes($_LANG['check']) . ')' . $admin_note, 'edit', 'user_surplus');
				$link[0]['text'] = $_LANG['back_list'];
				$link[0]['href'] = 'user_account.php?act=list&' . list_link_postfix();
				sys_msg($_LANG['attradd_succed'], 0, $link);
			}
		}
		else if ($_REQUEST['act'] == 'query') {
			$list = account_list();
			$smarty->assign('list', $list['list']);
			$smarty->assign('filter', $list['filter']);
			$smarty->assign('record_count', $list['record_count']);
			$smarty->assign('page_count', $list['page_count']);
			$sort_flag = sort_flag($list['filter']);
			$smarty->assign($sort_flag['tag'], $sort_flag['img']);
			make_json_result($smarty->fetch('user_account_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
		}
		else if ($_REQUEST['act'] == 'remove') {
			check_authz_json('surplus_manage');
			$id = @intval($_REQUEST['id']);
			$sql = 'SELECT u.user_name FROM ' . $ecs->table('users') . ' AS u, ' . $ecs->table('user_account') . ' AS ua ' . (' WHERE u.user_id = ua.user_id AND ua.id = \'' . $id . '\' ');
			$user_name = $db->getOne($sql);
			$sql = 'DELETE FROM ' . $ecs->table('user_account') . (' WHERE id = \'' . $id . '\'');

			if ($db->query($sql, 'SILENT')) {
				admin_log(addslashes($user_name), 'remove', 'user_surplus');
				$url = 'user_account.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
				ecs_header('Location: ' . $url . "\n");
				exit();
			}
			else {
				make_json_error($db->error());
			}
		}
		else if ($_REQUEST['act'] == 'batch') {
			admin_priv('surplus_manage');
			$checkboxes = !empty($_REQUEST['checkboxes']) ? $_REQUEST['checkboxes'] : '';
			$admin_note = '';

			if ($checkboxes) {
				foreach ($checkboxes as $id) {
					$is_paid = 1;
					$account = array();
					$account = $db->getRow('SELECT * FROM ' . $ecs->table('user_account') . (' WHERE id = \'' . $id . '\''));
					$user_surplus = get_user_surplus($account['user_id']);
					$amount = $account['amount'];

					if ($account['is_paid'] == 0) {
						$sql = 'SELECT mobile_phone, email,user_name FROM' . $ecs->table('users') . ' WHERE user_id = \'' . $account['user_id'] . '\'';
						$user_info = $db->getRow($sql);
						$smsParams = array('user_name' => $user_info['user_name'], 'username' => $user_info['user_name'], 'user_money' => $user_surplus['user_money'], 'usermoney' => $user_surplus['user_money'], 'op_time' => local_date('Y-m-d H:i:s', gmtime()), 'optime' => local_date('Y-m-d H:i:s', gmtime()), 'add_time' => local_date('Y-m-d H:i:s', $account['add_time']), 'addtime' => local_date('Y-m-d H:i:s', $account['add_time']), 'examine' => $_LANG['through'], 'fmt_amount' => $amount, 'fmtamount' => $amount, 'mobile_phone' => $user_info['mobile_phone'] ? $user_info['mobile_phone'] : '', 'mobilephone' => $user_info['mobile_phone'] ? $user_info['mobile_phone'] : '');

						if ($account['process_type'] == '1') {
							$fmt_amount = str_replace('-', '', $amount);

							if ($user_surplus['frozen_money'] < $fmt_amount) {
								continue;
							}

							update_user_account($id, $amount, $admin_note, $is_paid);
							log_account_change($account['user_id'], 0, $amount + $account['deposit_fee'], 0, 0, $_LANG['surplus_type_1'], ACT_DRAWING);
							$smsParams['user_money'] = $user_surplus['user_money'];
							$smsParams['usermoney'] = $user_surplus['user_money'];
							$smsParams['process_type'] = $_LANG['surplus_type_1'];
							$smsParams['processtype'] = $_LANG['surplus_type_1'];
							if ($GLOBALS['_CFG']['user_account_code'] == '1' && $user_info['mobile_phone'] != '') {
								if ($GLOBALS['_CFG']['sms_type'] == 0) {
									huyi_sms($smsParams, 'user_account_code');
								}
								else if (1 <= $GLOBALS['_CFG']['sms_type']) {
									$result = sms_ali($smsParams, 'user_account_code');

									if ($result) {
										$resp = $GLOBALS['ecs']->ali_yu($result);
									}
								}
							}

							if ($user_info['email'] != '') {
								$tpl = get_mail_template('user_account_code');
								$smarty->assign('smsParams', $smsParams);
								$content = $smarty->fetch('str:' . $tpl['template_content']);
								send_mail($_CFG['shop_name'], $user_info['email'], $tpl['template_subject'], $content, $tpl['is_html']);
							}
						}
						else if ($account['process_type'] == '0') {
							update_user_account($id, $amount, $admin_note, $is_paid);
							log_account_change($account['user_id'], $amount, 0, 0, 0, $_LANG['surplus_type_0'], ACT_SAVING);
							$smsParams['user_money'] = $user_surplus['user_money'];
							$smsParams['usermoney'] = $user_surplus['user_money'];
							$smsParams['process_type'] = $_LANG['surplus_type_0'];
							$smsParams['processtype'] = $_LANG['surplus_type_0'];
							if ($GLOBALS['_CFG']['user_account_code'] == '1' && $user_info['mobile_phone'] != '') {
								if ($GLOBALS['_CFG']['sms_type'] == 0) {
									$huyi = huyi_sms($smsParams, 'user_account_code');
								}
								else if (1 <= $GLOBALS['_CFG']['sms_type']) {
									$result = sms_ali($smsParams, 'user_account_code');

									if ($result) {
										$resp = $GLOBALS['ecs']->ali_yu($result);
									}
								}
							}

							if ($user_info['email'] != '') {
								$tpl = get_mail_template('user_account_code');
								$smarty->assign('smsParams', $smsParams);
								$content = $smarty->fetch('str:' . $tpl['template_content']);
								send_mail($_CFG['shop_name'], $user_info['email'], $tpl['template_subject'], $content, $tpl['is_html']);
							}
						}

						admin_log('(' . addslashes($_LANG['check']) . ')' . $admin_note, 'edit', 'user_surplus');
					}
				}

				$link[0]['text'] = $_LANG['back_list'];
				$link[0]['href'] = 'user_account.php?act=list&' . list_link_postfix();
				sys_msg($_LANG['attradd_succed'], 0, $link);
			}
			else {
				$link[0]['text'] = $_LANG['back_list'];
				$link[0]['href'] = 'user_account.php?act=list&' . list_link_postfix();
				sys_msg($_LANG['please_take_handle'], 0, $link);
			}
		}
	}
}

?>
