<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function group_buy_count($children = '', $keywords)
{
	$now = gmtime();
	$where = '';
	$where .= ' AND g.is_delete = 0';

	if ($children) {
		$where .= ' AND (' . $children . ' OR ' . get_extension_goods($children) . ')';
	}

	if ($keywords) {
		$where = 'AND (ga.act_name LIKE \'%' . $keywords . '%\' OR g.goods_name LIKE \'%' . $keywords . '%\') ';
	}

	$sql = 'SELECT COUNT(*) ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON ga.goods_id = g.goods_id ' . 'WHERE ga.act_type = \'' . GAT_GROUP_BUY . '\' ' . ('AND ga.start_time <= \'' . $now . '\' AND ga.is_finished < 3 AND ga.review_status = 3 ') . $where;
	return $GLOBALS['db']->getOne($sql);
}

function group_buy_list($children = '', $size, $page, $keywords, $sort, $order, $type = '')
{
	$gb_list = array();
	$now = gmtime();
	$where = '';
	$where .= ' AND g.is_delete = 0 ';

	if ($children) {
		$where .= ' AND (' . $children . ' OR ' . get_extension_goods($children) . ')';
	}

	if ($keywords) {
		$where .= 'AND (b.act_name LIKE \'%' . $keywords . '%\' OR g.goods_name LIKE \'%' . $keywords . '%\') ';
	}

	if ($type) {
		if ($type == 'new') {
			$where .= ' AND b.is_new = 1';
		}
		else if ($type == 'hot') {
			$where .= ' AND b.is_hot = 1';
		}
	}

	if ($sort == 'comments_number') {
		$sql = 'SELECT b.*, IFNULL(g.goods_thumb, \'\') AS goods_thumb, b.act_id AS group_buy_id, g.market_price,' . 'b.start_time AS start_date, b.end_time AS end_date ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS b ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON b.goods_id = g.goods_id ' . 'WHERE b.act_type = \'' . GAT_GROUP_BUY . ('\' ' . $where . ' ') . ('AND b.start_time <= \'' . $now . '\' AND b.is_finished < 3 AND b.review_status = 3 ORDER BY g.') . $sort . ' ' . $order;
	}
	else {
		$sql = 'SELECT b.*, IFNULL(g.goods_thumb, \'\') AS goods_thumb, b.act_id AS group_buy_id, g.market_price,' . 'b.start_time AS start_date, b.end_time AS end_date ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS b ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON b.goods_id = g.goods_id ' . 'WHERE b.act_type = \'' . GAT_GROUP_BUY . ('\' ' . $where . ' ') . ('AND b.start_time <= \'' . $now . '\' AND b.is_finished < 3 AND b.review_status = 3 ORDER BY b.') . $sort . ' ' . $order;
	}

	if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'load_more_goods') {
		$start = intval($_REQUEST['goods_num']);
	}
	else {
		$start = ($page - 1) * $size;
	}

	$res = $GLOBALS['db']->selectLimit($sql, $size, $start);

	while ($group_buy = $GLOBALS['db']->fetchRow($res)) {
		$ext_info = unserialize($group_buy['ext_info']);
		$group_buy = array_merge($group_buy, $ext_info);
		$group_buy['formated_start_date'] = local_date($GLOBALS['_CFG']['time_format'], $group_buy['start_date']);
		$group_buy['formated_end_date'] = local_date($GLOBALS['_CFG']['time_format'], $group_buy['end_date']);
		$group_buy['is_end'] = $group_buy['end_date'] < $now ? 1 : 0;
		$group_buy['formated_deposit'] = price_format($group_buy['deposit'], false);
		$price_ladder = $group_buy['price_ladder'];
		if (!is_array($price_ladder) || empty($price_ladder)) {
			$price_ladder = array(
				array('amount' => 0, 'price' => 0)
				);
		}
		else {
			foreach ($price_ladder as $key => $amount_price) {
				$price_ladder[$key]['formated_price'] = price_format($amount_price['price']);
			}
		}

		$group_buy['price_ladder'] = $price_ladder;
		$price = $group_buy['market_price'];
		$nowprice = $group_buy['price_ladder'][0]['price'];
		$group_buy['jiesheng'] = $price - $nowprice;

		if (0 < $nowprice) {
			$group_buy['zhekou'] = round(10 / ($price / $nowprice), 1);
		}
		else {
			$group_buy['zhekou'] = 0;
		}

		$stat = group_buy_stat($group_buy['act_id'], $ext_info['deposit']);
		$group_buy['cur_amount'] = $stat['valid_goods'];
		$group_buy['goods_thumb'] = get_image_path($group_buy['goods_id'], $group_buy['goods_thumb'], true);
		$group_buy['url'] = build_uri('group_buy', array('gbid' => $group_buy['group_buy_id']));
		$gb_list[] = $group_buy;
	}

	return $gb_list;
}

