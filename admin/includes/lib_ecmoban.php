<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_categories_tree_pro($cat_id = 0, $type = '')
{
	if (0 < $cat_id) {
		$sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE cat_id = \'' . $cat_id . '\' LIMIT 1';
		$parent_id = $GLOBALS['db']->getOne($sql);
	}
	else {
		$parent_id = 0;
	}

	$sql = 'SELECT cat_id FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE parent_id = \'' . $parent_id . '\' AND is_show = 1 LIMIT 1 ';
	if ($GLOBALS['db']->getOne($sql) || ($parent_id == 0)) {
		$sql = 'SELECT cat_id,cat_name ,parent_id,is_show, category_links ' . 'FROM ' . $GLOBALS['ecs']->table('category') . 'WHERE parent_id = \'' . $parent_id . '\' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC';
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $row) {
			$cat_id = $row['cat_id'];

			if ($row['parent_id'] == 0) {
				$cat_name = '';

				for ($i = 1; $i <= $GLOBALS['_CFG']['auction_ad']; $i++) {
					$cat_name .= '\'cat_tree_' . $row['cat_id'] . '_' . $i . '\',';
				}

				$cat_name = substr($cat_name, 0, -1);
				$cat_arr[$row['cat_id']]['ad_position'] = get_ad_posti_child($cat_name);
			}

			$children = get_children($cat_id);
			$cat = $GLOBALS['db']->getRow('SELECT cat_name, keywords, cat_desc, style, grade, filter_attr, parent_id FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE cat_id = \'' . $cat_id . '\' LIMIT 1');
			$sql = 'SELECT a.article_id, a.title, ac.cat_name, a.add_time, a.file_url, a.open_type FROM ' . $GLOBALS['ecs']->table('article_cat') . ' AS ac RIGHT JOIN ' . $GLOBALS['ecs']->table('article') . ' AS a ON a.cat_id=ac.cat_id AND a.is_open = 1 WHERE ac.cat_name=\'' . $row['cat_name'] . '\' ORDER BY a.article_type,a.article_id DESC LIMIT 4 ';
			$articles = $GLOBALS['db']->getAll($sql);

			foreach ($articles as $key => $val) {
				$articles[$key]['url'] = $val['open_type'] != 1 ? build_uri('article', array('aid' => $val['article_id']), $val['title']) : trim($val['file_url']);
			}

			$cat_keys = get_array_keys_cat($cat_id);
			$sql = 'SELECT b.brand_id, b.brand_name, b.brand_logo, COUNT(*) AS goods_num ' . 'FROM ' . $GLOBALS['ecs']->table('brand') . 'AS b ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.brand_id = b.brand_id AND g.user_id = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_cat') . ' AS gc ON g.goods_id = gc.goods_id ' . ' WHERE ' . $children . ' OR ' . 'gc.cat_id ' . db_create_in(array_unique(array_merge(array($cat_id), $cat_keys))) . ' AND b.is_show = 1 ' . 'GROUP BY b.brand_id HAVING goods_num > 0 ' . $where_having . ' ORDER BY b.sort_order, b.brand_id ASC';
			$brands_list = $GLOBALS['db']->getAll($sql);
			$sql = 'SELECT b.brand_id, b.brand_name, b.brand_logo, COUNT(*) AS goods_num, g.user_id ' . 'FROM ' . $GLOBALS['ecs']->table('brand') . 'AS b ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('link_brand') . 'AS lb ON lb.brand_id = b.brand_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_brand') . 'AS msb ON msb.bid = lb.bid AND msb.audit_status = 1 AND msb.is_show = 1 ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.brand_id = msb.bid AND g.user_id > 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_cat') . ' AS gc ON g.goods_id = gc.goods_id ' . ' WHERE ' . $children . ' OR ' . 'gc.cat_id ' . db_create_in(array_unique(array_merge(array($cat_id), $cat_keys))) . ' AND b.is_show = 1 ' . 'GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY b.sort_order, b.brand_id ASC';
			$msb_brands_list = $GLOBALS['db']->getAll($sql);

			if ($msb_brands_list) {
				$new_array = array_merge($brands_list, $msb_brands_list);
				$new = unique_arr($new_array);
				$brands_list = $new;
			}

			foreach ($brands_list as $key => $val) {
				$temp_key = $key;
				$brands_list[$temp_key]['brand_name'] = $val['brand_name'];
				$brands_list[$temp_key]['url'] = build_uri('category', array('cid' => $cat_id, 'bid' => $val['brand_id'], 'price_min' => $price_min, 'price_max' => $price_max, 'filter_attr' => $filter_attr_str), $cat['cat_name']);

				if ($brand == $brands[$key]['brand_id']) {
					$brands_list[$temp_key]['selected'] = 1;
				}
				else {
					$brands_list[$temp_key]['selected'] = 0;
				}
			}

			$cat_arr[$row['cat_id']]['brands'] = $brands_list;
			$cat_arr[$row['cat_id']]['articles'] = $articles;

			if ($row['is_show']) {
				if (($row['parent_id'] == 0) && !empty($row['category_links'])) {
					if (empty($type)) {
						$cat_name_arr = explode('、', $row['cat_name']);

						if (!empty($cat_name_arr)) {
							$category_links_arr = explode("\r\n", $row['category_links']);
						}

						$cat_name_str = '';

						foreach ($cat_name_arr as $cat_name_key => $cat_name_val) {
							$link_str = $category_links_arr[$cat_name_key];
							$cat_name_str .= '<a href="' . $link_str . '" target="_blank">' . $cat_name_val;

							if (count($cat_name_arr) == ($cat_name_key + 1)) {
								$cat_name_str .= '</a>';
							}
							else {
								$cat_name_str .= '</a>、';
							}
						}

						$cat_arr[$row['cat_id']]['name'] = $cat_name_str;
						$cat_arr[$row['cat_id']]['category_link'] = 1;
						$cat_arr[$row['cat_id']]['oldname'] = $row['cat_name'];
					}
					else {
						$cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
						$cat_arr[$row['cat_id']]['oldname'] = $row['cat_name'];
					}
				}
				else {
					$cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
				}

				$cat_arr[$row['cat_id']]['id'] = $row['cat_id'];
				$cat_arr[$row['cat_id']]['url'] = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);

				if (isset($row['cat_id']) != NULL) {
					$cat_arr[$row['cat_id']]['cat_id'] = get_child_tree_pro($row['cat_id']);
				}
			}
		}
	}

	if (isset($cat_arr)) {
		return $cat_arr;
	}
}

function get_child_tree_pro($tree_id = 0, $level = 0, $table = 'category', $getrid = 0, $user_id = 0)
{
	$where = '';
	$select = '';

	if ($table == 'merchants_category') {
		$select = ', user_id ';

		if ($user_id) {
			$where .= ' AND user_id = \'' . $user_id . '\'';
		}
	}

	$three_arr = array();
	$sql = 'SELECT cat_id FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE parent_id = \'' . $tree_id . '\' ' . $where . ' AND is_show = 1 LIMIT 1';
	if ($GLOBALS['db']->getOne($sql) || ($tree_id == 0)) {
		$child_sql = 'SELECT cat_id, cat_name, parent_id, is_show ' . $select . 'FROM ' . $GLOBALS['ecs']->table($table) . 'WHERE parent_id = \'' . $tree_id . '\' AND is_show = 1 ' . $where . ' ORDER BY sort_order ASC, cat_id ASC';
		$res = $GLOBALS['db']->getAll($child_sql);

		if ($res) {
			foreach ($res as $row) {
				$three_arr[$row['cat_id']]['id'] = $row['cat_id'];

				if ($getrid == 0) {
					$three_arr[$row['cat_id']]['name'] = htmlspecialchars(addslashes(str_replace("\r\n", '', $row['cat_name'])), ENT_QUOTES);

					if ($table == 'merchants_category') {
						$three_arr[$row['cat_id']]['url'] = build_uri('merchants_store', array('cid' => $row['cat_id'], 'urid' => $row['user_id']), $row['cat_name']);
					}
					else {
						$three_arr[$row['cat_id']]['url'] = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
					}

					if ($table == 'merchants_category') {
						$three_arr[$row['cat_id']]['ru_id'] = $row['user_id'];
						$three_arr[$row['cat_id']]['seller_name'] = get_shop_name($row['user_id'], 1);
					}

					if ($row['parent_id'] != 0) {
						$three_arr[$row['cat_id']]['level'] = $level + 1;
					}
					else {
						$three_arr[$row['cat_id']]['level'] = $level;
					}

					$three_arr[$row['cat_id']]['select'] = str_repeat('&nbsp;', $three_arr[$row['cat_id']]['level'] * 4);
				}

				if (isset($row['cat_id']) != NULL) {
					if ($row['parent_id'] != 0) {
						$three_arr[$row['cat_id']]['cat_id'] = get_child_tree_pro($row['cat_id'], $level + 1, $table, $getrid);
					}
					else {
						$three_arr[$row['cat_id']]['cat_id'] = get_child_tree_pro($row['cat_id'], $level, $table, $getrid);
					}
				}

				if (!$three_arr[$row['cat_id']]['cat_id'] && $getrid) {
					unset($three_arr[$row['cat_id']]['cat_id']);
				}
			}
		}
	}

	return $three_arr;
}

function get_discount($goods_id, $warehouse_id = 0, $area_id = 0)
{
	$leftJoin = ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ';
	$sql = 'SELECT g.market_price, g.promote_start_date, g.promote_end_date, ' . 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price' . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . ' WHERE g.goods_id = ' . $goods_id . ' ';
	$row = $GLOBALS['db']->getRow($sql);

	if (0 < $row['promote_price']) {
		$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
	}
	else {
		$promote_price = 0;
	}

	$price = $row['market_price'];

	if (0 < $promote_price) {
		$nowprice = $row['promote_price'];
	}
	else {
		$nowprice = $row['shop_price'];
	}

	$jiesheng = $price - $nowprice;
	$arr['jiesheng'] = $jiesheng;

	if (0 < $nowprice) {
		$arr['discount'] = round(($nowprice / $price) * 10, 1);
	}
	else {
		$arr['discount'] = 0;
	}

	if ($arr['discount'] <= 0) {
		$arr['discount'] = 0;
	}

	return $arr;
}

function get_goods_attr_info_new($arr, $type = 'pice', $warehouse_id = 0, $area_id = 0)
{
	$attr = '';

	if (!empty($arr)) {
		$fmt = "%s:%s[%s] \n";
		$leftJoin = '';
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('goods') . ' as g on g.goods_id = ga.goods_id';
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_attr') . ' as wap on ga.goods_id = wap.goods_id and wap.warehouse_id = \'' . $warehouse_id . '\' and ga.goods_attr_id = wap.goods_attr_id ';
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_attr') . ' as wa on ga.goods_id = wa.goods_id and wa.area_id = \'' . $area_id . '\' and ga.goods_attr_id = wa.goods_attr_id ';
		$sql = 'SELECT ga.goods_attr_id, a.attr_name, ga.attr_value, ' . ' IF(g.model_attr < 1, ga.attr_price, IF(g.model_attr < 2, wap.attr_price, wa.attr_price)) as attr_price ' . 'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS ga ' . $leftJoin . ' left join ' . $GLOBALS['ecs']->table('attribute') . ' AS a ' . 'on a.attr_id = ga.attr_id ' . 'WHERE ' . db_create_in($arr, 'ga.goods_attr_id') . ' ORDER BY a.sort_order, a.attr_id, ga.goods_attr_id';
		$res = $GLOBALS['db']->query($sql);

		while ($row = $GLOBALS['db']->fetchRow($res)) {
			$attr_price = round(floatval($row['attr_price']), 2);
			$attr_price = price_format($attr_price, false);
			$attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], $attr_price);
		}

		$attr = str_replace('[0]', '', $attr);
	}

	return $attr;
}

function comment_percent($goods_id)
{
	$sql = 'SELECT COUNT(*) AS haoping FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value = \'' . $goods_id . '\' AND comment_type=0 AND status = 1 AND parent_id = 0 AND (comment_rank = 4 OR comment_rank = 5)';
	$haoping_count = $GLOBALS['db']->getOne($sql);
	$sql = 'SELECT COUNT(*) AS zhongping FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value = \'' . $goods_id . '\' AND comment_type=0 AND status = 1 AND parent_id = 0 AND (comment_rank = 2 OR comment_rank = 3)';
	$zhongping_count = $GLOBALS['db']->getOne($sql);
	$sql = 'SELECT COUNT(*) AS chaping FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value = \'' . $goods_id . '\' AND comment_type=0 AND status = 1 AND parent_id = 0 AND comment_rank = 1';
	$chaping_count = $GLOBALS['db']->getOne($sql);
	$sql = 'SELECT COUNT(*) AS comment_count FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value = \'' . $goods_id . '\' AND comment_type=0 AND status = 1 AND parent_id = 0';
	$comment_count = $GLOBALS['db']->getOne($sql);
	$arr['haoping_percent'] = substr(number_format(($haoping_count / $comment_count) * 100, 2, '.', ''), 0, -1);
	$arr['zhongping_percent'] = substr(number_format(($zhongping_count / $comment_count) * 100, 2, '.', ''), 0, -1);
	$arr['chaping_percent'] = substr(number_format(($chaping_count / $comment_count) * 100, 2, '.', ''), 0, -1);

	if ($comment_count == 0) {
		$arr['haoping_percent'] = 100;
	}

	foreach ($arr as $key => $val) {
		if ($val == 0) {
			$arr[$key] = 0;
		}
	}

	return $arr;
}

function get_month_day_start_end_goods($group_buy_id, $first_month_day = 0, $last_month_day = 0)
{
	$where = '';
	$where .= 'AND (order_status = \'' . OS_CONFIRMED . '\' OR order_status = \'' . OS_UNCONFIRMED . '\') AND o.extension_code = \'group_buy\' ';
	$sql = 'select gac.*, g.*, count(gac.act_id) as valid_goods, SUM(og.goods_number) AS v_goods_number from ' . $GLOBALS['ecs']->table('goods_activity') . ' as gac, ' . $GLOBALS['ecs']->table('goods') . ' as g, ' . $GLOBALS['ecs']->table('order_goods') . ' as og,  ' . $GLOBALS['ecs']->table('order_info') . ' as o ' . 'where gac.goods_id = og.goods_id and og.order_id = o.order_id and gac.goods_id = g.goods_id AND gac.review_status = 3 ' . ' and o.add_time >= ' . $first_month_day . ' and o.add_time <= ' . $last_month_day . ' AND gac.act_id <> \'' . $group_buy_id . '\' ' . $where . ' group by gac.act_id order by v_goods_number desc limit 0,10';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[] = $row;
		$ext_info = unserialize($row['ext_info']);
		$arr[$key]['ext_info'] = $ext_info;
		$price_ladder = $arr[$key]['ext_info']['price_ladder'];
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

		$arr[$key]['price_ladder'] = $price_ladder;
		$cur_price = $price_ladder[0]['price'];

		foreach ($price_ladder as $amount_price) {
			if ($amount_price['amount'] <= $cur_amount) {
				$cur_price = $amount_price['price'];
			}
			else {
				break;
			}
		}

		$arr[$key]['cur_price'] = price_format($cur_price, false);
		$arr[$key]['market_price'] = price_format($row['market_price'], false);
		$price = $row['market_price'];
		$nowprice = $cur_price;
		$arr[$key]['jiesheng'] = price_format($price - $nowprice, false);

		if (0 < $nowprice) {
			$arr[$key]['zhekou'] = round(10 / $price / $nowprice, 1);
		}
		else {
			$arr[$key]['zhekou'] = 0;
		}

		$arr[$key]['valid_goods'] = $row['v_goods_number'];
		$arr[$key]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
	}

	return $arr;
}

function ments_count_all($goods_id, $type = 'comment_rank', $count_type = 0)
{
	$count = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value = \'' . $goods_id . '\' AND status = 1 AND parent_id = 0 and ' . $type . ' in(1,2,3,4,5)';
	$res = $GLOBALS['db']->getOne($count);

	if ($res == 0) {
		if ($count_type == 0) {
			return $res = 1;
		}
		else {
			return $res = 0;
		}
	}
	else {
		return $res;
	}
}

function ments_count_rank_num($goods_id, $num, $type = 'comment_rank')
{
	$count = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value = \'' . $goods_id . '\' AND status = 1 AND parent_id = 0 and ' . $type . ' = \'' . $num . '\'';
	$res = $GLOBALS['db']->getOne($count);
	return $res;
}

