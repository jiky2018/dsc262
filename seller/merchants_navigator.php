<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_nav()
{
	$adminru = get_admin_ru_id();
	$where = '';

	if (0 < $adminru['ru_id']) {
		$where .= ' where ru_id = ' . $adminru['ru_id'];
	}

	$result = get_filter();

	if ($result === false) {
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'type DESC, vieworder' : 'type DESC, ' . trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('merchants_nav') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT id, name, ifshow, vieworder, opennew, url, type, ru_id' . ' FROM ' . $GLOBALS['ecs']->table('merchants_nav') . $where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$navdb = $GLOBALS['db']->getAll($sql);
	$type = '';
	$navdb2 = array();

	foreach ($navdb as $k => $v) {
		if (!empty($type) && ($type != $v['type'])) {
			$navdb2[] = array();
		}

		$navdb2[] = $v;
		$data = array('shoprz_brandName', 'shop_class_keyWords', 'shopNameSuffix');
		$shop_info = get_table_date('merchants_shop_information', 'user_id = \'' . $v['ru_id'] . '\'', $data);
		$navdb2[$k]['user_name'] = $shop_info['shoprz_brandName'] . $shop_info['shopNameSuffix'];
		$type = $v['type'];
	}

	$arr = array('navdb' => $navdb2, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function sort_nav($a, $b)
{
	return $b['vieworder'] < $a['vieworder'] ? 1 : -1;
}

function get_sysnav()
{
	$adminru = get_admin_ru_id();
	global $_LANG;
	$catlist = cat_list(0, 0, 0, 'merchants_category', array(), 0, $adminru['ru_id']);

	foreach ($catlist as $key => $val) {
		$val['url'] = build_uri('merchants_store', array('cid' => $val['cat_id'], 'urid' => $adminru['ru_id']), $val['cat_name']);
		$sysmain[] = array('cat_id' => $val['cat_id'], 'cat_name' => $val['cat_name'], 'url' => $val['url']);
	}

	return $sysmain;
}

function nav_update($id, $args)
{
	if (empty($args) || empty($id)) {
		return false;
	}

	return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_nav'), $args, 'update', 'id=\'' . $id . '\'');
}

function analyse_uri($uri)
{
	$uri = strtolower(str_replace('&amp;', '&', $uri));
	$arr = explode('-', $uri);

	switch ($arr[0]) {
	case 'category':
		return array('type' => 'c', 'id' => $arr[1]);
		break;

	case 'article_cat':
		return array('type' => 'a', 'id' => $arr[1]);
		break;

	default:
		break;
	}

	list($fn, $pm) = explode('?', $uri);

	if (strpos($uri, '&') === false) {
		$arr = array($pm);
	}
	else {
		$arr = explode('&', $pm);
	}

	switch ($fn) {
	case 'category.php':
		foreach ($arr as $k => $v) {
			list($key, $val) = explode('=', $v);

			if ($key == 'id') {
				return array('type' => 'c', 'id' => $val);
			}
		}

		break;

	case 'article_cat.php':
		foreach ($arr as $k => $v) {
			list($key, $val) = explode('=', $v);

			if ($key == 'id') {
				return array('type' => 'a', 'id' => $val);
			}
		}

		break;

	default:
		return false;
		break;
	}
}

function is_show_in_nav($type, $id)
{
	if ($type == 'c') {
		$tablename = $GLOBALS['ecs']->table('category');
	}
	else {
		$tablename = $GLOBALS['ecs']->table('article_cat');
	}

	return $GLOBALS['db']->getOne('SELECT show_in_nav FROM ' . $tablename . ' WHERE cat_id = \'' . $id . '\'');
}

function set_show_in_nav($type, $id, $val)
{
	if ($type == 'c') {
		$tablename = $GLOBALS['ecs']->table('category');
	}
	else {
		$tablename = $GLOBALS['ecs']->table('article_cat');
	}

	$GLOBALS['db']->query('UPDATE ' . $tablename . ' SET show_in_nav = \'' . $val . '\' WHERE cat_id = \'' . $id . '\'');
	clear_cache_files();
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'index');
$exc = new exchange($ecs->table('merchants_nav'), $db, 'id', 'name');
$smarty->assign('menu_select', array('action' => '19_merchants_store', 'current' => '04_merchants_basic_nav'));
$adminru = get_admin_ru_id();

if ($_REQUEST['act'] == 'list') {
	admin_priv('seller_store_other');
	$smarty->assign('primary_cat', $_LANG['19_merchants_store']);
	$smarty->assign('ur_here', $_LANG['navigator']);
	$smarty->assign('action_link', array('text' => $_LANG['add_new'], 'href' => 'merchants_navigator.php?act=add', 'class' => 'icon-plus'));
	$smarty->assign('full_page', 1);
	$navdb = get_nav();

	if ($adminru['ru_id'] == 0) {
		$smarty->assign('priv_ru', 1);
	}
	else {
		$smarty->assign('priv_ru', 0);
	}

	$page_count_arr = seller_page($navdb, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('navdb', $navdb['navdb']);
	$smarty->assign('filter', $navdb['filter']);
	$smarty->assign('record_count', $navdb['record_count']);
	$smarty->assign('page_count', $navdb['page_count']);
	assign_query_info();
	$smarty->assign('current', 'merchants_navigator');
	$smarty->display('store_navigation.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$navdb = get_nav();
	$page_count_arr = seller_page($navdb, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);

	if ($adminru['ru_id'] == 0) {
		$smarty->assign('priv_ru', 1);
	}
	else {
		$smarty->assign('priv_ru', 0);
	}

	$smarty->assign('navdb', $navdb['navdb']);
	$smarty->assign('filter', $navdb['filter']);
	$smarty->assign('record_count', $navdb['record_count']);
	$smarty->assign('page_count', $navdb['page_count']);
	$sort_flag = sort_flag($navdb['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$smarty->assign('current', 'merchants_navigator');
	make_json_result($smarty->fetch('store_navigation.dwt'), '', array('filter' => $navdb['filter'], 'page_count' => $navdb['page_count']));
}
else if ($_REQUEST['act'] == 'add') {
	$smarty->assign('primary_cat', $_LANG['19_merchants_store']);

	if (empty($_REQUEST['step'])) {
		$rt = array('act' => 'add');
		$sysmain = get_sysnav();
		$smarty->assign('action_link', array('text' => $_LANG['go_list'], 'href' => 'merchants_navigator.php?act=list', 'class' => 'icon-reply'));
		$smarty->assign('ur_here', $_LANG['navigator']);
		assign_query_info();
		$smarty->assign('sysmain', $sysmain);
		$smarty->assign('rt', $rt);
		$smarty->assign('current', 'merchants_navigator');
		$smarty->display('store_navigation_add.dwt');
	}
	else if ($_REQUEST['step'] == 2) {
		$item_name = $_REQUEST['item_name'];
		$item_url = $_REQUEST['item_url'];
		$item_ifshow = $_REQUEST['item_ifshow'];
		$item_opennew = $_REQUEST['item_opennew'];
		$item_type = $_REQUEST['item_type'];
		$item_catId = trim($_REQUEST['item_catId']);
		$item_catId = intval($item_catId);
		$vieworder = $db->getOne('SELECT max(vieworder) FROM ' . $ecs->table('merchants_nav') . ' WHERE type = \'' . $item_type . '\'');
		$item_vieworder = (empty($_REQUEST['item_vieworder']) ? $vieworder + 1 : $_REQUEST['item_vieworder']);
		$sql = '';
		if (($item_ifshow == 1) && ($item_type == 'middle')) {
			$arr = analyse_uri($item_url);

			if ($arr) {
				set_show_in_nav($arr['type'], $arr['id'], 1);
				$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('merchants_nav') . ' (name,ctype,cid,ifshow,cat_id,vieworder,opennew,url,type,ru_id) VALUES(\'' . $item_name . '\',\'' . $arr['type'] . '\',\'' . $arr['id'] . '\',\'' . $item_ifshow . '\',\'' . $item_catId . '\',\'' . $item_vieworder . '\',\'' . $item_opennew . '\',\'' . $item_url . '\',\'' . $item_type . '\', \'' . $adminru['ru_id'] . '\')';
			}
		}

		if (empty($sql)) {
			$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('merchants_nav') . ' (name,ifshow,vieworder,opennew,url,type,ru_id) VALUES(\'' . $item_name . '\',\'' . $item_ifshow . '\',\'' . $item_vieworder . '\',\'' . $item_opennew . '\',\'' . $item_url . '\',\'' . $item_type . '\', \'' . $adminru['ru_id'] . '\')';
		}

		$db->query($sql);
		clear_cache_files();
		$links[] = array('text' => $_LANG['navigator'], 'href' => 'merchants_navigator.php?act=list');
		$links[] = array('text' => $_LANG['add_new'], 'href' => 'merchants_navigator.php?act=add');
		sys_msg($_LANG['edit_ok'], 0, $links);
	}
}
else if ($_REQUEST['act'] == 'edit') {
	$smarty->assign('primary_cat', $_LANG['19_merchants_store']);
	$id = $_REQUEST['id'];

	if (empty($_REQUEST['step'])) {
		$rt = array('act' => 'edit', 'id' => $id);
		$row = $db->getRow('SELECT * FROM ' . $GLOBALS['ecs']->table('merchants_nav') . ' WHERE id=\'' . $id . '\'');
		$rt['item_name'] = $row['name'];
		$rt['item_url'] = $row['url'];
		$rt['item_vieworder'] = $row['vieworder'];
		$rt['item_ifshow_' . $row['ifshow']] = 'checked="true"';
		$rt['item_opennew_' . $row['opennew']] = 'checked="true"';
		$rt['item_type_' . $row['type']] = 'selected';
		$rt['item_catId'] = $row['cat_id'];
		$sysmain = get_sysnav();
		$smarty->assign('action_link', array('text' => $_LANG['go_list'], 'href' => 'merchants_navigator.php?act=list', 'class' => 'icon-reply'));
		$smarty->assign('ur_here', $_LANG['navigator']);
		assign_query_info();
		$smarty->assign('sysmain', $sysmain);
		$smarty->assign('rt', $rt);
		$smarty->assign('current', 'merchants_navigator');
		$smarty->display('store_navigation_add.dwt');
	}
	else if ($_REQUEST['step'] == 2) {
		$item_name = $_REQUEST['item_name'];
		$item_url = $_REQUEST['item_url'];
		$item_ifshow = $_REQUEST['item_ifshow'];
		$item_opennew = $_REQUEST['item_opennew'];
		$item_type = $_REQUEST['item_type'];
		$item_vieworder = (int) $_REQUEST['item_vieworder'];
		$item_catId = trim($_REQUEST['item_catId']);
		$item_catId = intval($item_catId);
		$row = $db->getRow('SELECT ctype,cid,ifshow,type FROM ' . $GLOBALS['ecs']->table('merchants_nav') . ' WHERE id = \'' . $id . '\'');
		$arr = analyse_uri($item_url);

		if ($arr) {
			if (($row['ctype'] == $arr['type']) && ($row['cid'] == $arr['id'])) {
				if ($item_type != 'middle') {
					set_show_in_nav($arr['type'], $arr['id'], 0);
				}
			}
			else {
				if (($row['ifshow'] == 1) && ($row['type'] == 'middle')) {
					set_show_in_nav($row['ctype'], $row['cid'], 0);
				}
				else {
					if (($row['ifshow'] == 0) && ($row['type'] == 'middle')) {
					}
				}
			}

			if (($item_ifshow != is_show_in_nav($arr['type'], $arr['id'])) && ($item_type == 'middle')) {
				set_show_in_nav($arr['type'], $arr['id'], $item_ifshow);
			}

			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('merchants_nav') . ' SET name=\'' . $item_name . '\',ctype=\'' . $arr['type'] . '\',cid=\'' . $arr['id'] . '\',ifshow=\'' . $item_ifshow . '\',vieworder=\'' . $item_vieworder . '\',opennew=\'' . $item_opennew . '\',url=\'' . $item_url . '\',type=\'' . $item_type . '\' WHERE id=\'' . $id . '\'';
		}
		else {
			if ($row['ctype'] && $row['cid']) {
				set_show_in_nav($row['ctype'], $row['cid'], 0);
			}

			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('merchants_nav') . ' SET name=\'' . $item_name . '\',ctype=\'\',cid=\'\',ifshow=\'' . $item_ifshow . '\',cat_id=\'' . $item_catId . '\',vieworder=\'' . $item_vieworder . '\',opennew=\'' . $item_opennew . '\',url=\'' . $item_url . '\',type=\'' . $item_type . '\' WHERE id=\'' . $id . '\'';
		}

		$db->query($sql);
		clear_cache_files();
		$links[] = array('text' => $_LANG['navigator'], 'href' => 'merchants_navigator.php?act=list');
		sys_msg($_LANG['edit_ok'], 0, $links);
	}
}
else if ($_REQUEST['act'] == 'del') {
	$id = (int) $_GET['id'];
	$row = $db->getRow('SELECT ctype,cid,type FROM ' . $GLOBALS['ecs']->table('merchants_nav') . ' WHERE id = \'' . $id . '\' LIMIT 1');
	if (($row['type'] == 'middle') && $row['ctype'] && $row['cid']) {
		set_show_in_nav($row['ctype'], $row['cid'], 0);
	}

	$sql = ' DELETE FROM ' . $GLOBALS['ecs']->table('merchants_nav') . ' WHERE id=\'' . $id . '\' LIMIT 1';
	$db->query($sql);
	clear_cache_files();
	ecs_header("Location: merchants_navigator.php?act=list\n");
	exit();
}
else if ($_REQUEST['act'] == 'edit_sort_order') {
	$id = intval($_POST['id']);
	$order = json_str_iconv(trim($_POST['val']));

	if (!preg_match('/^[0-9]+$/', $order)) {
		make_json_error(sprintf($_LANG['enter_int'], $order));
	}
	else if ($exc->edit('vieworder = \'' . $order . '\'', $id)) {
		clear_cache_files();
		make_json_result(stripslashes($order));
	}
	else {
		make_json_error($db->error());
	}
}

if ($_REQUEST['act'] == 'toggle_ifshow') {
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$row = $db->getRow('SELECT type,ctype,cid FROM ' . $GLOBALS['ecs']->table('merchants_nav') . ' WHERE id = \'' . $id . '\' LIMIT 1');
	if (($row['type'] == 'middle') && $row['ctype'] && $row['cid']) {
		set_show_in_nav($row['ctype'], $row['cid'], $val);
	}

	if (nav_update($id, array('ifshow' => $val)) != false) {
		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}

if ($_REQUEST['act'] == 'toggle_opennew') {
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);

	if (nav_update($id, array('opennew' => $val)) != false) {
		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}

?>
