<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_xiaoliang($goods_id = 0)
{
	$sql = 'SELECT sum(goods_number) FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE goods_id =' . $goods_id;
	$xl = $GLOBALS['db']->getOne($sql);

	if (empty($xl)) {
		$xl = 0;
	}

	return $xl;
}

function goods_sort($goods_a, $goods_b)
{
	if ($goods_a['sort_order'] == $goods_b['sort_order']) {
		return 0;
	}

	return $goods_a['sort_order'] < $goods_b['sort_order'] ? -1 : 1;
}

function get_categories_tree($cat_id = 0)
{
	if (0 < $cat_id) {
		$sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id = \'' . $cat_id . '\'');
		$parent_id = $GLOBALS['db']->getOne($sql);
	}
	else {
		$parent_id = 0;
	}

	$sql = 'SELECT cat_id FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE parent_id = \'' . $parent_id . '\' AND is_show = 1 LIMIT 1');
	if ($GLOBALS['db']->getOne($sql) || $parent_id == 0) {
		$sql = 'SELECT cat_id,cat_name ,parent_id,is_show, category_links ' . 'FROM ' . $GLOBALS['ecs']->table('category') . ('WHERE parent_id = \'' . $parent_id . '\' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC');
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $row) {
			if ($row['is_show']) {
				$cat_arr[$row['cat_id']]['id'] = $row['cat_id'];
				$cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
				$cat_arr[$row['cat_id']]['url'] = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);

				if (isset($row['cat_id']) != NULL) {
					$cat_arr[$row['cat_id']]['cat_id'] = get_child_tree($row['cat_id']);
				}
			}
		}
	}

	if (isset($cat_arr)) {
		return $cat_arr;
	}

	return false;
}

function get_child_tree($tree_id = 0, $top = 0)
{
	$three_arr = array();
	$where = '';
	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE parent_id = \'' . $tree_id . '\' AND is_show = 1') . $where;
	if ($GLOBALS['db']->getOne($sql) || $tree_id == 0) {
		$child_sql = 'SELECT c.cat_id, c.cat_name, c.touch_icon,c.parent_id, c.cat_alias_name, c.is_show, (SELECT goods_thumb FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE cat_id = c.cat_id AND is_on_sale = 1 AND is_delete = 0 ORDER BY sort_order ASC, goods_id DESC limit 1 ) as goods_thumb ' . ' FROM ' . $GLOBALS['ecs']->table('category') . ' c' . (' WHERE c.parent_id = \'' . $tree_id . '\' AND c.is_show = 1 ') . $where . ' ORDER BY c.sort_order ASC, c.cat_id ASC';
		$res = $GLOBALS['db']->getAll($child_sql);

		foreach ($res as $k => $row) {
			if ($row['is_show']) {
				$three_arr[$k]['id'] = $row['cat_id'];
				$three_arr[$k]['name'] = $row['cat_alias_name'] ? $row['cat_alias_name'] : $row['cat_name'];
				$three_arr[$k]['url'] = url('category/index/products', array('id' => $row['cat_id']));
				$three_arr[$k]['cat_img'] = !empty($row['touch_icon']) ? get_image_path($row['touch_icon']) : get_image_path($row['goods_thumb']);
				$three_arr[$k]['haschild'] = 0;
			}

			if (isset($row['cat_id'])) {
				$child_tree = get_child_tree($row['cat_id']);

				if ($child_tree) {
					$three_arr[$k]['cat_id'] = $child_tree;
					$three_arr[$k]['haschild'] = 1;
				}
			}
		}
	}

	return $three_arr;
}

function get_child_tree_new($tree_id = 0, $top = 0)
{
	$url = dao('category')->field('cat_id , cat_icon, cat_name, cat_alias_name , parent_id')->where(array('parent_id' => $tree_id, 'is_show' => 1))->ORDER('sort_order')->select();

	foreach ($url as $key => $value) {
		$category = dao('category')->field('cat_id ,cat_name, cat_alias_name , parent_id')->where(array('parent_id' => $value['cat_id'], 'is_show' => 1))->select();

		foreach ($category as $k => $val) {
			$category[$k] = array('cat_id' => $val['cat_id'], 'cat_name' => $val['cat_name'], 'cat_alias_name' => $val['cat_alias_name'], 'url' => url('category/index/products', array('id' => $val['cat_id'])), 'parent_id' => $val['parent_id']);
		}

		$url[$key] = array('cat_id' => $value['cat_id'], 'cat_name' => $value['cat_name'], 'cat_alias_name' => $value['cat_alias_name'], 'url' => url('category/index/products', array('id' => $value['cat_id'])), 'parent_id' => $value['parent_id'], 'child_tree' => $category);
	}

	return $url;
}

function get_top10($cats = '', $presale)
{
	$cats = get_category_parentChild_tree1($cats, 1);
	$cats = arr_foreach($cats);

	if ($cats) {
		$cats = implode(',', $cats) . ',' . $cats;
		$cats = get_children($cats, 0, 1);
	}
	else {
		$cats = 'g.cat_id IN (' . $cats . ')';
	}

	$where = !empty($cats) ? 'AND (' . $cats . ' OR ' . get_extension_goods($cats) . ') ' : '';

	if ($presale == 'presale') {
		$where .= ' AND ( SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('presale_activity') . 'AS pa WHERE pa.goods_id = g.goods_id) > 0 AND pa.review_status = 3 ';
	}

	switch ($GLOBALS['_CFG']['top10_time']) {
	case 1:
		$top10_time = 'AND o.order_sn >= \'' . date('Ymd', gmtime() - 365 * 86400) . '\'';
		break;

	case 2:
		$top10_time = 'AND o.order_sn >= \'' . date('Ymd', gmtime() - 180 * 86400) . '\'';
		break;

	case 3:
		$top10_time = 'AND o.order_sn >= \'' . date('Ymd', gmtime() - 90 * 86400) . '\'';
		break;

	case 4:
		$top10_time = 'AND o.order_sn >= \'' . date('Ymd', gmtime() - 30 * 86400) . '\'';
		break;

	default:
		$top10_time = '';
	}

	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, SUM(og.goods_number) as goods_number,g.comments_number, g.market_price, g.shop_price , g.promote_price, g.market_price ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g, ' . $GLOBALS['ecs']->table('order_info') . ' AS o, ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ('WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . $where . ' ' . $top10_time . ' ');

	if ($GLOBALS['_CFG']['use_storage'] == 1) {
		$sql .= ' AND g.goods_number > 0 ';
	}

	$sql .= ' AND og.order_id = o.order_id AND og.goods_id = g.goods_id ' . 'AND (o.order_status = \'' . OS_CONFIRMED . '\' OR o.order_status = \'' . OS_SPLITED . '\') ' . 'AND (o.pay_status = \'' . PS_PAYED . '\' OR o.pay_status = \'' . PS_PAYING . '\') ' . 'AND (o.shipping_status = \'' . SS_SHIPPED . '\' OR o.shipping_status = \'' . SS_RECEIVED . '\') ' . 'GROUP BY g.goods_id ORDER BY goods_number DESC, g.goods_id DESC LIMIT ' . $GLOBALS['_CFG']['top_number'];
	$arr = $GLOBALS['db']->getAll($sql);
	$i = 0;

	for ($count = count($arr); $i < $count; $i++) {
		$arr[$i]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($arr[$i]['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $arr[$i]['goods_name'];
		$arr[$i]['url'] = build_uri('goods', array('gid' => $arr[$i]['goods_id']), $arr[$i]['goods_name']);
		$arr[$i]['thumb'] = get_image_path($arr[$i]['goods_thumb']);
		$arr[$i]['price'] = price_format($arr[$i]['shop_price']);

		if (0 < $arr[$i]['market_price']) {
			$discount_arr = get_discount($arr[$i]);
		}

		$arr[$i]['zhekou'] = $discount_arr['discount'];
		$arr[$i]['jiesheng'] = $discount_arr['jiesheng'];
	}

	return $arr;
}

function get_recommend_goods($type = '', $cats = '', $warehouse_id = 0, $area_id = 0, $ru_id = 0, $rec_type = 0, $presale = '')
{
	if (!in_array($type, array('best', 'new', 'hot'))) {
		return array();
	}

	$leftJoin = '';
	$tag_where = '';

	if ($presale == 'presale') {
		$tag_where .= ' AND ( SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('presale_activity') . 'AS pa WHERE pa.goods_id = g.goods_id) > 0 ';
	}

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$tag_where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	if (0 < $ru_id) {
		$tag_where .= ' and g.user_id = \'' . $ru_id . '\' ';
		$goods_hot_new_best = 'g.store_hot = 1 OR g.store_new = 1 OR g.store_best = 1';
		$goods_hnb_files = 'g.store_new as is_new, g.store_hot as is_hot, g.store_best as is_best,';
	}
	else {
		$goods_hot_new_best = 'g.is_best = 1 OR g.is_new =1 OR g.is_hot = 1';
		$goods_hnb_files = 'g.is_best, g.is_new, g.is_hot,';
	}

	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$tag_where .= ' AND g.review_status > 2 ';
	}

	static $type_goods = array();

	if (empty($type_goods[$type])) {
		$type_goods['best'] = array();
		$type_goods['new'] = array();
		$type_goods['hot'] = array();
		$data = read_static_cache('recommend_goods');

		if ($data === false) {
			$sql = 'SELECT g.goods_id, ' . $goods_hnb_files . ' g.is_promote, b.brand_name,g.sort_order ' . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' . $leftJoin . ' WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND (' . $goods_hot_new_best . ')' . $tag_where . ' ORDER BY g.sort_order, g.last_update DESC';
			$goods_res = $GLOBALS['db']->getAll($sql);
			$goods_data['best'] = array();
			$goods_data['new'] = array();
			$goods_data['hot'] = array();
			$goods_data['brand'] = array();

			if (!empty($goods_res)) {
				foreach ($goods_res as $data) {
					if ($data['is_best'] == 1) {
						$goods_data['best'][] = array('goods_id' => $data['goods_id'], 'sort_order' => $data['sort_order']);
					}

					if ($data['is_new'] == 1) {
						$goods_data['new'][] = array('goods_id' => $data['goods_id'], 'sort_order' => $data['sort_order']);
					}

					if ($data['is_hot'] == 1) {
						$goods_data['hot'][] = array('goods_id' => $data['goods_id'], 'sort_order' => $data['sort_order']);
					}

					if ($data['brand_name'] != '') {
						$goods_data['brand'][$data['goods_id']] = $data['brand_name'];
					}
				}
			}

			write_static_cache('recommend_goods', $goods_data);
		}
		else {
			$goods_data = $data;
		}

		$time = gmtime();
		$order_type = $GLOBALS['_CFG']['recommend_order'];
		static $type_array = array();

		if ($rec_type == 0) {
			$type2lib = array('best' => 'recommend_best', 'new' => 'recommend_new', 'hot' => 'recommend_hot');
		}
		else if ($rec_type == 1) {
			$type2lib = array('best' => 'recommend_best_goods', 'new' => 'recommend_new_goods', 'hot' => 'recommend_hot_goods');
		}

		if (empty($type_array)) {
			foreach ($type2lib as $key => $data) {
				if (!empty($goods_data[$key])) {
					$num = get_library_number($data);
					$data_count = count($goods_data[$key]);
					$num = $num < $data_count ? $num : $data_count;

					if ($order_type == 0) {
						$rand_key = array_slice($goods_data[$key], 0, $num);

						foreach ($rand_key as $key_data) {
							$type_array[$key][] = $key_data['goods_id'];
						}
					}
					else {
						$rand_key = array_rand($goods_data[$key], $num);

						if ($num == 1) {
							$type_array[$key][] = $goods_data[$key][$rand_key]['goods_id'];
						}
						else {
							foreach ($rand_key as $key_data) {
								$type_array[$key][] = $goods_data[$key][$key_data]['goods_id'];
							}
						}
					}
				}
				else {
					$type_array[$key] = array();
				}
			}
		}

		$sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.comments_number, g.sales_volume, g.market_price, ' . 'g.is_best, g.is_new, g.is_hot, g.user_id, g.model_attr, ' . 'IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'promote_start_date, promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb, g.goods_img, RAND() AS rnd, g.product_price, g.product_promote_price ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ');
		$type_merge = array_merge($type_array['new'], $type_array['best'], $type_array['hot']);
		$type_merge = array_unique($type_merge);
		$sql .= ' WHERE g.goods_id ' . db_create_in($type_merge);
		$sql .= $tag_where;
		$sql .= ' ORDER BY g.sort_order, g.last_update DESC';
		$result = $GLOBALS['db']->getAll($sql);

		foreach ($result as $idx => $row) {
			if (0 < $row['promote_price']) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			}
			else {
				$promote_price = 0;
			}

			$price_info = get_goods_one_attr_price($row, $warehouse_id, $area_id, $promote_price);
			$row = !empty($row) ? array_merge($row, $price_info) : $row;
			$promote_price = $row['promote_price'];

			if (0 < $row['market_price']) {
				$discount_arr = get_discount($row);
			}

			$goods[$idx]['zhekou'] = $discount_arr['discount'];
			$goods[$idx]['jiesheng'] = $discount_arr['jiesheng'];
			$goods[$idx]['id'] = $row['goods_id'];
			$goods[$idx]['name'] = $row['goods_name'];
			$goods[$idx]['is_promote'] = $row['is_promote'];
			$goods[$idx]['brief'] = $row['goods_brief'];
			$goods[$idx]['comments_number'] = $row['comments_number'];
			$goods[$idx]['sales_volume'] = $row['sales_volume'];
			$goods[$idx]['brand_name'] = isset($goods_data['brand'][$row['goods_id']]) ? $goods_data['brand'][$row['goods_id']] : '';
			$goods[$idx]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
			$goods[$idx]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
			$goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
			$goods[$idx]['market_price'] = price_format($row['market_price']);
			$goods[$idx]['shop_price'] = price_format($row['shop_price']);
			$goods[$idx]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
			$goods[$idx]['thumb'] = get_image_path($row['goods_thumb']);
			$goods[$idx]['goods_img'] = get_image_path($row['goods_img']);
			$goods[$idx]['shop_name'] = get_shop_name($row['user_id'], 1);
			$goods[$idx]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			$goods[$idx]['shopUrl'] = url('store/index/index', array('id' => $row['user_id']));

			if (in_array($row['goods_id'], $type_array['best'])) {
				$type_goods['best'][] = $goods[$idx];
			}

			if (in_array($row['goods_id'], $type_array['new'])) {
				$type_goods['new'][] = $goods[$idx];
			}

			if (in_array($row['goods_id'], $type_array['hot'])) {
				$type_goods['hot'][] = $goods[$idx];
			}
		}
	}

	return $type_goods[$type];
}

