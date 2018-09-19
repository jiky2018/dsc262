<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
assign_template();
$position = assign_ur_here();
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here', $position['ur_here']);
$smarty->assign('keywords', htmlspecialchars($_CFG['shop_keywords']));
$smarty->assign('description', htmlspecialchars($_CFG['shop_desc']));
$smarty->assign('flash_theme', $_CFG['flash_theme']);
$smarty->assign('feed_url', $_CFG['rewrite'] == 1 ? 'feed.xml' : 'feed.php');
$smarty->assign('warehouse_id', $region_id);
$smarty->assign('area_id', $area_id);
$smarty->assign('helps', get_shop_help());
assign_dynamic('index', $region_id, $area_id);
$replace_data = array('http://localhost/ecmoban_dsc2.0.5_20170518/', 'http://localhost/ecmoban_dsc2.2.6_20170727/', 'http://localhost/ecmoban_dsc2.3/');
$page = get_html_file($dir . '/pc_html.php');
$nav_page = get_html_file($dir . '/nav_html.php');
$topBanner = get_html_file($dir . '/topBanner.php');
$topBanner = str_replace($replace_data, $ecs->url(), $topBanner);
$page = str_replace($replace_data, $ecs->url(), $page);

if ($GLOBALS['_CFG']['open_oss'] == 1) {
	$bucket_info = get_bucket_info();
	$endpoint = $bucket_info['endpoint'];
}
else {
	$endpoint = (!empty($GLOBALS['_CFG']['site_domain']) ? $GLOBALS['_CFG']['site_domain'] : '');
}

/* if ($page && $endpoint) {
	$desc_preg = get_goods_desc_images_preg($endpoint, $page);
	$page = $desc_preg['goods_desc'];
}

if ($topBanner && $endpoint) {
	$desc_preg = get_goods_desc_images_preg($endpoint, $topBanner);
	$topBanner = $desc_preg['goods_desc'];
} */

$user_id = (!empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);

if (!defined('THEME_EXTENSION')) {
	$categories_pro = get_category_tree_leve_one();
	$smarty->assign('categories_pro', $categories_pro);
}

$bonusadv = getleft_attr('bonusadv', 0, $suffix, $GLOBALS['_CFG']['template']);

if ($bonusadv['img_file']) {
	$bonusadv['img_file'] = get_image_path(0, $bonusadv['img_file']);

	if (strpos($bonusadv['img_file'], $_COOKIE['index_img_file']) !== false) {
		if ($_COOKIE['bonusadv'] == 1) {
			$bonusadv['img_file'] = '';
		}
		else if ($bonusadv['img_file']) {
			setcookie('bonusadv', 1, gmtime() + (3600 * 10), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
			setcookie('index_img_file', $bonusadv['img_file'], gmtime() + (3600 * 10), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		}
	}
	else {
		setcookie('bonusadv', 1, gmtime() + (3600 * 10), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('index_img_file', $bonusadv['img_file'], gmtime() + (3600 * 10), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}
}

$pc_page['tem'] = $suffix;

$cat_row['top_style_tpl'] = substr($suffix,-1);

$smarty->assign('cate_info', $cat_row);

$smarty->assign('suffix', $suffix);
$smarty->assign('enableTem', $enableTem);
$smarty->assign('categories_child', $categories_child);
$smarty->assign('pc_page', $pc_page);
$smarty->assign('nav_page', $nav_page);
$smarty->assign('bonusadv', $bonusadv);
$smarty->assign('page', $page);
$smarty->assign('topBanner', $topBanner);
$smarty->assign('user_id', $user_id);
$smarty->assign('site_domain', $_CFG['site_domain']);
$smarty->display('categorytop.dwt');

?>
