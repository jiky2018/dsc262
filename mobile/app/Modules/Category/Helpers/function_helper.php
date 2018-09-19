<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_cat_info($cat_id)
{
	return $GLOBALS['db']->getRow('SELECT cat_name, keywords, cat_desc, style, grade, filter_attr, parent_id, cat_icon FROM {pre}category as g WHERE cat_id = ' . $cat_id);
}

function get_cagtegory_goods($keywords, $cat_id, $sort = 'g.last_update', $order = 'DESC', $limit = '10')
{
	if ($cat_id == 0) {
		$where = '';
	}
	else {
		$where = ' AND ' . get_children($cat_id);
	}

	if ($keywords) {
		$where .= ' AND ( 1 ' . $keywords . ' ) ';
	}

	$sql = 'SELECT g.goods_id, g.cat_id, g.goods_name,g.goods_img,g.shop_price FROM {pre}goods AS g WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.review_status > 2 ' . $where . ' ORDER BY ' . $sort . ' ' . $order . (' LIMIT ' . $limit);
	$goods_list = $GLOBALS['db']->query($sql);

	foreach ($goods_list as $key => $val) {
		$is_show = catshow($val['cat_id']);

		if ($is_show == 1) {
			$goods_list[$key]['goods_img'] = get_image_path($val['goods_img']);
			$sql = 'SELECT (AVG(comment_rank) * 20) AS rank,COUNT(*) AS count FROM {pre}comment WHERE comment_type = 0 AND id_value = ' . $val['goods_id'];
			$rank = $GLOBALS['db']->getRow($sql);
			$goods_list[$key]['rank'] = substr($rank['rank'], 0, 2) ? substr($rank['rank'], 0, 2) : 0;
			$goods_list[$key]['count'] = $rank['count'];
		}
	}

	return $goods_list;
}

function catshow($cat_id)
{
	$is_show = dao('category')->field('is_show, parent_id')->where(array('cat_id' => $cat_id))->find();
	if ($is_show['parent_id'] == 0 || $is_show['is_show'] == 0) {
		return $is_show['is_show'];
	}
	else {
		return catshow($is_show['parent_id']);
	}

	return $is_show['is_show'];
}

function get_parent_grade($cat_id)
{
	static $res;

	if ($res === NULL) {
		$data = read_static_cache('cat_parent_grade');

		if ($data === false) {
			$sql = 'SELECT parent_id, cat_id, grade ' . ' FROM ' . $GLOBALS['ecs']->table('category');
			$res = $GLOBALS['db']->getAll($sql);
			write_static_cache('cat_parent_grade', $res);
		}
		else {
			$res = $data;
		}
	}

	if (!$res) {
		return 0;
	}

	$parent_arr = array();
	$grade_arr = array();

	foreach ($res as $val) {
		$parent_arr[$val['cat_id']] = $val['parent_id'];
		$grade_arr[$val['cat_id']] = $val['grade'];
	}

	while (0 < $parent_arr[$cat_id] && $grade_arr[$cat_id] == 0) {
		$cat_id = $parent_arr[$cat_id];
	}

	return $grade_arr[$cat_id];
}