function get_promote_goods($cats = '', $warehouse_id = 0, $area_id = 0)
{
	$time = gmtime();
	$order_type = $GLOBALS['_CFG']['recommend_order'];
	$leftJoin = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$where = '';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	$num = get_library_number('recommend_promotion');
	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.comments_number, g.sales_volume,g.market_price, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, goods_img, b.brand_name, ' . 'g.is_best, g.is_new, g.is_hot, g.is_promote, RAND() AS rnd ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . 'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . (' AND g.is_promote = 1 AND promote_start_date <= \'' . $time . '\' AND promote_end_date >= \'' . $time . '\' ') . $where;
	$sql .= $order_type == 0 ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY rnd';
	$sql .= ' LIMIT ' . $num . ' ';
	$result = $GLOBALS['db']->getAll($sql);
	$goods = array();

	foreach ($result as $idx => $row) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			$goods[$idx]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		}
		else {
			$goods[$idx]['promote_price'] = '';
		}

		if (0 < $row['market_price']) {
			$discount_arr = get_discount($row);
		}

		$goods[$idx]['zhekou'] = $discount_arr['discount'];
		$goods[$idx]['jiesheng'] = $discount_arr['jiesheng'];
		$goods[$idx]['id'] = $row['goods_id'];
		$goods[$idx]['s_time'] = $row['promote_start_date'];
		$goods[$idx]['e_time'] = $row['promote_end_date'];
		$goods[$idx]['t_now'] = $time;
		$goods[$idx]['id'] = $row['goods_id'];
		$goods[$idx]['name'] = $row['goods_name'];
		$goods[$idx]['brief'] = $row['goods_brief'];
		$goods[$idx]['brand_name'] = $row['brand_name'];
		$goods[$idx]['comments_number'] = $row['comments_number'];
		$goods[$idx]['sales_volume'] = $row['sales_volume'];
		$goods[$idx]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
		$goods[$idx]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
		$goods[$idx]['market_price'] = price_format($row['market_price']);
		$goods[$idx]['shop_price'] = price_format($row['shop_price']);
		$goods[$idx]['thumb'] = get_image_path($row['goods_thumb']);
		$goods[$idx]['goods_img'] = get_image_path($row['goods_img']);
		$goods[$idx]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
	}

	return $goods;
}

function get_category_recommend_goods($type = '', $cats = '', $warehouse_id = 0, $area_id = 0, $current = 1, $pageSize = 10)
{
	$brand_where = 0 < $brand ? ' AND g.brand_id = \'' . $brand . '\'' : '';
	$price_where = 0 < $min ? ' AND g.shop_price >= ' . $min . ' ' : '';
	$price_where .= 0 < $max ? ' AND g.shop_price <= ' . $max . ' ' : '';
	$where = '';

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.comments_number ,g.sales_volume,' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price,' . 'promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, goods_img, b.brand_name ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . 'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . $where . $brand_where . $price_where . $ext;
	$type2lib = array('best' => 'recommend_best', 'new' => 'recommend_new', 'hot' => 'recommend_hot', 'promote' => 'recommend_promotion');

	switch ($type) {
	case 'best':
		$sql .= ' AND is_best = 1';
		break;

	case 'new':
		$sql .= ' AND is_new = 1';
		break;

	case 'hot':
		$sql .= ' AND is_hot = 1';
		break;

	case 'promote':
		$time = gmtime();
		$sql .= ' AND is_promote = 1 AND promote_start_date <= \'' . $time . '\' AND promote_end_date >= \'' . $time . '\'';
		break;
	}

	if (!empty($cats)) {
		$sql .= ' AND g.cat_id = ' . $cats;
	}

	$order_type = $GLOBALS['_CFG']['recommend_order'];
	$sql .= $order_type == 0 ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY RAND()';
	$sql .= ' LIMIT ' . $current . ' ,' . $pageSize . ' ';
	$res = $GLOBALS['db']->getall($sql);
	$idx = 0;
	$goods = array();

	foreach ($res as $row) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			$goods[$idx]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		}
		else {
			$goods[$idx]['promote_price'] = '';
		}

		$goods[$idx]['id'] = $row['goods_id'];

		if (0 < $row['market_price']) {
			$discount_arr = get_discount($row);
		}

		$goods[$idx]['zhekou'] = $discount_arr['discount'];
		$goods[$idx]['jiesheng'] = $discount_arr['jiesheng'];
		$goods[$idx]['comments_number'] = $row['comments_number'];
		$goods[$idx]['sales_volume'] = $row['sales_volume'];
		$goods[$idx]['name'] = $row['goods_name'];
		$goods[$idx]['brief'] = $row['goods_brief'];
		$goods[$idx]['brand_name'] = $row['brand_name'];
		$goods[$idx]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$goods[$idx]['market_price'] = price_format($row['market_price']);
		$goods[$idx]['shop_price'] = price_format($row['shop_price']);
		$goods[$idx]['thumb'] = get_image_path($row['goods_thumb']);
		$goods[$idx]['goods_img'] = get_image_path($row['goods_img']);
		$goods[$idx]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
		$idx++;
	}

	return $goods;
}

