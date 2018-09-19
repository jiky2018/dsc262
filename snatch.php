<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_myprice($id)
{
	$my_only_price = array();
	$my_price_time = array();
	$pay_points = 0;
	$bid_price = array();

	if (!empty($_SESSION['user_id'])) {
		$sql = 'SELECT bid_price, bid_time FROM ' . $GLOBALS['ecs']->table('snatch_log') . (' WHERE snatch_id = \'' . $id . '\' AND user_id = \'' . $_SESSION['user_id'] . '\' ORDER BY bid_time DESC');
		$my_price_time = $GLOBALS['db']->GetAll($sql);
		$sql = 'SELECT bid_price FROM ' . $GLOBALS['ecs']->table('snatch_log') . (' WHERE snatch_id = \'' . $id . '\' AND user_id = \'' . $_SESSION['user_id'] . '\' ORDER BY bid_time DESC');
		$my_price = $GLOBALS['db']->GetCol($sql);

		if ($my_price_time) {
			$sql = 'SELECT bid_price , count(*) AS num FROM ' . $GLOBALS['ecs']->table('snatch_log') . ('  WHERE snatch_id =\'' . $id . '\' AND bid_price ') . db_create_in(join(',', $my_price)) . ' GROUP BY bid_price HAVING num = 1';
			$my_only_price = $GLOBALS['db']->GetCol($sql);
		}

		$user_name = $GLOBALS['db']->getOne('SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $_SESSION['user_id'] . '\' '));
		$i = 0;

		for ($count = count($my_price_time); $i < $count; $i++) {
			$bid_price[] = array('price' => price_format($my_price_time[$i]['bid_price'], false), 'bid_price' => price_format($my_price_time[$i]['bid_price'], false), 'user_name' => $user_name, 'bid_date' => local_date('Y-m-d H:i:s', $my_price_time[$i]['bid_time']), 'is_only' => in_array($my_price_time[$i]['bid_price'], $my_only_price));
		}

		$sql = 'SELECT pay_points FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $_SESSION['user_id'] . '\'');
		$pay_points = $GLOBALS['db']->GetOne($sql);
		$pay_points = $pay_points . $GLOBALS['_CFG']['integral_name'];
	}

	$sql = 'SELECT end_time FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE act_id = \'' . $id . '\' AND review_status = 3 AND act_type=') . GAT_SNATCH;
	$end_time = $GLOBALS['db']->getOne($sql);
	$my_price_time = array('pay_points' => $pay_points, 'bid_price' => $bid_price, 'bid_price_count' => count($bid_price), 'is_end' => $end_time < gmtime());
	return $my_price_time;
}

function get_price_list($id)
{
	$sql = 'SELECT t1.log_id, t1.bid_price, t1.bid_time, t2.user_name FROM ' . $GLOBALS['ecs']->table('snatch_log') . ' AS t1, ' . $GLOBALS['ecs']->table('users') . ' AS t2 ' . ('WHERE snatch_id = \'' . $id . '\' AND t1.user_id = t2.user_id ORDER BY t1.log_id DESC');
	$res = $GLOBALS['db']->query($sql);
	$price_list = array();

	while ($row = $GLOBALS['db']->FetchRow($res)) {
		$row['user_name'] = setAnonymous($row['user_name']);
		$price_list[] = array('bid_price' => price_format($row['bid_price'], false), 'user_name' => $row['user_name'], 'bid_date' => local_date('Y-m-d H:i', $row['bid_time']));
	}

	return $price_list;
}

