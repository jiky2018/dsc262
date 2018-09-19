<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function get_coupons_list($ru_id = '')
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('coupons') . '';
	$res = $GLOBALS['db']->getOne($sql);
	$result = get_filter();
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('coupons') . '';
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('coupons') . (' ' . $filter['sort_order']);
	set_filter($filter, $sql);
	$arr = array();
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['cou_type'] = $row['cou_type'] == 1 ? '注册赠券' : ($row['cou_type'] == 2 ? '购物赠券' : ($row['cou_type'] == 3 ? '全场赠券' : ($row['cou_type'] == 4 ? '会员赠券' : '')));
		$row['user_name'] = get_shop_name($row['ru_id'], 1);
		$row['cou_start_time'] = local_date('Y-m-d', $row['cou_start_time']);
		$row['cou_end_time'] = local_date('Y-m-d', $row['cou_end_time']);
		$row['cou_is_use'] = $row['cou_is_use'] == 0 ? '未使用' : '<span style=color:red;>已使用</span>';
		$row['cou_is_time'] = $row['cou_is_time'] == 0 ? '未过期' : '<span style=color:red;>已过期</span>';
		$arr[] = $row;
	}

	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_coupons_info($cou_id)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('coupons_user') . ' WHERE cou_id =\'' . $cou_id . '\' ';
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT * FROM' . $GLOBALS['ecs']->table('coupons_user') . 'WHERE cou_id=\'' . $cou_id . '\' ORDER BY uc_id ASC';
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		if ($val['is_use_time']) {
			$row[$key]['is_use_time'] = local_date('Y-m-d H:i:s', $val['is_use_time']);
		}
		else {
			$row[$key]['is_use_time'] = '';
		}

		if ($val['order_id']) {
			$row[$key]['order_sn'] = $GLOBALS['db']->getOne('SELECT order_sn FROM' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id=' . $val['order_id']);
		}

		if ($val['user_id']) {
			$row[$key]['user_name'] = $GLOBALS['db']->getOne('SELECT user_name FROM' . $GLOBALS['ecs']->table('users') . ' WHERE user_id=' . $val['user_id']);
		}
	}

	$arr = array('item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_coupons_info2($cou_id)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('coupons_user') . ' WHERE cou_id =\'' . $cou_id . '\' ';
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT * FROM' . $GLOBALS['ecs']->table('coupons_user') . 'WHERE cou_id=\'' . $cou_id . ('\' ORDER BY uc_id ASC LIMIT ' . $filter['start'] . ' , ' . $filter['page_size']);
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		if ($val['is_use_time']) {
			$row[$key]['is_use_time'] = local_date('Y-m-d H:i:s', $val['is_use_time']);
		}
		else {
			$row[$key]['is_use_time'] = '';
		}

		if ($val['order_id']) {
			$row[$key]['order_sn'] = $GLOBALS['db']->getOne('SELECT order_sn FROM' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id=' . $val['order_id']);
		}

		if ($val['user_id']) {
			$row[$key]['user_name'] = $GLOBALS['db']->getOne('SELECT user_name FROM' . $GLOBALS['ecs']->table('users') . ' WHERE user_id=' . $val['user_id']);
		}
	}

	$arr = array('item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_rank_arr($rank_list, $cou_ok_user = '')
{
	$cou_ok_user = !empty($cou_ok_user) ? explode(',', $cou_ok_user) : array();
	$arr = array();

	if ($rank_list) {
		foreach ($rank_list as $key => $row) {
			$arr[$key]['rank_id'] = $key;
			$arr[$key]['rank_name'] = $row;
			if ($cou_ok_user && in_array($key, $cou_ok_user)) {
				$arr[$key]['is_checked'] = 1;
			}
			else {
				$arr[$key]['is_checked'] = 0;
			}
		}
	}

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

$exc = new exchange($ecs->table('bonus_type'), $db, 'type_id', 'type_name');
$cou = new exchange($ecs->table('coupons'), $db, 'cou_id', 'cou_name');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('ru_id', $adminru['ru_id']);

if ($_REQUEST['act'] == 'list') {
	admin_priv('coupons_manage');
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['cou_list']);
	$smarty->assign('action_link', array('text' => $_LANG['continus_add'], 'href' => 'coupons.php?act=add', 'class' => 'icon-plus'));
	$smarty->assign('full_page', 1);
	$list = get_coupons_type_info('1,2,3,4,5', $adminru['ru_id']);
	$smarty->assign('cou_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$smarty->assign('url', $ecs->seller_url());
	assign_query_info();
	$smarty->display('coupons_type.dwt');
}

if ($_REQUEST['act'] == 'query') {
	if ($_POST['cou_type']) {
		$list = get_coupons_type_info($_POST['cou_type'], $adminru['ru_id']);
	}
	else {
		$list = get_coupons_type_info('1,2,3,4', $adminru['ru_id']);
	}

	$cou_id = intval($_REQUEST['coupons_one_list']);

	if ($cou_id) {
		$list = get_coupons_info2($cou_id);
		$smarty->assign('coupons_list', $list['item']);
		$smarty->assign('filter', $list['filter']);
		$smarty->assign('record_count', $list['record_count']);
		$smarty->assign('page_count', $list['page_count']);
		$smarty->assign('cou_id', $cou_id);
		$page_count_arr = seller_page($list, $_REQUEST['page']);
		$smarty->assign('page_count_arr', $page_count_arr);
		$sort_flag = sort_flag($list['filter']);
		$smarty->assign($sort_flag['tag'], $sort_flag['img']);
		$smarty->assign('url', $ecs->seller_url());
		make_json_result($smarty->fetch('coupons_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
		exit();
	}

	$smarty->assign('cou_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('coupons_type.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

if ($_REQUEST['act'] == 'edit_type_name') {
	check_authz_json('coupons_manage');
	$id = intval($_POST['id']);
	$val = json_str_iconv(trim($_POST['val']));

	if (!$exc->is_only('type_name', $id, $val)) {
		make_json_error($_LANG['type_name_exist']);
	}
	else {
		$exc->edit('type_name=\'' . $val . '\'', $id);
		make_json_result(stripslashes($val));
	}
}

if ($_REQUEST['act'] == 'edit_type_money') {
	check_authz_json('coupons_manage');
	$id = intval($_POST['id']);
	$val = floatval($_POST['val']);

	if ($val <= 0) {
		make_json_error($_LANG['type_money_error']);
	}
	else {
		$exc->edit('type_money=\'' . $val . '\'', $id);
		make_json_result(number_format($val, 2));
	}
}

if ($_REQUEST['act'] == 'edit_min_amount') {
	check_authz_json('coupons_manage');
	$id = intval($_POST['id']);
	$val = floatval($_POST['val']);

	if ($val < 0) {
		make_json_error($_LANG['min_amount_empty']);
	}
	else {
		$exc->edit('min_amount=\'' . $val . '\'', $id);
		make_json_result(number_format($val, 2));
	}
}

if ($_REQUEST['act'] == 'remove') {
	check_authz_json('coupons_manage');
	$id = intval($_GET['id']);
	$cou_arr = $db->getRow('SELECT ru_id FROM ' . $ecs->table('coupons') . (' WHERE cou_id = \'' . $id . '\' LIMIT 1'));

	if ($cou_arr['ru_id'] != $adminru['ru_id']) {
		$url = 'bonus.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}

	$exc->drop($id);
	$db->query('UPDATE ' . $ecs->table('goods') . (' SET bonus_type_id = 0 WHERE bonus_type_id = \'' . $id . '\''));
	$db->query('DELETE FROM ' . $ecs->table('user_bonus') . (' WHERE bonus_type_id = \'' . $id . '\''));
	$url = 'bonus.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

if ($_REQUEST['act'] == 'add') {
	admin_priv('coupons_manage');
	$smarty->assign('cou_type', $_REQUEST['type']);
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('ur_here', '优惠券添加');
	$smarty->assign('action_link', array('href' => 'coupons.php?act=list', 'text' => '优惠券列表', 'class' => 'icon-reply'));
	$smarty->assign('action', 'add');
	$smarty->assign('form_act', 'insert');
	$smarty->assign('cfg_lang', $_CFG['lang']);
	$next_month = local_strtotime('+1 months');
	$bonus_arr['send_start_date'] = local_date('Y-m-d H:i:s');
	$bonus_arr['use_start_date'] = local_date('Y-m-d H:i:s');
	$bonus_arr['send_end_date'] = local_date('Y-m-d H:i:s', $next_month);
	$bonus_arr['use_end_date'] = local_date('Y-m-d H:i:s', $next_month);
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '17_coupons'));
	$cou_arr = array('ru_id' => $adminru['ru_id']);
	$smarty->assign('cou', $cou_arr);
	$rank_list = get_rank_list();
	$rank_list = get_rank_arr($rank_list);
	$smarty->assign('rank_list', $rank_list);
	set_default_filter(0, 0, $adminru['ru_id']);
	assign_query_info();
	$smarty->display('coupons_type_info.dwt');
}

if ($_REQUEST['act'] == 'insert') {
	$cou_start_time = local_strtotime($_POST['cou_start_time']);
	$cou_end_time = local_strtotime($_POST['cou_end_time']);
	$cou_add_time = gmtime();
	$cou_user_num = empty($_POST['cou_user_num']) ? 1 : $_POST['cou_user_num'];
	$cou_goods = '';
	$spec_cat = '';
	$usableCouponGoods = empty($_POST['usableCouponGoods']) ? 1 : $_POST['usableCouponGoods'];
	if (!empty($_POST['cou_goods']) && $usableCouponGoods == 2) {
		$cou_goods = implode(',', array_unique($_POST['cou_goods']));
	}
	else {
		if (!empty($_POST['vc_cat']) && $usableCouponGoods == 3) {
			$spec_cat = implode(',', array_unique($_POST['vc_cat']));
		}
	}

	$cou_ok_goods = '';
	$cou_ok_cat = '';
	$buyableCouponGoods = empty($_POST['buyableCouponGoods']) ? 1 : $_POST['buyableCouponGoods'];
	if (!empty($_POST['cou_ok_goods']) && $buyableCouponGoods == 2) {
		$cou_ok_goods = implode(',', array_unique($_POST['cou_ok_goods']));
	}
	else {
		if (!empty($_POST['vc_ok_cat']) && $buyableCouponGoods == 3) {
			$cou_ok_cat = implode(',', array_unique($_POST['vc_ok_cat']));
		}
		else {
			$cou_ok_goods = 0;
			$cou_ok_cat = '';
		}
	}

	$is_only = $cou->is_only('cou_name', $_POST[cou_name], 0);

	if (!$is_only) {
		sys_msg(sprintf($_LANG['title_exist'], stripslashes($_POST[cou_name])), 1);
	}

	$cou_type = isset($_POST['cou_type']) ? intval($_POST['cou_type']) : 0;
	if ($cou_type == 1 || $cou_type == 3) {
		$rank_list = get_rank_list();
		$cou_ok_user = '';

		foreach ($rank_list as $k => $v) {
			$cou_ok_user .= $k . ',';
		}

		$cou_ok_user = substr($cou_ok_user, 0, -1);
	}
	else if ($cou_type == 4) {
		$cou_ok_user = empty($_POST['cou_user_four']) ? '0' : implode(',', array_unique($_POST['cou_user_four']));
	}
	else {
		$cou_ok_user = empty($_POST['cou_ok_user']) ? '0' : implode(',', array_unique($_POST['cou_ok_user']));
	}

	$sql = 'INSERT INTO ' . $ecs->table('coupons') . (" (\r\n    cou_name,cou_total,cou_man,cou_money,cou_user_num,cou_goods,spec_cat,cou_start_time,cou_end_time,cou_type,cou_get_man,\r\n     cou_ok_user,cou_ok_goods,cou_ok_cat,cou_intro,cou_add_time,ru_id,cou_title)\r\n    VALUES (\r\n            '" . $_POST['cou_name'] . "',\r\n            '" . $_POST['cou_total'] . "',\r\n            '" . $_POST['cou_man'] . "',\r\n            '" . $_POST['cou_money'] . "',\r\n            '" . $cou_user_num . "',\r\n            '" . $cou_goods . "',\r\n            '" . $spec_cat . "',  \r\n            '" . $cou_start_time . "',\r\n            '" . $cou_end_time . "',\r\n            '" . $_POST['cou_type'] . "',\r\n            '" . $_POST['cou_get_man'] . "',\r\n            '" . $cou_ok_user . "',\r\n            '" . $cou_ok_goods . "',\r\n            '" . $cou_ok_cat . "',\r\n            '" . $_POST['cou_intro'] . "',\r\n            '" . $cou_add_time . "',\r\n            '") . $adminru['ru_id'] . ("',\r\n            '" . $_POST['cou_title'] . "'\r\n            )");
	$db->query($sql);

	if ($cou_type == 5) {
		$cou_id = $db->insert_id();
		$region_list = !empty($_REQUEST['free_value']) ? trim($_REQUEST['free_value']) : '';
		$sql = 'INSERT INTO' . $ecs->table('coupons_region') . ('(`cou_id`,`region_list`) VALUES (\'' . $cou_id . '\',\'' . $region_list . '\')');
		$db->query($sql);
	}

	clear_cache_files();
	$link[0]['text'] = $_LANG['back_list'];
	$link[0]['href'] = 'coupons.php?act=list';
	sys_msg($_LANG['add'] . '&nbsp;' . $_POST['type_name'] . '&nbsp;' . $_LANG['attradd_succed'], 0, $link);
}

if ($_REQUEST['act'] == 'edit') {
	admin_priv('coupons_manage');
	$cou_id = !empty($_GET['cou_id']) ? intval($_GET['cou_id']) : 1;
	$cou_arr = $db->getRow('SELECT * FROM ' . $ecs->table('coupons') . (' WHERE cou_id = \'' . $cou_id . '\''));

	if ($cou_arr['ru_id'] != $adminru['ru_id']) {
		$Loaction = 'coupons.php?act=list';
		ecs_header('Location: ' . $Loaction . "\n");
		exit();
	}

	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$cou_arr['cou_start_time'] = local_date('Y-m-d H:i:s', $cou_arr['cou_start_time']);
	$cou_arr['cou_end_time'] = local_date('Y-m-d H:i:s', $cou_arr['cou_end_time']);
	$rank_list = get_rank_list();
	$rank_list = get_rank_arr($rank_list, $cou_arr['cou_ok_user']);
	$smarty->assign('rank_list', $rank_list);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('ur_here', $_LANG['cou_edit']);
	$smarty->assign('action_link', array('href' => 'coupons.php?act=list&' . list_link_postfix(), 'text' => $_LANG['cou_list'], 'class' => 'icon-reply'));
	$smarty->assign('form_act', 'update');

	if ($cou_arr['spec_cat']) {
		$cou_arr['cats'] = get_choose_cat($cou_arr['spec_cat']);
	}
	else if ($cou_arr['cou_goods']) {
		$cou_arr['goods'] = get_choose_goods($cou_arr['cou_goods']);
	}

	if ($cou_arr['cou_ok_cat']) {
		$cou_arr['ok_cat'] = get_choose_cat($cou_arr['cou_ok_cat']);
	}
	else if ($cou_arr['cou_ok_goods']) {
		$cou_arr['ok_goods'] = get_choose_goods($cou_arr['cou_ok_goods']);
	}

	if ($cou_arr['cou_type'] == 5) {
		$region_arr = get_cou_region_list($cou_id);
		$cou_arr['free_value'] = $region_arr['free_value'];
		$cou_arr['free_value_name'] = $region_arr['free_value_name'];
	}

	$smarty->assign('cou', $cou_arr);
	$smarty->assign('cou_act', 'edit');
	$smarty->assign('cou_type', $cou_arr['cou_type']);
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '17_coupons'));
	set_default_filter(0, 0, $adminru['ru_id']);
	assign_query_info();
	$smarty->display('coupons_type_info.dwt');
}

if ($_REQUEST['act'] == 'update') {
	$cou_id = empty($_REQUEST['cou_id']) ? '' : intval($_REQUEST['cou_id']);
	$cou_start_time = local_strtotime($_POST['cou_start_time']);
	$cou_end_time = local_strtotime($_POST['cou_end_time']);
	$cou_add_time = gmtime();
	$cou_user_num = empty($_POST['cou_user_num']) ? 1 : $_POST['cou_user_num'];
	$cou_goods = '';
	$spec_cat = '';
	$usableCouponGoods = empty($_POST['usableCouponGoods']) ? 1 : $_POST['usableCouponGoods'];
	if (!empty($_POST['cou_goods']) && $usableCouponGoods == 2) {
		$cou_goods = implode(',', array_unique($_POST['cou_goods']));
	}
	else {
		if (!empty($_POST['vc_cat']) && $usableCouponGoods == 3) {
			$spec_cat = implode(',', array_unique($_POST['vc_cat']));
		}
	}

	$cou_ok_goods = '';
	$cou_ok_cat = '';
	$buyableCouponGoods = empty($_POST['buyableCouponGoods']) ? 1 : $_POST['buyableCouponGoods'];
	if (!empty($_POST['cou_ok_goods']) && $buyableCouponGoods == 2) {
		$cou_ok_goods = implode(',', array_unique($_POST['cou_ok_goods']));
	}
	else {
		if (!empty($_POST['vc_ok_cat']) && $buyableCouponGoods == 3) {
			$cou_ok_cat = implode(',', array_unique($_POST['vc_ok_cat']));
		}
		else {
			$cou_ok_goods = 0;
			$cou_ok_cat = '';
		}
	}

	$is_only = $cou->is_only('cou_name', $_POST[cou_name], 0, 'cou_id != \'' . $cou_id . '\'');

	if (!$is_only) {
		sys_msg(sprintf($_LANG['title_exist'], stripslashes($_POST[cou_name])), 1);
	}

	$cou_type = isset($_POST['cou_type']) ? intval($_POST['cou_type']) : 0;
	if ($cou_type == 1 || $cou_type == 3) {
		$rank_list = get_rank_list();
		$cou_ok_user = '';

		foreach ($rank_list as $k => $v) {
			$cou_ok_user .= $k . ',';
		}

		$cou_ok_user = substr($cou_ok_user, 0, -1);
	}
	else if ($cou_type == 4) {
		$cou_ok_user = empty($_POST['cou_user_four']) ? '0' : implode(',', array_unique($_POST['cou_user_four']));
	}
	else {
		$cou_ok_user = empty($_POST['cou_ok_user']) ? '0' : implode(',', array_unique($_POST['cou_ok_user']));
	}

	$record = array('cou_name' => $_POST['cou_name'], 'cou_title' => $_POST['cou_title'], 'cou_total' => $_POST['cou_total'], 'cou_man' => $_POST['cou_man'], 'cou_money' => $_POST['cou_money'], 'cou_user_num' => $cou_user_num, 'cou_goods' => $cou_goods, 'spec_cat' => $spec_cat, 'cou_start_time' => $cou_start_time, 'cou_end_time' => $cou_end_time, 'cou_type' => $cou_type, 'cou_get_man' => $_POST['cou_get_man'], 'cou_ok_user' => $cou_ok_user, 'cou_ok_goods' => $cou_ok_goods, 'cou_ok_cat' => $cou_ok_cat, 'cou_intro' => $_POST['cou_intro'], 'cou_add_time' => $cou_add_time);
	$record['review_status'] = 1;
	$db->autoExecute($ecs->table('coupons'), $record, 'UPDATE', 'cou_id = \'' . $cou_id . '\'');

	if ($cou_type == 5) {
		$region_list = !empty($_REQUEST['free_value']) ? trim($_REQUEST['free_value']) : '';
		$sql = 'SELECT COUNT(*) FROM' . $ecs->table('coupons_region') . ('WHERE cou_id = \'' . $cou_id . '\'');
		$count_free = $db->getOne($sql);

		if (0 < $count_free) {
			$sql = 'UPDATE' . $ecs->table('coupons_region') . ('SET region_list = \'' . $region_list . '\' WHERE cou_id = \'' . $cou_id . '\'');
		}
		else {
			$sql = 'INSERT INTO' . $ecs->table('coupons_region') . ('(`cou_id`,`region_list`) VALUES (\'' . $cou_id . '\',\'' . $region_list . '\')');
		}

		$db->query($sql);
	}

	clear_cache_files();
	$link[0]['text'] = $_LANG['back_list'];
	$link[0]['href'] = 'coupons.php?act=list';
	sys_msg($_LANG['edit'] . '&nbsp;' . $_POST['type_name'] . '&nbsp;' . $_LANG['attradd_succed'], 0, $link);
}

if ($_REQUEST['act'] == 'change_order') {
	$cou_id = $_REQUEST['cou_id'];
	$cou_order = $_REQUEST['cou_order'];
	$sql = 'UPDATE ' . $ecs->table('coupons') . 'SET cou_order=\'' . $cou_order . '\' WHERE cou_id=\'' . $cou_id . '\'';

	if ($db->query($sql)) {
		echo $data = 'ok';
	}
}

if ($_REQUEST['act'] == 'send') {
	admin_priv('coupons_manage');
	$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
	assign_query_info();
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['send_bonus']);
	$smarty->assign('action_link', array('href' => 'bonus.php?act=list', 'text' => $_LANG['04_bonustype_list']));

	if ($_REQUEST['send_by'] == SEND_BY_USER) {
		$smarty->assign('id', $id);
		$smarty->assign('ranklist', get_rank_list());
		$smarty->display('bonus_by_user.dwt');
	}
	else if ($_REQUEST['send_by'] == SEND_BY_GOODS) {
		$bonus_type = $db->GetRow('SELECT type_id, type_name FROM ' . $ecs->table('bonus_type') . (' WHERE type_id=\'' . $_REQUEST['id'] . '\''));
		$goods_list = get_bonus_goods($_REQUEST['id']);
		$sql = 'SELECT goods_id FROM ' . $ecs->table('goods') . (' WHERE bonus_type_id > 0 AND bonus_type_id <> \'' . $_REQUEST['id'] . '\'');
		$other_goods_list = $db->getCol($sql);
		$smarty->assign('other_goods', join(',', $other_goods_list));
		$select_category_html = '';
		$select_category_html .= insert_select_category(0, 0, 0, 'cat_id', 1);
		$smarty->assign('select_category_html', $select_category_html);
		$smarty->assign('brand_list', get_brand_list());
		$smarty->assign('bonus_type', $bonus_type);
		$smarty->assign('goods_list', $goods_list);
		$smarty->display('bonus_by_goods.dwt');
	}
	else if ($_REQUEST['send_by'] == SEND_BY_PRINT) {
		$smarty->assign('type_list', get_bonus_type());
		$smarty->display('bonus_by_print.dwt');
	}
}

if ($_REQUEST['act'] == 'send_by_user') {
	$user_list = array();
	$start = empty($_REQUEST['start']) ? 0 : intval($_REQUEST['start']);
	$limit = empty($_REQUEST['limit']) ? 10 : intval($_REQUEST['limit']);
	$validated_email = empty($_REQUEST['validated_email']) ? 0 : intval($_REQUEST['validated_email']);
	$send_count = 0;

	if (isset($_REQUEST['send_rank'])) {
		$rank_id = intval($_REQUEST['rank_id']);

		if (0 < $rank_id) {
			$sql = 'SELECT min_points, max_points, special_rank FROM ' . $ecs->table('user_rank') . (' WHERE rank_id = \'' . $rank_id . '\'');
			$row = $db->getRow($sql);

			if ($row['special_rank']) {
				$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('users') . (' WHERE user_rank = \'' . $rank_id . '\'');
				$send_count = $db->getOne($sql);

				if ($validated_email) {
					$sql = 'SELECT user_id, email, user_name FROM ' . $ecs->table('users') . (' WHERE user_rank = \'' . $rank_id . '\' AND is_validated = 1') . (' LIMIT ' . $start . ', ' . $limit);
				}
				else {
					$sql = 'SELECT user_id, email, user_name FROM ' . $ecs->table('users') . (' WHERE user_rank = \'' . $rank_id . '\'') . (' LIMIT ' . $start . ', ' . $limit);
				}
			}
			else {
				$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('users') . ' WHERE rank_points >= ' . intval($row['min_points']) . ' AND rank_points < ' . intval($row['max_points']);
				$send_count = $db->getOne($sql);

				if ($validated_email) {
					$sql = 'SELECT user_id, email, user_name FROM ' . $ecs->table('users') . ' WHERE rank_points >= ' . intval($row['min_points']) . ' AND rank_points < ' . intval($row['max_points']) . (' AND is_validated = 1 LIMIT ' . $start . ', ' . $limit);
				}
				else {
					$sql = 'SELECT user_id, email, user_name FROM ' . $ecs->table('users') . ' WHERE rank_points >= ' . intval($row['min_points']) . ' AND rank_points < ' . intval($row['max_points']) . (' LIMIT ' . $start . ', ' . $limit);
				}
			}

			$user_list = $db->getAll($sql);
			$count = count($user_list);
		}
	}
	else if (isset($_REQUEST['send_user'])) {
		if (empty($_REQUEST['user'])) {
			sys_msg($_LANG['send_user_empty'], 1);
		}

		$user_array = is_array($_REQUEST['user']) ? $_REQUEST['user'] : explode(',', $_REQUEST['user']);
		$send_count = count($user_array);
		$id_array = array_slice($user_array, $start, $limit);
		$sql = 'SELECT user_id, email, user_name FROM ' . $ecs->table('users') . ' WHERE user_id ' . db_create_in($id_array);
		$user_list = $db->getAll($sql);
		$count = count($user_list);
	}

	$loop = 0;
	$bonus_type = bonus_type_info($_REQUEST['id']);
	$tpl = get_mail_template('send_bonus');
	$today = local_date($GLOBALS['_CFG']['time_format'], gmtime());

	foreach ($user_list as $key => $val) {
		$smarty->assign('user_name', $val['user_name']);
		$smarty->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
		$smarty->assign('send_date', $today);
		$smarty->assign('sent_date', $today);
		$smarty->assign('count', 1);
		$smarty->assign('money', price_format($bonus_type['type_money']));
		$content = $smarty->fetch('str:' . $tpl['template_content']);

		if (add_to_maillist($val['user_name'], $val['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
			$sql = 'INSERT INTO ' . $ecs->table('user_bonus') . '(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) ' . ('VALUES (\'' . $_REQUEST['id'] . '\', 0, \'' . $val['user_id'] . '\', 0, 0, ') . BONUS_MAIL_SUCCEED . ')';
			$db->query($sql);
		}
		else {
			$sql = 'INSERT INTO ' . $ecs->table('user_bonus') . '(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) ' . ('VALUES (\'' . $_REQUEST['id'] . '\', 0, \'' . $val['user_id'] . '\', 0, 0, ') . BONUS_MAIL_FAIL . ')';
			$db->query($sql);
		}

		if ($limit <= $loop) {
			break;
		}
		else {
			$loop++;
		}
	}

	if ($start + $limit < $send_count) {
		$href = 'bonus.php?act=send_by_user&start=' . ($start + $limit) . ('&limit=' . $limit . '&id=' . $_REQUEST['id'] . '&');

		if (isset($_REQUEST['send_rank'])) {
			$href .= 'send_rank=1&rank_id=' . $rank_id;
		}

		if (isset($_REQUEST['send_user'])) {
			$href .= 'send_user=1&user=' . implode(',', $user_array);
		}

		$link[] = array('text' => $_LANG['send_continue'], 'href' => $href);
	}

	$link[] = array('text' => $_LANG['back_list'], 'href' => 'bonus.php?act=list');
	sys_msg(sprintf($_LANG['sendbonus_count'], $count), 0, $link);
}

if ($_REQUEST['act'] == 'send_mail') {
	$bonus_id = intval($_REQUEST['bonus_id']);

	if ($bonus_id <= 0) {
		exit('invalid params');
	}

	include_once ROOT_PATH . 'includes/lib_order.php';
	$bonus = bonus_info($bonus_id);

	if (empty($bonus)) {
		sys_msg($_LANG['bonus_not_exist']);
	}

	$count = send_bonus_mail($bonus['bonus_type_id'], array($bonus_id));
	$link[0]['text'] = $_LANG['back_bonus_list'];
	$link[0]['href'] = 'bonus.php?act=bonus_list&bonus_type=' . $bonus['bonus_type_id'];
	sys_msg(sprintf($_LANG['success_send_mail'], $count), 0, $link);
}

if ($_REQUEST['act'] == 'get_goods_list') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$filters = $json->decode($_GET['JSON']);
	$arr = get_goods_list($filters);
	$opt = array();

	foreach ($arr as $key => $val) {
		$opt[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => $val['shop_price']);
	}

	make_json_result($opt);
}

if ($_REQUEST['act'] == 'add_bonus_goods') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	check_authz_json('coupons_manage');
	$add_ids = $json->decode($_GET['add_ids']);
	$args = $json->decode($_GET['JSON']);
	$type_id = $args[0];

	foreach ($add_ids as $key => $val) {
		$sql = 'UPDATE ' . $ecs->table('goods') . (' SET bonus_type_id=\'' . $type_id . '\' WHERE goods_id=\'' . $val . '\'');
		$db->query($sql, 'SILENT') || make_json_error($db->error());
	}

	$arr = get_bonus_goods($type_id);
	$opt = array();

	foreach ($arr as $key => $val) {
		$opt[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => '');
	}

	make_json_result($opt);
}

if ($_REQUEST['act'] == 'drop_bonus_goods') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	check_authz_json('coupons_manage');
	$drop_goods = $json->decode($_GET['drop_ids']);
	$drop_goods_ids = db_create_in($drop_goods);
	$arguments = $json->decode($_GET['JSON']);
	$type_id = $arguments[0];
	$db->query('UPDATE ' . $ecs->table('goods') . ' SET bonus_type_id = 0 ' . ('WHERE bonus_type_id = \'' . $type_id . '\' AND goods_id ') . $drop_goods_ids);
	$arr = get_bonus_goods($type_id);
	$opt = array();

	foreach ($arr as $key => $val) {
		$opt[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => '');
	}

	make_json_result($opt);
}

if ($_REQUEST['act'] == 'search_users') {
	$keywords = json_str_iconv(trim($_GET['keywords']));
	$sql = 'SELECT user_id, user_name FROM ' . $ecs->table('users') . ' WHERE user_name LIKE \'%' . mysql_like_quote($keywords) . '%\' OR user_id LIKE \'%' . mysql_like_quote($keywords) . '%\'';
	$row = $db->getAll($sql);
	make_json_result($row);
}

if ($_REQUEST['act'] == 'coupons_list') {
	$cou_id = intval($_GET['cou_id']);
	$list = get_coupons_info2($cou_id);
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['coupons_list']);
	$smarty->assign('action_link', array('href' => 'coupons.php?act=list', 'text' => $_LANG['coupons_list'], 'class' => 'icon-reply'));
	$smarty->assign('coupons_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '17_coupons'));
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('coupons_list.dwt');
}

if ($_REQUEST['act'] == 'user_query_coupons') {
	$cou_id = intval($_GET['cou_id']);
	$list = get_coupons_info($cou_id);
	$smarty->assign('ur_here', $_LANG['coupons_list']);
	$smarty->assign('action_link', array('href' => 'coupons.php?act=list', 'text' => $_LANG['coupons_list']));
	$smarty->assign('coupons_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('coupons_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'remove_coupons') {
	$cou_id = intval($_GET['cou_id']);

	if ($cou_id) {
		$res = $db->query('DELETE FROM ' . $ecs->table('coupons') . (' WHERE cou_id=\'' . $cou_id . '\''));

		if ($res) {
			exit('ok');
		}
	}

	$uc_id = intval($_GET['id']);

	if ($uc_id) {
		$cou_id = $db->getOne('SELECT cou_id FROM ' . $ecs->table('coupons_user') . 'WHERE uc_id=\'' . $uc_id . '\'');
		$res = $db->query('DELETE FROM ' . $ecs->table('coupons_user') . (' WHERE uc_id=\'' . $uc_id . '\''));
		$url = 'coupons.php?act=user_query_coupons&cou_id=' . $cou_id . str_replace('act=remove_coupons', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
	}
}

if ($_REQUEST['act'] == 'batch') {
	admin_priv('coupons_manage');
	$bonus_type_id = intval($_REQUEST['bonus_type']);

	if (isset($_POST['checkboxes'])) {
		$bonus_id_list = $_POST['checkboxes'];

		if (isset($_POST['drop'])) {
			$sql = 'DELETE FROM ' . $ecs->table('user_bonus') . ' WHERE bonus_id ' . db_create_in($bonus_id_list);
			$db->query($sql);
			admin_log(count($bonus_id_list), 'remove', 'userbonus');
			clear_cache_files();
			$link[] = array('text' => $_LANG['back_bonus_list'], 'href' => 'bonus.php?act=bonus_list&bonus_type=' . $bonus_type_id);
			sys_msg(sprintf($_LANG['batch_drop_success'], count($bonus_id_list)), 0, $link);
		}
		else if (isset($_POST['mail'])) {
			$count = send_bonus_mail($bonus_type_id, $bonus_id_list);
			$link[] = array('text' => $_LANG['back_bonus_list'], 'href' => 'bonus.php?act=bonus_list&bonus_type=' . $bonus_type_id);
			sys_msg(sprintf($_LANG['success_send_mail'], $count), 0, $link);
		}
	}
	else {
		sys_msg($_LANG['no_select_bonus'], 1);
	}
}

?>
