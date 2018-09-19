<?php
//zend by 商创网络 Q Q:123456    禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . '/includes/lib_area.php';
$area_info = get_area_info($province_id);
$area_id = $area_info['region_id'];
$where = 'regionId = \'' . $province_id . '\'';
$date = array('parent_id');
$region_id = get_table_date('region_warehouse', $where, $date, 2);
$brand = isset($_REQUEST['brand']) && 0 < intval($_REQUEST['brand']) ? intval($_REQUEST['brand']) : 0;
$price_max = isset($_REQUEST['price_max']) && 0 < intval($_REQUEST['price_max']) ? intval($_REQUEST['price_max']) : 0;
$price_min = isset($_REQUEST['price_min']) && 0 < intval($_REQUEST['price_min']) ? intval($_REQUEST['price_min']) : 0;
$filter_attr_str = isset($_REQUEST['filter_attr']) ? htmlspecialchars(trim($_REQUEST['filter_attr'])) : '0';
$filter_attr_str = trim(urldecode($filter_attr_str));
$filter_attr_str = preg_match('/^[\\d\\.]+$/', $filter_attr_str) ? $filter_attr_str : '';
$filter_attr = empty($filter_attr_str) ? '' : explode('.', $filter_attr_str);
$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
$default_sort_order_type = $_CFG['sort_order_type'] == '0' ? 'goods_id' : ($_CFG['sort_order_type'] == '1' ? 'shop_price' : 'last_update');
$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('goods_id', 'shop_price', 'last_update')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
$order = isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')) ? trim($_REQUEST['order']) : $default_sort_order_method;
$page = !empty($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

if (defined('THEME_EXTENSION')) {
	$size = 24;
}
else {
	$size = !empty($_CFG['page_size']) && 0 < intval($_CFG['page_size']) ? intval($_CFG['page_size']) : 10;
}

$merchant_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$shop_date = array('shop_id');
$shop_where = 'user_id = \'' . $merchant_id . '\'';
$shop_id = get_table_date('merchants_shop_information', $shop_where, $shop_date);
if ($merchant_id == 0 || $shop_id < 1) {
	header("Location: index.php\n");
	exit();
}

$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $sort . '-' . $order . '-' . $page . '-' . $size . '-' . $merchant_id . '-' . $_CFG['lang']));
$shop_info = get_shop_name($merchant_id, 3);

if (!$smarty->is_cached('merchants_shop.dwt', $cache_id)) {
	assign_template('', array(), $merchant_id);
	$position = assign_ur_here(0, $shop_info['shop_name']);
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);
	$categories_pro = get_category_tree_leve_one();
	$smarty->assign('categories_pro', $categories_pro);
	$goods_list = get_shop_goods_cmt_list($merchant_id, $region_id, $area_id, $price_min, $price_max, $page, $size, $sort, $order);
	$smarty->assign('goods_list', $goods_list);
	$count = get_shop_goods_cmt_count($merchant_id, $price_min, $price_max, $sort);
	$smarty->assign('count', $count);

	if (0 < $merchant_id) {
		$merchants_goods_comment = get_merchants_goods_comment($merchant_id);
	}

	$smarty->assign('merch_cmt', $merchants_goods_comment);
	$build_uri = array('urid' => $merchant_id, 'append' => $shop_info['shop_name']);
	$domain_url = get_seller_domain_url($merchant_id, $build_uri);
	$shop_info['store_url'] = $domain_url['domain_name'];
	$smarty->assign('shop_info', $shop_info);
	$brand_list = get_shop_brand_list($merchant_id);
	$smarty->assign('helps', get_shop_help());
	$address = get_shop_address_info($merchant_id);
	$smarty->assign('brand_list', $brand_list);
	$smarty->assign('address', $address);
	$sql = 'select * from ' . $ecs->table('seller_shopinfo') . (' where ru_id=\'' . $merchant_id . '\'');
	$basic_info = $db->getRow($sql);
	$basic_info['logo_thumb'] = str_replace('../', '', $basic_info['logo_thumb']);

	if ($GLOBALS['_CFG']['open_oss'] == 1) {
		$bucket_info = get_bucket_info();
		$basic_info['logo_thumb'] = $bucket_info['endpoint'] . $basic_info['logo_thumb'];
	}

	$smarty->assign('basic_info', $basic_info);
	$smarty->assign('merchant_id', $merchant_id);
	$smarty->assign('script_name', 'merchants_store_shop');
	$limit = 0;

	if (defined('THEME_EXTENSION')) {
		$limit = 7;
	}

	$store_best_list = get_shop_goods_count_list(0, $region_id, $area_id, 1, 'store_best', 0, $limit);
	$smarty->assign('store_best_list', $store_best_list);

	if (defined('THEME_EXTENSION')) {
		$collect_store = 0;

		if (0 < $_SESSION['user_id']) {
			$sql = 'SELECT rec_id FROM ' . $GLOBALS['ecs']->table('collect_store') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND ru_id = \'' . $merchant_id . '\' ';
			$collect_store = $GLOBALS['db']->getOne($sql);
		}

		$smarty->assign('collect_store', $collect_store);
	}

	assign_pager('merchants_store_shop', 0, $count, $size, $sort, $order, $page, '', $brand, $price_min, $price_max, 'list', $filter_attr_str, '', '', $merchant_id);
	assign_dynamic('merchants_store_shop');
}

$smarty->display('merchants_shop.dwt', $cache_id);

?>
