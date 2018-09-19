<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
$region_id = isset($_REQUEST['region_id']) ? $base->get_intval($_REQUEST['region_id']) : -1;
$parent_id = isset($_REQUEST['parent_id']) ? $base->get_intval($_REQUEST['parent_id']) : -1;
$region_name = isset($_REQUEST['region_name']) ? $base->get_addslashes($_REQUEST['region_name']) : -1;
$region_type = isset($_REQUEST['region_type']) ? $base->get_intval($_REQUEST['region_type']) : -1;
$val = array('region_id' => $region_id, 'parent_id' => $parent_id, 'region_name' => $region_name, 'region_type' => $region_type, 'region_select' => $data, 'page_size' => $page_size, 'page' => $page, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'format' => $format);
$region = new \app\controller\region($val);

switch ($method) {
case 'dsc.region.list.get':
	$table = array('region' => 'region');
	$result = $region->get_region_list($table);
	exit($result);
	break;

case 'dsc.region.info.get':
	$table = array('region' => 'region');
	$result = $region->get_region_info($table);
	exit($result);
	break;

case 'dsc.region.insert.post':
	$table = array('region' => 'region');
	$result = $region->get_region_insert($table);
	exit($result);
	break;

case 'dsc.region.update.post':
	$table = array('region' => 'region');
	$result = $region->get_region_update($table);
	exit($result);
	break;

case 'dsc.region.del.get':
	$table = array('region' => 'region');
	$result = $region->get_region_delete($table);
	exit($result);
	break;

default:
	echo '非法接口连接';
	break;
}

?>
