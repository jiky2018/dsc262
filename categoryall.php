<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
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
$cache_id = sprintf('%X', crc32($_REQUEST['id'] . '-' . $_CFG['lang']));

if (!$smarty->is_cached('category_all.dwt', $cache_id)) {
	$position = assign_ur_here(0, $_LANG['all_category']);
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);

	for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
		$category_all_left .= '\'category_all_left' . $i . ',';
		$category_all_right .= '\'category_all_right' . $i . ',';
	}

	$smarty->assign('category_all_left', $category_all_left);
	$smarty->assign('category_all_right', $category_all_right);
	$categories_list = get_category_tree_leve_one(0, 1);
	$smarty->assign('categories_list', $categories_list);
	$categories_pro = get_category_tree_leve_one();
	$smarty->assign('categories_pro', $categories_pro);
	$top_goods = get_top10(0, '', 0, $region_id, $area_id);
	$smarty->assign('top_goods', $top_goods);
	$smarty->assign('helps', get_shop_help());
	assign_dynamic('category_all');
	assign_template('c', $catlist);
}

$smarty->display('category_all.dwt', $cache_id);

?>