function get_conments_stars($all = NULL, $one = NULL, $two = NULL, $three = NULL, $four = NULL, $five = NULL, $baseline = '')
{
	$num = 5;
	$one_num = 1;
	$two_num = 2;
	$three_num = 3;
	$four_num = 4;
	$five_num = 5;
	$allNmu = $all * 5;
	$oneAll = $one * $one_num;
	$twoAll = $two * $two_num;
	$threeAll = $three * $three_num;
	$fourAll = $four * $four_num;
	$fiveAll = $five * $five_num;
	$allStars = $oneAll + $twoAll + $threeAll + $fourAll + $fiveAll;
	$badReview = $one / $all;
	$middleReview = ($two + $three) / $all;
	$goodReview = ($four + $five) / $all;
	$badmen = $one;
	$middlemen = $two + $three;
	$goodmen = $four + $five;
	$allmen = $one + $two + $three + $four + $five;
	$percentage = sprintf('%.2f', ($allStars / $allNmu) * 100);
	$arr = array('score' => sprintf('%.2f', round($percentage / 20, 2)), 'badReview' => round($badReview, 2) * 100, 'middlReview' => round($middleReview, 2) * 100, 'goodReview' => round($goodReview, 2) * 100, 'allReview' => $percentage, 'badmen' => $badmen, 'middlemen' => $middlemen, 'goodmen' => $goodmen, 'allmen' => $allmen);
	if ((1 <= $percentage) && ($percentage < 40)) {
		$arr['stars'] = 1;
	}
	else {
		if ((40 <= $percentage) && ($percentage < 60)) {
			$arr['stars'] = 2;
		}
		else {
			if ((60 <= $percentage) && ($percentage < 80)) {
				$arr['stars'] = 3;
			}
			else {
				if ((80 <= $percentage) && ($percentage < 100)) {
					$arr['stars'] = 4;
				}
				else if ($percentage == 100) {
					$arr['score'] = 5;
					$arr['stars'] = 5;
					$arr['badReview'] = 0;
					$arr['middlReview'] = 0;
					$arr['goodReview'] = 100;
					$arr['allReview'] = 100;
					return $arr;
				}
				else {
					$arr = array('score' => 5, 'stars' => 5, 'badReview' => 0, 'middlReview' => 0, 'goodReview' => 100, 'allReview' => 100, 'allmen' => 0, 'badmen' => 0, 'middlemen' => 0, 'goodmen' => 0);
				}
			}
		}
	}

	$review = $arr['badReview'] + $arr['middlReview'] + $arr['goodReview'];

	if (100 < $review) {
		$review = $review - 100;
		$maxReview = max($arr['badReview'], $arr['middlReview'], $arr['goodReview']);

		if ($maxReview == $arr['badReview']) {
			$arr['badReview'] = $arr['badReview'] - $review;
		}
		else if ($maxReview == $arr['middlReview']) {
			$arr['middlReview'] = $arr['middlReview'] - $review;
		}
		else if ($maxReview == $arr['goodReview']) {
			$arr['goodReview'] = $arr['goodReview'] - $review;
		}
	}

	$arr['left'] = $arr['stars'] * 18;

	if ($baseline) {
		$sql = 'SELECT ' . $baseline . ' FROM ' . $GLOBALS['ecs']->table('comment_baseline') . 'WHERE 1 LIMIT 1';
		$res = $GLOBALS['db']->getRow($sql);
		$arr['up_down'] = $arr['goodReview'] - $res[$baseline];

		if ($res[$baseline] < $arr['up_down']) {
			$arr['is_status'] = 1;
		}
		else if ($arr['up_down'] < $res[$baseline]) {
			$arr['is_status'] = 0;
			$arr['up_down'] = abs($arr['up_down']);
		}
		else {
			$arr['is_status'] = 2;
		}
	}

	return $arr;
}

function get_comments_percent($goods_id)
{
	$arr = array('score' => 5, 'stars' => 5, 'badReview' => 0, 'middlReview' => 0, 'goodReview' => 100, 'allReview' => 100, 'allmen' => 0, 'badmen' => 0, 'middlemen' => 0, 'goodmen' => 0);
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value = \'' . $goods_id . '\' AND status = 1 AND parent_id = 0';
	$arr['allmen'] = $GLOBALS['db']->getOne($sql);

	if ($arr['allmen'] == 0) {
		return $arr;
	}
	else {
		$mc_one = ments_count_rank_num($goods_id, 1);
		$mc_two = ments_count_rank_num($goods_id, 2);
		$mc_three = ments_count_rank_num($goods_id, 3);
		$mc_four = ments_count_rank_num($goods_id, 4);
		$mc_five = ments_count_rank_num($goods_id, 5);
		$arr['goodmen'] = $mc_four + $mc_five;
		$arr['middlemen'] = $mc_two + $mc_three;
		$arr['badmen'] = $mc_one;
		$arr['goodReview'] = round(($arr['goodmen'] / $arr['allmen']) * 100, 1);
		$arr['middlReview'] = round(($arr['middlemen'] / $arr['allmen']) * 100, 1);
		$arr['badReview'] = round(($arr['badmen'] / $arr['allmen']) * 100, 1);
		return $arr;
	}
}

function get_merchants_goods_comment($ru_id)
{
	$seller_cmt = read_static_cache('seller_comment_' . $ru_id, '/data/sc_file/');

	if ($seller_cmt) {
		$arr = $seller_cmt['seller_comment'];
	}
	else {
		$sql = 'select shop_id, user_id from ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE user_id = \'' . $ru_id . '\' LIMIT 1';
		$res = $GLOBALS['db']->getAll($sql);
		$arr = array();

		foreach ($res as $key => $row) {
			$arr[$key] = $row;
			$arr[$key]['mc_all_Rank'] = seller_ments_count_all($row['user_id'], 'desc_rank');
			$arr[$key]['mc_one_Rank'] = seller_ments_count_rank_num($row['user_id'], 1, 'desc_rank');
			$arr[$key]['mc_two_Rank'] = seller_ments_count_rank_num($row['user_id'], 2, 'desc_rank');
			$arr[$key]['mc_three_Rank'] = seller_ments_count_rank_num($row['user_id'], 3, 'desc_rank');
			$arr[$key]['mc_four_Rank'] = seller_ments_count_rank_num($row['user_id'], 4, 'desc_rank');
			$arr[$key]['mc_five_Rank'] = seller_ments_count_rank_num($row['user_id'], 5, 'desc_rank');
			$arr[$key]['mc_all_Server'] = seller_ments_count_all($row['user_id'], 'service_rank');
			$arr[$key]['mc_one_Server'] = seller_ments_count_rank_num($row['user_id'], 1, 'service_rank');
			$arr[$key]['mc_two_Server'] = seller_ments_count_rank_num($row['user_id'], 2, 'service_rank');
			$arr[$key]['mc_three_Server'] = seller_ments_count_rank_num($row['user_id'], 3, 'service_rank');
			$arr[$key]['mc_four_Server'] = seller_ments_count_rank_num($row['user_id'], 4, 'service_rank');
			$arr[$key]['mc_five_Server'] = seller_ments_count_rank_num($row['user_id'], 5, 'service_rank');
			$arr[$key]['mc_all_Delivery'] = seller_ments_count_all($row['user_id'], 'delivery_rank');
			$arr[$key]['mc_one_Delivery'] = seller_ments_count_rank_num($row['user_id'], 1, 'delivery_rank');
			$arr[$key]['mc_two_Delivery'] = seller_ments_count_rank_num($row['user_id'], 2, 'delivery_rank');
			$arr[$key]['mc_three_Delivery'] = seller_ments_count_rank_num($row['user_id'], 3, 'delivery_rank');
			$arr[$key]['mc_four_Delivery'] = seller_ments_count_rank_num($row['user_id'], 4, 'delivery_rank');
			$arr[$key]['mc_five_Delivery'] = seller_ments_count_rank_num($row['user_id'], 5, 'delivery_rank');
			$sql = 'SELECT sid FROM ' . $GLOBALS['ecs']->table('comment_seller') . ' WHERE ru_id = \'' . $row['user_id'] . '\' LIMIT 1';
			$sid = $GLOBALS['db']->getOne($sql);

			if (0 < $sid) {
				@$arr['commentRank']['mc_all'] += $arr[$key]['mc_all_Rank'];
				@$arr['commentRank']['mc_one'] += $arr[$key]['mc_one_Rank'];
				@$arr['commentRank']['mc_two'] += $arr[$key]['mc_two_Rank'];
				@$arr['commentRank']['mc_three'] += $arr[$key]['mc_three_Rank'];
				@$arr['commentRank']['mc_four'] += $arr[$key]['mc_four_Rank'];
				@$arr['commentRank']['mc_five'] += $arr[$key]['mc_five_Rank'];
				@$arr['commentServer']['mc_all'] += $arr[$key]['mc_all_Server'];
				@$arr['commentServer']['mc_one'] += $arr[$key]['mc_one_Server'];
				@$arr['commentServer']['mc_two'] += $arr[$key]['mc_two_Server'];
				@$arr['commentServer']['mc_three'] += $arr[$key]['mc_three_Server'];
				@$arr['commentServer']['mc_four'] += $arr[$key]['mc_four_Server'];
				@$arr['commentServer']['mc_five'] += $arr[$key]['mc_five_Server'];
				@$arr['commentDelivery']['mc_all'] += $arr[$key]['mc_all_Delivery'];
				@$arr['commentDelivery']['mc_one'] += $arr[$key]['mc_one_Delivery'];
				@$arr['commentDelivery']['mc_two'] += $arr[$key]['mc_two_Delivery'];
				@$arr['commentDelivery']['mc_three'] += $arr[$key]['mc_three_Delivery'];
				@$arr['commentDelivery']['mc_four'] += $arr[$key]['mc_four_Delivery'];
				@$arr['commentDelivery']['mc_five'] += $arr[$key]['mc_five_Delivery'];
			}
		}

		@$arr['cmt']['commentRank']['zconments'] = get_conments_stars($arr['commentRank']['mc_all'], $arr['commentRank']['mc_one'], $arr['commentRank']['mc_two'], $arr['commentRank']['mc_three'], $arr['commentRank']['mc_four'], $arr['commentRank']['mc_five'], 'goods');
		@$arr['cmt']['commentServer']['zconments'] = get_conments_stars($arr['commentServer']['mc_all'], $arr['commentServer']['mc_one'], $arr['commentServer']['mc_two'], $arr['commentServer']['mc_three'], $arr['commentServer']['mc_four'], $arr['commentServer']['mc_five'], 'service');
		@$arr['cmt']['commentDelivery']['zconments'] = get_conments_stars($arr['commentDelivery']['mc_all'], $arr['commentDelivery']['mc_one'], $arr['commentDelivery']['mc_two'], $arr['commentDelivery']['mc_three'], $arr['commentDelivery']['mc_four'], $arr['commentDelivery']['mc_five'], 'shipping');
		@$arr['cmt']['all_zconments']['score'] = sprintf('%.2f', ($arr['cmt']['commentRank']['zconments']['score'] + $arr['cmt']['commentServer']['zconments']['score'] + $arr['cmt']['commentDelivery']['zconments']['score']) / 3);
		@$arr['cmt']['all_zconments']['allReview'] = round(($arr['cmt']['commentRank']['zconments']['allReview'] + $arr['cmt']['commentServer']['zconments']['allReview'] + $arr['cmt']['commentDelivery']['zconments']['allReview']) / 3, 2);
		@$arr['cmt']['all_zconments']['position'] = 100 - $arr['cmt']['all_zconments']['allReview'] - 3;
	}

	return $arr;
}

function seller_ments_count_all($ru_id, $type)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment_seller') . ' WHERE ru_id = \'' . $ru_id . '\' AND ' . $type . ' IN(1,2,3,4,5)';
	$res = $GLOBALS['db']->getOne($sql);
	return $res;
}

function seller_ments_count_rank_num($ru_id, $num, $type)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment_seller') . ' WHERE ru_id = \'' . $ru_id . '\' AND ' . $type . ' = \'' . $num . '\'';
	$res = $GLOBALS['db']->getOne($sql);
	return $res;
}

function get_goods_con_list($goods_id = 0, $table, $type = 0)
{
	$sql = 'select * from ' . $GLOBALS['ecs']->table($table) . ' where goods_id = \'' . $goods_id . '\'';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['id'] = $row['id'];

		if ($type == 0) {
			$arr[$key]['cfull'] = $row['cfull'];
			$arr[$key]['creduce'] = $row['creduce'];
		}
		else if ($type == 1) {
			$arr[$key]['sfull'] = $row['sfull'];
			$arr[$key]['sreduce'] = $row['sreduce'];
		}
	}

	if ($type == 0) {
		$arr = get_array_sort($arr, 'cfull');
	}
	else if ($type == 1) {
		$arr = get_array_sort($arr, 'sfull');
	}

	return $arr;
}

function get_con_goods_amount($goods_amount = 0, $goods_id = 0, $type = 0, $shipping_fee = 0, $parent_id = 0)
{
	if ($parent_id == 0) {
		if ($type == 0) {
			$table = 'goods_consumption';
		}
		else if ($type == 1) {
			$table = 'goods_conshipping';

			if (empty($shipping_fee)) {
				$shipping_fee = 0;
			}
		}

		$res = get_goods_con_list($goods_id, $table, $type);

		if ($res) {
			$arr = array();
			$arr['amount'] = '';

			foreach ($res as $key => $row) {
				if ($type == 0) {
					if ($row['cfull'] <= $goods_amount) {
						$arr[$key]['cfull'] = $row['cfull'];
						$arr[$key]['creduce'] = $row['creduce'];
						$arr[$key]['goods_amount'] = $goods_amount - $row['creduce'];

						if (0 < $arr[$key]['goods_amount']) {
							$arr['amount'] .= $arr[$key]['goods_amount'] . ',';
						}
					}
				}
				else if ($type == 1) {
					if ($row['sfull'] <= $goods_amount) {
						$arr[$key]['sfull'] = $row['sfull'];
						$arr[$key]['sreduce'] = $row['sreduce'];

						if (0 < $shipping_fee) {
							$arr[$key]['shipping_fee'] = $shipping_fee - $row['sreduce'];
							$arr['amount'] .= $arr[$key]['shipping_fee'] . ',';
						}
						else {
							$arr['amount'] = '0' . ',';
						}
					}
				}
			}

			if ($type == 0) {
				if (!empty($arr['amount'])) {
					$arr['amount'] = substr($arr['amount'], 0, -1);
				}
				else {
					$arr['amount'] = $goods_amount;
				}
			}
			else if ($type == 1) {
				if (!empty($arr['amount'])) {
					$arr['amount'] = substr($arr['amount'], 0, -1);
				}
				else {
					$arr['amount'] = $shipping_fee;
				}
			}
		}
		else if ($type == 0) {
			$arr['amount'] = $goods_amount;
		}
		else if ($type == 1) {
			$arr['amount'] = $shipping_fee;
		}

		if ($type == 1) {
			$sql = 'SELECT largest_amount FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id = \'' . $goods_id . '\'';
			$largest_amount = $GLOBALS['db']->getOne($sql);
			if ((0 < $largest_amount) && ($largest_amount < $goods_amount)) {
				$arr['amount'] = 0;
			}
		}
	}
	else if ($type == 0) {
		$arr['amount'] = $goods_amount;
	}
	else if ($type == 1) {
		$arr['amount'] = $shipping_fee;
	}

	return $arr;
}

function get_order_pdf_goods($order_id = 0)
{
	$goods_list = array();
	$goods_attr = array();
	$sql = "SELECT o.*, g.goods_number AS storage, o.goods_attr, g.suppliers_id, IFNULL(b.brand_name, '') AS brand_name, p.product_sn\r\n            FROM " . $GLOBALS['ecs']->table('order_goods') . " AS o\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('products') . " AS p\r\n                    ON p.product_id = o.product_id\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g\r\n                    ON o.goods_id = g.goods_id\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('brand') . " AS b\r\n                    ON g.brand_id = b.brand_id\r\n            WHERE o.order_id = '" . $order_id . '\'';
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if ($row['is_real'] == 0) {
			$filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $_CFG['lang'] . '.php';

			if (file_exists($filename)) {
				include_once $filename;

				if (!empty($_LANG[$row['extension_code'] . '_link'])) {
					$row['goods_name'] = $row['goods_name'] . sprintf($_LANG[$row['extension_code'] . '_link'], $row['goods_id'], $order['order_sn']);
				}
			}
		}

		$row['goods_amount'] = $row['goods_price'] * $row['goods_number'];
		$goods_con = get_con_goods_amount($row['goods_amount'], $row['goods_id'], 0, 0, $row['parent_id']);
		$goods_con['amount'] = explode(',', $goods_con['amount']);
		$row['amount'] = min($goods_con['amount']);
		$row['dis_amount'] = $row['goods_amount'] - $row['amount'];
		$row['discount_amount'] = price_format($row['dis_amount'], false);
		$products = get_warehouse_id_attr_number($row['goods_id'], $row['goods_attr_id'], $row['ru_id'], $row['warehouse_id'], $row['area_id'], $row['model_attr']);
		$row['goods_storage'] = $products['product_number'];

		if ($row['model_attr'] == 1) {
			$table_products = 'products_warehouse';
			$type_files = ' and warehouse_id = \'' . $row['warehouse_id'] . '\'';
		}
		else if ($row['model_attr'] == 2) {
			$table_products = 'products_area';
			$type_files = ' and area_id = \'' . $row['area_id'] . '\'';
		}
		else {
			$table_products = 'products';
			$type_files = '';
		}

		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $row['goods_id'] . '\'' . $type_files . ' LIMIT 0, 1';
		$prod = $GLOBALS['db']->getRow($sql);

		if (empty($prod)) {
			$row['goods_storage'] = $row['storage'];
		}

		$row['goods_storage'] = !empty($row['goods_storage']) ? $row['goods_storage'] : 0;
		$row['storage'] = $row['goods_storage'];
		$row['product_sn'] = $products['product_sn'];
		$row['formated_subtotal'] = price_format($row['amount']);
		$row['formated_goods_price'] = price_format($row['goods_price']);
		$row['warehouse_name'] = $GLOBALS['db']->getOne('select region_name from ' . $GLOBALS['ecs']->table('region_warehouse') . ' where region_id = \'' . $row['warehouse_id'] . '\'');
		$goods_attr[] = explode(' ', trim($row['goods_attr']));

		if ($row['extension_code'] == 'package_buy') {
			$row['storage'] = '';
			$row['brand_name'] = '';
			$row['package_goods_list'] = get_package_goods($row['goods_id']);
		}

		$goods_list[] = $row;
	}

	return $goods_list;
}