function get_goods_info($goods_id, $warehouse_id = 0, $area_id = 0)
{
	$time = gmtime();
	$tag = array();
	$leftJoin = '';
	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$sql = 'SELECT g.*, ' . $shop_price . ' IF(g.model_inventory < 1, g.goods_number, IF(g.model_inventory < 2, wg.region_number, wag.region_number)) as goods_number,' . (' IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price,') . ' IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price,' . ' c.measure_unit, g.brand_id as brand_id, b.brand_logo, g.comments_number, g.sales_volume,b.brand_name AS goods_brand, m.type_money AS bonus_money, ' . 'IFNULL(AVG(r.comment_rank), 0) AS comment_rank, ' . ('IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\') AS rank_price ') . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('category') . ' AS c ON g.cat_id = c.cat_id ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('comment') . ' AS r ' . 'ON r.id_value = g.goods_id AND comment_type = 0 AND r.parent_id = 0 AND r.status = 1 ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('bonus_type') . ' AS m ' . ('ON g.bonus_type_id = m.type_id AND m.send_start_date <= \'' . $time . '\' AND m.send_end_date >= \'' . $time . '\'') . ' LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' . ('WHERE g.goods_id = \'' . $goods_id . '\' AND g.is_delete = 0 ') . 'GROUP BY g.goods_id';
	$row = $GLOBALS['db']->getRow($sql);

	if ($row !== false) {
		$row['comment_rank'] = ceil($row['comment_rank']) == 0 ? 5 : ceil($row['comment_rank']);

		if (0 < $row['market_price']) {
			$discount_arr = get_discount($row);
		}

		$row['zhekou'] = $discount_arr['discount'];
		$row['jiesheng'] = $discount_arr['jiesheng'];

		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$watermark_img = '';

		if ($promote_price != 0) {
			$watermark_img = 'watermark_promote';
		}
		else if ($row['is_new'] != 0) {
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

		$row['promote_price_org'] = $promote_price;
		$row['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$time = gmtime();
		if ($row['promote_start_date'] <= $time && $time <= $row['promote_end_date']) {
			$row['gmt_end_time'] = $row['promote_end_date'];
		}
		else {
			$row['gmt_end_time'] = 0;
		}

		$row['promote_end_time'] = !empty($row['gmt_end_time']) ? local_date($GLOBALS['_CFG']['time_format'], $row['gmt_end_time']) : 0;
		$row['goods_number'] = $GLOBALS['_CFG']['use_storage'] == 1 ? $row['goods_number'] : '1';
		$row['attr_number'] = $row['goods_number'];
		$row['integral'] = $GLOBALS['_CFG']['integral_scale'] ? round($row['integral'] * 100 / $GLOBALS['_CFG']['integral_scale']) : 0;
		$row['bonus_money'] = $row['bonus_money'] == 0 ? 0 : price_format($row['bonus_money'], false);

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();

			if ($row['goods_desc']) {
				$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $row['goods_desc']);
				$row['goods_desc'] = $desc_preg['goods_desc'];
			}
		}

		$goods['goods_brand_url'] = build_uri('brand', array('bid' => $row['brand_id']));
		$row['goods_img'] = get_image_path($row['goods_img']);
		$row['goods_thumb'] = get_image_path($row['goods_thumb']);
		$row['marketPrice'] = $row['market_price'];
		$row['market_price'] = price_format($row['market_price']);

		if (0 < $promote_price) {
			$row['shop_price_formated'] = $row['promote_price'];
			$row['goods_price'] = $promote_price;
		}
		else {
			$row['shop_price_formated'] = price_format($row['shop_price']);
			$row['goods_price'] = $row['shop_price'];
		}

		$row['promote_price'] = price_format($promote_price);
		$row['goodsweight'] = $row['goods_weight'];
		$row['isHas_attr'] = count($GLOBALS['db']->getAll('select goods_attr_id from ' . $GLOBALS['ecs']->table('goods_attr') . (' where goods_id = \'' . $goods_id . '\'')));
		$row['rz_shopName'] = get_shop_name($row['user_id'], 1);
		$row['store_url'] = url('store/index/shop_info', array('id' => $row['user_id']));
		$row['shopinfo'] = get_shop_name($row['user_id'], 2);
		$row['shopinfo']['logo_thumb'] = get_image_path(str_replace('../', '', $row['shopinfo']['logo_thumb']));
		$row['shopinfo']['brand_thumb'] = get_image_path($row['shopinfo']['brand_thumb']);
		$row['goods_url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$consumption = get_goods_con_list($goods_id, 'goods_consumption');
		$conshipping = get_goods_con_list($goods_id, 'goods_conshipping', 1);
		$sql = 'SELECT ld.goods_desc FROM ' . $GLOBALS['ecs']->table('link_desc_goodsid') . ' AS dg, ' . $GLOBALS['ecs']->table('link_goods_desc') . ' AS ld WHERE dg.goods_id = \'' . $row['goods_id'] . '\' AND dg.d_id = ld.id AND ld.review_status > 2';
		$link_desc = $GLOBALS['db']->getOne($sql);
		if ($row['goods_desc'] == '<p><br/></p>' || empty($row['goods_desc'])) {
			$row['goods_desc'] = $link_desc;
		}

		$row['consumption'] = $consumption;
		$row['conshipping'] = $conshipping;
		$row['goods_weight'] = 0 < intval($row['goods_weight']) ? $row['goods_weight'] . $GLOBALS['_LANG']['kilogram'] : $row['goods_weight'] * 1000 . $GLOBALS['_LANG']['gram'];
		$suppliers = get_suppliers_name($row['suppliers_id']);
		$row['suppliers_name'] = $suppliers['suppliers_name'];

		if ($row['goods_product_tag']) {
			$impression_list = !empty($row['goods_product_tag']) ? explode(',', $row['goods_product_tag']) : '';

			foreach ($impression_list as $kk => $vv) {
				$tag[$kk]['txt'] = $vv;
				$tag[$kk]['num'] = comment_goodstag_num($row['goods_id'], $vv);
			}

			$row['impression_list'] = $tag;
		}

		$manage_info = get_auto_manage_info($row['goods_id'], 'goods');

		if (!empty($manage_info['starttime'])) {
			$row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $manage_info['starttime']);
		}
		else {
			$row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
		}

		$row['end_time'] = !empty($manage_info['endtime']) ? local_date($GLOBALS['_CFG']['time_format'], $manage_info['endtime']) : '';
		$row['collect_count'] = get_collect_goods_user_count($row['goods_id']);
		$row['is_collect'] = get_collect_user_goods($row['goods_id']);
		return $row;
	}
	else {
		return false;
	}
}

function get_goods_extends($goods_id = 0)
{
	$sql = 'select * from ' . $GLOBALS['ecs']->table('goods_extend') . (' where goods_id=\'' . $goods_id . '\'');
	$goods_extend = $GLOBALS['db']->getRow($sql);

	if (0 < count($goods_extend)) {
		return $goods_extend;
	}
	else {
		return '';
	}
}

function get_goods_properties($goods_id, $warehouse_id = 0, $area_id = 0, $goods_attr_id = '', $attr_type = 0)
{
	$attr_array = array();

	if (!empty($goods_attr_id)) {
		$attr_array = explode(',', $goods_attr_id);
	}

	$sql = 'SELECT attr_group ' . 'FROM ' . $GLOBALS['ecs']->table('goods_type') . ' AS gt, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ('WHERE g.goods_id=\'' . $goods_id . '\' AND gt.cat_id=g.goods_type');
	$grp = $GLOBALS['db']->getOne($sql);

	if (!empty($grp)) {
		$groups = explode("\n", strtr($grp, "\r", ''));
	}

	$model_attr = get_table_date('goods', 'goods_id = \'' . $goods_id . '\'', array('model_attr'), 2);
	$leftJoin = '';
	$select = '';

	if ($model_attr == 1) {
		$select = ' wap.attr_price as warehouse_attr_price, ';
		$leftJoin = 'LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_attr') . (' AS wap ON g.goods_attr_id = wap.goods_attr_id AND wap.warehouse_id = \'' . $warehouse_id . '\' ');
	}
	else if ($model_attr == 2) {
		$select = ' waa.attr_price as area_attr_price, ';
		$leftJoin = 'LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_attr') . (' AS waa ON g.goods_attr_id = waa.goods_attr_id AND area_id = \'' . $area_id . '\' ');
	}

	$goodsAttr = '';
	if ($attr_type == 1 && !empty($goods_attr_id)) {
		$goodsAttr = ' and g.goods_attr_id in(' . $goods_attr_id . ') ';
	}

	$sql = 'SELECT a.attr_id, a.attr_name, a.attr_group, a.is_linked, a.attr_type, ' . $select . 'g.goods_attr_id, g.attr_value, g.attr_price, g.attr_img_flie, g.attr_img_site, g.attr_checked, g.attr_sort ' . 'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.attr_id = g.attr_id ' . $leftJoin . ('WHERE g.goods_id = \'' . $goods_id . '\' ') . $goodsAttr . 'ORDER BY a.sort_order, g.attr_sort, a.attr_id, g.goods_attr_id ASC';
	$res = $GLOBALS['db']->getAll($sql);
	$arr['pro'] = array();
	$arr['spe'] = array();
	$arr['lnk'] = array();

	foreach ($res as $row) {
		$row['attr_value'] = str_replace("\n", '<br />', $row['attr_value']);

		if ($row['attr_type'] == 0) {
			$group = isset($groups[$row['attr_group']]) ? $groups[$row['attr_group']] : $GLOBALS['_LANG']['goods_attr'];
			$arr['pro'][$group][$row['attr_id']]['name'] = $row['attr_name'];
			$arr['pro'][$group][$row['attr_id']]['value'] = $row['attr_value'];
		}
		else {
			if ($model_attr == 1) {
				$attr_price = $row['warehouse_attr_price'];
			}
			else if ($model_attr == 2) {
				$attr_price = $row['area_attr_price'];
			}
			else {
				$attr_price = $row['attr_price'];
			}

			$arr['spe'][$row['attr_id']]['attr_type'] = $row['attr_type'];
			$arr['spe'][$row['attr_id']]['name'] = $row['attr_name'];

			if ($row['attr_checked'] == 1) {
				$arr['spe'][$row['attr_id']]['checked'] = $row['attr_checked'];
			}

			$arr['spe'][$row['attr_id']]['values'][] = array('label' => $row['attr_value'], 'img_flie' => get_has_attr_info($row['attr_id'], $row['attr_value'], $row['attr_img_flie'], 0), 'img_site' => get_has_attr_info($row['attr_id'], $row['attr_value'], $row['attr_img_site'], 1), 'checked' => $row['attr_checked'], 'attr_sort' => $row['attr_sort'], 'combo_checked' => get_combo_godos_attr($attr_array, $row['goods_attr_id']), 'price' => $attr_price, 'format_price' => price_format(abs($attr_price), false), 'id' => $row['goods_attr_id']);
		}

		if ($row['is_linked'] == 1) {
			$arr['lnk'][$row['attr_id']]['name'] = $row['attr_name'];
			$arr['lnk'][$row['attr_id']]['value'] = $row['attr_value'];
		}
	}

	return $arr;
}

function get_combo_godos_attr($attr_array, $goods_attr_id)
{
	if ($attr_array) {
		for ($i = 0; $i < count($attr_array); $i++) {
			if ($attr_array[$i] == $goods_attr_id) {
				$checked = 1;
				break;
			}
			else {
				$checked = 0;
			}
		}
	}
	else {
		$checked = 0;
	}

	return $checked;
}

function get_has_attr_info($attr_id = 0, $attr_value = '', $centent = '', $type = 0)
{
	$sql = 'select attr_img, attr_site from ' . $GLOBALS['ecs']->table('attribute_img') . (' where attr_values = \'' . $attr_value . '\' and attr_id = \'' . $attr_id . '\'');
	$res = $GLOBALS['db']->getRow($sql);

	if (empty($centent)) {
		if ($type == 0) {
			$centent = $res['attr_img'];
		}
		else if ($type == 1) {
			$centent = $res['attr_site'];
		}
	}

	return $centent;
}

