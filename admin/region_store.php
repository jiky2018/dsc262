<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function region_store_list()
{
	$filter = array();
	$filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
	if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
		$filter['keyword'] = json_str_iconv($filter['keyword']);
	}

	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'rs.rs_id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$where = 'WHERE 1 ';

	if (!empty($filter['keyword'])) {
		$where .= ' AND (rs.rs_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\') ';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('region_store') . ' AS rs ' . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$arr = array();
	$sql = 'SELECT rs.* FROM ' . $GLOBALS['ecs']->table('region_store') . ' AS rs ' . $where . 'ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$region_id = get_table_date('rs_region', 'rs_id=\'' . $rows['rs_id'] . '\'', array('region_id'), 2);

		if ($region_id) {
			$rows['region_name'] = get_table_date('region', 'region_id=\'' . $region_id . '\'', array('region_name'), 2);
		}

		$rows['user_name'] = get_table_date('admin_user', 'rs_id=\'' . $rows['rs_id'] . '\'', array('user_name'), 2);
		$arr[] = $rows;
	}

	return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_region_store_info($rs_id = 0)
{
	$region_store = get_table_date('region_store', 'rs_id=\'' . $rs_id . '\'', array('*'));

	if ($region_store) {
		$sql = ' SELECT region_id FROM ' . $GLOBALS['ecs']->table('rs_region') . (' WHERE rs_id = \'' . $rs_id . '\' ');
		$region_id = $GLOBALS['db']->getOne($sql);
		$sql = ' SELECT user_id FROM ' . $GLOBALS['ecs']->table('admin_user') . (' WHERE rs_id = \'' . $rs_id . '\' ');
		$user_id = $GLOBALS['db']->getOne($sql);
		$region_store['region_id'] = $region_id;
		$region_store['user_id'] = $user_id;
	}

	return $region_store;
}

