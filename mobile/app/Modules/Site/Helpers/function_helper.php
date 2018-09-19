<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function goods_list($type, $page, $size, $warehouse_id = 0, $area_id = 0)
{
	$page = (1 < $page ? ($page - 1) * 10 : 0);
	$where .= ' g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ' . 'g.is_delete = 0 AND g.review_status>2 ';

	switch ($type) {
	case 'new':
		$where .= ' AND g.is_new = \'1\'';
		break;

	case 'best':
		$where .= ' AND g.is_best = \'1\'';
		break;

	case 'hot':
		$where .= ' AND g.is_hot = \'1\'';
		break;
	}

	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin = ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	$sql = 'SELECT g.goods_id, g.user_id, g.goods_name, ' . $shop_price . ' g.goods_name_style, g.comments_number,g.sales_volume,g.market_price, g.is_new, g.is_best, g.is_hot, ' . ' IF(g.model_price < 1, g.goods_number, IF(g.model_price < 2, wg.region_number, wag.region_number)) AS goods_number, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, g.model_price, ' . 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, g.goods_type, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img, g.cat_id ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . 'WHERE ' . $where . ' ORDER BY g.sort_order ASC , g.goods_id DESC LIMIT ' . $page . ' , ' . $size;
	$goods_list = $GLOBALS['db']->getAll($sql);
	$time = gmtime();

	foreach ($goods_list as $key => $val) {
		if (0 < $val['promote_price']) {
			$promote_price = bargain_price($val['promote_price'], $val['promote_start_date'], $val['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$price_other = array('market_price' => $val['market_price'], 'org_price' => $val['org_price'], 'shop_price' => $val['shop_price'], 'promote_price' => $promote_price);
		$price_info = get_goods_one_attr_price($val['goods_id'], $warehouse_id, $area_id, $price_other);
		$val = (!empty($val) ? array_merge($val, $price_info) : $val);
		$promote_price = $val['promote_price'];
		$goods_list[$key]['goods_img'] = get_image_path($val['goods_img']);
		$goods_list[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
		$goods_list[$key]['url'] = url('goods/index/index', array('id' => $val['goods_id'], 'u' => $_SESSION['user_id']));
		if (($val['promote_start_date'] < $time) && ($time < $val['promote_end_date']) && ($val['is_promote'] == 1) && ($val['model_price'] == 1)) {
			$goods_list[$key]['current_price'] = price_format($val['warehouse_promote_price']);
		}
		else {
			if (($val['promote_start_date'] < $time) && ($time < $val['promote_end_date']) && ($val['is_promote'] == 1) && ($val['model_price'] == 0)) {
				$goods_list[$key]['current_price'] = price_format($val['promote_price']);
			}
			else {
				$goods_list[$key]['current_price'] = price_format($val['shop_price']);
			}
		}

		if (empty($val['promote_start_date']) || empty($val['promote_end_date'])) {
			$goods_list[$key]['current_price'] = price_format($val['shop_price']);
		}

		$goods_list[$key]['link_url'] = url('category/index/search', array('id' => $val['cat_id']));
	}

	return $goods_list;
}

function limit_grab($warehouse_id = 0, $area_id = 0)
{
	$time = gmtime();
	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin = ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	$review_goods = ($GLOBALS['_CFG']['review_goods'] == 1 ? ' AND g.review_status > 2 ' : '');
	$sql = 'SELECT g.goods_id, g.user_id, g.goods_name, ' . $shop_price . ' g.goods_name_style, g.comments_number,g.sales_volume,g.market_price, g.is_new, g.is_best, g.is_hot, ' . ' IF(g.model_price < 1, g.goods_number, IF(g.model_price < 2, wg.region_number, wag.region_number)) AS goods_number, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, g.model_price, ' . 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, g.goods_type, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . 'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ' . 'g.is_delete = 0 AND g.is_promote = \'1\' ' . ' AND g.promote_start_date < ' . $time . ' AND g.promote_end_date > ' . $time . ' ' . $review_goods . ' GROUP BY g.goods_id ORDER BY g.sort_order ASC, g.goods_id DESC LIMIT 5';
	$goods_list = $GLOBALS['db']->getAll($sql);

	foreach ($goods_list as $key => $val) {
		if (0 < $val['promote_price']) {
			$promote_price = bargain_price($val['promote_price'], $val['promote_start_date'], $val['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$price_other = array('market_price' => $val['market_price'], 'org_price' => $val['org_price'], 'shop_price' => $val['shop_price'], 'promote_price' => $promote_price);
		$price_info = get_goods_one_attr_price($val['goods_id'], $warehouse_id, $area_id, $price_other);
		$val = (!empty($val) ? array_merge($val, $price_info) : $val);
		$promote_price = $val['promote_price'];
		$goods_list[$key]['goods_img'] = get_image_path($val['goods_img']);
		$goods_list[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
		$end_time['end_time'] = date('Y/m/d H:i:s', $val['promote_end_date']);
		$goods_list[$key]['shop_price'] = price_format($val['shop_price']);
		$goods_list[$key]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$goods_list[$key]['url'] = build_uri('goods', array('gid' => $val['goods_id']), $val['goods_name']);
		if (($val['promote_start_date'] <= $time) && ($time <= $val['promote_end_date']) && ($val['is_promote'] == 1)) {
			$goods_list[$key]['current_price'] = price_format(min($val['promote_price'], $val['shop_price']));
		}
		else {
			$goods_list[$key]['current_price'] = price_format($val['shop_price']);
		}

		if (($val['promote_start_date'] == 0) || ($val['promote_end_date'] == 0)) {
			$goods_list[$key]['current_price'] = price_format($val['shop_price']);
		}
	}

	return $goods_list;
}

function get_brand()
{
	$sql = 'SELECT brand_id,brand_name,brand_logo FROM {pre}brand WHERE is_show =1 LIMIT 20';
	$brand_list = $GLOBALS['db']->getAll($sql);

	foreach ($brand_list as $key => $val) {
		$brand_list[$key]['brand_logo'] = get_data_path($val['brand_logo'], 'brandlogo');
	}

	return $brand_list;
}

function get_store()
{
	$review_goods = ($GLOBALS['_CFG']['review_goods'] == 1 ? ' and review_status > 2 ' : '');
	$sql = 'SELECT msi.shop_id, msi.user_id, msi.shoprz_brandName, msi.shopNameSuffix,ssh.street_thumb,ssh.brand_thumb,ssh.logo_thumb FROM {pre}merchants_shop_information as msi' . '  LEFT JOIN {pre}seller_shopinfo AS ssh ON msi.user_id = ssh.ru_id WHERE msi.merchants_audit = 1 and msi.is_street = 1 ORDER BY msi.sort_order,msi.shop_id DESC';
	$store_list = $GLOBALS['db']->getAll($sql);

	foreach ($store_list as $key => $val) {
		$store_list[$key]['street_thumb'] = get_image_path($val['street_thumb']);
		$store_list[$key]['brand_thumb'] = get_image_path($val['brand_thumb']);
		$store_list[$key]['logo_thumb'] = get_image_path($val['logo_thumb']);
		$store_list[$key]['url'] = build_uri('store', array('stid' => $val['user_id']));
		$sql = 'SELECT goods_id,goods_name,goods_thumb,goods_img FROM {pre}goods WHERE user_id = ' . $val['user_id'] . ' AND ' . ' is_on_sale = 1 AND is_alone_sale = 1 AND ' . 'is_delete = 0 ' . $review_goods . ' ORDER BY sort_order,goods_id desc LIMIT 4';
		$goods_list = $GLOBALS['db']->getAll($sql);

		foreach ($goods_list as $k => $v) {
			$goods_list[$k]['goods_img'] = get_image_path($v['goods_img']);
			$goods_list[$k]['goods_thumb'] = get_image_path($v['goods_thumb']);

			if ($k == 3) {
				break;
			}
		}

		$count = count($goods_list);

		if (4 <= $count) {
			$store_list[$key]['goods_list'] = $goods_list;
		}
		else {
			unset($store_list[$key]);
		}
	}

	if (!empty($store_list)) {
		return array_slice($store_list, 0, 12);
	}
	else {
		return $store_list;
	}
}

function get_hot_click()
{
	$sql = 'SELECT goods_id,goods_name FROM {pre}goods WHERE is_on_sale = 1 AND is_alone_sale = 1 AND ' . 'is_delete = 0 ORDER BY click_count DESC LIMIT 6';
	$goods_list = $GLOBALS['db']->getAll($sql);

	foreach ($goods_list as $key => $val) {
		if (18 < strlen($val['goods_name'])) {
			$goods_list[$key]['goods_name'] = mb_strcut($val['goods_name'], 0, 18, 'utf-8') . '...';
		}
	}

	return $goods_list;
}

function count_number($type)
{
	$where .= ' g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ' . 'g.is_delete = 0 AND g.review_status>2 ';

	switch ($type) {
	case 'new':
		$where .= ' AND g.is_new = \'1\'';
		break;

	case 'best':
		$where .= ' AND g.is_best = \'1\'';
		break;

	case 'hot':
		$where .= ' AND g.is_hot = \'1\'';
		break;
	}

	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	$sql = 'SELECT g.goods_id, g.user_id, g.goods_name, ' . $shop_price . ' g.goods_name_style, g.comments_number,g.sales_volume,g.market_price, g.is_new, g.is_best, g.is_hot, ' . ' IF(g.model_price < 1, g.goods_number, IF(g.model_price < 2, wg.region_number, wag.region_number)) AS goods_number, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, g.model_price, ' . 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, g.goods_type, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . 'WHERE ' . $where . ' ORDER BY g.sort_order';
	$goods_list = $GLOBALS['db']->getAll($sql);
	return count($goods_list);
}


?>
