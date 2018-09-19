<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function exchange_get_goods($children, $min, $max, $ext, $size, $page, $sort, $order)
{
	$display = $GLOBALS['display'];
	$where = 'eg.is_exchange = 1 AND g.is_delete = 0 AND ' . ('(' . $children . ' OR ') . get_extension_goods($children) . ')';

	if (0 < $min) {
		$where .= ' AND eg.exchange_integral >= ' . $min . ' ';
	}

	if (0 < $max) {
		$where .= ' AND eg.exchange_integral <= ' . $max . ' ';
	}

	$select = ', (SELECT SUM(og.goods_number) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi, ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' WHERE oi.order_id = og.order_id AND oi.extension_code = \'exchange_goods\' AND og.goods_id = g.goods_id ' . ' AND (oi.order_status = \'' . OS_CONFIRMED . '\' OR  oi.order_status = \'' . OS_SPLITED . '\' OR oi.order_status = \'' . OS_SPLITING_PART . '\') ' . ' AND (oi.pay_status  = \'' . PS_PAYING . '\' OR  oi.pay_status  = \'' . PS_PAYED . '\')) AS volume ';

	if ($sort == 'sales_volume') {
		$sort = 'volume';
	}

	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, eg.exchange_integral, ' . 'g.goods_type, g.goods_brief, g.goods_thumb , g.goods_img, eg.is_hot ' . $select . 'FROM ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ('WHERE eg.goods_id = g.goods_id AND eg.review_status = 3 AND ' . $where . ' ' . $ext . ' ORDER BY ' . $sort . ' ' . $order);
	if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'load_more_goods') {
		$start = intval($_REQUEST['goods_num']);
	}
	else {
		$start = ($page - 1) * $size;
	}

	$res = $GLOBALS['db']->selectLimit($sql, $size, $start);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$watermark_img = '';

		if ($row['is_hot'] != 0) {
			$watermark_img = 'watermark_hot_small';
		}

		if ($watermark_img != '') {
			$arr[$row['goods_id']]['watermark_img'] = $watermark_img;
		}

		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];

		if ($display == 'grid') {
			$arr[$row['goods_id']]['goods_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		}
		else {
			$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		}

		$arr[$row['goods_id']]['name'] = $row['goods_name'];
		$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
		$arr[$row['goods_id']]['sales_volume'] = $row['volume'];
		$arr[$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
		$arr[$row['goods_id']]['exchange_integral'] = $row['exchange_integral'];
		$arr[$row['goods_id']]['type'] = $row['goods_type'];
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
		$arr[$row['goods_id']]['url'] = build_uri('exchange_goods', array('gid' => $row['goods_id']), $row['goods_name']);
	}

	return $arr;
}

function get_exchange_goods_count($children, $min = 0, $max = 0, $ext = '')
{
	$where = 'eg.is_exchange = 1 AND g.is_delete = 0 AND (' . $children . ' OR ' . get_extension_goods($children) . ')';

	if (0 < $min) {
		$where .= ' AND eg.exchange_integral >= ' . $min . ' ';
	}

	if (0 < $max) {
		$where .= ' AND eg.exchange_integral <= ' . $max . ' ';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg, ' . $GLOBALS['ecs']->table('goods') . (' AS g WHERE eg.goods_id = g.goods_id AND eg.review_status = 3 AND ' . $where . ' ' . $ext);
	return $GLOBALS['db']->getOne($sql);
}

function get_exchange_recommend_goods($type = '', $cats = '', $min = 0, $max = 0, $ext = '')
{
	$price_where = 0 < $min ? ' AND g.shop_price >= ' . $min . ' ' : '';
	$price_where .= 0 < $max ? ' AND g.shop_price <= ' . $max . ' ' : '';
	$sql = 'SELECT g.goods_id, g.goods_name, g.market_price, g.goods_name_style, eg.exchange_integral, ' . 'g.goods_brief, g.goods_thumb, goods_img, b.brand_name ' . 'FROM ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = eg.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' . 'WHERE eg.is_exchange = 1 AND eg.review_status = 3 AND g.is_delete = 0 ' . $price_where . $ext;
	$num = 0;
	$type2lib = array('best' => 'exchange_best', 'new' => 'exchange_new', 'hot' => 'exchange_hot');
	$num = get_library_number($type2lib[$type], 'exchange_list');

	switch ($type) {
	case 'best':
		$sql .= ' AND eg.is_best = 1';
		break;

	case 'new':
		$sql .= ' AND eg.is_new = 1';
		break;

	case 'hot':
		$sql .= ' AND eg.is_hot = 1';
		break;
	}

	if (!empty($cats)) {
		$sql .= ' AND (' . $cats . ' OR ' . get_extension_goods($cats) . ')';
	}

	$order_type = $GLOBALS['_CFG']['recommend_order'];
	$sql .= $order_type == 0 ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY RAND()';
	$res = $GLOBALS['db']->selectLimit($sql, $num);
	$idx = 0;
	$goods = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$goods[$idx]['id'] = $row['goods_id'];
		$goods[$idx]['name'] = $row['goods_name'];
		$goods[$idx]['market_price'] = price_format($row['market_price']);
		$goods[$idx]['brief'] = $row['goods_brief'];
		$goods[$idx]['brand_name'] = $row['brand_name'];
		$goods[$idx]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$goods[$idx]['exchange_integral'] = $row['exchange_integral'];
		$goods[$idx]['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$goods[$idx]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
		$goods[$idx]['url'] = build_uri('exchange_goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
		$idx++;
	}

	return $goods;
}

function get_exchange_goods_info($goods_id, $warehouse_id = 0, $area_id = 0)
{
	$leftJoin = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$select .= ', (SELECT SUM(og.goods_number) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi, ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' WHERE oi.order_id = og.order_id AND oi.extension_code = \'exchange_goods\' AND og.goods_id = g.goods_id ' . ' AND (oi.order_status = \'' . OS_CONFIRMED . '\' OR  oi.order_status = \'' . OS_SPLITED . '\' OR oi.order_status = \'' . OS_SPLITING_PART . '\') ' . ' AND (oi.pay_status  = \'' . PS_PAYING . '\' OR  oi.pay_status  = \'' . PS_PAYED . '\')) AS volume ';
	$time = gmtime();
	$sql = 'SELECT g.*, c.measure_unit, b.brand_id, b.brand_name AS goods_brand, eg.exchange_integral, eg.market_integral, eg.is_exchange, ' . 'IF(g.model_inventory < 1, g.goods_number, IF(g.model_inventory < 2, wg.region_number, wag.region_number)) as goods_number ' . $select . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg ON g.goods_id = eg.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('category') . ' AS c ON g.cat_id = c.cat_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' . $leftJoin . ('WHERE  g.goods_id = \'' . $goods_id . '\' AND g.is_delete = 0 AND eg.review_status = 3 ') . 'GROUP BY g.goods_id';
	$row = $GLOBALS['db']->getRow($sql);

	if ($row !== false) {
		$watermark_img = '';

		if ($row['is_new'] != 0) {
			$watermark_img = 'watermark_new';
		}
		else if ($row['is_best'] != 0) {
			$watermark_img = 'watermark_best';
		}
		else if ($row['is_hot'] != 0) {
			$watermark_img = 'watermark_hot';
		}

		if ($watermark_img != '') {
			$row['watermark_img'] = $watermark_img;
		}

		$row['goods_weight'] = 0 < intval($row['goods_weight']) ? $row['goods_weight'] . $GLOBALS['_LANG']['kilogram'] : $row['goods_weight'] * 1000 . $GLOBALS['_LANG']['gram'];
		$row['add_time'] = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();

			if ($row['goods_desc']) {
				$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $row['goods_desc']);
				$row['goods_desc'] = $desc_preg['goods_desc'];
			}
		}

		$row['market_integral'] = !empty($row['market_integral']) ? $row['market_integral'] : 0;
		$row['goods_img'] = get_image_path($goods_id, $row['goods_img']);
		$row['goods_thumb'] = get_image_path($goods_id, $row['goods_thumb'], true);
		$row['goods_number'] = $row['goods_number'];
		$row['marketPrice'] = $row['market_price'];
		$row['market_price'] = price_format($row['market_price']);
		$row['goods_price'] = price_format($row['exchange_integral'] * $GLOBALS['_CFG']['integral_scale'] / 100);
		$row['rz_shopName'] = get_shop_name($row['user_id'], 1);
		$build_uri = array('urid' => $row['user_id'], 'append' => $row['rz_shopName']);
		$domain_url = get_seller_domain_url($row['user_id'], $build_uri);
		$row['store_url'] = $domain_url['domain_name'];
		$row['shopinfo'] = get_shop_name($row['user_id'], 2);
		$row['shopinfo']['brand_thumb'] = str_replace(array('../'), '', $row['shopinfo']['brand_thumb']);

		if ($row['goods_product_tag']) {
			$impression_list = !empty($row['goods_product_tag']) ? explode(',', $row['goods_product_tag']) : '';

			foreach ($impression_list as $kk => $vv) {
				$tag[$kk]['txt'] = $vv;
				$tag[$kk]['num'] = comment_goodstag_num($row['goods_id'], $vv);
			}

			$row['impression_list'] = $tag;
		}

		$row['collect_count'] = get_collect_goods_user_count($row['goods_id']);

		if ($row['user_id'] == 0) {
			$row['brand'] = get_brand_url($row['brand_id']);
		}

		return $row;
	}
	else {
		return false;
	}
}

function get_linked_goods($goods_id, $warehouse_id = 0, $area_id = 0)
{
	$where = '';
	$leftJoin = '';
	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'g.market_price, g.sales_volume, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . ' g.promote_start_date, g.promote_end_date ' . 'FROM ' . $GLOBALS['ecs']->table('link_goods') . ' lg ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = lg.link_goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . $leftJoin . ('WHERE lg.goods_id = \'' . $goods_id . '\' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ') . $where . 'LIMIT ' . $GLOBALS['_CFG']['related_goods_number'];
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
		$arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];

		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
	}

	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . '/includes/lib_area.php';