function category_get_goods($keywords = '', $children, $intro = '', $brand, $min, $max, $ext, $size, $page, $sort, $order, $warehouse_id = 0, $area_id = 0, $ubrand = 0, $hasgoods = 0, $promotion = 0, $cou_id = 0)
{
	$display = isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : 'grid';
	$where = 'g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ';

	if ($keywords) {
		$where .= ' AND (( 1 ' . $keywords . ' ) ) ';
	}
	else {
		$get_extension_goods = get_extension_goods($children);
		if (!empty($get_extension_goods) && !empty($children)) {
			$where .= ' AND (' . $children . ' OR ' . $get_extension_goods . ')';
		}
	}

	if ($hasgoods) {
		$where .= ' AND g.goods_number > 0';
	}

	if ($promotion) {
		$intro = 'promotion';
	}

	if ($intro) {
		switch ($intro) {
		case 'best':
			$where .= ' AND g.is_best = 1 ';
			break;

		case 'new':
			$where .= ' AND g.is_new = 1 ';
			break;

		case 'hot':
			$where .= ' AND g.is_hot = 1 ';
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
		$where .= ' AND g.brand_id ' . db_create_in($brand);
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

	if (0 < $min) {
		$where .= ' AND IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) >= ' . $min . ' ';
	}

	if (0 < $max) {
		$where .= ' AND IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) <= ' . $max . ' ';
	}

	if ($sort == 'last_update') {
		$sort = 'g.last_update';
	}

	if ($sort == 'goods_id') {
		$sort = 'IF(goods_sort > 0, goods_sort + g.sort_order, g.sort_order) ' . $order . ', g.goods_id ';
	}

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	if (0 < $cou_id) {
		$cou_data = dao('coupons')->field('cou_type, ru_id, cou_goods, spec_cat, cou_ok_goods, cou_ok_cat')->where(array('cou_id' => $cou_id))->find();
		$where .= get_con_where($cou_data['ru_id'], $cou_data['cou_goods'], $cou_data['spec_cat']);
	}

	$sql = 'SELECT g.goods_id, g.cat_id, g.cloud_id, g.user_id, g.goods_name, ' . $shop_price . ' g.goods_name_style, g.comments_number,g.sales_volume,g.market_price, g.is_new, g.is_best, g.is_hot,g.model_attr,' . ' IF(g.model_price < 1, g.goods_number, IF(g.model_price < 2, wg.region_number, wag.region_number)) AS goods_number, ' . '(SELECT ' . 'IF((iw.goods_number + iw.user_number + iw.goods_comment_number + iw.merchants_comment_number + iw.user_attention_number) > iw.return_number, (iw.goods_number + iw.user_number + iw.goods_comment_number + iw.merchants_comment_number + iw.user_attention_number - iw.return_number), 0) ' . ' AS goods_sort FROM ' . $GLOBALS['ecs']->table('intelligent_weight') . ' AS iw WHERE iw.goods_id = g.goods_id LIMIT 1) AS goods_sort, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, g.model_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, g.goods_type, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img, msi.self_run, g.product_price, g.product_promote_price  ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE ' . $where . ' ' . $ext . ' GROUP BY g.goods_id ORDER BY ' . $sort . ' ' . $order);
	$total_query = $GLOBALS['db']->query($sql);
	$total = is_array($total_query) ? count($total_query) : 0;
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	$arr = array();

	foreach ($res as $row) {
		$is_show = catshow($row['cat_id']);
		if ($is_show == 1 || 0 < $row['cloud_id']) {
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

			$arr[$row['goods_id']]['name'] = $row['goods_name'];
			$arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
			$arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
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
			$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
			$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
			$arr[$row['goods_id']]['type'] = $row['goods_type'];
			$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
			$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_thumb']);
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
	}

	return array('list' => array_values($arr), 'totalpage' => ceil($total / $size));
}

function add_highlight($goods_name, $keyword = '')
{
	if (!empty($keyword)) {
		$string = '<font style=color:red; font-size:inherit;><b>' . $keyword . '</b></font>';
		$goods_name = str_replace($keyword, $string, $goods_name);
	}

	return $goods_name;
}

function get_con_where($ru_id, $cou_goods, $spec_cat)
{
	$cou_where = ' AND g.user_id = \'' . $ru_id . '\' ';

	if ($cou_goods) {
		$cou_where .= ' AND g.goods_id IN (' . $cou_goods . ') ';
	}
	else if ($spec_cat) {
		$cou_children = get_cou_children($spec_cat);

		if ($cou_children) {
			$cou_where .= ' AND g.cat_id IN (' . $cou_children . ') ';
		}
	}

	return $cou_where;
}


?>