function get_linked_goods($goods_id, $warehouse_id = 0, $area_id = 0, $area_city = 0)
{
	$leftJoin = '';
	$where_area = '';

	if ($GLOBALS['_CFG']['area_pricetype'] == 1) {
		$where_area = ' AND wag.city_id = \'' . $area_city . '\'';
	}

	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ' . $where_area . ' ');
	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'g.market_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . ' g.promote_start_date, g.promote_end_date ' . 'FROM ' . $GLOBALS['ecs']->table('link_goods') . ' lg ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = lg.link_goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . $leftJoin . ('WHERE lg.goods_id = \'' . $goods_id . '\' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ') . 'LIMIT ' . $GLOBALS['_CFG']['related_goods_number'];
	$res = $GLOBALS['db']->query($sql);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$watermark_img = '';

		if ($promote_price != 0) {
			$watermark_img = 'watermark_promote_small';
		}
		else if ($row['is_new'] != 0) {
			$watermark_img = 'watermark_new_small';
		}
		else if ($row['is_best'] != 0) {
			$watermark_img = 'watermark_best_small';
		}
		else if ($row['is_hot'] != 0) {
			$watermark_img = 'watermark_hot_small';
		}

		if ($watermark_img != '') {
			$arr[$row['goods_id']]['watermark_img'] = $watermark_img;
		}

		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
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

function get_top_group_goods($order, $user_id = 0)
{
	$sql = 'SELECT ga.*, g.sales_volume, g.goods_thumb, g.goods_id FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' ga' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' g ON ga.goods_id = g.goods_id ' . (' WHERE ga.user_id = \'' . $user_id . '\' AND g.goods_id > 0 AND ga.review_status = 3 AND act_type = \'') . GAT_GROUP_BUY . '\' ORDER BY g.' . $order . ' LIMIT 5 ';
	$look_top_list = $GLOBALS['db']->getAll($sql);

	foreach ($look_top_list as $key => $look_top) {
		$ext_info = unserialize($look_top['ext_info']);
		$look_top['ext_info'] = $ext_info;
		$price_ladder = $look_top['ext_info']['price_ladder'];
		if (!is_array($price_ladder) || empty($price_ladder)) {
			$price_ladder = array(
				array('amount' => 0, 'price' => 0)
				);
		}
		else {
			foreach ($price_ladder as $k => $amount_price) {
				$price_ladder[$k]['formated_price'] = price_format($amount_price['price'], false);
			}
		}

		$look_top['ext_info']['price_ladder'] = $price_ladder;
		$cur_price = $price_ladder[0]['price'];
		$stat = group_buy_stat($look_top['act_id'], $look_top['deposit']);
		$look_top = array_merge($look_top, $stat);
		$cur_amount = $stat['valid_goods'];

		foreach ($price_ladder as $amount_price) {
			if ($amount_price['amount'] <= $cur_amount) {
				$cur_price = $amount_price['price'];
			}
			else {
				break;
			}
		}

		$look_top['goods_thumb'] = get_image_path($look_top['goods_id'], $look_top['goods_thumb'], true);
		$look_top['ext_info']['cur_price'] = price_format($cur_price, false);
		$look_top_list_1[$key] = $look_top;
	}

	return $look_top_list_1;
}

