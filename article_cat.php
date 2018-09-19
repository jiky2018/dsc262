<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

get_request_filter();
require ROOT_PATH . '/includes/lib_area.php';
clear_cache_files();

if (!empty($_GET['id'])) {
	$cat_id = intval($_GET['id']);
}
else if (!empty($_GET['category'])) {
	$cat_id = intval($_GET['category']);
}
else if (!empty($_GET['article_id'])) {
	$article_id = intval($_GET['article_id']);
	$sql = 'SELECT cat_id FROM' . $ecs->table('article') . ('WHERE article_id = \'' . $article_id . '\' LIMIT 1');
	$cat_id = $db->getOne($sql);
	if ($cat_id === false || $cat_id == 0) {
		ecs_header("Location: ./\n");
		exit();
	}
}
else {
	ecs_header("Location: ./\n");
	exit();
}

$page = !empty($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$cache_id = sprintf('%X', crc32($cat_id . '-' . $page . '-' . $_CFG['lang']));

if (!$smarty->is_cached('article_cat.dwt', $cache_id)) {
	assign_template('a', array($cat_id));
	$position = assign_ur_here($cat_id);
	$smarty->assign('helps', get_shop_help());
	$smarty->assign('ur_here', $position['ur_here']);

	if (!defined('THEME_EXTENSION')) {
		$categories_pro = get_category_tree_leve_one();
		$smarty->assign('categories_pro', $categories_pro);
	}

	$smarty->assign('sys_categories', article_categories_tree(0, 2));
	$smarty->assign('custom_categories', article_categories_tree(0, 1));
	$cat_list = get_cat_list($cat_id);
	$child_count = count($cat_list[0]['child_list']);

	if ($child_count == 0) {
		$cat_list = get_cat_list($cat_list[0]['parent_id']);
		$child_count = count($cat_list[0]['child_list']);
	}

	$smarty->assign('cat_list', $cat_list);
	$smarty->assign('child_count', $child_count);
	$smarty->assign('best_goods', get_recommend_goods('best'));
	$smarty->assign('new_goods', get_recommend_goods('new'));
	$smarty->assign('hot_goods', get_recommend_goods('hot'));
	$smarty->assign('promotion_goods', get_promote_goods());
	$meta = $db->getRow('SELECT keywords, cat_name ,cat_desc,cat_type FROM ' . $ecs->table('article_cat') . (' WHERE cat_id = \'' . $cat_id . '\''));
	if ($meta === false || empty($meta)) {
		ecs_header("Location: ./\n");
		exit();
	}

	$smarty->assign('cat_info', $meta);
	$smarty->assign('keywords', htmlspecialchars($meta['keywords']));
	$smarty->assign('description', htmlspecialchars($meta['cat_desc']));
	$smarty->assign('cat_name', $meta['cat_name']);
	$size = isset($_CFG['article_page_size']) && 0 < intval($_CFG['article_page_size']) ? intval($_CFG['article_page_size']) : 20;
	$count = get_article_count($cat_id);
	$pages = 0 < $count ? ceil($count / $size) : 1;

	if ($pages < $page) {
		$page = $pages;
	}

	$pager['search']['id'] = $cat_id;
	$keywords = '';
	$goon_keywords = '';

	if (isset($_REQUEST['keywords'])) {
		$keywords = addslashes(htmlspecialchars(urldecode(trim($_REQUEST['keywords']))));
		$pager['search']['keywords'] = $keywords;
		$search_url = substr(strrchr($_POST['cur_url'], '/'), 1);
		$smarty->assign('search_value', stripslashes(stripslashes($keywords)));
		$smarty->assign('search_url', $search_url);
		$count = get_article_count($cat_id, $keywords);
		$pages = 0 < $count ? ceil($count / $size) : 1;

		if ($pages < $page) {
			$page = $pages;
		}

		$goon_keywords = urlencode($_REQUEST['keywords']);
	}

	$smarty->assign('artciles_list', get_cat_articles($cat_id, $page, $size, $keywords));
	$smarty->assign('cat_id', $cat_id);
	assign_pager('article_cat', $cat_id, $count, $size, '', '', $page, $goon_keywords);
	assign_dynamic('article_cat');
}

$smarty->assign('feed_url', $_CFG['rewrite'] == 1 ? 'feed-typearticle_cat' . $cat_id . '.xml' : 'feed.php?type=article_cat' . $cat_id);
$seo = get_seo_words('article');

foreach ($seo as $key => $value) {
	$seo[$key] = str_replace(array('{sitename}', '{key}', '{name}', '{description}', '{article_class}'), array($_CFG['shop_name'], $meta['keywords'], $meta['cat_name'], $meta['cat_desc'], $cat_list[0]['cat_name']), $value);
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

$smarty->display('article_cat.dwt', $cache_id);

?>
