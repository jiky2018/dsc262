<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_type_list()
{
	$sql = 'SELECT c_id, COUNT(*) AS sent_count' . ' FROM ' . $GLOBALS['ecs']->table('pay_card') . ' GROUP BY c_id';
	$res = $GLOBALS['db']->query($sql);
	$sent_arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$sent_arr[$row['c_id']] = $row['sent_count'];
	}

	$sql = 'SELECT c_id, COUNT(*) AS used_count' . ' FROM ' . $GLOBALS['ecs']->table('pay_card') . ' WHERE used_time != 0' . ' GROUP BY c_id';
	$res = $GLOBALS['db']->query($sql);
	$used_arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$used_arr[$row['c_id']] = $row['used_count'];
	}

	$result = get_filter();

	if ($result === false) {
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'type_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('pay_card_type');
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('pay_card_type') . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$arr = array();
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['send_by'] = $GLOBALS['_LANG']['send_by'][$row['send_type']];
		$row['send_count'] = isset($sent_arr[$row['type_id']]) ? $sent_arr[$row['type_id']] : 0;
		$row['use_count'] = isset($used_arr[$row['type_id']]) ? $used_arr[$row['type_id']] : 0;
		$arr[] = $row;
	}

	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_bonus_goods($type_id)
{
	$sql = 'SELECT goods_id, goods_name FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE bonus_type_id = \'' . $type_id . '\'';
	$row = $GLOBALS['db']->getAll($sql);
	return $row;
}

function get_bonus_list()
{
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$where = ' WHERE 1 ';

	if ($_GET['tid']) {
		$where .= ' AND c_id = \'' . $_GET['tid'] . '\' ';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('pay_card') . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT ub.*, u.user_name, u.email,  bt.type_name,bt.type_money ' . ' FROM ' . $GLOBALS['ecs']->table('pay_card') . ' AS ub ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('pay_card_type') . ' AS bt ON bt.type_id=ub.c_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id=ub.user_id ' . ' ' . $where . ' ' . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ', ' . $filter['page_size'];
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$row[$key]['used_time'] = $val['used_time'] == 0 ? $GLOBALS['_LANG']['no_use'] : local_date($GLOBALS['_CFG']['date_format'], $val['used_time']);
		$row[$key]['emailed'] = $GLOBALS['_LANG']['mail_status'][$row[$key]['emailed']];
	}

	$arr = array('item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function bonus_type_info($bonus_type_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('bonus_type') . ' WHERE type_id = \'' . $bonus_type_id . '\'';
	return $GLOBALS['db']->getRow($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$exc = new exchange($ecs->table('pay_card_type'), $db, 'type_id', 'type_name');

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', $_LANG['pc_type_list']);
	$smarty->assign('action_link', array('text' => $_LANG['pc_type_add'], 'href' => 'pay_card.php?act=add'));
	$smarty->assign('full_page', 1);
	$list = get_type_list();
	$smarty->assign('type_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$smarty->display('pc_type_list.dwt');
}

if (($_REQUEST['act'] == 'add') || ($_REQUEST['act'] == 'edit')) {
	if ($_REQUEST['act'] == 'add') {
		$smarty->assign('form_act', 'insert');
		$smarty->assign('ur_here', $_LANG['pc_type_add']);
		$next_month = local_strtotime('+1 months');
		$bonus_arr['use_end_date'] = local_date('Y-m-d', $next_month);
		$smarty->assign('bonus_arr', $bonus_arr);
	}
	else {
		$type_id = (!empty($_GET['type_id']) ? intval($_GET['type_id']) : 0);
		$bonus_arr = $db->getRow('SELECT * FROM ' . $ecs->table('pay_card_type') . ' WHERE type_id = \'' . $type_id . '\'');
		$bonus_arr['use_end_date'] = local_date('Y-m-d', $bonus_arr['use_end_date']);
		$smarty->assign('ur_here', $_LANG['pc_type_edit']);
		$smarty->assign('form_act', 'update');
		$smarty->assign('bonus_arr', $bonus_arr);
	}

	$smarty->assign('lang', $_LANG);
	$smarty->assign('action_link', array('href' => 'value_card.php?act=list', 'text' => $_LANG['vc_type_list']));
	$smarty->assign('cfg_lang', $_CFG['lang']);
	assign_query_info();
	$smarty->display('pc_type_info.dwt');
}

if (($_REQUEST['act'] == 'insert') || ($_REQUEST['act'] == 'update')) {
	$type_name = (!empty($_POST['type_name']) ? trim($_POST['type_name']) : '');
	$type_id = (!empty($_POST['type_id']) ? intval($_POST['type_id']) : 0);
	$type_prefix = (!empty($_POST['type_prefix']) ? trim($_POST['type_prefix']) : 0);
	$use_enddate = local_strtotime($_POST['use_end_date']);
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('pay_card_type') . ' WHERE type_name=\'' . $type_name . '\' AND type_id <> \'' . $type_id . '\' ';

	if (0 < $db->getOne($sql)) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['type_name_exist'], 0, $link);
	}

	if (0 < $type_id) {
		$sql = 'UPDATE ' . $ecs->table('pay_card_type') . ' SET ' . 'type_name       = \'' . $type_name . '\', ' . 'type_money      = \'' . $_POST['type_money'] . '\', ' . 'type_prefix     = \'' . $type_prefix . '\', ' . 'use_end_date    = \'' . $use_enddate . '\' ' . 'WHERE type_id   = \'' . $type_id . '\'';
		$db->query($sql);
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'pay_card.php?act=list&' . list_link_postfix());
		sys_msg($_LANG['edit'] . ' ' . $_POST['type_name'] . ' ' . $_LANG['attradd_succed'], 0, $link);
	}
	else {
		$sql = 'INSERT INTO ' . $ecs->table('pay_card_type') . ' (type_name, type_money, type_prefix, use_end_date)VALUES (\'' . $type_name . '\', \'' . $_POST['type_money'] . '\', \'' . $type_prefix . '\', \'' . $use_enddate . '\')';
		$db->query($sql);
		$link[0]['text'] = $_LANG['continus_add'];
		$link[0]['href'] = 'pay_card.php?act=add';
		$link[1]['text'] = $_LANG['back_list'];
		$link[1]['href'] = 'pay_card.php?act=list';
		sys_msg($_LANG['add'] . '&nbsp;' . $_POST['type_name'] . '&nbsp;' . $_LANG['attradd_succed'], 0, $link);
	}

	clear_cache_files();
}