$warehouse_other = array('province_id' => $province_id, 'city_id' => $city_id);
$warehouse_area_info = get_warehouse_area_info($warehouse_other);
$region_id = $warehouse_area_info['region_id'];
$area_id = $warehouse_area_info['area_id'];
$area_city = $warehouse_area_info['city_id'];

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}

$user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('category_load_type', $_CFG['category_load_type']);
	$smarty->assign('query_string', preg_replace('/act=\\w+&?/', '', $_SERVER['QUERY_STRING']));
	$page = isset($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$size = isset($_CFG['page_size']) && 0 < intval($_CFG['exchange_size']) ? intval($_CFG['exchange_size']) : 10;
	$cat_id = isset($_REQUEST['cat_id']) && 0 < intval($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$integral_max = isset($_REQUEST['integral_max']) && 0 < intval($_REQUEST['integral_max']) ? intval($_REQUEST['integral_max']) : 0;
	$integral_min = isset($_REQUEST['integral_min']) && 0 < intval($_REQUEST['integral_min']) ? intval($_REQUEST['integral_min']) : 0;
	$default_display_type = $_CFG['show_order_type'] == '0' ? 'list' : ($_CFG['show_order_type'] == '1' ? 'grid' : 'text');
	$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
	$default_sort_order_type = $_CFG['sort_order_type'] == '0' ? 'goods_id' : ($_CFG['sort_order_type'] == '1' ? 'sales_volume' : 'is_exchange');
	$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('goods_id', 'sales_volume', 'exchange_integral', 'is_exchange')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
	$order = isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')) ? trim($_REQUEST['order']) : $default_sort_order_method;
	$display = isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array('list', 'grid', 'text')) ? trim($_REQUEST['display']) : (isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : $default_display_type);
	$display = in_array($display, array('list', 'grid', 'text')) ? $display : 'text';
	setcookie('ECS[display]', $display, gmtime() + 86400 * 7, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$cache_id = sprintf('%X', crc32($cat_id . '-' . $display . '-' . $sort . '-' . $order . '-' . $page . '-' . $size . '-' . $_SESSION['user_rank'] . '-' . $_CFG['lang'] . '-' . $integral_max . '-' . $integral_min));

	if (!$smarty->is_cached('exchange_list.dwt', $cache_id)) {
		$children = get_children($cat_id);
		$cat_select = array('cat_name', 'keywords', 'cat_desc', 'style', 'grade', 'filter_attr', 'parent_id');
		$cat = get_cat_info($cat_id, $cat_select);

		if (!empty($cat)) {
			$smarty->assign('keywords', htmlspecialchars($cat['keywords']));
			$smarty->assign('description', htmlspecialchars($cat['cat_desc']));
		}

		assign_template();
		$position = assign_ur_here('exchange');
		$smarty->assign('ur_here', $position['ur_here']);

		if (!defined('THEME_EXTENSION')) {
			$categories_pro = get_category_tree_leve_one();
			$smarty->assign('categories_pro', $categories_pro);
		}

		$smarty->assign('helps', get_shop_help());
		$vote = get_vote();

		if (!empty($vote)) {
			$smarty->assign('vote_id', $vote['id']);
			$smarty->assign('vote', $vote['content']);
		}

		$ext = '';
		$smarty->assign('best_goods', get_exchange_recommend_goods('best', $children, $integral_min, $integral_max));
		$smarty->assign('hot_goods', get_exchange_recommend_goods('hot', $children, $integral_min, $integral_max));
		$count = get_exchange_goods_count($children, $integral_min, $integral_max);
		$max_page = 0 < $count ? ceil($count / $size) : 1;

		if ($max_page < $page) {
			$page = $max_page;
		}

		$goodslist = exchange_get_goods($children, $integral_min, $integral_max, $ext, $size, $page, $sort, $order);

		if ($display == 'grid') {
			if (count($goodslist) % 2 != 0) {
				$goodslist[] = array();
			}
		}

		$smarty->assign('goods_list', $goodslist);
		$smarty->assign('category', $cat_id);
		$smarty->assign('integral_max', $integral_max);
		$smarty->assign('integral_min', $integral_min);
		$category_list = cat_list();
		$smarty->assign('category_list', $category_list);

		if (!$_CFG['category_load_type']) {
			assign_pager('exchange', $cat_id, $count, $size, $sort, $order, $page, '', '', $integral_min, $integral_max, $display);
		}

		if (defined('THEME_EXTENSION')) {
			for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
				$exchange_top_banner .= '\'activity_top_ad_exchange' . $i . ',';
			}

			$smarty->assign('activity_top_banner', $exchange_top_banner);

			if (0 < $_SESSION['user_id']) {
				$smarty->assign('info', get_user_default($_SESSION['user_id']));
			}

			$smarty->assign('cat_id', $cat_id);
		}

		assign_dynamic('exchange_list');
	}

	$smarty->assign('category', 1.0E+19);
	$seo = get_seo_words('change');

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

	$smarty->assign('feed_url', $_CFG['rewrite'] == 1 ? 'feed-typeexchange.xml' : 'feed.php?type=exchange');
	$smarty->display('exchange_list.dwt', $cache_id);
}

