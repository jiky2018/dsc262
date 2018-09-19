<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
$product_id = isset($_REQUEST['product_id']) ? $base->get_intval($_REQUEST['product_id']) : -1;
$goods_id = isset($_REQUEST['goods_id']) ? $base->get_intval($_REQUEST['goods_id']) : -1;
$product_sn = isset($_REQUEST['product_sn']) ? $base->get_addslashes($_REQUEST['product_sn']) : -1;
$bar_code = isset($_REQUEST['bar_code']) ? $base->get_intval($_REQUEST['bar_code']) : -1;
$warehouse_id = isset($_REQUEST['warehouse_id']) ? $base->get_intval($_REQUEST['warehouse_id']) : -1;
$area_id = isset($_REQUEST['area_id']) ? $base->get_intval($_REQUEST['area_id']) : -1;
$val = array('product_id' => $product_id, 'goods_id' => $goods_id, 'product_sn' => $product_sn, 'bar_code' => $bar_code, 'warehouse_id' => $warehouse_id, 'area_id' => $area_id, 'product_select' => $data, 'page_size' => $page_size, 'page' => $page, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'format' => $format);
$product = new \app\controller\product($val);

switch ($method) {
case 'dsc.product.list.get':
	$table = array('product' => 'products');
	$result = $product->get_product_list($table);
	exit($result);
	break;

case 'dsc.product.info.get':
	$table = array('product' => 'products');
	$result = $product->get_product_info($table);
	exit($result);
	break;

case 'dsc.product.insert.post':
	$table = array('product' => 'products');
	$result = $product->get_product_insert($table);
	exit($result);
	break;

case 'dsc.product.update.post':
	$table = array('product' => 'products');
	$result = $product->get_product_update($table);
	exit($result);
	break;

case 'dsc.product.del.get':
	$table = array('product' => 'products');
	$result = $product->get_product_delete($table);
	exit($result);
	break;

case 'dsc.product.warehouse.list.get':
	$table = array('product' => 'products_warehouse');
	$result = $product->get_product_list($table);
	exit($result);
	break;

case 'dsc.product.warehouse.info.get':
	$table = array('product' => 'products_warehouse');
	$result = $product->get_product_info($table);
	exit($result);
	break;

case 'dsc.product.warehouse.insert.post':
	$table = array('product' => 'products_warehouse');
	$result = $product->get_product_insert($table);
	exit($result);
	break;

case 'dsc.product.warehouse.update.post':
	$table = array('product' => 'products_warehouse');
	$result = $product->get_product_update($table);
	exit($result);
	break;

case 'dsc.product.warehouse.del.get':
	$table = array('product' => 'products_warehouse');
	$result = $product->get_product_delete($table);
	exit($result);
	break;

case 'dsc.product.area.list.get':
	$table = array('product' => 'products_area');
	$result = $product->get_product_list($table);
	exit($result);
	break;

case 'dsc.product.area.info.get':
	$table = array('product' => 'products_area');
	$result = $product->get_product_info($table);
	exit($result);
	break;

case 'dsc.product.area.insert.post':
	$table = array('product' => 'products_area');
	$result = $product->get_product_insert($table);
	exit($result);
	break;

case 'dsc.product.area.update.post':
	$table = array('product' => 'products_area');
	$result = $product->get_product_update($table);
	exit($result);
	break;

case 'dsc.product.area.del.get':
	$table = array('product' => 'products_area');
	$result = $product->get_product_delete($table);
	exit($result);
	break;

default:
	echo '非法接口连接';
	break;
}

?>