function get_region_admin()
{
	$super_admin_id = get_table_date('admin_user', 'action_list=\'all\'', array('user_id'), 2);
	$sql = ' SELECT user_id, user_name FROM ' . $GLOBALS['ecs']->table('admin_user') . (' WHERE action_list != \'all\' AND ru_id = 0 AND parent_id = ' . $super_admin_id . ' ORDER BY user_id DESC');
	$region_admin = $GLOBALS['db']->getAll($sql);
	return $region_admin;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$adminru = get_admin_ru_id();

if (empty($_REQUEST['act'])) {
	exit('Error');
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('region_store_manage');
	$smarty->assign('ur_here', $_LANG['01_region_store_manage']);
	$smarty->assign('action_link', array('text' => $_LANG['region_store_add'], 'href' => 'region_store.php?act=add'));
	$smarty->assign('full_page', 1);
	$region_store = region_store_list($adminru['ru_id']);
	$smarty->assign('list', $region_store['list']);
	$smarty->assign('filter', $region_store['filter']);
	$smarty->assign('record_count', $region_store['record_count']);
	$smarty->assign('page_count', $region_store['page_count']);
	$sort_flag = sort_flag($region_store['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('region_store_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	check_authz_json('region_store_manage');
	$region_store = region_store_list($adminru['ru_id']);
	$smarty->assign('list', $region_store['list']);
	$smarty->assign('filter', $region_store['filter']);
	$smarty->assign('record_count', $region_store['record_count']);
	$smarty->assign('page_count', $region_store['page_count']);
	$sort_flag = sort_flag($region_store['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('region_store_list.dwt'), '', array('filter' => $region_store['filter'], 'page_count' => $region_store['page_count']));
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('region_store_manage');
	$id = intval($_GET['id']);
	$exc = new exchange($ecs->table('region_store'), $db, 'rs_id');
	$exc->drop($id);
	$db->query('DELETE FROM' . $ecs->table('rs_region') . (' WHERE rs_id=\'' . $id . '\''));
	$db->autoExecute($ecs->table('admin_user'), array('rs_id' => 0), 'UPDATE', 'rs_id = \'' . $id . '\'');
	$url = 'region_store.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'edit_rs_name') {
	check_authz_json('region_store_manage');
	$id = intval($_POST['id']);
	$val = trim($_POST['val']);
	$sql = ' UPDATE ' . $ecs->table('region_store') . (' SET rs_name = \'' . $val . '\' WHERE rs_id = \'' . $id . '\' ');
	$db->query($sql);
	make_json_result($val);
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		admin_priv('region_store_manage');
		$rs_id = !empty($_REQUEST['rs_id']) ? intval($_REQUEST['rs_id']) : 0;

		if (0 < $rs_id) {
			$region_store = get_region_store_info($rs_id);

			if ($region_store['region_id']) {
				$region_level = get_region_level($region_store['region_id']);
				$region_store['region_level'] = $region_level;
			}

			$smarty->assign('region_store', $region_store);
			$smarty->assign('ur_here', $_LANG['edit']);
			$smarty->assign('form_action', 'update');
		}
		else {
			$smarty->assign('ur_here', $_LANG['add']);
			$smarty->assign('form_action', 'insert');
		}

		$smarty->assign('action_link', array('text' => $_LANG['01_region_store_manage'], 'href' => 'region_store.php?act=list'));
		$smarty->assign('country_all', get_regions());
		$smarty->assign('province_all', get_regions(1, 1));
		$smarty->assign('region_admin', get_region_admin());
		assign_query_info();
		$smarty->display('region_store_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
			admin_priv('region_store_manage');
			$store_data = array();
			$region_data = array();
			$admin_data = array();
			$rs_id = !empty($_REQUEST['rs_id']) ? intval($_REQUEST['rs_id']) : 0;
			$store_data['rs_name'] = !empty($_REQUEST['rs_name']) ? trim($_REQUEST['rs_name']) : '';
			$region_data['region_id'] = !empty($_REQUEST['city']) ? intval($_REQUEST['city']) : 0;
			$admin_data['user_id'] = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
			$region_data['rs_id'] = $rs_id;
			$admin_data['rs_id'] = $rs_id;
			$sql = ' SELECT rs_id FROM ' . $ecs->table('region_store') . (' WHERE rs_name = \'' . $store_data['rs_name'] . '\' AND rs_id <> \'' . $rs_id . '\' LIMIT 1 ');
			$is_only = $db->getOne($sql);

			if (!empty($is_only)) {
				sys_msg($_LANG['region_store_exist'], 1);
			}

			if (0 < $rs_id) {
				$db->autoExecute($ecs->table('region_store'), $store_data, 'UPDATE', 'rs_id = \'' . $rs_id . '\'');
				$msg = $_LANG['edit_success'];
			}
			else {
				$db->autoExecute($ecs->table('region_store'), $store_data, 'INSERT');
				$msg = $_LANG['add_success'];
				$rs_id = $db->insert_id();
				$region_data['rs_id'] = $rs_id;
				$admin_data['rs_id'] = $rs_id;
			}

			$sql = ' SELECT id FROM ' . $ecs->table('rs_region') . (' WHERE region_id = \'' . $region_data['region_id'] . '\' AND rs_id <> \'' . $rs_id . '\' LIMIT 1 ');
			$is_only = $db->getOne($sql);

			if (!empty($is_only)) {
				sys_msg($_LANG['rs_region_exist'], 1);
			}

			$sql = ' SELECT id FROM ' . $ecs->table('rs_region') . (' WHERE rs_id = \'' . $rs_id . '\' LIMIT 1 ');
			$is_exist = $db->getOne($sql);

			if ($is_exist) {
				$db->autoExecute($ecs->table('rs_region'), $region_data, 'UPDATE', 'rs_id = \'' . $rs_id . '\'');
			}
			else {
				$db->autoExecute($ecs->table('rs_region'), $region_data, 'INSERT');
			}

			$sql = ' SELECT rs_id FROM ' . $ecs->table('admin_user') . (' WHERE rs_id <> \'' . $rs_id . '\' AND user_id = \'' . $admin_data['user_id'] . '\' LIMIT 1 ');
			$is_only = $db->getOne($sql);

			if (!empty($is_only)) {
				sys_msg($_LANG['rs_admin_exist'], 1);
			}

			$db->autoExecute($ecs->table('admin_user'), array('rs_id' => 0), 'UPDATE', 'rs_id = \'' . $rs_id . '\'');
			$db->autoExecute($ecs->table('admin_user'), $admin_data, 'UPDATE', 'user_id = \'' . $admin_data['user_id'] . '\'');
			$link[] = array('text' => $_LANG['back_list'], 'href' => 'region_store.php?act=list');
			sys_msg($msg, 0, $link);
		}
		else if ($_REQUEST['act'] == 'admin_update') {
			check_authz_json('region_store_manage');
			$smarty->assign('region_admin', get_region_admin());
			$content = $smarty->fetch('library/region_admin.lbi');
			make_json_result($content);
		}
	}
}

?>
