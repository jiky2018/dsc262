<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_agencylist()
{
	$result = get_filter();

	if ($result === false) {
		$filter = array();
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'agency_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('agency');
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('agency') . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$arr = array();

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$arr[] = $rows;
	}

	return array('agency' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('agency'), $db, 'agency_id', 'agency_name');

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', $_LANG['agency_list']);
	$smarty->assign('action_link', array('text' => $_LANG['add_agency'], 'href' => 'agency.php?act=add'));
	$smarty->assign('full_page', 1);
	$agency_list = get_agencylist();
	$smarty->assign('agency_list', $agency_list['agency']);
	$smarty->assign('filter', $agency_list['filter']);
	$smarty->assign('record_count', $agency_list['record_count']);
	$smarty->assign('page_count', $agency_list['page_count']);
	$sort_flag = sort_flag($agency_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('agency_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$agency_list = get_agencylist();
	$smarty->assign('agency_list', $agency_list['agency']);
	$smarty->assign('filter', $agency_list['filter']);
	$smarty->assign('record_count', $agency_list['record_count']);
	$smarty->assign('page_count', $agency_list['page_count']);
	$sort_flag = sort_flag($agency_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('agency_list.dwt'), '', array('filter' => $agency_list['filter'], 'page_count' => $agency_list['page_count']));
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('agency_manage');
	$id = intval($_GET['id']);
	$name = $exc->get_name($id);
	$exc->drop($id);
	$table_array = array('admin_user', 'region', 'order_info', 'delivery_order', 'back_order');

	foreach ($table_array as $value) {
		$sql = 'UPDATE ' . $ecs->table($value) . ' SET agency_id = 0 WHERE agency_id = \'' . $id . '\'';
		$db->query($sql);
	}

	admin_log($name, 'remove', 'agency');
	clear_cache_files();
	$url = 'agency.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'batch') {
	if (empty($_POST['checkboxes'])) {
		sys_msg($_LANG['no_record_selected']);
	}
	else {
		admin_priv('agency_manage');
		$ids = $_POST['checkboxes'];

		if (isset($_POST['remove'])) {
			$sql = 'DELETE FROM ' . $ecs->table('agency') . ' WHERE agency_id ' . db_create_in($ids);
			$db->query($sql);
			$table_array = array('admin_user', 'region', 'order_info', 'delivery_order', 'back_order');

			foreach ($table_array as $value) {
				$sql = 'UPDATE ' . $ecs->table($value) . ' SET agency_id = 0 WHERE agency_id ' . db_create_in($ids) . ' ';
				$db->query($sql);
			}

			admin_log('', 'batch_remove', 'agency');
			clear_cache_files();
			$link[] = array('text' => '返回', 'href' => 'agency.php?act=list');
			sys_msg($_LANG['batch_drop_ok'], '', $link);
		}
	}
}
else {
	if (($_REQUEST['act'] == 'add') || ($_REQUEST['act'] == 'edit')) {
		admin_priv('agency_manage');
		$is_add = $_REQUEST['act'] == 'add';
		$smarty->assign('form_action', $is_add ? 'insert' : 'update');

		if ($is_add) {
			$agency = array(
				'agency_id'   => 0,
				'agency_name' => '',
				'agency_desc' => '',
				'region_list' => array()
				);
		}
		else {
			if (empty($_GET['id'])) {
				sys_msg('invalid param');
			}

			$id = $_GET['id'];
			$sql = 'SELECT * FROM ' . $ecs->table('agency') . ' WHERE agency_id = \'' . $id . '\'';
			$agency = $db->getRow($sql);

			if (empty($agency)) {
				sys_msg('agency does not exist');
			}

			$sql = 'SELECT region_id, region_name FROM ' . $ecs->table('region') . ' WHERE agency_id = \'' . $id . '\'';
			$agency['region_list'] = $db->getAll($sql);
		}

		$sql = 'SELECT user_id, user_name, CASE ' . 'WHEN agency_id = 0 THEN \'free\' ' . 'WHEN agency_id = \'' . $agency['agency_id'] . '\' THEN \'this\' ' . 'ELSE \'other\' END ' . 'AS type ' . 'FROM ' . $ecs->table('admin_user') . ' WHERE ru_id = 0';
		$agency['admin_list'] = $db->getAll($sql);
		$smarty->assign('agency', $agency);
		$Province_list = get_regions(1, 1);
		$smarty->assign('Province_list', $Province_list);

		if ($is_add) {
			$smarty->assign('ur_here', $_LANG['add_agency']);
		}
		else {
			$smarty->assign('ur_here', $_LANG['edit_agency']);
		}

		if ($is_add) {
			$href = 'agency.php?act=list';
		}
		else {
			$href = 'agency.php?act=list&' . list_link_postfix();
		}

		$smarty->assign('action_link', array('href' => $href, 'text' => $_LANG['agency_list']));
		assign_query_info();
		$smarty->display('agency_info.dwt');
	}
	else {
		if (($_REQUEST['act'] == 'insert') || ($_REQUEST['act'] == 'update')) {
			admin_priv('agency_manage');
			$is_add = $_REQUEST['act'] == 'insert';
			$agency = array('agency_id' => intval($_POST['id']), 'agency_name' => sub_str($_POST['agency_name'], 255, false), 'agency_desc' => $_POST['agency_desc']);

			if (!$exc->is_only('agency_name', $agency['agency_name'], $agency['agency_id'])) {
				sys_msg($_LANG['agency_name_exist']);
			}

			if (empty($_POST['regions'])) {
				sys_msg($_LANG['no_regions']);
			}

			if ($is_add) {
				$db->autoExecute($ecs->table('agency'), $agency, 'INSERT');
				$agency['agency_id'] = $db->insert_id();
			}
			else {
				$db->autoExecute($ecs->table('agency'), $agency, 'UPDATE', 'agency_id = \'' . $agency['agency_id'] . '\'');
			}

			if (!$is_add) {
				$sql = 'UPDATE ' . $ecs->table('admin_user') . ' SET agency_id = 0 WHERE agency_id = \'' . $agency['agency_id'] . '\'';
				$db->query($sql);
				$sql = 'UPDATE ' . $ecs->table('region') . ' SET agency_id = 0 WHERE agency_id = \'' . $agency['agency_id'] . '\'';
				$db->query($sql);
			}

			if (isset($_POST['admins'])) {
				$sql = 'UPDATE ' . $ecs->table('admin_user') . ' SET agency_id = \'' . $agency['agency_id'] . '\' WHERE user_id ' . db_create_in($_POST['admins']);
				$db->query($sql);
			}

			if (isset($_POST['regions'])) {
				$sql = 'UPDATE ' . $ecs->table('region') . ' SET agency_id = \'' . $agency['agency_id'] . '\' WHERE region_id ' . db_create_in($_POST['regions']);
				$db->query($sql);
			}

			if ($is_add) {
				admin_log($agency['agency_name'], 'add', 'agency');
			}
			else {
				admin_log($agency['agency_name'], 'edit', 'agency');
			}

			clear_cache_files();

			if ($is_add) {
				$links = array(
					array('href' => 'agency.php?act=add', 'text' => $_LANG['continue_add_agency']),
					array('href' => 'agency.php?act=list', 'text' => $_LANG['back_agency_list'])
					);
				sys_msg($_LANG['add_agency_ok'], 0, $links);
			}
			else {
				$links = array(
					array('href' => 'agency.php?act=list&' . list_link_postfix(), 'text' => $_LANG['back_agency_list'])
					);
				sys_msg($_LANG['edit_agency_ok'], 0, $links);
			}
		}
	}
}

?>