function get_snatch_list($keywords = '', $size, $page, $sort, $order, $warehouse_id, $area_id)
{
	$where = '';
	$leftJoin = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($keywords) {
		$where = ' AND (ga.act_name LIKE \'%' . $keywords . '%\' OR g.goods_name LIKE \'%' . $keywords . '%\') ';
	}

	$now = gmtime();
	$sql = 'SELECT ga.act_id AS snatch_id, ga.act_name AS snatch_name, ga.end_time, ga.start_time, ga.ext_info, IFNULL(g.goods_thumb, \'\') AS goods_thumb, g.market_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'g.promote_start_date, g.promote_end_date' . ' FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON ga.goods_id = g.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . $leftJoin . (' WHERE ga.review_status = 3 AND ga.start_time <= \'' . $now . '\' AND g.goods_id <> \'\' AND ga.act_type=') . GAT_SNATCH . $where . (' ORDER BY ' . $sort . ' ' . $order . ' ');
	$snatch_list = array();
	$overtime = 0;
	if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'load_more_goods') {
		$start = intval($_REQUEST['goods_num']);
	}
	else {
		$start = ($page - 1) * $size;
	}

	$res = $GLOBALS['db']->selectLimit($sql, $size, $start);

	while ($row = $GLOBALS['db']->FetchRow($res)) {
		$overtime = $now < $row['end_time'] ? 0 : 1;
		$ext_info = unserialize($row['ext_info']);
		$snatch = array_merge($row, $ext_info);
		$row['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['start_time']);
		$row['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['end_time']);
		$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		$shop_price = 0 < $promote_price ? $promote_price : $row['shop_price'];
		$snatch['max_price'] = price_format($snatch['max_price']);
		$snatch['end_time_date'] = local_date('Y-m-d H:i:s', $snatch['end_time']);
		$snatch_list[] = array('snatch_id' => $row['snatch_id'], 'snatch_name' => $row['snatch_name'], 'snatch' => $snatch, 'start_time' => $row['start_time'], 'max_price' => price_format($snatch['max_price']), 'end_time' => $row['end_time'], 'current_time' => local_date('Y-m-d H:i:s', gmtime()), 'overtime' => $overtime, 'formated_market_price' => price_format($row['market_price']), 'formated_shop_price' => price_format($shop_price), 'goods_thumb' => get_image_path($row['goods_id'], $row['goods_thumb'], true), 'price_list_count' => count(get_price_list($row['snatch_id'])), 'url' => build_uri('snatch', array('sid' => $row['snatch_id'])));
	}

	return $snatch_list;
}

function get_snatch_count($keywords = '')
{
	$where = '';

	if ($keywords) {
		$where = ' AND (ga.act_name LIKE \'%' . $keywords . '%\' OR g.goods_name LIKE \'%' . $keywords . '%\') ';
	}

	$now = gmtime();
	$sql = 'SELECT count(*) ' . ' FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON ga.goods_id = g.goods_id ' . (' WHERE ga.review_status = 3 AND start_time <= \'' . $now . '\' AND g.goods_id <> \'\' AND act_type=') . GAT_SNATCH . $where;
	return $GLOBALS['db']->getOne($sql);
}

function get_snatch($id)
{
	$sql = 'SELECT g.goods_id,g.cat_id, ga.act_ensure,g.goods_desc as goods_desc_old,  g.goods_sn, g.is_real, g.goods_name, g.extension_code, g.market_price, g.shop_price AS org_price, ' . 'g.goods_img, g.user_id, ga.product_id, ' . ('IFNULL(mp.user_price, g.shop_price * \'' . $_SESSION['discount'] . '\') AS shop_price, ') . 'g.promote_price, g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, ' . 'ga.act_name AS snatch_name, ga.start_time, ga.end_time, ga.ext_info, ga.act_desc AS `desc`, ga.act_promise,  ga.act_ensure ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'ON g.goods_id = ga.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE ga.act_id = \'' . $id . '\' AND g.goods_id <> \'\' AND ga.review_status = 3 AND g.is_delete = 0');
	$goods = $GLOBALS['db']->GetRow($sql);

	if ($goods) {
		$promote_price = bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
		$goods['formated_market_price'] = price_format($goods['market_price']);
		$goods['formated_shop_price'] = price_format($goods['shop_price']);
		$goods['formated_promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$goods['goods_thumb'] = get_image_path($goods['goods_id'], $goods['goods_thumb'], true);
		$goods['goods_img'] = get_image_path($goods['goods_id'], $goods['goods_img'], true);
		$goods['url'] = build_uri('goods', array('gid' => $goods['goods_id']), $goods['goods_name']);
		$goods['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $goods['start_time']);
		$info = unserialize($goods['ext_info']);

		if ($info) {
			foreach ($info as $key => $val) {
				$goods[$key] = $val;
			}

			$goods['is_end'] = $goods['end_time'] < gmtime();
			$goods['formated_start_price'] = price_format($goods['start_price']);
			$goods['formated_end_price'] = price_format($goods['end_price']);
			$goods['formated_max_price'] = price_format($goods['max_price']);
		}

		$goods['gmt_end_time'] = local_date('Y-m-d H:i:s', $goods['end_time']);
		$goods['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $goods['end_time']);
		$goods['snatch_time'] = sprintf($GLOBALS['_LANG']['snatch_start_time'], $goods['start_time'], $goods['end_time']);
		$goods['rz_shopName'] = get_shop_name($goods['user_id'], 1);
		$goods['shopinfo'] = get_shop_name($goods['user_id'], 2);
		$goods['shopinfo']['brand_thumb'] = str_replace(array('../'), '', $goods['shopinfo']['brand_thumb']);
		$build_uri = array('urid' => $goods['user_id'], 'append' => $goods['rz_shopName']);
		$domain_url = get_seller_domain_url($goods['user_id'], $build_uri);
		$goods['store_url'] = $domain_url['domain_name'];
		$basic_info = get_seller_shopinfo($goods['user_id']);
		$goods['province'] = $basic_info['province'];
		$goods['city'] = $basic_info['city'];
		$goods['kf_type'] = $basic_info['kf_type'];
		$goods['[kf_ww'] = $basic_info['kf_ww'];
		$goods['kf_qq'] = $basic_info['kf_qq'];

		if ($basic_info['kf_qq']) {
			$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
			$kf_qq = explode('|', $kf_qq[0]);

			if (!empty($kf_qq[1])) {
				$goods['kf_qq'] = $kf_qq[1];
			}
			else {
				$goods['kf_qq'] = '';
			}
		}
		else {
			$$goods['kf_qq'] = '';
		}

		if ($basic_info['kf_ww']) {
			$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
			$kf_ww = explode('|', $kf_ww[0]);

			if (!empty($kf_ww[1])) {
				$goods['[kf_ww'] = $kf_ww[1];
			}
			else {
				$goods['[kf_ww'] = '';
			}
		}
		else {
			$goods['[kf_ww'] = '';
		}

		$goods['shop_name'] = $basic_info['shop_name'];
		$goods['org_price_int'] = intval($goods['org_price']);
		return $goods;
	}
	else {
		return false;
	}
}

function get_last_snatch()
{
	$now = gmtime();
	$sql = 'SELECT act_id FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE  start_time < \'' . $now . '\' AND end_time > \'' . $now . '\' AND review_status = 3 AND act_type = ') . GAT_SNATCH . ' ORDER BY end_time ASC LIMIT 1';
	return $GLOBALS['db']->GetOne($sql);
}

function get_exchange_recommend_goods($type = '', $warehouse_id, $area_id)
{
	$leftJoin = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$now = gmtime();
	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, ' . 'g.goods_brief, g.goods_thumb, goods_img, b.brand_name, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'g.promote_start_date, g.promote_end_date,' . 'ga.act_name, ga.act_id, ga.ext_info, ga.start_time, ga.start_time, ga.end_time ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . $leftJoin . 'WHERE ga.act_type = \'' . GAT_SNATCH . '\' ' . ('AND ga.review_status = 3 AND ga.start_time <= \'' . $now . '\' AND ga.end_time >= \'' . $now . '\' AND ga.is_finished < 2 ');
	$num = 11;

	switch ($type) {
	case 'best':
		$sql .= ' AND ga.is_best = 1';
		break;

	case 'new':
		$sql .= ' AND ga.is_new = 1';
		break;

	case 'hot':
		$sql .= ' AND ga.is_hot = 1';
		break;
	}

	$sql .= $order_type == 0 ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY RAND()';
	$res = $GLOBALS['db']->selectLimit($sql, $num);
	$idx = 0;
	$snatch = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$snatch[$idx]['id'] = $row['goods_id'];
		$snatch[$idx]['name'] = $row['goods_name'];
		$snatch[$idx]['brief'] = $row['goods_brief'];
		$snatch[$idx]['brand_name'] = $row['brand_name'];
		$snatch[$idx]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$snatch[$idx]['exchange_integral'] = $row['exchange_integral'];
		$snatch[$idx]['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$snatch[$idx]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
		$snatch[$idx]['url'] = build_uri('snatch', array('sid' => $row['act_id']));
		$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		$snatch[$idx]['formated_shop_price'] = price_format($row['shop_price']);
		$snatch[$idx]['formated_shop_price'] = 0 < $promote_price ? price_format($promote_price) : $snatch[$idx]['formated_shop_price'];
		$snatch[$idx]['formated_market_price'] = price_format($row['market_price']);
		$ext_info = unserialize($row['ext_info']);
		$snatch_info = array_merge($row, $ext_info);
		$snatch[$idx]['auction'] = $snatch_info;
		$snatch[$idx]['status_no'] = auction_status($snatch_info);
		$snatch[$idx]['count'] = snatch_log($row['act_id']);
		$snatch[$idx]['price_list_count'] = count(get_price_list($row['act_id']));
		$snatch[$idx]['end_time_date'] = local_date('Y-m-d H:i:s', $row['end_time']);
		$snatch[$idx]['short_style_name'] = add_style($snatch[$idx]['short_name'], $row['goods_name_style']);
		$idx++;
	}

	return $snatch;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_area.php';
$warehouse_other = array('province_id' => $province_id, 'city_id' => $city_id);
$warehouse_area_info = get_warehouse_area_info($warehouse_other);
$region_id = $warehouse_area_info['region_id'];
$area_id = $warehouse_area_info['area_id'];
$area_city = $warehouse_area_info['city_id'];
if (isset($_COOKIE['region_id']) && !empty($_COOKIE['region_id'])) {
	$region_id = $_COOKIE['region_id'];
}

assign_ur_here();
$smarty->assign('now_time', gmtime());
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$template = 'snatch_list';
if (!isset($_REQUEST['act']) && !isset($_REQUEST['id'])) {
	if (defined('THEME_EXTENSION')) {
		$template = 'snatch_index';
	}

	$_REQUEST['act'] = 'list';
}
else {
	if (0 < $id && !isset($_REQUEST['act'])) {
		$_REQUEST['act'] = 'main';
	}
}

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('category_load_type', $_CFG['category_load_type']);
	$smarty->assign('query_string', preg_replace('/act=\\w+&?/', '', $_SERVER['QUERY_STRING']));
	$page = isset($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$size = isset($_CFG['page_size']) && 0 < intval($_CFG['page_size']) ? intval($_CFG['page_size']) : 10;
	$size = 15;
	$keywords = !empty($_REQUEST['keywords']) ? htmlspecialchars(trim($_REQUEST['keywords'])) : '';
	$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
	$default_sort_order_type = 'snatch_id';
	$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('snatch_id', 'end_time', 'start_time')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
	$order = isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')) ? trim($_REQUEST['order']) : $default_sort_order_method;
	assign_template();
	assign_dynamic('snatch');
	$position = assign_ur_here(1, $_LANG['snatch']);
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);

	if (defined('THEME_EXTENSION')) {
		$categories_pro = get_category_tree_leve_one();
		$smarty->assign('categories_pro', $categories_pro);
	}

	$smarty->assign('helps', get_shop_help());
	$smarty->assign('feed_url', $_CFG['rewrite'] == 1 ? 'feed-typesnatch.xml' : 'feed.php?type=snatch');
	$snatch_list = get_snatch_list($keywords, $size, $page, $sort, $order, $region_id, $area_id);
	$smarty->assign('snatch_list', $snatch_list);
	$count = get_snatch_count($keywords);

	if (!$_CFG['category_load_type']) {
		$pager = get_pager('snatch.php', array('act' => 'list', 'keywords' => $keywords, 'sort' => $sort, 'order' => $order), $count, $page, $size);
		$smarty->assign('pager', $pager);
	}

	if (defined('THEME_EXTENSION')) {
		for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
			$activity_top_banner .= '\'activity_top_ad_snatch' . $i . ',';
		}

		$smarty->assign('activity_top_banner', $activity_top_banner);
		$sql = ' SELECT SUM(og.goods_number) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON og.order_id = oi.order_id  ' . ' WHERE oi.extension_code = \'snatch\' AND oi.pay_status = 2 ';
		$snatch_goods_num = $GLOBALS['db']->getOne($sql);
		$smarty->assign('snatch_goods_num', $snatch_goods_num);
	}

	$smarty->assign('hot_goods', get_exchange_recommend_goods('hot', $region_id, $area_id));
	$smarty->display($template . '.dwt');
	exit();
}
else if ($_REQUEST['act'] == 'load_more_goods') {
	$page = isset($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$size = isset($_CFG['page_size']) && 0 < intval($_CFG['page_size']) ? intval($_CFG['page_size']) : 10;
	$size = 15;
	$keywords = !empty($_REQUEST['keywords']) ? htmlspecialchars(trim($_REQUEST['keywords'])) : '';
	$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
	$default_sort_order_type = 'snatch_id';
	$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('snatch_id', 'end_time', 'start_time')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
	$order = isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')) ? trim($_REQUEST['order']) : $default_sort_order_method;
	$snatch_list = get_snatch_list($keywords, $size, $page, $sort, $order, $region_id, $area_id);
	$smarty->assign('snatch_list', $snatch_list);
	$smarty->assign('type', 'snatch');
	$result = array('error' => 0, 'message' => '', 'cat_goods' => '', 'best_goods' => '');
	$result['cat_goods'] = html_entity_decode($smarty->fetch('library/more_goods_page.lbi'));
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'main') {
	$goods = get_snatch($id);

	if ($goods) {
		if (defined('THEME_EXTENSION')) {
			$position = assign_ur_here($goods['cat_id'], $goods['snatch_name'], array(), '', $goods['user_id']);
		}
		else {
			$position = assign_ur_here(0, $goods['snatch_name']);
		}

		$myprice = get_myprice($id);

		if ($goods['is_end']) {
			$smarty->assign('result', get_snatch_result($id));
		}

		$smarty->assign('id', $id);
		$smarty->assign('snatch_goods', $goods);
		$smarty->assign('goods', $goods);
		$smarty->assign('myprice', $myprice);
		if (isset($goods['product_id']) && 0 < $goods['product_id']) {
			$goods_specifications = get_specifications_list($goods['goods_id']);
			$good_products = get_good_products($goods['goods_id'], 'AND product_id = ' . $goods['product_id']);
			$_good_products = explode('|', $good_products[0]['goods_attr']);
			$products_info = '';

			foreach ($_good_products as $value) {
				$products_info .= ' ' . $goods_specifications[$value]['attr_name'] . '：' . $goods_specifications[$value]['attr_value'];
			}

			$smarty->assign('products_info', $products_info);
			unset($goods_specifications);
			unset($good_products);
			unset($_good_products);
			unset($products_info);
		}
	}
	else {
		show_message($_LANG['now_not_snatch']);
	}

	$vote = get_vote();

	if (!empty($vote)) {
		$smarty->assign('vote_id', $vote['id']);
		$smarty->assign('vote', $vote['content']);
	}

	assign_template();
	assign_dynamic('snatch');
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);

	if (defined('THEME_EXTENSION')) {
		$categories_pro = get_category_tree_leve_one();
		$smarty->assign('categories_pro', $categories_pro);
	}

	$smarty->assign('helps', get_shop_help());
	$smarty->assign('price_list', get_price_list($id));
	$smarty->assign('price_list_count', count(get_price_list($id)));
	$smarty->assign('feed_url', $_CFG['rewrite'] == 1 ? 'feed-typesnatch.xml' : 'feed.php?type=snatch');
	$smarty->assign('pictures', get_goods_gallery($goods['goods_id']));
	$mc_all = ments_count_all($goods['goods_id']);
	$mc_one = ments_count_rank_num($goods['goods_id'], 1);
	$mc_two = ments_count_rank_num($goods['goods_id'], 2);
	$mc_three = ments_count_rank_num($goods['goods_id'], 3);
	$mc_four = ments_count_rank_num($goods['goods_id'], 4);
	$mc_five = ments_count_rank_num($goods['goods_id'], 5);
	$comment_all = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);

	if (0 < $goods['user_id']) {
		$merchants_goods_comment = get_merchants_goods_comment($goods['user_id']);
	}

	$smarty->assign('comment_all', $comment_all);
	$smarty->assign('merch_cmt', $merchants_goods_comment);
	$basic_date = array('region_name');
	$basic_info['province'] = get_table_date('region', 'region_id = \'' . $goods['province'] . '\'', $basic_date, 2);
	$basic_info['city'] = get_table_date('region', 'region_id= \'' . $goods['city'] . '\'', $basic_date, 2) . '市';
	$basic_info['kf_type'] = $goods['kf_type'];
	$basic_info['shop_name'] = $goods['shop_name'];

	if ($goods['kf_qq']) {
		$kf_qq = array_filter(preg_split('/\\s+/', $goods['kf_qq']));
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

	if ($goods['kf_ww']) {
		$kf_ww = array_filter(preg_split('/\\s+/', $goods['kf_ww']));
		$kf_ww = explode('|', $kf_ww[0]);

		if (!empty($kf_ww[1])) {
			$basic_info['kf_ww'] = $kf_ww[1];
		}
		else {
			$basic_info['kf_ww'] = '';
		}

		$basic_info['kf_ww'] = $kf_ww[1];
	}
	else {
		$basic_info['kf_ww'] = '';
	}

	$shop_information = get_shop_name($goods['user_id']);

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

	$smarty->assign('shop_information', $shop_information);
	$smarty->assign('basic_info', $basic_info);
	$smarty->assign('hot_goods', get_exchange_recommend_goods('hot', $region_id, $area_id));
	$properties = get_goods_properties($goods['goods_id'], $region_id, $area_id, $area_city);
	$smarty->assign('cfg', $_CFG);
	$smarty->assign('properties', $properties['pro']);
	$smarty->assign('specification', $properties['spe']);
	$smarty->assign('goods_id', $goods['goods_id']);
	$smarty->assign('region_id', $region_id);
	$smarty->assign('area_id', $area_id);
	$smarty->display('snatch.dwt');
	exit();
}

if ($_REQUEST['act'] == 'new_price_list') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => 0, 'content' => '');
	$myprice = get_myprice($id);
	$smarty->assign('price_list', $myprice['bid_price']);
	$smarty->assign('price_list_count', count($myprice['bid_price']));
	$result['content'] = $smarty->fetch('library/snatch_price.lbi');
	$result['id'] = $id;
	exit($json->encode($result));
}