if ($_REQUEST['act'] == 'remove') {
	$id = intval($_GET['id']);
	$exc->drop($id);
	$db->query('DELETE FROM ' . $ecs->table('pay_card_type') . ' WHERE type_id = \'' . $id . '\'');
	$url = 'pay_card.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

if ($_REQUEST['act'] == 'query') {
	$list = get_type_list();
	$smarty->assign('type_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('pc_type_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

if ($_REQUEST['act'] == 'send') {
	$id = (!empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '');
	$smarty->assign('ur_here', $_LANG['send_bonus']);
	$smarty->assign('action_link', array('href' => 'shoppingcard.php?act=list', 'text' => $_LANG['bonus_type']));
	$smarty->assign('type_id', $id);
	$smarty->assign('type_list', get_pay_card_type($id));
	assign_query_info();
	$smarty->display('pay_card_send.dwt');
}

if ($_REQUEST['act'] == 'send_pay_card') {
	@set_time_limit(0);
	$tid = ($_POST['type_id'] ? intval($_POST['type_id']) : 0);
	$send_sum = (!empty($_POST['send_num']) ? intval($_POST['send_num']) : 1);
	$card_type = intval($_POST['card_type']);
	$password_type = intval($_POST['password_type']);
	$sql = ' SELECT type_prefix FROM ' . $GLOBALS['ecs']->table('pay_card_type') . ' WHERE type_id = \'' . $tid . '\' ';
	$type_prefix = $GLOBALS['db']->getOne($sql);
	$prefix_len = strlen($type_prefix);
	$length = $prefix_len + $card_type;
	$num = $db->getOne(' SELECT MAX(SUBSTRING(card_number,' . $prefix_len . '+1)) FROM ' . $ecs->table('pay_card') . ' WHERE c_id = \'' . $tid . '\' AND LENGTH(card_number) = \'' . $length . '\' ');
	$num = ($num ? intval($num) : 1);
	$i = 0;

	for ($j = 0; $i < $send_sum; $i++) {
		$card_number = $type_prefix . str_pad(mt_rand(0, 9999) + $num, $card_type, '0', STR_PAD_LEFT);
		$card_psd = strtoupper(mc_random($password_type));
		$db->query('INSERT INTO ' . $ecs->table('pay_card') . ' (card_number, card_psd, c_id) VALUES(\'' . $card_number . '\', \'' . $card_psd . '\', \'' . $tid . '\')');
		$j++;
	}

	admin_log($card_number, 'add', 'pay_card');
	clear_cache_files();
	$link[0]['text'] = $_LANG['back_list'];
	$link[0]['href'] = 'pay_card.php?act=list';
	sys_msg($_LANG['creat_pay_card'] . $j . $_LANG['pay_card_num'], 0, $link);
}

if ($_REQUEST['act'] == 'pc_list') {
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['bonus_list']);
	$id = ($_REQUEST['tid'] ? intval($_REQUEST['tid']) : 0);
	$smarty->assign('action_link', array('href' => 'pay_card.php?act=export_pc_list&id=' . $id, 'text' => $_LANG['export_pc_list']));
	$list = get_bonus_list();
	$bonus_type = bonus_type_info(intval($id));
	$smarty->assign('show_bonus_sn', 1);
	$smarty->assign('bonus_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('pay_card_view.dwt');
}

if ($_REQUEST['act'] == 'export_pc_list') {
	$id = ($_REQUEST['id'] ? intval($_REQUEST['id']) : 0);
	$where = ' WHERE 1 ';

	if (0 < $id) {
		$where .= ' AND c_id = \'' . $id . '\' ';
	}

	$arr = array();
	$sql = 'SELECT ub.*, u.user_name, u.email,  bt.type_name,bt.type_money,bt.use_end_date ' . ' FROM ' . $GLOBALS['ecs']->table('pay_card') . ' AS ub ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('pay_card_type') . ' AS bt ON bt.type_id=ub.c_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id=ub.user_id ' . ' ' . $where . ' ';
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$arr[$key]['id'] = $val['id'];
		$arr[$key]['card_number'] = $val['card_number'];
		$arr[$key]['card_psd'] = $val['card_psd'];
		$arr[$key]['type_name'] = $val['type_name'];
		$arr[$key]['type_money'] = $val['type_money'];
		$arr[$key]['use_end_date'] = $val['use_end_date'] == 0 ? $GLOBALS['_LANG']['no_use'] : local_date($GLOBALS['_CFG']['date_format'], $val['use_end_date']);
		$arr[$key]['user_name'] = !empty($val['user_name']) ? $val['user_name'] : $_LANG['no_use'];
		$arr[$key]['used_time'] = $val['used_time'] == 0 ? $GLOBALS['_LANG']['no_use'] : local_date($GLOBALS['_CFG']['date_format'], $val['used_time']);
	}

	$prev = array($_LANG['record_id'], $_LANG['bonus_sn'], $_LANG['bonus_psd'], $_LANG['bonus_type'], $_LANG['type_money'], $_LANG['use_enddate'], $_LANG['user_id'], $_LANG['used_time']);
	export_csv_pro($arr, 'export_vc_list', $prev);
}

if ($_REQUEST['act'] == 'pc_query') {
	$list = get_bonus_list();
	$bonus_type = bonus_type_info(intval($_REQUEST['bonus_type']));
	$smarty->assign('show_bonus_sn', 1);
	$smarty->assign('bonus_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('pay_card_view.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

if ($_REQUEST['act'] == 'remove_pc') {
	$id = intval($_GET['id']);
	$db->query('DELETE FROM ' . $ecs->table('pay_card') . ' WHERE id=\'' . $id . '\'');
	$url = 'pay_card.php?act=pc_query&' . str_replace('act=remove_pc', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

?>