function get_attr_values($values = array())
{
	if (0 < count($values)) {
		$is_checked = '';

		for ($i = 0; $i < count($values); $i++) {
			$is_checked += $values[$i]['checked'];
		}

		return $is_checked;
	}
	else {
		return 0;
	}
}

function get_same_attribute_goods($attr)
{
	$lnk = array();

	if (!empty($attr)) {
		foreach ($attr['lnk'] as $key => $val) {
			$lnk[$key]['title'] = sprintf($GLOBALS['_LANG']['same_attrbiute_goods'], $val['name'], $val['value']);
			$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.sales_volume,g.comments_number,g.goods_img, g.shop_price AS org_price, ' . ('IFNULL(mp.user_price, g.shop_price * \'' . $_SESSION['discount'] . '\') AS shop_price, ') . 'g.market_price, g.promote_price, g.promote_start_date, g.promote_end_date ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods_attr') . ' as a ON g.goods_id = a.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE a.attr_id = \'' . $key . '\' AND g.is_on_sale=1 AND a.attr_value = \'' . $val['value'] . '\' AND g.goods_id <> \'' . $_REQUEST['id'] . '\' ') . 'LIMIT ' . $GLOBALS['_CFG']['attr_related_number'];
			$res = $GLOBALS['db']->getAll($sql);

			foreach ($res as $row) {
				$lnk[$key]['goods'][$row['goods_id']]['goods_id'] = $row['goods_id'];
				$lnk[$key]['goods'][$row['goods_id']]['goods_name'] = $row['goods_name'];

				if (0 < $row['market_price']) {
					$discount_arr = get_discount($row);
				}

				$lnk[$key]['goods'][$row['goods_id']]['zhekou'] = $discount_arr['discount'];
				$lnk[$key]['goods'][$row['goods_id']]['jiesheng'] = $discount_arr['jiesheng'];
				$lnk[$key]['goods'][$row['goods_id']]['sales_volume'] = $row['sales_volume'];
				$lnk[$key]['goods'][$row['goods_id']]['comments_number'] = $row['comments_number'];
				$lnk[$key]['goods'][$row['goods_id']]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
				$lnk[$key]['goods'][$row['goods_id']]['goods_thumb'] = empty($row['goods_thumb']) ? $GLOBALS['_CFG']['no_picture'] : $row['goods_thumb'];
				$lnk[$key]['goods'][$row['goods_id']]['market_price'] = price_format($row['market_price']);
				$lnk[$key]['goods'][$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
				$lnk[$key]['goods'][$row['goods_id']]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
				$lnk[$key]['goods'][$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			}
		}
	}

	return $lnk;
}

function get_goods_gallery($goods_id, $gallery_number = 0, $table = 'goods_gallery')
{
	$sql = 'SELECT img_id, img_url, thumb_url, img_desc, external_url' . ' FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE goods_id = \'' . $goods_id . '\'  ORDER BY img_desc ASC LIMIT ') . $GLOBALS['_CFG']['goods_gallery_number'];
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $gallery_img) {
		if (!empty($gallery_img['external_url'])) {
			$row[$key]['img_url'] = $gallery_img['external_url'];
			$row[$key]['thumb_url'] = $gallery_img['external_url'];
		}
		else {
			$row[$key]['img_url'] = get_image_path($gallery_img['img_url']);
			$row[$key]['thumb_url'] = get_image_path($gallery_img['thumb_url']);
		}
	}

	return $row;
}

function assign_cat_goods($cat_id, $num = 0, $from = 'web', $order_rule = '', $return = 'cat', $warehouse_id = 0, $area_id = 0, $floor_sort_order = 0)
{
	$children = get_category_parentChild_tree1($cat_id, 1);
	$children = arr_foreach($children);

	if ($children) {
		$children = implode(',', $children) . ',' . $cat_id;
		$children = get_children($children, 0, 1);
	}
	else {
		$children = 'g.cat_id IN (' . $cat_id . ')';
	}

	$leftJoin = '';
	$tag_where = '';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$tag_where = ' and lag.region_id = \'' . $area_id . '\' ';
	}

	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$tag_where .= ' AND g.review_status > 2 ';
	}

	$sql = 'SELECT g.goods_id, g.goods_name, g.market_price, g.comments_number ,g.sales_volume, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . ' g.is_promote, g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . 'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ' . 'g.is_delete = 0 AND (' . $children . 'OR ' . get_extension_goods($children) . ') ' . $tag_where;
	$order_rule = empty($order_rule) ? 'ORDER BY g.sort_order, g.goods_id DESC' : $order_rule;
	$sql .= $order_rule;

	if (0 < $num) {
		$sql .= ' LIMIT ' . $num;
	}

	$res = $GLOBALS['db']->getAll($sql);
	$goods = array();

	foreach ($res as $idx => $row) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			$goods[$idx]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		}
		else {
			$goods[$idx]['promote_price'] = '';
		}

		$goods_res[$idx]['is_promote'] = $row['is_promote'];
		$goods[$idx]['id'] = $row['goods_id'];
		$goods[$idx]['name'] = $row['goods_name'];
		$goods[$idx]['brief'] = $row['goods_brief'];

		if (0 < $row['market_price']) {
			$discount_arr = get_discount($row);
		}

		$goods[$idx]['zhekou'] = $discount_arr['discount'];
		$goods[$idx]['jiesheng'] = $discount_arr['jiesheng'];
		$goods[$idx]['comments_number'] = $row['comments_number'];
		$goods[$idx]['sales_volume'] = $row['sales_volume'];
		$goods[$idx]['market_price'] = price_format($row['market_price']);
		$goods[$idx]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$goods[$idx]['shop_price'] = price_format($row['shop_price']);
		$goods[$idx]['thumb'] = get_image_path($row['goods_thumb']);
		$goods[$idx]['goods_img'] = get_image_path($row['goods_img']);
		$goods[$idx]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
	}

	if ($from == 'web') {
		$goods['id'] = $cat_id;
		$GLOBALS['smarty']->assign('cat_goods_' . $cat_id, $goods);
	}
	else if ($from == 'wap') {
		$cat['goods'] = $goods;
	}

	$sql = 'SELECT cat_name FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id = \'' . $cat_id . '\'');
	$cat['name'] = $GLOBALS['db']->getOne($sql);
	$cat['url'] = build_uri('category', array('cid' => $cat_id), $cat['name']);
	$cat['id'] = $cat_id;
	$cat_list_arr = cat_list($cat_id, 0);
	$goods_index_cat1 = get_cat_goods_index_cat1($cat_list_arr);
	$goods_index_cat2 = get_cat_goods_index_cat2($goods_index_cat1);

	foreach ($goods_index_cat2 as $key => $value) {
		if ($value['level'] == 1) {
			$sql = 'SELECT g.goods_id,g.cat_id, g.goods_name, g.market_price, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . ' g.is_promote, g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . 'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND is_delete = 0 AND ' . get_children($value['cat_id']) . $tag_where . ' ORDER BY g.sort_order, g.goods_id DESC';

			if (0 < $num) {
				$sql .= ' LIMIT ' . $num;
			}

			$goods_res = $GLOBALS['db']->getAll($sql);

			foreach ($goods_res as $idx => $row) {
				if (0 < $row['promote_price']) {
					$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
					$goods_res[$idx]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
				}
				else {
					$goods_res[$idx]['promote_price'] = '';
				}

				$goods_res[$idx]['is_promote'] = $row['is_promote'];
				$goods_res[$idx]['market_price'] = price_format($row['market_price']);
				$goods_res[$idx]['shop_price'] = price_format($row['shop_price']);
				$goods_res[$idx]['promote_price'] = $goods_res[$idx]['promote_price'];
				$goods_res[$idx]['shop_price'] = price_format($row['shop_price']);
				$goods_res[$idx]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
				$goods_res[$idx]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			}

			$goods_index_cat2[$key]['goods'] = $goods_res;
		}
		else {
			unset($goods_index_cat2[$key]);
		}
	}

	$cat['goods_level2'] = $goods_index_cat1;
	$cat['goods_level3'] = $goods_index_cat2;
	$brand_tag_where = '';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$brand_leftJoin .= ', ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag ';
		$brand_tag_where = ' AND g.goods_id = lag.goods_id AND lag.region_id = \'' . $area_id . '\' ';
	}

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$brand_tag_where .= ' AND g.review_status > 2 ';
	}

	$cat['floor_banner'] = 'floor_banner' . $cat_id;
	$cat['floor_sort_order'] = $floor_sort_order + 1;
	$cat['brands_theme2'] = get_brands_theme2($brands);
	return $cat;
}

function get_cat_goods_index_cat1($cat_list_arr)
{
	foreach ($cat_list_arr as $key => $value) {
		if ($value['level'] != 1) {
			unset($cat_list_arr[$key]);
		}
		else {
			$cat_list_arr[$key] = $value;
			$cat_list_arr[$key]['child_tree'] = get_child_tree($value['cat_id']);
		}
	}

	$cat_list_arr = array_values($cat_list_arr);
	return $cat_list_arr;
}

function get_cat_goods_index_cat2($cat_list_arr)
{
	foreach ($cat_list_arr as $key => $value) {
		if ($key <= 10) {
			$cat_list_arr[$key] = $value;
		}
		else {
			unset($cat_list_arr[$key]);
		}
	}

	return $cat_list_arr;
}

function get_brands_theme2($brands)
{
	$arr = array();

	if ($brands) {
		foreach ($brands as $key => $row) {
			if ($key < 8) {
				$arr['one_brands'][$key] = $row;
			}
			else {
				if (8 <= $key && $key <= 14) {
					$arr['two_brands'][$key] = $row;
				}
				else {
					if (15 <= $key && $key <= 21) {
						$arr['three_brands'][$key] = $row;
					}
					else {
						if (22 <= $key && $key <= 28) {
							$arr['foure_brands'][$key] = $row;
						}
						else {
							if (29 <= $key && $key <= 35) {
								$arr['five_brands'][$key] = $row;
							}
						}
					}
				}
			}
		}

		$arr = array_values($arr);
	}

	return $arr;
}