if ($_REQUEST['act'] == 'load_more_goods') {
	$page = isset($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$size = isset($_CFG['page_size']) && 0 < intval($_CFG['exchange_size']) ? intval($_CFG['exchange_size']) : 10;
	$cat_id = isset($_REQUEST['cat_id']) && 0 < intval($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$integral_max = isset($_REQUEST['integral_max']) && 0 < intval($_REQUEST['integral_max']) ? intval($_REQUEST['integral_max']) : 0;
	$integral_min = isset($_REQUEST['integral_min']) && 0 < intval($_REQUEST['integral_min']) ? intval($_REQUEST['integral_min']) : 0;
	$default_display_type = $_CFG['show_order_type'] == '0' ? 'list' : ($_CFG['show_order_type'] == '1' ? 'grid' : 'text');
	$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
	$default_sort_order_type = $_CFG['sort_order_type'] == '0' ? 'goods_id' : ($_CFG['sort_order_type'] == '1' ? 'sales_volume' : 'is_exchange');
	$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('goods_id', 'sales_volume', 'exchange_integral', 'is_exchange')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
	$order = isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')) ? trim($_REQUEST['order']) : $default_sort_order_method;
	$display = isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array('list', 'grid', 'text')) ? trim($_REQUEST['display']) : (isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : $default_display_type);
	$display = in_array($display, array('list', 'grid', 'text')) ? $display : 'text';
	setcookie('ECS[display]', $display, gmtime() + 86400 * 7, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$children = get_children($cat_id);
	$ext = '';
	$count = get_exchange_goods_count($children, $integral_min, $integral_max);
	$max_page = 0 < $count ? ceil($count / $size) : 1;

	if ($max_page < $page) {
		$page = $max_page;
	}

	$goodslist = exchange_get_goods($children, $integral_min, $integral_max, $ext, $size, $page, $sort, $order);

	if ($display == 'grid') {
		if (count($goodslist) % 2 != 0) {
			$goodslist[] = array();
		}
	}

	$smarty->assign('goods_list', $goodslist);
	$smarty->assign('type', 'exchange');
	$result = array('error' => 0, 'message' => '', 'cat_goods' => '', 'best_goods' => '');
	$result['cat_goods'] = html_entity_decode($smarty->fetch('library/more_goods_page.lbi'));
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'view') {
	$goods_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$cache_id = $goods_id . '-' . $_SESSION['user_rank'] . '-' . $_CFG['lang'] . '-exchange';
	$cache_id = sprintf('%X', crc32($cache_id));

	if (!$smarty->is_cached('exchange_goods.dwt', $cache_id)) {
		$smarty->assign('image_width', $_CFG['image_width']);
		$smarty->assign('image_height', $_CFG['image_height']);
		$smarty->assign('helps', get_shop_help());
		$smarty->assign('id', $goods_id);
		$smarty->assign('type', 0);
		$smarty->assign('cfg', $_CFG);

		if (!defined('THEME_EXTENSION')) {
			$categories_pro = get_category_tree_leve_one();
			$smarty->assign('categories_pro', $categories_pro);
		}

		$goods = get_exchange_goods_info($goods_id, $region_id, $area_id);

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

		$goodslist = exchange_get_goods(get_children($goods['cat_id']), 0, 0, '', 6, 1, 'sales_volume', 'DESC');
		$smarty->assign('look_top', $goodslist);

		if ($goods === false) {
			ecs_header("Location: ./\n");
			exit();
		}
		else {
			$children = get_children($goods['cat_id']);

			if (0 < $goods['brand_id']) {
				$goods['goods_brand_url'] = build_uri('brand', array('bid' => $goods['brand_id']), $goods['goods_brand']);
			}

			$goods['goods_style_name'] = add_style($goods['goods_name'], $goods['goods_name_style']);
			$smarty->assign('goods', $goods);
			$smarty->assign('goods_id', $goods['goods_id']);
			$smarty->assign('keywords', htmlspecialchars($goods['keywords']));
			$smarty->assign('description', htmlspecialchars($goods['goods_brief']));
			assign_template();

			if (defined('THEME_EXTENSION')) {
				$position = assign_ur_here($goods['cat_id'], $goods['goods_name'], array(), '', $goods['user_id']);
			}
			else {
				$position = assign_ur_here(0, $goods['goods_name']);
			}

			$smarty->assign('ur_here', $position['ur_here']);
			$properties = get_goods_properties($goods_id, $region_id, $area_id, $area_city);
			$smarty->assign('properties', $properties['pro']);
			$smarty->assign('specification', $properties['spe']);
			$smarty->assign('pictures', get_goods_gallery($goods_id));
			$smarty->assign('best_goods', get_exchange_recommend_goods('best', $children));
			$smarty->assign('hot_goods', get_exchange_recommend_goods('hot', $children));

			if ($area_id == NULL) {
				$area_id = 0;
			}

			$area = array('region_id' => $region_id, 'province_id' => $province_id, 'city_id' => $city_id, 'district_id' => $district_id, 'street_id' => $street_id, 'street_list' => $street_list, 'goods_id' => $goods_id, 'user_id' => $user_id, 'area_id' => $area_id, 'merchant_id' => $goods['user_id']);
			$smarty->assign('area', $area);
			$comment_all = get_comments_percent($goods_id);

			if (0 < $goods['user_id']) {
				$merchants_goods_comment = get_merchants_goods_comment($goods['user_id']);
				$smarty->assign('merch_cmt', $merchants_goods_comment);
			}

			$smarty->assign('comment_all', $comment_all);
			$goods_area = 1;

			if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
				$area_list = get_goods_link_area_list($goods_id, $goods['user_id']);

				if ($area_list['goods_area']) {
					if (in_array($area_id, $area_list['goods_area'])) {
						$goods_area = 1;
					}
					else {
						$goods_area = 0;
					}
				}
				else {
					$goods_area = 0;
				}
			}

			$smarty->assign('goods_area', $goods_area);
			$goods_info = goods_info($goods_id);
			$basic_info = get_shop_info_content($goods_info['user_id']);

			if ($GLOBALS['_CFG']['customer_service'] == 0) {
				$goods_user_id = 0;
			}
			else {
				$goods_user_id = $goods_info['user_id'];
			}

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
			$basic_date = array('region_name');
			$basic_info['province'] = get_table_date('region', 'region_id = \'' . $basic_info['province'] . '\'', $basic_date, 2);
			$basic_info['city'] = get_table_date('region', 'region_id= \'' . $basic_info['city'] . '\'', $basic_date, 2) . '市';

			if ($basic_info['kf_ww']) {
				$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
				$kf_ww = explode('|', $kf_ww[0]);

				if (!empty($kf_ww[1])) {
					$basic_info['kf_ww'] = $kf_ww[1];
				}
				else {
					$basic_info['kf_ww'] = '';
				}
			}
			else {
				$basic_info['kf_ww'] = '';
			}

			if ($basic_info['kf_qq']) {
				$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
				$kf_qq = explode('|', $kf_qq[0]);

				if (!empty($kf_qq[1])) {
					$basic_info['kf_qq'] = $kf_qq[1];
				}
				else {
					$basic_info['kf_qq'] = '';
				}
			}
			else {
				$basic_info['kf_qq'] = '';
			}

			$smarty->assign('basic_info', $basic_info);
			$linked_goods = get_linked_goods($goods_id, $region_id, $area_id);
			$smarty->assign('related_goods', $linked_goods);
			$history_goods = get_history_goods($goods_id, $region_id, $area_id, $area_city);
			$smarty->assign('history_goods', $history_goods);
			assign_dynamic('exchange_goods');
			$seo = get_seo_words('change_content');

			foreach ($seo as $key => $value) {
				$seo[$key] = str_replace(array('{sitename}', '{key}', '{name}', '{description}'), array($_CFG['shop_name'], $goods['goods_style_name'], $goods['goods_name'], $goods['goods_style_name']), $value);
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
		}
	}

	$region = array(1, $province_id, $city_id, $district_id, $street_id, $street_list);
	$shippingFee = goodsShippingFee($goods_id, $region_id, $area_id, $region);
	$smarty->assign('shippingFee', $shippingFee);
	$smarty->assign('region_id', $region_id);
	$smarty->assign('area_id', $area_id);
	$smarty->assign('area_htmlType', 'exchange');
	$smarty->assign('integral_scale', price_format($_CFG['integral_scale']));
	$smarty->assign('category', $goods_id);
	$smarty->assign('user_id', $_SESSION['user_id']);
	$discuss_list = get_discuss_all_list($goods_id, 0, 1, 10);
	$smarty->assign('discuss_list', $discuss_list);
	$smarty->assign('user', get_user_info($_SESSION['user_id']));
	$smarty->display('exchange_goods.dwt', $cache_id);
}
else if ($_REQUEST['act'] == 'price') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'err_no' => 0, 'result' => '', 'qty' => 1);
	$goods_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$attr_id = isset($_REQUEST['attr']) && !empty($_REQUEST['attr']) ? explode(',', $_REQUEST['attr']) : array();
	$number = isset($_REQUEST['number']) ? intval($_REQUEST['number']) : 1;
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$onload = isset($_REQUEST['onload']) ? trim($_REQUEST['onload']) : '';
	$goods = get_goods_info($goods_id, $warehouse_id, $area_id);

	if ($goods_id == 0) {
		$res['err_msg'] = $_LANG['err_change_attr'];
		$res['err_no'] = 1;
	}
	else {
		if ($number == 0) {
			$res['qty'] = $number = 1;
		}
		else {
			$res['qty'] = $number;
		}

		$products = get_warehouse_id_attr_number($goods_id, $_REQUEST['attr'], $goods['user_id'], $warehouse_id, $area_id);
		$attr_number = $products['product_number'];

		if ($goods['model_attr'] == 1) {
			$table_products = 'products_warehouse';
			$type_files = ' and warehouse_id = \'' . $warehouse_id . '\'';
		}
		else if ($goods['model_attr'] == 2) {
			$table_products = 'products_area';
			$type_files = ' and area_id = \'' . $area_id . '\'';
		}
		else {
			$table_products = 'products';
			$type_files = '';
		}

		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . (' WHERE goods_id = \'' . $goods_id . '\'') . $type_files . ' LIMIT 0, 1';
		$prod = $GLOBALS['db']->getRow($sql);

		if ($goods['goods_type'] == 0) {
			$attr_number = $goods['goods_number'];
		}
		else {
			if (empty($prod)) {
				$attr_number = $goods['goods_number'];
			}

			if (!empty($prod) && $GLOBALS['_CFG']['add_shop_price'] == 0 && $onload == 'onload') {
				if (empty($attr_number)) {
					$attr_number = $goods['goods_number'];
				}
			}
		}

		$attr_number = !empty($attr_number) ? $attr_number : 0;
		$res['attr_number'] = $attr_number;
	}

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$area_list = get_goods_link_area_list($goods_id, $goods['user_id']);

		if ($area_list['goods_area']) {
			if (!in_array($area_id, $area_list['goods_area'])) {
				$res['err_no'] = 2;
			}
		}
		else {
			$res['err_no'] = 2;
		}
	}

	exit($json->encode($res));
}
else if ($_REQUEST['act'] == 'getInfo') {
	require_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => 0, 'message' => '');
	$attr_id = $_POST['attr_id'];
	$sql = 'SELECT attr_gallery_flie FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_attr_id = \'' . $attr_id . '\' and goods_id = \'' . $goods_id . '\'');
	$row = $db->getRow($sql);
	$result['t_img'] = $row['attr_gallery_flie'];
	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'buy') {
	if (!isset($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
		$back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'exchange') ? $GLOBALS['_SERVER']['HTTP_REFERER'] : './index.php';
	}

	if ($_SESSION['user_id'] <= 0) {
		show_message($_LANG['eg_error_login'], array($_LANG['back_up_page']), array($back_act), 'error');
	}

	$goods_number = isset($_POST['number']) ? intval($_POST['number']) : 0;
	$goods_id = isset($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;

	if ($goods_id <= 0) {
		ecs_header("Location: ./\n");
		exit();
	}

	$goods = get_exchange_goods_info($goods_id, $region_id, $area_id);

	if (empty($goods)) {
		ecs_header("Location: ./\n");
		exit();
	}

	if ($goods['is_exchange'] == 0) {
		show_message($_LANG['eg_error_status'], array($_LANG['back_up_page']), array($back_act), 'error');
	}

	$user_info = get_user_info($_SESSION['user_id']);
	$user_points = $user_info['payPoints'];

	if ($user_points < $goods['exchange_integral']) {
		show_message($_LANG['eg_error_integral'], array($_LANG['back_up_page']), array($back_act), 'error');
	}

	$specs = isset($_POST['goods_spec']) ? htmlspecialchars(trim($_POST['goods_spec'])) : '';

	if (!empty($specs)) {
		$_specs = explode(',', $specs);
		$product_info = get_products_info($goods_id, $_specs, $region_id, $area_id);
	}

	if (empty($product_info)) {
		$product_info = array('product_number' => '', 'product_id' => 0);
	}

	if ($goods['model_attr'] == 1) {
		$table_products = 'products_warehouse';
		$type_files = ' and warehouse_id = \'' . $warehouse_id . '\'';
	}
	else if ($goods['model_attr'] == 2) {
		$table_products = 'products_area';
		$type_files = ' and area_id = \'' . $area_id . '\'';
	}
	else {
		$table_products = 'products';
		$type_files = '';
	}

	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $goods['goods_id'] . '\'' . $type_files . ' LIMIT 0, 1';
	$prod = $GLOBALS['db']->getRow($sql);

	if ($_CFG['use_storage'] == 1) {
		$is_product = 0;
		if (is_spec($_specs) && !empty($prod)) {
			if ($product_info['product_number'] == 0) {
				show_message($_LANG['eg_error_number'], array($_LANG['back_up_page']), array($back_act), 'error');
			}
		}
		else {
			$is_product = 1;
		}

		if ($is_product == 1) {
			if ($goods['goods_number'] == 0) {
				show_message($_LANG['eg_error_number'], array($_LANG['back_up_page']), array($back_act), 'error');
			}
		}
	}

	$attr_list = array();
	$sql = 'SELECT a.attr_name, g.attr_value ' . 'FROM ' . $ecs->table('goods_attr') . ' AS g, ' . $ecs->table('attribute') . ' AS a ' . 'WHERE g.attr_id = a.attr_id ' . 'AND g.goods_attr_id ' . db_create_in($specs) . ' ORDER BY a.sort_order, a.attr_id, g.goods_attr_id';
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		$attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
	}

	$goods_attr = join(chr(13) . chr(10), $attr_list);
	include_once ROOT_PATH . 'includes/lib_order.php';
	clear_cart(CART_EXCHANGE_GOODS);
	$goods['exchange_integral'] = $goods['exchange_integral'] * $GLOBALS['_CFG']['integral_scale'] / 100;
	$cart = array('user_id' => $_SESSION['user_id'], 'session_id' => SESS_ID, 'goods_id' => $goods['goods_id'], 'product_id' => $product_info['product_id'], 'goods_sn' => addslashes($goods['goods_sn']), 'goods_name' => addslashes($goods['goods_name']), 'market_price' => $goods['marketPrice'], 'goods_price' => 0, 'goods_number' => $goods_number, 'goods_attr' => addslashes($goods_attr), 'goods_attr_id' => $specs, 'warehouse_id' => $region_id, 'area_id' => $area_id, 'ru_id' => $goods['user_id'], 'is_real' => $goods['is_real'], 'extension_code' => addslashes($goods['extension_code']), 'parent_id' => 0, 'rec_type' => CART_EXCHANGE_GOODS, 'is_gift' => 0);
	$db->autoExecute($ecs->table('cart'), $cart, 'INSERT');
	$_SESSION['flow_type'] = CART_EXCHANGE_GOODS;
	$_SESSION['extension_code'] = 'exchange_goods';
	$_SESSION['extension_id'] = $goods_id;
	$_SESSION['direct_shopping'] = 4;
	ecs_header("Location: ./flow.php?step=checkout&direct_shopping=4\n");
	exit();
}
else if ($_REQUEST['act'] == 'in_stock') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'result' => '', 'qty' => 1);
	clear_cache_files();
	$goods_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
	$province = empty($_REQUEST['province']) ? 1 : intval($_REQUEST['province']);
	$city = empty($_REQUEST['city']) ? 52 : intval($_REQUEST['city']);
	$district = empty($_REQUEST['district']) ? 500 : intval($_REQUEST['district']);
	$d_null = empty($_REQUEST['d_null']) ? 0 : intval($_REQUEST['d_null']);
	$user_address = get_user_address_region($user_id);
	$user_address = explode(',', $user_address['region_address']);
	setcookie('province', $province, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('city', $city, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('district', $district, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$regionId = 0;
	setcookie('regionId', $regionId, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('type_province', 0, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('type_city', 0, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('type_district', 0, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$res['d_null'] = $d_null;

	if ($d_null == 0) {
		if (in_array($district, $user_address)) {
			$res['isRegion'] = 1;
		}
		else {
			$res['message'] = $_LANG['Distribution_message'];
			$res['isRegion'] = 88;
		}
	}
	else {
		setcookie('district', '', gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}

	$res['goods_id'] = $goods_id;
	$flow_warehouse = get_warehouse_goods_region($province);
	setcookie('flow_region', $flow_warehouse['region_id'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	exit($json->encode($res));
}

?>
