<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
$seller_type = isset($_REQUEST['seller_type']) ? $base->get_intval($_REQUEST['seller_type']) : -1;
$seller_id = isset($_REQUEST['seller_id']) ? $base->get_intval($_REQUEST['seller_id']) : -1;
$cat_id = isset($_REQUEST['cat_id']) ? $base->get_intval($_REQUEST['cat_id']) : -1;
$user_cat = isset($_REQUEST['user_cat']) ? $base->get_intval($_REQUEST['user_cat']) : -1;
$goods_id = isset($_REQUEST['goods_id']) ? $base->get_intval($_REQUEST['goods_id']) : -1;
$brand_id = isset($_REQUEST['brand_id']) ? $base->get_intval($_REQUEST['brand_id']) : -1;
$goods_sn = isset($_REQUEST['goods_sn']) ? $base->get_addslashes($_REQUEST['goods_sn']) : -1;
$bar_code = isset($_REQUEST['bar_code']) ? $base->get_addslashes($_REQUEST['bar_code']) : -1;
$w_id = isset($_REQUEST['w_id']) ? $base->get_intval($_REQUEST['w_id']) : -1;
$a_id = isset($_REQUEST['a_id']) ? $base->get_intval($_REQUEST['a_id']) : -1;
$region_id = isset($_REQUEST['region_id']) ? $base->get_intval($_REQUEST['region_id']) : -1;
$region_sn = isset($_REQUEST['region_sn']) ? $base->get_addslashes($_REQUEST['region_sn']) : -1;
$img_id = isset($_REQUEST['img_id']) ? $base->get_intval($_REQUEST['img_id']) : -1;
$attr_id = isset($_REQUEST['attr_id']) ? $base->get_intval($_REQUEST['attr_id']) : -1;
$goods_attr_id = isset($_REQUEST['goods_attr_id']) ? $base->get_intval($_REQUEST['goods_attr_id']) : -1;
$tid = isset($_REQUEST['tid']) ? $base->get_addslashes($_REQUEST['tid']) : -1;
$val = array('seller_type' => $seller_type, 'seller_id' => $seller_id, 'brand_id' => $brand_id, 'cat_id' => $cat_id, 'user_cat' => $user_cat, 'goods_id' => $goods_id, 'goods_sn' => $goods_sn, 'bar_code' => $bar_code, 'w_id' => $w_id, 'a_id' => $a_id, 'region_id' => $region_id, 'region_sn' => $region_sn, 'img_id' => $img_id, 'attr_id' => $attr_id, 'goods_attr_id' => $goods_attr_id, 'tid' => $tid, 'goods_select' => $data, 'page_size' => $page_size, 'page' => $page, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'format' => $format);
$goods = new \app\controller\goods($val);

