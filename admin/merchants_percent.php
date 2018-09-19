<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function percent_list()
{
	$result = get_filter();

	if ($result === false) {
		$aiax = (isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'percent_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
		$where = 'WHERE 1 ';
		$filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);
		if (isset($_REQUEST['page_size']) && (0 < intval($_REQUEST['page_size']))) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && (0 < intval($_COOKIE['ECSCP']['page_size']))) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_percent') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = "SELECT percent_id, percent_value , sort_order, add_time  \r\n                FROM " . $GLOBALS['ecs']->table('merchants_percent') . $au_sql . " \r\n                " . $where . ' ' . $admin_sup . "\r\n                ORDER BY " . $filter['sort_by'] . ' ' . $filter['sort_order'] . "\r\n                LIMIT " . (($filter['page'] - 1) * $filter['page_size']) . ', ' . $filter['page_size'] . ' ';
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$percent_list = $GLOBALS['db']->getAll($sql);
	$count = count($percent_list);

	for ($i = 0; $i < $count; $i++) {
		$percent_list[$i]['add_time'] = local_date($GLOBALS['_CFG']['date_format'], $percent_list[$i]['add_time']);
	}

	$arr = array('result' => $percent_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
define('SUPPLIERS_ACTION_LIST', 'delivery_view,back_view');

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => 'add_suppliers_percent'));
	admin_priv('merchants_percent');
	$user_id = (isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);

	if (empty($user_id)) {
		$Loaction = 'merchants_commission.php?act=list';
		ecs_header('Location: ' . $Loaction . "\n");
		exit();
	}

	$result = percent_list();
	$smarty->assign('ur_here', $_LANG['suppliers_percent_list']);
	$smarty->assign('action_link', array('href' => 'merchants_percent.php?act=add', 'text' => $_LANG['add_suppliers_percent']));
	$smarty->assign('action_link2', array('href' => 'merchants_commission.php?act=list', 'text' => $_LANG['suppliers_list_server']));
	$smarty->assign('full_page', 1);
	$smarty->assign('percent_list', $result['result']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');
	$smarty->assign('user_id', $user_id);
	assign_query_info();
	$smarty->display('merchants_percent_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	check_authz_json('merchants_percent');
	$result = percent_list();
	$smarty->assign('percent_list', $result['result']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$sort_flag = sort_flag($result['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('merchants_percent_list.dwt'), '', array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}
else if ($_REQUEST['act'] == 'edit_percent_value') {
	check_authz_json('merchants_percent');
	$id = intval($_POST['id']);
	$value = json_str_iconv(trim($_POST['val']));
	$sql = "SELECT percent_id\r\n            FROM " . $ecs->table('merchants_percent') . "\r\n            WHERE percent_value = '" . $value . "'\r\n            AND percent_id <> '" . $id . '\' ';

	if ($db->getOne($sql)) {
		make_json_error(sprintf($_LANG['percent_name_exist'], $value));
	}
	else {
		$sql = 'UPDATE ' . $ecs->table('merchants_percent') . "\r\n                SET percent_value = '" . $value . "'\r\n                WHERE percent_id = '" . $id . '\'';

		if ($result = $db->query($sql)) {
			admin_log($value, 'edit', 'merchants_percent');
			clear_cache_files();
			make_json_result(stripslashes($value));
		}
		else {
			make_json_result(sprintf($_LANG['agency_edit_fail'], $value));
		}
	}
}
else if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('merchants_percent');
	$id = intval($_POST['id']);
	$value = json_str_iconv(trim($_POST['val']));
	$sql = "SELECT percent_id\r\n            FROM " . $ecs->table('merchants_percent') . "\r\n            WHERE sort_order = '" . $value . "'\r\n            AND percent_id <> '" . $id . '\' ';

	if ($db->getOne($sql)) {
		make_json_error(sprintf($_LANG['percent_sort_exist'], $value));
	}
	else {
		$sql = 'UPDATE ' . $ecs->table('merchants_percent') . "\r\n                SET sort_order = '" . $value . "'\r\n                WHERE percent_id = '" . $id . '\'';

		if ($result = $db->query($sql)) {
			admin_log($value, 'edit', 'merchants_percent');
			clear_cache_files();
			make_json_result(stripslashes($value));
		}
		else {
			make_json_result(sprintf($_LANG['agency_edit_fail'], $value));
		}
	}
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('merchants_percent');
	$id = intval($_REQUEST['id']);
	$sql = 'DELETE FROM ' . $ecs->table('merchants_percent') . "\r\n\t\tWHERE percent_id = '" . $id . '\'';
	$db->query($sql);
	admin_log($suppliers['percent_id'], 'remove', 'merchants_percent');
	clear_cache_files();
	$url = 'merchants_percent.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'batch') {
	if (empty($_POST['checkboxes'])) {
		sys_msg($_LANG['no_record_selected']);
	}
	else {
		admin_priv('merchants_percent');
		$ids = $_POST['checkboxes'];

		if (isset($_POST['remove'])) {
			$sql = "SELECT *\r\n                    FROM " . $ecs->table('merchants_percent') . "\r\n                    WHERE percent_id " . db_create_in($ids);
			$percent = $db->getAll($sql);

			if (empty($percent)) {
				sys_msg($_LANG['batch_drop_no']);
			}

			$sql = 'DELETE FROM ' . $ecs->table('merchants_percent') . "\r\n                WHERE percent_id " . db_create_in($ids);
			$db->query($sql);
			$percent_value = '';

			foreach ($percent as $value) {
				$percent_value .= $value['percent_value'] . '|';
			}

			admin_log($percent_value, 'remove', 'suppliers');
			clear_cache_files();
			sys_msg($_LANG['batch_drop_ok']);
		}
	}
}
else if (in_array($_REQUEST['act'], array('add', 'edit'))) {
	admin_priv('merchants_percent');
	$smarty->assign('action_link', array('href' => 'merchants_percent.php?act=list', 'text' => $_LANG['suppliers_percent_list']));

	if ($_REQUEST['act'] == 'add') {
		$smarty->assign('ur_here', $_LANG['add_suppliers_percent']);
		$smarty->assign('form_action', 'insert');
		assign_query_info();
		$smarty->display('merchants_percent_info.dwt');
	}
	else if ($_REQUEST['act'] == 'edit') {
		$suppliers = array();
		$id = $_REQUEST['id'];
		$sql = 'SELECT * FROM ' . $ecs->table('merchants_percent') . ' WHERE percent_id = \'' . $id . '\'';
		$percent = $db->getRow($sql);

		if (count($percent) <= 0) {
			sys_msg('suppliers_percent does not exist');
		}

		$sql = 'SELECT * FROM ' . $ecs->table('merchants_percent') . ' WHERE percent_id = \'' . $id . '\'';
		$percent = $db->getRow($sql);
		$smarty->assign('ur_here', $_LANG['edit_suppliers_percent']);
		$smarty->assign('form_action', 'update');
		$smarty->assign('percent', $percent);
		assign_query_info();
		$smarty->display('merchants_percent_info.dwt');
	}
}
else if (in_array($_REQUEST['act'], array('insert', 'update'))) {
	admin_priv('merchants_percent');

	if ($_REQUEST['act'] == 'insert') {
		$suppliers = array('percent_value' => trim($_POST['percent_value']), 'sort_order' => trim($_POST['sort_order']), 'add_time' => gmtime());
		$sql = "SELECT percent_id\r\n                FROM " . $ecs->table('merchants_percent') . "\r\n                WHERE percent_value = '" . $suppliers['percent_value'] . '\' ';

		if ($db->getOne($sql)) {
			sys_msg($_LANG['percent_name_exist']);
		}

		$db->autoExecute($ecs->table('merchants_percent'), $suppliers, 'INSERT');
		$suppliers['percent_id'] = $db->insert_id();
		admin_log($suppliers['percent_value'], 'add', 'merchants_percent');
		clear_cache_files();
		$links = array(
			array('href' => 'merchants_percent.php?act=add', 'text' => $_LANG['continue_add_percent']),
			array('href' => 'merchants_percent.php?act=list', 'text' => $_LANG['back_percent_list'])
			);
		sys_msg($_LANG['add_percent_ok'], 0, $links);
	}

	if ($_REQUEST['act'] == 'update') {
		$percent = array('id' => trim($_POST['id']));
		$percent['new'] = array('percent_value' => trim($_POST['percent_value']), 'sort_order' => trim($_POST['sort_order']));
		$sql = 'SELECT * FROM ' . $ecs->table('merchants_percent') . ' WHERE percent_id = \'' . $percent['id'] . '\'';
		$percent['old'] = $db->getRow($sql);

		if (empty($percent['old']['percent_id'])) {
			sys_msg('suppliers_percent does not exist');
		}

		$sql = "SELECT percent_id\r\n                FROM " . $ecs->table('merchants_percent') . "\r\n                WHERE percent_value = '" . $suppliers['new']['percent_value'] . "'\r\n                AND percent_id <> '" . $percent['id'] . '\'';

		if ($db->getOne($sql)) {
			sys_msg($_LANG['percent_name_exist']);
		}

		$db->autoExecute($ecs->table('merchants_percent'), $percent['new'], 'UPDATE', 'percent_id = \'' . $percent['id'] . '\'');
		admin_log($suppliers['old']['percent_value'], 'edit', 'merchants_percent');
		clear_cache_files();
		$links[] = array('href' => 'merchants_percent.php?act=list', 'text' => $_LANG['back_percent_list']);
		sys_msg($_LANG['edit_percent_ok'], 0, $links);
	}
}

?>
