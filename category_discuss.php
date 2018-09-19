<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_area.php';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$preg = '/<script[\\s\\S]*?<\\/script>/i';
$id = isset($_REQUEST['id']) ? strtolower($_REQUEST['id']) : 0;
$id = !empty($id) ? preg_replace($preg, '', stripslashes($id)) : 0;

if (empty($id)) {
	ecs_header("Location: ./\n");
	exit();
}

$goods_id = intval($id);
$cache_id = sprintf('%X', crc32($goods_id . '-' . $_SESSION['user_rank'] . '-' . $_CFG['lang']));
$warehouse_other = array('province_id' => $province_id, 'city_id' => $city_id);
$warehouse_area_info = get_warehouse_area_info($warehouse_other);
$region_id = $warehouse_area_info['region_id'];
$area_id = $warehouse_area_info['area_id'];
$area_city = $warehouse_area_info['city_id'];
$history_goods = get_history_goods($goods_id, $region_id, $area_id, $area_city);
$smarty->assign('history_goods', $history_goods);
$goodsInfo = get_goods_info($goods_id, $region_id, $area_id);
$goodsInfo['goods_price'] = price_format($goodsInfo['goods_price']);
$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('presale_activity') . (' WHERE goods_id = \'' . $goods_id . '\'');
$presale = $GLOBALS['db']->getAll($sql);

if ($presale) {
	foreach ($presale as $row) {
		$goodsInfo['goods_url'] = build_uri('presale', array('act' => 'view', 'presaleid' => $row['act_id']));
	}

	$smarty->assign('is_presale', $presale);
}

$smarty->assign('goodsInfo', $goodsInfo);
$mc_all = ments_count_all($goods_id);
$mc_one = ments_count_rank_num($goods_id, 1);
$mc_two = ments_count_rank_num($goods_id, 2);
$mc_three = ments_count_rank_num($goods_id, 3);
$mc_four = ments_count_rank_num($goods_id, 4);
$mc_five = ments_count_rank_num($goods_id, 5);
$comment_all = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);
$smarty->assign('comment_all', $comment_all);

if (!$smarty->is_cached('category_discuss.dwt', $cache_id)) {
	if (defined('THEME_EXTENSION')) {
		$smarty->assign('user_info', get_user_default($_SESSION['user_id']));
		$goods = get_goods_info($goods_id);

		if (defined('THEME_EXTENSION')) {
			$sql = 'SELECT rec_id FROM ' . $ecs->table('collect_store') . ' WHERE user_id = \'' . $_SESSION['user_id'] . ('\' AND ru_id = \'' . $goods['user_id'] . '\' ');
			$rec_id = $db->getOne($sql);

			if (0 < $rec_id) {
				$goods['error'] = '1';
			}
			else {
				$goods['error'] = '2';
			}
		}

		$smarty->assign('goods', $goods);

		if (0 < $goods['user_id']) {
			$merchants_goods_comment = get_merchants_goods_comment($goods['user_id']);
			$smarty->assign('merch_cmt', $merchants_goods_comment);
		}

		if ($GLOBALS['_CFG']['customer_service'] == 0) {
			$goods_user_id = 0;
		}
		else {
			$goods_user_id = $goods['user_id'];
		}

		$basic_info = get_shop_info_content($goods_user_id);
		$shop_information = get_shop_name($goods_user_id);

		if ($goods_user_id == 0) {
			if ($db->getOne('SELECT kf_im_switch FROM ' . $ecs->table('seller_shopinfo') . 'WHERE ru_id = 0')) {
				$shop_information['is_dsc'] = true;
			}
			else {
				$shop_information['is_dsc'] = false;
			}
		}
		else {
			$shop_information['is_dsc'] = false;
		}

		$smarty->assign('shop_information', $shop_information);
		$smarty->assign('kf_appkey', $basic_info['kf_appkey']);
		$smarty->assign('im_user_id', 'dsc' . $_SESSION['user_id']);
	}

	if ($db->getOne(' SELECT rec_id FROM ' . $ecs->table('collect_store') . ' WHERE user_id = \'user_id\' AND ru_id = \'goods_user_id\' ')) {
		$smarty->assign('is_collected', true);
	}

	$smarty->assign('goods_id', $goods_id);
	assign_template();
	$position = assign_ur_here($goodsInfo['cat_id'], $goodsInfo['goods_name'], array(), '', $goodsInfo['user_id']);
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);

	if (!defined('THEME_EXTENSION')) {
		$categories_pro = get_category_tree_leve_one();
		$smarty->assign('categories_pro', $categories_pro);
	}

	$smarty->assign('keywords', htmlspecialchars($_CFG['shop_keywords']));
	$smarty->assign('description', htmlspecialchars($_CFG['shop_desc']));
	$smarty->assign('flash_theme', $_CFG['flash_theme']);
	$smarty->assign('feed_url', $_CFG['rewrite'] == 1 ? 'feed.xml' : 'feed.php');
	$smarty->assign('helps', get_shop_help());
	if (intval($_CFG['captcha']) & CAPTCHA_COMMENT && 0 < gd_version()) {
		$smarty->assign('enabled_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	$smarty->assign('shop_notice', $_CFG['shop_notice']);
}

$discuss_list = get_discuss_all_list($goods_id);
$smarty->assign('discuss_list', $discuss_list);
$all_count = get_discuss_type_count($goods_id);
$t_count = get_discuss_type_count($goods_id, 1);
$w_count = get_discuss_type_count($goods_id, 2);
$q_count = get_discuss_type_count($goods_id, 3);
$s_count = get_commentImg_count($goods_id);
$smarty->assign('all_count', $all_count);
$smarty->assign('t_count', $t_count);
$smarty->assign('w_count', $w_count);
$smarty->assign('q_count', $q_count);
$smarty->assign('s_count', $s_count);
$discuss_hot = get_discuss_all_list($goods_id, 0, 1, 10, 0, 'dis_browse_num');
$smarty->assign('hot_list', $discuss_hot);
$smarty->assign('user_id', $user_id);
$smarty->display('category_discuss.dwt', $cache_id);

?>
