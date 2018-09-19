<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_floor_content($curr_template, $filename, $id = 0, $region = '')
{
	$where = ' where 1 ';

	if (!empty($id)) {
		$where .= ' and id=\'' . $id . '\'';
	}

	if (!empty($region)) {
		$where .= ' and region=\'' . $region . '\'';
	}

	$sql = 'select * from ' . $GLOBALS['ecs']->table('floor_content') . $where . ' and filename=\'' . $filename . '\' and theme=\'' . $curr_template . '\'';
	$row = $GLOBALS['db']->getAll($sql);
	return $row;
}

function get_floors($curr_template, $filename)
{
	$sql = 'select * from ' . $GLOBALS['ecs']->table('floor_content') . ' where filename=\'' . $filename . '\' and theme=\'' . $curr_template . '\' group by filename,theme,id';
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$row[$key]['brand_list'] = $GLOBALS['db']->getAll('select b.brand_id, b.brand_name from ' . $GLOBALS['ecs']->table('brand') . ' AS b, ' . $GLOBALS['ecs']->table('floor_content') . ' AS fc ' . ' where fc.filename = \'' . $val['filename'] . '\' AND theme = \'' . $val['theme'] . '\' AND id = \'' . $val['id'] . '\' AND id = \'' . $val['id'] . '\' AND region = \'' . $val['region'] . '\' AND b.brand_id = fc.brand_id');
		$row[$key]['cat_name'] = $GLOBALS['db']->getOne('select cat_name from ' . $GLOBALS['ecs']->table('category') . ' where cat_id=\'' . $val['id'] . '\' limit 1');
	}

	return $row;
}

function get_template($curr_template, $filename, $region)
{
	$sql = 'select region,id from ' . $GLOBALS['ecs']->table('template') . ' where filename=\'' . $filename . '\' and theme=\'' . $curr_template . '\' and region=\'' . $region . '\'';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key] = $row;
		$arr[$key]['filename'] = $filename;
		$arr[$key]['cat_name'] = $GLOBALS['db']->getOne('select cat_name from ' . $GLOBALS['ecs']->table('category') . ' where cat_id = \'' . $row['id'] . '\' limit 1');
	}

	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once 'includes/lib_template.php';
require_once ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php';
$act = (empty($_REQUEST['act']) ? 'list' : trim($_REQUEST['act']));

if ($act == 'list') {
	admin_priv('template_select');
	$filename = (empty($_REQUEST['filename']) ? 'index' : trim($_REQUEST['filename']));
	$smarty->assign('ur_here', $_LANG['floor_content_list']);
	$smarty->assign('action_link', array('text' => $_LANG['floor_content_add'], 'href' => 'set_floor_brand.php?act=add&filename=' . $filename));
	$curr_template = $_CFG['template'];
	$floor_content = get_floors($curr_template, $filename);
	$smarty->assign('floor_content', $floor_content);
	$smarty->assign('full_page', 1);
	$smarty->display('floor_content_list.dwt');
}
else if ($act == 'add') {
	admin_priv('template_select');
	set_default_filter($goods_id);
	$filename = (empty($_REQUEST['filename']) ? 'index' : trim($_REQUEST['filename']));
	$smarty->assign('action_link', array('text' => $_LANG['floor_content_list'], 'href' => 'set_floor_brand.php?act=list&filename=' . $filename));
	$curr_template = $_CFG['template'];
	$template = get_template($curr_template, $filename, '首页楼层');
	$smarty->assign('filename', $filename);
	$smarty->assign('template', $template);
	set_default_filter($goods_id);
	$smarty->assign('brand_list', search_brand_list());
	$smarty->assign('ur_here', $_LANG['set_floor']);
	$smarty->display('floor_content_add.dwt');
}
else if ($act == 'edit') {
	admin_priv('template_select');
	set_default_filter($goods_id);
	$filename = (!empty($_GET['filename']) ? trim($_GET['filename']) : '');
	$theme = (!empty($_GET['theme']) ? trim($_GET['theme']) : '');
	$region = (!empty($_GET['region']) ? trim($_GET['region']) : '');
	$cat_id = (!empty($_GET['id']) ? intval($_GET['id']) : 0);
	$smarty->assign('action_link', array('text' => $_LANG['floor_content_list'], 'href' => 'set_floor_brand.php?act=list'));
	$floor_content = get_floor_content($theme, $filename, $cat_id, $region);
	$template = get_template($theme, $filename, '首页楼层');
	$smarty->assign('filename', $filename);
	$smarty->assign('template', $template);
	$smarty->assign('floor_content', $floor_content);
	$smarty->assign('cat_id', $cat_id);
	set_default_filter($goods_id);
	$smarty->assign('brand_list', search_brand_list());
	$smarty->assign('ur_here', $_LANG['set_floor']);
	$smarty->display('floor_content_add.dwt');
}
else if ($act == 'remove') {
	$filename = (!empty($_GET['filename']) ? trim($_GET['filename']) : 0);
	$theme = (!empty($_GET['theme']) ? trim($_GET['theme']) : 0);
	$region = (!empty($_GET['region']) ? trim($_GET['region']) : '');
	$cat_id = (!empty($_GET['id']) ? intval($_GET['id']) : 0);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('floor_content') . ' WHERE filename = \'' . $filename . '\' AND theme = \'' . $theme . '\' AND id =  \'' . $cat_id . '\' AND region = \'' . $region . '\'';
	$GLOBALS['db']->query($sql);
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'set_floor_brand.php?filename=index');
	sys_msg($_LANG['remove_success'], 0, $link);
}

?>
