<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function cat_update($cat_id, $args)
{
	if (empty($args) || empty($cat_id)) {
		return false;
	}

	return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_lib_cat'), $args, 'update', 'cat_id=\'' . $cat_id . '\'');
}

function lib_get_cat_level($parent_id = 0, $level = 0)
{
	$sql = 'SELECT glc.cat_id, glc.cat_name,glc.is_show ,glc.sort_order , glc.parent_id ' . ' FROM ' . $GLOBALS['ecs']->table('goods_lib_cat') . ' AS glc WHERE glc.parent_id = \'' . $parent_id . '\' ' . ' order by glc.sort_order, glc.cat_id';
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $k => $row) {
		$cat_id_str = lib_get_class_nav($res[$k]['cat_id'], 'goods_lib_cat');
		$res[$k]['cat_child'] = substr($cat_id_str['catId'], 0, -1);

		if (empty($cat_id_str['catId'])) {
			$res[$k]['cat_child'] = substr($res[$k]['cat_id'], 0, -1);
		}

		$res[$k]['cat_child'] = isset($res[$k]['cat_child']) && !empty($res[$k]['cat_child']) ? get_del_str_comma($res[$k]['cat_child']) : '';

		if ($res[$k]['cat_child']) {
			$cat_in = ' AND g.lib_cat_id in(' . $res[$k]['cat_child'] . ')';
		}
		else {
			$cat_in = '';
		}

		$goodsNums = $GLOBALS['db']->getAll('SELECT g.goods_id FROM ' . $GLOBALS['ecs']->table('goods_lib') . ' AS g ' . ' WHERE 1 ' . $cat_in . $ruCat);
		$goods_ids = array();

		foreach ($goodsNums as $num_key => $num_val) {
			$goods_ids[] = $num_val['goods_id'];
		}

		$res[$k]['goods_num'] = count($goodsNums);
		$res[$k]['goodsNum'] = $goodsNum;
		$res[$k]['level'] = $level;
	}

	return $res;
}

function lib_get_class_nav($cat_id, $table = 'goods_lib_cat')
{
	$sql = 'select cat_id,cat_name,parent_id from ' . $GLOBALS['ecs']->table($table) . ' where cat_id = \'' . $cat_id . '\'';
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $key => $row) {
		$arr[$key]['cat_id'] = $row['cat_id'];
		$arr[$key]['cat_name'] = $row['cat_name'];
		$arr[$key]['parent_id'] = $row['parent_id'];
		$arr['catId'] .= $row['cat_id'] . ',';
		$arr[$key]['child'] = lib_get_parent_child($row['cat_id'], $table);

		if (empty($arr[$key]['child']['catId'])) {
			$arr['catId'] = $arr['catId'];
		}
		else {
			$arr['catId'] .= $arr[$key]['child']['catId'];
		}
	}

	return $arr;
}

function lib_get_parent_child($parent_id = 0, $table = 'goods_lib_cat')
{
	$sql = 'select cat_id,cat_name,parent_id from ' . $GLOBALS['ecs']->table($table) . ' where parent_id = \'' . $parent_id . '\'';
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $key => $row) {
		$arr[$key]['cat_id'] = $row['cat_id'];
		$arr[$key]['cat_name'] = $row['cat_name'];
		$arr[$key]['parent_id'] = $row['parent_id'];
		$arr['catId'] .= $row['cat_id'] . ',';
		$arr[$key]['child'] = lib_get_parent_child($row['cat_id']);
		$arr['catId'] .= $arr[$key]['child']['catId'];
	}

	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('goods_lib_cat'), $db, 'cat_id', 'cat_name');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$adminru = get_admin_ru_id();
$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => '03_category_list'));