function assign_brand_goods($brand_id, $num = 0, $cat_id = 0, $order_rule = '', $warehouse_id, $area_id)
{
	$leftJoin = '';
	$tag_where = '';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$tag_where = ' and lag.region_id = \'' . $area_id . '\' ';
	}

	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$sql = 'SELECT g.goods_id, g.goods_name, g.market_price, g.sales_volume,g.comments_number, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . ' g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.brand_id = \'' . $brand_id . '\'') . $tag_where;

	if (0 < $cat_id) {
		$sql .= get_children($cat_id);
	}

	$order_rule = empty($order_rule) ? ' ORDER BY g.sort_order, g.goods_id DESC' : $order_rule;
	$sql .= $order_rule;

	if (0 < $num) {
		$res = $GLOBALS['db']->selectLimit($sql, $num);
	}
	else {
		$res = $GLOBALS['db']->query($sql);
	}

	$idx = 0;
	$goods = array();

	foreach ($res as $row) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$goods[$idx]['id'] = $row['goods_id'];
		$goods[$idx]['name'] = $row['goods_name'];

		if (0 < $row['market_price']) {
			$discount_arr = get_discount($row);
		}

		$goods[$idx]['zhekou'] = $discount_arr['discount'];
		$goods[$idx]['jiesheng'] = $discount_arr['jiesheng'];
		$goods[$idx]['comments_number'] = $row['comments_number'];
		$goods[$idx]['sales_volume'] = $row['sales_volume'];
		$goods[$idx]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$goods[$idx]['market_price'] = price_format($row['market_price']);
		$goods[$idx]['shop_price'] = price_format($row['shop_price']);
		$goods[$idx]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$goods[$idx]['brief'] = $row['goods_brief'];
		$goods[$idx]['thumb'] = get_image_path($row['goods_thumb']);
		$goods[$idx]['goods_img'] = get_image_path($row['goods_img']);
		$goods[$idx]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$idx++;
	}

	$sql = 'SELECT brand_name FROM ' . $GLOBALS['ecs']->table('brand') . (' WHERE brand_id = \'' . $brand_id . '\'');
	$brand['id'] = $brand_id;
	$brand['name'] = $GLOBALS['db']->getOne($sql);
	$brand['url'] = build_uri('brand', array('bid' => $brand_id), $brand['name']);
	$brand_goods = array('brand' => $brand, 'goods' => $goods);
	return $brand_goods;
}

function get_extension_goods($cats)
{
	$extension_goods_array = '';
	$sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods_cat') . (' AS g WHERE ' . $cats);
	$extension_goods_array = $GLOBALS['db']->getCol($sql);
	return db_create_in($extension_goods_array, 'g.goods_id');
}

function bargain_price($price, $start, $end)
{
	if ($price == 0) {
		return 0;
	}
	else {
		$time = gmtime();
		if ($start <= $time && $time <= $end) {
			return $price;
		}
		else {
			return 0;
		}
	}
}

function spec_price($spec, $goods_id = 0, $warehouse_area = array())
{
	if (!empty($spec)) {
		if (is_array($spec)) {
			foreach ($spec as $key => $val) {
				$spec[$key] = addslashes($val);
			}
		}
		else {
			$spec = addslashes($spec);
		}

		$warehouse_id = $warehouse_area['warehouse_id'];
		$area_id = $warehouse_area['area_id'];
		$model_attr = get_table_date('goods', 'goods_id = \'' . $goods_id . '\'', array('model_attr'), 2);
		$attr['price'] = 0;

		if ($GLOBALS['_CFG']['goods_attr_price'] == 1) {
			$attr_type_spec = '';

			foreach ($spec as $key => $val) {
				$attr_type = get_goods_attr_id(array('goods_attr_id' => $val, 'goods_id' => $goods_id), array('a.attr_type'));
				if ($attr_type == 2 && $spec[$key]) {
					$attr_type_spec .= $spec[$key] . ',';
					unset($spec[$key]);
				}
			}

			$attr_type_spec_price = 0;

			if ($attr_type_spec) {
				$attr_type_spec_price = get_goods_attr_price($goods_id, $model_attr, $attr_type_spec, $warehouse_id, $area_id);
			}

			$where = '';

			foreach ($spec as $key => $val) {
				$where .= ' AND FIND_IN_SET(\'' . $val . '\', REPLACE(goods_attr, \'|\', \',\')) ';
			}

			if ($model_attr == 1) {
				$table = 'products_warehouse';
				$where .= ' AND warehouse_id = \'' . $warehouse_id . '\' ' . $where;
			}
			else if ($model_attr == 2) {
				$table = 'products_area';
				$area_id = $warehouse_area['area_id'];
				$where .= ' AND area_id = \'' . $area_id . '\' ' . $where;
			}
			else {
				$table = 'products';
			}

			$sql = 'SELECT product_price FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE  goods_id = \'' . $goods_id . '\' ' . $where);
			$price = $GLOBALS['db']->getOne($sql);
			$price += $attr_type_spec_price;
		}
		else {
			$price = get_goods_attr_price($goods_id, $model_attr, $spec, $warehouse_id, $area_id);
		}
	}
	else {
		$price = 0;
	}

	return floatval($price);
}

function get_goods_attr_price($goods_id, $model_attr, $spec, $warehouse_id, $area_id)
{
	$where = ' AND ' . db_create_in($spec, 'goods_attr_id');

	if ($model_attr == 1) {
		$sql = 'SELECT SUM(attr_price) AS attr_price FROM ' . $GLOBALS['ecs']->table('warehouse_attr') . (' WHERE goods_id = \'' . $goods_id . '\' AND warehouse_id = \'' . $warehouse_id . '\'') . $where;
	}
	else if ($model_attr == 2) {
		$sql = 'SELECT SUM(attr_price) AS attr_price FROM ' . $GLOBALS['ecs']->table('warehouse_area_attr') . (' WHERE goods_id = \'' . $goods_id . '\' AND area_id = \'' . $area_id . '\'') . $where;
	}
	else {
		$sql = 'SELECT SUM(attr_price) AS attr_price FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_id = \'' . $goods_id . '\' ' . $where);
	}

	$price = $GLOBALS['db']->getOne($sql, true);
	return $price;
}

function group_buy_info($group_buy_id, $current_num = 0)
{
	$group_buy_id = intval($group_buy_id);
	$sql = 'SELECT b.*,g.*, b.act_id AS group_buy_id, b.act_desc AS group_buy_desc, b.start_time AS start_date, b.end_time AS end_date ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS b ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON b.goods_id = g.goods_id ' . ('WHERE act_id = \'' . $group_buy_id . '\' ') . 'AND act_type = \'' . GAT_GROUP_BUY . '\'' . 'AND b.review_status = 3';
	$group_buy = $GLOBALS['db']->getRow($sql);

	if (empty($group_buy)) {
		return array();
	}

	$ext_info = unserialize($group_buy['ext_info']);
	$group_buy = array_merge($group_buy, $ext_info);
	$group_buy['formated_start_date'] = local_date('Y-m-d H:i', $group_buy['start_time']);
	$group_buy['formated_end_date'] = local_date('Y-m-d H:i', $group_buy['end_time']);
	$now = gmtime();
	$group_buy['is_end'] = $group_buy['end_time'] < $now ? 1 : 0;
	$group_buy['xiangou_start_date'] = $group_buy['start_time'];
	$group_buy['xiangou_end_date'] = $group_buy['end_time'];
	$group_buy['formated_deposit'] = price_format($group_buy['deposit'], false);
	$price_ladder = $group_buy['price_ladder'];
	if (!is_array($price_ladder) || empty($price_ladder)) {
		$price_ladder = array(
			array('amount' => 0, 'price' => 0)
			);
	}
	else {
		foreach ($price_ladder as $key => $amount_price) {
			$price_ladder[$key]['formated_price'] = price_format($amount_price['price'], false);
		}
	}

	$group_buy['price_ladder'] = $price_ladder;
	$stat = group_buy_stat($group_buy_id, $group_buy['deposit']);
	$group_buy = array_merge($group_buy, $stat);
	$cur_price = $price_ladder[0]['price'];
	$cur_amount = $stat['valid_goods'] + $current_num;

	foreach ($price_ladder as $amount_price) {
		if ($amount_price['amount'] <= $cur_amount) {
			$cur_price = $amount_price['price'];
		}
		else {
			break;
		}
	}

	$group_buy['goods_desc'] = $GLOBALS['db']->getOne('select goods_desc from ' . $GLOBALS['ecs']->table('goods') . ' where goods_id = \'' . $group_buy['goods_id'] . '\'');
	$group_buy['cur_price'] = $cur_price;
	$group_buy['formated_cur_price'] = price_format($cur_price, false);
	$price = $group_buy['market_price'];
	$nowprice = $group_buy['cur_price'];
	$group_buy['jiesheng'] = $price - $nowprice;
	if (0 < $nowprice && 0 < $price) {
		$group_buy['zhekou'] = round(10 / ($price / $nowprice), 1);
	}
	else {
		$group_buy['zhekou'] = 0;
	}

	$group_buy['trans_price'] = $group_buy['cur_price'];
	$group_buy['formated_trans_price'] = $group_buy['formated_cur_price'];
	$group_buy['trans_amount'] = $group_buy['valid_goods'];
	$group_buy['status'] = group_buy_status($group_buy);

	if (isset($GLOBALS['_LANG']['gbs'][$group_buy['status']])) {
		$group_buy['status_desc'] = $GLOBALS['_LANG']['gbs'][$group_buy['status']];
	}

	$group_buy['start_time'] = $group_buy['formated_start_date'];
	$group_buy['end_time'] = $group_buy['formated_end_date'];
	$group_buy['rz_shopName'] = get_shop_name($group_buy['user_id'], 1);
	$group_buy['store_url'] = build_uri('merchants_store', array('urid' => $group_buy['user_id']), $group_buy['rz_shopName']);
	$group_buy['shopinfo'] = get_shop_name($group_buy['user_id'], 2);
	$group_buy['shopinfo']['brand_thumb'] = str_replace(array('../'), '', $group_buy['shopinfo']['brand_thumb']);

	if ($group_buy['goods_product_tag']) {
		$impression_list = !empty($group_buy['goods_product_tag']) ? explode(',', $group_buy['goods_product_tag']) : '';

		foreach ($impression_list as $kk => $vv) {
			$tag[$kk]['txt'] = $vv;
			$tag[$kk]['num'] = comment_goodstag_num($group_buy['goods_id'], $vv);
		}

		$group_buy['impression_list'] = $tag;
	}

	$group_buy['collect_count'] = get_collect_goods_user_count($group_buy['goods_id']);
	return $group_buy;
}

