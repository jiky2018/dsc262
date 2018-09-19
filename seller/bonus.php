<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function get_type_list($ru_id)
{
	$sql = 'SELECT bonus_type_id, COUNT(*) AS sent_count' . ' FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' GROUP BY bonus_type_id';
	$res = $GLOBALS['db']->query($sql);
	$sent_arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$sent_arr[$row['bonus_type_id']] = $row['sent_count'];
	}

	$sql = 'SELECT bonus_type_id, COUNT(*) AS used_count' . ' FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' WHERE used_time > 0' . ' GROUP BY bonus_type_id';
	$res = $GLOBALS['db']->query($sql);
	$used_arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$used_arr[$row['bonus_type_id']] = $row['used_count'];
	}

	$result = get_filter();

	if ($result === false) {
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'bt.type_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['use_type'] = empty($_REQUEST['use_type']) ? 0 : intval($_REQUEST['use_type']);
		$filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);
		$where = ' WHERE 1';

		if ($filter['use_type'] == 1) {
			$where .= ' AND bt.user_id = 0 AND bt.usebonus_type = 0';
		}
		else if ($filter['use_type'] == 2) {
			$where .= ' AND bt.user_id > 0 AND bt.usebonus_type = 0';
		}
		else if ($filter['use_type'] == 3) {
			$where .= ' AND bt.usebonus_type = 1';
		}
		else if ($filter['use_type'] == 4) {
			$where .= ' AND bt.user_id = \'' . $ru_id . '\' AND bt.usebonus_type = 0';
		}
		else if (0 < $ru_id) {
			$where .= ' AND (bt.user_id = \'' . $ru_id . '\' OR bt.usebonus_type = 1)';
		}

		if ($filter['review_status']) {
			$where .= ' AND bt.review_status = \'' . $filter['review_status'] . '\' ';
		}

		if (!empty($filter['keyword'])) {
			$where .= ' AND  bt.type_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'';
		}

		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_where = '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($ru_id == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$where .= ' AND bt.user_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = bt.user_id ' . $store_where . ') > 0 ');
				}
			}
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('bonus_type') . ' AS bt ' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT bt.* FROM ' . $GLOBALS['ecs']->table('bonus_type') . ' AS bt ' . $where . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order']);
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
		$row['user_name'] = get_shop_name($row['user_id'], 1);
		$arr[] = $row;
	}

	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_bonus_goods($type_id)
{
	$sql = 'SELECT goods_id, goods_name FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE bonus_type_id = \'' . $type_id . '\'');
	$row = $GLOBALS['db']->getAll($sql);
	return $row;
}

function get_bonus_list()
{
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'ub.bonus_id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$filter['bonus_type'] = empty($_REQUEST['bonus_type']) ? 0 : intval($_REQUEST['bonus_type']);
	$where = empty($filter['bonus_type']) ? '' : ' WHERE bonus_type_id=\'' . $filter['bonus_type'] . '\'';
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('user_bonus') . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT ub.*, u.user_name, u.email, o.order_sn, bt.type_name ' . ' FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' AS ub ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('bonus_type') . ' AS bt ON bt.type_id=ub.bonus_type_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id=ub.user_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . (' AS o ON o.order_id=ub.order_id ' . $where . ' ') . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
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
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('bonus_type') . (' WHERE type_id = \'' . $bonus_type_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function send_bonus_mail($bonus_type_id, $bonus_id_list)
{
	$bonus_type = bonus_type_info($bonus_type_id);

	if ($bonus_type['send_type'] != SEND_BY_USER) {
		return 0;
	}

	$sql = 'SELECT b.bonus_id, u.user_name, u.email ' . 'FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' AS b, ' . $GLOBALS['ecs']->table('users') . ' AS u ' . ' WHERE b.user_id = u.user_id ' . ' AND b.bonus_id ' . db_create_in($bonus_id_list) . ' AND b.order_id = 0 ' . ' AND u.email <> \'\'';
	$bonus_list = $GLOBALS['db']->getAll($sql);

	if (empty($bonus_list)) {
		return 0;
	}

	$send_count = 0;
	$tpl = get_mail_template('send_bonus');
	$today = local_date($GLOBALS['_CFG']['date_format']);

	foreach ($bonus_list as $bonus) {
		$GLOBALS['smarty']->assign('user_name', $bonus['user_name']);
		$GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
		$GLOBALS['smarty']->assign('send_date', $today);
		$GLOBALS['smarty']->assign('sent_date', $today);
		$GLOBALS['smarty']->assign('count', 1);
		$GLOBALS['smarty']->assign('money', price_format($bonus_type['type_money']));
		$content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);

		if (add_to_maillist($bonus['user_name'], $bonus['email'], $tpl['template_subject'], $content, $tpl['is_html'], false)) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_bonus') . ' SET emailed = \'' . BONUS_MAIL_SUCCEED . '\'' . (' WHERE bonus_id = \'' . $bonus['bonus_id'] . '\'');
			$GLOBALS['db']->query($sql);
			$send_count++;
		}
		else {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_bonus') . ' SET emailed = \'' . BONUS_MAIL_FAIL . '\'' . (' WHERE bonus_id = \'' . $bonus['bonus_id'] . '\'');
			$GLOBALS['db']->query($sql);
		}
	}

	return $send_count;
}