function get_order_ruid($order_id = 0)
{
	$sql = 'select og.ru_id from ' . $GLOBALS['ecs']->table('order_info') . ' as oi left join ' . $GLOBALS['ecs']->table('order_goods') . ' as og on oi.order_id = og.order_id where oi.order_id = \'' . $order_id . '\'' . ' group by oi.order_id';
	$ru_id = $GLOBALS['db']->getOne($sql);
	$sql = 'select * from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id = \'' . $ru_id . '\'';
	return $GLOBALS['db']->getRow($sql);
}

function get_cart_combo_goods_list($goods_id = 0, $parent = 0, $group = '')
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
		$sess = '';
	}
	else {
		$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
		$sess = real_cart_mac_ip();
	}

	$sql = 'select goods_price, goods_number, goods_id from ' . $GLOBALS['ecs']->table('cart_combo') . ' where ' . $sess_id . ' and (parent_id = \'' . $parent . '\' or (goods_id = \'' . $parent . '\' and parent_id = \'0\')) and group_id = \'' . $group . '\'';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();
	$arr['combo_amount'] = 0;
	$arr['combo_number'] = 0;

	foreach ($res as $key => $row) {
		$arr[$key]['goods_number'] = $row['goods_number'];
		$arr[$key]['goods_price'] = $row['goods_price'];
		$arr[$key]['goods_id'] = $row['goods_id'];
		$arr['combo_amount'] += $row['goods_price'] * $row['goods_number'];
		$arr['combo_number'] += $row['goods_number'];
	}

	$arr['shop_price'] = $arr['combo_amount'];
	$arr['combo_amount'] = price_format($arr['combo_amount'], false);
	return $arr;
}

function get_cfg_group_goods()
{
	$group_goods = $GLOBALS['_CFG']['group_goods'];
	$arr = array();

	if (!empty($group_goods)) {
		$group_goods = explode(',', $group_goods);

		foreach ($group_goods as $key => $row) {
			$key += 1;
			$arr[$key] = $row;
		}
	}

	return $arr;
}

function get_merge_fittings_array($fittings_index, $fittings)
{
	$arr = array();

	if ($fittings_index) {
		for ($i = 1; $i <= count($fittings_index); $i++) {
			for ($j = 0; $j <= count($fittings); $j++) {
				if ($fittings_index[$i] == $fittings[$j]['group_id']) {
					$arr[$i][$j] = $fittings[$j];
				}
			}
		}
	}

	$arr = array_values($arr);
	return $arr;
}

function get_fittings_array_list($merge_fittings, $goods_fittings)
{
	$arr = array();

	if ($merge_fittings) {
		for ($i = 0; $i < count($merge_fittings); $i++) {
			$merge_fittings[$i] = array_merge($goods_fittings, $merge_fittings[$i]);
			$merge_fittings[$i] = array_values($merge_fittings[$i]);
			$arr[$i]['fittings_interval'] = get_choose_goods_combo_cart($merge_fittings[$i]);
		}
	}

	return $arr;
}

function get_combo_goods_list_select($goods_id = 0, $parent = 0, $group = '')
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
		$sess = '';
	}
	else {
		$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
		$sess = real_cart_mac_ip();
	}

	$sql = 'select rec_id, goods_id, group_id, goods_attr_id from ' . $GLOBALS['ecs']->table('cart_combo') . ' where ' . $sess_id . ' and (parent_id = \'' . $parent . '\' or (goods_id = \'' . $parent . '\' and parent_id = \'0\')) and group_id = \'' . $group . '\'';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();
	$arr['attr_count'] = '';

	foreach ($res as $key => $row) {
		$arr[$key]['rec_id'] = $row['rec_id'];
		$arr[$key]['goods_id'] = $row['goods_id'];
		$arr[$key]['group_id'] = $row['group_id'];
		$arr[$key]['goods_attr_id'] = $row['goods_attr_id'];
		$arr[$key]['attr_count'] = get_goods_attr_type_list($row['goods_id'], 1);

		if (!empty($arr[$key]['goods_attr_id'])) {
			$attr_count = count(explode(',', $arr[$key]['goods_attr_id']));
		}
		else {
			$attr_count = 0;
		}

		if (0 < $arr[$key]['attr_count']) {
			if ($attr_count == $arr[$key]['attr_count']) {
				$arr[$key]['yes_attr'] = 1;
			}
			else {
				$arr[$key]['yes_attr'] = 0;
			}
		}
		else {
			$arr[$key]['yes_attr'] = 1;
		}

		$arr['attr_count'] .= $arr[$key]['yes_attr'] . ',';
	}

	$attr_array = 0;
	$attr_yes = explode(',', substr($arr['attr_count'], 0, -1));

	foreach ($attr_yes as $row) {
		$attr_array += $row;
	}

	$goods_count = count($res);

	if ($attr_array == $goods_count) {
		return 1;
	}
	else {
		return 0;
	}
}

function get_merchants_navigator($ru_id = 0, $ctype = '', $catlist = array())
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('merchants_nav') . "\r\n            WHERE ru_id = " . $ru_id . ' and ifshow = \'1\' ORDER BY vieworder, type';
	$res = $GLOBALS['db']->query($sql);
	$cur_url = substr(strrchr($_SERVER['REQUEST_URI'], '/'), 1);

	if (intval($GLOBALS['_CFG']['rewrite'])) {
		if (strpos($cur_url, '-')) {
			preg_match('/([a-z]*)-([0-9]*)/', $cur_url, $matches);
			$cur_url = $matches[1] . '.php?id=' . $matches[2];
		}
	}
	else {
		$cur_url = substr(strrchr($_SERVER['REQUEST_URI'], '/'), 1);
	}

	$noindex = false;
	$active = 0;
	$navlist = array(
		'top'    => array(),
		'middle' => array(),
		'bottom' => array()
		);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$navlist[$row['type']][] = array('cat_id' => 'cid', 'cat_name' => $row['name'], 'opennew' => $row['opennew'], 'url' => $row['url'], 'ctype' => $row['ctype'], 'cid' => $row['cat_id'], 'vieworder' => $row['vieworder']);
	}

	foreach ($navlist['middle'] as $k => $v) {
		$condition = (empty($ctype) ? strpos($cur_url, $v['url']) === 0 : (strpos($cur_url, $v['url']) === 0) && (strlen($cur_url) == strlen($v['url'])));

		if ($condition) {
			$navlist['middle'][$k]['active'] = 1;
			$noindex = true;
			$active += 1;
		}

		if (substr($v['url'], 0, 8) == 'category') {
			$cat_id = $v['cid'];
			$children = get_children($cat_id);
			$cat_list = get_categories_tree_xaphp($cat_id);
			$navlist['middle'][$k]['cat'] = 1;
			$navlist['middle'][$k]['cat_list'] = $cat_list;
		}
		else if (substr($v['url'], 0, 15) == 'merchants_store') {
			if ($v['cid']) {
				$build_uri = array('cid' => $v['cid'], 'urid' => $ru_id, 'append' => $v['name']);
				$domain_url = get_seller_domain_url($ru_id, $build_uri);
				$navlist['middle'][$k]['url'] = $domain_url['domain_name'];
			}
		}
	}

	if (!empty($ctype) && ($active < 1)) {
		foreach ($catlist as $key => $val) {
			foreach ($navlist['middle'] as $k => $v) {
				if (!empty($v['ctype']) && ($v['ctype'] == $ctype) && ($v['cid'] == $val) && ($active < 1)) {
					$navlist['middle'][$k]['active'] = 1;
					$noindex = true;
					$active += 1;
				}
			}
		}
	}

	if ($noindex == false) {
		$navlist['config']['index'] = 1;
	}

	return $navlist;
}

function get_user_attr_checked($goods_attr, $attr_id)
{
	$arr['class'] = 'catcolor';
	$arr['attr_val'] = '';

	if ($goods_attr) {
		foreach ($goods_attr as $key => $grow) {
			if ($grow == $attr_id) {
				$arr['class'] = 'cattsel';
				$arr['attr_val'] = $grow;
				return $arr;
			}
		}
	}

	return $arr;
}

function get_user_region_address($order_id = 0, $address = '', $type = 0)
{
	if ($type == 1) {
		$table = 'order_return';
		$where = 'o.ret_id = \'' . $order_id . '\'';
	}
	else {
		$table = 'order_info';
		$where = 'o.order_id = \'' . $order_id . '\'';
	}

	$sql = 'SELECT concat(IFNULL(p.region_name, \'\'), ' . '\'  \', IFNULL(t.region_name, \'\'), \'  \', IFNULL(d.region_name, \'\'), \'  \', IFNULL(s.region_name, \'\')) AS region ' . 'FROM ' . $GLOBALS['ecs']->table($table) . ' AS o ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON o.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON o.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON o.district = d.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS s ON o.street = s.region_id ' . 'WHERE ' . $where;
	$region = $GLOBALS['db']->getOne($sql);

	if ($address) {
		$region = $region . '&nbsp;' . $address;
	}

	return $region;
}

function get_flow_user_region($order_id = 0)
{
	$sql = 'SELECT concat(IFNULL(p.region_name, \'\'), ' . '\'\', IFNULL(t.region_name, \'\'), \'\', IFNULL(d.region_name, \'\'), \'\', IFNULL(s.region_name, \'\')) AS region ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON o.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON o.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON o.district = d.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS s ON o.street = s.region_id ' . 'WHERE o.order_id = \'' . $order_id . '\'';
	return $GLOBALS['db']->getOne($sql);
}

function get_area_region_info($region)
{
	$where = '1';
	$left = '';
	$select = '';
	if (isset($region['province']) && $region['province']) {
		$where .= ' AND p.region_id = \'' . $region['province'] . '\'';
	}

	if (isset($region['street']) && $region['street']) {
		$select .= ', \' \', IFNULL(d.region_name, \'\'), \' \', IFNULL(s.region_name, \'\')';
		$left .= $GLOBALS['ecs']->table('region') . ' AS p, ';
		$left .= $GLOBALS['ecs']->table('region') . ' AS t, ';
		$left .= $GLOBALS['ecs']->table('region') . ' AS d, ';
		$left .= $GLOBALS['ecs']->table('region') . ' AS s ';
		$where .= ' AND t.region_id = \'' . $region['city'] . '\'';
		$where .= ' AND d.region_id = \'' . $region['district'] . '\'';
		$where .= ' AND s.region_id = \'' . $region['street'] . '\'';
	}
	else {
		if (isset($region['district']) && $region['district']) {
			$select .= ', \' \', IFNULL(t.region_name, \'\'), \' \', IFNULL(d.region_name, \'\')';
			$left .= $GLOBALS['ecs']->table('region') . ' AS p, ';
			$left .= $GLOBALS['ecs']->table('region') . ' AS t, ';
			$left .= $GLOBALS['ecs']->table('region') . ' AS d ';
			$where .= ' AND t.region_id = \'' . $region['city'] . '\'';
			$where .= ' AND d.region_id = \'' . $region['district'] . '\'';
		}
		else {
			if (isset($region['city']) && $region['city']) {
				$select .= ', \' \', IFNULL(t.region_name, \'\')';
				$left .= $GLOBALS['ecs']->table('region') . ' AS p, ';
				$left .= $GLOBALS['ecs']->table('region') . ' AS t ';
				$where .= ' AND t.region_id = \'' . $region['city'] . '\'';
			}
			else {
				$left .= $GLOBALS['ecs']->table('region') . ' AS p ';
			}
		}
	}

	$sql = 'SELECT concat(IFNULL(p.region_name, \'\') ' . $select . ') AS region ' . 'FROM ' . $left . 'WHERE ' . $where;
	return $GLOBALS['db']->getOne($sql);
}

function get_rank_info()
{
	global $db;
	global $ecs;

	if (!empty($_SESSION['user_rank'])) {
		$sql = 'SELECT rank_id, rank_name, special_rank, max_points FROM ' . $ecs->table('user_rank') . ' WHERE rank_id = \'' . $_SESSION['user_rank'] . '\'';
		$row = $db->getRow($sql);

		if (empty($row)) {
			return array();
		}

		if ($row['special_rank']) {
			$GLOBALS['smarty']->assign('special_rank', $row['special_rank']);
			return array('rank_name' => $row['rank_name']);
		}
		else {
			$rank_points = $db->getOne('SELECT rank_points FROM ' . $ecs->table('users') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\'');
			$sql = 'SELECT rank_name,min_points FROM ' . $ecs->table('user_rank') . ' WHERE min_points > \'' . $rank_points . '\' ORDER BY min_points ASC LIMIT 1';
			$rt = $db->getRow($sql);
			$next_rank_name = $rt['rank_name'];
			$next_rank = $rt['min_points'] - $rank_points;
			$row['rank_points'] = $rank_points;
			$user_rank = $row;
			return array('next_rank_name' => $next_rank_name, 'next_rank' => $next_rank, 'user_rank' => $user_rank);
		}
	}
	else {
		return array();
	}
}

function get_user_default($user_id = 0)
{
	$user_bonus = get_user_bonus();
	$sql = 'SELECT user_name, email, mobile_phone, pay_points, user_money, credit_line, last_login, is_validated, user_picture, rank_points,user_rank, nick_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $user_id . '\' LIMIT 1';
	$row = $GLOBALS['db']->getRow($sql);
	$info = array();

	if (0 < $row['user_rank']) {
		$sql = 'SELECT rank_id, rank_name, discount FROM ' . $GLOBALS['ecs']->table('user_rank') . ' WHERE rank_id = \'' . $row['user_rank'] . '\'';
	}
	else {
		$sql = 'SELECT rank_id, rank_name, discount, min_points' . ' FROM ' . $GLOBALS['ecs']->table('user_rank') . ' WHERE min_points<= ' . intval($row['rank_points']) . ' ORDER BY min_points DESC';
	}

	if ($user_id && ($rank = $GLOBALS['db']->getRow($sql))) {
		$info['rank_name'] = $rank['rank_name'];
	}
	else {
		$info['rank_name'] = $GLOBALS['_LANG']['undifine_rank'];
	}

	$info['username'] = $row['user_name'];
	$info['shop_name'] = $GLOBALS['_CFG']['shop_name'];
	$info['integral'] = $row['pay_points'] . $GLOBALS['_CFG']['integral_name'];
	$info['is_validate'] = $GLOBALS['_CFG']['member_email_validate'] && !$row['is_validated'] ? 0 : 1;
	$info['credit_line'] = $row['credit_line'];
	$info['formated_credit_line'] = price_format($info['credit_line'], false);
	$info['nick_name'] = !empty($row['nick_name']) ? $row['nick_name'] : $info['username'];
	if ((strpos($row['user_picture'], 'http://') === false) && (strpos($row['user_picture'], 'https://') === false)) {
		if (($GLOBALS['_CFG']['open_oss'] == 1) && $row['user_picture']) {
			$bucket_info = get_bucket_info();
			$info['user_picture'] = $bucket_info['endpoint'] . $row['user_picture'];
		}
		else {
			$info['user_picture'] = $row['user_picture'];
		}
	}
	else {
		$info['user_picture'] = $row['user_picture'];
	}

	$info['is_validated'] = $row['is_validated'];
	$last_time = (!isset($_SESSION['last_time']) ? $row['last_login'] : $_SESSION['last_time']);

	if ($last_time == 0) {
		$_SESSION['last_time'] = $last_time = gmtime();
	}

	$info['last_time'] = local_date($GLOBALS['_CFG']['time_format'], $last_time);
	$info['surplus'] = price_format($row['user_money'], false);
	$info['bonus'] = sprintf($GLOBALS['_LANG']['user_bonus_info'], $user_bonus['bonus_count'], price_format($user_bonus['bonus_value'], false));

	if (defined('THEME_EXTENSION')) {
		$info['bonus_count'] = $user_bonus['bonus_count'];
		$info['bonus_value'] = price_format($user_bonus['bonus_value']);
		$info['pay_points'] = 0 < $row['pay_points'] ? $row['pay_points'] : 0;
	}

	$info['email'] = $row['email'];
	$info['mobile_phone'] = $row['mobile_phone'];
	$info['user_money'] = 0 < $row['user_money'] ? $row['user_money'] : 0;
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE user_id = \'' . $user_id . '\' AND add_time > \'' . local_strtotime('-1 months') . '\'';
	$info['order_count'] = $GLOBALS['db']->getOne($sql);
	include_once ROOT_PATH . 'includes/lib_order.php';
	$sql = 'SELECT order_id, order_sn ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE user_id = \'' . $user_id . '\' AND shipping_time > \'' . $last_time . '\'' . order_query_sql('shipped');
	$info['shipped_order'] = $GLOBALS['db']->getAll($sql);
	return $info;
}

function single_show_reply_list($parent_id, $page)
{
	require_once 'includes/cls_newPage.php';
	$record_count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . 'WHERE parent_id = ' . $parent_id . ' AND single_id > 0');
	$reply_comment = new Page($record_count, 5, '', $parent_id, 0, $page, 'single_reply_gotoPage', 1, 1);
	$limit = $reply_comment->limit;
	$reply_paper = $reply_comment->fpage(array(0, 4, 5, 6, 9));
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE parent_id=\'' . $parent_id . '\' AND single_id > 0 AND status=1 ORDER BY add_time DESC ' . $limit;
	$comment = $GLOBALS['db']->getAll($sql);
	$comment_list = array();
	$replay_comment = array();

	foreach ($comment as $key => $comm) {
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE comment_id=\'' . $comm['parent_id'] . '\'';
		$child_comment = $GLOBALS['db']->getRow($sql);

		if ($child_comment) {
			$comment_list[$key]['quote_username'] = $child_comment['user_name'];
			$comment_list[$key]['quote_content'] = $child_comment['content'];
		}

		$comment_list[$key]['comment_id'] = $comm['comment_id'];
		$comment_list[$key]['content'] = $comm['content'];

		if (!empty($comm['add_time'])) {
			$comment_list[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $comm['add_time']);
		}

		if (!empty($comm['user_name'])) {
			$comment_list[$key]['user_name'] = $comm['user_name'];
		}
	}

	$cmt = array('comment_list' => $comment_list, 'reply_paper' => $reply_paper, 'record_count' => $record_count);
	return $cmt;
}