function group_buy_stat($group_buy_id, $deposit)
{
	$group_buy_id = intval($group_buy_id);
	$sql = 'SELECT goods_id ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ('WHERE act_id = \'' . $group_buy_id . '\' ') . 'AND act_type = \'' . GAT_GROUP_BUY . '\'' . 'AND review_status = 3 ';
	$group_buy_goods_id = $GLOBALS['db']->getOne($sql);
	$sql = 'SELECT COUNT(*) AS total_order, SUM(g.goods_number) AS total_goods ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o, ' . $GLOBALS['ecs']->table('order_goods') . ' AS g ' . ' WHERE o.order_id = g.order_id ' . 'AND o.extension_code = \'group_buy\' ' . ('AND o.extension_id = \'' . $group_buy_id . '\' ') . ('AND g.goods_id = \'' . $group_buy_goods_id . '\' ') . 'AND (order_status = \'' . OS_CONFIRMED . '\' OR order_status = \'' . OS_UNCONFIRMED . '\')';
	$stat = $GLOBALS['db']->getRow($sql);

	if ($stat['total_order'] == 0) {
		$stat['total_goods'] = 0;
	}

	$deposit = floatval($deposit);
	if (0 < $deposit && 0 < $stat['total_order']) {
		$sql .= ' AND (o.money_paid + o.surplus) >= \'' . $deposit . '\'';
		$row = $GLOBALS['db']->getRow($sql);
		$stat['valid_order'] = $row['total_order'];

		if ($stat['valid_order'] == 0) {
			$stat['valid_goods'] = 0;
		}
		else {
			$stat['valid_goods'] = $row['total_goods'];
		}
	}
	else {
		$stat['valid_order'] = $stat['total_order'];
		$stat['valid_goods'] = $stat['total_goods'];
	}

	return $stat;
}

function group_buy_status($group_buy)
{
	$now = gmtime();

	if ($group_buy['is_finished'] == 0) {
		if ($now < $group_buy['start_time']) {
			$status = GBS_PRE_START;
		}
		else if ($group_buy['end_time'] < $now) {
			$status = GBS_FINISHED;
		}
		else {
			if ($group_buy['restrict_amount'] == 0 || $group_buy['valid_goods'] < $group_buy['restrict_amount']) {
				$status = GBS_UNDER_WAY;
			}
			else {
				$status = GBS_FINISHED;
			}
		}
	}
	else if ($group_buy['is_finished'] == GBS_SUCCEED) {
		$status = GBS_SUCCEED;
	}
	else if ($group_buy['is_finished'] == GBS_FAIL) {
		$status = GBS_FAIL;
	}

	return $status;
}

function auction_info($act_id, $config = false)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE act_id = \'' . $act_id . '\' AND review_status = 3');
	$auction = $GLOBALS['db']->getRow($sql);
	$auction['endTime'] = $auction['end_time'];

	if ($auction['act_type'] != GAT_AUCTION) {
		return array();
	}

	$auction['status_no'] = auction_status($auction);

	if ($config == true) {
		$auction['start_time'] = local_date('Y-m-d H:i', $auction['start_time']);
		$auction['end_time'] = local_date('Y-m-d H:i', $auction['end_time']);
	}
	else {
		$auction['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $auction['start_time']);
		$auction['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $auction['end_time']);
	}

	$ext_info = unserialize($auction['ext_info']);
	$auction = array_merge($auction, $ext_info);
	$auction['formated_start_price'] = price_format($auction['start_price']);
	$auction['formated_end_price'] = price_format($auction['end_price']);
	$auction['formated_amplitude'] = price_format($auction['amplitude']);
	$auction['formated_deposit'] = price_format($auction['deposit']);
	$sql = 'SELECT COUNT(DISTINCT bid_user) FROM ' . $GLOBALS['ecs']->table('auction_log') . (' WHERE act_id = \'' . $act_id . '\'');
	$auction['bid_user_count'] = $GLOBALS['db']->getOne($sql);

	if (0 < $auction['bid_user_count']) {
		$sql = 'SELECT a.*, u.user_name ' . 'FROM ' . $GLOBALS['ecs']->table('auction_log') . ' AS a, ' . $GLOBALS['ecs']->table('users') . ' AS u ' . 'WHERE a.bid_user = u.user_id ' . ('AND act_id = \'' . $act_id . '\' ') . 'ORDER BY a.log_id DESC';
		$row = $GLOBALS['db']->getRow($sql);
		$row['formated_bid_price'] = price_format($row['bid_price'], false);
		$row['bid_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['bid_time']);
		$auction['last_bid'] = $row;
	}
	else {
		$auction['bid_user_count'] = 0;
	}

	if (1 < $auction['status_no']) {
		$sql = 'SELECT COUNT(*)' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE extension_code = \'auction\'' . (' AND extension_id = \'' . $act_id . '\'') . ' AND order_status ' . db_create_in(array(OS_CONFIRMED, OS_UNCONFIRMED));
		$auction['order_count'] = $GLOBALS['db']->getOne($sql);
	}
	else {
		$auction['order_count'] = 0;
	}

	$auction['current_price'] = isset($auction['last_bid']) ? $auction['last_bid']['bid_price'] : $auction['start_price'];
	$auction['current_price_int'] = intval($auction['current_price']);
	$auction['formated_current_price'] = price_format($auction['current_price'], false);
	return $auction;
}

function auction_log($act_id, $type = 0)
{
	if ($type == 1) {
		$sql = 'SELECT count(*) ,u.user_id ' . 'FROM ' . $GLOBALS['ecs']->table('auction_log') . ' AS a,' . $GLOBALS['ecs']->table('users') . ' AS u ' . 'WHERE a.bid_user = u.user_id ' . ('AND act_id = \'' . $act_id . '\' ');
		$log = $GLOBALS['db']->getOne($sql);
	}
	else {
		$log = array();
		$sql = 'SELECT a.*, u.user_name ,u.user_id ' . 'FROM ' . $GLOBALS['ecs']->table('auction_log') . ' AS a,' . $GLOBALS['ecs']->table('users') . ' AS u ' . 'WHERE a.bid_user = u.user_id ' . ('AND act_id = \'' . $act_id . '\' ') . 'ORDER BY a.log_id DESC';
		$res = $GLOBALS['db']->query($sql);

		foreach ($res as $row) {
			$row['bid_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['bid_time']);
			$row['formated_bid_price'] = price_format($row['bid_price'], false);
			$log[] = $row;
		}
	}

	return $log;
}

function auction_status($auction)
{
	$now = gmtime();

	if ($auction['is_finished'] == 0) {
		if ($now < $auction['start_time']) {
			return PRE_START;
		}
		else if ($auction['end_time'] < $now) {
			return FINISHED;
		}
		else {
			return UNDER_WAY;
		}
	}
	else if ($auction['is_finished'] == 1) {
		return FINISHED;
	}
	else {
		return SETTLED;
	}
}

function goods_info($goods_id, $warehouse_id, $area_id, $select = array(), $attr_id = '')
{
	$leftJoin = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$sql = 'SELECT g.*, b.brand_name, c.measure_unit, ' . 'IF(g.model_inventory < 1, g.goods_number, IF(g.model_inventory < 2, wg.region_number, wag.region_number)) as goods_number ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON g.brand_id = b.brand_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('category') . ' AS c ON g.cat_id = c.cat_id ' . $leftJoin . ('WHERE g.goods_id = \'' . $goods_id . '\'');
	$row = $GLOBALS['db']->getRow($sql);

	if (!empty($row)) {
		if ($GLOBALS['_CFG']['add_shop_price'] == 1) {
			$add_tocart = 1;
		}
		else {
			$add_tocart = 0;
		}

		$row['goods_price'] = get_final_price($row['goods_id'], $row['goods_number'], true, $attr_id, $warehouse_id, $area_id, 0, 0, $add_tocart);
		$row['goods_weight'] = 0 < intval($row['goods_weight']) ? $row['goods_weight'] . $GLOBALS['_LANG']['kilogram'] : $row['goods_weight'] * 1000 . $GLOBALS['_LANG']['gram'];
		$row['goods_img'] = get_image_path($row['goods_img']);

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();

			if ($row['goods_desc']) {
				$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $row['goods_desc']);
				$row['goods_desc'] = $desc_preg['goods_desc'];
			}
		}

		$row['rz_shopName'] = get_shop_name($row['user_id'], 1);
		$row['store_url'] = url('store/index/shop_info', array('id' => $row['user_id']));
		$row['shopinfo'] = get_shop_name($row['user_id'], 2);
		$row['shopinfo']['brand_thumb'] = str_replace(array('../'), '', $row['shopinfo']['brand_thumb']);
		$row['shopinfo']['logo_thumb'] = get_image_path(str_replace(array('../'), '', $row['shopinfo']['logo_thumb']));
		$row['shopinfo']['shop_logo'] = get_image_path(str_replace(array('../'), '', $row['shopinfo']['shop_logo']));
		$basic_info = get_seller_shopinfo($row['user_id']);
		$row['province'] = $basic_info['province'];
		$row['city'] = $basic_info['city'];

		if ($basic_info['kf_qq']) {
			$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
			$kf_qq = explode('|', $kf_qq[0]);
			$row['kf_qq'] = $kf_qq[1];
		}
		else {
			$row['kf_qq'] = '';
		}

		if ($basic_info['kf_ww']) {
			$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
			$kf_ww = explode('|', $kf_ww[0]);
			$row['kf_ww'] = $kf_ww[1];
		}
		else {
			$row['kf_ww'] = '';
		}

		$row['kf_type'] = $basic_info['kf_type'];
		$row['shop_name'] = $basic_info['shop_name'];

		if ($row['goods_product_tag']) {
			$impression_list = !empty($row['goods_product_tag']) ? explode(',', $row['goods_product_tag']) : '';

			foreach ($impression_list as $kk => $vv) {
				$tag[$kk]['txt'] = $vv;
				$tag[$kk]['num'] = comment_goodstag_num($row['goods_id'], $vv);
			}

			$row['impression_list'] = $tag;
		}
	}

	return $row;
}

