<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
if (isset($_GET['code']) && !empty($_GET['code'])) {
	$oath_where = '';
	if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
		$oath_where .= '&user_id=' . $_SESSION['user_id'];
		$oath_where .= '&jump=account_bind';
	}

	$redirect_url = $ecs->url() . 'user.php?act=oath_login&type=qq&code=' . $_GET['code'] . $oath_where;
	header('location:' . $redirect_url);
	exit();
}

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . '/includes/lib_area.php';
require ROOT_PATH . '/includes/lib_visual.php';
$warehouse_other = array('province_id' => $province_id, 'city_id' => $city_id);
$warehouse_area_info = get_warehouse_area_info($warehouse_other);
$region_id = $warehouse_area_info['region_id'];
$area_id = $warehouse_area_info['area_id'];
$area_city = $warehouse_area_info['city_id'];
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$uachar = '/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i';
if (($ua == '' || preg_match($uachar, $ua)) && !strpos(strtolower($_SERVER['REQUEST_URI']), 'wap')) {
	$Loaction = 'mobile/';

	if (!empty($Loaction)) {
		ecs_header('Location: ' . $Loaction . "\n");
		exit();
	}
}

if (!empty($_GET['gOo'])) {
	if (!empty($_GET['gcat'])) {
		$Loaction = 'category.php?id=' . $_GET['gcat'];
	}
	else if (!empty($_GET['acat'])) {
		$Loaction = 'article_cat.php?id=' . $_GET['acat'];
	}
	else if (!empty($_GET['goodsid'])) {
		$Loaction = 'goods.php?id=' . $_GET['goodsid'];
	}
	else if (!empty($_GET['articleid'])) {
		$Loaction = 'article.php?id=' . $_GET['articleid'];
	}

	if (!empty($Loaction)) {
		ecs_header('Location: ' . $Loaction . "\n");
		exit();
	}
}

$suffix = !empty($_REQUEST['suffix']) ? trim($_REQUEST['suffix']) : '';
$preview = 1;
if (empty($suffix) && $_CFG['openvisual'] == 1) {
	$rs_id = 0;

	if ($_CFG['region_store_enabled'] == 1) {
		$sql = 'SELECT rs_id FROM' . $ecs->table('rs_region') . 'WHERE region_id = \'' . $_COOKIE['city'] . '\' LIMIT 1';
		$rs_id = $db->getOne($sql);
		$rs_id = isset($rs_id) ? intval($rs_id) : 0;
		$sql = 'SELECT COUNT(*) FROM' . $ecs->table('home_templates') . ('WHERE rs_id = \'' . $rs_id . '\'');
		$count_temp = $db->getOne($sql);
		if ($count_temp == 0 && 0 < $rs_id) {
			$des = ROOT_PATH . 'data/home_Templates/' . $GLOBALS['_CFG']['template'];
			$new_suffix = get_new_dirName(0, $des);
			$enableTem = $db->getOne('SELECT code FROM' . $GLOBALS['ecs']->table('home_templates') . ' WHERE rs_id= 0 AND theme = \'' . $GLOBALS['_CFG']['template'] . '\' AND is_enable = 1');
			if (!empty($new_suffix) && $enableTem) {
				if (!is_dir($des . '/' . $new_suffix)) {
					make_dir($des . '/' . $new_suffix);
				}

				recurse_copy($des . '/' . $enableTem, $des . '/' . $new_suffix, 1);
				$sql = 'INSERT INTO' . $ecs->table('home_templates') . '(`rs_id`,`code`,`is_enable`,`theme`) VALUES (\'' . $rs_id . ('\',\'' . $new_suffix . '\',\'1\',\'') . $GLOBALS['_CFG']['template'] . '\')';
				$db->query($sql);
			}
		}
	}

	$enableTem = $db->getOne('SELECT code FROM' . $GLOBALS['ecs']->table('home_templates') . (' WHERE rs_id= \'' . $rs_id . '\' AND theme = \'') . $GLOBALS['_CFG']['template'] . '\' AND is_enable = 1');
	$suffix = !empty($enableTem) ? trim($enableTem) : '';
	$preview = 0;
}

$dir = ROOT_PATH . 'data/home_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $suffix;

if ($preview == 1) {
	$dir_temp = ROOT_PATH . 'data/home_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $suffix . '/temp';

	if (is_dir($dir_temp)) {
		$dir = $dir_temp;
	}
}

