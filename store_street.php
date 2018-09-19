<?php
//QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_cat_store_list($cat_id)
{
	$sql = 'SELECT user_shopMain_category AS user_cat, user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE 1 AND user_shopMain_category <> \'\' AND merchants_audit = 1';
	$res = $GLOBALS['db']->getAll($sql);
	$user_id = '';
	$arr = array();

	foreach ($res as $key => $row) {
		$row['cat_str'] = '';
		$row['user_cat'] = explode('-', $row['user_cat']);

		foreach ($row['user_cat'] as $uck => $ucrow) {
			if ($ucrow) {
				$row['user_cat'][$uck] = explode(':', $ucrow);

				if (!empty($row['user_cat'][$uck][0])) {
					$row['cat_str'] .= $row['user_cat'][$uck][0] . ',';
				}
			}
		}

		if ($row['cat_str']) {
			$row['cat_str'] = substr($row['cat_str'], 0, -1);
			$row['cat_str'] = explode(',', $row['cat_str']);
			if (in_array($cat_id, $row['cat_str']) || $cat_id == 0) {
				$user_id .= $row['user_id'] . ',';
			}
		}

		$arr[] = $row;
	}

	if ($user_id) {
		$user_id = substr($user_id, 0, -1);
	}

	return $user_id;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$uachar = '/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i';
if (($ua == '' || preg_match($uachar, $ua)) && !strpos(strtolower($_SERVER['REQUEST_URI']), 'wap')) {
	$Loaction = 'mobile/index.php?r=store';

	if (!empty($Loaction)) {
		ecs_header('Location: ' . $Loaction . "\n");
		exit();
	}
}

require ROOT_PATH . '/includes/lib_area.php';
$area_info = get_area_info($province_id);
$area_id = $area_info['region_id'];
$where = 'regionId = \'' . $province_id . '\'';
$date = array('parent_id');
$region_id = get_table_date('region_warehouse', $where, $date, 2);
$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
$default_sort_order_type = 'shop_id';
$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('shop_id', 'store_score', 'sales_volume')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
$order = isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')) ? trim($_REQUEST['order']) : $default_sort_order_method;
$page = !empty($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$size = 16;

if (isset($_REQUEST['act'])) {
	include_once 'includes/cls_json.php';
	if ($_REQUEST['act'] == 'search_city' || $_REQUEST['act'] == 'search_district') {
		$_POST['area'] = strip_tags(urldecode($_POST['area']));
		$_POST['area'] = json_str_iconv($_POST['area']);
		$result = array('error' => 0, 'message' => '', 'content' => '');
		$json = new JSON();

		if (empty($_POST['area'])) {
			$result['error'] = 1;
			exit($json->encode($result));
		}

		$area = $json->decode($_POST['area']);

		if ($_REQUEST['act'] == 'search_city') {
			$result['region_type'] = 2;
		}
		else {
			$result['region_type'] = 3;
		}

		$smarty->assign('region_type', $result['region_type']);

		if ($area->region_type == 1) {
			$result['store_province'] = $area->region_id;
		}
		else if ($area->region_type == 2) {
			$region = $GLOBALS['db']->getRow('SELECT region_id, parent_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE region_id = \'' . $area->region_id . '\'');
			$store_province = $GLOBALS['db']->getRow('SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE region_id = \'' . $region['parent_id'] . '\'');
			$result['store_province'] = $store_province['region_id'];
			$result['store_city'] = $area->region_id;
		}
		else if ($area->region_type == 3) {
			$region = $GLOBALS['db']->getRow('SELECT region_id, parent_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE region_id = \'' . $area->region_id . '\'');
			$store_city = $GLOBALS['db']->getRow('SELECT region_id, parent_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE region_id = \'' . $region['parent_id'] . '\'');
			$result['store_city'] = $store_city['region_id'];
			$result['store_district'] = $area->region_id;
			$result['store_province'] = $GLOBALS['db']->getOne('SELECT region_id FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE region_id = \'' . $store_city['parent_id'] . '\'');
		}

		$region_list = get_current_region_list($area->region_id, $result['region_type']);
		$smarty->assign('region_list', $region_list);

		if (!$region_list) {
			$result['error'] = 2;
		}

		$result['store_user'] = $area->store_user;

		if ($result['store_province']) {
			$id .= 'store_province-' . $result['store_province'] . '|';
		}

		if ($result['store_city']) {
			$id .= 'store_city-' . $result['store_city'] . '|';
		}

		if ($result['store_district']) {
			$id .= 'store_district-' . $result['store_district'] . '|';
		}

		if ($result['store_user']) {
			$id .= 'store_user-' . $result['store_user'];
		}

		$substr = substr($id, -1);

		if ($substr == '|') {
			$id = substr($id, 0, -1);
		}

		$result['id'] = $id;
		$result['content'] = $smarty->fetch('library/street_region_list.lbi');
		exit($json->encode($result));
	}
	else if ($_REQUEST['act'] == 'search_cat') {
		$_POST['area'] = strip_tags(urldecode($_POST['area']));
		$_POST['area'] = json_str_iconv($_POST['area']);
		$result = array('error' => 0, 'message' => '', 'content' => '');
		$json = new JSON();
		$area = $json->decode($_POST['area']);
		$cat_id = $area->region_id;
		$region_type = $area->region_type;
		$store_province = $area->store_province;
		$store_city = $area->store_city;
		$store_district = $area->store_district;

		if (empty($_POST['area'])) {
			$result['error'] = 1;
			exit($json->encode($result));
		}

		$store_user = get_cat_store_list($cat_id);

		if ($store_user) {
			$su_id = 'store_user-' . $store_user;
		}
		else {
			$su_id = '';
		}

		$id = '';

		if ($store_province) {
			$id .= 'store_province-' . $store_province . '|';
		}

		if ($store_city) {
			$id .= 'store_city-' . $store_city . '|';
		}

		if ($store_district) {
			$id .= 'store_district-' . $store_district . '|';
		}

		if ($store_province || $store_city || $store_district) {
			$id .= $su_id;
		}
		else {
			$id = $su_id;
		}

		$result['id'] = $id;
		$result['store_user'] = $store_user;
		$result['store_province'] = $store_province;
		$result['store_city'] = $store_city;
		$result['store_district'] = $store_district;
		exit($json->encode($result));
	}
}

$smarty->assign('category', 1.0E+19);
$store_search_cmt = isset($_REQUEST['store_search_cmt']) ? intval($_REQUEST['store_search_cmt']) : '';
$_REQUEST['keywords'] = '';
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

if (!$smarty->is_cached('store_street.dwt', $cache_id)) {
	assign_template();
	$position = assign_ur_here(0, '店铺街');
	$smarty->assign('ur_here', $position['ur_here']);
	$smarty->assign('user_id', $_SESSION['user_id']);
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('keywords', htmlspecialchars($_CFG['shop_keywords']));
	$smarty->assign('description', htmlspecialchars($_CFG['shop_desc']));
	$smarty->assign('flash_theme', $_CFG['flash_theme']);
	$smarty->assign('feed_url', $_CFG['rewrite'] == 1 ? 'feed.xml' : 'feed.php');
	$smarty->assign('helps', get_shop_help());
	$categories_pro = get_category_tree_leve_one();
	$smarty->assign('categories_pro', $categories_pro);
	$keywords = htmlspecialchars(stripcslashes($_REQUEST['keywords']));
	$count = get_store_shop_count($keywords);
	$store_shop_list = get_store_shop_list(0, $keywords, $count, $size, $page, $sort, $order, $region_id, $area_id);
	$shop_list = $store_shop_list['shop_list'];
	$smarty->assign('store_shop_list', $shop_list);
	$smarty->assign('pager', $store_shop_list['pager']);
	$smarty->assign('count', $count);
	$smarty->assign('size', $size);
	$province_list = get_current_region_list();
	$smarty->assign('province_list', $province_list);

	if (defined('THEME_EXTENSION')) {
		$store_best_list = get_shop_goods_count_list(0, $region_id, $area_id, 1, 'store_best');
		$smarty->assign('store_best_list', $store_best_list);

		for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
			$store_street_ad .= '\'store_street_ad' . $i . ',';
		}

		$smarty->assign('store_street_ad', $store_street_ad);
	}

	assign_dynamic('store_street');
}

$smarty->display('store_street.dwt', $cache_id);

?>
