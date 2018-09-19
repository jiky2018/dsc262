<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
$brand_id = isset($_REQUEST['brand_id']) ? $base->get_intval($_REQUEST['brand_id']) : -1;
$brand_name = isset($_REQUEST['brand_name']) ? $base->get_addslashes($_REQUEST['brand_name']) : -1;
$val = array('brand_id' => $brand_id, 'brand_name' => $brand_name, 'brand_select' => $data, 'page_size' => $page_size, 'page' => $page, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'format' => $format);
$brand = new \app\controller\brand($val);

switch ($method) {
case 'dsc.brand.list.get':
	$table = array('brand' => 'brand');
	$result = $brand->get_brand_list($table);
	exit($result);
	break;

case 'dsc.brand.info.get':
	$table = array('brand' => 'brand');
	$result = $brand->get_brand_info($table);
	exit($result);
	break;

case 'dsc.brand.insert.post':
	$table = array('brand' => 'brand');
	$result = $brand->get_brand_insert($table);
	exit($result);
	break;

case 'dsc.brand.update.post':
	$table = array('brand' => 'brand');
	$result = $brand->get_brand_update($table);
	exit($result);
	break;

case 'dsc.brand.del.get':
	$table = array('brand' => 'brand');
	$result = $brand->get_brand_delete($table);
	exit($result);
	break;

default:
	echo '非法接口连接';
	break;
}

?>