function get_isHas_area($parent_id = 0, $type = 0)
{
	if ($type == 0) {
		$where = 'parent_id = \'' . $parent_id . '\' ';
		$sql = 'SELECT region_id FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE ' . $where;
		return $GLOBALS['db']->getOne($sql, true);
	}
	else if ($type == 1) {
		$where = 'r1.region_id = \'' . $parent_id . '\'';
		$sql = 'SELECT r1.parent_id, r2.region_name FROM ' . $GLOBALS['ecs']->table('region') . ' as r1, ' . $GLOBALS['ecs']->table('region') . ' as r2 ' . ' WHERE 1 AND r1.parent_id = r2.region_id AND ' . $where;
		return $GLOBALS['db']->getRow($sql);
	}
}

function get_goods_file_content($goods_id, $arr = '', $ru_id, $review_goods)
{
	if (0 < $ru_id) {
		if (!empty($arr)) {
			$arr = explode('-', $arr);
			$arr1 = $arr[0];
			$arr2 = $arr[1];
			$arr1 = explode(',', $arr1);

			for ($i = 0; $i < count($arr1); $i++) {
				if ($arr1[$i] == 'promote_price') {
					$contents = floatval($_POST[$arr1[$i]]);
				}
				else {
					$contents = $_POST[$arr[$i]];
				}

				$sql = 'SELECT ' . $arr1[$i] . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id = \'' . $goods_id . '\'';
				$res = $GLOBALS['db']->getOne($sql);

				if ($contents != $res) {
					$review_status = 1;

					if ($GLOBALS['_CFG']['review_goods'] == 0) {
						$review_status = 5;
					}
					else if ($review_goods == 0) {
						$review_status = 5;
					}

					if ($review_status < 3) {
						$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE goods_id ' . db_create_in($goods_id);
						$GLOBALS['db']->query($sql);
					}

					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . ' SET review_status = ' . $review_status . ' ' . ' WHERE goods_id = \'' . $goods_id . '\' AND user_id > 0';
					$GLOBALS['db']->query($sql);
					break;
				}
			}
		}
		else {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . ' SET review_status = 3 ' . ' WHERE goods_id = \'' . $goods_id . '\'';
			$GLOBALS['db']->query($sql);
		}
	}
}

function mc_random($length, $char_str = 'abcdefghijklmnopqrstuvwxyz0123456789')
{
	$hash = '';
	$chars = $char_str;
	$max = strlen($chars);

	for ($i = 0; $i < $length; $i++) {
		$hash .= substr($chars, rand(0, 1000) % $max, 1);
	}

	return $hash;
}

function get_default_user_orders($user_id, $record_count, $where = '', $page = 1)
{
	$arr = array();
	$sql = 'SELECT og.ru_id, oi.main_order_id, oi.consignee, oi.order_id, oi.order_sn, oi.order_status, oi.shipping_status, oi.pay_status, oi.add_time, oi.shipping_time, oi.auto_delivery_time, oi.sign_time, ' . '(oi.goods_amount + oi.shipping_fee + oi.insure_fee + oi.pay_fee + oi.pack_fee + oi.card_fee + oi.tax - oi.discount) AS total_fee, og.goods_id, ' . 'oi.invoice_no, oi.shipping_name, oi.tel, oi.email, oi.address, oi.province, oi.city, oi.district ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi' . ' left join ' . $GLOBALS['ecs']->table('order_goods') . ' as og on oi.order_id = og.order_id' . ' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = 0 ' . $where . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id) = 0 ' . ' group by oi.order_id ORDER BY oi.add_time DESC limit 0,5';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']] . ',' . $GLOBALS['_LANG']['ps'][$row['pay_status']] . ',' . $GLOBALS['_LANG']['ss'][$row['shipping_status']];
		$arr[$key]['order_id'] = $row['order_id'];
		$arr[$key]['order_sn'] = $row['order_sn'];
		$arr[$key]['consignee'] = $row['consignee'];
		$arr[$key]['total_fee'] = price_format($row['total_fee'], false);
		$arr[$key]['order_status'] = $row['order_status'];
		$arr[$key]['order_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
	}

	return $arr;
}

