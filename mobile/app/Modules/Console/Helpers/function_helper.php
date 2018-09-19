<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function replace_img_view($data)
{
	$data = strstr($data, 'data');
	return $data;
}

function category_get_goods($children, $intro = '', $brand, $user_id, $sort = '', $warehouse_id = 0, $area_id = 0, $limit = 10)
{
	$display = isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : 'grid';
	$where = 'g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND ssi.shop_close = 1';
	$get_extension_goods = get_extension_goods($children);
	if (!empty($get_extension_goods) && !empty($children)) {
		$where .= ' AND (' . $children . ' OR ' . $get_extension_goods . ')';
	}

	if ($user_id) {
		$where .= ' AND g.user_id = ' . $user_id;
	}

	if ($intro) {
		switch ($intro) {
		case 'best':
			$where .= 0 < $user_id ? ' AND  g.store_best = \'1\' ' : (0 < $user_id ? ' AND 1 ' : ' AND is_best = \'1\' ');
			break;

		case 'new':
			$where .= 0 < $user_id ? ' AND  g.store_new = \'1\' ' : ' AND  g.is_new = \'1\' ';
			break;

		case 'hot':
			$where .= 0 < $user_id ? ' AND  g.store_hot = \'1\' ' : ' AND  g.is_hot = \'1\' ';
			break;

		case 'promotion':
			$time = gmtime();
			$where .= ' AND g.promote_price > 0 AND g.promote_start_date <= \'' . $time . '\' AND g.promote_end_date >= \'' . $time . '\' ';
			break;

		default:
			$where .= '';
		}
	}

	$leftJoin = '';

	if ($brand) {
		$where .= ' AND g.brand_id IN(' . $brand . ')';
	}

	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi on msi.user_id = g.user_id ';
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' as ssi on ssi.ru_id = g.user_id ';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' AND lag.region_id = \'' . $area_id . '\' ';
	}

	if ($sort == '') {
		$sort = 'g.last_update';
	}

	if ($sort == 'goods_id') {
		$sort = 'IF(goods_sort > 0, goods_sort + g.sort_order, g.sort_order) ' . $order . ', g.goods_id ';
	}

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	$discount = isset($_SESSION['user_id']) && 0 < $_SESSION['user_id'] ? $_SESSION['discount'] : 1;
	$user_rank = isset($_SESSION['user_id']) && 0 < $_SESSION['user_id'] ? $_SESSION['user_rank'] : 0;
	$sql = 'SELECT g.goods_id, g.user_id, g.goods_name, ' . $shop_price . ' g.goods_name_style, g.comments_number,g.sales_volume,g.market_price, g.is_new, g.is_best, g.is_hot,g.model_attr,' . ' IF(g.model_price < 1, g.goods_number, IF(g.model_price < 2, wg.region_number, wag.region_number)) AS goods_number, ' . '(SELECT ' . 'IF((iw.goods_number + iw.user_number + iw.goods_comment_number + iw.merchants_comment_number + iw.user_attention_number) > iw.return_number, (iw.goods_number + iw.user_number + iw.goods_comment_number + iw.merchants_comment_number + iw.user_attention_number - iw.return_number), 0) ' . ' AS goods_sort FROM ' . $GLOBALS['ecs']->table('intelligent_weight') . ' AS iw WHERE iw.goods_id = g.goods_id LIMIT 1) AS goods_sort, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, g.model_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $discount . '\'), g.shop_price * \'' . $discount . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, g.goods_type, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img, msi.self_run , g.product_price, g.product_promote_price  ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id  AND mp.user_rank = \'' . $user_rank . '\' ') . ('WHERE ' . $where . '  ORDER BY ' . $sort . ' LIMIT ' . $limit);
	$res = $GLOBALS['db']->query($sql);
	$arr = array();

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
		$arr[$row['goods_id']]['org_price'] = $row['org_price'];
		$arr[$row['goods_id']]['model_price'] = $row['model_price'];
		$arr[$row['goods_id']]['warehouse_price'] = $row['warehouse_price'];
		$arr[$row['goods_id']]['warehouse_promote_price'] = $row['warehouse_promote_price'];
		$arr[$row['goods_id']]['region_price'] = $row['region_price'];
		$arr[$row['goods_id']]['region_promote_price'] = $row['region_promote_price'];
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

		$arr[$row['goods_id']]['title'] = $row['goods_name'];
		$arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
		$arr[$row['goods_id']]['sale'] = $row['sales_volume'];
		$arr[$row['goods_id']]['comments_number'] = $row['comments_number'];
		$arr[$row['goods_id']]['is_promote'] = $row['is_promote'];
		$arr[$row['goods_id']]['promote_start_date'] = $row['promote_start_date'];
		$arr[$row['goods_id']]['promote_end_date'] = $row['promote_end_date'];

		if (0 < $row['market_price']) {
			$discount_arr = get_discount($row);
		}

		$arr[$row['goods_id']]['zhekou'] = $discount_arr['discount'];
		$arr[$row['goods_id']]['jiesheng'] = $discount_arr['jiesheng'];
		$arr[$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
		$goods_id = $row['goods_id'];
		$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' where id_value =\'' . $goods_id . '\' AND status = 1 AND parent_id = 0'));
		$arr[$row['goods_id']]['review_count'] = $count;
		$arr[$row['goods_id']]['marketPrice'] = price_format($row['market_price']);
		$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$arr[$row['goods_id']]['type'] = $row['goods_type'];
		$arr[$row['goods_id']]['price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$arr[$row['goods_id']]['img'] = get_image_path($row['goods_thumb']);
		$arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_img']);
		$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$arr[$row['goods_id']]['is_hot'] = $row['is_hot'];
		$arr[$row['goods_id']]['is_best'] = $row['is_best'];
		$arr[$row['goods_id']]['is_new'] = $row['is_new'];
		$arr[$row['goods_id']]['self_run'] = $row['self_run'];
		$arr[$row['goods_id']]['is_shipping'] = $row['is_shipping'];

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

		$arr[$row['goods_id']]['stock'] = $row['goods_number'];
		$sql = 'select * from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id=\'' . $row['user_id'] . '\'';
		$basic_info = $GLOBALS['db']->getRow($sql);
		$arr[$row['goods_id']]['user_id'] = $row['user_id'];
		$arr[$row['goods_id']]['store_url'] = build_uri('merchants_store', array('urid' => $row['user_id']), $arr[$row['goods_id']]['rz_shopName']);
		$arr[$row['goods_id']]['count'] = selled_count($row['goods_id']);
	}

	return array_values($arr);
}

