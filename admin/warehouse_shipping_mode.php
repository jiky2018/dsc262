<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function shipping_mode_list($shipping_id, $region_id)
{
	$adminru = get_admin_ru_id();

	if (0 < $adminru['ru_id']) {
		$ru_id = $adminru['ru_id'];
	}
	else {
		$ru_id = 0;
	}

	$ruCat = ' and wf.user_id = \'' . $ru_id . '\' ';
	$result = get_filter();

	if ($result === false) {
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
		}

		$filter['pay_points_gt'] = empty($_REQUEST['pay_points_gt']) ? 0 : intval($_REQUEST['pay_points_gt']);
		$filter['pay_points_lt'] = empty($_REQUEST['pay_points_lt']) ? 0 : intval($_REQUEST['pay_points_lt']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$ex_where = ' WHERE 1 ';
		$ex_where .= $ruCat;
		$ex_where .= ' AND wf.region_id = \'' . $region_id . '\' AND wf.shipping_id = \'' . $shipping_id . '\' group by wf.id';
		$sql = 'SELECT wf.id, rw1.region_name as region_name1, rw2.region_name as region_name2 ' . ' FROM ' . $GLOBALS['ecs']->table('warehouse_freight') . ' AS wf' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw1 ON wf.warehouse_id = rw1.region_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('shipping') . ' as s ON wf.shipping_id = s.shipping_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw2 ON wf.region_id = rw2.regionId' . $ex_where;
		$filter['record_count'] = count($GLOBALS['db']->getAll($sql));
		$filter = page_and_size($filter);
		$sql = 'SELECT wf.id, wf.shipping_id, rw2.region_id, rw1.region_name as region_name1, rw2.region_name as region_name2 ' . ' FROM ' . $GLOBALS['ecs']->table('warehouse_freight') . ' AS wf' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw1 ON wf.warehouse_id = rw1.region_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('shipping') . ' as s ON wf.shipping_id = s.shipping_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw2 ON wf.region_id = rw2.regionId' . $ex_where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$shipping_list = $GLOBALS['db']->getAll($sql);
	$count = count($shipping_list);

	for ($i = 0; $i < $count; $i++) {
		$user_list[$i]['region_name1'] = $shipping_list[$i]['region_name1'];
		$user_list[$i]['region_name2'] = $shipping_list[$i]['region_name2'];
	}

	$arr = array('shipping_list' => $shipping_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'list') {
	admin_priv('warehouse_manage');
	$smarty->assign('user_ranks', $ranks);
	$shipping_id = (isset($_REQUEST['shipping_id']) ? intval($_REQUEST['shipping_id']) : 0);
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$region_id = $id;
	if (($shipping_id == 0) || ($region_id == 0)) {
		if (isset($_SESSION['admin_shipping_id']) && isset($_SESSION['admin_region_id'])) {
			$shipping_id = $_SESSION['admin_shipping_id'];
			$region_id = $_SESSION['admin_region_id'];
		}
		else {
			$shipping_id = 0;
			$region_id = 0;
		}
	}

	$sql = 'select shipping_name from ' . $ecs->table('shipping') . ' where shipping_id = \'' . $shipping_id . '\'';
	$ur_here = $db->getOne($sql);
	$_SESSION['admin_shipping_id'] = $shipping_id;
	$_SESSION['admin_region_id'] = $region_id;
	$smarty->assign('ur_here', $ur_here);
	$shipping_list = shipping_mode_list($shipping_id, $region_id);
	$smarty->assign('shipping_list', $shipping_list['shipping_list']);
	$smarty->assign('filter', $shipping_list['filter']);
	$smarty->assign('record_count', $shipping_list['record_count']);
	$smarty->assign('page_count', $shipping_list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
	$sql = 'select region_id from ' . $ecs->table('region_warehouse') . ' where regionId = \'' . $region_id . '\'';
	$regionId = $db->getOne($sql);
	$smarty->assign('action_link', array('text' => $_LANG['01_shipping_add'], 'href' => 'warehouse.php?act=freight&id=' . $regionId));
	assign_query_info();
	$smarty->display('warehouse_shipping_mode_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$shipping_id = $_SESSION['admin_shipping_id'];
	$region_id = $_SESSION['admin_region_id'];
	$shipping_list = shipping_mode_list($shipping_id, $region_id);
	$smarty->assign('shipping_list', $shipping_list['shipping_list']);
	$smarty->assign('filter', $shipping_list['filter']);
	$smarty->assign('record_count', $shipping_list['record_count']);
	$smarty->assign('page_count', $shipping_list['page_count']);
	$sort_flag = sort_flag($shipping_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('warehouse_shipping_mode_list.dwt'), '', array('filter' => $shipping_list['filter'], 'page_count' => $shipping_list['page_count']));
}
else if ($_REQUEST['act'] == 'freight') {
	admin_priv('warehouse_manage');
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$sql = 'select s.*, wf.id, wf.configure, rw1.region_name as region_name1, rw2.region_name as region_name2 from ' . $ecs->table('warehouse_freight') . ' as wf' . ' LEFT JOIN ' . $ecs->table('shipping') . ' as s ON wf.shipping_id = s.shipping_id' . ' LEFT JOIN ' . $ecs->table('region_warehouse') . ' as rw1 ON wf.warehouse_id = rw1.region_id' . ' LEFT JOIN ' . $ecs->table('region_warehouse') . ' as rw2 ON wf.region_id = rw2.regionId' . ' where wf.id = \'' . $id . '\'';
	$row = $db->getRow($sql);
	$set_modules = 1;
	include_once ROOT_PATH . 'includes/modules/shipping/' . $row['shipping_code'] . '.php';
	$fields = unserialize($row['configure']);
	if ($row['support_cod'] && ($fields[count($fields) - 1]['name'] != 'pay_fee')) {
		$fields[] = array('name' => 'pay_fee', 'value' => 0);
	}

	foreach ($fields as $key => $val) {
		if ($val['name'] == 'basic_fee') {
			$val['name'] = 'base_fee';
		}

		if ($val['name'] == 'item_fee') {
			$item_fee = 1;
		}

		if ($val['name'] == 'fee_compute_mode') {
			$smarty->assign('fee_compute_mode', $val['value']);
			unset($fields[$key]);
		}
		else {
			$fields[$key]['name'] = $val['name'];
			$fields[$key]['label'] = $_LANG[$val['name']];
		}
	}

	if (empty($item_fee)) {
		$field = array('name' => 'item_fee', 'value' => '0', 'label' => empty($_LANG['item_fee']) ? '' : $_LANG['item_fee']);
		array_unshift($fields, $field);
	}

	$Province_list = get_regions(1, 1);
	$smarty->assign('Province_list', $Province_list);
	$smarty->assign('fields', $fields);
	$smarty->assign('ur_here', '运费');
	$smarty->assign('action_link', array('href' => 'warehouse.php?act=ship_list', 'text' => '返回配送列表'));
	$smarty->assign('region_name1', $row['region_name1']);
	$smarty->assign('region_name2', $row['region_name2']);
	$smarty->assign('shipping_area', $row);
	$smarty->assign('form_action', 'update');
	assign_query_info();
	$smarty->display('warehouse_shipping_mode_info.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	admin_priv('warehouse_manage');
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$sql = 'select shipping_id from ' . $ecs->table('warehouse_freight') . ' where id = \'' . $id . '\'';
	$shipping = $db->getOne($sql);
	$shipping_code = $db->getOne('SELECT shipping_code FROM ' . $ecs->table('shipping') . ' WHERE shipping_id=\'' . $shipping . '\'');
	$plugin = '../includes/modules/shipping/' . $shipping_code . '.php';

	if (!file_exists($plugin)) {
		sys_msg($_LANG['not_find_plugin'], 1);
	}
	else {
		$set_modules = 1;
		include_once $plugin;
	}

	$config = array();

	foreach ($modules[0]['configure'] as $key => $val) {
		$config[$key]['name'] = $val['name'];
		$config[$key]['value'] = $_POST[$val['name']];
	}

	$count = count($config);
	$config[$count]['name'] = 'free_money';
	$config[$count]['value'] = empty($_POST['free_money']) ? '' : $_POST['free_money'];
	$count++;
	$config[$count]['name'] = 'fee_compute_mode';
	$config[$count]['value'] = empty($_POST['fee_compute_mode']) ? '' : $_POST['fee_compute_mode'];

	if ($modules[0]['cod']) {
		$count++;
		$config[$count]['name'] = 'pay_fee';
		$config[$count]['value'] = make_semiangle(empty($_POST['pay_fee']) ? '' : $_POST['pay_fee']);
	}

	$sql = 'UPDATE ' . $ecs->table('warehouse_freight') . ' SET configure=\'' . serialize($config) . '\' ' . 'WHERE id=\'' . $id . '\'';
	$db->query($sql);
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'warehouse_shipping_mode.php?act=freight&id=' . $id);
	sys_msg($_LANG['edit_success'], 0, $link);
}
else if ($_REQUEST['act'] == 'batch_remove') {
	admin_priv('warehouse_manage');

	if (isset($_POST['checkboxes'])) {
		get_freight_batch_remove($_POST['checkboxes']);
		$count = count($_POST['checkboxes']);
		$lnk[] = array('text' => $_LANG['go_back'], 'href' => 'warehouse_shipping_mode.php?act=list');
		sys_msg(sprintf($_LANG['batch_remove_success'], $count), 0, $lnk);
	}
	else {
		$lnk[] = array('text' => $_LANG['go_back'], 'href' => 'warehouse_shipping_mode.php?act=list');
		sys_msg($_LANG['no_select_user'], 0, $lnk);
	}
}
else if ($_REQUEST['act'] == 'remove') {
	admin_priv('warehouse_manage');
	$sql = 'DELETE FROM ' . $ecs->table('warehouse_freight') . ' WHERE id = \'' . $_GET['id'] . '\'';
	$db->query($sql);
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'warehouse_shipping_mode.php?act=list');
	$_LANG['remove_success'] = '删除成功';
	sys_msg(sprintf($_LANG['remove_success'], $username), 0, $link);
}

?>
