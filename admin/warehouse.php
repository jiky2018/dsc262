<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function new_region_id($region_id)
{
	$regions_id = array();

	if (empty($region_id)) {
		return $regions_id;
	}

	$sql = 'SELECT region_id FROM ' . $GLOBALS['ecs']->table('region_warehouse') . 'WHERE parent_id ' . db_create_in($region_id);
	$result = $GLOBALS['db']->getAll($sql);

	foreach ($result as $val) {
		$regions_id[] = $val['region_id'];
	}

	return $regions_id;
}

function get_region_type_area($type = 1)
{
	$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE region_type = \'' . $type . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key] = $row;
		$region_id = get_table_date('region_warehouse', 'regionId = \'' . $row['region_id'] . '\'', array('region_id'), 2);

		if (0 < $region_id) {
			unset($arr[$key]);
		}
	}

	return $arr;
}

function get_region_name_area($region_id = 0)
{
	$sql = 'select region_name from ' . $GLOBALS['ecs']->table('region') . (' where region_id = \'' . $region_id . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function get_freight_warehouse_id($region_id)
{
	$sql = 'SELECT region_id, parent_id, region_name FROM ' . $GLOBALS['ecs']->table('region_warehouse') . (' WHERE region_id = \'' . $region_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['region_id'] = $row['region_id'];
		$arr[$key]['parent_id'] = $row['parent_id'];
		$arr[$key]['region_name'] = $row['region_name'];
		$arr[$key]['parent'] = get_freight_warehouse_id($row['parent_id']);

		if ($arr[$key]['parent_id'] == 0) {
			$arr[$key]['parent'] = $row['region_id'];
		}
	}

	return $arr;
}

function get_parent_freight($parent)
{
	$arr = array();

	for ($i = 0; $i < count($parent); $i++) {
		if (is_array($parent[$i]['parent'])) {
			$arr[$i]['parent'] = get_parent_freight($parent[$i]['parent']);
		}
		else {
			$arr[$i]['parent'] = $parent[$i]['parent'];
		}
	}

	return $arr;
}

function array_switch($array)
{
	static $result_array = array();

	if (count($array) == 0) {
		return false;
	}

	foreach ($array as $value) {
		if (is_array($value)) {
			array_switch($value);
		}
		else {
			$result_array[] = $value;
		}
	}

	return $result_array;
}

function get_haveget_parent()
{
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('region_warehouse'), $db, 'region_id', 'region_name');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$adminru = get_admin_ru_id();

if ($_REQUEST['act'] == 'list') {
	admin_priv('warehouse_manage');
	$smarty->assign('menu_select', array('action' => '01_system', 'current' => '09_warehouse_management'));
	$smarty->assign('action_link2', array('href' => 'warehouse.php?act=ship_list', 'text' => '仓库运费模板'));
	$region_id = empty($_REQUEST['pid']) ? 0 : intval($_REQUEST['pid']);
	$regionId = empty($_REQUEST['regionId']) ? 0 : intval($_REQUEST['regionId']);
	$smarty->assign('parent_id', $region_id);

	if (0 < $region_id) {
		$pid = $exc->get_name($region_id, 'parent_id');
		$action_link = array('text' => $_LANG['back_page'], 'href' => 'warehouse.php?act=list&&pid=' . $pid);
	}
	else {
		$action_link = '';
	}

	$smarty->assign('action_link', $action_link);

	if ($region_id == 0) {
		$region_type = 0;
	}
	else {
		$region_type = $exc->get_name($region_id, 'region_type') + 1;
	}

	$smarty->assign('region_type', $region_type);
	$region_arr = area_warehouse_list($region_id);
	$smarty->assign('region_arr', $region_arr);

	if (0 < $region_id) {
		$region_name = $exc->get_name($region_id);
		$region_here = '[ ' . $region_name . ' ] ';
		$regionType = $db->getOne('SELECT MAX(region_type) FROM' . $ecs->table('region_warehouse'));
		$parent_id = 0;

		for ($i = 0; $i < $regionType; $i++) {
			$sql = 'SELECT parent_id ,region_id FROM ' . $ecs->table('region_warehouse') . (' WHERE region_id = \'' . $region_id . '\'');
			$region_info = $db->getRow($sql);

			if ($region_info['parent_id'] == 0) {
				$parent_id = $region_info['region_id'];
				continue;
			}
			else {
				$region_id = $region_info['parent_id'];
			}
		}

		$area_name = $exc->get_name($region_id);
		$area = '[ ' . $area_name . ' ] ';
	}
	else {
		$area = $_LANG['country'];
		$region_here = $_LANG['country'];
	}

	$smarty->assign('area_here', $area);
	$smarty->assign('region_here', $region_here);

	if (0 < $regionId) {
		$ecs_region = area_list($regionId, 1);
	}
	else {
		$ecs_region = get_region_type_area();
	}

	$smarty->assign('ecs_region', $ecs_region);
	$smarty->assign('dialog_region', $regionId);
	$adminru = get_admin_ru_id();
	$ruCat = '';

	if ($adminru['ru_id'] == 0) {
		$smarty->assign('priv_ru', 1);
	}
	else {
		$smarty->assign('priv_ru', 0);
	}

	$lang_area_list = $_LANG['05_area_list_01'];

	if (0 < $region_id) {
		$lang_area_list .= '&nbsp;&nbsp;--&nbsp;&nbsp;' . $area;
	}

	$smarty->assign('ur_here', $lang_area_list);
	$smarty->assign('full_page', 1);
	$smarty->assign('freight_model', $GLOBALS['_CFG']['freight_model']);
	assign_query_info();

	if ($region_type == 0) {
		$smarty->display('warehouse_list_stair.dwt');
	}
	else {
		$smarty->display('warehouse_list.dwt');
	}
}
else if ($_REQUEST['act'] == 'ship_list') {
	admin_priv('warehouse_manage');
	$smarty->assign('menu_select', array('action' => '01_system', 'current' => 'warehouse_ship_list'));
	$sql = ' select ru_id, shipping_id from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id=\'' . $adminru['ru_id'] . '\' ';
	$seller_shopinfo = $GLOBALS['db']->getRow($sql);
	$smarty->assign('seller_shopinfo', $seller_shopinfo);
	$shipping_list = warehouse_shipping_list();

	foreach ($shipping_list as $key => $val) {
		$sql = 'SELECT shipping_desc, insure, support_cod FROM ' . $ecs->table('shipping') . (' WHERE shipping_id = \'' . $val['shipping_id'] . '\' ');
		$shipping_info = $db->getRow($sql);
		$shipping_list[$key]['shipping_desc'] = $shipping_info['shipping_desc'];

		if ($val['shipping_id'] == 17) {
			unset($shipping_list[$key]);
		}
	}

	$smarty->assign('shipping_list', $shipping_list);
	$action_link = array('text' => '仓库列表', 'href' => 'warehouse.php?act=list');
	$smarty->assign('action_link', $action_link);
	$action_link2 = array('text' => '仓库运费模板', 'href' => 'warehouse.php?act=ship_list');
	$smarty->assign('action_link2', $action_link2);
	$smarty->assign('ur_here', '仓库运费模板');
	$smarty->display('warehouse_shipping_list.dwt');
}
else if ($_REQUEST['act'] == 'remove_tpl') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('content' => '', 'id' => '');
	$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$shipping_id = !empty($_REQUEST['shipping_id']) ? intval($_REQUEST['shipping_id']) : 0;
	$sql = 'DELETE FROM ' . $ecs->table('warehouse_freight_tpl') . (' WHERE id=\'' . $id . '\'');

	if ($db->query($sql)) {
		$result['content'] = 1;
		$result['id'] = $id;
	}
	else {
		$result['content'] = 0;
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'multi_remove') {
	$ids = implode(',', $_REQUEST['checkboxes']);
	$sql = 'DELETE FROM ' . $ecs->table('warehouse_freight_tpl') . ' WHERE id in (' . $ids . ')';

	if ($db->query($sql)) {
		$data = '移除成功';
	}
	else {
		$data = '移除失败';
	}

	$links[0] = array('href' => 'warehouse.php?act=tpl_list&shipping_id=' . intval($_REQUEST['shipping_id']), 'text' => $_LANG['go_back']);
	sys_msg($data, 0, $links);
}

if ($_REQUEST['act'] == 'tpl_list') {
	$shipping_id = intval($_REQUEST['shipping_id']);
	$sql = 'SELECT shipping_code,shipping_name FROM ' . $ecs->table('shipping') . ' WHERE shipping_id=' . $shipping_id;
	$shipping = $db->getRow($sql);
	$shipping_code = $shipping['shipping_code'];
	$list = get_ship_tpl_list($shipping_id, $adminru['ru_id']);
	$smarty->assign('areas', $list);
	$smarty->assign('ur_here', '仓库运费模板列表 - ' . $shipping['shipping_name']);
	$smarty->assign('action_link2', array('href' => 'warehouse.php?act=ship_tpl&shipping_id=' . $shipping_id, 'text' => '新增运费模板'));
	$smarty->assign('action_link', array('href' => 'warehouse.php?act=ship_list', 'text' => '返回配送列表'));
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->assign('shipping_id', $shipping_id);
	$smarty->assign('shipping_code', $shipping_code);
	$smarty->display('warehouse_shipping_tpl_list.dwt');
}
else if ($_REQUEST['act'] == 'ship_tpl') {
	$shipping_id = isset($_REQUEST['shipping_id']) ? $_REQUEST['shipping_id'] : 0;
	$smarty->assign('shipping_id', $shipping_id);
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
	$sql = 'SELECT a.shipping_name, a.shipping_code, a.support_cod, b.* ' . ' FROM ' . $ecs->table('warehouse_freight_tpl') . ' AS b ' . ' left join ' . $ecs->table('shipping') . ' AS a on a.shipping_id=b.shipping_id ' . ' WHERE b.id=\'' . $id . '\' and b.shipping_id=\'' . $shipping_id . '\' and b.user_id=\'' . $adminru['ru_id'] . '\'';
	$row = $db->getRow($sql);

	if (!empty($row)) {
		$smarty->assign('ur_here', '仓库运费模板编辑 - ' . $row['shipping_name']);
		if (!empty($row) && $row['shipping_code'] == 'cac') {
			$sql = 'SELECT * FROM ' . $ecs->table('shipping_point') . (' WHERE shipping_area_id=\'' . $row['shipping_area_id'] . '\'');
			$row['point'] = $db->getAll($sql);
		}

		$set_modules = 1;
		include_once ROOT_PATH . 'includes/modules/shipping/' . $row['shipping_code'] . '.php';
		$fields = unserialize($row['configure']);
		if ($row['support_cod'] && $fields[count($fields) - 1]['name'] != 'pay_fee') {
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

		$smarty->assign('shipping_area', $row);
	}
	else {
		$shipping = $db->getRow('SELECT shipping_name, shipping_code FROM ' . $ecs->table('shipping') . (' WHERE shipping_id=\'' . $shipping_id . '\''));
		$smarty->assign('ur_here', '仓库运费模板_' . $shipping['shipping_name']);
		$set_modules = 1;
		include_once ROOT_PATH . 'includes/modules/shipping/' . $shipping['shipping_code'] . '.php';
		$fields = array();

		foreach ($modules[0]['configure'] as $key => $val) {
			$fields[$key]['name'] = $val['name'];
			$fields[$key]['value'] = $val['value'];
			$fields[$key]['label'] = $_LANG[$val['name']];
		}

		$count = count($fields);
		$fields[$count]['name'] = 'free_money';
		$fields[$count]['value'] = '0';
		$fields[$count]['label'] = $_LANG['free_money'];

		if ($modules[0]['cod']) {
			$count++;
			$fields[$count]['name'] = 'pay_fee';
			$fields[$count]['value'] = '0';
			$fields[$count]['label'] = $_LANG['pay_fee'];
		}

		$shipping_area['shipping_id'] = 0;
		$shipping_area['free_money'] = 0;
		$smarty->assign('shipping_area', array('shipping_id' => $_REQUEST['shipping_id'], 'shipping_code' => $shipping['shipping_code']));
	}

	$smarty->assign('action_link2', array('href' => 'warehouse.php?act=tpl_list&shipping_id=' . $shipping_id, 'text' => '返回模板列表'));
	$warehouse_list = get_warehouse_list_goods();
	$sql = ' SELECT warehouse_id from ' . $ecs->table('warehouse_freight_tpl') . (' where id=\'' . $id . '\' and shipping_id=\'' . $shipping_id . '\' and user_id=\'') . $adminru['ru_id'] . '\' ';
	$warehouses = $db->getOne($sql);

	foreach ($warehouse_list as $key => $value) {
		if (!empty($warehouses)) {
			if (in_array($value['region_id'], explode(',', $warehouses))) {
				$warehouse_list[$key]['check_status'] = 1;
			}
		}
	}

	$smarty->assign('warehouse_list', $warehouse_list);
	$smarty->assign('warehouse_count', count($warehouse_list) + 1);
	$smarty->assign('form_action', 'freight_tpl_insert');
	$shipping_list = warehouse_shipping_list();
	$smarty->assign('shipping_list', $shipping_list);
	$regions = array();
	$sql = ' SELECT region_id from ' . $ecs->table('warehouse_freight_tpl') . (' where id=\'' . $id . '\' and shipping_id=\'' . $shipping_id . '\' and user_id=\'') . $adminru['ru_id'] . '\' ';
	$region_list = $res = $db->getOne($sql);

	if (!empty($region_list)) {
		$sql = ' SELECT region_id,region_name from ' . $ecs->table('region') . ' where region_id in (' . $region_list . ') ';
		$res = $db->query($sql);

		while ($arr = $db->fetchRow($res)) {
			$regions[$arr['region_id']] = $arr['region_name'];
		}
	}

	assign_query_info();
	$smarty->assign('fields', $fields);
	$Province_list = get_regions(1, 1);
	$smarty->assign('Province_list', $Province_list);
	$smarty->assign('regions', $regions);
	$smarty->display('warehouse_shipping_tpl_info.dwt');
}
else if ($_REQUEST['act'] == 'freight_tpl_insert') {
	$warehouse_id = empty($_REQUEST['warehouse_id']) ? '' : implode(',', $_REQUEST['warehouse_id']);
	$shipping_id = empty($_REQUEST['shipping_id']) ? '' : intval($_REQUEST['shipping_id']);
	$tpl_name = empty($_REQUEST['tpl_name']) ? '' : trim($_REQUEST['tpl_name']);
	$id = empty($_REQUEST['id']) ? '' : intval($_REQUEST['id']);
	$rId = empty($_REQUEST['regions']) ? '' : implode(',', $_REQUEST['regions']);
	$regionId = $rId;
	if ($shipping_id == 0 || empty($tpl_name) || empty($warehouse_id) || empty($regionId)) {
		$add_to_mess = '请将信息填写完整';
		$add_edit = 'act=ship_tpl&shipping_id=' . $shipping_id;
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'warehouse.php?' . $add_edit);
		sys_msg($add_to_mess, 0, $link);
	}
	else {
		$add_to_mess = '运费添加成功';
	}

	$adminru = get_admin_ru_id();

	if (!empty($id)) {
		$where = ' and id <> ' . $id . ' ';
	}
	else {
		$where = '';
	}

	$sql = 'select warehouse_id,region_id from ' . $ecs->table('warehouse_freight_tpl') . (' where shipping_id = \'' . $shipping_id . '\' and user_id = \'') . $adminru['ru_id'] . '\'' . $where;
	$res = $db->getAll($sql);

	foreach ($res as $key => $val) {
		$warehouse_state = array_intersect(explode(',', $val['warehouse_id']), explode(',', $warehouse_id));
		$region_state = array_intersect(explode(',', $val['region_id']), explode(',', $rId));
		if ($warehouse_state && $region_state) {
			$add_to_mess = '模板抵达地区已存在！';
			$add_edit = 'act=tpl_list&shipping_id=' . $shipping_id;
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'warehouse.php?' . $add_edit);
			sys_msg($add_to_mess, 0, $link);
		}
	}

	$shipping_code = $db->getOne('SELECT shipping_code FROM ' . $ecs->table('shipping') . (' WHERE shipping_id=\'' . $shipping_id . '\''));
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

	$other['tpl_name'] = $tpl_name;
	$other['warehouse_id'] = $warehouse_id;
	$other['shipping_id'] = $shipping_id;
	$other['region_id'] = $regionId;
	$other['configure'] = serialize($config);
	$other['user_id'] = $adminru['ru_id'];
	$sql = ' select * from ' . $ecs->table('warehouse_freight_tpl') . (' where shipping_id=\'' . $shipping_id . '\' and user_id=\'') . $adminru['ru_id'] . '\' ';
	$tpl_status = $db->getRow($sql);
	if (empty($tpl_status) || empty($id)) {
		$db->autoExecute($ecs->table('warehouse_freight_tpl'), $other, 'INSERT');
		$add_to_mess = '模板添加成功';
	}
	else {
		$db->autoExecute($ecs->table('warehouse_freight_tpl'), $other, 'UPDATE', ' id= ' . $id . ' and user_id = "' . $adminru['ru_id'] . '" and shipping_id= ' . $shipping_id);
		$add_to_mess = '模板修改成功';
	}

	$add_edit = 'act=tpl_list&shipping_id=' . $shipping_id;
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'warehouse.php?' . $add_edit);
	sys_msg($add_to_mess, 0, $link);
}
else if ($_REQUEST['act'] == 'add_area') {
	check_authz_json('warehouse_manage');
	$parent_id = intval($_POST['parent_id']);
	$region_name = json_str_iconv(trim($_POST['region_name']));
	$region_type = intval($_POST['region_type']);
	$area_here = json_str_iconv(trim($_POST['area_here']));
	$regionId = intval($_POST['regionId']);

	if (0 < $regionId) {
		$region_name = get_region_name_area($regionId);
	}

	if (empty($region_name)) {
		make_json_error($_LANG['region_name_empty']);
	}

	$sql = 'SELECT region_id FROM ' . $ecs->table('region_warehouse') . (' WHERE regionId = \'' . $regionId . '\' ');
	$res = $db->getOne($sql);
	if (0 < $res && $regionId) {
		make_json_error($_LANG['region_name_exist']);
	}
	else {
		$sql = 'select region_id from ' . $ecs->table('region_warehouse') . (' where region_name = \'' . $region_name . '\' AND region_type <> 1');
		$res = $db->getOne($sql);

		if (0 < $res) {
			make_json_error($_LANG['region_name_exist']);
		}
	}

	$smarty->assign('freight_model', $GLOBALS['_CFG']['freight_model']);
	$sql = 'INSERT INTO ' . $ecs->table('region_warehouse') . ' (regionId, parent_id, region_name, region_code, region_type) ' . ('VALUES (\'' . $regionId . '\', \'' . $parent_id . '\', \'' . $region_name . '\', \'' . $regionId . '\', \'' . $region_type . '\')');

	if ($GLOBALS['db']->query($sql, 'SILENT')) {
		$rw_id1 = $GLOBALS['db']->insert_id();
		admin_log($region_name, 'add', 'area');

		if ($parent_id) {
			$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE parent_id = \'' . $regionId . '\'');
			$region_list1 = $GLOBALS['db']->getAll($sql);

			foreach ($region_list1 as $key => $row) {
				$rl_region_type1 = $region_type + 1;
				$sql = 'INSERT INTO ' . $ecs->table('region_warehouse') . ' (regionId, parent_id, region_name, region_code, region_type) ' . 'VALUES (\'' . $row['region_id'] . ('\', \'' . $rw_id1 . '\', \'') . $row['region_name'] . '\', \'' . $row['region_id'] . ('\', \'' . $rl_region_type1 . '\')');
				$GLOBALS['db']->query($sql);
				$rw_id2 = $GLOBALS['db']->insert_id();
				$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE parent_id = \'' . $row['region_id'] . '\'';
				$region_list2 = $GLOBALS['db']->getAll($sql);

				foreach ($region_list2 as $k => $r) {
					$rl_region_type2 = $rl_region_type1 + 1;
					$sql = 'INSERT INTO ' . $ecs->table('region_warehouse') . ' (regionId, parent_id, region_name, region_code, region_type) ' . 'VALUES (\'' . $r['region_id'] . ('\', \'' . $rw_id2 . '\', \'') . $r['region_name'] . '\', \'' . $r['region_id'] . ('\', \'' . $rl_region_type2 . '\')');
					$GLOBALS['db']->query($sql);
					$rw_id3 = $GLOBALS['db']->insert_id();
					$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE parent_id = \'' . $r['region_id'] . '\'';
					$street_list = $GLOBALS['db']->getAll($sql);

					foreach ($street_list as $sk => $sr) {
						$rl_region_type3 = $rl_region_type2 + 1;
						$sql = 'INSERT INTO ' . $ecs->table('region_warehouse') . ' (regionId, parent_id, region_name, region_code, region_type) ' . 'VALUES (\'' . $sr['region_id'] . ('\', \'' . $rw_id3 . '\', \'') . $sr['region_name'] . '\', \'' . $sr['region_id'] . ('\', \'' . $rl_region_type3 . '\')');
						$GLOBALS['db']->query($sql);
					}
				}
			}
		}

		$region_arr = area_warehouse_list($parent_id);
		$smarty->assign('region_arr', $region_arr);
		$adminru = get_admin_ru_id();
		$ruCat = '';

		if ($adminru['ru_id'] == 0) {
			$smarty->assign('priv_ru', 1);
		}
		else {
			$smarty->assign('priv_ru', 0);
		}

		$smarty->assign('region_type', $region_type);

		if ($region_type == 0) {
			make_json_result($smarty->fetch('warehouse_list_stair.dwt'));
		}
		else {
			$region_name = $exc->get_name($parent_id);
			$region_here = '[ ' . $region_name . ' ] ';
			$smarty->assign('region_here', $region_here);
			$smarty->assign('area_here', $area_here);
			make_json_result($smarty->fetch('warehouse_list.dwt'));
		}
	}
	else {
		make_json_error($_LANG['add_area_error']);
	}
}
else if ($_REQUEST['act'] == 'edit_area_name') {
	check_authz_json('warehouse_manage');
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
else if ($_REQUEST['act'] == 'edit_region_code') {
	check_authz_json('warehouse_manage');
	$id = intval($_POST['id']);
	$region_code = json_str_iconv(trim($_POST['val']));

	if (!$exc->is_only('region_code', $region_code, $id)) {
		make_json_error($_LANG['region_code_exist']);
	}

	if ($exc->edit('region_code = \'' . $region_code . '\'', $id)) {
		admin_log($region_code, 'edit', 'area');
		make_json_result(stripslashes($region_code));
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'drop_area') {
	check_authz_json('warehouse_manage');
	$id = explode('|', $_REQUEST['id']);
	$area_here = $id[1];
	$id = intval($id[0]);
	$sql = 'SELECT * FROM ' . $ecs->table('region_warehouse') . (' WHERE region_id = \'' . $id . '\'');
	$region = $db->getRow($sql);

	if (0 < $region['parent_id']) {
		$area_name = $exc->get_name($region['parent_id']);
		$area = '[ ' . $area_name . ' ] ';
	}
	else {
		$area = $_LANG['country'];
	}

	$smarty->assign('area_here', $area);
	$smarty->assign('freight_model', $GLOBALS['_CFG']['freight_model']);
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

	$regionIds = array();

	if (is_array($delete_region)) {
		foreach ($delete_region as $v) {
			$sql = ' SELECT regionId FROM ' . $ecs->table('region_warehouse') . (' WHERE region_id = \'' . $v . '\' ');
			$regionIds[] = $db->getOne($sql);
		}
	}

	$sql = ' DELETE FROM ' . $ecs->table('warehouse_freight') . ' WHERE region_id ' . db_create_in($regionIds);
	$db->query($sql);
	$sql = ' DELETE FROM ' . $ecs->table('region_warehouse') . ' WHERE region_id ' . db_create_in($delete_region);
	$db->query($sql);

	if ($exc->drop($id)) {
		admin_log(addslashes($region['region_name']), 'remove', 'area');
		$region_arr = area_warehouse_list($region['parent_id']);
		$smarty->assign('region_arr', $region_arr);
		$smarty->assign('region_type', $region['region_type']);
		$adminru = get_admin_ru_id();
		$ruCat = '';

		if ($adminru['ru_id'] == 0) {
			$smarty->assign('priv_ru', 1);
		}
		else {
			$smarty->assign('priv_ru', 0);
		}

		if ($region['region_type'] == 0) {
			make_json_result($smarty->fetch('warehouse_list_stair.dwt'));
		}
		else {
			$region_name = $exc->get_name($region['parent_id']);
			$region_here = '[ ' . $region_name . ' ] ';
			$smarty->assign('region_here', $region_here);
			$smarty->assign('area_here', $area_here);
			make_json_result($smarty->fetch('warehouse_list.dwt'));
		}
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'freight') {
	$sql = ' select ru_id, shipping_id from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id=\'' . $adminru['ru_id'] . '\' ';
	$seller_shopinfo = $GLOBALS['db']->getRow($sql);
	$smarty->assign('seller_shopinfo', $seller_shopinfo);
	$smarty->assign('action_link', array('href' => 'warehouse.php?act=list', 'text' => '返回仓库列表'));
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
	$region_id = $id;
	$parent = get_freight_warehouse_id($region_id);
	$parent = get_parent_freight($parent);
	$parent = array_switch($parent);
	$parent_id = $parent[0];
	$smarty->assign('parent_id', $parent_id);
	$warehouse_list = get_warehouse_list_goods();
	$smarty->assign('warehouse_list', $warehouse_list);
	$sql = 'select region_name from ' . $ecs->table('region_warehouse') . (' where region_id = \'' . $region_id . '\'');
	$region_name = $db->getOne($sql);
	$smarty->assign('region_name', $region_name);
	$smarty->assign('region_id', $region_id);
	$smarty->assign('form_action', 'freight_insert');
	$shipping_list = warehouse_shipping_list();
	$smarty->assign('shipping_list', $shipping_list);
	$sql = 'select regionId from ' . $ecs->table('region_warehouse') . (' where region_id = \'' . $region_id . '\'');
	$regionId = $db->getOne($sql);
	$freight_list = get_warehouse_freight_type($regionId);
	$smarty->assign('freight_list', $freight_list);
	$smarty->assign('regionId', $regionId);
	$smarty->assign('ur_here', '运费管理');
	assign_query_info();
	$smarty->display('warehouse_freight.dwt');
}
else if ($_REQUEST['act'] == 'freight_insert') {
	$return_data = empty($_REQUEST['return_data']) ? 0 : intval($_REQUEST['return_data']);
	$warehouse_id = empty($_REQUEST['warehouse_id']) ? 0 : intval($_REQUEST['warehouse_id']);
	$shipping_id = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
	$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
	$region_id = $id;
	$rId = empty($_REQUEST['rId']) ? 0 : intval($_REQUEST['rId']);
	$regionId = $rId;

	if ($shipping_id == 0) {
		$add_to_mess = '请选择配送方式';
		$add_edit = 'act=freight&region_id=' . $region_id;
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'warehouse.php?' . $add_edit);
		sys_msg($add_to_mess, 0, $link);
	}

	$adminru = get_admin_ru_id();

	if (0 < $adminru['ru_id']) {
		$ru_id = $adminru['ru_id'];
	}
	else {
		$ru_id = 0;
	}

	$ruCat = ' and user_id = \'' . $ru_id . '\'';
	$shipping_code = $db->getOne('SELECT shipping_code FROM ' . $ecs->table('shipping') . (' WHERE shipping_id=\'' . $shipping_id . '\''));
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

	$sql = 'select regionId from ' . $ecs->table('region_warehouse') . (' where regionId = \'' . $regionId . '\'');
	$regionId = $db->getOne($sql);
	$adminru = get_admin_ru_id();
	$other['warehouse_id'] = $warehouse_id;
	$other['shipping_id'] = $shipping_id;
	$other['region_id'] = $regionId;
	$other['configure'] = serialize($config);
	$other['user_id'] = $adminru['ru_id'];
	$sql = 'SELECT id FROM ' . $ecs->table('warehouse_freight') . (' WHERE warehouse_id = \'' . $warehouse_id . '\' and shipping_id = \'' . $shipping_id . '\' and region_id = \'' . $regionId . '\' AND user_id = \'') . $adminru['ru_id'] . '\'';
	$id = $db->getOne($sql);

	if ($id) {
		$db->autoExecute($ecs->table('warehouse_freight'), $other, 'UPDATE', 'id=\'' . $id . '\'');
		$add_to_mess = '运费编辑成功';
	}
	else {
		$db->autoExecute($ecs->table('warehouse_freight'), $other, 'INSERT');
		$add_to_mess = '运费添加成功';
	}

	$add_edit = 'act=freight&id=' . $region_id;
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'warehouse.php?' . $add_edit);
	sys_msg($add_to_mess, 0, $link);
}
else if ($_REQUEST['act'] == 'get_freight_area') {
	check_authz_json('warehouse_manage');
	$shipping_id = !empty($_GET['shipping_id']) ? intval($_GET['shipping_id']) : 0;
	$warehouse_id = !empty($_GET['warehouse_id']) ? intval($_GET['warehouse_id']) : 0;
	$region_id = !empty($_GET['region_id']) ? intval($_GET['region_id']) : 0;
	$sql = 'SELECT s.*, wf.id, wf.configure, rw1.region_name as region_name1, rw2.region_name as region_name2 FROM ' . $ecs->table('warehouse_freight') . ' AS wf' . ' LEFT JOIN ' . $ecs->table('shipping') . ' AS s ON wf.shipping_id = s.shipping_id' . ' LEFT JOIN ' . $ecs->table('region_warehouse') . ' AS rw1 ON wf.warehouse_id = rw1.region_id' . ' LEFT JOIN ' . $ecs->table('region_warehouse') . ' AS rw2 ON wf.region_id = rw2.regionId' . (' WHERE wf.shipping_id = \'' . $shipping_id . '\' AND wf.warehouse_id = \'' . $warehouse_id . '\' AND wf.user_id = \'') . $adminru['ru_id'] . ('\' AND wf.region_id = \'' . $region_id . '\'');
	$shipping = $db->getRow($sql);

	if ($shipping) {
		$set_modules = 1;
		include_once ROOT_PATH . 'includes/modules/shipping/' . $shipping['shipping_code'] . '.php';
		$fields = unserialize($shipping['configure']);
		if ($shipping['support_cod'] && $fields[count($fields) - 1]['name'] != 'pay_fee') {
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

		$return_data = 1;
	}
	else {
		$sql = 'SELECT shipping_name, shipping_code FROM ' . $ecs->table('shipping') . (' WHERE shipping_id=\'' . $shipping_id . '\'');
		$shipping = $db->getRow($sql);
		$set_modules = 1;
		include_once ROOT_PATH . 'includes/modules/shipping/' . $shipping['shipping_code'] . '.php';
		$fields = unserialize($shipping['configure']);
		$fields = array();

		foreach ($modules[0]['configure'] as $key => $val) {
			$fields[$key]['name'] = $val['name'];
			$fields[$key]['value'] = $val['value'];
			$fields[$key]['label'] = $_LANG[$val['name']];
		}

		$count = count($fields);
		$fields[$count]['name'] = 'free_money';
		$fields[$count]['value'] = '0';
		$fields[$count]['label'] = $_LANG['free_money'];

		if ($modules[0]['cod']) {
			$count++;
			$fields[$count]['name'] = 'pay_fee';
			$fields[$count]['value'] = '0';
			$fields[$count]['label'] = $_LANG['pay_fee'];
		}

		$return_data = 0;
	}

	$smarty->assign('shipping_area', array('shipping_id' => $_REQUEST['shipping_id'], 'shipping_code' => $shipping['shipping_code']));
	$smarty->assign('fields', $fields);
	$smarty->assign('return_data', $return_data);
	make_json_result($smarty->fetch('warehouse_freight_area.dwt'));
}

?>
