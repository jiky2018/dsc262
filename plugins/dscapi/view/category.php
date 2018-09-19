<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
$seller_id = isset($_REQUEST['seller_id']) ? $base->get_intval($_REQUEST['seller_id']) : -1;
$cat_id = isset($_REQUEST['cat_id']) ? $base->get_intval($_REQUEST['cat_id']) : -1;
$parent_id = isset($_REQUEST['parent_id']) ? $base->get_intval($_REQUEST['parent_id']) : -1;
$cat_name = isset($_REQUEST['cat_name']) ? $base->get_addslashes($_REQUEST['cat_name']) : -1;
$val = array('seller_id' => $seller_id, 'cat_id' => $cat_id, 'parent_id' => $parent_id, 'cat_name' => $cat_name, 'category_select' => $data, 'page_size' => $page_size, 'page' => $page, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'format' => $format);
$category = new \app\controller\category($val);

switch ($method) {
case 'dsc.category.list.get':
	$table = array('category' => 'category');
	$result = $category->get_category_list($table);
	exit($result);
	break;

case 'dsc.category.info.get':
	$table = array('category' => 'category');
	$result = $category->get_category_info($table);
	exit($result);
	break;

case 'dsc.category.insert.post':
	$table = array('category' => 'category');
	$result = $category->get_category_insert($table);
	exit($result);
	break;

case 'dsc.category.update.post':
	$table = array('category' => 'category');
	$result = $category->get_category_update($table);
	exit($result);
	break;

case 'dsc.category.del.get':
	$table = array('category' => 'category');
	$result = $category->get_category_delete($table);
	exit($result);
	break;

case 'dsc.category.seller.list.get':
	$table = array('seller' => 'merchants_category');
	$result = $category->get_category_seller_list($table);
	exit($result);
	break;

case 'dsc.category.seller.info.get':
	$table = array('seller' => 'merchants_category');
	$result = $category->get_category_seller_info($table);
	exit($result);
	break;

case 'dsc.category.seller.insert.post':
	$table = array('seller' => 'merchants_category');
	$result = $category->get_category_seller_insert($table);
	exit($result);
	break;

case 'dsc.category.seller.update.post':
	$table = array('seller' => 'merchants_category');
	$result = $category->get_category_seller_update($table);
	exit($result);
	break;

case 'dsc.category.seller.del.get':
	$table = array('seller' => 'merchants_category');
	$result = $category->get_category_seller_delete($table);
	exit($result);
	break;

default:
	echo '非法接口连接';
	break;
}

?>
