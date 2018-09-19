<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function new_region_id($region_id)
{
	$regions_id = array();

	if (empty($region_id)) {
		return $regions_id;
	}

	$sql = 'SELECT region_id FROM ' . $GLOBALS['ecs']->table('region') . 'WHERE parent_id ' . db_create_in($region_id);
	$result = $GLOBALS['db']->getAll($sql);

	foreach ($result as $val) {
		$regions_id[] = $val['region_id'];
	}

	return $regions_id;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('region'), $db, 'region_id', 'region_name');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('area_list');
	$smarty->assign('menu_select', array('action' => '01_system', 'current' => '05_area_list'));
	$region_id = empty($_REQUEST['pid']) ? 0 : intval($_REQUEST['pid']);
	$smarty->assign('parent_id', $region_id);

	if ($region_id == 0) {
		$region_type = 0;
	}
	else {
		$region_type = $exc->get_name($region_id, 'region_type') + 1;
	}

	$smarty->assign('region_type', $region_type);
	$region_arr = area_list($region_id);
	$smarty->assign('region_arr', $region_arr);
	$area_top = '-';

	if (0 < $region_id) {
		$area_name = $exc->get_name($region_id);
		$area_top = $area_name;

		if ($region_arr) {
			$area = $region_arr[0]['type'];
		}
	}
	else {
		$area = $_LANG['country'];
	}

	$smarty->assign('area_top', $area_top);
	$smarty->assign('area_here', $area);

	if (0 < $region_id) {
		$parent_id = $exc->get_name($region_id, 'parent_id');
		$action_link = array('text' => $_LANG['back_page'], 'href' => 'area_manage.php?act=list&&pid=' . $parent_id, 'type' => 1);
	}
	else {
		$action_link = array('text' => $_LANG['create_region_initial'], 'href' => 'area_manage.php?act=create_region_initial');
	}

	$smarty->assign('action_link', $action_link);
	$smarty->assign('ur_here', $_LANG['05_area_list']);
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->display('area_list.dwt');
}
else if ($_REQUEST['act'] == 'restore_region') {
	admin_priv('area_list');
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'area_manage.php?act=list');
	$sql = ' TRUNCATE TABLE ' . $GLOBALS['ecs']->table('region');
	$GLOBALS['db']->query($sql);
	$sql = ' INSERT INTO ' . $GLOBALS['ecs']->table('region') . ' SELECT * FROM ' . $GLOBALS['ecs']->table('region_backup');

	if ($GLOBALS['db']->query($sql)) {
		sys_msg($_LANG['restore_success'], 0, $link);
	}
	else {
		sys_msg($_LANG['restore_failure'], 0, $link);
	}
}
else if ($_REQUEST['act'] == 'create_region_initial') {
	admin_priv('area_list');
	$smarty->assign('ur_here', $_LANG['create_region_initial']);
	dsc_unlink(ROOT_PATH . DATA_DIR . '/sc_file/pin_regions.php');
	$region_list = get_city_region();
	$record_count = count($region_list);
	$smarty->assign('record_count', $record_count);
	$smarty->assign('page', 1);
	assign_query_info();
	$smarty->display('area_initial.dwt');
}
else if ($_REQUEST['act'] == 'ajax_region_initial') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	static $temp = array();
	$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1;
	$region_list = get_city_region();
	$region_list = $ecs->page_array($page_size, $page, $region_list);
	$pin = new pin();
	$arr = array();
	$letters = range('A', 'Z');

	foreach ($region_list['list'] as $key => $region) {
		if ($region) {
			foreach ($letters as $val) {
				if (strtolower($val) == substr($pin->Pinyin($region['region_name'], EC_CHARSET), 0, 1)) {
					$region_list[$key] = $region;
					$region_list[$key]['initial'] = $val;
				}
			}
		}

		$list = array('region_id' => $region['region_id'], 'parent_id' => $region['parent_id'], 'region_name' => $region['region_name'], 'is_has' => $region['is_has'], 'initial' => $region_list[$key]['initial']);
	}

	$result['list'] = $list;

	if ($result['list']) {
		$pin_regions = read_static_cache('pin_regions', '/data/sc_file/');

		if ($pin_regions === false) {
			write_static_cache('pin_regions', array($result['list']), '/data/sc_file/');
		}
		else {
			array_push($pin_regions, $result['list']);
			write_static_cache('pin_regions', $pin_regions, '/data/sc_file/');
		}
	}

	$result['page'] = $region_list['filter']['page'] + 1;
	$result['page_size'] = $region_list['filter']['page_size'];
	$result['record_count'] = $region_list['filter']['record_count'];
	$result['page_count'] = $region_list['filter']['page_count'];
	$result['is_stop'] = 1;

	if ($region_list['filter']['page_count'] < $page) {
		$result['is_stop'] = 0;
		$regions = get_pin_regions();

		if ($regions) {
			dsc_unlink(ROOT_PATH . DATA_DIR . '/sc_file/pin_regions.php');
			write_static_cache('pin_regions', $regions, '/data/sc_file/');
		}
	}
	else {
		$result['filter_page'] = $region_list['filter']['page'];
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'add_area') {
	check_authz_json('area_list');
	$parent_id = intval($_POST['parent_id']);
	$region_name = json_str_iconv(trim($_POST['region_name']));
	$region_type = intval($_POST['region_type']);
	$region_arr = area_list($region_id);
	$smarty->assign('region_arr', $region_arr);
	$area_top = '-';

	if (0 < $region_id) {
		$area_name = $exc->get_name($region_id);
		$area_top = $area_name;

		if ($region_arr) {
			$area = $region_arr[0]['type'];
		}
	}
	else {
		$area = $_LANG['country'];
	}

	$smarty->assign('area_top', $area_top);
	$smarty->assign('area_here', $area);

	if (empty($region_name)) {
		make_json_error($_LANG['region_name_empty']);
	}

	if (!$exc->is_only('region_name', $region_name, 0, 'parent_id = \'' . $parent_id . '\'')) {
		make_json_error($_LANG['region_name_exist']);
	}

	$sql = 'INSERT INTO ' . $ecs->table('region') . ' (parent_id, region_name, region_type) ' . ('VALUES (\'' . $parent_id . '\', \'' . $region_name . '\', \'' . $region_type . '\')');

	if ($GLOBALS['db']->query($sql, 'SILENT')) {
		admin_log($region_name, 'add', 'area');
		$region_arr = area_list($parent_id);

		foreach ($region_arr as $k => $v) {
			$region_arr[$k]['parent_name'] = $exc->get_name($v['parent_id']);
		}

		$smarty->assign('region_arr', $region_arr);
		$smarty->assign('region_type', $region_type);
		make_json_result($smarty->fetch('library/area_list.lbi'));
	}
	else {
		make_json_error($_LANG['add_area_error']);
	}
}
else if ($_REQUEST['act'] == 'edit_area_name') {
	check_authz_json('area_list');
	$id = intval($_POST['id']);
	$region_name = json_str_iconv(trim($_POST['val']));

	if (empty($region_name)) {
		make_json_error($_LANG['region_name_empty']);
	}

	$msg = '';
	$parent_id = $exc->get_name($id, 'parent_id');

	if (!$exc->is_only('region_name', $region_name, $id, 'parent_id = \'' . $parent_id . '\'')) {
		make_json_error($_LANG['region_name_exist']);
	}

	if ($exc->edit('region_name = \'' . $region_name . '\'', $id)) {
		admin_log($region_name, 'edit', 'area');
		make_json_result(stripslashes($region_name));
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'drop_area') {
	check_authz_json('area_list');
	$id = intval($_REQUEST['id']);
	$sql = 'SELECT * FROM ' . $ecs->table('region') . (' WHERE region_id = \'' . $id . '\'');
	$region = $db->getRow($sql);
	$region_arr = area_list($region['parent_id']);

	foreach ($region_arr as $k => $v) {
		$region_arr[$k]['parent_name'] = $exc->get_name($region['parent_id']);
	}

	$smarty->assign('region_arr', $region_arr);
	$region_type = $region['region_type'];
	$delete_region[] = $id;
	$new_region_id = $id;

	if ($region_type < 6) {
		for ($i = 1; $i < 6 - $region_type; $i++) {
			$new_region_id = new_region_id($new_region_id);

			if (count($new_region_id)) {
				$delete_region = array_merge($delete_region, $new_region_id);
			}
			else {
				continue;
			}
		}
	}

	$sql = 'DELETE FROM ' . $ecs->table('region') . 'WHERE region_id' . db_create_in($delete_region);

	if ($db->query($sql)) {
		admin_log(addslashes($region['region_name']), 'remove', 'area');
		$region_arr = area_list($region['parent_id']);

		foreach ($region_arr as $k => $v) {
			$region_arr[$k]['parent_name'] = $exc->get_name($region['parent_id']);
		}

		$smarty->assign('region_arr', $region_arr);
		$smarty->assign('region_type', $region['region_type']);
		make_json_result($smarty->fetch('library/area_list.lbi'));
	}
	else {
		make_json_error($db->error());
	}
}

?>