$smarty->assign('cfg_bonus_adv', $_CFG['bonus_adv']);
if (!empty($suffix) && file_exists($dir) && defined('THEME_EXTENSION')) {
	$real_ip = real_ip();
	$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $real_ip . '-' . $_CFG['lang'] . '-' . $suffix));
	get_down_hometemplates($suffix);
	require ROOT_PATH . 'homeindex.php';
	exit();
}
else {
	$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

	if (!$smarty->is_cached('index.dwt', $cache_id)) {
		assign_template();
		$position = assign_ur_here();
		$smarty->assign('ur_here', $position['ur_here']);
		$seo = get_seo_words('index');

		foreach ($seo as $key => $value) {
			$seo[$key] = str_replace(array('{sitename}', '{key}', '{description}'), array($position['title'], $_CFG['shop_keywords'], $_CFG['shop_desc']), $value);
		}

		if (!empty($seo['keywords'])) {
			$smarty->assign('keywords', htmlspecialchars($seo['keywords']));
		}
		else {
			$smarty->assign('keywords', htmlspecialchars($_CFG['shop_keywords']));
		}

		if (!empty($seo['description'])) {
			$smarty->assign('description', htmlspecialchars($seo['description']));
		}
		else {
			$smarty->assign('description', htmlspecialchars($_CFG['shop_desc']));
		}

		if (!empty($seo['title'])) {
			$smarty->assign('page_title', htmlspecialchars($seo['title']));
		}
		else {
			$smarty->assign('page_title', $position['title']);
		}

		$smarty->assign('flash_theme', $_CFG['flash_theme']);
		$smarty->assign('feed_url', $_CFG['rewrite'] == 1 ? 'feed.xml' : 'feed.php');

		for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
			$ad_arr .= '\'c' . $i . ',';
			$index_ad .= '\'index_ad' . $i . ',';
			$cat_goods_banner .= '\'cat_goods_banner' . $i . ',';
			$cat_goods_hot .= '\'cat_goods_hot' . $i . ',';
			$index_brand_banner .= '\'index_brand_banner' . $i . ',';
			$index_brand_street .= '\'index_brand_street' . $i . ',';
			$index_group_banner .= '\'index_group_banner' . $i . ',';
			$index_banner_group .= '\'index_banner_group' . $i . ',';

			if (defined('THEME_EXTENSION')) {
				$recommend_category .= '\'recommend_category' . $i . ',';
				$index_expert_field .= '\'expert_field_ad' . $i . ',';
				$recommend_merchants .= '\'recommend_merchants' . $i . ',';
			}
		}

		$smarty->assign('adarr', $ad_arr);
		$smarty->assign('index_ad', $index_ad);

		if (defined('THEME_EXTENSION')) {
			$smarty->assign('rec_cat', $recommend_category);
			$smarty->assign('expert_field', $index_expert_field);
			$smarty->assign('recommend_merchants', $recommend_merchants);
		}

		$smarty->assign('cat_goods_banner', $cat_goods_banner);
		$smarty->assign('cat_goods_hot', $cat_goods_hot);
		$smarty->assign('index_brand_banner', $index_brand_banner);
		$smarty->assign('index_brand_street', $index_brand_street);
		$smarty->assign('index_group_banner', $index_group_banner);
		$smarty->assign('index_banner_group', $index_banner_group);
		$smarty->assign('top_banner', 'top_banner');
		$smarty->assign('warehouse_id', $region_id);
		$smarty->assign('area_id', $area_id);
		$smarty->assign('area_city', $area_city);
		$smarty->assign('helps', get_shop_help());

		if (!defined('THEME_EXTENSION')) {
			$categories_pro = get_category_tree_leve_one();
			$smarty->assign('categories_pro', $categories_pro);
		}

		if (defined('THEME_EXTENSION')) {
			for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
				$bonushome .= '\'bonushome' . $i . ',';
			}

			$smarty->assign('bonushome', $bonushome);
			$guess_num = 10;
			$smarty->assign('floor_data', get_floor_data('index'));
		}
		else {
			$guess_num = 9;
			$smarty->assign('guess_store', get_guess_store($_SESSION['user_id'], 2));
			$smarty->assign('new_goods', get_recommend_goods('new', '', $region_id, $area_id, $area_city));
			$smarty->assign('best_goods', get_recommend_goods('best', '', $region_id, $area_id, $area_city));
			$smarty->assign('hot_goods', get_recommend_goods('hot', '', $region_id, $area_id, $area_city));
			$smarty->assign('promotion_goods', get_promote_goods('', $region_id, $area_id, $area_city));
		}

		$smarty->assign('guess_goods', get_guess_goods($_SESSION['user_id'], 1, 1, $guess_num, $region_id, $area_id, $area_city));
		$smarty->assign('data_dir', DATA_DIR);
		assign_dynamic('index', $region_id, $area_id, $area_city);
	}

	$smarty->display('index.dwt', $cache_id);
}

?>