function add_to_maillist($username, $email, $subject, $content, $is_html)
{
	$time = time();
	$content = addslashes($content);
	$template_id = $GLOBALS['db']->getOne('SELECT template_id FROM ' . $GLOBALS['ecs']->table('mail_templates') . ' WHERE template_code = \'send_bonus\'');
	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('email_sendlist') . (' ( email, template_id, email_content, pri, last_send) VALUES (\'' . $email . '\', \'' . $template_id . '\', \'' . $content . '\', 1, \'' . $time . '\')');
	$GLOBALS['db']->query($sql);
	return true;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$smarty->assign('controller', basename(PHP_SELF, '.php'));
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'bonus');
$exc = new exchange($ecs->table('bonus_type'), $db, 'type_id', 'type_name');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('url', $ecs->seller_url());

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['04_bonustype_list']);
	$smarty->assign('action_link', array('text' => $_LANG['bonustype_add'], 'href' => 'bonus.php?act=add', 'class' => 'icon-plus'));
	$smarty->assign('full_page', 1);
	$list = get_type_list($adminru['ru_id']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('type_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('bonus_type.dwt');
}

if ($_REQUEST['act'] == 'query') {
	$list = get_type_list($adminru['ru_id']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('type_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('bonus_type.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

if ($_REQUEST['act'] == 'edit_type_name') {
	check_authz_json('bonus_manage');
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
	check_authz_json('bonus_manage');
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
	check_authz_json('bonus_manage');
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

if ($_REQUEST['act'] == 'edit_min_goods_amount') {
	check_authz_json('bonus_manage');
	$id = intval($_POST['id']);
	$val = floatval($_POST['val']);

	if ($val < 0) {
		make_json_error($_LANG['min_amount_empty']);
	}
	else {
		$exc->edit('min_goods_amount=\'' . $val . '\'', $id);
		make_json_result(number_format($val, 2));
	}
}

if ($_REQUEST['act'] == 'remove') {
	check_authz_json('bonus_manage');
	$id = intval($_GET['id']);
	$bonus_arr = $db->getRow('SELECT * FROM ' . $ecs->table('bonus_type') . (' WHERE type_id = \'' . $id . '\''));

	if ($bonus_arr['user_id'] != $adminru['ru_id']) {
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
	admin_priv('bonus_manage');
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '04_bonustype_list'));
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('ur_here', $_LANG['bonustype_add']);
	$smarty->assign('action_link', array('href' => 'bonus.php?act=list', 'text' => $_LANG['04_bonustype_list'], 'class' => 'icon-reply'));
	$smarty->assign('action', 'add');
	$smarty->assign('form_act', 'insert');
	$smarty->assign('cfg_lang', $_CFG['lang']);
	$next_month = local_strtotime('+1 months');
	$bonus_arr['send_start_date'] = local_date('Y-m-d H:i:s');
	$bonus_arr['use_start_date'] = local_date('Y-m-d H:i:s');
	$bonus_arr['send_end_date'] = local_date('Y-m-d H:i:s', $next_month);
	$bonus_arr['use_end_date'] = local_date('Y-m-d H:i:s', $next_month);
	$smarty->assign('bonus_arr', $bonus_arr);
	assign_query_info();
	$smarty->display('bonus_type_info.dwt');
}

if ($_REQUEST['act'] == 'insert') {
	$type_name = !empty($_POST['type_name']) ? trim($_POST['type_name']) : '';
	$type_id = !empty($_POST['type_id']) ? intval($_POST['type_id']) : 0;
	$min_amount = !empty($_POST['min_amount']) ? intval($_POST['min_amount']) : 0;
	$usebonus_type = isset($_POST['usebonus_type']) ? intval($_POST['usebonus_type']) : 0;
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('bonus_type') . (' WHERE type_name=\'' . $type_name . '\'');

	if (0 < $db->getOne($sql)) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['type_name_exist'], 0, $link);
	}

	if ($_POST['min_goods_amount'] < $_POST['type_money']) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['type_money_beyond'], 0, $link);
	}

	$send_startdate = local_strtotime($_POST['send_start_date']);
	$send_enddate = local_strtotime($_POST['send_end_date']);
	$use_startdate = local_strtotime($_POST['use_start_date']);
	$use_enddate = local_strtotime($_POST['use_end_date']);
	$sql = 'INSERT INTO ' . $ecs->table('bonus_type') . (" (type_name, type_money,send_start_date,send_end_date,use_start_date,use_end_date,send_type, usebonus_type, user_id, min_amount,  min_goods_amount)\r\n    VALUES ('" . $type_name . "',\r\n            '" . $_POST['type_money'] . "',\r\n            '" . $send_startdate . "',\r\n            '" . $send_enddate . "',\r\n            '" . $use_startdate . "',\r\n            '" . $use_enddate . "',\r\n            '" . $_POST['send_type'] . "',\r\n            '" . $usebonus_type . "',    \r\n            '" . $adminru['ru_id'] . "',\r\n            '" . $min_amount . '\',') . '\'' . floatval($_POST['min_goods_amount']) . '\'' . ')';
	$db->query($sql);
	admin_log($_POST['type_name'], 'add', 'bonustype');
	clear_cache_files();
	$link[0]['text'] = $_LANG['continus_add'];
	$link[0]['href'] = 'bonus.php?act=add';
	$link[1]['text'] = $_LANG['back_list'];
	$link[1]['href'] = 'bonus.php?act=list';
	sys_msg($_LANG['add'] . '&nbsp;' . $_POST['type_name'] . '&nbsp;' . $_LANG['attradd_succed'], 0, $link);
}

