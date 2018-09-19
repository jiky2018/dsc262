<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function zc_cat_update($cat_id, $args)
{
	if (empty($args) || empty($cat_id)) {
		return false;
	}

	return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('zc_category'), $args, 'update', 'cat_id=\'' . $cat_id . '\'');
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('category'), $db, 'cat_id', 'cat_name');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('zc_category_manage');
	$parent_id = (!isset($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']));
	if (isset($_REQUEST['back_level']) && (0 < $_REQUEST['back_level'])) {
		$level = $_REQUEST['back_level'] - 1;
		$parent_id = $db->getOne('SELECT parent_id FROM ' . $ecs->table('zc_category') . ' WHERE cat_id = \'' . $parent_id . '\'', true);
	}
	else {
		$level = (isset($_REQUEST['level']) ? $_REQUEST['level'] + 1 : 0);
	}

	$smarty->assign('level', $level);
	$smarty->assign('parent_id', $parent_id);
	$cat_list = get_cat_level($parent_id, $level, 'zc_category');
	$smarty->assign('ur_here', $_LANG['02_crowdfunding_cat']);
	$smarty->assign('action_link', array('href' => 'zc_category.php?act=add', 'text' => $_LANG['add_zc_category']));
	$smarty->assign('full_page', 1);
	$smarty->assign('cat_info', $cat_list);
	assign_query_info();
	$smarty->display('zc_category_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$parent_id = (empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']));
	$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('zc_category') . ' WHERE parent_id = \'' . $parent_id . '\' ORDER BY sort_order, cat_id ';
	$cat_list = $GLOBALS['db']->getAll($sql);
	$smarty->assign('cat_info', $cat_list);
	make_json_result($smarty->fetch('zc_category_list.dwt'));
}

if ($_REQUEST['act'] == 'add') {
	admin_priv('zc_category_manage');
	$parent_id = (empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']));

	if (!empty($parent_id)) {
		set_default_filter(0, $parent_id, 0, 0, 'zc_category');
		$smarty->assign('parent_category', get_every_category($parent_id, 'zc_category'));
		$smarty->assign('parent_id', $parent_id);
	}
	else {
		set_default_filter(0, 0, 0, 0, 'zc_category');
	}

	$smarty->assign('ur_here', $_LANG['add_zc_category']);
	$smarty->assign('action_link', array('href' => 'zc_category.php?act=list', 'text' => $_LANG['02_crowdfunding_cat']));
	$smarty->assign('form_act', 'insert');
	$smarty->assign('cat_info', array('is_show' => 1, 'is_group_show' => 0, 'is_search_show' => 0, 'is_search_show_layout' => 1));
	$smarty->assign('table', 'zc_category');
	assign_query_info();
	$smarty->display('zc_category_info.dwt');
}

if ($_REQUEST['act'] == 'insert') {
	admin_priv('zc_category_manage');
	$parent_id = (empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']));
	$cat['cat_id'] = !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
	$cat['parent_id'] = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
	$cat['sort_order'] = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
	$cat['cat_desc'] = !empty($_POST['cat_desc']) ? $_POST['cat_desc'] : '';
	$cat['cat_name'] = !empty($_POST['cat_name']) ? trim($_POST['cat_name']) : '';
	$cat['cat_recommend'] = !empty($_POST['cat_recommend']) ? $_POST['cat_recommend'] : array();

	if (cat_exists($cat['cat_name'], $cat['parent_id'])) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['catname_exist'], 0, $link);
	}

	if ($db->autoExecute($ecs->table('zc_category'), $cat) !== false) {
		$cat_id = $db->insert_id();
		admin_log($_POST['cat_name'], 'add', 'zc_category');
		clear_cache_files();
		$link[0]['text'] = $_LANG['continue_add'];
		$link[0]['href'] = 'zc_category.php?act=add&parent_id=' . $parent_id;
		$link[1]['text'] = $_LANG['go_list'];
		$link[1]['href'] = 'zc_category.php?act=list&parent_id=' . $parent_id;
		sys_msg('分类添加成功', 0, $link);
	}
}

if ($_REQUEST['act'] == 'edit') {
	admin_priv('zc_category_manage');
	$cat_id = intval($_REQUEST['cat_id']);
	$cat_info = get_cat_info($cat_id, array(), 'zc_category');
	$smarty->assign('parent_id', $cat_info['parent_id']);
	$smarty->assign('parent_category', get_every_category($cat_info['parent_id'], 'zc_category'));
	set_default_filter(0, $cat_info['parent_id'], 0, 0, 'zc_category');
	$smarty->assign('table', 'zc_category');
	$smarty->assign('ur_here', $_LANG['edit_zc_category']);
	$smarty->assign('action_link', array('text' => $_LANG['02_crowdfunding_cat'], 'href' => 'zc_category.php?act=list'));
	$smarty->assign('cat_info', $cat_info);
	$smarty->assign('form_act', 'update');
	assign_query_info();
	$smarty->display('zc_category_info.dwt');
}

if ($_REQUEST['act'] == 'update') {
	admin_priv('zc_category_manage');
	$cat_id = (!empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0);
	$cat['parent_id'] = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
	$cat['sort_order'] = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
	$cat['cat_desc'] = !empty($_POST['cat_desc']) ? $_POST['cat_desc'] : '';
	$cat['cat_name'] = !empty($_POST['cat_name']) ? trim($_POST['cat_name']) : '';
	$cat['cat_recommend'] = !empty($_POST['cat_recommend']) ? $_POST['cat_recommend'] : array();

	if ($cat['cat_name'] != $old_cat_name) {
		if (cat_exists($cat['cat_name'], $cat['parent_id'], $cat_id)) {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
			sys_msg($_LANG['catname_exist'], 0, $link);
		}
	}

	$children = get_array_keys_cat($cat_id, 0, 'zc_category');

	if (in_array($cat['parent_id'], $children)) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['prev_category_wrong'], 0, $link);
	}

	if ($db->autoExecute($ecs->table('zc_category'), $cat, 'UPDATE', 'cat_id=\'' . $cat_id . '\'')) {
		clear_cache_files();
		admin_log($_POST['cat_name'], 'edit', 'zc_category');
		$link[] = array('text' => $_LANG['go_list'], 'href' => 'zc_category.php?act=list');
		sys_msg($_LANG['edit_success'], 0, $link);
	}
}

if ($_REQUEST['act'] == 'remove') {
	check_authz_json('zc_category_manage');
	$cat_id = intval($_GET['id']);
	$cat_name = $db->getOne('SELECT cat_name FROM ' . $ecs->table('zc_category') . ' WHERE cat_id=\'' . $cat_id . '\'');
	$cat_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('zc_category') . ' WHERE parent_id=\'' . $cat_id . '\'');
	$goods_count = 0;
	$goods_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('zc_project') . ' WHERE cat_id=\'' . $cat_id . '\'');
	if (($cat_count == 0) && ($goods_count == 0)) {
		$sql = 'DELETE FROM ' . $ecs->table('zc_category') . ' WHERE cat_id = \'' . $cat_id . '\'';

		if ($db->query($sql)) {
			clear_cache_files();
			admin_log($cat_name, 'remove', 'zc_category');
		}
	}
	else {
		make_json_error($cat_name . ' ' . $_LANG['cat_isleaf']);
	}

	$url = 'zc_category.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('zc_category_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);

	if (zc_cat_update($id, array('sort_order' => $val))) {
		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}

?>
