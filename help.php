<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_article_info($article_id)
{
	$sql = 'SELECT a.*, IFNULL(AVG(r.comment_rank), 0) AS comment_rank ' . 'FROM ' . $GLOBALS['ecs']->table('article') . ' AS a ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('comment') . ' AS r ON r.id_value = a.article_id AND comment_type = 1 ' . 'WHERE a.is_open = 1 AND a.article_id = \'' . $article_id . '\' GROUP BY a.article_id';
	$row = $GLOBALS['db']->getRow($sql);

	if ($row !== false) {
		$row['comment_rank'] = ceil($row['comment_rank']);
		$row['add_time'] = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);
		if (empty($row['author']) || ($row['author'] == '_SHOPHELP')) {
			$row['author'] = $GLOBALS['_CFG']['shop_name'];
		}
	}

	return $row;
}

function article_related_goods($id)
{
	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price AS org_price, ' . 'IFNULL(mp.user_price, g.shop_price * \'' . $_SESSION['discount'] . '\') AS shop_price, ' . 'g.market_price, g.promote_price, g.promote_start_date, g.promote_end_date ' . 'FROM ' . $GLOBALS['ecs']->table('goods_article') . ' ga ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . 'WHERE ga.article_id = \'' . $id . '\' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0';
	$res = $GLOBALS['db']->query($sql);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$arr[$row['goods_id']]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
		$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

		if (0 < $row['promote_price']) {
			$arr[$row['goods_id']]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			$arr[$row['goods_id']]['formated_promote_price'] = price_format($arr[$row['goods_id']]['promote_price']);
		}
		else {
			$arr[$row['goods_id']]['promote_price'] = 0;
		}
	}

	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

$_REQUEST['id'] = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 18;
$region_id = $_REQUEST['id'];
$cache_id = sprintf('%X', crc32($_REQUEST['id'] . '-' . $_CFG['lang']));

if (!$smarty->is_cached('help.dwt', $cache_id)) {
	$sql = 'SELECT a.region_id ,a.shipping_area_id,b.region_name,c.name,c.user_name,c.id,c.address,c.mobile,c.img_url,c.anchor,c.line FROM ' . $GLOBALS['ecs']->table('area_region') . " AS a\r\nLEFT JOIN " . $GLOBALS['ecs']->table('region') . ' AS b ON a.region_id=b.region_id  LEFT JOIN ' . $GLOBALS['ecs']->table('shipping_point') . ' AS c ON c.shipping_area_id=a.shipping_area_id ' . 'WHERE c.name != \'\' AND a.region_id IN (SELECT region_id FROM ' . $ecs->table('region') . ' WHERE parent_id=\'' . $region_id . '\')';
	$self_point = $db->getAll($sql);
	$sql = 'SELECT region_name FROM ' . $ecs->table('region') . ' WHERE region_id=' . $region_id;
	$region_name = $db->getOne($sql);

	if (empty($self_point)) {
		ecs_header("Location: ./\n");
		exit();
	}

	$smarty->assign('self_point', $self_point);
	$smarty->assign('region_name', $region_name);
	$smarty->assign('sys_categories', article_categories_tree(0, 2));
	$smarty->assign('custom_categories', article_categories_tree(0, 1));

	if (!defined('THEME_EXTENSION')) {
		$categories_pro = get_category_tree_leve_one();
		$smarty->assign('categories_pro', $categories_pro);
	}

	$smarty->assign('article_categories', article_categories_tree($article_id));
	$smarty->assign('helps', get_shop_help());
	$smarty->assign('new_article', get_new_article(5));
	$smarty->assign('id', $article_id);
	$smarty->assign('username', $_SESSION['user_name']);
	$smarty->assign('email', $_SESSION['email']);
	$smarty->assign('type', '1');
	if ((intval($_CFG['captcha']) & CAPTCHA_COMMENT) && (0 < gd_version())) {
		$smarty->assign('enabled_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	$smarty->assign('article', $article);
	$smarty->assign('keywords', htmlspecialchars($article['keywords']));
	$smarty->assign('description', htmlspecialchars($article['description']));
	$position = assign_ur_here($article['cat_id'], $article['title']);
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $region_name . $_LANG['Since_some']);
	$smarty->assign('comment_type', 1);
	assign_dynamic('help');
	assign_template('c', $catlist);
}

$smarty->display('help.dwt', $cache_id);

?>