if ($_REQUEST['act'] == 'edit') {
	admin_priv('bonus_manage');
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '04_bonustype_list'));
	$type_id = !empty($_GET['type_id']) ? intval($_GET['type_id']) : 0;
	$bonus_arr = $db->getRow('SELECT * FROM ' . $ecs->table('bonus_type') . (' WHERE type_id = \'' . $type_id . '\''));
	$bonus_arr['send_start_date'] = local_date('Y-m-d H:i:s', $bonus_arr['send_start_date']);
	$bonus_arr['send_end_date'] = local_date('Y-m-d H:i:s', $bonus_arr['send_end_date']);
	$bonus_arr['use_start_date'] = local_date('Y-m-d H:i:s', $bonus_arr['use_start_date']);
	$bonus_arr['use_end_date'] = local_date('Y-m-d H:i:s', $bonus_arr['use_end_date']);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('ur_here', $_LANG['bonustype_edit']);
	$smarty->assign('action_link', array('href' => 'bonus.php?act=list&' . list_link_postfix(), 'text' => $_LANG['04_bonustype_list'], 'class' => 'icon-reply'));
	$smarty->assign('form_act', 'update');
	$smarty->assign('bonus_arr', $bonus_arr);
	assign_query_info();
	$smarty->display('bonus_type_info.dwt');
}

if ($_REQUEST['act'] == 'update') {
	$send_startdate = local_strtotime($_POST['send_start_date']);
	$send_enddate = local_strtotime($_POST['send_end_date']);
	$use_startdate = local_strtotime($_POST['use_start_date']);
	$use_enddate = local_strtotime($_POST['use_end_date']);
	$type_name = !empty($_POST['type_name']) ? trim($_POST['type_name']) : '';
	$type_id = !empty($_POST['type_id']) ? intval($_POST['type_id']) : 0;
	$min_amount = !empty($_POST['min_amount']) ? intval($_POST['min_amount']) : 0;
	$review_status = 1;
	$usebonus_type = isset($_POST['usebonus_type']) ? intval($_POST['usebonus_type']) : 0;

	if ($_POST['min_goods_amount'] < $_POST['type_money']) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['type_money_beyond'], 0, $link);
	}

	$sql = 'UPDATE ' . $ecs->table('bonus_type') . ' SET ' . ('type_name       = \'' . $type_name . '\', ') . ('type_money      = \'' . $_POST['type_money'] . '\', ') . ('send_start_date = \'' . $send_startdate . '\', ') . ('send_end_date   = \'' . $send_enddate . '\', ') . ('use_start_date  = \'' . $use_startdate . '\', ') . ('use_end_date    = \'' . $use_enddate . '\', ') . ('send_type       = \'' . $_POST['send_type'] . '\', ') . ('usebonus_type   = \'' . $usebonus_type . '\', ') . ('min_amount      = \'' . $min_amount . '\', ') . ('review_status   = \'' . $review_status . '\', ') . 'min_goods_amount = \'' . floatval($_POST['min_goods_amount']) . '\' ' . ('WHERE type_id   = \'' . $type_id . '\'');
	$db->query($sql);
	admin_log($_POST['type_name'], 'edit', 'bonustype');
	clear_cache_files();
	$link[] = array('text' => $_LANG['back_list'], 'href' => 'bonus.php?act=list&' . list_link_postfix());
	sys_msg($_LANG['edit'] . ' ' . $_POST['type_name'] . ' ' . $_LANG['attradd_succed'], 0, $link);
}

