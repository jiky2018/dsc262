<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
include_once ROOT_PATH . 'includes/lib_transaction.php';
require ROOT_PATH . 'includes/lib_area.php';
$area_info = get_area_info($province_id);
$area_id = $area_info['region_id'];
$where = 'regionId = \'' . $province_id . '\'';
$date = array('parent_id');
$region_id = get_table_date('region_warehouse', $where, $date, 2);
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/shopping_flow.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/package.php';
assign_template();
assign_dynamic('package');
$position = assign_ur_here(0, $_LANG['shopping_package']);
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here', $position['ur_here']);

if (defined('THEME_EXTENSION')) {
	$categories_pro = get_category_tree_leve_one();
	$smarty->assign('categories_pro', $categories_pro);
}

$now = gmtime();
$sql = 'SELECT * FROM ' . $ecs->table('goods_activity') . ' WHERE `start_time` <= \'' . $now . '\' AND `end_time` >= \'' . $now . '\' AND `act_type` = \'4\' AND review_status = 3 ORDER BY `end_time`';
$res = $db->query($sql);
$list = array();

while ($row = $db->fetchRow($res)) {
	$row['start_time'] = local_date('Y-m-d H:i:s', $row['start_time']);
	$row['end_time'] = local_date('Y-m-d H:i:s', $row['end_time']);
	$ext_arr = unserialize($row['ext_info']);
	unset($row['ext_info']);

	if ($ext_arr) {
		foreach ($ext_arr as $key => $val) {
			$row[$key] = $val;
		}
	}

	$sql = 'SELECT pg.package_id, pg.goods_id, pg.goods_number, pg.admin_id, ' . ' g.goods_sn, g.goods_name, g.market_price, g.goods_thumb, ' . ' IFNULL(mp.user_price, g.shop_price * \'' . $_SESSION['discount'] . '\') AS rank_price ' . ' FROM ' . $GLOBALS['ecs']->table('package_goods') . ' AS pg ' . '   LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . '   ON g.goods_id = pg.goods_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . ' WHERE pg.package_id = ' . $row['act_id'] . ' ' . ' ORDER BY pg.goods_id';
	$goods_res = $GLOBALS['db']->getAll($sql);
	$subtotal = 0;
	$goods_number = 0;

	foreach ($goods_res as $key => $val) {
		$goods_res[$key]['goods_thumb'] = get_image_path($val['goods_id'], $val['goods_thumb'], true);
		$goods_res[$key]['market_price'] = price_format($val['market_price']);
		$goods_res[$key]['rank_price'] = price_format($val['rank_price']);
		$goods_res[$key]['url'] = build_uri('goods', array('gid' => $val['goods_id']), $val['goods_name']);
		$goods_res[$key]['package_amounte'] = price_format(($val['rank_price'] * $val['goods_number']) - $row['package_price']);
		$subtotal += $val['rank_price'] * $val['goods_number'];
		$goods_number += $val['goods_number'];
	}

	$row['goods_list'] = $goods_res;
	$row['subtotal'] = price_format($subtotal);
	$row['saving'] = price_format($subtotal - $row['package_price']);
	$row['package_price'] = price_format($row['package_price']);
	$row['package_amounte'] = price_format($subtotal - $row['package_price']);
	$row['package_number'] = $goods_number;
	$list[] = $row;
}

$smarty->assign('list', $list);
$smarty->assign('helps', get_shop_help());
$smarty->assign('lang', $_LANG);
$smarty->assign('category', 1.0E+19);
$smarty->assign('area_id', $area_id);
$smarty->assign('region_id', $region_id);
$smarty->assign('feed_url', $_CFG['rewrite'] == 1 ? 'feed-typepackage.xml' : 'feed.php?type=package');
$smarty->display('package.dwt');

?>
