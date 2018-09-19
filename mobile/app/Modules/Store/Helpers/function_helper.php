<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_cat_store_list($cat_id)
{
	$sql = 'SELECT user_shopMain_category AS user_cat, user_id FROM {pre}merchants_shop_information  WHERE 1 AND user_shopMain_category <> \'\' AND merchants_audit = 1';
	$res = $GLOBALS['db']->query($sql);
	$user_id = '';
	$arr = array();

	foreach ($res as $key => $row) {
		$row['cat_str'] = '';
		$row['user_cat'] = explode('-', $row['user_cat']);

		foreach ($row['user_cat'] as $uck => $ucrow) {
			if ($ucrow) {
				$row['user_cat'][$uck] = explode(':', $ucrow);

				if (!empty($row['user_cat'][$uck][0])) {
					$row['cat_str'] .= $row['user_cat'][$uck][0] . ',';
				}
			}
		}

		if ($row['cat_str']) {
			$row['cat_str'] = substr($row['cat_str'], 0, -1);
			$row['cat_str'] = explode(',', $row['cat_str']);

			if (in_array($cat_id, $row['cat_str'])) {
				$user_id .= $row['user_id'] . ',';
			}
		}

		$arr[] = $row;
	}

	if ($user_id) {
		$user_id = substr($user_id, 0, -1);
	}

	return $user_id;
}

function store_get_goods($children, $brand, $min, $max, $ext, $size, $page, $sort, $order, $merchant_id, $warehouse_id = 0, $area_id = 0, $keyword, $type)
{
	if ($children == '') {
		$cat_where = ' AND g.user_id = \'' . $merchant_id . '\' ';
	}
	else {
		$cat_where = ' AND ' . $children . ' ';
	}

	$display = $GLOBALS['display'];
	$where = 'g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ' . ('g.is_delete = 0 ' . $cat_where);

	if (0 < $brand) {
		$where .= 'AND g.brand_id=' . $brand . ' ';
	}

	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if (0 < $min) {
		$where .= ' AND IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) >= ' . $min . ' ';
	}

	if (0 < $max) {
		$where .= ' AND IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) <= ' . $max . ' ';
	}

	$where .= ' AND g.user_id = \'' . $merchant_id . '\'';

	if (!empty($keyword)) {
		$where .= ' AND g.goods_name LIKE \'%' . mysql_like_quote($keyword) . '%\'';
	}

	if ($sort == 'last_update') {
		$sort = 'g.last_update';
	}

	if ($type) {
		$turetype .= $type . ' = 1 AND';
	}
	else {
		$turetype = '';
	}

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	if ($size == 0 && $page == 0) {
		$sql = 'SELECT count(*), ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, g.is_promote, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, g.goods_type, g.goods_number ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE ' . $turetype . ' ' . $where . ' ' . $ext . '  ORDER BY ' . $sort . ' ' . $order);
		$res = $GLOBALS['db']->getRow($sql);
		return $res['count(*)'];
	}
	else {
		$sql = 'SELECT g.goods_id, g.goods_name,g.model_attr, g.goods_number, ' . $shop_price . ' g.goods_name_style, g.comments_number,g.sales_volume,g.market_price, g.is_new, g.is_best, g.is_hot, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, g.is_promote, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, g.goods_type, ' . 'g.promote_start_date, g.promote_end_date, g.goods_brief,g.product_price,g.product_promote_price, g.goods_thumb , g.goods_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE ' . $turetype . ' ' . $where . ' ' . $ext . '  group by g.goods_id  ORDER BY ' . $sort . ' ' . $order);
		$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	}

	$arr = array();
	$idx = 0;

	foreach ($res as $row) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$price_info = get_goods_one_attr_price($row, $warehouse_id, $area_id, $promote_price);
		$row = !empty($row) ? array_merge($row, $price_info) : $row;
		$promote_price = $row['promote_price'];
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
			$arr[$idx]['watermark_img'] = $watermark_img;
		}

		$arr[$idx]['goods_id'] = $row['goods_id'];

		if ($display == 'grid') {
			$arr[$idx]['goods_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		}
		else {
			$arr[$idx]['goods_name'] = $row['goods_name'];
		}

		$arr[$idx]['name'] = $row['goods_name'];
		$arr[$idx]['goods_brief'] = $row['goods_brief'];
		$arr[$idx]['sales_volume'] = $row['sales_volume'];
		$arr[$idx]['comments_number'] = $row['comments_number'];

		if (0 < $row['market_price']) {
			$discount_arr = get_discount($row);
		}

		$arr[$idx]['zhekou'] = $discount_arr['discount'];
		$arr[$idx]['jiesheng'] = $discount_arr['jiesheng'];
		$arr[$idx]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
		$goods_id = $row['goods_id'];
		$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' where comment_type=0 and id_value =\'' . $goods_id . '\''));
		$arr[$idx]['review_count'] = $count;
		$arr[$idx]['market_price'] = price_format($row['market_price']);
		$arr[$idx]['shop_price'] = price_format($row['shop_price']);
		$arr[$idx]['type'] = $row['goods_type'];
		$arr[$idx]['is_promote'] = $row['is_promote'];
		$arr[$idx]['goods_number'] = $row['goods_number'];
		$arr[$idx]['promote_price'] = price_format($promote_price);
		$arr[$idx]['goods_thumb'] = get_image_path($row['goods_thumb']);
		$arr[$idx]['goods_img'] = get_image_path($row['goods_thumb']);
		$arr[$idx]['goods_url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$arr[$idx]['count'] = selled_count($row['goods_id']);
		$arr[$idx]['pictures'] = get_goods_gallery($row['goods_id']);
		$attr = get_goods_properties($row['goods_id'], $warehouse_id, $area_id);
		$arr[$idx]['spe'] = $attr['spe'];
		$idx++;
	}

	return $arr;
}


?>