if ($_REQUEST['act'] == 'send') {
	admin_priv('bonus_manage');
	$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
	assign_query_info();
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['send_bonus']);
	$smarty->assign('action_link', array('href' => 'bonus.php?act=list', 'text' => $_LANG['04_bonustype_list'], 'class' => 'icon-reply'));
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '04_bonustype_list'));

	if ($_REQUEST['send_by'] == SEND_BY_USER) {
		$smarty->assign('id', $id);
		$smarty->assign('ranklist', get_rank_list());
		$smarty->display('bonus_by_user.dwt');
	}
	else if ($_REQUEST['send_by'] == SEND_BY_GOODS) {
		$smarty->assign('id', $id);
		set_default_filter(0, 0, $adminru['ru_id']);
		$smarty->assign('filter_brand_list', search_brand_list());
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
	else if ($_REQUEST['send_by'] == SEND_BY_GET) {
		$bonus = array();
		$sql = 'SELECT type_id, type_name, type_money FROM ' . $GLOBALS['ecs']->table('bonus_type') . ' WHERE send_type = 4';
		$res = $GLOBALS['db']->query($sql);

		while ($row = $GLOBALS['db']->fetchRow($res)) {
			$bonus[$row['type_id']] = $row['type_name'] . ' [' . sprintf($GLOBALS['_CFG']['currency_format'], $row['type_money']) . ']';
		}

		$smarty->assign('type_list', $bonus);
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

if ($_REQUEST['act'] == 'send_by_print') {
	@set_time_limit(0);
	$bonus_typeid = !empty($_POST['bonus_type_id']) ? $_POST['bonus_type_id'] : 0;
	$bonus_sum = !empty($_POST['bonus_sum']) ? $_POST['bonus_sum'] : 1;
	$num = $db->getOne('SELECT MAX(bonus_sn) FROM ' . $ecs->table('user_bonus'));
	$num = $num ? floor($num / 10000) : 100000;
	$i = 0;

	for ($j = 0; $i < $bonus_sum; $i++) {
		$bonus_sn = $num + $i . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
		$bonus_password = strtoupper(mc_random(10));
		$db->query('INSERT INTO ' . $ecs->table('user_bonus') . (' (bonus_type_id, bonus_sn, bonus_password) VALUES(\'' . $bonus_typeid . '\', \'' . $bonus_sn . '\', \'' . $bonus_password . '\')'));
		$j++;
	}

	admin_log($bonus_sn, 'add', 'userbonus');
	clear_cache_files();
	$link[0]['text'] = $_LANG['back_bonus_list'];
	$link[0]['href'] = 'bonus.php?act=bonus_list&bonus_type=' . $bonus_typeid;
	sys_msg($_LANG['creat_bonus'] . $j . $_LANG['creat_bonus_num'], 0, $link);
}

if ($_REQUEST['act'] == 'gen_excel') {
	@set_time_limit(0);
	$tid = !empty($_GET['tid']) ? intval($_GET['tid']) : 0;
	$type_name = $db->getOne('SELECT type_name FROM ' . $ecs->table('bonus_type') . (' WHERE type_id = \'' . $tid . '\''));
	$bonus_filename = $type_name . '_bonus_list';

	if (EC_CHARSET != 'gbk') {
		$bonus_filename = ecs_iconv('UTF8', 'GB2312', $bonus_filename);
	}

	header('Content-type: application/vnd.ms-excel; charset=utf-8');
	header('Content-Disposition: attachment; filename=' . $bonus_filename . '.xls');

	if (EC_CHARSET != 'gbk') {
		echo ecs_iconv('UTF8', 'GB2312', $_LANG['bonus_excel_file']) . "\t\n";
		echo ecs_iconv('UTF8', 'GB2312', $_LANG['bonus_sn']) . '	';
		echo ecs_iconv('UTF8', 'GB2312', $_LANG['bind_password']) . '	';
		echo ecs_iconv('UTF8', 'GB2312', $_LANG['type_money']) . '	';
		echo ecs_iconv('UTF8', 'GB2312', $_LANG['type_name']) . '	';
		echo ecs_iconv('UTF8', 'GB2312', $_LANG['use_enddate']) . "\t\n";
	}
	else {
		echo $_LANG['bonus_excel_file'] . "\t\n";
		echo $_LANG['bonus_sn'] . '	';
		echo $_LANG['bind_password'] . '	';
		echo $_LANG['type_money'] . '	';
		echo $_LANG['type_name'] . '	';
		echo $_LANG['use_enddate'] . "\t\n";
	}

	$val = array();
	$sql = 'SELECT ub.bonus_id, ub.bonus_type_id, ub.bonus_sn, bonus_password, bt.type_name, bt.type_money, bt.use_end_date ' . 'FROM ' . $ecs->table('user_bonus') . ' AS ub, ' . $ecs->table('bonus_type') . ' AS bt ' . ('WHERE bt.type_id = ub.bonus_type_id AND ub.bonus_type_id = \'' . $tid . '\' ORDER BY ub.bonus_id DESC');
	$res = $db->query($sql);
	$code_table = array();

	while ($val = $db->fetchRow($res)) {
		echo $val['bonus_sn'] . '	';
		echo $val['bonus_password'] . '	';
		echo $val['type_money'] . '	';

		if (!isset($code_table[$val['type_name']])) {
			if (EC_CHARSET != 'gbk') {
				$code_table[$val['type_name']] = ecs_iconv('UTF8', 'GB2312', $val['type_name']);
			}
			else {
				$code_table[$val['type_name']] = $val['type_name'];
			}
		}

		echo $code_table[$val['type_name']] . '	';
		echo local_date('Y-m-d H:i:s', $val['use_end_date']);
		echo "\t\n";
	}
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
	check_authz_json('bonus_manage');
	$add_ids = explode(',', trim($_GET['add_ids']));
	$type_id = $_GET['bid'];

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
	check_authz_json('bonus_manage');
	$drop_goods = explode(',', trim($_GET['drop_ids']));
	$drop_goods_ids = db_create_in($drop_goods);
	$type_id = $_GET['bid'];
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

if ($_REQUEST['act'] == 'bonus_list') {
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['bonus_list']);
	$smarty->assign('action_link', array('href' => 'bonus.php?act=list', 'text' => $_LANG['04_bonustype_list'], 'class' => 'icon-reply'));
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '04_bonustype_list'));
	$list = get_bonus_list();
	$bonus_type = bonus_type_info(intval($_REQUEST['bonus_type']));

	if ($bonus_type['send_type'] == SEND_BY_PRINT) {
		$smarty->assign('show_bonus_sn', 1);
	}
	else if ($bonus_type['send_type'] == SEND_BY_USER) {
		$smarty->assign('show_mail', 1);
	}

	$smarty->assign('bonus_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	assign_query_info();
	$smarty->display('bonus_list.dwt');
}

if ($_REQUEST['act'] == 'query_bonus') {
	$list = get_bonus_list();
	$bonus_type = bonus_type_info(intval($_REQUEST['bonus_type']));

	if ($bonus_type['send_type'] == SEND_BY_PRINT) {
		$smarty->assign('show_bonus_sn', 1);
	}
	else if ($bonus_type['send_type'] == SEND_BY_USER) {
		$smarty->assign('show_mail', 1);
	}

	$smarty->assign('bonus_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	make_json_result($smarty->fetch('bonus_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'remove_bonus') {
	check_authz_json('bonus_manage');
	$id = intval($_GET['id']);
	$db->query('DELETE FROM ' . $ecs->table('user_bonus') . (' WHERE bonus_id=\'' . $id . '\''));
	$url = 'bonus.php?act=query_bonus&' . str_replace('act=remove_bonus', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

if ($_REQUEST['act'] == 'batch') {
	admin_priv('bonus_manage');
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