function get_thumb_tree($tree_id = 0)
{
	$three_arr = array();
	$where = '';
	$child_sql = 'SELECT album_id, album_name ' . ' FROM ' . $GLOBALS['ecs']->table('gallery_album') . (' WHERE parent_id = \'' . $tree_id . '\' AND is_show = 1 ') . $where . ' ORDER BY sort_order ASC';
	$res = $GLOBALS['db']->getAll($child_sql);

	foreach ($res as $k => $row) {
		if ($row['is_show']) {
			$three_arr[$k]['album_id'] = $row['album_id'];
			$three_arr[$k]['album_name'] = $row['album_name'];
			$three_arr[$k]['haschild'] = 0;
		}

		if (isset($row['album_id'])) {
			$child_tree = get_thumb_tree($row['album_id']);

			if ($child_tree) {
				$three_arr[$k]['tree'] = $child_tree;
				$three_arr[$k]['haschild'] = 1;
			}
		}
	}

	return $three_arr;
}

function get_article_tree($tree_id = 0)
{
	$three_arr = array();
	$child_sql = 'SELECT cat_id, cat_name ' . ' FROM ' . $GLOBALS['ecs']->table('article_cat') . (' WHERE parent_id = \'' . $tree_id . '\' ORDER BY sort_order ASC');
	$res = $GLOBALS['db']->getAll($child_sql);

	foreach ($res as $k => $row) {
		$three_arr[$k]['cat_id'] = $row['cat_id'];
		$three_arr[$k]['cat_name'] = $row['cat_name'];
		$three_arr[$k]['haschild'] = 0;

		if (isset($row['cat_name'])) {
			$child_tree = get_article_tree($row['cat_id']);

			if ($child_tree) {
				$three_arr[$k]['tree'] = $child_tree;
				$three_arr[$k]['haschild'] = 1;
			}
		}
	}

	return $three_arr;
}

function article_tree($tree_id = 0)
{
	$three_arr = array();
	$res = dao('article_cat')->field('cat_id')->where(array('parent_id' => $tree_id))->order('sort_order ASC')->select();

	foreach ($res as $k => $row) {
		$three_arr[$k]['cat_id'] = $row['cat_id'];

		if (isset($row['cat_id'])) {
			$child_tree = article_tree($row['cat_id']);

			foreach ($child_tree as $key => $value) {
				array_unshift($three_arr, $value['cat_id']);
			}
		}
	}

	return $three_arr;
}


?>
