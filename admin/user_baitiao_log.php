<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function baitiao_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'baitiao_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$ex_where = ' WHERE 1 ';

		if ($filter['keywords']) {
			$ex_where .= ' AND u.user_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\'';
		}

		$filter['record_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('baitiao') . 'AS b LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON b.user_id=u.user_id ' . $ex_where);
		$filter = page_and_size($filter);
		$sql = 'SELECT b.*,u.user_name ' . ' FROM ' . $GLOBALS['ecs']->table('baitiao') . 'as b left join ' . $GLOBALS['ecs']->table('users') . ' as u on b.user_id=u.user_id ' . $ex_where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$baitiao_list = $GLOBALS['db']->getAll($sql);
	$arr = array('baitiao_list' => $baitiao_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function getbaitiao_list($user_id = 0)
{
	$result = get_filter();

	if ($result === false) {
		if (0 < $user_id) {
			$filter['user_id'] = $user_id;
		}

		$ex_where = ' WHERE 1 ';

		if (0 < $filter['user_id']) {
			$ex_where .= ' AND b.user_id = \'' . mysql_like_quote($filter['user_id']) . '\'';
		}

		$filter['record_count'] = $GLOBALS['db']->getOne('SELECT COUNT(b.log_id) AS total FROM ' . $GLOBALS['ecs']->table('baitiao_log') . ' AS b LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . '  AS o ON b.order_id=o.order_id  ' . $ex_where . ' ORDER BY b.log_id DESC');
		$filter = page_and_size($filter);
		$sql = 'SELECT b.*,o.order_sn, o.money_paid AS order_amount FROM ' . $GLOBALS['ecs']->table('baitiao_log') . ' AS b LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . '  AS o ON b.order_id=o.order_id  ' . $ex_where . ' ORDER BY b.log_id DESC' . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$bt_log = $GLOBALS['db']->getAll($sql);

	if ($bt_log) {
		foreach ($bt_log as $key => $val) {
			$bt_log[$key]['use_date'] = local_date($GLOBALS['_CFG']['time_format'], $bt_log[$key]['use_date']);

			if ($val['is_stages'] == 1) {
				$bt_log[$key]['repay_date'] = unserialize($bt_log[$key]['repay_date']);
			}
			else {
				$bt_log[$key]['repay_date'] = local_date($GLOBALS['_CFG']['time_format'], $bt_log[$key]['repay_date']);
			}

			if ($bt_log[$key]['repayed_date']) {
				$bt_log[$key]['repayed_date'] = local_date($GLOBALS['_CFG']['time_format'], $bt_log[$key]['repayed_date']);
			}
		}
	}

	$arr = array('bt_log' => $bt_log, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'list') {
	admin_priv('baitiao_manage');
	$baitiao_list = baitiao_list();
	$smarty->assign('ur_here', $_LANG['bt_list']);
	$smarty->assign('baitiao_list', $baitiao_list['baitiao_list']);
	$smarty->assign('filter', $baitiao_list['filter']);
	$smarty->assign('record_count', $baitiao_list['record_count']);
	$smarty->assign('page_count', $baitiao_list['page_count']);
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->display('baitiao_list.dwt');
}
else if ($_REQUEST['act'] == 'log_list') {
	admin_priv('baitiao_manage');
	$baitiao_id = (isset($_REQUEST['bt_id']) ? intval($_REQUEST['bt_id']) : 0);
	$user_id = (isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0);
	$bt_other = array('user_id' => $user_id);
	$bt_info = get_baitiao_info($bt_other);
	$bt_info['format_amount'] = price_format($bt_info['amount'], false);
	$sql = 'SELECT SUM(b.stages_one_price * (b.stages_total - b.yes_num)) AS total_amount, COUNT(log_id) AS numbers , SUM(b.stages_one_price * b.yes_num) AS already_amount FROM ' . $ecs->table('baitiao_log') . ' AS b ' . ' WHERE b.user_id = \'' . $user_id . '\' AND b.is_repay = 0 AND b.is_refund = 0 LIMIT 1';
	$repay_bt = $db->getRow($sql);
	$repay_bt['format_total_amount'] = price_format($repay_bt['total_amount'], false);
	$repay_bt['format_already_amount'] = price_format($repay_bt['already_amount'], false);
	$bt_log = getbaitiao_list($user_id);
	$remain_amount = floatval($bt_info['amount']) - floatval($repay_bt['total_amount']);
	$format_remain_amount = price_format($remain_amount, false);
	$smarty->assign('remain_amount', $remain_amount);
	$smarty->assign('format_remain_amount', $format_remain_amount);
	$smarty->assign('bt_info', $bt_info);
	$smarty->assign('repay_bt', $repay_bt);
	$smarty->assign('bt_amount', $bt_amount);
	$smarty->assign('bt_logs', $bt_log['bt_log']);
	$smarty->assign('filter', $bt_log['filter']);
	$smarty->assign('record_count', $bt_log['record_count']);
	$smarty->assign('page_count', $bt_log['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('action_link', array('text' => $_LANG['bt_list'], 'href' => 'user_baitiao_log.php?act=list'));
	$smarty->assign('baitiao_page', array('total' => $total, 'page_total' => $page_total, 'page_one_num' => $page_one_num));
	$smarty->assign('ur_here', $_LANG['bt_details']);
	$smarty->display('baitiao_log_list.dwt');
}
else if ($_REQUEST['act'] == 'log_list_query') {
	admin_priv('baitiao_manage');
	$baitiao_id = (isset($_REQUEST['bt_id']) ? intval($_REQUEST['bt_id']) : 0);
	$user_id = (isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0);
	$bt = 'SELECT * FROM ' . $ecs->table('baitiao') . ' WHERE user_id=\'' . $user_id . '\'';
	$bt_info = $db->getRow($bt);
	$bt_sun = 'SELECT SUM(o.order_amount) FROM ' . $ecs->table('baitiao_log') . ' AS b LEFT JOIN ' . $ecs->table('order_info') . '  AS o ON b.order_id=o.order_id WHERE b.user_id=\'' . $user_id . '\' AND b.is_repay=0 AND  b.is_stages<>1  AND is_refund=0 ';
	$repay_sun_amount = $db->getOne($bt_sun);
	$bt_repay = 'SELECT SUM(o.order_amount) AS total_amount,COUNT(log_id) AS numbers FROM ' . $ecs->table('baitiao_log') . ' AS b LEFT JOIN ' . $ecs->table('order_info') . '  AS o ON b.order_id=o.order_id WHERE b.user_id=\'' . $user_id . '\' AND b.is_repay=0 AND is_refund=0';
	$repay_bt = $db->getRow($bt_repay);
	$bt_log = getbaitiao_list($user_id);
	$remain_amount = floatval($bt_info['amount']) - floatval($repay_bt['total_amount']);
	$smarty->assign('remain_amount', $remain_amount);
	$smarty->assign('bt_info', $bt_info);
	$smarty->assign('repay_sun_amount', $repay_sun_amount);
	$smarty->assign('repay_bt', $repay_bt);
	$smarty->assign('bt_amount', $bt_amount);
	$smarty->assign('bt_logs', $bt_log['bt_log']);
	$smarty->assign('filter', $bt_log['filter']);
	$smarty->assign('record_count', $bt_log['record_count']);
	$smarty->assign('page_count', $bt_log['page_count']);
	$smarty->assign('action_link', array('text' => $_LANG['bt_list'], 'href' => 'user_baitiao_log.php?act=list'));
	$smarty->assign('baitiao_page', array('total' => $total, 'page_total' => $page_total, 'page_one_num' => $page_one_num));
	$smarty->assign('ur_here', $_LANG['bt_details']);
	make_json_result($smarty->fetch('baitiao_log_list.dwt'), '', array('filter' => $bt_log['filter'], 'page_count' => $bt_log['page_count']));
}
else if ($_REQUEST['act'] == 'bt_add_tp') {
	admin_priv('baitiao_manage');
	$user_id = (empty($_REQUEST['user_id']) ? '' : trim($_REQUEST['user_id']));

	if (0 < $user_id) {
		$user_sql = 'SELECT user_name,user_id FROM ' . $ecs->table('users') . ' WHERE user_id=\'' . $user_id . '\'';
		$user_info = $db->getRow($user_sql);
		$bt_sql = 'SELECT b.*,u.user_name,u.user_id FROM ' . $ecs->table('baitiao') . ' AS b LEFT JOIN ' . $ecs->table('users') . ' AS u ON u.user_id=b.user_id WHERE b.user_id=\'' . $user_id . '\'';
		$bt_info = $db->getRow($bt_sql);
		$smarty->assign('action_link2', array('href' => 'users.php?act=list', 'text' => '会员列表'));
	}

	$smarty->assign('ur_here', $_LANG['bt_ur_here']);
	$smarty->assign('action_link', array('href' => 'baitiao_batch.php?act=add', 'text' => '白条批量设置'));
	$smarty->assign('user_id', $user_id);
	$smarty->assign('full_page', 1);
	$smarty->assign('form_action', 'bt_edit');
	$smarty->assign('user_info', $user_info);
	$smarty->assign('bt_info', $bt_info);
	$smarty->display('user_list_edit.dwt');
}
else if ($_REQUEST['act'] == 'bt_edit') {
	admin_priv('baitiao_manage');
	$user_id = (empty($_POST['user_id']) ? 0 : trim($_POST['user_id']));
	$amount = (empty($_POST['amount']) ? 0 : floatval(trim($_POST['amount'])));
	$repay_term = (empty($_POST['repay_term']) ? 0 : intval($_POST['repay_term']));
	$over_repay_trem = (empty($_POST['over_repay_trem']) ? 0 : intval($_POST['over_repay_trem']));

	if (0 < $user_id) {
		$bt_sql = 'SELECT baitiao_id FROM ' . $ecs->table('baitiao') . ' WHERE user_id=\'' . $user_id . '\'';
		$bt_info = $db->getOne($bt_sql);

		if ($bt_info) {
			$bt_up_sql = 'update ' . $ecs->table('baitiao') . ' set amount=\'' . $amount . '\',repay_term=\'' . $repay_term . '\',over_repay_trem=\'' . $over_repay_trem . '\',add_time=' . gmtime() . ' where baitiao_id=\'' . $bt_info . '.baitiao_id\'';
			$up_ok = $db->query($bt_up_sql);

			if ($up_ok) {
				$links[] = array('text' => $_LANG['go_back'], 'href' => 'user_baitiao_log.php?act=bt_add_tp&user_id=' . $user_id);
				sys_msg('会员白条更新成功！', 0, $links);
			}
		}
		else {
			$bt_insert_sql = 'INSERT INTO ' . $ecs->table('baitiao') . ' (user_id,amount,repay_term,over_repay_trem,add_time) VALUES (\'' . $user_id . '\',\'' . $amount . '\',\'' . $repay_term . '\',\'' . $over_repay_trem . '\',' . gmtime() . ')';
			$in_ok = $db->query($bt_insert_sql);

			if ($in_ok) {
				$links[] = array('text' => $_LANG['go_back'], 'href' => 'user_baitiao_log.php?act=bt_add_tp&user_id=' . $user_id);
				sys_msg('会员白条设置成功！', 0, $links);
			}
		}
	}
}
else if ($_REQUEST['act'] == 'query') {
	check_authz_json('baitiao_manage');
	$baitiao_list = baitiao_list();
	$smarty->assign('baitiao_list', $baitiao_list['baitiao_list']);
	$smarty->assign('filter', $baitiao_list['filter']);
	$smarty->assign('record_count', $baitiao_list['record_count']);
	$smarty->assign('page_count', $baitiao_list['page_count']);
	$sort_flag = sort_flag($baitiao_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('baitiao_list.dwt'), '', array('filter' => $baitiao_list['filter'], 'page_count' => $baitiao_list['page_count']));
}
else if ($_REQUEST['act'] == 'batch_remove') {
	admin_priv('baitiao_manage');
	if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
		sys_msg('没有选择任何数据', 1);
	}

	$sql = 'DELETE FROM ' . $ecs->table('baitiao') . ' WHERE baitiao_id ' . db_create_in(join(',', $_POST['checkboxes']));
	$del_ok = $db->query($sql);

	if ($del_ok) {
		$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'user_baitiao_log.php?act=list');
		sys_msg('删除白条成功', 0, $lnk);
	}
}
else if ($_REQUEST['act'] == 'batch_remove_log') {
	admin_priv('baitiao_manage');
	if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
		sys_msg('没有选择任何数据', 1);
	}

	$sql = 'SELECT log_id,is_repay,baitiao_id,user_id FROM ' . $ecs->table('baitiao_log') . ' WHERE log_id ' . db_create_in(join(',', $_POST['checkboxes']));
	$bt_log = $db->getAll($sql);

	if ($bt_log) {
		$no_del_num = 0;

		foreach ($bt_log as $key => $val) {
			if ($val['is_repay']) {
				$del_sql = 'DELETE FROM ' . $ecs->table('baitiao_log') . ' WHERE is_repay=1 and log_id =' . $val['log_id'];
				$del_ok = $db->query($del_sql);
			}
			else {
				$no_del_num++;
			}
		}

		if (0 < $no_del_num) {
			$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'user_baitiao_log.php?act=log_list&bt_id=' . $bt_log[0]['baitiao_id'] . '&user_id=' . $bt_log[0]['user_id']);
			sys_msg('有' . $no_del_num . '条待付款记录不可删除', 0, $lnk);
		}
		else {
			$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'user_baitiao_log.php?act=log_list&bt_id=' . $bt_log[0]['baitiao_id'] . '&user_id=' . $bt_log[0]['user_id']);
			sys_msg('删除消费记录成功', 0, $lnk);
		}
	}
	else {
		sys_msg('没有选择任何数据', 1);
	}
}
else if ($_REQUEST['act'] == 'remove') {
	admin_priv('baitiao_manage');
	$sql = 'SELECT baitiao_id FROM ' . $ecs->table('baitiao') . ' WHERE baitiao_id = \'' . $_GET['baitiao_id'] . '\'';
	$baitiao_id = $db->getOne($sql);

	if (0 < $baitiao_id) {
		$sql = 'delete from ' . $ecs->table('baitiao') . ' where baitiao_id = \'' . $baitiao_id . '\'';
		$del_ok = $db->query($sql);

		if ($del_ok) {
			admin_log(addslashes($baitiao_id), 'remove', 'baitiao');
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'user_baitiao_log.php?act=list');
			sys_msg('删除白条成功', 0, $link);
		}
	}
}
else if ($_REQUEST['act'] == 'remove_log') {
	admin_priv('baitiao_manage');
	$sql = 'SELECT log_id,is_repay,baitiao_id,user_id,is_refund FROM ' . $ecs->table('baitiao_log') . ' WHERE log_id = \'' . $_GET['log_id'] . '\'';
	$bt_log = $db->getRow($sql);

	if (0 < $bt_log['log_id']) {
		if ($bt_log['is_repay'] || $bt_log['is_refund']) {
			$sql = 'DELETE FROM ' . $ecs->table('baitiao_log') . ' WHERE is_repay=1 OR is_refund=1 AND log_id = \'' . $bt_log['log_id'] . '\'';
			$del_ok = $db->query($sql);

			if ($del_ok) {
				admin_log(addslashes($log_id), 'remove', 'baitiao_log');
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'user_baitiao_log.php?act=log_list&bt_id=' . $bt_log['baitiao_id'] . '&user_id=' . $bt_log['user_id']);
				sys_msg('删除白条记录成功', 0, $link);
			}
		}
		else {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'user_baitiao_log.php?act=log_list&bt_id=' . $bt_log['baitiao_id'] . '&user_id=' . $bt_log['user_id']);
			sys_msg('待付款的记录不可删除', 0, $link);
		}
	}
}

?>
