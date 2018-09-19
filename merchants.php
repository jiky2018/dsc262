<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . '/includes/lib_area.php';
$article_id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : $_CFG['marticle_id']);
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

if (!$smarty->is_cached('merchants.dwt')) {
	assign_template();
	$position = assign_ur_here();
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);
	$categories_pro = get_category_tree_leve_one();
	$smarty->assign('categories_pro', $categories_pro);
	$marticle = explode(',', $_CFG['marticle']);
	$article_menu1 = get_merchants_article_menu($marticle[0]);
	$article_menu2 = get_merchants_article_menu($marticle[1]);
	$article_info = get_merchants_article_info($article_id);

	for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
		$ad_arr .= '\'merch' . $i . ',';
	}

	if (defined('THEME_EXTENSION')) {
		for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
			$merchants_index_top .= '\'merchants_index_top' . $i . ',';
			$merchants_index_category_ad .= '\'merchants_index_category_ad' . $i . ',';
			$merchants_index_case_ad .= '\'merchants_index_case_ad' . $i . ',';
		}

		$smarty->assign('merchants_index_case_ad', $merchants_index_case_ad);
		$smarty->assign('merchants_index_category_ad', $merchants_index_category_ad);
		$smarty->assign('merchants_index_top', $merchants_index_top);
		if (isset($_CFG['marticle_index']) && !empty($_CFG['marticle_index'])) {
			$sql = 'SELECT title,description,article_id FROM' . $ecs->table('article') . ' WHERE is_open = 1 AND article_id IN (' . $_CFG['marticle_index'] . ')';
			$articles_imp = $db->getAll($sql);
			$smarty->assign('articles_imp', $articles_imp);
		}
	}

	$smarty->assign('adarr', $ad_arr);
	$smarty->assign('article', $article_info);
	$smarty->assign('article_menu1', $article_menu1);
	$smarty->assign('article_menu2', $article_menu2);
	$smarty->assign('article_id', $article_id);
	$smarty->assign('marticle', $marticle[0]);

	if (defined('THEME_EXTENSION')) {
		$user_id = (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);
		$smarty->assign('user_id', $user_id);
		$smarty->assign('footer', 2);
	}

	$smarty->assign('helps', get_shop_help());
	assign_dynamic('merchants');
}

$smarty->display('merchants.dwt');

?>