function get_merchant_group_goods($group_buy_id)
{
	$ru_id = $GLOBALS['db']->getOne('SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE act_id = \'' . $group_buy_id . '\''));
	$sql = 'SELECT ga.act_id, ga.ext_info, ga.act_name, g.goods_thumb, g.sales_volume FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' ga' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' g ON ga.goods_id = g.goods_id ' . (' WHERE ga.user_id = \'' . $ru_id . '\' AND ga.review_status = 3 AND act_type = \'') . GAT_GROUP_BUY . '\' LIMIT 4 ';
	$merchant_group = $GLOBALS['db']->getAll($sql);

	foreach ($merchant_group as $key => $row) {
		$ext_info = unserialize($row['ext_info']);
		$row = array_merge($row, $ext_info);
		$merchant_group[$key]['cur_price'] = $row['ext_info']['cur_price'];
		$price_ladder = $row['price_ladder'];
		if (!is_array($price_ladder) || empty($price_ladder)) {
			$price_ladder = array(
				array('amount' => 0, 'price' => 0)
				);
		}
		else {
			foreach ($price_ladder as $k => $amount_price) {
				$price_ladder[$k]['formated_price'] = price_format($amount_price['price'], false);
			}
		}

		$merchant_group[$key]['shop_price'] = $price_ladder[0]['formated_price'];
		$merchant_group[$key]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
	}

	return $merchant_group;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/lib_area.php';
$warehouse_other = array('province_id' => $province_id, 'city_id' => $city_id);
$warehouse_area_info = get_warehouse_area_info($warehouse_other);
$region_id = $warehouse_area_info['region_id'];
$area_id = $warehouse_area_info['area_id'];
$area_city = $warehouse_area_info['city_id'];
$keywords = !empty($_REQUEST['keywords']) ? htmlspecialchars(trim($_REQUEST['keywords'])) : '';

if (isset($_REQUEST['keywords'])) {
	clear_all_files();
}

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

$user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$uachar = '/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i';
if (($ua == '' || preg_match($uachar, $ua)) && !strpos(strtolower($_SERVER['REQUEST_URI']), 'wap')) {
	if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'view') {
		$group_buy_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	}

	$Loaction = 'mobile/index.php?m=groupbuy&a=detail&id=' . $group_buy_id;

	if (!empty($Loaction)) {
		ecs_header('Location: ' . $Loaction . "\n");
		exit();
	}
}

$template = 'group_buy_list';

if (empty($_REQUEST['act'])) {
	if (defined('THEME_EXTENSION')) {
		$template = 'group_buy';
	}

	$_REQUEST['act'] = 'list';
}

