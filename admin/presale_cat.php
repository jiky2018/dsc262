<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function presale_cat_exists($cat_name, $parent_cat, $exclude = 0)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('presale_cat') . ' WHERE parent_id = \'' . $parent_cat . '\' AND cat_name = \'' . $cat_name . '\' AND cat_id <> \'' . $exclude . '\'';
	return 0 < $GLOBALS['db']->getOne($sql) ? true : false;
}

function cat_update($cat_id, $args)
{
	if (empty($args) || empty($cat_id)) {
		return false;
	}

	return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('presale_cat'), $args, 'update', 'cat_id=\'' . $cat_id . '\'');
}

function cname_exists($cat_name, $parent_cat, $exclude = 0)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('presale_cat') . ' WHERE parent_id = \'' . $parent_cat . '\' AND cat_name = \'' . $cat_name . '\' AND cat_id <> \'' . $exclude . '\'';
	return 0 < $GLOBALS['db']->getOne($sql) ? true : false;
}

function presale_child_cat($pid)
{
	$sql = ' SELECT cat_id, cat_name, parent_id, sort_order FROM ' . $GLOBALS['ecs']->table('presale_cat') . ' WHERE parent_id = \'' . $pid . '\' ';
	return $GLOBALS['db']->getAll($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('presale_cat'), $db, 'cat_id', 'cat_name');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if ($_REQUEST['act'] == 'list') {
	$parent_id = (isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0);

	if ($parent_id) {
		$cat_list = presale_child_cat($parent_id);
	}
	else {
		$cat_list = presale_cat_list(0, 0, false, 0, true, 'admin');
	}

	$adminru = get_admin_ru_id();
	$smarty->assign('ru_id', $adminru['ru_id']);

	if ($adminru['ru_id'] == 0) {
		$smarty->assign('action_link', array('href' => 'presale_cat.php?act=add', 'text' => $_LANG['add_presale_cat']));
	}

	$smarty->assign('ur_here', $_LANG['presale_cat']);
	$smarty->assign('full_page', 1);
	$smarty->assign('cat_info', $cat_list);
	assign_query_info();
	$smarty->display('presale_cat_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$cat_list = presale_cat_list(0, 0, false);
	$smarty->assign('cat_info', $cat_list);
	$adminru = get_admin_ru_id();
	$smarty->assign('ru_id', $adminru['ru_id']);
	make_json_result($smarty->fetch('presale_cat_list.dwt'));
}

if ($_REQUEST['act'] == 'add') {
	admin_priv('cat_manage');
	$smarty->assign('ur_here', $_LANG['add_presale_cat']);
	$smarty->assign('action_link', array('href' => 'presale_cat.php?act=list', 'text' => $_LANG['presale_cat_list']));
	$parent_id = (isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0);
	$cat_select = presale_cat_list(0, 0, false, 0, true, '', 1);

	foreach ($cat_select as $k => $v) {
		if ($v['level']) {
			$level = str_repeat('&nbsp;', $v['level'] * 4);
			$cat_select[$k]['name'] = $level . $v['name'];
		}
	}

	$smarty->assign('cat_select', $cat_select);
	$smarty->assign('form_act', 'insert');
	$smarty->assign('cat_info', array('is_show' => 1, 'parent_id' => $parent_id));
	$adminru = get_admin_ru_id();
	$smarty->assign('ru_id', $adminru['ru_id']);
	assign_query_info();
	$smarty->display('presale_cat_info.dwt');
}

if ($_REQUEST['act'] == 'insert') {
	admin_priv('cat_manage');
	$cat['parent_id'] = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
	$cat['sort_order'] = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
	$cat['cat_name'] = !empty($_POST['cat_name']) ? trim($_POST['cat_name']) : '';

	if (cname_exists($cat['cat_name'], $cat['parent_id'])) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['catname_exist'], 0, $link);
	}

	if ($db->autoExecute($ecs->table('presale_cat'), $cat) !== false) {
		$cat_id = $db->insert_id();
		admin_log($_POST['cat_name'], 'add', 'presale_cat');
		clear_cache_files();
		$link[0]['text'] = $_LANG['continue_add'];
		$link[0]['href'] = 'presale_cat.php?act=add';
		$link[1]['text'] = $_LANG['back_list'];
		$link[1]['href'] = 'presale_cat.php?act=list';
		sys_msg($_LANG['catadd_succed'], 0, $link);
	}
}