function get_default_collection_goods($user_id)
{
	if (!isset($_COOKIE['province'])) {
		$area_array = get_ip_area_name();

		if ($area_array['county_level'] == 2) {
			$date = array('region_id', 'parent_id', 'region_name');
			$where = 'region_name = \'' . $area_array['area_name'] . '\' AND region_type = 2';
			$city_info = get_table_date('region', $where, $date, 1);
			$date = array('region_id', 'region_name');
			$where = 'region_id = \'' . $city_info[0]['parent_id'] . '\'';
			$province_info = get_table_date('region', $where, $date);
			$where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
			$district_info = get_table_date('region', $where, $date, 1);
		}
		else if ($area_array['county_level'] == 1) {
			$area_name = $area_array['area_name'];
			$date = array('region_id', 'region_name');
			$where = 'region_name = \'' . $area_name . '\'';
			$province_info = get_table_date('region', $where, $date);
			$where = 'parent_id = \'' . $province_info['region_id'] . '\' order by region_id asc limit 0, 1';
			$city_info = get_table_date('region', $where, $date, 1);
			$where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
			$district_info = get_table_date('region', $where, $date, 1);
		}
	}

	$province_id = (isset($_COOKIE['province']) ? $_COOKIE['province'] : $province_info['region_id']);
	$city_id = (isset($_COOKIE['city']) ? $_COOKIE['city'] : $city_info[0]['region_id']);
	$district_id = (isset($_COOKIE['district']) ? $_COOKIE['district'] : $district_info[0]['region_id']);
	setcookie('province', $province_id, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('city', $city_id, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('district', $district_id, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$area_info = get_area_info($province_id);
	$area_id = $area_info['region_id'];
	$region_where = 'regionId = \'' . $province_id . '\'';
	$date = array('parent_id');
	$region_id = get_table_date('region_warehouse', $region_where, $date, 2);
	$leftJoin = '';
	$rz_shopName = '';

	if (defined('THEME_EXTENSION')) {
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS msi ON g.user_id = msi.user_id ';
		$rz_shopName = 'msi.rz_shopName, ';
	}

	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $region_id . '\' ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ';
	$sql = 'SELECT g.goods_thumb, g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price, ' . $rz_shopName . 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'g.promote_start_date,g.promote_end_date, c.rec_id, c.is_attention, c.add_time' . ' FROM ' . $GLOBALS['ecs']->table('collect_goods') . ' AS c' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'ON g.goods_id = c.goods_id ' . $leftJoin . ' LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . ' WHERE c.user_id = \'' . $user_id . '\' ORDER BY c.rec_id DESC limit 0,5';
	$res = $GLOBALS['db']->query($sql, $num, $start);

	if (defined('THEME_EXTENSION')) {
		$sql = 'SELECT shop_name FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = 0 ';
		$shop_name = $GLOBALS['db']->getOne($sql);
	}

	$goods_list = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$goods_list[$row['goods_id']]['rec_id'] = $row['rec_id'];
		$goods_list[$row['goods_id']]['is_attention'] = $row['is_attention'];
		$goods_list[$row['goods_id']]['goods_id'] = $row['goods_id'];

		if (defined('THEME_EXTENSION')) {
			$goods_list[$row['goods_id']]['shop_name'] = !empty($row['rz_shopName']) ? $row['rz_shopName'] : $shop_name;
		}

		$goods_list[$row['goods_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
		$goods_list[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$goods_list[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$goods_list[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$goods_list[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$goods_list[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$goods_list[$row['goods_id']]['goods_thumb'] = get_image_path($arrrow['goods_id'], $row['goods_thumb'], true);
	}

	return $goods_list;
}

function get_user_helpart()
{
	$article_id = $GLOBALS['_CFG']['user_helpart'];
	$arr = array();
	$new_article = substr($article_id, -1);

	if ($new_article == ',') {
		$article_id = substr($article_id, 0, -1);
	}

	if (!empty($article_id)) {
		$sql = 'SELECT article_id, title FROM ' . $GLOBALS['ecs']->table('article') . ' where article_id in(' . $article_id . ') order by article_id DESC';
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $key => $row) {
			$arr[$key]['article_id'] = $row['article_id'];
			$arr[$key]['title'] = $row['title'];
			$arr[$key]['url'] = build_uri('article', array('aid' => $row['article_id']), $row['title']);
		}
	}

	return $arr;
}

function get_cart_goods_ru_list($goods, $type = 0)
{
	$ru_id_list = get_cart_goods_ru_id($goods);
	$ru_id_list = array_values(array_unique($ru_id_list));
	$arr = array();

	foreach ($ru_id_list as $wkey => $ru) {
		foreach ($goods as $gkey => $row) {
			if ($ru == $row['ru_id']) {
				$arr[$ru][$gkey] = $row;
			}
		}
	}

	if ($type == 1) {
		return $arr;
	}
	else {
		$new_arr = array();

		foreach ($arr as $key => $row) {
			$new_arr[$key] = get_cart_goods_warehouse_list($row);
		}

		return $new_arr;
	}
}

function get_cart_goods_ru_id($goods)
{
	$arr = array();

	if (0 < count($goods)) {
		foreach ($goods as $key => $row) {
			$arr[$key] = $row['ru_id'];
		}
	}

	return $arr;
}

function get_cart_goods_warehouse_list($goods)
{
	$warehouse_id_list = get_cart_goods_warehouse_id($goods);
	$warehouse_id_list = array_values(array_unique($warehouse_id_list));
	$arr = array();

	foreach ($warehouse_id_list as $wkey => $warehouse) {
		foreach ($goods as $gkey => $row) {
			if ($warehouse == $row['warehouse_id']) {
				$arr[$warehouse][$gkey] = $row;
			}
		}
	}

	return $arr;
}

function get_cart_goods_warehouse_id($goods)
{
	$arr = array();

	foreach ($goods as $key => $row) {
		$arr[$key] = $row['warehouse_id'];
	}

	return $arr;
}

function get_cart_goods_combined_freight($goods, $type = 0, $region = '', $ru_id = 0, $shipping_id = 0)
{
	$arr = array();
	$new_arr = array();

	if ($type == 1) {
		foreach ($goods as $key => $row) {
			foreach ($row as $warehouse => $rows) {
				foreach ($rows as $gkey => $grow) {
					$trow = get_goods_transport($grow['tid']);
					if (($grow['extension_code'] == 'package_buy') || ($grow['is_shipping'] == 0)) {
						@$arr[$key][$warehouse]['goods_transport'] .= $grow['goods_id'] . '|' . $key . '|' . $grow['tid'] . '|' . $grow['freight'] . '|' . $grow['shipping_fee'] . '|' . $grow['goods_number'] . '|' . $grow['goodsWeight'] . '|' . $grow['goods_price'] . '-';
						if ($grow['freight'] && ($trow['freight_type'] == 0)) {
							$weight = 0;
							$goods_price = 0;
							$number = 0;
						}
						else {
							$weight = $grow['goodsWeight'] * $grow['goods_number'];
							$goods_price = $grow['goods_price'] * $grow['goods_number'];
							$number = $grow['goods_number'];
						}

						@$arr[$key][$warehouse]['weight'] += $weight;
						@$arr[$key][$warehouse]['goods_price'] += $goods_price;
						@$arr[$key][$warehouse]['number'] += $number;
						@$arr[$key][$warehouse]['ru_id'] = $key;
						@$arr[$key][$warehouse]['warehouse_id'] = $warehouse;
						@$arr[$key][$warehouse]['warehouse_name'] = $GLOBALS['db']->getOne('SELECT region_name FROM ' . $GLOBALS['ecs']->table('region_warehouse') . ' WHERE region_id = \'' . $warehouse . '\'');
					}
				}
			}
		}

		foreach ($arr as $key => $row) {
			if (!empty($shipping_id)) {
				$shipping_info = get_shipping_code($shipping_id);
				$shipping_code = $shipping_info['shipping_code'];
			}
			else {
				$seller_shipping = get_seller_shipping_type($key);
				$shipping_code = $seller_shipping['shipping_code'];
			}

			foreach ($row as $warehouse => $rows) {
				@$arr[$key][$warehouse]['shipping'] = get_goods_freight($rows, $rows['warehouse_id'], $region, $rows['goods_number'], $shipping_code);
			}
		}

		$new_arr['shipping_fee'] = 0;

		foreach ($arr as $key => $row) {
			foreach ($row as $warehouse => $rows) {
				if (isset($rows['shipping_code']) && ($rows['shipping_code'] == 'cac')) {
					$rows['shipping']['shipping_fee'] = 0;
				}

				$new_arr['shipping_fee'] += $rows['shipping']['shipping_fee'];
			}
		}

		$arr = array('ru_list' => $arr, 'shipping' => $new_arr);
		return $arr;
	}
	else if ($type == 2) {
		$arr = get_cart_goods_warehouse_list($goods);

		foreach ($arr as $warehouse => $row) {
			foreach ($row as $gw => $grow) {
				if (($grow['extension_code'] == 'package_buy') || ($grow['is_shipping'] == 0)) {
					$trow = get_goods_transport($grow['tid']);
					@$new_arr[$warehouse]['goods_transport'] .= $grow['goods_id'] . '|' . $grow['ru_id'] . '|' . $grow['tid'] . '|' . $grow['freight'] . '|' . $grow['shipping_fee'] . '|' . $grow['goods_number'] . '|' . $grow['goodsWeight'] . '|' . $grow['goods_price'] . '-';
					if ($grow['freight'] && ($trow['freight_type'] == 0)) {
						$weight = 0;
						$goods_price = 0;
						$number = 0;
					}
					else {
						$weight = $grow['goodsWeight'] * $grow['goods_number'];
						$goods_price = $grow['goods_price'] * $grow['goods_number'];
						$number = $grow['goods_number'];
					}

					@$new_arr[$warehouse]['weight'] += $weight;
					@$new_arr[$warehouse]['goods_price'] += $goods_price;
					@$new_arr[$warehouse]['number'] += $number;
					@$new_arr[$warehouse]['ru_id'] = $grow['ru_id'];
					@$new_arr[$warehouse]['warehouse_id'] = $warehouse;
					@$new_arr[$warehouse]['order_id'] = $grow['order_id'];
					@$new_arr[$warehouse]['warehouse_name'] = $GLOBALS['db']->getOne('SELECT region_name FROM ' . $GLOBALS['ecs']->table('region_warehouse') . ' WHERE region_id = \'' . $warehouse . '\'');
				}
			}
		}

		foreach ($new_arr as $key => $row) {
			$sql = 'SELECT country, province, city, district, district, street, shipping_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $row['order_id'] . '\'';
			$order = $GLOBALS['db']->getRow($sql);
			$shipping_arr = explode(',', $order['shipping_id']);

			if (is_array($shipping_arr)) {
				foreach ($shipping_arr as $kk => $vv) {
					$ruid_shipping = explode('|', $vv);

					if ($ruid_shipping[0] == $ru_id) {
						$shipping_info = get_shipping_code($ruid_shipping[1]);
						$shipping_code = $shipping_info['shipping_code'];
						continue;
					}
				}
			}

			@$new_arr[$key]['shipping'] = get_goods_freight($row, $row['warehouse_id'], $order, $row['number'], $shipping_code);
			if (($ru_id == 0) && ($shipping_type == 1)) {
				$new_arr[$key]['shipping']['shipping_fee'] = 0;
			}

			$new_arr['shipping_fee'] += $new_arr[$key]['shipping']['shipping_fee'];
		}

		$arr = $new_arr;
	}

	return $arr;
}

function get_warehouse_cart_goods_info($goods, $type, $region, $shipping_id = 0)
{
	if ($type == 1) {
		$goods = get_cart_goods_ru_list($goods);
	}
	else {
		$goods = get_cart_goods_warehouse_list($goods);
	}

	$shipping_fee = get_cart_goods_combined_freight($goods, $type, $region, 0, $shipping_id);
	return $shipping_fee;
}

function get_ru_info_list($ru_list)
{
	$arr = array();

	foreach ($ru_list as $key => $row) {
		if ($key == 0) {
			$shop_name = $GLOBALS['db']->getOne('SELECT shop_name FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = \'' . $key . '\'');
		}
		else {
			$shop_information = $GLOBALS['db']->getRow('SELECT shoprz_brandName, shopNameSuffix FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE user_id = \'' . $key . '\'');
			$shop_name = $shop_information['shoprz_brandName'] . $shop_information['shopNameSuffix'];
		}

		$arr[$key]['ru_name'] = $shop_name;
		$arr[$key]['ru_shipping'] = $row;

		foreach ($row as $warehouse => $rows) {
			$arr[$key]['shipping_fee'] += $rows['shipping']['shipping_fee'];
		}

		$arr[$key]['shippingFee'] = $arr[$key]['shipping_fee'];
		$arr[$key]['shipping_fee'] = price_format($arr[$key]['shipping_fee'], false);
	}

	return $arr;
}

function get_goods_link_area_list($goods_id = 0, $ru_id = 0)
{
	$sql = 'SELECT goods_id, region_id, ru_id FROM ' . $GLOBALS['ecs']->table('link_area_goods') . ' WHERE goods_id = \'' . $goods_id . '\' AND ru_id = \'' . $ru_id . '\'';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();
	$arr['goods_area'] = '';

	if ($res) {
		foreach ($res as $key => $row) {
			$arr['goods_area'] .= $row['region_id'] . ',';
		}

		$arr['goods_area'] = explode(',', substr($arr['goods_area'], 0, -1));
	}

	return $arr;
}

function get_single_order_fav($discount_all = '', $orderFavourable = array(), $type = 0)
{
	$discount = 0;
	$has_terrace = '';

	foreach ($orderFavourable as $key => $row) {
		$discount += $row['compute_discount']['discount'];
		$has_terrace .= $key . ',';
	}

	if ($has_terrace != '') {
		$has_terrace = substr($has_terrace, 0, -1);
		$has_terrace = explode(',', $has_terrace);
	}

	if (in_array(0, $has_terrace)) {
		$has_terrace = 1;
	}
	else {
		$has_terrace = 0;
	}

	$discount_all = number_format($discount_all, 2, '.', '');
	$discount = number_format($discount, 2, '.', '');
	$commonuse_discount = $discount_all - $discount;
	return array('discount' => $commonuse_discount, 'has_terrace' => $has_terrace);
}

function get_shop_name($ru_id = 0, $type = 0)
{
	$sql = 'SELECT shop_name, check_sellername, shopname_audit, shop_logo, brand_thumb FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = \'' . $ru_id . '\' LIMIT 1';
	$shopinfo = $GLOBALS['db']->getRow($sql);
	$sql = 'SELECT concat(shoprz_brandName, shopNameSuffix) as shop_name, shoprz_brandName, shopNameSuffix, rz_shopName, is_IM, self_run FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE user_id = \'' . $ru_id . '\' LIMIT 1';
	$shop_information = $GLOBALS['db']->getRow($sql);
	$shopinfo['self_run'] = $shop_information['self_run'];

	if ($type == 3) {
		$shop_information['shop_name'] = $shop_information['shoprz_brandName'];
		$shop_information['rz_shopName'] = str_replace(array('旗舰店', '专卖店', '专营店'), '', $shop_information['rz_shopName']);
	}

	if ($shopinfo['shopname_audit'] == 1) {
		if ($shopinfo['check_sellername'] == 1) {
			$shop_name = $shop_information['rz_shopName'];
		}
		else if ($shopinfo['check_sellername'] == 2) {
			$shop_name = $shopinfo['shop_name'];
		}
		else if (0 < $ru_id) {
			$shop_name = $shop_information['shop_name'];
		}
		else {
			$shop_name = $shopinfo['shop_name'];
		}
	}
	else {
		$shop_name = $shop_information['rz_shopName'];
	}

	if ($type == 1) {
		return $shop_name;
	}
	else if ($type == 2) {
		return $shopinfo;
	}
	else if ($type == 3) {
		return array('shop_name' => $shop_name, 'shopNameSuffix' => $shop_information['shopNameSuffix']);
	}
	else {
		return $shop_information;
	}
}

function get_cache_site_file($file = '', $var_arr = array())
{
	static $arr;

	if ($arr === NULL) {
		$data = read_static_cache($file);

		if ($data === false) {
			if (($file == 'category_tree') || ($file == 'category_tree1') || ($file == 'category_tree2')) {
				if (empty($var_arr)) {
					$arr = get_categories_tree_pro();
				}
				else {
					$arr = get_categories_tree_pro($var_arr[0], $var_arr[1]);
				}
			}
			else {
				$arr = $var_arr;
			}

			write_static_cache($file, $arr);
		}
		else {
			$arr = $data;
		}
	}

	return $arr;
}

function get_brand_info($id_name, $act = '', $selType = 0)
{
	if ($act == 'merchants_brands') {
		$select = 'bid as brand_id, brandName as brand_name, bank_name_letter as brand_letter, brandLogo as brand_logo, brand_desc, user_id';
		$idType = 'bid';
		$nameType = 'brandName';
		$table = 'merchants_shop_brand';
	}
	else {
		$select = '*';
		$idType = 'brand_id';
		$nameType = 'brand_name';
		$table = 'brand';
	}

	$where = '1';

	if ($selType == 1) {
		$where = $nameType . ' = \'' . $id_name . '\' AND audit_status = 1';
	}
	else {
		$where = $idType . ' = \'' . $id_name . '\'';
	}

	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where . ' LIMIT 1';
	return $GLOBALS['db']->getRow($sql);
}

function get_common_store_list()
{
	$sql = 'SELECT shop_id, user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE merchants_audit = 1';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['shop_id'] = $row['shop_id'];
		$arr[$key]['ru_id'] = $row['user_id'];
		$arr[$key]['store_name'] = get_shop_name($row['user_id'], 1);
	}

	return $arr;
}

function get_current_region_list($province_id = 1, $region_type = 1)
{
	$where = ' AND region_type = \'' . $region_type . '\'';
	$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE parent_id = \'' . $province_id . '\' ' . $where;
	return $GLOBALS['db']->getAll($sql);
}

function get_seller_shopinfo($ru_id = 0, $select = array())
{
	if ($select) {
		$select = implode(',', $select);
	}
	else {
		$select = 'province, city, kf_type, kf_ww, kf_qq, shop_name';
	}

	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = \'' . $ru_id . '\'';
	return $GLOBALS['db']->getRow($sql);
}

function get_suppliers_name($suppliers_id = 0)
{
	$sql = 'SELECT suppliers_id, suppliers_name FROM ' . $GLOBALS['ecs']->table('suppliers') . ' WHERE suppliers_id = \'' . $suppliers_id . '\'';
	return $GLOBALS['db']->getRow($sql);
}

function get_auto_manage_info($goods_id)
{
	$sql = 'SELECT starttime, endtime FROM ' . $GLOBALS['ecs']->table('auto_manage') . ' WHERE type = \'' . $type . '\' AND item_id = \'' . $goods_id . '\'';
	return $GLOBALS['db']->getRow($sql);
}

function get_user_buy_goods_order($goods_id, $user_id, $order_id)
{
	$sql = 'SELECT og.goods_attr_id, oi.add_time FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og, ' . $GLOBALS['ecs']->table('order_info') . ' AS oi' . ' WHERE og.order_id = oi.order_id AND oi.user_id = \'' . $user_id . '\' AND og.goods_id = \'' . $goods_id . '\' AND oi.order_id = \'' . $order_id . '\' limit 0,1';
	$buy_goods = $GLOBALS['db']->getRow($sql);
	$buy_goods['goods_attr'] = get_goods_attr_order($buy_goods['goods_attr_id']);
	$buy_goods['add_time'] = !empty($buy_goods['add_time']) ? local_date($GLOBALS['_CFG']['time_format'], $buy_goods['add_time']) : '';
	return $buy_goods;
}

function get_goods_attr_order($goods_attr_id)
{
	if ($goods_attr_id) {
		$attr = '';

		if (!empty($goods_attr_id)) {
			$fmt = '%s：%s <br/>';
			$sql = 'SELECT ga.goods_attr_id, a.attr_name, ga.attr_value ' . 'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS ga ' . ' left join ' . $GLOBALS['ecs']->table('attribute') . ' AS a ' . 'on a.attr_id = ga.attr_id ' . 'WHERE ' . db_create_in($goods_attr_id, 'ga.goods_attr_id') . ' ORDER BY a.sort_order, a.attr_id, ga.goods_attr_id';
			$res = $GLOBALS['db']->query($sql);

			while ($row = $GLOBALS['db']->fetchRow($res)) {
				$attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], '');
			}

			$attr = str_replace('[0]', '', $attr);
		}

		return $attr;
	}
}

function get_reply_list($goods_id, $comment_id, $type = 0, $reply_page = 1, $libType = 0, $reply_size = 2)
{
	if ($type == 1) {
		$sql = 'SELECT c.user_id, c.content, c.add_time, c.user_name FROM ' . $GLOBALS['ecs']->table('comment') . ' AS c ' . ' WHERE c.id_value = \'' . $goods_id . '\' AND c.parent_id = \'' . $comment_id . '\' AND c.user_id = \'' . $_SESSION['user_id'] . '\' AND status = 0 ORDER BY c.comment_id DESC';
		$reply_list = $GLOBALS['db']->getAll($sql);
	}
	else {
		require_once 'includes/cls_pager.php';
		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' AS c ' . ' WHERE c.id_value = \'' . $goods_id . '\' AND c.parent_id = \'' . $comment_id . '\' AND c.user_id > 0 AND status = 1';
		$reply_count = $GLOBALS['db']->getOne($sql);
		$id = '"' . $goods_id . '|' . $comment_id . '"';
		$reply_comment = new Pager($reply_count, $reply_size, '', $id, 0, $reply_page, 'reply_comment_gotoPage', 1, $libType, 1);
		$limit = $reply_comment->limit;
		$reply_pager = $reply_comment->fpage(array(0, 4, 5, 6, 9));
		$setFloorMax = $reply_comment->setFloorMax;

		if ($reply_size < $setFloorMax) {
			$setFloorMax += 1;
		}
		else {
			$setFloorMax = $reply_comment->pageCurrent + 1;
		}

		$sql = 'SELECT @rownum:=@rownum-1 AS floor, c.user_id, c.content, c.add_time, c.user_name FROM (SELECT @rownum:=' . $setFloorMax . ') r, ' . $GLOBALS['ecs']->table('comment') . ' AS c ' . ' WHERE c.id_value = \'' . $goods_id . '\' AND c.parent_id = \'' . $comment_id . '\' AND c.user_id > 0 AND status = 1 ORDER BY c.comment_id DESC ' . $limit;
		$reply_list = $GLOBALS['db']->getAll($sql);

		foreach ($reply_list as $key => $row) {
			$reply_list[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
			$reply_list[$key]['content'] = nl2br(str_replace('\\n', '<br />', htmlspecialchars($row['content'])));
		}
	}

	$arr = array('reply_list' => $reply_list, 'reply_pager' => $reply_pager, 'reply_count' => $reply_count, 'reply_size' => $reply_size);
	return $arr;
}

function get_discuss_all_list($goods_id = 0, $dis_type = 0, $reply_page = 1, $size = 40, $revType = 0, $sort = 'add_time', $did = 0)
{
	require_once 'includes/cls_pager.php';
	$where = '1';

	if ($dis_type == 4) {
		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' AS cmt ' . 'LEFT JOIN (SELECT comment_id,goods_id,comment_img FROM ' . $GLOBALS['ecs']->table('comment_img') . ' GROUP BY comment_id) cmt2 ON (cmt2.comment_id = cmt.comment_id) ' . 'WHERE cmt.id_value = \'' . $goods_id . '\' AND cmt2.comment_img != \'\' AND cmt.status = 1';
		$record_count = $GLOBALS['db']->getOne($sql);

		if (defined('THEME_EXTENSION')) {
			$pageType = 1;
			$id = '"' . $goods_id . '|' . $dis_type . '|' . $revType . '|' . $sort . '"';
		}
		else {
			$pageType = 0;
			$id = $goods_id;
		}

		$discuss = new Pager($record_count, $size, '', $id, 0, $reply_page, 'discuss_list_gotoPage', $pageType);
		$limit = $discuss->limit;
		$pager = $discuss->fpage(array(0, 4, 5, 6, 9));
		$sql = 'SELECT cmt.comment_id AS dis_id,cmt.id_value,cmt.useful,cmt.content,cmt.add_time,cmt.user_name,cmt2.comment_img FROM ' . $GLOBALS['ecs']->table('comment') . ' AS cmt ' . 'LEFT JOIN (SELECT comment_id,goods_id,comment_img FROM ' . $GLOBALS['ecs']->table('comment_img') . ' GROUP BY comment_id) cmt2 ON (cmt2.comment_id = cmt.comment_id) ' . 'WHERE cmt.id_value = \'' . $goods_id . '\' AND cmt2.comment_img != \'\' AND cmt.status = 1 AND cmt.comment_id <> \'' . $did . '\' ' . $limit;
		$res = $GLOBALS['db']->getAll($sql);
		$arr = array();

		foreach ($res as $key => $row) {
			$row['user_name'] = setAnonymous($row['user_name']);
			$arr[$key] = $row;
			$arr[$key]['dis_title'] = nl2br(str_replace('\\n', '<br />', htmlspecialchars($row['content'])));
			$arr[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
			$arr[$key]['reply_num'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('discuss_circle') . 'WHERE parent_id = \'' . $row['dis_id'] . '\'');
			$arr[$key]['dis_browse_num'] = $row['useful'];
			$arr[$key]['dis_type'] = 4;
		}
	}
	else {
		if (0 < $dis_type) {
			$where .= ' AND DC1.dis_type = \'' . $dis_type . '\'';
		}

		$id = '"' . $goods_id . '|' . $dis_type . '|' . $revType . '|' . $sort . '"';
		$record_count = get_discuss_type_count($goods_id, $dis_type);
		$discuss = new Pager($record_count, $size, '', $id, 0, $reply_page, 'discuss_list_gotoPage', 1);
		$limit = $discuss->limit;
		$pager = $discuss->fpage(array(0, 4, 5, 6, 9));

		if ($sort != 'reply_num') {
			$sort = 'DC1.' . $sort;
		}

		$sql = 'SELECT DC1.dis_id, DC1.dis_type, DC1.dis_title, DC1.user_name, DC1.add_time, DC1.dis_browse_num, u.nick_name, ' . '(SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('discuss_circle') . ' AS DC2' . ' WHERE DC2.parent_id = DC1.dis_id) AS reply_num' . ' FROM ' . $GLOBALS['ecs']->table('discuss_circle') . ' AS DC1' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id = DC1.user_id ' . ' WHERE ' . $where . ' AND DC1.parent_id = 0 AND DC1.goods_id = \'' . $goods_id . '\' AND DC1.dis_id <> \'' . $did . '\' ORDER BY ' . $sort . ' DESC ' . $limit;
		$res = $GLOBALS['db']->getAll($sql);
		$arr = array();

		foreach ($res as $key => $row) {
			$row['user_name'] = setAnonymous($row['user_name']);
			$arr[$key] = $row;
			$arr[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
			$arr[$key]['reply_num'] = $row['reply_num'];
		}
	}

	return array('list' => $arr, 'pager' => $pager, 'record_count' => $record_count);
}

function get_discuss_type_count($goods_id, $dis_type = 0)
{
	$where = '1';

	if (0 < $dis_type) {
		$where .= ' AND dis_type = \'' . $dis_type . '\'';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('discuss_circle') . ' WHERE ' . $where . ' AND parent_id = 0 AND goods_id = \'' . $goods_id . '\'';
	return $GLOBALS['db']->getOne($sql);
}

function get_commentImg_count($goods_id)
{
	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' AS cmt ' . 'LEFT JOIN (SELECT comment_id,goods_id,comment_img FROM ' . $GLOBALS['ecs']->table('comment_img') . ' GROUP BY comment_id) cmt2 ON (cmt2.comment_id = cmt.comment_id) ' . 'WHERE cmt.id_value = \'' . $goods_id . '\' AND cmt2.comment_img != \'\' AND cmt.status = 1';
	$num = $GLOBALS['db']->getOne($sql);
	return $num;
}

function get_history_goods($goods_id = 0, $warehouse_id = 0, $area_id = 0)
{
	$arr = array();

	if (!empty($_COOKIE['ECS']['history'])) {
		$where = db_create_in($_COOKIE['ECS']['history'], 'g.goods_id');

		if ($GLOBALS['_CFG']['review_goods'] == 1) {
			$where .= ' AND g.review_status > 2 ';
		}

		$leftJoin = '';
		$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ';
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ';

		if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
			$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
			$where .= ' and lag.region_id = \'' . $area_id . '\' ';
		}

		if (0 < $goods_id) {
			$where .= ' AND g.goods_id <> \'' . $goods_id . '\' ';
		}

		$sql = 'SELECT g.goods_id, g.user_id, g.goods_name, g.goods_thumb, g.goods_img, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ' . 'g.market_price, g.sales_volume, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'g.promote_start_date, g.promote_end_date' . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . $leftJoin . ' WHERE ' . $where . ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 order by INSTR(\'' . $_COOKIE['ECS']['history'] . '\',g.goods_id) limit 0,10';
		$res = $GLOBALS['db']->query($sql);

		while ($row = $GLOBALS['db']->fetchRow($res)) {
			if (0 < $row['promote_price']) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			}
			else {
				$promote_price = 0;
			}

			$price_other = array('market_price' => $row['market_price'], 'org_price' => $row['org_price'], 'shop_price' => $row['shop_price'], 'promote_price' => $promote_price);
			$price_info = get_goods_one_attr_price($row['goods_id'], $warehouse_id, $area_id, $price_other);
			$row = (!empty($row) ? array_merge($row, $price_info) : $row);
			$promote_price = $row['promote_price'];
			$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
			$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
			$arr[$row['goods_id']]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
			$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
			$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			$arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
			$arr[$row['goods_id']]['shop_name'] = get_shop_name($row['user_id'], 1);
			$arr[$row['goods_id']]['shopUrl'] = build_uri('merchants_store', array('urid' => $row['user_id']));
			$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
			$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
			$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		}
	}

	return $arr;
}

function flow_drop_cart_goods($id, $step = '')
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE rec_id = \'' . $id . '\'';
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		if ($row['extension_code'] == 'package_buy') {
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . 'AND rec_id = \'' . $id . '\' LIMIT 1';
		}
		else {
			if (($row['parent_id'] == 0) && ($row['is_gift'] == 0)) {
				$sql = "SELECT c.rec_id\r\n                    FROM " . $GLOBALS['ecs']->table('cart') . ' AS c, ' . $GLOBALS['ecs']->table('group_goods') . ' AS gg, ' . $GLOBALS['ecs']->table('goods') . " AS g\r\n                    WHERE gg.parent_id = '" . $row['goods_id'] . "'\r\n                    AND c.goods_id = gg.goods_id\r\n                    AND c.parent_id = '" . $row['goods_id'] . "'\r\n                    AND c.extension_code <> 'package_buy'\r\n                    AND gg.goods_id = g.goods_id\r\n                    AND g.is_alone_sale = 0 AND c.group_id='" . $row['group_id'] . '\'';
				$res = $GLOBALS['db']->query($sql);
				$_del_str = $id . ',';

				while ($id_alone_sale_goods = $GLOBALS['db']->fetchRow($res)) {
					$_del_str .= $id_alone_sale_goods['rec_id'] . ',';
				}

				$_del_str = trim($_del_str, ',');

				if ($row['group_id']) {
					$where = ' AND group_id=\'' . $row['group_id'] . '\'';
				}

				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . 'AND (rec_id IN (' . $_del_str . ') OR parent_id = \'' . $row['goods_id'] . '\' OR is_gift <> 0) ' . $where;
			}
			else {
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . 'AND rec_id = \'' . $id . '\' LIMIT 1';
			}
		}

		$GLOBALS['db']->query($sql);

		if ($step == 'drop_to_collect') {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('collect_goods') . ' WHERE user_id=\'' . $_SESSION['user_id'] . '\' AND goods_id = \'' . $row['goods_id'] . '\'';

			if ($GLOBALS['db']->GetOne($sql) < 1) {
				$time = gmtime();
				$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('collect_goods') . ' (user_id, goods_id, add_time)' . 'VALUES (\'' . $_SESSION['user_id'] . '\', \'' . $row['goods_id'] . '\', \'' . $time . '\')';
				$GLOBALS['db']->query($sql);
			}
		}
	}

	flow_clear_cart_alone();
}

function flow_clear_cart_alone()
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
		$c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
		$c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	$sql = "SELECT c.rec_id, gg.parent_id\r\n            FROM " . $GLOBALS['ecs']->table('cart') . " AS c\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('group_goods') . " AS gg ON c.goods_id = gg.goods_id\r\n                LEFT JOIN" . $GLOBALS['ecs']->table('goods') . " AS g ON c.goods_id = g.goods_id\r\n            WHERE " . $c_sess . " AND c.extension_code <> 'package_buy'\r\n            AND gg.parent_id > 0\r\n            AND g.is_alone_sale = 0";
	$res = $GLOBALS['db']->query($sql);
	$rec_id = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$rec_id[$row['rec_id']][] = $row['parent_id'];
	}

	if (empty($rec_id)) {
		return NULL;
	}

	$sql = "SELECT DISTINCT goods_id\r\n            FROM " . $GLOBALS['ecs']->table('cart') . "\r\n            WHERE " . $sess_id . ' AND extension_code <> \'package_buy\'';
	$res = $GLOBALS['db']->query($sql);
	$cart_good = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$cart_good[] = $row['goods_id'];
	}

	if (empty($cart_good)) {
		return NULL;
	}

	$del_rec_id = '';

	foreach ($rec_id as $key => $value) {
		foreach ($value as $v) {
			if (in_array($v, $cart_good)) {
				continue 2;
			}
		}

		$del_rec_id = $key . ',';
	}

	$del_rec_id = trim($del_rec_id, ',');

	if ($del_rec_id == '') {
		return NULL;
	}

	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . "\r\n            WHERE " . $sess_id . ' AND rec_id IN (' . $del_rec_id . ')';
	$GLOBALS['db']->query($sql);
}

function generate_user_sn($user_id)
{
	$user_sn = 'SC' . str_repeat('0', 6 - strlen($user_id)) . $user_id;
	$sql = 'SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_name LIKE \'' . mysql_like_quote($user_id) . '%\' AND user_id <> \'' . $user_id . '\' ' . ' ORDER BY LENGTH(user_name) DESC';
	$sn_list = $GLOBALS['db']->getCol($sql);

	if (in_array($user_sn, $sn_list)) {
		$max = pow(10, (strlen($sn_list[0]) - strlen($user_sn)) + 1) - 1;
		$new_sn = $user_sn . mt_rand(0, $max);

		while (in_array($new_sn, $sn_list)) {
			$new_sn = $user_sn . mt_rand(0, $max);
		}

		$user_sn = $new_sn;
	}

	return $user_sn;
}

function presale_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
{
	static $res;

	if ($res === NULL) {
		$data = read_static_cache('presale_cat_releate');

		if ($data === false) {
			$sql = 'SELECT p.cat_id, p.cat_name, p.parent_id, p.sort_order, COUNT(s.parent_id) AS has_children ' . 'FROM ' . $GLOBALS['ecs']->table('presale_cat') . ' AS p ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('presale_cat') . ' AS s ON s.parent_id = p.cat_id ' . 'GROUP BY p.cat_id ' . 'ORDER BY p.parent_id, p.sort_order ASC';
			$res = $GLOBALS['db']->getAll($sql);
			$sql = 'SELECT pa.cat_id, COUNT(*) AS goods_num ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('presale_activity') . ' AS pa ON pa.goods_id = g.goods_id ' . ' WHERE is_delete = 0 AND is_on_sale = 0 ' . ' GROUP BY pa.cat_id';
			$res2 = $GLOBALS['db']->getAll($sql);
			$sql = 'SELECT pc.cat_id, COUNT(*) AS goods_num FROM ' . $GLOBALS['ecs']->table('presale_cat') . ' AS pc ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('presale_activity') . ' AS pa ON pc.cat_id = pa.cat_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = pa.goods_id ' . 'WHERE g.is_delete = 0 AND g.is_on_sale = 0 GROUP BY pa.cat_id';
			$res3 = $GLOBALS['db']->getAll($sql);
			$newres = array();

			foreach ($res2 as $k => $v) {
				$newres[$v['cat_id']] = $v['goods_num'];

				foreach ($res3 as $ks => $vs) {
					if ($v['cat_id'] == $vs['cat_id']) {
						$newres[$v['cat_id']] = $v['goods_num'] + $vs['goods_num'];
					}
				}
			}

			foreach ($res as $k => $v) {
				$res[$k]['goods_num'] = !empty($newres[$v['cat_id']]) ? $newres[$v['cat_id']] : 0;
			}

			if (count($res) <= 1000) {
				write_static_cache('presale_cat_releate', $res);
			}
		}
		else {
			$res = $data;
		}
	}

	if (empty($res) == true) {
		return $re_type ? '' : array();
	}

	$options = presale_cat_options($cat_id, $res);
	$children_level = 99999;

	if ($is_show_all == false) {
		foreach ($options as $key => $val) {
			if ($children_level < $val['level']) {
				unset($options[$key]);
			}
			else if ($val['is_show'] == 0) {
				unset($options[$key]);

				if ($val['level'] < $children_level) {
					$children_level = $val['level'];
				}
			}
			else {
				$children_level = 99999;
			}
		}
	}

	if (0 < $level) {
		if ($cat_id == 0) {
			$end_level = $level;
		}
		else {
			$first_item = reset($options);
			$end_level = $first_item['level'] + $level;
		}

		foreach ($options as $key => $val) {
			if ($end_level <= $val['level']) {
				unset($options[$key]);
			}
		}
	}

	if ($re_type == true) {
		$select = '';

		foreach ($options as $var) {
			$select .= '<option value="' . $var['cat_id'] . '" ';
			$select .= ($selected == $var['cat_id'] ? 'selected=\'ture\'' : '');
			$select .= '>';

			if (0 < $var['level']) {
				$select .= str_repeat('&nbsp;', $var['level'] * 4);
			}

			$select .= htmlspecialchars(addslashes($var['cat_name']), ENT_QUOTES) . '</option>';
		}

		return $select;
	}
	else {
		foreach ($options as $key => $value) {
		}

		return $options;
	}
}

function presale_cat_options($spec_cat_id, $arr)
{
	static $cat_options = array();

	if (isset($cat_options[$spec_cat_id])) {
		return $cat_options[$spec_cat_id];
	}

	if (!isset($cat_options[0])) {
		$level = $last_cat_id = 0;
		$options = $cat_id_array = $level_array = array();
		$data = read_static_cache('presale_cat_option_static');

		if ($data === false) {
			while (!empty($arr)) {
				foreach ($arr as $key => $value) {
					$cat_id = $value['cat_id'];
					if (($level == 0) && ($last_cat_id == 0)) {
						if (0 < $value['parent_id']) {
							break;
						}

						$options[$cat_id] = $value;
						$options[$cat_id]['level'] = $level;
						$options[$cat_id]['id'] = $cat_id;
						$options[$cat_id]['name'] = $value['cat_name'];
						unset($arr[$key]);

						if ($value['has_children'] == 0) {
							continue;
						}

						$last_cat_id = $cat_id;
						$cat_id_array = array($cat_id);
						$level_array[$last_cat_id] = ++$level;
						continue;
					}

					if ($value['parent_id'] == $last_cat_id) {
						$options[$cat_id] = $value;
						$options[$cat_id]['level'] = $level;
						$options[$cat_id]['id'] = $cat_id;
						$options[$cat_id]['name'] = $value['cat_name'];
						unset($arr[$key]);

						if (0 < $value['has_children']) {
							if (end($cat_id_array) != $last_cat_id) {
								$cat_id_array[] = $last_cat_id;
							}

							$last_cat_id = $cat_id;
							$cat_id_array[] = $cat_id;
							$level_array[$last_cat_id] = ++$level;
						}
					}
					else if ($last_cat_id < $value['parent_id']) {
						break;
					}
				}

				$count = count($cat_id_array);

				if (1 < $count) {
					$last_cat_id = array_pop($cat_id_array);
				}
				else if ($count == 1) {
					if ($last_cat_id != end($cat_id_array)) {
						$last_cat_id = end($cat_id_array);
					}
					else {
						$level = 0;
						$last_cat_id = 0;
						$cat_id_array = array();
						continue;
					}
				}

				if ($last_cat_id && isset($level_array[$last_cat_id])) {
					$level = $level_array[$last_cat_id];
				}
				else {
					$level = 0;
				}
			}

			if (count($options) <= 2000) {
				write_static_cache('presale_cat_option_static', $options);
			}
		}
		else {
			$options = $data;
		}

		$cat_options[0] = $options;
	}
	else {
		$options = $cat_options[0];
	}

	if (!$spec_cat_id) {
		return $options;
	}
	else {
		if (empty($options[$spec_cat_id])) {
			return array();
		}

		$spec_cat_id_level = $options[$spec_cat_id]['level'];

		foreach ($options as $key => $value) {
			if ($key != $spec_cat_id) {
				unset($options[$key]);
			}
			else {
				break;
			}
		}

		$spec_cat_id_array = array();

		foreach ($options as $key => $value) {
			if ((($spec_cat_id_level == $value['level']) && ($value['cat_id'] != $spec_cat_id)) || ($value['level'] < $spec_cat_id_level)) {
				break;
			}
			else {
				$spec_cat_id_array[$key] = $value;
			}
		}

		$cat_options[$spec_cat_id] = $spec_cat_id_array;
		return $spec_cat_id_array;
	}
}

function presale_info($presale_id, $current_num = 0, $user_id = 0, $path = '')
{
	$where = '';

	if (empty($path)) {
		$where = ' AND b.review_status = 3 ';
	}

	$presale_id = intval($presale_id);
	$sql = 'SELECT b.*, b.cat_id AS pa_catid,g.goods_name, g.shop_price, g.user_id, g.goods_id, g.goods_product_tag, g.cat_id, b.review_status, b.review_content, ' . 'g.xiangou_start_date, g.xiangou_end_date, g.xiangou_end_date, g.xiangou_num, g.is_xiangou, b.user_id AS ru_id ' . 'FROM ' . $GLOBALS['ecs']->table('presale_activity') . ' AS b ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON b.goods_id = g.goods_id ' . 'WHERE b.act_id = \'' . $presale_id . '\' ' . $where;
	$presale = $GLOBALS['db']->getRow($sql);

	if (empty($presale)) {
		return array();
	}

	$presale['act_name'] = !empty($presale['act_name']) ? $presale['act_name'] : $presale['goods_name'];
	$presale['formated_start_date'] = local_date('Y-m-d H:i', $presale['start_time']);
	$presale['formated_end_date'] = local_date('Y-m-d H:i', $presale['end_time']);
	$presale['formated_pay_start_date'] = local_date('Y-m-d H:i', $presale['pay_start_time']);
	$presale['formated_pay_end_date'] = local_date('Y-m-d H:i', $presale['pay_end_time']);
	$presale['formated_deposit'] = price_format($presale['deposit'], false);
	$presale['final_payment'] = $presale['shop_price'] - $presale['deposit'];
	$presale['formated_final_payment'] = price_format($presale['final_payment'], false);
	$stat = presale_stat($presale_id, $presale['deposit'], $user_id);
	$presale = array_merge($presale, $stat);
	$presale['status'] = presale_status($presale);

	if (isset($GLOBALS['_LANG']['gbs'][$presale['status']])) {
		$presale['status_desc'] = $GLOBALS['_LANG']['gbs'][$presale['status']];
	}

	$presale['start_time'] = $presale['formated_start_date'];
	$presale['end_time'] = $presale['formated_end_date'];
	$presale['pay_start_time'] = $presale['formated_pay_start_date'];
	$presale['pay_end_time'] = $presale['formated_pay_end_date'];

	if ($presale['goods_product_tag']) {
		$impression_list = (!empty($presale['goods_product_tag']) ? explode(',', $presale['goods_product_tag']) : '');

		foreach ($impression_list as $kk => $vv) {
			$tag[$kk]['txt'] = $vv;
			$tag[$kk]['num'] = comment_goodstag_num($presale['goods_id'], $vv);
		}

		$presale['impression_list'] = $tag;
	}

	$presale['collect_count'] = get_collect_goods_user_count($presale['goods_id']);
	return $presale;
}

function presale_stat($presale_id, $deposit, $user_id = 0)
{
	$presale_id = intval($presale_id);
	$sql = 'SELECT goods_id ' . 'FROM ' . $GLOBALS['ecs']->table('presale_activity') . 'WHERE act_id = \'' . $presale_id . '\' AND review_status = 3 ';
	$goods_id = $GLOBALS['db']->getOne($sql);
	$where = '';

	if ($user_id) {
		$where .= ' AND o.user_id = \'' . $user_id . '\'';
	}

	$sql = 'SELECT COUNT(*) AS total_order, SUM(g.goods_number) AS total_goods ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o, ' . $GLOBALS['ecs']->table('order_goods') . ' AS g ' . ' WHERE o.order_id = g.order_id ' . 'AND o.extension_code = \'presale\' ' . 'AND o.extension_id = \'' . $presale_id . '\' ' . 'AND g.goods_id = \'' . $goods_id . '\' ' . 'AND (order_status = \'' . OS_CONFIRMED . '\' OR order_status = \'' . OS_UNCONFIRMED . '\')';
	$stat = $GLOBALS['db']->getRow($sql);

	if ($stat['total_order'] == 0) {
		$stat['total_goods'] = 0;
	}

	$deposit = floatval($deposit);
	if ((0 < $deposit) && (0 < $stat['total_order'])) {
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

function presale_status($presale)
{
	$now = gmtime();

	if ($presale['is_finished'] == 0) {
		if ($now < $presale['start_time']) {
			$status = GBS_PRE_START;
		}
		else if ($presale['end_time'] < $now) {
			$status = GBS_FINISHED;
		}
		else if ($presale['is_finished'] == 0) {
			$status = GBS_UNDER_WAY;
		}
		else {
			$status = GBS_FINISHED;
		}
	}
	else if ($presale['is_finished'] == GBS_SUCCEED) {
		$status = GBS_SUCCEED;
	}
	else if ($presale['is_finished'] == GBS_FAIL) {
		$status = GBS_FAIL;
	}

	return $status;
}

function wholesale_status($wholesale)
{
	$now = gmtime();

	if ($now < $wholesale['start_time']) {
		$status = GBS_PRE_START;
	}
	else if ($wholesale['end_time'] < $now) {
		$status = GBS_FINISHED;
	}
	else if ($wholesale['is_finished'] == 0) {
		$status = GBS_UNDER_WAY;
	}
	else {
		$status = GBS_FINISHED;
	}

	return $status;
}

function get_order_goods_buy_list($warehouse_id = 0, $area_id = 0)
{
	$where = '1';
	$leftJoin = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ';
	$where .= ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id) = 0 ';
	$sql = 'SELECT g.goods_id, g.goods_name, g.user_id AS ru_id, g.sales_volume, g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img, ' . 'IF(g.model_inventory < 1, g.goods_number, IF(g.model_inventory < 2, wg.region_number, wag.region_number)) as goods_number, ' . 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price,' . 'IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\') AS rank_price ' . 'FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON og.goods_id = g.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ON og.order_id = oi.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\'' . $leftJoin . ' WHERE ' . $where . ' AND oi.user_id = \'' . $_SESSION['user_id'] . '\' GROUP BY og.goods_id ORDER BY g.sales_volume DESC LIMIT 0,18';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['goods_id'] = $row['goods_id'];
		$arr[$key]['goods_name'] = $row['goods_name'];
		$arr[$key]['goods_img'] = get_image_path($goods_id, $row['goods_img']);
		$arr[$key]['goods_thumb'] = get_image_path($goods_id, $row['goods_thumb'], true);

		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$arr[$key]['sales_volume'] = $row['sales_volume'];
		$arr[$key]['shop_price'] = price_format($row['shop_price']);
		$arr[$key]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$arr[$key]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$arr[$key]['shop_name'] = get_shop_name($row['ru_id'], 1);
		$build_uri = array('urid' => $row['ru_id'], 'append' => $arr[$key]['shop_name']);
		$domain_url = get_seller_domain_url($row['ru_id'], $build_uri);
		$arr[$key]['store_url'] = $domain_url['domain_name'];
	}

	return $arr;
}

function get_guess_goods($user_id, $history = 0, $page = 1, $limit = 5, $warehouse_id = 0, $area_id = 0)
{
	$order_idArr = $finished_goods = $link_cats = array();
	$start = (1 < $page ? $page - 1 : 0) * $limit;
	$leftJoin = ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ';

	if (empty($history)) {
		$sql = 'SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE user_id = \'' . $user_id . '\' ORDER BY order_id DESC LIMIT 5';
		$order_arr = $GLOBALS['db']->getAll($sql);

		if ($order_arr) {
			foreach ($order_arr as $key => $val) {
				$order_idArr[] = $val['order_id'];
			}

			$order_str = db_create_in($order_idArr, 'og.order_id');
			$sql = 'SELECT g.goods_id, g.cat_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = og.goods_id ' . 'WHERE ' . $order_str . ' GROUP BY g.goods_id DESC';
			$cat_All = $GLOBALS['db']->getAll($sql);

			foreach ($cat_All as $kk => $vv) {
				$finished_goodsStr .= '\'' . $vv['goods_id'] . '\',';
				$link_cats[] = $vv['cat_id'];
			}

			$finished_goodsStr = substr($finished_goodsStr, 0, -1);

			if (empty($finished_goodsStr)) {
				$finished_goodsStr = '\'\'';
			}

			$link_cats = array_unique($link_cats);
			$link_cats_str = db_create_in($link_cats, 'g.cat_id');
			$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, ' . 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price,' . ' g.sales_volume, g.promote_start_date, g.promote_end_date FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . ' WHERE ' . $link_cats_str . ' AND g.goods_id NOT IN (' . $finished_goodsStr . ') AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ORDER BY g.sales_volume DESC  LIMIT 8';
			$query = $GLOBALS['db']->query($sql);
		}
	}
	else if (!empty($_COOKIE['ECS']['history'])) {
		$where = db_create_in($_COOKIE['ECS']['history'], 'goods_id');
		$sql = 'SELECT cat_id,goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE ' . $where . ' AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0';
		$query = $GLOBALS['db']->query($sql);

		while ($row = $GLOBALS['db']->fetch_array($query)) {
			$cat_arr[] = $row['cat_id'];
			$goods_str .= '\'' . $row['goods_id'] . '\',';
		}

		$goods_str = substr($goods_str, 0, -1);
		$where_cat = db_create_in(array_unique($cat_arr), 'g.cat_id');

		if (!empty($goods_str)) {
			$goods_str = 'AND g.goods_id NOT IN (' . $goods_str . ')';
		}

		$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, ' . 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price,' . ' g.sales_volume, g.user_id, g.promote_start_date, g.promote_end_date FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . ' WHERE ' . $where_cat . ' ' . $goods_str . ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.sales_volume > 0 LIMIT ' . $start . ', ' . $limit;
		$query = $GLOBALS['db']->query($sql);
	}

	if ((empty($guess_goods) || (count($guess_goods) < $limit)) && ($history == 1)) {
		$guess_goods = array();
		$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.user_id, ' . 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price,' . 'g.sales_volume, g.promote_start_date, g.promote_end_date FROM ' . $GLOBALS['ecs']->table('goods') . 'as g' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . ' WHERE  (g.sales_volume > 0 OR g.is_hot = 1) AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ORDER BY g.sales_volume DESC LIMIT ' . $start . ', ' . $limit;
		$query = $GLOBALS['db']->query($sql);
	}

	while ($row = $GLOBALS['db']->fetch_array($query)) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$price_other = array('market_price' => $row['market_price'], 'org_price' => $row['org_price'], 'shop_price' => $row['shop_price'], 'promote_price' => $promote_price);
		$price_info = get_goods_one_attr_price($row['goods_id'], $warehouse_id, $area_id, $price_other);
		$row = (!empty($row) ? array_merge($row, $price_info) : $row);
		$promote_price = $row['promote_price'];
		$guess_goods[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$guess_goods[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$guess_goods[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
		$guess_goods[$row['goods_id']]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$guess_goods[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$guess_goods[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$guess_goods[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$guess_goods[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$guess_goods[$row['goods_id']]['shop_name'] = get_shop_name($row['user_id'], 1);
		$guess_goods[$row['goods_id']]['shopUrl'] = build_uri('merchants_store', array('urid' => $row['user_id']));
		$sql = 'SELECT AVG(comment_rank) FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value = \'' . $row['goods_id'] . '\'';
		$comment_rank = $GLOBALS['db']->getOne($sql);

		if ($comment_rank) {
			$guess_goods[$row['goods_id']]['comment_percent'] = round(($comment_rank / 5) * 100, 1);
		}
		else {
			$guess_goods[$row['goods_id']]['comment_percent'] = 100;
		}
	}

	return $guess_goods;
}

function get_guess_store($user_id, $limit)
{
	$store_list = array();

	if ($user_id) {
		$sql = 'SELECT ru_id FROM ' . $GLOBALS['ecs']->table('collect_store') . ' WHERE user_id = \'' . $user_id . '\' LIMIT ' . $limit;
		$store_list = $GLOBALS['db']->getAll($sql);
	}

	if (empty($store_list) || (count($store_list) < 4)) {
		$sql = 'SELECT SUM(goods_number) AS total,ru_id, rec_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE ru_id > 0 GROUP BY ru_id ORDER BY total DESC LIMIT ' . $limit;
		$row = $GLOBALS['db']->getAll($sql);
		$ru_id = '';

		foreach ($row as $k => $v) {
			$ru_id .= $v['ru_id'] . ',';
		}

		$ru_id = (!empty($ru_id) ? substr($ru_id, 0, -1) : '\'\'');

		if ($row) {
			$sql = 'SELECT ss.ru_id, ss.street_thumb, ss.brand_thumb FROM' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS msi ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS ss ON msi.user_id = ss.ru_id ' . ' WHERE 1 AND msi.user_id IN(' . $ru_id . ') ORDER BY sort_order LIMIT ' . $limit;
			$row = $GLOBALS['db']->getAll($sql);
		}
	}
	else {
		$ru_id = '';

		foreach ($store_list as $key => $row) {
			$ru_id .= $row['ru_id'] . ',';
		}

		$ru_id = (!empty($ru_id) ? substr($ru_id, 0, -1) : '\'\'');
		$sql = 'SELECT ss.ru_id, ss.street_thumb, ss.brand_thumb FROM' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS msi ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS ss ON msi.user_id = ss.ru_id ' . ' WHERE 1 AND msi.user_id IN(' . $ru_id . ') ORDER BY sort_order LIMIT ' . $limit;
		$row = $GLOBALS['db']->getAll($sql);
	}

	foreach ($row as $key => $val) {
		if (isset($val['rec_id'])) {
			$sql = 'SELECT street_thumb,brand_thumb FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . '  WHERE ru_id = \'' . $val['ru_id'] . '\'';
			$shopinfo = $GLOBALS['db']->getRow($sql);
		}
		else {
			$shopinfo = array('street_thumb' => $val['street_thumb'], 'brand_thumb' => $val['brand_thumb']);
		}

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();
			$shopinfo['street_thumb'] = $bucket_info['endpoint'] . $val['street_thumb'];
			$shopinfo['brand_thumb'] = $bucket_info['endpoint'] . $val['brand_thumb'];
		}

		$shopinfo['shop_name'] = get_shop_name($val['ru_id'], 1);
		$build_uri = array('urid' => $val['ru_id'], 'append' => $shopinfo['shop_name']);
		$domain_url = get_seller_domain_url($val['ru_id'], $build_uri);
		$shopinfo['store_url'] = $domain_url['domain_name'];
		$store_list[$key] = $shopinfo;

		if (!$shopinfo['shop_name']) {
			unset($store_list[$key]);
		}
	}

	return $store_list;
}

function get_goods_comment_count($goods_id, $cmtType = 0)
{
	if ($cmtType == 1) {
		$where = ' AND comment_rank in(5,4)';
	}
	else if ($cmtType == 2) {
		$where = ' AND comment_rank in(3,2)';
	}
	else if ($cmtType == 3) {
		$where = ' AND comment_rank = 1';
	}
	else {
		$where = '';
	}

	$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value = \'' . $goods_id . '\' AND comment_type = \'0\' AND status = 1 AND parent_id = 0 ' . $where);
	return $count;
}

function comment_goodstag_num($goods_id = 0, $txt = '')
{
	$txt = (!empty($txt) ? trim($txt) : '');
	$sql = 'SELECT goods_tag FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value = \'' . $goods_id . '\'';
	$res = $GLOBALS['db']->query($sql);
	$str = '';

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if ($row['goods_tag']) {
			$str .= $row['goods_tag'] . ',';
		}
	}

	if ($str && $txt) {
		$str = substr($str, 0, -1);
		$num = substr_count($str, $txt);
	}
	else {
		$num = 0;
	}

	return $num;
}

function get_collect_goods_user_count($goods_id)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('collect_goods') . ' WHERE goods_id = \'' . $goods_id . '\'';
	return $GLOBALS['db']->getOne($sql);
}

function get_collect_user_goods($goods_id)
{
	if (0 < $_SESSION['user_id']) {
		$sql = 'SELECT rec_id FROM ' . $GLOBALS['ecs']->table('collect_goods') . ' WHERE goods_id = \'' . $goods_id . '\' AND user_id = \'' . $_SESSION['user_id'] . '\'';
		return $GLOBALS['db']->getOne($sql, true);
	}
}

function get_collect_brand_user_count($brand_id, $ru_id = 0)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('collect_brand') . ' WHERE brand_id = \'' . $brand_id . '\' AND ru_id = \'' . $ru_id . '\'';
	return $GLOBALS['db']->getOne($sql);
}

function get_collect_user_brand($brand_id, $ru_id = 0)
{
	$sql = 'SELECT rec_id FROM ' . $GLOBALS['ecs']->table('collect_brand') . ' WHERE brand_id = \'' . $brand_id . '\' AND ru_id = \'' . $ru_id . '\' AND user_id = \'' . $_SESSION['user_id'] . '\'';
	return $GLOBALS['db']->getOne($sql, true);
}

function get_self_point($district, $point_id = 0, $limit = 100)
{
	$where = '';
	$shipping_dateStr = (isset($_SESSION['flow_consignee']['shipping_dateStr']) ? trim($_SESSION['flow_consignee']['shipping_dateStr']) : '');

	if (0 < $point_id) {
		$where = 'sp.id = \'' . $point_id . '\'';
	}
	else {
		$where = 'ar.region_id = \'' . $district . '\'';
	}

	$sql = 'SELECT ar.shipping_area_id,ar.region_id ,sp.id as point_id,sp.name,sp.mobile,sp.address,sp.anchor,sa.shipping_id,ss.shipping_code,cr.parent_id as city FROM ' . $GLOBALS['ecs']->table('shipping_point') . ' AS sp ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('area_region') . ' AS ar ON ar.shipping_area_id = sp.shipping_area_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('shipping_area') . ' AS sa ON sa.shipping_area_id = sp.shipping_area_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('shipping') . ' AS ss ON ss.shipping_id = sa.shipping_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS cr ON cr.region_id = ar.region_id ' . 'WHERE ' . $where . ' LIMIT ' . $limit;
	$list = $GLOBALS['db']->getAll($sql);

	foreach ($list as $key => $val) {
		if ((0 < $point_id) && ($val['point_id'] == $point_id)) {
			$list[$key]['is_check'] = 1;
		}

		if ($shipping_dateStr) {
			$list[$key]['shipping_dateStr'] = $shipping_dateStr;
		}
		else {
			$list[$key]['shipping_dateStr'] = date('m', strtotime(' +1day')) . '月' . date('d', strtotime(' +1day')) . '日&nbsp;【周' . transition_date(date('Y-m-d', strtotime(' +1day'))) . '】';
		}
	}

	return $list;
}

function transition_date($date)
{
	$arr_week = array('日', '一', '二', '三', '四', '五', '六');
	$datearr = explode('-', $date);
	$year = $datearr[0];
	$month = sprintf('%02d', $datearr[1]);
	$day = sprintf('%02d', $datearr[2]);
	$hour = $minute = $second = 0;
	$dayofweek = mktime($hour, $minute, $second, $month, $day, $year);
	$week = date('w', $dayofweek);
	return $arr_week[$week];
}

function get_server_ip()
{
	if (isset($_SERVER)) {
		$server_addr = (!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '');
		$local_addr = (!empty($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : '');

		if ($server_addr) {
			$server_ip = $server_addr;
		}
		else {
			$server_ip = $local_addr;
		}
	}
	else {
		$server_ip = getenv('SERVER_ADDR');
	}

	return $server_ip;
}

function sc_guid()
{
	if (function_exists('com_create_guid') === true) {
		return trim(com_create_guid(), '{}');
	}

	return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

function get_bucket_info()
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('oss_configure') . ' WHERE is_use = 1 LIMIT 1';
	$res = $GLOBALS['db']->getRow($sql);
	$http = $GLOBALS['ecs']->http();

	if ($res) {
		$regional = substr($res['regional'], 0, 2);
		if (($regional == 'us') || ($regional == 'ap')) {
			$res['outside_site'] = $http . $res['bucket'] . '.oss-' . $res['regional'] . '.aliyuncs.com';
			$res['inside_site'] = $http . $res['bucket'] . '.oss-' . $res['regional'] . '-internal.aliyuncs.com';
		}
		else {
			$res['outside_site'] = $http . $res['bucket'] . '.oss-cn-' . $res['regional'] . '.aliyuncs.com';
			$res['inside_site'] = $http . $res['bucket'] . '.oss-cn-' . $res['regional'] . '-internal.aliyuncs.com';
		}

		if (empty($res['endpoint'])) {
			$res['endpoint'] = $res['outside_site'] . '/';
		}
	}

	return $res;
}

function get_goods_desc_images_preg($endpoint = '', $text_desc = '', $str_file = 'goods_desc')
{
	if ($text_desc) {
		$preg = '/<img.*?src=[\\"|\']?(.*?)[\\"|\'].*?>/i';
		preg_match_all($preg, $text_desc, $desc_img);
	}
	else {
		$desc_img = '';
	}

	$arr = array();
	if ($desc_img && $endpoint) {
		foreach ($desc_img[1] as $key => $row) {
			$row = explode(IMAGE_DIR, $row);
			$arr[] = $endpoint . IMAGE_DIR . $row[1];
		}

		if ($desc_img[1]) {
			if (1 < count($desc_img[1])) {
				$desc_img[1] = array_unique($desc_img[1]);

				foreach ($desc_img[1] as $key => $row) {
					if ((strpos($row, 'http://') === false) && (strpos($row, 'https://') === false)) {
						$row_str = substr($row, 0, 1);
						$str = substr($endpoint, str_len($endpoint) - 1);
						if (($str == '/') && ($row_str == '/')) {
							$endpoint = substr($endpoint, 0, -1);
						}

						$text_desc = str_replace($row, $endpoint . $row, $text_desc);
					}
				}
			}
			else if (strpos($text_desc, $endpoint) === false) {
				$text_desc = str_replace('/' . IMAGE_DIR, $endpoint . IMAGE_DIR, $text_desc);
			}
		}
	}

	$res = array('images_list' => $arr, $str_file => $text_desc);
	return $res;
}

function get_desc_images_del($images_list = '')
{
	if ($images_list) {
		for ($i = 0; $i < count($images_list); $i++) {
			$img = explode(IMAGE_DIR, $images_list[$i]);
			@unlink(ROOT_PATH . IMAGE_DIR . $img[1]);
		}
	}
}

function G($start, $end = '', $dec = 4)
{
	static $_info = array();
	static $_mem = array();

	if (is_float($end)) {
		$_info[$start] = $end;
	}
	else if (!empty($end)) {
		if (!isset($_info[$end])) {
			$_info[$end] = microtime(true);
		}

		if (MEMORY_LIMIT_ON && ($dec == 'm')) {
			if (!isset($_mem[$end])) {
				$_mem[$end] = memory_get_usage();
			}

			return number_format(($_mem[$end] - $_mem[$start]) / 1024);
		}
		else {
			return number_format($_info[$end] - $_info[$start], $dec);
		}
	}
	else {
		$_info[$start] = microtime(true);

		if (MEMORY_LIMIT_ON) {
			$_mem[$start] = memory_get_usage();
		}
	}

	return NULL;
}

function unique_arr($arr, $step = 0)
{
	$new = array();
	$u_arr = array();

	foreach ($arr as $k1 => $r1) {
		if (isset($r1['user_id'])) {
			$u_arr[] = $r1;
			array_push($new, $r1);
		}
	}

	if ($u_arr) {
		$new_arr = array();

		foreach ($u_arr as $k3 => $r3) {
			foreach ($arr as $k2 => $r2) {
				if ($r2['brand_id'] == $r3['brand_id']) {
					unset($arr[$k2]);
				}
			}
		}
	}

	foreach ($arr as $r1) {
		$new[] = $r1;
	}

	if (0 < $step) {
		$new = array_slice($new, 0, $step);
	}

	return $new;
}

function get_shop_config_val($val = '')
{
	$sel_config = array();

	if (defined('CACHE_MEMCACHED')) {
		$sel_config['open_memcached'] = CACHE_MEMCACHED;
	}
	else {
		$sel_config['open_memcached'] = 0;
	}

	return $sel_config;
}

function get_seller_domain_url($ru_id = 0, $build_uri = array())
{
	$build_uri['cid'] = isset($build_uri['cid']) ? $build_uri['cid'] : 0;
	$build_uri['urid'] = isset($build_uri['urid']) ? $build_uri['urid'] : 0;
	$append = (isset($build_uri['append']) ? $build_uri['append'] : '');
	unset($build_uri['append']);
	$res = get_seller_domain_info($ru_id);
	$res['seller_url'] = $res['domain_name'];
	if ($res['domain_name'] && $res['is_enable']) {
		if ($build_uri['cid']) {
			$build_uri['domain_name'] = $res['domain_name'];
			$res['domain_name'] = get_return_store_url($build_uri, $append);
		}
		else {
			$res['domain_name'] = $res['domain_name'];
		}

		$res['domain_name'] = $res['domain_name'];
	}
	else {
		$res['domain_name'] = get_return_store_url($build_uri, $append);
	}

	return $res;
}

function get_seller_domain_info($ru_id = 0)
{
	$sql = 'SELECT domain_name, is_enable, validity_time FROM ' . $GLOBALS['ecs']->table('seller_domain') . ' WHERE ru_id = \'' . $ru_id . '\' LIMIT 1';
	$row = $GLOBALS['db']->getRow($sql);

	if (!$row) {
		$row['domain_name'] = '';
		$row['is_enable'] = '';
		$row['validity_time'] = '';
	}

	return $row;
}

function get_category_store_list($ru_id, $is_url = 0, $level = 0, $parent_id = 0)
{
	$where = (!empty($ru_id) ? ' user_id = \'' . $ru_id . '\'' : ' user_id <> 0');
	$sql = 'SELECT cat_id, cat_name, parent_id, user_id AS ru_id, sort_order, ' . 'measure_unit, grade, is_show, show_in_nav FROM ' . $GLOBALS['ecs']->table('merchants_category') . ' WHERE ' . $where . ' AND parent_id = \'' . $parent_id . '\' ORDER BY sort_order, cat_id DESC';
	$res = $GLOBALS['db']->getAll($sql);

	if ($ru_id) {
		$ruCat = ' and g.user_id = \'' . $ru_id . '\' ';
	}

	$level = 0;
	$arr = array();

	if ($res) {
		foreach ($res as $key => $row) {
			$cat_id_str = get_class_nav($row['cat_id'], 'merchants_category');
			$row['cat_child'] = substr($cat_id_str['catId'], 0, -1);

			if (empty($cat_id_str['catId'])) {
				$row['cat_child'] = substr($row['cat_id'], 0, -1);
			}

			$goodsNums = $GLOBALS['db']->getAll('SELECT * FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' WHERE g.is_delete = 0 AND g.user_cat in(' . $row['cat_child'] . ')' . $ruCat);
			$goods_ids = array();

			foreach ($goodsNums as $num_key => $num_val) {
				$goods_ids[] = $num_val['goods_id'];
			}

			$row['goods_num'] = count($goodsNums);
			$row['goodsCat'] = $goodsCat;
			$row['goodsNum'] = $goodsNum;
			$row['user_name'] = get_shop_name($row['ru_id'], 1);
			$row['level'] = $level;

			if ($is_url) {
				$build_uri = array('urid' => $row['ru_id'], 'append' => $row['user_name'], 'cid' => $row['cat_id']);
				$domain_url = get_seller_domain_url($row['ru_id'], $build_uri);
				$row['url'] = $domain_url['domain_name'];
			}

			$arr[$key] = $row;
		}
	}

	return $arr;
}

function get_category_store_child_list($parent_id = 0, $level = 0, $ru_id = 0, $is_url = 0)
{
	if (0 < $ru_id) {
		$ruCat = ' and g.user_id = \'' . $ru_id . '\' ';
	}

	$where = (!empty($ru_id) ? ' user_id = \'' . $ru_id . '\'' : ' user_id <> 0');
	$where .= (!empty($parent_id) ? ' AND parent_id = \'' . $parent_id . '\'' : '');
	$sql = 'SELECT cat_id, cat_name, parent_id, user_id AS ru_id, ' . 'sort_order, measure_unit, grade, is_show, show_in_nav FROM ' . $GLOBALS['ecs']->table('merchants_category') . ' WHERE ' . $where . ' ORDER BY sort_order, cat_id DESC';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	if ($res) {
		foreach ($res as $key => $row) {
			$cat_id_str = get_class_nav($row['cat_id'], 'merchants_category');
			$row['cat_child'] = substr($cat_id_str['catId'], 0, -1);

			if (empty($cat_id_str['catId'])) {
				$row['cat_child'] = substr($row['cat_id'], 0, -1);
			}

			$goodsNums = $GLOBALS['db']->getAll('SELECT * FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' WHERE g.is_delete = 0 AND g.user_cat in(' . $row['cat_child'] . ')' . $ruCat);
			$goods_ids = array();

			foreach ($goodsNums as $num_key => $num_val) {
				$goods_ids[] = $num_val['goods_id'];
			}

			$row['goods_num'] = count($goodsNums);
			$row['goodsCat'] = $goodsCat;
			$row['goodsNum'] = $goodsNum;
			$row['user_name'] = get_shop_name($row['ru_id'], 1);
			$row['level'] = $level;
			$row['level_type'] = $level + 3;

			if ($is_url) {
				$build_uri = array('urid' => $row['ru_id'], 'append' => $row['user_name'], 'cid' => $row['cat_id']);
				$domain_url = get_seller_domain_url($row['ru_id'], $build_uri);
				$row['url'] = $domain_url['domain_name'];
			}

			$arr[$key] = $row;
		}
	}

	return $arr;
}

function get_store_category_leve_list($cat_list = array(), $ru_id = 0, $is_url = 0)
{
	if ($cat_list) {
		foreach ($cat_list as $key => $row) {
			$cat_list[$key]['child_tree'] = get_category_store_child_list($row['cat_id'], $row['level'], $ru_id, $is_url);
		}
	}

	return $cat_list;
}

function array_get_by_key(array $array, $string)
{
	if (!trim($string)) {
		return false;
	}

	preg_match_all('/"' . $string . '";\\w{1}:(?:\\d+:|)(.*?);/', serialize($array), $res);
	return $res[1];
}

function get_store_cat_read($cat_list, $level = 0)
{
	$arr = array();

	if ($cat_list) {
		foreach ($cat_list as $key => $row) {
			if ($row['level'] == $level) {
				$row['level'] = $level - 3;
				$row['level_type'] = $level;
				$arr[$key] = $row;
			}
		}

		$arr = array_values($arr);
	}

	return $arr;
}

function get_shipping_code($shipping_id = 0)
{
	$sql = 'SELECT shipping_id, shipping_code FROM ' . $GLOBALS['ecs']->table('shipping') . ' WHERE shipping_id = \'' . $shipping_id . '\'';
	return $GLOBALS['db']->getRow($sql);
}

function sms_ali($params = array(), $send_time = '', $test = array())
{
	if ($test) {
		preg_match_all('/\\{(.+?)\\}/', $test['temp_content'], $match);
		$smsParams = get_sms_params($match[1], $send_time, $params);
		$SignName = $test['set_sign'];
		$SmsCdoe = $test['temp_id'];
	}
	else {
		$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('alidayu_configure') . ' WHERE send_time = \'' . $send_time . '\' ';
		$row = $GLOBALS['db']->getRow($sql);
		preg_match_all('/\\{(.+?)\\}/', $row['temp_content'], $match);
		$smsParams = get_sms_params($match[1], $send_time, $params);
		$SignName = $row['set_sign'];
		$SmsCdoe = $row['temp_id'];
	}

	$result = array('SmsType' => 'normal', 'SignName' => $SignName, 'SmsCdoe' => $SmsCdoe, 'smsParams' => json_encode($smsParams), 'mobile_phone' => $params['mobile_phone']);
	return $result;
}

function get_sms_params($temp_content = '', $send_time = '', $params = '')
{
	$smsParams = array();

	if (!empty($temp_content)) {
		if ($send_time == 'sms_order_shipped') {
			if ($params['shop_name']) {
				$params['shop_name'] = '【' . $params['shop_name'] . '】';
			}
		}

		$smsParams = get_sms_params_var($temp_content, $params);
	}

	return $smsParams;
}

function get_sms_params_var($temp_content, $params)
{
	$arr = array();

	if ($temp_content) {
		foreach ($temp_content as $key => $row) {
			$arr[$row] = $params[$row];
		}
	}

	return $arr;
}

function get_users_real($user_id, $user_type = 0)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('users_real') . ' WHERE user_id = \'' . $user_id . '\' AND user_type = \'' . $user_type . ' \' LIMIT 1';
	$real_user = $GLOBALS['db']->getRow($sql);
	return $real_user;
}

function huyi_sms($sms_content, $send_time, $temp_content = '')
{
	include_once ROOT_PATH . 'includes/cls_sms.php';
	$sms = new sms();

	if ($temp_content) {
		$msg['temp_content'] = $temp_content;
	}
	else {
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('alidayu_configure') . ' WHERE send_time = \'' . $send_time . '\'';
		$msg = $GLOBALS['db']->getRow($sql);
	}

	preg_match_all('/\\$\\{(.*?)\\}/', $msg['temp_content'], $matches);

	foreach ($matches[1] as $vo) {
		$msg['temp_content'] = str_replace('${' . $vo . '}', $sms_content[$vo], $msg['temp_content']);
	}

	$result = $sms->send($sms_content['mobile_phone'], $msg['temp_content']);
	return $result;
}

function get_order_bill_log($other, $type = 'insert')
{
	if ($type == 'insert') {
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_bill_order'), $other, 'INSERT');
	}
	else {
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_bill_order'), $other, 'UPDATE', 'order_id = \'' . $other['order_id'] . '\'');
	}

	$sql = 'SELECT rec_id, order_id, goods_id, goods_price, goods_number, goods_attr, drp_money FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id = \'' . $other['order_id'] . '\'';
	$goods_list = $GLOBALS['db']->getAll($sql);

	foreach ($goods_list as $key => $row) {
		$row['cat_id'] = $GLOBALS['db']->getOne('SELECT cat_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id = \'' . $row['goods_id'] . '\'', true);
		$proportion = get_order_goods_commission($row['order_id'], 1);

		if ($proportion['cat']) {
			foreach ($proportion['cat'] as $gkey => $grow) {
				if ($row['goods_id'] == $gkey) {
					$row['proportion'] = $grow['commission_rate'];
					$row['cat_id'] = $grow['cat_id'];
					break;
				}
			}
		}

		$goods = $row;

		if ($type == 'insert') {
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_bill_goods'), $goods, 'INSERT');
		}
		else {
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_bill_goods'), $goods, 'UPDATE', 'rec_id = \'' . $row['rec_id'] . '\'');
		}
	}
}

function get_order_return_amount($order_id = 0)
{
	$sql = 'SELECT SUM(actual_return) AS return_amount FROM ' . $GLOBALS['ecs']->table('order_return') . ' WHERE order_id = \'' . $order_id . '\' AND return_type IN(1, 3) AND refound_status = 1';
	return $GLOBALS['db']->getOne($sql);
}

function get_goods_report_type()
{
	$sql = 'SELECT type_id , type_name , type_desc FROM ' . $GLOBALS['ecs']->table('goods_report_type') . ' WHERE is_show = 1';
	$report_type = $GLOBALS['db']->getAll($sql);
	return $report_type;
}

function get_goods_report_title($type_id = 0)
{
	$where = 'WHERE 1 AND is_show = 1';

	if (0 < $type_id) {
		$where .= ' AND type_id = \'' . $type_id . '\'';
	}

	$sql = 'SELECT title_id , type_id , title_name FROM ' . $GLOBALS['ecs']->table('goods_report_title') . $where;
	$report_title = $GLOBALS['db']->getAll($sql);

	if ($report_title) {
		foreach ($report_title as $k => $v) {
			if (0 < $v['type_id']) {
				$sql = 'SELECT type_name FROM ' . $GLOBALS['ecs']->table('goods_report_type') . 'WHERE type_id = \'' . $v['type_id'] . '\'';
				$report_title[$k]['type_name'] = $GLOBALS['db']->getOne($sql);
			}
		}
	}

	return $report_title;
}

function get_complaint_title()
{
	$sql = 'SELECT title_id , title_name , title_desc FROM ' . $GLOBALS['ecs']->table('complain_title') . 'WHERE is_show=1';
	$report_type = $GLOBALS['db']->getAll($sql);
	return $report_type;
}

function get_complaint_info($complaint_id = 0)
{
	$sql = 'SELECT complaint_id,order_id,order_sn,user_id,user_name,ru_id,shop_name,title_id,complaint_content,add_time,complaint_handle_time,' . 'admin_id,appeal_messg,appeal_time,end_handle_time,end_admin_id,complaint_state,complaint_active,end_handle_messg FROM' . $GLOBALS['ecs']->table('complaint') . ' WHERE complaint_id = \'' . $complaint_id . '\' LIMIT 1';
	$complaint_info = $GLOBALS['db']->getRow($sql);
	$complaint_info['title_name'] = $GLOBALS['db']->getOne('SELECT title_name FROM' . $GLOBALS['ecs']->table('complain_title') . 'WHERE title_id = \'' . $complaint_info['title_id'] . '\'');
	$sql = 'SELECT img_file ,img_id FROM ' . $GLOBALS['ecs']->table('complaint_img') . ' WHERE complaint_id = \'' . $complaint_info['complaint_id'] . '\' ORDER BY  img_id DESC';
	$img_list = $GLOBALS['db']->getAll($sql);

	if (!empty($img_list)) {
		foreach ($img_list as $k => $v) {
			$img_list[$k]['img_file'] = get_image_path($v['img_id'], $v['img_file']);
		}
	}

	$complaint_info['img_list'] = $img_list;
	$sql = 'SELECT img_file ,img_id FROM ' . $GLOBALS['ecs']->table('appeal_img') . ' WHERE complaint_id = \'' . $complaint_info['complaint_id'] . '\' ORDER BY  img_id DESC';
	$appeal_img = $GLOBALS['db']->getAll($sql);

	if (!empty($appeal_img)) {
		foreach ($appeal_img as $k => $v) {
			$appeal_img[$k]['img_file'] = get_image_path($v['img_id'], $v['img_file']);
		}
	}

	$complaint_info['appeal_img'] = $appeal_img;
	$complaint_info['end_handle_user'] = $GLOBALS['db']->getOne('SELECT user_name FROM' . $GLOBALS['ecs']->table('admin_user') . 'WHERE user_id = \'' . $complaint_info['end_admin_id'] . '\'');
	$complaint_info['handle_user'] = $GLOBALS['db']->getOne('SELECT user_name FROM' . $GLOBALS['ecs']->table('admin_user') . 'WHERE user_id = \'' . $complaint_info['admin_id'] . '\'');
	$complaint_info['add_time'] = local_date('Y-m-d H:i:s', $complaint_info['add_time']);
	$complaint_info['appeal_time'] = local_date('Y-m-d H:i:s', $complaint_info['appeal_time']);
	$complaint_info['end_handle_time'] = local_date('Y-m-d H:i:s', $complaint_info['end_handle_time']);
	$complaint_info['complaint_handle_time'] = local_date('Y-m-d H:i:s', $complaint_info['complaint_handle_time']);
	return $complaint_info;
}

function checkTalkView($complaint_id = 0, $type = 'admin')
{
	$sql = 'SELECT talk_id,talk_member_name,talk_member_type,talk_content,talk_state,talk_time,view_state FROM' . $GLOBALS['ecs']->table('complaint_talk') . 'WHERE complaint_id=\'' . $complaint_id . '\' ORDER BY talk_time DESC';
	$talk_list = $GLOBALS['db']->getAll($sql);

	foreach ($talk_list as $k => $v) {
		$talk_list[$k]['talk_time'] = local_date('Y-m-d H:i:s', $v['talk_time']);

		if ($v['view_state']) {
			$view_state = explode(',', $v['view_state']);

			if (!in_array($type, $view_state)) {
				$view_state_new = $v['view_state'] . ',' . $type;
				$sql = 'UPDATE' . $GLOBALS['ecs']->table('complaint_talk') . ' SET view_state = \'' . $view_state_new . '\' WHERE talk_id = \'' . $v['talk_id'] . '\'';
				$GLOBALS['db']->query($sql);
			}
		}
	}

	return $talk_list;
}

function del_complaint_img($complaint_id = 0, $table = 'complaint_img')
{
	$sql = 'SELECT img_file ,img_id FROM ' . $GLOBALS['ecs']->table('complaint_img') . ' WHERE complaint_id = \'' . $complaint_id . '\' ORDER BY  img_id DESC';
	$img_list = $GLOBALS['db']->getAll($sql);

	if (!empty($img_list)) {
		foreach ($img_list as $k => $v) {
			if ($v['img_file']) {
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE img_id = \'' . $v['img_id'] . '\'';
				$GLOBALS['db']->query($sql);
				get_oss_del_file(array($v['img_file']));
				@unlink(ROOT_PATH . $v['img_file']);
			}
		}
	}

	return '';
}

function del_complaint_talk($complaint_id = 0)
{
	$sql = 'DELETE FROM' . $GLOBALS['ecs']->table('complaint_talk') . 'WHERE complaint_id = \'' . $complaint_id . '\'';
	return $GLOBALS['db']->query($sql);
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
