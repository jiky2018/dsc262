<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function vc_type_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = ' WHERE 1 ';
		$where .= !empty($filter['keyword']) ? ' AND (ggt.gift_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\')' : '';
		$sql = ' SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('value_card_type') . ' AS t ' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('value_card_type') . ' AS t' . (' ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$arr = array();
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$array = array($GLOBALS['_LANG']['all_goods'], $GLOBALS['_LANG']['spec_cat'], $GLOBALS['_LANG']['spec_goods']);
		$row['use_condition'] = $array[$row['use_condition']];
		$row['vc_indate'] = $row['vc_indate'] . $GLOBALS['_LANG']['months'];
		$row['vc_dis'] = $row['vc_dis'] * 100 . '%';
		$row['send_amount'] = send_amount($row['id']);
		$row['use_amount'] = use_amount($row['id']);
		$arr[] = $row;
	}

	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function vc_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['tid'] = empty($_REQUEST['tid']) ? 0 : trim($_REQUEST['tid']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'vc.vid' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['value_card_type'] = empty($_REQUEST['value_card_type']) ? 0 : intval($_REQUEST['value_card_type']);
		$where = ' WHERE 1 ';

		if ($filter['tid']) {
			$where .= ' AND tid = \'' . $filter['tid'] . '\' ';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('value_card') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = ' SELECT vc.*, t.name, u.user_name FROM ' . $GLOBALS['ecs']->table('value_card') . ' AS vc ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('value_card_type') . ' AS t ON vc.tid = t.id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id = vc.user_id ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$row[$key]['bind_time'] = 0 < $val['bind_time'] ? local_date($GLOBALS['_CFG']['date_format'], $val['bind_time']) : $GLOBALS['_LANG']['no_use'];
	}

	$arr = array('item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function send_amount($id)
{
	$sql = ' SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('value_card') . (' WHERE tid = \'' . $id . '\' ');
	return $GLOBALS['db']->getOne($sql);
}

function use_amount($id)
{
	$sql = ' SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('value_card') . (' WHERE tid = \'' . $id . '\' AND user_id > 0 AND bind_time > 0 ');
	return $GLOBALS['db']->getOne($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$exc = new exchange($ecs->table('value_card_type'), $db, 'id', 'name');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', $_LANG['vc_type_list']);
	$smarty->assign('action_link', array('text' => $_LANG['vc_type_add'], 'href' => 'value_card.php?act=vc_type_add'));
	$smarty->assign('full_page', 1);
	$list = vc_type_list();
	$smarty->assign('value_card_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('vc_type_list.dwt');
}

if ($_REQUEST['act'] == 'query') {
	$list = vc_type_list();
	$smarty->assign('value_card_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('vc_type_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

if ($_REQUEST['act'] == 'vc_query') {
	$vc_list = vc_list();
	$smarty->assign('value_card_list', $vc_list['item']);
	$smarty->assign('filter', $vc_list['filter']);
	$smarty->assign('record_count', $vc_list['record_count']);
	$smarty->assign('page_count', $vc_list['page_count']);
	$sort_flag = sort_flag($vc_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('value_card_view.dwt'), '', array('filter' => $vc_list['filter'], 'page_count' => $vc_list['page_count']));
}

if ($_REQUEST['act'] == 'vc_type_add' || $_REQUEST['act'] == 'vc_type_edit') {
	if ($_REQUEST['act'] == 'vc_type_add') {
		$smarty->assign('ur_here', $_LANG['vc_type_add']);
		$smarty->assign('form_act', 'insert');
	}
	else {
		$id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;
		$sql = ' SELECT * FROM ' . $ecs->table('value_card_type') . (' WHERE id = \'' . $id . '\' ');
		$row = $db->getRow($sql);
		$row['vc_dis'] = $row['vc_dis'] * 100;

		if ($row['use_condition'] == 1) {
			$row['cats'] = get_choose_cat($row['spec_cat']);
		}
		else if ($row['use_condition'] == 2) {
			$row['goods'] = get_choose_goods($row['spec_goods']);
		}

		if ($row['use_merchants'] == 'all') {
			$row['use_merchants'] = 0;
		}
		else if ($row['use_merchants'] == 'self') {
			$row['use_merchants'] = 1;
		}
		else {
			$row['selected_merchants'] = $row['use_merchants'];
			$row['use_merchants'] = 2;
		}

		$smarty->assign('ur_here', $_LANG['vc_type_edit']);
		$smarty->assign('form_act', 'update');
	}

	$smarty->assign('vc', $row);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('action_link', array('href' => 'value_card.php?act=list', 'text' => $_LANG['vc_type_list']));
	$smarty->assign('cfg_lang', $_CFG['lang']);
	set_default_filter();
	assign_query_info();
	$smarty->display('vc_type_info.dwt');
}

if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
	$name = isset($_POST['name']) ? trim($_POST['name']) : '';
	$vc_desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';
	$vc_limit = isset($_POST['limit']) ? intval($_POST['limit']) : 1;
	$vc_value = isset($_POST['value']) ? intval($_POST['value']) : 0;
	$vc_dis = !empty($_POST['vc_dis']) ? intval($_POST['vc_dis']) / 100 : 1;
	$vc_indate = !empty($_POST['indate']) ? intval($_POST['indate']) : 36;
	$use_condition = isset($_POST['use_condition']) ? intval($_POST['use_condition']) : 0;
	$use_merchants = isset($_POST['use_merchants']) ? intval($_POST['use_merchants']) : '';
	$spec_cat = !empty($_POST['vc_cat']) && $use_condition == 1 ? implode(',', array_unique($_POST['vc_cat'])) : '';
	$spec_goods = !empty($_POST['vc_goods']) && $use_condition == 2 ? implode(',', array_unique($_POST['vc_goods'])) : '';
	$prefix = isset($_POST['prefix']) ? trim($_POST['prefix']) : 0;
	$is_rec = isset($_POST['is_rec']) ? intval($_POST['is_rec']) : 0;
	$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if ($use_merchants == 0) {
		$use_merchants = 'all';
	}
	else if ($use_merchants == 1) {
		$use_merchants = 'self';
	}
	else if ($use_merchants == 2) {
		$use_merchants = isset($_POST['selected_merchants']) ? trim($_POST['selected_merchants']) : '';
	}

	if (0 < $id) {
		$sql = ' UPDATE ' . $ecs->table('value_card_type') . ' SET ' . (' name = \'' . $name . '\', ') . (' vc_desc = \'' . $vc_desc . '\', ') . (' vc_limit = \'' . $vc_limit . '\', ') . (' vc_value = \'' . $vc_value . '\', ') . (' vc_prefix = \'' . $prefix . '\', ') . (' vc_dis = \'' . $vc_dis . '\', ') . (' vc_indate = \'' . $vc_indate . '\', ') . (' use_condition = \'' . $use_condition . '\', ') . (' use_merchants = \'' . $use_merchants . '\', ') . (' spec_goods = \'' . $spec_goods . '\', ') . (' spec_cat = \'' . $spec_cat . '\', ') . (' is_rec = \'' . $is_rec . '\' ') . (' WHERE id = \'' . $id . '\' ');
		$db->query($sql);
		$notice = '编辑类型成功！';
	}
	else {
		$value_card = array('name' => $name, 'vc_desc' => $vc_desc, 'vc_limit' => $vc_limit, 'vc_value' => $vc_value, 'vc_prefix' => $prefix, 'vc_dis' => $vc_dis, 'vc_indate' => $vc_indate, 'use_condition' => $use_condition, 'use_merchants' => $use_merchants, 'spec_goods' => $spec_goods, 'spec_cat' => $spec_cat, 'is_rec' => $is_rec, 'add_time' => gmtime());
		$db->autoExecute($ecs->table('value_card_type'), $value_card, 'INSERT');
		$notice = '添加类型成功！';
	}

	$link[0]['text'] = $_LANG['back_list'];
	$link[0]['href'] = 'value_card.php?act=list';
	sys_msg($notice, 0, $link);
}

if ($_REQUEST['act'] == 'remove') {
	$id = intval($_GET['id']);
	$sql = ' SELECT COUNT(*) FROM ' . $ecs->table('value_card') . (' WHERE tid = \'' . $id . '\' AND user_id > 0 ');
	$row = $db->getOne($sql);

	if (0 < $row) {
		make_json_error($_LANG['notice_remove_type_error']);
	}
	else {
		$exc->drop($id);
		$sql = ' DELETE FROM ' . $ecs->table('value_card') . (' WHERE tid = \'' . $id . '\' ');
		$db->query($sql);
		$url = 'value_card.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
	}

	exit();
}
else if ($_REQUEST['act'] == 'batch_remove') {
	if (empty($_POST['checkboxes'])) {
		sys_msg($_LANG['no_record_selected']);
	}
	else {
		$ids = $_POST['checkboxes'];
		$sql = ' SELECT COUNT(*) FROM ' . $ecs->table('value_card') . ' WHERE tid' . db_create_in($ids) . ' AND user_id > 0 ';
		$row = $db->getOne($sql);

		if (isset($_POST['drop'])) {
			if (0 < $row) {
				$links[] = array('text' => $_LANG['back_list'], 'href' => 'value_card.php?act=list&' . list_link_postfix());
				sys_msg($_LANG['notice_remove_type_error'], 1, $links);
			}
			else {
				$sql = 'DELETE FROM ' . $ecs->table('value_card_type') . ' WHERE id ' . db_create_in($ids);
				$res = $db->query($sql);

				if ($res) {
					$sql = ' DELETE FROM ' . $ecs->table('value_card') . ' WHERE tid ' . db_create_in($ids);
					$db->query($sql);
				}

				admin_log('', 'batch_remove', 'value_card');
				clear_cache_files();
				$links[] = array('text' => $_LANG['back_list'], 'href' => 'value_card.php?act=list&' . list_link_postfix());
				sys_msg($_LANG['batch_drop_ok'], 0, $links);
			}
		}
	}
}

if ($_REQUEST['act'] == 'remove_vc') {
	$id = intval($_GET['id']);
	$sql = ' SELECT user_id FROM ' . $ecs->table('value_card') . (' WHERE vid = \'' . $id . '\' ');
	$row = $db->getOne($sql);

	if (0 < $row) {
		make_json_error($_LANG['notice_remove_vc_error']);
	}
	else {
		$sql = ' DELETE FROM ' . $ecs->table('value_card') . (' WHERE vid = \'' . $id . '\' ');
		$db->query($sql);
		$url = 'value_card.php?act=vc_query&' . str_replace('act=remove_vc', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
	}

	exit();
}

if ($_REQUEST['act'] == 'send') {
	$id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;
	$smarty->assign('type_id', $id);
	$smarty->assign('ur_here', $_LANG['value_card_send']);
	$smarty->display('value_card_send.dwt');
}

if ($_REQUEST['act'] == 'vc_list') {
	$id = $_REQUEST['tid'] ? intval($_REQUEST['tid']) : 0;
	$smarty->assign('action_link', array('text' => $_LANG['export_vc_list'], 'href' => 'value_card.php?act=export_vc_list&id=' . $id));
	$vc_list = vc_list();
	$smarty->assign('value_card_list', $vc_list['item']);
	$smarty->assign('filter', $vc_list['filter']);
	$smarty->assign('record_count', $vc_list['record_count']);
	$smarty->assign('page_count', $vc_list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['value_card_list']);
	$smarty->display('value_card_view.dwt');
}

if ($_REQUEST['act'] == 'export_vc_list') {
	$id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;
	$where = ' WHERE 1 ';

	if (0 < $id) {
		$where .= ' AND vc.tid = \'' . $id . '\' ';
	}

	$arr = array();
	$sql = ' SELECT vc.vid,vc.value_card_sn,vc.value_card_password,vc.vc_value,vc.bind_time, t.name, u.user_name FROM ' . $GLOBALS['ecs']->table('value_card') . ' AS vc ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('value_card_type') . ' AS t ON vc.tid = t.id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id = vc.user_id ' . $where;
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$arr[$key]['vid'] = $val['vid'];
		$arr[$key]['value_card_sn'] = $val['value_card_sn'];
		$arr[$key]['value_card_password'] = $val['value_card_password'];
		$arr[$key]['name'] = $val['name'];
		$arr[$key]['vc_value'] = $val['vc_value'];
		$arr[$key]['user_name'] = $val['user_name'];
		$arr[$key]['bind_time'] = 0 < $val['bind_time'] ? local_date($GLOBALS['_CFG']['date_format'], $val['bind_time']) : $GLOBALS['_LANG']['no_use'];
	}

	$prev = array($_LANG['record_id'], $_LANG['value_card_sn'], $_LANG['value_card_password'], $_LANG['value_card_type'], $_LANG['value_card_value'], $_LANG['bind_user'], $_LANG['bind_time']);
	export_csv_pro($arr, 'export_vc_list', $prev);
}

if ($_REQUEST['act'] == 'send_value_card') {
	@set_time_limit(0);
	$tid = $_POST['type_id'] ? intval($_POST['type_id']) : 0;
	$send_sum = !empty($_POST['send_num']) ? intval($_POST['send_num']) : 1;
	$card_type = intval($_POST['card_type']);
	$password_type = intval($_POST['password_type']);
	$sql = ' SELECT vc_value, vc_prefix FROM ' . $GLOBALS['ecs']->table('value_card_type') . (' WHERE id = \'' . $tid . '\' ');
	$row = $GLOBALS['db']->getRow($sql);
	$vc_prefix = $row['vc_prefix'] ? trim($row['vc_prefix']) : '';
	$prefix_len = strlen($vc_prefix);
	$length = $prefix_len + $card_type;
	$num = $db->getOne(' SELECT MAX(SUBSTRING(value_card_sn,' . $prefix_len . '+1)) FROM ' . $ecs->table('value_card') . (' WHERE tid = \'' . $tid . '\' AND LENGTH(value_card_sn) = \'' . $length . '\' '));
	$num = $num ? intval($num) : 1;
	$i = 0;

	for ($j = 0; $i < $send_sum; $i++) {
		$value_card_sn = $vc_prefix . str_pad($num + $i + 1, $card_type, '0', STR_PAD_LEFT);
		$value_card_password = strtoupper(mc_random($password_type));
		$db->query('INSERT INTO ' . $ecs->table('value_card') . (' (tid, value_card_sn, value_card_password, vc_value, card_money) VALUES(\'' . $tid . '\', \'' . $value_card_sn . '\', \'' . $value_card_password . '\', \'' . $row['vc_value'] . '\', \'' . $row['vc_value'] . '\')'));
		$j++;
	}

	admin_log($value_card_sn, 'add', 'value_card');
	clear_cache_files();
	$link[0]['text'] = $_LANG['back_list'];
	$link[0]['href'] = 'value_card.php?act=list';
	sys_msg($_LANG['creat_value_card'] . $j . $_LANG['value_card_num'], 0, $link);
}

if ($_REQUEST['act'] == 'select_merchants') {
	require_once ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$selected = !empty($_GET['selected']) ? trim($_GET['selected']) : '';
	$sql = ' SELECT ru_id FROM ' . $ecs->table('seller_shopinfo') . ' WHERE ru_id > 0 ';
	$shop_ids = $db->getAll($sql);
	$can_choice = array();

	foreach ($shop_ids as $k => $v) {
		$can_choice[$k]['ru_id'] = $v['ru_id'];
		$can_choice[$k]['rz_shopName'] = get_shop_name($v['ru_id'], 1);
	}

	$is_choice = array();
	$is_choice = explode(',', $selected);
	$smarty->assign('can_choice', $can_choice);
	$smarty->assign('is_choice', $is_choice);
	$result['content'] = $GLOBALS['smarty']->fetch('library/merchants_list.lbi');
	exit($json->encode($result));
}

?>