if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'in_warehouse') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'result' => '', 'qty' => 1);
	clear_cache_files();
	setcookie('region_id', $pid, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('regionId', $pid, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$area_region = 0;
	setcookie('area_region', $area_region, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$res['goods_id'] = $goods_id;
	exit();
	$json = new JSON();
	exit($json->encode($res));
}

if ($_REQUEST['act'] == 'list') {
	$_REQUEST['sort'] = isset($_REQUEST['sort']) ? dsc_addslashes(trim($_REQUEST['sort'])) : '';
	$smarty->assign('category_load_type', $_CFG['category_load_type']);
	$smarty->assign('query_string', preg_replace('/act=\\w+&?/', '', $_SERVER['QUERY_STRING']));
	$cat_id = isset($_REQUEST['cat_id']) && 0 < intval($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$page = isset($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$size = isset($_CFG['page_size']) && 0 < intval($_CFG['page_size']) ? intval($_CFG['page_size']) : 10;
	$size = 20;
	$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';

	if ($_REQUEST['sort'] == 'comments_number') {
		$default_sort_order_type = $_CFG['sort_order_type'] == '0' ? 'start_time' : ($_CFG['sort_order_type'] == '1' ? 'shop_price' : 'last_update');
	}
	else {
		$default_sort_order_type = 'act_id';
	}

	$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('act_id', 'start_time', 'sales_volume', 'comments_number')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
	$order = isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')) ? trim($_REQUEST['order']) : $default_sort_order_method;
	$children = '';
	$count = 0;

	if ($template == 'group_buy_list') {
		if ($cat_id) {
			$children = get_children($cat_id);
		}

		$count = group_buy_count($children, $keywords);
	}

	if (0 < $count && $template == 'group_buy_list') {
		$page_count = ceil($count / $size);
		$page = isset($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
		$page = $page_count < $page ? $page_count : $page;
		$cache_id = $_CFG['lang'] . '-' . $cat_id . '-' . $size . '-' . $page . '-' . $sort . '-' . $order . '-' . $keywords;
		$cache_id = sprintf('%X', crc32($cache_id));
	}
	else {
		$cache_id = $_CFG['lang'];
		$cache_id = sprintf('%X', crc32($cache_id));
	}

	if (!$smarty->is_cached($template . '.dwt', $cache_id)) {
		if (0 < $count && $template == 'group_buy_list') {
			$gb_list = group_buy_list($children, $size, $page, $keywords, $sort, $order);
			$smarty->assign('gb_list', $gb_list);

			if (!$_CFG['category_load_type']) {
				$pager = get_pager('group_buy.php', array('act' => 'list', 'keywords' => $keywords, 'sort' => $sort, 'order' => $order), $count, $page, $size);
				$smarty->assign('pager', $pager);
			}
		}

		$smarty->assign('cfg', $_CFG);
		assign_template();
		$position = assign_ur_here(0, $_LANG['group_buy']);
		$smarty->assign('ur_here', $position['ur_here']);
		$seo = get_seo_words('group');

		foreach ($seo as $key => $value) {
			$seo[$key] = str_replace(array('{sitename}', '{key}', '{description}'), array($_CFG['shop_name'], $_CFG['shop_keywords'], $_CFG['shop_desc']), $value);
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

		if ($template == 'group_buy_list') {
			if (defined('THEME_EXTENSION')) {
				$categories_pro = get_category_tree_leve_one();
				$smarty->assign('categories_pro', $categories_pro);
			}
		}
		else {
			$category_list = cat_list();
			$smarty->assign('category_list', $category_list);
		}

		$smarty->assign('helps', get_shop_help());
		$smarty->assign('feed_url', $_CFG['rewrite'] == 1 ? 'feed-typegroup_buy.xml' : 'feed.php?type=group_buy');

		if ($template == 'group_buy') {
			$group_top_banner = '';

			for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
				$group_top_banner .= '\'activity_top_ad_group_buy' . $i . ',';
			}

			$smarty->assign('activity_top_banner', $group_top_banner);
			$new_list = group_buy_list($children, 5, 1, $keywords, $sort, $order, 'new');
			$smarty->assign('new_list', $new_list);
			$hot_list = group_buy_list($children, 10, 1, $keywords, $sort, $order, 'hot');
			$smarty->assign('hot_list', $hot_list);
		}

		$smarty->assign('cat_id', $cat_id);
		assign_dynamic('group_buy_list');
	}

	$smarty->display($template . '.dwt', $cache_id);
}
else if ($_REQUEST['act'] == 'load_more_goods') {
	$cat_id = isset($_REQUEST['cat_id']) && 0 < intval($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$page = isset($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$size = isset($_CFG['page_size']) && 0 < intval($_CFG['page_size']) ? intval($_CFG['page_size']) : 10;
	$size = 20;
	$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';

	if ($_REQUEST['sort'] == 'comments_number') {
		$default_sort_order_type = $_CFG['sort_order_type'] == '0' ? 'goods_id' : ($_CFG['sort_order_type'] == '1' ? 'shop_price' : 'last_update');
	}
	else {
		$default_sort_order_type = 'act_id';
	}

	$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('act_id', 'goods_id', 'sales_volume', 'comments_number')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
	$order = isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')) ? trim($_REQUEST['order']) : $default_sort_order_method;
	$children = get_children($cat_id);
	$count = group_buy_count($children, $keywords);

	if (0 < $count) {
		$gb_list = group_buy_list($children, $size, $page, $keywords, $sort, $order);
		$smarty->assign('gb_list', $gb_list);
		$smarty->assign('type', 'group_buy');
		$result = array('error' => 0, 'message' => '', 'cat_goods' => '', 'best_goods' => '');
		$result['cat_goods'] = html_entity_decode($smarty->fetch('library/more_goods_page.lbi'));
		exit(json_encode($result));
	}
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
else if ($_REQUEST['act'] == 'view') {
	$group_buy_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if ($group_buy_id <= 0) {
		ecs_header("Location: ./\n");
		exit();
	}

	$group_buy = group_buy_info($group_buy_id);
	$smarty->assign('comment_percent', comment_percent($group_buy['goods_id']));

	if (!$group_buy) {
		show_message($_LANG['now_not_snatch']);
	}

	$first_month_day = local_mktime(0, 0, 0, date('m'), 1, date('Y'));
	$last_month_day = local_mktime(0, 0, 0, date('m'), date('t'), date('Y')) + 24 * 60 * 60 - 1;
	$group_list = get_month_day_start_end_goods($group_buy_id, $first_month_day, $last_month_day);
	$smarty->assign('group_list', $group_list);
	$merchant_group = get_merchant_group_goods($group_buy_id);
	$smarty->assign('merchant_group_goods', $merchant_group);
	$smarty->assign('look_top', get_top_group_goods('click_count', $user_id));
	$smarty->assign('buy_top', get_top_group_goods('sales_volume', $user_id));

	if (empty($group_buy)) {
		ecs_header("Location: ./\n");
		exit();
	}

	$cache_id = $_CFG['lang'] . '-' . $group_buy_id . '-' . $group_buy['status'] . gmtime();

	if ($group_buy['status'] == GBS_UNDER_WAY) {
		$cache_id = $cache_id . '-' . $group_buy['valid_goods'] . '-' . intval(0 < $_SESSION['user_id']);
	}

	$cache_id = sprintf('%X', crc32($cache_id));

	if (!$smarty->is_cached('group_buy_goods.dwt', $cache_id)) {
		$group_buy['gmt_end_date'] = $group_buy['end_date'];
		$smarty->assign('group_buy', $group_buy);
		$goods_id = $group_buy['goods_id'];
		$goods = goods_info($goods_id);
		$smarty->assign('id', $goods_id);
		$smarty->assign('type', 0);

		if (empty($goods)) {
			ecs_header("Location: ./\n");
			exit();
		}

		$goods['url'] = build_uri('goods', array('gid' => $goods_id), $goods['goods_name']);
		$smarty->assign('gb_goods', $goods);
		$properties = get_goods_properties($goods_id, $region_id, $area_id, $area_city);
		$smarty->assign('properties', $properties['pro']);
		$smarty->assign('specification', $properties['spe']);
		$smarty->assign('cfg', $_CFG);
		assign_template();
		$linked_goods = get_linked_goods($goods_id, $region_id, $area_id, $area_city);

		if (defined('THEME_EXTENSION')) {
			$position = assign_ur_here($group_buy['cat_id'], $group_buy['goods_name'], array(), '', $group_buy['user_id']);
		}
		else {
			$position = assign_ur_here(0, $goods['goods_name']);
		}

		$smarty->assign('page_title', $position['title']);
		$smarty->assign('ur_here', $position['ur_here']);
		$smarty->assign('price_ladder', $group_buy['price_ladder']);
		$min_amount = 0;

		if ($group_buy['price_ladder']) {
			$ladder_arr = array();
			$ladder_arr = array_column($group_buy['price_ladder'], 'amount');

			if (!empty($ladder_arr)) {
				$min_amount = min($ladder_arr);
			}
		}

		$smarty->assign('min_amount', $min_amount);

		if (!defined('THEME_EXTENSION')) {
			$categories_pro = get_category_tree_leve_one();
			$smarty->assign('categories_pro', $categories_pro);
		}

		$smarty->assign('related_goods', $linked_goods);
		$smarty->assign('brand_list', get_brands());
		$smarty->assign('helps', get_shop_help());

		if ($area_id == NULL) {
			$area_id = 0;
		}

		$area = array('region_id' => $region_id, 'province_id' => $province_id, 'city_id' => $city_id, 'district_id' => $district_id, 'goods_id' => $goods_id, 'user_id' => $user_id, 'area_id' => $area_id, 'merchant_id' => $goods['user_id']);
		$smarty->assign('area', $area);
		$smarty->assign('area_htmlType', 'group_buy');
		assign_dynamic('group_buy_goods');
	}

	$smarty->assign('category', $goods_id);
	$sql = 'UPDATE ' . $ecs->table('goods') . ' SET click_count = click_count + 1 ' . 'WHERE goods_id = \'' . $group_buy['goods_id'] . '\'';
	$db->query($sql);
	$comment_all = get_comments_percent($goods_id);
	$merchants_goods_comment = get_merchants_goods_comment($goods['user_id']);
	$smarty->assign('comment_all', $comment_all);
	$smarty->assign('merch_cmt', $merchants_goods_comment);

	if ($GLOBALS['_CFG']['customer_service'] == 0) {
		$goods['user_id'] = 0;
	}

	$basic_info = get_shop_info_content($goods['user_id']);
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

	$group_buy['is_collect'] = '';

	if (0 < $_SESSION['user_id']) {
		$sql = ' SELECT rec_id FROM ' . $ecs->table('collect_goods') . ' WHERE goods_id = \'' . $group_buy['goods_id'] . ' \' AND  user_id = \'' . $_SESSION['user_id'] . '\'';
		$group_buy['is_collect'] = $db->getOne($sql);
	}

	if (defined('THEME_EXTENSION')) {
		$sql = 'SELECT rec_id FROM ' . $ecs->table('collect_store') . ' WHERE user_id = \'' . $_SESSION['user_id'] . ('\' AND ru_id = \'' . $group_buy['user_id'] . '\' ');
		$rec_id = $db->getOne($sql);

		if (0 < $rec_id) {
			$group_buy['error'] = '1';
		}
		else {
			$group_buy['error'] = '2';
		}
	}

	$goods_info = goods_info($goods_id);

	if ($GLOBALS['_CFG']['customer_service'] == 0) {
		$goods_info['user_id'] = 0;
	}

	$shop_information = get_shop_name($goods_info['user_id']);

	if ($goods_info['user_id'] == 0) {
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

	$shop_information['goods_id'] = $goods_id;
	$smarty->assign('shop_information', $shop_information);
	$region = array(1, $province_id, $city_id, $district_id);
	$shippingFee = goodsShippingFee($goods_id, $region_id, $area_id, $region);
	$smarty->assign('shippingFee', $shippingFee);
	$smarty->assign('basic_info', $basic_info);
	$smarty->assign('goods', $group_buy);
	$smarty->assign('pictures', get_goods_gallery($goods_id));
	$smarty->assign('now_time', gmtime());
	$linked_goods = get_linked_goods($goods_id, $region_id, $area_id, $area_city);
	$smarty->assign('related_goods', $linked_goods);
	$history_goods = get_history_goods($goods_id, $region_id, $area_id);
	$smarty->assign('history_goods', $history_goods);
	$smarty->assign('region_id', $region_id);
	$smarty->assign('area_id', $area_id);
	$start_date = $group_buy['xiangou_start_date'];
	$end_date = $group_buy['xiangou_end_date'];
	$order_goods = get_for_purchasing_goods($start_date, $end_date, $goods_id, $_SESSION['user_id'], 'group_buy');
	$smarty->assign('xiangou', $xiangou);
	$smarty->assign('orderG_number', $order_goods['goods_number']);

	if ($_CFG['two_code']) {
		$group_buy_path = ROOT_PATH . IMAGE_DIR . '/group_wenxin/';

		if (!file_exists($group_buy_path)) {
			make_dir($group_buy_path);
		}

		$logo = empty($_CFG['two_code_logo']) ? $goods['goods_img'] : str_replace('../', '', $_CFG['two_code_logo']);
		$size = '200x200';
		$url = $ecs->url();
		$two_code_links = trim($_CFG['two_code_links']);
		$two_code_links = empty($two_code_links) ? $url : $two_code_links;
		$data = $two_code_links . 'group_buy.php?act=view&id=' . $group_buy_id;
		$errorCorrectionLevel = 'H';
		$matrixPointSize = 4;
		$filename = IMAGE_DIR . '/group_wenxin/weixin_code_' . $goods['goods_id'] . '.png';

		if (!file_exists(ROOT_PATH . $filename)) {
			require ROOT_PATH . 'includes/phpqrcode/phpqrcode.php';
			QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize);
			$QR = imagecreatefrompng($filename);

			if ($logo !== false) {
				$logo = imagecreatefromstring(file_get_contents($logo));
				$QR_width = imagesx($QR);
				$QR_height = imagesy($QR);
				$logo_width = imagesx($logo);
				$logo_height = imagesy($logo);
				$logo_qr_width = $QR_width / 5;
				$scale = $logo_width / $logo_qr_width;
				$logo_qr_height = $logo_height / $scale;
				$from_width = ($QR_width - $logo_qr_width) / 2;
				imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
			}

			imagepng($QR, $filename);
			imagedestroy($QR);
		}

		$smarty->assign('weixin_img_url', $filename);
		$smarty->assign('weixin_img_text', trim($_CFG['two_code_mouse']));
		$smarty->assign('two_code', trim($_CFG['two_code']));
	}

	$seo = get_seo_words('group_content');

	foreach ($seo as $key => $value) {
		$seo[$key] = str_replace(array('{name}', '{description}'), array($group_buy['act_name'], $group_buy['act_desc']), $value);
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

	$smarty->assign('user_id', $_SESSION['user_id']);
	$smarty->display('group_buy_goods.dwt', $cache_id);
}
else if ($_REQUEST['act'] == 'price') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'err_no' => 0, 'result' => '', 'qty' => 1);
	$goods_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$attr_id = isset($_REQUEST['attr']) ? explode(',', $_REQUEST['attr']) : array();
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

		if (0 < $goods['cloud_id']) {
			$attr_number = 0;

			if (!empty($attr_id)) {
				$plugin_file = ROOT_PATH . '/plugins/cloudApi/cloudApi.php';
				$sql = 'SELECT cloud_product_id FROM' . $ecs->table('products') . 'WHERE product_id = \'' . $products['product_id'] . '\'';
				$productIds = $db->getCol($sql);

				if (file_exists($plugin_file)) {
					include_once $plugin_file;
					$cloud = new cloud();
					$cloud_prod = $cloud->queryInventoryNum($productIds);
					$cloud_prod = json_decode($cloud_prod, true);

					if ($cloud_prod['code'] == 10000) {
						$cloud_product = $cloud_prod['data'];

						if ($cloud_product) {
							foreach ($cloud_product as $k => $v) {
								if (in_array($v['productId'], $productIds)) {
									if ($v['hasTax'] == 1) {
										$attr_number = $v['taxNum'];
									}
									else {
										$attr_number = $v['noTaxNum'];
									}

									break;
								}
							}
						}
					}
				}
			}
		}
		else if ($goods['goods_type'] == 0) {
			$attr_number = $goods['goods_number'];
		}
		else if (empty($prod)) {
			$attr_number = $goods['goods_number'];
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

	$res['onload'] = $onload;
	exit($json->encode($res));
}
else if ($_REQUEST['act'] == 'buy') {
	$goods_attr = isset($_POST['goods_attr_id']) && !empty($_POST['goods_attr_id']) ? dsc_addslashes($_POST['goods_attr_id'], 0) : '';

	if ($_SESSION['user_id'] <= 0) {
		show_message($_LANG['gb_error_login'], '', '', 'error');
	}

	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$group_buy_id = isset($_POST['group_buy_id']) ? intval($_POST['group_buy_id']) : 0;

	if ($group_buy_id <= 0) {
		ecs_header("Location: ./\n");
		exit();
	}

	$number = isset($_POST['number']) ? intval($_POST['number']) : 1;
	$number = $number < 1 ? 1 : $number;
	$group_buy = group_buy_info($group_buy_id, $number);

	if (empty($group_buy)) {
		ecs_header("Location: ./\n");
		exit();
	}

	if ($group_buy['status'] != GBS_UNDER_WAY) {
		show_message($_LANG['gb_error_status'], '', '', 'error');
	}

	$goods = goods_info($group_buy['goods_id'], $region_id, $area_id);

	if (empty($goods)) {
		ecs_header("Location: ./\n");
		exit();
	}

	$prod = array();
	$products = array();

	if ($goods_attr) {
		if ($goods['model_attr'] == 1) {
			$table_products = 'products_warehouse';
			$type_files = ' AND warehouse_id = \'' . $region_id . '\'';
		}
		else if ($goods['model_attr'] == 2) {
			$table_products = 'products_area';
			$type_files = ' AND area_id = \'' . $area_id . '\'';
		}
		else {
			$table_products = 'products';
			$type_files = '';
		}

		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $goods['goods_id'] . '\'' . $type_files . ' LIMIT 1';
		$prod = $GLOBALS['db']->getRow($sql);
		$products = get_warehouse_id_attr_number($goods['goods_id'], $goods_attr, $goods['user_id'], $region_id, $area_id);
		$product_number = isset($products['product_number']) ? $products['product_number'] : 0;
	}

	if ($prod) {
		$goods_number = $product_number;
	}
	else {
		$goods_number = $goods['goods_number'];
	}

	if ($goods_attr && $goods['cloud_id']) {
		$plugin_file = ROOT_PATH . '/plugins/cloudApi/cloudApi.php';
		$sql = 'SELECT cloud_product_id FROM' . $ecs->table('products') . 'WHERE product_id = \'' . $products['product_id'] . '\'';
		$productIds = $db->getCol($sql);

		if (file_exists($plugin_file)) {
			include_once $plugin_file;
			$cloud = new cloud();
			$cloud_prod = $cloud->queryInventoryNum($productIds);
			$cloud_prod = json_decode($cloud_prod, true);

			if ($cloud_prod['code'] == 10000) {
				$cloud_product = $cloud_prod['data'];

				if ($cloud_product) {
					foreach ($cloud_product as $k => $v) {
						if (in_array($v['productId'], $productIds)) {
							if ($v['hasTax'] == 1) {
								$goods_number = $v['taxNum'];
							}
							else {
								$goods_number = $v['noTaxNum'];
							}

							break;
						}
					}
				}
			}
		}
	}

	$url = build_uri('group_buy', array('gbid' => $group_buy_id));

	if ($goods_number <= 0) {
		show_message($GLOBALS['_LANG']['buy_error'], $GLOBALS['_LANG']['go_back'], $url);
		exit();
	}

	$start_date = $group_buy['xiangou_start_date'];
	$end_date = $group_buy['xiangou_end_date'];
	$order_goods = get_for_purchasing_goods($start_date, $end_date, $group_buy['goods_id'], $_SESSION['user_id'], 'group_buy');
	$restrict_amount = $number + $order_goods['goods_number'];
	if (0 < $group_buy['restrict_amount'] && $group_buy['restrict_amount'] < $restrict_amount) {
		show_message($_LANG['gb_error_restrict_amount'], '', '', 'error');
	}
	else {
		if (0 < $group_buy['restrict_amount'] && $group_buy['restrict_amount'] - $group_buy['valid_goods'] < $number) {
			show_message($_LANG['gb_error_goods_lacking'], '', '', 'error');
		}
	}

	if ($goods_attr) {
		$goods_attr_id = $goods_attr;
		$attr_list = array();
		$sql = 'SELECT a.attr_name, g.attr_value ' . 'FROM ' . $ecs->table('goods_attr') . ' AS g, ' . $ecs->table('attribute') . ' AS a ' . 'WHERE g.attr_id = a.attr_id ' . 'AND g.goods_attr_id ' . db_create_in($goods_attr_id) . ' ORDER BY a.sort_order, a.attr_id, g.goods_attr_id';
		$res = $db->query($sql);

		while ($row = $db->fetchRow($res)) {
			$attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
		}

		$goods_attr = join(chr(13) . chr(10), $attr_list);
	}
	else {
		$goods_attr = '';
		$goods_attr_id = '';
	}

	include_once ROOT_PATH . 'includes/lib_order.php';
	clear_cart(CART_GROUP_BUY_GOODS);

	if (!empty($_SESSION['user_id'])) {
		$sess = '';
	}
	else {
		$sess = real_cart_mac_ip();
	}

	$goods_price = 0 < $group_buy['deposit'] ? $group_buy['deposit'] : $group_buy['cur_price'];
	$cart = array('user_id' => $_SESSION['user_id'], 'session_id' => $sess, 'goods_id' => $group_buy['goods_id'], 'product_id' => $products['product_id'], 'goods_sn' => addslashes($goods['goods_sn']), 'goods_name' => addslashes($goods['goods_name']), 'market_price' => $goods['market_price'], 'goods_price' => $goods_price, 'goods_number' => $number, 'goods_attr' => addslashes($goods_attr), 'goods_attr_id' => $goods_attr_id, 'ru_id' => $goods['user_id'], 'warehouse_id' => $region_id, 'area_id' => $area_id, 'is_real' => $goods['is_real'], 'extension_code' => addslashes($goods['extension_code']), 'parent_id' => 0, 'rec_type' => CART_GROUP_BUY_GOODS, 'is_gift' => 0);
	$db->autoExecute($ecs->table('cart'), $cart, 'INSERT');
	$_SESSION['flow_type'] = CART_GROUP_BUY_GOODS;
	$_SESSION['extension_code'] = 'group_buy';
	$_SESSION['extension_id'] = $group_buy_id;
	$_SESSION['browse_trace'] = 'group_buy';
	ecs_header("Location: ./flow.php?step=checkout&direct_shopping=5\n");
	exit();
}

?>
