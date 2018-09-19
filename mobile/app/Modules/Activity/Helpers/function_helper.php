<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_activity_count()
{
	$sql = 'SELECT COUNT(*) as count FROM {pre}favourable_activity f LEFT JOIN {pre}touch_activity a on a.act_id = f.act_id WHERE f.review_status = 3 ';
	$res = $GLOBALS['db']->getRow($sql);
	$count = $res['count'] ? $res['count'] : 0;
	return $count;
}

function get_activity_info($size, $page)
{
	$start = ($page - 1) * $size;
	$sql = 'SELECT f.* , a.act_banner' . ' FROM {pre}favourable_activity AS f LEFT JOIN {pre}touch_activity AS a on a.act_id = f.act_id ' . ' WHERE f.review_status = 3 ' . ' ORDER BY f.sort_order ASC, f.end_time DESC';
	$res = $GLOBALS['db']->query($sql);
	$arr = array();

	foreach ($res as $row) {
		$arr[$row['act_id']]['start_time'] = local_date('Y-m-d H:i', $row['start_time']);
		$arr[$row['act_id']]['end_time'] = local_date('Y-m-d H:i', $row['end_time']);
		$arr[$row['act_id']]['url'] = url('activity/index/goods_list', array('id' => $row['act_id']));
		$arr[$row['act_id']]['act_name'] = $row['act_name'];
		$arr[$row['act_id']]['act_id'] = $row['act_id'];
		$arr[$row['act_id']]['act_banner'] = get_image_path($row['act_banner']);
	}

	return $arr;
}

function get_activity_goods($filter = array('goods_ids' => '', 'cat_ids' => '', 'brand_ids' => '', 'user_id' => 0), $warehouse_id, $area_id, $page = 1, $size = 4)
{
	$leftJoin = '';
	$where = 'g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ';

	if (!empty($filter['cat_ids'])) {
		$where .= ' AND g.cat_id ' . db_create_in($filter['cat_ids']);
	}

	if (isset($filter['brand_ids']) && !empty($filter['brand_ids'])) {
		$where .= ' AND g.brand_id ' . db_create_in($filter['brand_ids']);
		$leftJoin .= 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ' . 'ON b.brand_id = g.brand_id ';
	}

	if (isset($filter['goods_ids']) && !empty($filter['goods_ids'])) {
		$where .= ' AND g.goods_id ' . db_create_in($filter['goods_ids']);
	}

	if (isset($filter['user_id'])) {
		$where .= ' AND g.user_id = \'' . $filter['user_id'] . '\' ';
	}

	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	$sort = ' g.sort_order';
	$order = ' ASC';

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	$sql = 'SELECT g.goods_id, g.user_id, g.goods_name, ' . $shop_price . ' g.goods_name_style, g.comments_number,g.sales_volume,g.market_price, g.is_new, g.is_best, g.is_hot, ' . ' IF(g.model_price < 1, g.goods_number, IF(g.model_price < 2, wg.region_number, wag.region_number)) AS goods_number, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, g.model_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, g.goods_type, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE ' . $where . '  ORDER BY ' . $sort . ' ' . $order);
	$total_query = $GLOBALS['db']->query($sql);
	$total = is_array($total_query) ? count($total_query) : 0;
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	$arr = array();

	foreach ($res as $row) {
		$arr[$row['goods_id']]['org_price'] = $row['org_price'];
		$arr[$row['goods_id']]['model_price'] = $row['model_price'];
		$arr[$row['goods_id']]['warehouse_price'] = $row['warehouse_price'];
		$arr[$row['goods_id']]['warehouse_promote_price'] = $row['warehouse_promote_price'];
		$arr[$row['goods_id']]['region_price'] = $row['region_price'];
		$arr[$row['goods_id']]['region_promote_price'] = $row['region_promote_price'];

		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

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

		if ($display == 'grid') {
			$arr[$row['goods_id']]['goods_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		}
		else {
			$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		}

		$arr[$row['goods_id']]['name'] = $row['goods_name'];
		$arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
		$arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
		$arr[$row['goods_id']]['comments_number'] = $row['comments_number'];
		$arr[$row['goods_id']]['is_promote'] = $row['is_promote'];

		if (0 < $row['market_price']) {
			$discount_arr = get_discount($row);
		}

		$arr[$row['goods_id']]['zhekou'] = $discount_arr['discount'];
		$arr[$row['goods_id']]['jiesheng'] = $discount_arr['jiesheng'];
		$arr[$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
		$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' where id_value =\'' . $row['goods_id'] . '\' AND status = 1 AND parent_id = 0');
		$arr[$row['goods_id']]['review_count'] = $count;
		$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$arr[$row['goods_id']]['type'] = $row['goods_type'];
		$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_thumb']);
		$arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_img']);
		$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

		if ($row['model_attr'] == 1) {
			$table_products = 'products_warehouse';
			$type_files = ' and warehouse_id = \'' . $warehouse_id . '\'';
		}
		else if ($row['model_attr'] == 2) {
			$table_products = 'products_area';
			$type_files = ' and area_id = \'' . $area_id . '\'';
		}
		else {
			$table_products = 'products';
			$type_files = '';
		}

		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $row['goods_id'] . '\'' . $type_files . ' LIMIT 0, 1';
		$arr[$row['goods_id']]['prod'] = $GLOBALS['db']->getRow($sql);

		if (empty($prod)) {
			$arr[$row['goods_id']]['prod'] = 1;
		}
		else {
			$arr[$row['goods_id']]['prod'] = 0;
		}

		$arr[$row['goods_id']]['goods_number'] = $row['goods_number'];
		$sql = 'select * from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id=\'' . $row['user_id'] . '\'';
		$basic_info = $GLOBALS['db']->getRow($sql);
		$arr[$row['goods_id']]['kf_type'] = $basic_info['kf_type'];
		$arr[$row['goods_id']]['kf_ww'] = $basic_info['kf_ww'];
		$arr[$row['goods_id']]['kf_qq'] = $basic_info['kf_qq'];
		$arr[$row['goods_id']]['rz_shopName'] = get_shop_name($row['user_id'], 1);
		$arr[$row['goods_id']]['user_id'] = $row['user_id'];
		$arr[$row['goods_id']]['store_url'] = build_uri('merchants_store', array('urid' => $row['user_id']), $arr[$row['goods_id']]['rz_shopName']);
		$arr[$row['goods_id']]['count'] = selled_count($row['goods_id']);
		$mc_all = ments_count_all($row['goods_id']);
		$mc_one = ments_count_rank_num($row['goods_id'], 1);
		$mc_two = ments_count_rank_num($row['goods_id'], 2);
		$mc_three = ments_count_rank_num($row['goods_id'], 3);
		$mc_four = ments_count_rank_num($row['goods_id'], 4);
		$mc_five = ments_count_rank_num($row['goods_id'], 5);
		$arr[$row['goods_id']]['zconments'] = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);
	}

	return array('list' => array_values($arr), 'totalpage' => ceil($total / $size));
}


?>