switch ($method) {
case 'dsc.goods.list.get':
	$table = array('goods' => 'goods');
	$goods_list = $goods->get_goods_list($table);
	exit($goods_list);
	break;

case 'dsc.goods.info.get':
	$table = array('goods' => 'goods');
	$goods_info = $goods->get_goods_info($table);
	exit($goods_info);
	break;

case 'dsc.goods.insert.post':
	$table = array('goods' => 'goods');
	$result = $goods->get_goods_insert($table);
	exit($result);
	break;

case 'dsc.goods.batchinsert.post':
	$table = array('goods' => 'goods');
	$result = $goods->get_goods_batchinsert($table);
	exit($result);
	break;

case 'dsc.goods.update.post':
	$table = array('goods' => 'goods');
	$result = $goods->get_goods_update($table);
	exit($result);
	break;

case 'dsc.goods.del.get':
	$table = array('goods' => 'goods');
	$result = $goods->get_goods_delete($table);
	exit($result);
	break;

case 'dsc.goods.warehouse.list.get':
	$table = array('warehouse' => 'warehouse_goods');
	$result = $goods->get_goods_warehouse_list($table);
	exit($result);
	break;

case 'dsc.goods.warehouse.info.get':
	$table = array('warehouse' => 'warehouse_goods');
	$result = $goods->get_goods_warehouse_info($table);
	exit($result);
	break;

case 'dsc.goods.warehouse.insert.post':
	$table = array('warehouse' => 'warehouse_goods');
	$result = $goods->get_goods_warehouse_insert($table);
	exit($result);
	break;

case 'dsc.goods.warehouse.update.post':
	$table = array('warehouse' => 'warehouse_goods');
	$result = $goods->get_goods_warehouse_update($table);
	exit($result);
	break;

case 'dsc.goods.warehouse.del.get':
	$table = array('warehouse' => 'warehouse_goods');
	$result = $goods->get_goods_warehouse_delete($table);
	exit($result);
	break;

case 'dsc.goods.area.list.get':
	$table = array('area' => 'warehouse_area_goods');
	$result = $goods->get_goods_area_list($table);
	exit($result);
	break;

case 'dsc.goods.area.info.get':
	$table = array('area' => 'warehouse_area_goods');
	$result = $goods->get_goods_area_info($table);
	exit($result);
	break;

case 'dsc.goods.area.insert.post':
	$table = array('area' => 'warehouse_area_goods');
	$result = $goods->get_goods_area_insert($table);
	exit($result);
	break;

case 'dsc.goods.area.update.post':
	$table = array('area' => 'warehouse_area_goods');
	$result = $goods->get_goods_area_update($table);
	exit($result);
	break;

case 'dsc.goods.area.del.get':
	$table = array('area' => 'warehouse_area_goods');
	$result = $goods->get_goods_area_delete($table);
	exit($result);
	break;

case 'dsc.goods.gallery.list.get':
	$table = array('gallery' => 'goods_gallery');
	$result = $goods->get_goods_gallery_list($table);
	exit($result);
	break;

case 'dsc.goods.gallery.info.get':
	$table = array('gallery' => 'goods_gallery');
	$result = $goods->get_goods_gallery_info($table);
	exit($result);
	break;

case 'dsc.goods.gallery.insert.post':
	$table = array('gallery' => 'goods_gallery');
	$result = $goods->get_goods_gallery_insert($table);
	exit($result);
	break;

case 'dsc.goods.gallery.update.post':
	$table = array('gallery' => 'goods_gallery');
	$result = $goods->get_goods_gallery_update($table);
	exit($result);
	break;

case 'dsc.goods.gallery.del.get':
	$table = array('gallery' => 'goods_gallery');
	$result = $goods->get_goods_gallery_delete($table);
	exit($result);
	break;

case 'dsc.goods.attr.list.get':
	$table = array('attr' => 'goods_attr');
	$result = $goods->get_goods_attr_list($table);
	exit($result);
	break;

case 'dsc.goods.attr.info.get':
	$table = array('attr' => 'goods_attr');
	$result = $goods->get_goods_attr_info($table);
	exit($result);
	break;

case 'dsc.goods.attr.insert.post':
	$table = array('attr' => 'goods_attr');
	$result = $goods->get_goods_attr_insert($table);
	exit($result);
	break;

case 'dsc.goods.attr.update.post':
	$table = array('attr' => 'goods_attr');
	$result = $goods->get_goods_attr_update($table);
	exit($result);
	break;

case 'dsc.goods.attr.del.get':
	$table = array('attr' => 'goods_attr');
	$result = $goods->get_goods_attr_delete($table);
	exit($result);
	break;

case 'dsc.goods.freight.list.get':
	$table = array(
		array('table' => 'goods_transport', 'alias' => 'gt'),
		array('table' => 'goods_transport_extend', 'alias' => 'gted'),
		array('table' => 'goods_transport_express', 'alias' => 'gtes')
		);
	$result = $goods->get_goods_freight_list($table);
	exit($result);
	break;

case 'dsc.goods.freight.info.get':
	$table = array(
		array('table' => 'goods_transport', 'alias' => 'gt'),
		array('table' => 'goods_transport_extend', 'alias' => 'gted'),
		array('table' => 'goods_transport_express', 'alias' => 'gtes')
		);
	$result = $goods->get_goods_freight_info($table);
	exit($result);
	break;

case 'dsc.goods.freight.insert.post':
	$table = array('goods_transport', 'goods_transport_extend', 'goods_transport_express');
	$result = $goods->get_goods_freight_insert($table);
	exit($result);
	break;

case 'dsc.goods.freight.update.post':
	$table = array('goods_transport', 'goods_transport_extend', 'goods_transport_express');
	$result = $goods->get_goods_freight_update($table);
	exit($result);
	break;

case 'dsc.goods.freight.del.get':
	$table = array('goods_transport', 'goods_transport_extend', 'goods_transport_express');
	$result = $goods->get_goods_freight_delete($table);
	exit($result);
	break;

case 'dsc.goods.notification.update.post':
	$table = array('goods', 'order');
	$result = $goods->get_goods_notification_update($table);
	exit($result);
	break;

default:
	echo '非法接口连接';
	break;
}

?>
