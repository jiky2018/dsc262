<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
$region_id = isset($_REQUEST['region_id']) ? $base->get_intval($_REQUEST['region_id']) : -1;
$region_code = isset($_REQUEST['region_code']) ? $base->get_addslashes($_REQUEST['region_code']) : -1;
$parent_id = isset($_REQUEST['parent_id']) ? $base->get_intval($_REQUEST['parent_id']) : -1;
$region_name = isset($_REQUEST['region_name']) ? $base->get_addslashes($_REQUEST['region_name']) : -1;
$region_type = isset($_REQUEST['region_type']) ? $base->get_intval($_REQUEST['region_type']) : -1;
$val = array('region_id' => $region_id, 'region_code' => $region_code, 'parent_id' => $parent_id, 'region_name' => $region_name, 'region_type' => $region_type, 'warehouse_select' => $data, 'page_size' => $page_size, 'page' => $page, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'format' => $format);
$warehouse = new \app\controller\warehouse($val);

switch ($method) {
case 'dsc.warehouse.list.get':
	$table = array('warehouse' => 'region_warehouse');
	$result = $warehouse->get_warehouse_list($table);
	exit($result);
	break;

case 'dsc.warehouse.info.get':
	$table = array('warehouse' => 'region_warehouse');
	$result = $warehouse->get_warehouse_info($table);
	exit($result);
	break;

case 'dsc.warehouse.insert.post':
	$table = array('warehouse' => 'region_warehouse');
	$result = $warehouse->get_warehouse_insert($table);
	exit($result);
	break;

case 'dsc.warehouse.update.post':
	$table = array('warehouse' => 'region_warehouse');
	$result = $warehouse->get_warehouse_update($table);
	exit($result);
	break;

case 'dsc.warehouse.del.get':
	$table = array('warehouse' => 'region_warehouse');
	$result = $warehouse->get_brand_delete($table);
	exit($result);
	break;

default:
	echo '非法接口连接';
	break;
}

?>
