<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function suppliers_list()
{
	$result = get_filter();

	if ($result === false) {
		$aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'suppliers_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
		$where = 'WHERE 1 ';
		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);
		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('suppliers') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = "SELECT suppliers_id, suppliers_name, suppliers_desc, is_check\r\n                FROM " . $GLOBALS['ecs']->table('suppliers') . ("\r\n                " . $where . "\r\n                ORDER BY ") . $filter['sort_by'] . ' ' . $filter['sort_order'] . "\r\n                LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ', ' . $filter['page_size'] . ' ';
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);
	$arr = array('result' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
define('SUPPLIERS_ACTION_LIST', 'delivery_view,back_view');

if ($_REQUEST['act'] == 'list') {
	admin_priv('suppliers_manage');
	$result = suppliers_list();
	$smarty->assign('ur_here', $_LANG['suppliers_list']);
	$smarty->assign('action_link', array('href' => 'suppliers.php?act=add', 'text' => $_LANG['add_suppliers']));
	$smarty->assign('full_page', 1);
	$smarty->assign('suppliers_list', $result['result']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->display('suppliers_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	check_authz_json('suppliers_manage');
	$result = suppliers_list();
	$smarty->assign('suppliers_list', $result['result']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$sort_flag = sort_flag($result['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('suppliers_list.dwt'), '', array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('suppliers_manage');
	$id = intval($_REQUEST['id']);
	$sql = "SELECT *\r\n            FROM " . $ecs->table('suppliers') . ("\r\n            WHERE suppliers_id = '" . $id . '\'');
	$suppliers = $db->getRow($sql, true);

	if ($suppliers['suppliers_id']) {
		$sql = "SELECT COUNT(*)\r\n                FROM " . $ecs->table('order_info') . 'AS O, ' . $ecs->table('order_goods') . ' AS OG, ' . $ecs->table('goods') . (" AS G\r\n                WHERE O.order_id = OG.order_id\r\n                AND OG.goods_id = G.goods_id\r\n                AND G.suppliers_id = '" . $id . '\'');
		$order_exists = $db->getOne($sql, true);

		if (0 < $order_exists) {
			make_json_error('供货商是否存在订单，不能删除');
		}

		$sql = "SELECT COUNT(*)\r\n                FROM " . $ecs->table('goods') . ("AS G\r\n                WHERE G.suppliers_id = '" . $id . '\'');
		$goods_exists = $db->getOne($sql, true);

		if (0 < $goods_exists) {
			make_json_error('供货商是否存在商品，不能删除');
		}

		$sql = 'DELETE FROM ' . $ecs->table('suppliers') . ("\r\n            WHERE suppliers_id = '" . $id . '\'');
		$db->query($sql);
		$table_array = array('admin_user', 'delivery_order', 'back_order');

		foreach ($table_array as $value) {
			$sql = 'DELETE FROM ' . $ecs->table($value) . (' WHERE suppliers_id = \'' . $id . '\'');
			$db->query($sql, 'SILENT');
		}

		admin_log($suppliers['suppliers_name'], 'remove', 'suppliers');
		clear_cache_files();
	}

	$url = 'suppliers.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'is_check') {
	check_authz_json('suppliers_manage');
	$id = intval($_REQUEST['id']);
	$sql = "SELECT suppliers_id, is_check\r\n            FROM " . $ecs->table('suppliers') . ("\r\n            WHERE suppliers_id = '" . $id . '\'');
	$suppliers = $db->getRow($sql, true);

	if ($suppliers['suppliers_id']) {
		$_suppliers['is_check'] = empty($suppliers['is_check']) ? 1 : 0;
		$db->autoExecute($ecs->table('suppliers'), $_suppliers, '', 'suppliers_id = \'' . $id . '\'');
		clear_cache_files();
		make_json_result($_suppliers['is_check']);
	}

	exit();
}
else if ($_REQUEST['act'] == 'batch') {
	if (empty($_POST['checkboxes'])) {
		sys_msg($_LANG['no_record_selected']);
	}
	else {
		admin_priv('suppliers_manage');
		$ids = $_POST['checkboxes'];

		if (isset($_POST['remove'])) {
			$sql = "SELECT *\r\n                    FROM " . $ecs->table('suppliers') . "\r\n                    WHERE suppliers_id " . db_create_in($ids);
			$suppliers = $db->getAll($sql);

			foreach ($suppliers as $key => $value) {
				$sql = "SELECT COUNT(*)\r\n                        FROM " . $ecs->table('order_info') . 'AS O, ' . $ecs->table('order_goods') . ' AS OG, ' . $ecs->table('goods') . " AS G\r\n                        WHERE O.order_id = OG.order_id\r\n                        AND OG.goods_id = G.goods_id\r\n                        AND G.suppliers_id = '" . $value['suppliers_id'] . '\'';
				$order_exists = $db->getOne($sql, true);

				if (0 < $order_exists) {
					unset($suppliers[$key]);
				}

				$sql = "SELECT COUNT(*)\r\n                        FROM " . $ecs->table('goods') . "AS G\r\n                        WHERE G.suppliers_id = '" . $value['suppliers_id'] . '\'';
				$goods_exists = $db->getOne($sql, true);

				if (0 < $goods_exists) {
					unset($suppliers[$key]);
				}
			}

			if (empty($suppliers)) {
				sys_msg($_LANG['batch_drop_no']);
			}

			$sql = 'DELETE FROM ' . $ecs->table('suppliers') . "\r\n                WHERE suppliers_id " . db_create_in($ids);
			$db->query($sql);
			$table_array = array('admin_user', 'delivery_order', 'back_order');

			foreach ($table_array as $value) {
				$sql = 'DELETE FROM ' . $ecs->table($value) . ' WHERE suppliers_id ' . db_create_in($ids) . ' ';
				$db->query($sql, 'SILENT');
			}

			$suppliers_names = '';

			foreach ($suppliers as $value) {
				$suppliers_names .= $value['suppliers_name'] . '|';
			}

			admin_log($suppliers_names, 'remove', 'suppliers');
			clear_cache_files();
			$link[] = array('text' => '返回', 'href' => 'suppliers.php?act=list');
			sys_msg($_LANG['batch_drop_ok'], '', $link);
		}
	}
}
else if (in_array($_REQUEST['act'], array('add', 'edit'))) {
	admin_priv('suppliers_manage');

	if ($_REQUEST['act'] == 'add') {
		$suppliers = array();
		$sql = "SELECT user_id, user_name, CASE\r\n                WHEN suppliers_id = 0 THEN 'free'\r\n                ELSE 'other' END AS type\r\n                FROM " . $ecs->table('admin_user') . "\r\n                WHERE agency_id = 0\r\n                AND action_list <> 'all' AND ru_id = 0 ";
		$suppliers['admin_list'] = $db->getAll($sql);
		$smarty->assign('ur_here', $_LANG['add_suppliers']);
		$smarty->assign('action_link', array('href' => 'suppliers.php?act=list', 'text' => $_LANG['suppliers_list']));
		$smarty->assign('form_action', 'insert');
		$smarty->assign('suppliers', $suppliers);
		assign_query_info();
		$smarty->display('suppliers_info.dwt');
	}
	else if ($_REQUEST['act'] == 'edit') {
		$suppliers = array();
		$id = $_REQUEST['id'];
		$sql = 'SELECT * FROM ' . $ecs->table('suppliers') . (' WHERE suppliers_id = \'' . $id . '\'');
		$suppliers = $db->getRow($sql);

		if (count($suppliers) <= 0) {
			sys_msg('suppliers does not exist');
		}

		$sql = "SELECT user_id, user_name, CASE\r\n                WHEN suppliers_id = '" . $id . "' THEN 'this'\r\n                WHEN suppliers_id = 0 THEN 'free'\r\n                ELSE 'other' END AS type\r\n                FROM " . $ecs->table('admin_user') . "\r\n                WHERE agency_id = 0\r\n                AND action_list <> 'all' AND ru_id = 0 ";
		$suppliers['admin_list'] = $db->getAll($sql);
		$smarty->assign('ur_here', $_LANG['edit_suppliers']);
		$smarty->assign('action_link', array('href' => 'suppliers.php?act=list', 'text' => $_LANG['suppliers_list']));
		$smarty->assign('form_action', 'update');
		$smarty->assign('suppliers', $suppliers);
		assign_query_info();
		$smarty->display('suppliers_info.dwt');
	}
}
else if (in_array($_REQUEST['act'], array('insert', 'update'))) {
	admin_priv('suppliers_manage');

	if ($_REQUEST['act'] == 'insert') {
		$suppliers = array('suppliers_name' => trim($_POST['suppliers_name']), 'suppliers_desc' => trim($_POST['suppliers_desc']), 'parent_id' => 0);
		$sql = "SELECT suppliers_id\r\n                FROM " . $ecs->table('suppliers') . "\r\n                WHERE suppliers_name = '" . $suppliers['suppliers_name'] . '\' ';

		if ($db->getOne($sql)) {
			sys_msg($_LANG['suppliers_name_exist']);
		}

		$db->autoExecute($ecs->table('suppliers'), $suppliers, 'INSERT');
		$suppliers['suppliers_id'] = $db->insert_id();

		if (isset($_POST['admins'])) {
			$sql = 'UPDATE ' . $ecs->table('admin_user') . ' SET suppliers_id = \'' . $suppliers['suppliers_id'] . '\', action_list = \'' . SUPPLIERS_ACTION_LIST . '\' WHERE user_id ' . db_create_in($_POST['admins']);
			$db->query($sql);
		}

		admin_log($suppliers['suppliers_name'], 'add', 'suppliers');
		clear_cache_files();
		$links = array(
			array('href' => 'suppliers.php?act=add', 'text' => $_LANG['continue_add_suppliers']),
			array('href' => 'suppliers.php?act=list', 'text' => $_LANG['back_suppliers_list'])
			);
		sys_msg($_LANG['add_suppliers_ok'], 0, $links);
	}

	if ($_REQUEST['act'] == 'update') {
		$suppliers = array('id' => trim($_POST['id']));
		$suppliers['new'] = array('suppliers_name' => trim($_POST['suppliers_name']), 'suppliers_desc' => trim($_POST['suppliers_desc']));
		$sql = 'SELECT * FROM ' . $ecs->table('suppliers') . ' WHERE suppliers_id = \'' . $suppliers['id'] . '\'';
		$suppliers['old'] = $db->getRow($sql);

		if (empty($suppliers['old']['suppliers_id'])) {
			sys_msg('suppliers does not exist');
		}

		$sql = "SELECT suppliers_id\r\n                FROM " . $ecs->table('suppliers') . "\r\n                WHERE suppliers_name = '" . $suppliers['new']['suppliers_name'] . "'\r\n                AND suppliers_id <> '" . $suppliers['id'] . '\'';

		if ($db->getOne($sql)) {
			sys_msg($_LANG['suppliers_name_exist']);
		}

		$db->autoExecute($ecs->table('suppliers'), $suppliers['new'], 'UPDATE', 'suppliers_id = \'' . $suppliers['id'] . '\'');
		$sql = 'UPDATE ' . $ecs->table('admin_user') . ' SET suppliers_id = 0, action_list = \'' . SUPPLIERS_ACTION_LIST . '\' WHERE suppliers_id = \'' . $suppliers['id'] . '\'';
		$db->query($sql);

		if (isset($_POST['admins'])) {
			$sql = 'UPDATE ' . $ecs->table('admin_user') . ' SET suppliers_id = \'' . $suppliers['old']['suppliers_id'] . '\' WHERE user_id ' . db_create_in($_POST['admins']);
			$db->query($sql);
		}

		admin_log($suppliers['old']['suppliers_name'], 'edit', 'suppliers');
		clear_cache_files();
		$links[] = array('href' => 'suppliers.php?act=list', 'text' => $_LANG['back_suppliers_list']);
		sys_msg($_LANG['edit_suppliers_ok'], 0, $links);
	}
}

?>
