<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
$seller_type = isset($_REQUEST['seller_type']) ? $base->get_intval($_REQUEST['seller_type']) : -1;
$seller_id = isset($_REQUEST['seller_id']) ? $base->get_intval($_REQUEST['seller_id']) : -1;
$attr_id = isset($_REQUEST['attr_id']) ? $base->get_intval($_REQUEST['attr_id']) : -1;
$cat_id = isset($_REQUEST['cat_id']) ? $base->get_intval($_REQUEST['cat_id']) : -1;
$attr_name = isset($_REQUEST['attr_name']) ? $base->get_addslashes($_REQUEST['attr_name']) : -1;
$attr_type = isset($_REQUEST['attr_type']) ? $base->get_intval($_REQUEST['attr_type']) : -1;
$val = array('seller_type' => $seller_type, 'seller_id' => $seller_id, 'attr_id' => $attr_id, 'cat_id' => $cat_id, 'attr_name' => $attr_name, 'attr_type' => $attr_type, 'attribute_select' => $data, 'page_size' => $page_size, 'page' => $page, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'format' => $format);
$attribute = new \app\controller\attribute($val);

switch ($method) {
case 'dsc.goodstype.list.get':
	$table = array('goodstype' => 'goods_type');
	$result = $attribute->get_goodstype_list($table);
	exit($result);
	break;

case 'dsc.goodstype.info.get':
	$table = array('goodstype' => 'goods_type');
	$result = $attribute->get_goodstype_info($table);
	exit($result);
	break;

case 'dsc.goodstype.insert.post':
	$table = array('goodstype' => 'goods_type');
	$result = $attribute->get_goodstype_insert($table);
	exit($result);
	break;

case 'dsc.goodstype.update.post':
	$table = array('goodstype' => 'goods_type');
	$result = $attribute->get_goodstype_update($table);
	exit($result);
	break;

case 'dsc.goodstype.del.get':
	$table = array('goodstype' => 'goods_type');
	$result = $attribute->get_goodstype_delete($table);
	exit($result);
	break;

case 'dsc.attribute.list.get':
	$table = array('attribute' => 'attribute');
	$result = $attribute->get_attribute_list($table);
	exit($result);
	break;

case 'dsc.attribute.info.get':
	$table = array('attribute' => 'attribute');
	$result = $attribute->get_attribute_info($table);
	exit($result);
	break;

case 'dsc.attribute.insert.post':
	$table = array('attribute' => 'attribute');
	$result = $attribute->get_attribute_insert($table);
	exit($result);
	break;

case 'dsc.attribute.update.post':
	$table = array('attribute' => 'attribute');
	$result = $attribute->get_attribute_update($table);
	exit($result);
	break;

case 'dsc.attribute.del.get':
	$table = array('attribute' => 'attribute');
	$result = $attribute->get_attribute_delete($table);
	exit($result);
	break;

default:
	echo '非法接口连接';
	break;
}

?>