if ($_REQUEST['act'] == 'list') {
	admin_priv('goods_lib_cat');
	$parent_id = (!isset($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']));
	if (isset($_REQUEST['back_level']) && (0 < $_REQUEST['back_level'])) {
		$level = $_REQUEST['back_level'] - 1;
		$parent_id = $db->getOne('SELECT parent_id FROM ' . $ecs->table('goods_lib_cat') . ' WHERE cat_id = \'' . $parent_id . '\'', true);
	}
	else {
		$level = (isset($_REQUEST['level']) ? $_REQUEST['level'] + 1 : 0);
	}

	$smarty->assign('level', $level);
	$smarty->assign('parent_id', $parent_id);
	$cat_list = lib_get_cat_level($parent_id, $level);
	$smarty->assign('cat_info', $cat_list);
	$smarty->assign('ru_id', $adminru['ru_id']);

	if ($adminru['ru_id'] == 0) {
		$smarty->assign('action_link', array('href' => 'goods_lib_cat.php?act=add', 'text' => $_LANG['04_category_add']));
	}

	$smarty->assign('ur_here', $_LANG['21_goods_lib_cat']);
	$smarty->assign('full_page', 1);
	$cat_level = array('一', '二', '三', '四', '五', '六', '气', '八', '九', '十');
	$smarty->assign('cat_level', $cat_level[$level]);
	assign_query_info();
	$smarty->display('goods_lib_cat_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$cat_list = lib_get_cat_level();
	$smarty->assign('cat_info', $cat_list);
	$smarty->assign('ru_id', $adminru['ru_id']);
	make_json_result($smarty->fetch('goods_lib_cat_list.dwt'));
}

if ($_REQUEST['act'] == 'add') {
	admin_priv('goods_lib_cat');
	$parent_id = (empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']));

	if (!empty($parent_id)) {
		set_default_filter(0, $parent_id, 0, 0, 'goods_lib_cat');
		$smarty->assign('parent_category', get_every_category($parent_id, 'goods_lib_cat'));
		$smarty->assign('parent_id', $parent_id);
	}
	else {
		set_default_filter(0, 0, 0, 0, 'goods_lib_cat');
	}

	$smarty->assign('ur_here', $_LANG['04_category_add']);
	$smarty->assign('action_link', array('href' => 'goods_lib_cat.php?act=list', 'text' => $_LANG['03_category_list']));
	$smarty->assign('form_act', 'insert');
	$smarty->assign('cat_info', array('is_show' => 1));
	$smarty->assign('ru_id', $adminru['ru_id']);
	$smarty->assign('lib', 'lib');
	assign_query_info();
	$smarty->display('goods_lib_cat_info.dwt');
}

if ($_REQUEST['act'] == 'insert') {
	admin_priv('goods_lib_cat');
	$cat['cat_id'] = !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
	$cat['parent_id'] = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
	$cat['level'] = count(get_select_category($cat['parent_id'], 1, true)) - 2;
	if ((1 < $cat['level']) && ($adminru['ru_id'] == 0)) {
		$link[0]['text'] = $_LANG['go_back'];

		if (0 < $cat['cat_id']) {
			$link[0]['href'] = 'goods_lib_cat.php?act=edit&cat_id=' . $cat['cat_id'];
		}
		else {
			$link[0]['href'] = 'goods_lib_cat.php?act=add&parent_id=' . $cat['parent_id'];
		}

		sys_msg('平台最多只能设置三级分类', 0, $link);
		exit();
	}

	$cat['sort_order'] = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
	$cat['cat_name'] = !empty($_POST['cat_name']) ? trim($_POST['cat_name']) : '';
	$cat['is_show'] = !empty($_POST['is_show']) ? intval($_POST['is_show']) : 0;

	if (cat_exists($cat['cat_name'], $cat['parent_id'])) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['catname_exist'], 0, $link);
	}

	if ($db->autoExecute($ecs->table('goods_lib_cat'), $cat) !== false) {
		$cat_id = $db->insert_id();
		admin_log($_POST['cat_name'], 'add', 'goods_lib_cat');
		clear_cache_files();
		$link[0]['text'] = $_LANG['continue_add'];
		$link[0]['href'] = 'goods_lib_cat.php?act=add&parent_id=' . $cat['parent_id'];
		$link[1]['text'] = $_LANG['back_list'];
		$link[1]['href'] = 'goods_lib_cat.php?act=list&parent_id=' . $cat['parent_id'] . '&level=' . $cat['level'];
		sys_msg($_LANG['catadd_succed'], 0, $link);
	}
}

if ($_REQUEST['act'] == 'edit') {
	admin_priv('goods_lib_cat');
	$cat_id = intval($_REQUEST['cat_id']);
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods_lib_cat') . ' WHERE cat_id = \'' . $cat_id . '\' LIMIT 1';
	$cat_info = $GLOBALS['db']->getRow($sql);
	$smarty->assign('parent_id', $cat_info['parent_id']);
	$smarty->assign('parent_category', get_every_category($cat_info['parent_id'], 'goods_lib_cat'));
	set_default_filter(0, $cat_info['parent_id'], 0, 0, 'goods_lib_cat');
	$smarty->assign('ur_here', $_LANG['category_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['03_category_list'], 'href' => 'goods_lib_cat.php?act=list'));
	$smarty->assign('cat_id', $cat_id);
	$smarty->assign('ru_id', $adminru['ru_id']);
	$smarty->assign('cat_info', $cat_info);
	$smarty->assign('form_act', 'update');
	$smarty->assign('lib', 'lib');
	assign_query_info();
	$smarty->display('goods_lib_cat_info.dwt');
}

if ($_REQUEST['act'] == 'update') {
	admin_priv('goods_lib_cat');
	$cat_id = $cat['cat_id'] = !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
	$cat['parent_id'] = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
	$cat['level'] = count(get_select_category($cat['parent_id'], 1, true)) - 2;
	$old_cat_name = (isset($_REQUEST['old_cat_name']) ? $_REQUEST['old_cat_name'] : '');
	if ((1 < $cat['level']) && ($adminru['ru_id'] == 0)) {
		$link[0]['text'] = $_LANG['go_back'];

		if (0 < $cat['cat_id']) {
			$link[0]['href'] = 'goods_lib_cat.php?act=edit&cat_id=' . $cat['cat_id'];
		}
		else {
			$link[0]['href'] = 'goods_lib_cat.php?act=add&parent_id=' . $cat['parent_id'];
		}

		sys_msg('平台最多只能设置三级分类', 0, $link);
		exit();
	}

	$cat['sort_order'] = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
	$cat['cat_name'] = !empty($_POST['cat_name']) ? trim($_POST['cat_name']) : '';
	$cat['is_show'] = !empty($_POST['is_show']) ? intval($_POST['is_show']) : 0;

	if ($cat['cat_name'] != $old_cat_name) {
		if (cat_exists($cat['cat_name'], $cat['parent_id'], $cat_id)) {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
			sys_msg($_LANG['catname_exist'], 0, $link);
		}
	}

	$children = get_array_keys_cat($cat_id);

	if (in_array($cat['parent_id'], $children)) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['is_leaf_error'], 0, $link);
	}

	if ($db->autoExecute($ecs->table('goods_lib_cat'), $cat, 'UPDATE', 'cat_id=\'' . $cat_id . '\'')) {
		clear_cache_files();
		admin_log($_POST['cat_name'], 'edit', 'goods_lib_cat');
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'goods_lib_cat.php?act=list&parent_id=' . $cat['parent_id'] . '&level=' . $cat['level']);
		sys_msg($_LANG['catedit_succed'], 0, $link);
	}
}

if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('goods_lib_cat');
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

if ($_REQUEST['act'] == 'toggle_is_show') {
	check_authz_json('goods_lib_cat');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);

	if (cat_update($id, array('is_show' => $val)) != false) {
		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('goods_lib_cat');
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => 0, 'massege' => '', 'level' => '');
	$result['level'] = $_REQUEST['level'];
	$cat_id = intval($_GET['cat_id']);
	$result['cat_id'] = $cat_id;
	$cat_name = $db->getOne('SELECT cat_name FROM ' . $ecs->table('goods_lib_cat') . ' WHERE cat_id=\'' . $cat_id . '\'');
	$cat_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('goods_lib_cat') . ' WHERE parent_id=\'' . $cat_id . '\'');
	$goods_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('goods_lib') . ' WHERE cat_id=\'' . $cat_id . '\'');
	if (($cat_count == 0) && ($goods_count == 0)) {
		$sql = 'DELETE FROM ' . $ecs->table('goods_lib_cat') . ' WHERE cat_id = \'' . $cat_id . '\'';

		if ($db->query($sql)) {
			clear_cache_files();
			admin_log($cat_name, 'remove', 'goods_lib_cat');
			$result['error'] = 1;
		}
	}
	else {
		$result['error'] = 2;
		$result['massege'] = $cat_name . ' ' . $_LANG['cat_isleaf'];
	}

	exit($json->encode($result));
}

?>