function favourable_info($act_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('favourable_activity') . (' WHERE act_id = \'' . $act_id . '\' AND review_status = 3');
	$row = $GLOBALS['db']->getRow($sql);

	if (!empty($row)) {
		$row['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['start_time']);
		$row['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['end_time']);
		$row['formated_min_amount'] = price_format($row['min_amount']);
		$row['formated_max_amount'] = price_format($row['max_amount']);
		$row['gift'] = unserialize($row['gift']);

		if ($row['act_type'] == FAT_GOODS) {
			$row['act_type_ext'] = round($row['act_type_ext']);
		}
	}

	return $row;
}

function wholesale_info($act_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('wholesale') . (' WHERE act_id = \'' . $act_id . '\'');
	$row = $GLOBALS['db']->getRow($sql);

	if (!empty($row)) {
		$row['price_list'] = unserialize($row['prices']);
	}

	return $row;
}

function add_style($goods_name, $style)
{
	$goods_style_name = $goods_name;
	$arr = explode('+', $style);
	$font_color = !empty($arr[0]) ? $arr[0] : '';
	$font_style = !empty($arr[1]) ? $arr[1] : '';

	if ($font_color != '') {
		$goods_style_name = '<font style="color:' . $font_color . '; font-size:inherit;">' . $goods_style_name . '</font>';
	}

	if ($font_style != '') {
		$goods_style_name = '<' . $font_style . '>' . $goods_style_name . '</' . $font_style . '>';
	}

	return $goods_style_name;
}

function get_goods_attr($goods_id)
{
	$attr_list = array();
	$sql = 'SELECT a.attr_id, a.attr_name ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g, ' . $GLOBALS['ecs']->table('attribute') . ' AS a ' . ('WHERE g.goods_id = \'' . $goods_id . '\' ') . 'AND g.goods_type = a.cat_id ' . 'AND a.attr_type = 1';
	$attr_id_list = $GLOBALS['db']->getCol($sql);
	$res = $GLOBALS['db']->query($sql);

	foreach ($res as $attr) {
		if (defined('ECS_ADMIN')) {
			$attr['goods_attr_list'] = array($GLOBALS['_LANG']['select_please']);
		}
		else {
			$attr['goods_attr_list'] = array();
		}

		$attr_list[$attr['attr_id']] = $attr;
	}

	$sql = 'SELECT attr_id, goods_attr_id, attr_value ' . 'FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_id = \'' . $goods_id . '\' ') . 'AND attr_id ' . db_create_in($attr_id_list);
	$res = $GLOBALS['db']->query($sql);

	foreach ($res as $goods_attr) {
		$attr_list[$goods_attr['attr_id']]['goods_attr_list'][$goods_attr['goods_attr_id']] = $goods_attr['attr_value'];
	}

	return $attr_list;
}

function get_goods_fittings($goods_list = array(), $warehouse_id = 0, $area_id = 0, $rev = '', $type = 0, $goods_equal = array())
{
	$fitts_goodsList = '';

	if (0 < count($goods_equal)) {
		$fitts_goodsList = implode(',', $goods_equal);
		$fitts_goodsList = ' and cc.goods_id in(' . $fitts_goodsList . ') ';
	}

	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' cc.user_id = \'' . $_SESSION['user_id'] . '\' ';
		$sess = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$sess_id = ' cc.session_id = \'' . real_cart_mac_ip() . '\' ';
		$sess = ' session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	$temp_index = 1;
	$arr = array();
	$where = '';
	$goods_attr_id = '';
	$leftJoin = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($type == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('cart_combo') . ' as cc on g.goods_id = cc.goods_id ';
		$where .= ' and cc.group_id = \'' . $rev . '\' and ' . $sess_id;
		$goods_attr_id = ' cc.goods_attr_id, cc.group_id as cc_group_id, ';
	}
	else if ($type == 2) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('cart_combo') . ' as cc on g.goods_id = cc.goods_id and ' . $sess_id;
		$where .= ' and gg.group_id = \'' . $rev . '\'';
	}

	$sql = 'SELECT gg.parent_id, ggg.goods_name AS parent_name, gg.group_id, gg.goods_id, gg.goods_price, g.comments_number,g.sales_volume,g.goods_name, g.goods_thumb, g.goods_img, g.market_price, ' . $goods_attr_id . ' IF(g.model_inventory < 1, g.goods_number, IF(g.model_inventory < 2, wg.region_number, wag.region_number)) as goods_number,' . 'IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\') AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, g.goods_type ' . 'FROM ' . $GLOBALS['ecs']->table('group_goods') . ' AS gg ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . 'AS g ON g.goods_id = gg.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = gg.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS ggg ON ggg.goods_id = gg.parent_id ' . $leftJoin . 'WHERE gg.parent_id ' . db_create_in($goods_list) . ' AND g.is_delete = 0 AND g.is_on_sale = 1 ' . $where . $fitts_goodsList . 'GROUP BY gg.goods_id ORDER BY gg.parent_id, gg.goods_id';
	$res = $GLOBALS['db']->query($sql);

	foreach ($res as $row) {
		$arr[$temp_index]['parent_id'] = $row['parent_id'];

		if (0 < $row['market_price']) {
			$discount_arr = get_discount($row);
		}

		$arr[$temp_index]['zhekou'] = $discount_arr['discount'];
		$arr[$temp_index]['jiesheng'] = $discount_arr['jiesheng'];
		$arr[$temp_index]['parent_name'] = $row['parent_name'];
		$arr[$temp_index]['parent_short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['parent_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['parent_name'];
		$arr[$temp_index]['goods_id'] = $row['goods_id'];
		$arr[$temp_index]['goods_name'] = $row['goods_name'];
		$arr[$temp_index]['comments_number'] = $row['comments_number'];
		$arr[$temp_index]['sales_volume'] = $row['sales_volume'];
		$arr[$temp_index]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$arr[$temp_index]['fittings_price'] = price_format($row['goods_price']);
		$arr[$temp_index]['shop_price'] = price_format($row['shop_price']);
		$arr[$temp_index]['spare_price'] = price_format($row['shop_price'] - $row['goods_price']);
		$arr[$temp_index]['market_price'] = $row['market_price'];
		$minMax_price = get_goods_minMax_price($row['goods_id'], $warehouse_id, $area_id, $row['goods_price'], $row['market_price']);
		$arr[$temp_index]['fittings_minPrice'] = $minMax_price['goods_min'];
		$arr[$temp_index]['fittings_maxPrice'] = $minMax_price['goods_max'];
		$arr[$temp_index]['market_minPrice'] = $minMax_price['market_min'];
		$arr[$temp_index]['market_maxPrice'] = $minMax_price['market_max'];

		if (!empty($row['goods_attr_id'])) {
			$prod_attr = explode(',', $row['goods_attr_id']);
		}
		else {
			$prod_attr = array();
		}

		$warehouse_area = array('warehouse_id' => $warehouse_id, 'area_id' => $area_id);
		$attr_price = spec_price($prod_attr, $row['goods_id'], $warehouse_area);
		$arr[$temp_index]['attr_price'] = $attr_price;
		$arr[$temp_index]['shop_price_ori'] = $row['shop_price'];
		$arr[$temp_index]['fittings_price_ori'] = $row['goods_price'];
		$arr[$temp_index]['spare_price_ori'] = $row['shop_price'] - $row['goods_price'];
		$arr[$temp_index]['group_id'] = $row['group_id'];

		if ($type == 2) {
			$cc_rev = 'm_goods_' . $rev . '_' . $row['parent_id'];
			$sql = 'select cc.img_flie from ' . $GLOBALS['ecs']->table('cart_combo') . ' as cc' . ' where cc.goods_id = \'' . $row['goods_id'] . '\'' . (' AND cc.group_id = \'' . $cc_rev . '\' and ') . $sess_id;
		}
		else {
			$sql = 'select cc.img_flie from ' . $GLOBALS['ecs']->table('cart_combo') . ' as cc' . ' where cc.goods_id = \'' . $row['goods_id'] . '\'' . (' AND cc.group_id = \'' . $rev . '\' and ') . $sess_id;
		}

		$img_flie = $GLOBALS['db']->getOne($sql);
		$arr[$temp_index]['img_flie'] = $img_flie;

		if (!empty($arr[$temp_index]['img_flie'])) {
			$arr[$temp_index]['goods_thumb'] = $arr[$temp_index]['img_flie'];
		}
		else {
			$arr[$temp_index]['goods_thumb'] = get_image_path($row['goods_thumb']);
		}

		$arr[$temp_index]['goods_img'] = get_image_path($row['goods_img']);
		$arr[$temp_index]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$arr[$temp_index]['attr_id'] = !empty($row['goods_attr_id']) ? str_replace(',', '|', $row['goods_attr_id']) : '';
		$arr[$temp_index]['goods_number'] = get_goods_fittings_gnumber($row['goods_number'], $row['goods_id'], $warehouse_id, $area_id);

		if (empty($row['goods_attr_id'])) {
			$arr[$temp_index]['goods_number'] = get_goods_fittings_gnumber($row['goods_number'], $row['goods_id'], $warehouse_id, $area_id);
		}
		else {
			$goods = get_goods_info($row['goods_id'], $warehouse_id, $area_id);
			$products = get_warehouse_id_attr_number($row['goods_id'], $row['goods_attr_id'], $row['goods_name'], $warehouse_id, $area_id);
			$attr_number = $products['product_number'];
			$attr_number = !empty($attr_number) ? $attr_number : 0;

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

			$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $row['goods_id'] . '\'' . $type_files . ' LIMIT 0, 1';
			$prod = $GLOBALS['db']->getRow($sql);

			if (empty($prod)) {
				$attr_number = $goods['goods_number'];
			}

			$arr[$temp_index]['goods_number'] = $attr_number;
		}

		$arr[$temp_index]['properties'] = get_goods_properties($row['goods_id'], $warehouse_id, $area_id, $row['goods_attr_id']);

		if ($type == 2) {
			$group_id = 'm_goods_' . $rev . '_' . $row['parent_id'];
			$sql = 'select rec_id from ' . $GLOBALS['ecs']->table('cart_combo') . ' where goods_id = \'' . $row['goods_id'] . ('\' and group_id = \'' . $group_id . '\' and ') . $sess;
			$rec_id = $GLOBALS['db']->getOne($sql);
			$group_cnt = 'm_goods_' . $rev . '=' . $row['parent_id'];
			$arr[$temp_index]['group_top'] = $row['goods_id'] . '|' . $warehouse_id . '|' . $area_id . '|' . $group_cnt;

			if (0 < $rec_id) {
				$arr[$temp_index]['selected'] = 1;
			}
			else {
				$arr[$temp_index]['selected'] = 0;
			}
		}

		$temp_index++;
	}

	return $arr;
}

function get_goods_fittings_gnumber($goods_number, $goods_id, $warehouse_id, $area_id)
{
	$leftJoin = '';

	if ($model_attr == 1) {
		$table_products = 'products_warehouse';
		$type_files = ' AND warehouse_id = \'' . $warehouse_id . '\'';
	}
	else if ($model_attr == 2) {
		$table_products = 'products_area';
		$type_files = ' AND area_id = \'' . $area_id . '\'';
	}
	else {
		$table_products = 'products';
		$type_files = '';
	}

	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . (' WHERE goods_id = \'' . $goods_id . '\' ') . $type_files;
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	if ($res) {
		$arr['product_number'] = 0;

		foreach ($res as $key => $row) {
			$arr[$key] = $row;
			$arr['product_number'] += $row['product_number'];
		}
	}
	else {
		$arr['product_number'] = $goods_number;
	}

	return $arr['product_number'];
}

function get_goods_fittings_info($goods_id = 0, $warehouse_id = 0, $area_id = 0, $rev = '', $type = 0)
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' cc.user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$sess_id = ' cc.session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	$temp_index = 0;
	$arr = array();
	$where = '';
	$select = '';
	$leftJoin = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($type == 0) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('cart_combo') . ' as cc on g.goods_id = cc.goods_id ';
		$where .= ' and cc.group_id = \'' . $rev . '\' and ' . $sess_id;
		$select = 'cc.goods_attr_id, ';
	}

	$sql = 'SELECT g.goods_id,g.goods_number,g.sales_volume,g.goods_name, g.goods_thumb, g.goods_img, g.user_id, ' . 'g.promote_start_date, g.promote_end_date, ' . $select . ' g.market_price, ' . ' IF(g.model_inventory < 1, g.goods_number, IF(g.model_inventory < 2, wg.region_number, wag.region_number)) as goods_number,' . 'IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, g.goods_type ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . 'AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . $leftJoin . ('WHERE g.goods_id = \'' . $goods_id . '\' AND g.is_delete = 0 AND g.is_on_sale = 1 ') . $where . 'ORDER BY g.goods_id';
	$res = $GLOBALS['db']->query($sql);

	foreach ($res as $row) {
		$arr[$temp_index]['parent_id'] = $row['parent_id'];

		if (0 < $row['market_price']) {
			$discount_arr = get_discount($row);
		}

		$arr[$temp_index]['zhekou'] = $discount_arr['discount'];
		$arr[$temp_index]['jiesheng'] = $discount_arr['jiesheng'];
		$arr[$temp_index]['parent_name'] = $row['parent_name'];
		$arr[$temp_index]['parent_short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['parent_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['parent_name'];
		$arr[$temp_index]['goods_id'] = $row['goods_id'];
		$arr[$temp_index]['goods_name'] = $row['goods_name'];
		$arr[$temp_index]['comments_number'] = $row['comments_number'];
		$arr[$temp_index]['sales_volume'] = $row['sales_volume'];
		$arr[$temp_index]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$arr[$temp_index]['fittings_price'] = price_format($row['goods_price']);

		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$goods_price = 0 < $promote_price ? $promote_price : $row['shop_price'];
		$arr[$temp_index]['market_price'] = $row['market_price'];
		$arr[$temp_index]['shop_price'] = price_format($goods_price);
		$arr[$temp_index]['spare_price'] = price_format(0);
		$minMax_price = get_goods_minMax_price($row['goods_id'], $warehouse_id, $area_id, $goods_price, $row['market_price']);
		$arr[$temp_index]['fittings_minPrice'] = $minMax_price['goods_min'];
		$arr[$temp_index]['fittings_maxPrice'] = $minMax_price['goods_max'];
		$arr[$temp_index]['market_minPrice'] = $minMax_price['market_min'];
		$arr[$temp_index]['market_maxPrice'] = $minMax_price['market_max'];

		if (!empty($row['goods_attr_id'])) {
			$prod_attr = explode(',', $row['goods_attr_id']);
		}
		else {
			$prod_attr = array();
		}

		$warehouse_area = array('warehouse_id' => $warehouse_id, 'area_id' => $area_id);
		$attr_price = spec_price($prod_attr, $row['goods_id'], $warehouse_area);
		$arr[$temp_index]['attr_price'] = $attr_price;
		$arr[$temp_index]['shop_price_ori'] = $goods_price;
		$arr[$temp_index]['fittings_price_ori'] = 0;
		$arr[$temp_index]['spare_price_ori'] = 0;
		$arr[$temp_index]['group_id'] = $row['group_id'];
		$sql = 'select cc.img_flie from ' . $GLOBALS['ecs']->table('cart_combo') . ' as cc' . ' where cc.goods_id = \'' . $row['goods_id'] . '\'' . (' AND cc.group_id = \'' . $rev . '\' and ') . $sess_id;
		$img_flie = $GLOBALS['db']->getOne($sql);
		$arr[$temp_index]['img_flie'] = $img_flie;

		if (!empty($arr[$temp_index]['img_flie'])) {
			$arr[$temp_index]['goods_thumb'] = $arr[$temp_index]['img_flie'];
		}
		else {
			$arr[$temp_index]['goods_thumb'] = get_image_path($row['goods_thumb']);
		}

		$arr[$temp_index]['goods_img'] = get_image_path($row['goods_img']);
		$arr[$temp_index]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$arr[$temp_index]['attr_id'] = str_replace(',', '|', $row['goods_attr_id']);
		$goods = get_goods_info($goods_id, $warehouse_id, $area_id);
		$products = get_warehouse_id_attr_number($goods_id, $row['goods_attr_id'], $row['goods_name'], $warehouse_id, $area_id);
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

		if (empty($prod)) {
			$attr_number = $goods['goods_number'];
		}

		$attr_number = !empty($attr_number) ? $attr_number : 0;
		$arr[$temp_index]['goods_number'] = $attr_number;
		$arr[$temp_index]['properties'] = get_goods_properties($goods_id, $warehouse_id, $area_id, $row['goods_attr_id']);
		$temp_index++;
	}

	return $arr;
}