if ($_REQUEST['act'] == 'edit') {
	admin_priv('cat_manage');
	$cat_id = intval($_REQUEST['cat_id']);
	$cat_info = get_cat_info($cat_id, array(), 'presale_cat');
	$smarty->assign('ur_here', $_LANG['category_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['presale_cat_list'], 'href' => 'presale_cat.php?act=list'));
	$smarty->assign('cat_id', $cat_id);
	$adminru = get_admin_ru_id();
	$smarty->assign('ru_id', $adminru['ru_id']);
	$smarty->assign('cat_info', $cat_info);
	$smarty->assign('form_act', 'update');
	$cat_select = presale_cat_list(0, $cat_info['parent_id'], false, 0, true, '', 1);

	foreach ($cat_select as $k => $v) {
		if ($v['level']) {
			$level = str_repeat('&nbsp;', $v['level'] * 4);
			$cat_select[$k]['name'] = $level . $v['name'];
		}
	}

	$smarty->assign('cat_select', $cat_select);
	assign_query_info();
	$smarty->display('presale_cat_info.dwt');
}

if ($_REQUEST['act'] == 'update') {
	admin_priv('cat_manage');
	$cat_id = (!empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0);
	$old_cat_name = $_POST['old_cat_name'];
	$cat['parent_id'] = isset($_POST['parent_id']) ? trim($_POST['parent_id']) : 0;
	$cat['sort_order'] = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
	$cat['cat_name'] = !empty($_POST['cat_name']) ? trim($_POST['cat_name']) : '';
	$adminru = get_admin_ru_id();

	if ($cat['cat_name'] != $old_cat_name) {
		if (presale_cat_exists($cat['cat_name'], $cat['parent_id'], $cat_id)) {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
			sys_msg($_LANG['catname_exist'], 0, $link);
		}
	}

	$dat = $db->getRow('SELECT cat_name FROM ' . $ecs->table('presale_cat') . ' WHERE cat_id = \'' . $cat_id . '\'');

	if ($db->autoExecute($ecs->table('presale_cat'), $cat, 'UPDATE', 'cat_id = \'' . $cat_id . '\'')) {
		clear_cache_files();
		admin_log($_POST['cat_name'], 'edit', 'presale_cat');
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'presale_cat.php?act=list');
		sys_msg($_LANG['catedit_succed'], 0, $link);
	}
}

if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('cat_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);

	if (cat_update($id, array('sort_order' => $val))) {
		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}

if ($_REQUEST['act'] == 'remove') {
	check_authz_json('cat_manage');
	$cat_id = intval($_GET['id']);
	$cat_name = $db->getOne('SELECT cat_name FROM ' . $ecs->table('presale_cat') . ' WHERE cat_id = \'' . $cat_id . '\'');
	$cat_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('presale_cat') . ' WHERE parent_id = \'' . $cat_id . '\'');
	$goods_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('presale_activity') . ' WHERE cat_id = \'' . $cat_id . '\'');
	if (($cat_count == 0) && ($goods_count == 0)) {
		$sql = 'DELETE FROM ' . $ecs->table('presale_cat') . ' WHERE cat_id = \'' . $cat_id . '\'';

		if ($db->query($sql)) {
			clear_cache_files();
			admin_log($cat_name, 'remove', 'presale_cat');
		}
	}
	else {
		make_json_error($cat_name . ' ' . $_LANG['cat_isleaf']);
	}

	$url = 'presale_cat.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

?>