if ($_REQUEST['act'] == 'bid') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => 0, 'content' => '');
	$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
	$price = round($price, 2);
	$warehouse_id = isset($_POST['region_id']) ? intval($_POST['region_id']) : 0;
	$area_id = isset($_POST['area_id']) ? intval($_POST['area_id']) : 0;
	$goods_attr = isset($_REQUEST['goods_attr_id']) && !empty($_REQUEST['goods_attr_id']) ? dsc_addslashes($_REQUEST['goods_attr_id'], 0) : '';

	if (empty($_SESSION['user_id'])) {
		$result['error'] = 1;
		$result['prompt'] = 1;
		$result['content'] = $_LANG['not_login'];
		$result['back_url'] = 'snatch.php?id=' . $id;
		exit($json->encode($result));
	}

	$snatch_info = get_snatch($id);
	$smarty->assign('snatch_goods', $snatch_info);
	$goods = get_goods_info($snatch_info['goods_id'], $warehouse_id, $area_id);
	$prod = array();

	if ($goods_attr) {
		if ($goods['model_attr'] == 1) {
			$table_products = 'products_warehouse';
			$type_files = ' AND warehouse_id = \'' . $warehouse_id . '\'';
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
		$products = get_warehouse_id_attr_number($goods['goods_id'], $goods_attr, $goods['user_id'], $warehouse_id, $area_id);
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

	$url = build_uri('snatch', array('sid' => $id));

	if ($goods_number <= 0) {
		$result['error'] = 1;
		$result['content'] = $GLOBALS['_LANG']['buy_error'];
		exit($json->encode($result));
	}

	$sql = 'SELECT act_name AS snatch_name, end_time, ext_info FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE act_id =\'' . $id . '\' AND review_status = 3');
	$row = $db->getRow($sql, 'SILENT');

	if ($row) {
		$info = unserialize($row['ext_info']);

		if ($info) {
			foreach ($info as $key => $val) {
				$row[$key] = $val;
			}
		}
	}

	if (empty($row)) {
		$result['error'] = 1;
		$result['content'] = $db->error();
		exit($json->encode($result));
	}

	if ($row['end_time'] < gmtime()) {
		$result['error'] = 1;
		$result['content'] = $_LANG['snatch_is_end'];
		exit($json->encode($result));
	}

	if ($price < $row['start_price'] || $row['end_price'] < $price) {
		$result['error'] = 1;
		$result['content'] = sprintf($GLOBALS['_LANG']['not_in_range'], $row['start_price'], $row['end_price']);
		exit($json->encode($result));
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('snatch_log') . (' WHERE snatch_id = \'' . $id . '\' AND user_id = \'' . $_SESSION['user_id'] . '\' AND bid_price = \'' . $price . '\'');

	if (0 < $GLOBALS['db']->getOne($sql)) {
		$result['error'] = 1;
		$result['content'] = sprintf($GLOBALS['_LANG']['also_bid'], '￥' . $price);
		exit($json->encode($result));
	}

	$sql = 'SELECT pay_points FROM ' . $ecs->table('users') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\'';
	$pay_points = $db->getOne($sql);

	if ($pay_points < $row['cost_points']) {
		$result['error'] = 1;
		$result['content'] = $_LANG['lack_pay_points'];
		exit($json->encode($result));
	}

	log_account_change($_SESSION['user_id'], 0, 0, 0, 0 - $row['cost_points'], sprintf($_LANG['snatch_log'], $row['snatch_name']));
	$sql = 'INSERT INTO ' . $ecs->table('snatch_log') . '(snatch_id, user_id, bid_price, bid_time) VALUES' . ('(\'' . $id . '\', \'') . $_SESSION['user_id'] . '\', \'' . $price . '\', ' . gmtime() . ')';
	$db->query($sql);

	if ($snatch_info['is_end']) {
		$smarty->assign('result', get_snatch_result($id));
	}

	$smarty->assign('price_list', get_price_list($id));
	$smarty->assign('price_list_count', count(get_price_list($id)));
	$smarty->assign('myprice', get_myprice($id));
	$smarty->assign('id', $id);
	$result['content'] = $smarty->fetch('library/snatch.lbi');
	$result['content_price'] = $smarty->fetch('library/snatch_price.lbi');
	$result['id'] = $id;
	exit($json->encode($result));
}

if ($_REQUEST['act'] == 'buy') {
	$warehouse_id = isset($_POST['region_id']) ? intval($_POST['region_id']) : 0;
	$area_id = isset($_POST['area_id']) ? intval($_POST['area_id']) : 0;
	$goods_attr = isset($_POST['goods_attr_id']) && !empty($_POST['goods_attr_id']) ? dsc_addslashes($_POST['goods_attr_id'], 0) : '';

	if (empty($id)) {
		ecs_header("Location: ./\n");
		exit();
	}

	if (empty($_SESSION['user_id'])) {
		show_message($_LANG['not_login']);
	}

	$snatch = get_snatch($id);

	if (empty($snatch)) {
		ecs_header("Location: ./\n");
		exit();
	}

	$goods = get_goods_info($snatch['goods_id'], $warehouse_id, $area_id);
	$prod = array();
	$products = array();

	if ($goods_attr) {
		if ($goods['model_attr'] == 1) {
			$table_products = 'products_warehouse';
			$type_files = ' AND warehouse_id = \'' . $warehouse_id . '\'';
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
		$products = get_warehouse_id_attr_number($goods['goods_id'], $goods_attr, $goods['user_id'], $warehouse_id, $area_id);
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

	$url = build_uri('snatch', array('sid' => $id));

	if ($goods_number <= 0) {
		show_message($GLOBALS['_LANG']['buy_error'], $GLOBALS['_LANG']['go_back'], $url);
		exit();
	}

	if (empty($snatch['is_end'])) {
		$page = build_uri('snatch', array('sid' => $id));
		ecs_header('Location: ' . $page . "\n");
		exit();
	}

	$result = get_snatch_result($id);

	if ($_SESSION['user_id'] != $result['user_id']) {
		show_message($_LANG['not_for_you']);
	}

	if (0 < $result['order_count']) {
		show_message($_LANG['order_placed']);
	}

	if ($goods_attr) {
		$goods_attr_id = $goods_attr;
		$attr_list = array();
		$sql = 'SELECT a.attr_name, g.attr_value ' . 'FROM ' . $ecs->table('goods_attr') . ' AS g, ' . $ecs->table('attribute') . ' AS a ' . 'WHERE g.attr_id = a.attr_id ' . 'AND g.goods_attr_id ' . db_create_in($goods_attr_id) . ' ORDER BY a.sort_order, a.attr_id, g.goods_attr_id';
		$res = $db->query($sql);

		while ($row = $db->fetchRow($res)) {
			$attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
		}

		$goods_attr = join('', $attr_list);
	}
	else {
		$goods_attr = '';
		$goods_attr_id = '';
	}

	include_once ROOT_PATH . 'includes/lib_order.php';
	clear_cart(CART_SNATCH_GOODS);

	if (!empty($_SESSION['user_id'])) {
		$sess = '';
	}
	else {
		$sess = real_cart_mac_ip();
	}

	$cart = array('user_id' => $_SESSION['user_id'], 'session_id' => $sess, 'goods_id' => $snatch['goods_id'], 'product_id' => isset($products['product_id']) ? $products['product_id'] : 0, 'goods_sn' => addslashes($snatch['goods_sn']), 'goods_name' => addslashes($snatch['goods_name']), 'market_price' => $snatch['market_price'], 'goods_price' => $result['buy_price'], 'goods_number' => 1, 'goods_attr' => $goods_attr, 'goods_attr_id' => $goods_attr_id, 'warehouse_id' => $region_id, 'area_id' => $area_id, 'is_real' => $snatch['is_real'], 'ru_id' => $snatch['user_id'], 'extension_code' => addslashes($snatch['extension_code']), 'parent_id' => 0, 'rec_type' => CART_SNATCH_GOODS, 'is_gift' => 0);
	$db->autoExecute($ecs->table('cart'), $cart, 'INSERT');
	$_SESSION['flow_type'] = CART_SNATCH_GOODS;
	$_SESSION['extension_code'] = 'snatch';
	$_SESSION['extension_id'] = $id;
	$_SESSION['direct_shopping'] = 3;
	ecs_header("Location: ./flow.php?step=checkout&direct_shopping=3\n");
	exit();
}

?>