function get_products_info($goods_id, $spec_goods_attr_id, $warehouse_id = 0, $area_id = 0, $store_id = 0)
{
	$model_attr = get_table_date('goods', 'goods_id = \'' . $goods_id . '\'', array('model_attr'), 2);
	$return_array = array();
	if (empty($spec_goods_attr_id) || !is_array($spec_goods_attr_id) || empty($goods_id)) {
		return $return_array;
	}

	$goods_attr_array = sort_goods_attr_id_array($spec_goods_attr_id);
	$where = '';
	if (isset($goods_attr_array['sort']) && $goods_attr_array['sort']) {
		foreach ($goods_attr_array['sort'] as $key => $val) {
			$where .= ' AND FIND_IN_SET(\'' . $val . '\', REPLACE(goods_attr, \'|\', \',\')) ';
		}

		if (0 < $store_id) {
			$table_products = 'store_products';
			$where = ' and store_id = \'' . $store_id . '\'';
		}
		else if ($model_attr == 1) {
			$table_products = 'products_warehouse';
			$type_files = ' AND warehouse_id = \'' . $warehouse_id . '\'';
		}
		else if ($model_attr == 2) {
			$table_products = 'products_area';
			$type_files = ' AND area_id = \'' . $area_id . '\'';
		}
		else {
			$table_products = 'products';
			$type_files = '';
		}

		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . (' WHERE goods_id = \'' . $goods_id . '\'') . $where . $type_files . ' LIMIT 1';
		$return_array = $GLOBALS['db']->getRow($sql);

		if (!empty($return_array)) {
			$return_array['product_number'] = get_jigon_products_stock($return_array);
		}
	}

	return $return_array;
}

function get_jigon_products_stock($product)
{
	$stock = $product['product_number'];

	if (0 < $product['cloud_product_id']) {
		$productIds = array($product['cloud_product_id']);
		$cloud = new \App\Services\Erp\JigonService();
		$res = $cloud->query($productIds);
		$cloud_prod = json_decode($res, true);
		if ($cloud_prod['code'] == 10000 && $cloud_prod['data']) {
			foreach ($cloud_prod['data'] as $k => $v) {
				if (in_array($v['productId'], $productIds)) {
					if ($v['hasTax'] == 1) {
						$stock = $v['taxNum'];
					}
					else {
						$stock = $v['noTaxNum'];
					}

					break;
				}
			}
		}
	}

	return $stock;
}

function get_parent_cat_child($cat_id = 0)
{
	if (0 < $cat_id) {
		$sql = 'SELECT cat_id FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE parent_id = \'' . $cat_id . '\' AND is_show = 1 AND is_top_show = 1 LIMIT 1');

		if ($GLOBALS['db']->getOne($sql)) {
			$sql = 'SELECT cat_id,cat_name,parent_id,is_show ' . 'FROM ' . $GLOBALS['ecs']->table('category') . ('WHERE parent_id = \'' . $cat_id . '\' AND is_show = 1 AND is_top_show = 1 ORDER BY sort_order ASC, cat_id ASC');
			$res = $GLOBALS['db']->getAll($sql);

			foreach ($res as $row) {
				$cat_arr[$row['cat_id']]['id'] = $row['cat_id'];
				$cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
				$cat_arr[$row['cat_id']]['url'] = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);

				if (isset($row['cat_id']) != NULL) {
					$cat_arr[$row['cat_id']]['cat_id'] = get_child_tree_top($row['cat_id']);
				}
			}
		}

		if (isset($cat_arr)) {
			return $cat_arr;
		}
	}
}

function get_child_tree_top($tree_id = 0)
{
	$three_arr = array();
	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE parent_id = \'' . $tree_id . '\' AND is_show = 1 ');
	if ($GLOBALS['db']->getOne($sql) || $tree_id == 0) {
		$child_sql = 'SELECT cat_id, cat_name, parent_id, is_show ' . 'FROM ' . $GLOBALS['ecs']->table('category') . ('WHERE parent_id = \'' . $tree_id . '\' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC');
		$res = $GLOBALS['db']->getAll($child_sql);

		foreach ($res as $row) {
			$three_arr[$row['cat_id']]['id'] = $row['cat_id'];
			$three_arr[$row['cat_id']]['name'] = $row['cat_name'];
			$three_arr[$row['cat_id']]['url'] = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
		}
	}

	return $three_arr;
}

function snatch_log($snatch_id)
{
	$sql = 'SELECT count(*) ' . 'FROM ' . $GLOBALS['ecs']->table('snatch_log') . ' AS s,' . $GLOBALS['ecs']->table('users') . ' AS u ' . 'WHERE s.user_id = u.user_id ' . ('AND snatch_id = \'' . $snatch_id . '\' ');
	$log = $GLOBALS['db']->getOne($sql);
	return $log;
}

function getStoreIdByGoodsId($goods_id)
{
	$sql = 'SELECT store_id FROM ' . $GLOBALS['ecs']->table('store_goods') . ' WHERE goods_id = ' . $goods_id;
	$res = $GLOBALS['db']->getRow($sql);
	return $res['store_id'];
}


?>
