<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_consignee_list_p($user_id, $id = 0, $num = 10, $start = 0)
{
	if ($id) {
		$where['address_id'] = $id;
		$GLOBALS['db']->table = 'user_address';
		return $GLOBALS['db']->find($where);
	}
	else {
		$sql = 'select * from {pre}user_address where user_id = ' . $user_id . ' order by address_id limit ' . $start . ', ' . $num;
		return $GLOBALS['db']->query($sql);
	}
}

function flow_available_points($cart_value)
{
	if (!empty($_SESSION['user_id'])) {
		$c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	$where = '';

	if (!empty($cart_value)) {
		$where = ' AND c.rec_id ' . db_create_in($cart_value);
	}

	$sql = 'SELECT SUM(g.integral * c.goods_number) ' . 'FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'WHERE ' . $c_sess . (' AND c.goods_id = g.goods_id AND c.is_gift = 0 AND g.integral > 0 ' . $where) . 'AND c.rec_type = \'' . CART_GENERAL_GOODS . '\'';
	$val = intval($GLOBALS['db']->getOne($sql));
	return integral_of_value($val);
}

function get_cart_value($flow_type = 0, $store_id = 0)
{
	$where = '';

	if (!empty($_SESSION['user_id'])) {
		$c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	if (0 < $store_id) {
		$where .= ' c.store_id = ' . $store_id . ' AND ';
	}

	$sql = 'SELECT c.rec_id FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . (' AS g ON c.goods_id = g.goods_id WHERE ' . $where . ' ') . $c_sess . (' AND c.is_checked = 1 AND c.rec_type = \'' . $flow_type . '\' order by c.rec_id asc');
	$goods_list = $GLOBALS['db']->getAll($sql);
	$rec_id = '';

	if ($goods_list) {
		foreach ($goods_list as $key => $row) {
			$rec_id .= $row['rec_id'] . ',';
		}

		$rec_id = substr($rec_id, 0, -1);
	}

	return $rec_id;
}

function cart_by_favourable($merchant_goods)
{
	foreach ($merchant_goods as $key => $row) {
		$goods_num = 0;
		$package_goods_num = 0;
		$user_cart_goods = $row['goods_list'];
		$favourable_list = favourable_list($_SESSION['user_rank'], $row['ru_id']);
		$sort_favourable = sort_favourable($favourable_list);

		foreach ($user_cart_goods as $key1 => $row1) {
			$row1['original_price'] = $row1['goods_price'] * $row1['goods_number'];

			if ($row1['extension_code'] == 'package_buy') {
				$package_goods_num++;
			}
			else {
				$goods_num++;
			}

			if (isset($sort_favourable['by_all']) && $row1['extension_code'] != 'package_buy') {
				foreach ($sort_favourable['by_all'] as $key2 => $row2) {
					if ($row1['is_gift'] == 0) {
						$merchant_goods[$key]['new_list'][$row2['act_id']]['act_id'] = $row2['act_id'];
						$merchant_goods[$key]['new_list'][$row2['act_id']]['act_name'] = $row2['act_name'];
						$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type'] = $row2['act_type'];

						switch ($row2['act_type']) {
						case 0:
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '满赠';
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = intval($row2['act_type_ext']);
							break;

						case 1:
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '满减';
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = number_format($row2['act_type_ext'], 2);
							break;

						case 2:
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '折扣';
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = floatval($row2['act_type_ext'] / 10);
							break;

						default:
							break;
						}

						$merchant_goods[$key]['new_list'][$row2['act_id']]['min_amount'] = $row2['min_amount'];
						$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext'] = intval($row2['act_type_ext']);
						$merchant_goods[$key]['new_list'][$row2['act_id']]['cart_fav_amount'] = cart_favourable_amount($row2);
						$merchant_goods[$key]['new_list'][$row2['act_id']]['available'] = favourable_available($row2);
						$cart_favourable = cart_favourable();
						$merchant_goods[$key]['new_list'][$row2['act_id']]['cart_favourable_gift_num'] = empty($cart_favourable[$row2['act_id']]) ? 0 : intval($cart_favourable[$row2['act_id']]);
						$merchant_goods[$key]['new_list'][$row2['act_id']]['favourable_used'] = favourable_used($row2, $cart_favourable);
						$merchant_goods[$key]['new_list'][$row2['act_id']]['left_gift_num'] = intval($row2['act_type_ext']) - (empty($cart_favourable[$row2['act_id']]) ? 0 : intval($cart_favourable[$row2['act_id']]));

						if ($row2['gift']) {
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_gift_list'] = $row2['gift'];
						}

						$merchant_goods[$key]['new_list'][$row2['act_id']]['act_goods_list'][$row1['rec_id']] = $row1;
					}
					else {
						$merchant_goods[$key]['new_list'][$row2['act_id']]['act_cart_gift'][$row1['rec_id']] = $row1;
					}

					break;
				}

				continue;
			}

			if (isset($sort_favourable['by_category']) && $row1['extension_code'] != 'package_buy') {
				$get_act_range_ext = get_act_range_ext($_SESSION['user_rank'], $row['ru_id'], 1);
				$id_list = array();

				foreach ($get_act_range_ext as $id) {
					$id_list = array_merge($id_list, array_keys(cat_list(intval($id), 0)));
				}

				$cat_id = $GLOBALS['db']->getOne('SELECT cat_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $row1['goods_id'] . '\' '));
				$favourable_id_list = get_favourable_id($sort_favourable['by_category']);
				if (in_array(trim($cat_id), $id_list) && $row1['is_gift'] == 0 || in_array($row1['is_gift'], $favourable_id_list)) {
					foreach ($sort_favourable['by_category'] as $key2 => $row2) {
						$fav_act_range_ext = array();

						foreach (explode(',', $row2['act_range_ext']) as $id) {
							$fav_act_range_ext = array_merge($fav_act_range_ext, array_keys(cat_list(intval($id), 0)));
						}

						if ($row1['is_gift'] == 0 && in_array($cat_id, $fav_act_range_ext)) {
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_id'] = $row2['act_id'];
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_name'] = $row2['act_name'];
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type'] = $row2['act_type'];

							switch ($row2['act_type']) {
							case 0:
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '满赠';
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = intval($row2['act_type_ext']);
								break;

							case 1:
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '满减';
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = number_format($row2['act_type_ext'], 2);
								break;

							case 2:
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '折扣';
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = floatval($row2['act_type_ext'] / 10);
								break;

							default:
								break;
							}

							$merchant_goods[$key]['new_list'][$row2['act_id']]['min_amount'] = $row2['min_amount'];
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext'] = intval($row2['act_type_ext']);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['cart_fav_amount'] = cart_favourable_amount($row2);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['available'] = favourable_available($row2);
							$cart_favourable = cart_favourable();
							$merchant_goods[$key]['new_list'][$row2['act_id']]['cart_favourable_gift_num'] = empty($cart_favourable[$row2['act_id']]) ? 0 : intval($cart_favourable[$row2['act_id']]);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['favourable_used'] = favourable_used($row2, $cart_favourable);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['left_gift_num'] = intval($row2['act_type_ext']) - (empty($cart_favourable[$row2['act_id']]) ? 0 : intval($cart_favourable[$row2['act_id']]));

							if ($row2['gift']) {
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_gift_list'] = $row2['gift'];
							}

							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_goods_list'][$row1['rec_id']] = $row1;
						}

						if ($row1['is_gift'] == $row2['act_id']) {
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_cart_gift'][$row1['rec_id']] = $row1;
						}
					}

					continue;
				}
			}

			if (isset($sort_favourable['by_brand']) && $row1['extension_code'] != 'package_buy') {
				$get_act_range_ext = get_act_range_ext($_SESSION['user_rank'], $row['ru_id'], 2);
				$brand_id = $GLOBALS['db']->getOne('SELECT brand_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $row1['goods_id'] . '\' '));
				$favourable_id_list = get_favourable_id($sort_favourable['by_brand']);
				if (in_array(trim($brand_id), $get_act_range_ext) && $row1['is_gift'] == 0 || in_array($row1['is_gift'], $favourable_id_list)) {
					foreach ($sort_favourable['by_brand'] as $key2 => $row2) {
						$act_range_ext_str = ',' . $row2['act_range_ext'] . ',';
						$brand_id_str = ',' . $brand_id . ',';
						if ($row1['is_gift'] == 0 && strstr($act_range_ext_str, trim($brand_id_str))) {
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_id'] = $row2['act_id'];
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_name'] = $row2['act_name'];
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type'] = $row2['act_type'];

							switch ($row2['act_type']) {
							case 0:
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '满赠';
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = intval($row2['act_type_ext']);
								break;

							case 1:
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '满减';
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = number_format($row2['act_type_ext'], 2);
								break;

							case 2:
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '折扣';
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = floatval($row2['act_type_ext'] / 10);
								break;

							default:
								break;
							}

							$merchant_goods[$key]['new_list'][$row2['act_id']]['min_amount'] = $row2['min_amount'];
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext'] = intval($row2['act_type_ext']);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['cart_fav_amount'] = cart_favourable_amount($row2);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['available'] = favourable_available($row2);
							$cart_favourable = cart_favourable();
							$merchant_goods[$key]['new_list'][$row2['act_id']]['cart_favourable_gift_num'] = empty($cart_favourable[$row2['act_id']]) ? 0 : intval($cart_favourable[$row2['act_id']]);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['favourable_used'] = favourable_used($row2, $cart_favourable);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['left_gift_num'] = intval($row2['act_type_ext']) - (empty($cart_favourable[$row2['act_id']]) ? 0 : intval($cart_favourable[$row2['act_id']]));

							if ($row2['gift']) {
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_gift_list'] = $row2['gift'];
							}

							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_goods_list'][$row1['rec_id']] = $row1;
						}

						if ($row1['is_gift'] == $row2['act_id']) {
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_cart_gift'][$row1['rec_id']] = $row1;
						}
					}

					continue;
				}
			}

			if (isset($sort_favourable['by_goods']) && $row1['extension_code'] != 'package_buy') {
				$get_act_range_ext = get_act_range_ext($_SESSION['user_rank'], $row['ru_id'], 3);
				$favourable_id_list = get_favourable_id($sort_favourable['by_goods']);
				if (in_array($row1['goods_id'], $get_act_range_ext) || in_array($row1['is_gift'], $favourable_id_list)) {
					foreach ($sort_favourable['by_goods'] as $key2 => $row2) {
						$act_range_ext_str = ',' . $row2['act_range_ext'] . ',';
						$goods_id_str = ',' . $row1['goods_id'] . ',';
						if (strstr($act_range_ext_str, $goods_id_str) && $row1['is_gift'] == 0) {
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_id'] = $row2['act_id'];
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_name'] = $row2['act_name'];
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type'] = $row2['act_type'];

							switch ($row2['act_type']) {
							case 0:
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '满赠';
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = intval($row2['act_type_ext']);
								break;

							case 1:
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '满减';
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = number_format($row2['act_type_ext'], 2);
								break;

							case 2:
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_txt'] = '折扣';
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext_format'] = floatval($row2['act_type_ext'] / 10);
								break;

							default:
								break;
							}

							$merchant_goods[$key]['new_list'][$row2['act_id']]['min_amount'] = $row2['min_amount'];
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_type_ext'] = intval($row2['act_type_ext']);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['cart_fav_amount'] = cart_favourable_amount($row2);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['available'] = favourable_available($row2);
							$cart_favourable = cart_favourable();
							$merchant_goods[$key]['new_list'][$row2['act_id']]['cart_favourable_gift_num'] = empty($cart_favourable[$row2['act_id']]) ? 0 : intval($cart_favourable[$row2['act_id']]);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['favourable_used'] = favourable_used($row2, $cart_favourable);
							$merchant_goods[$key]['new_list'][$row2['act_id']]['left_gift_num'] = intval($row2['act_type_ext']) - (empty($cart_favourable[$row2['act_id']]) ? 0 : intval($cart_favourable[$row2['act_id']]));

							if ($row2['gift']) {
								$merchant_goods[$key]['new_list'][$row2['act_id']]['act_gift_list'] = $row2['gift'];
							}

							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_goods_list'][$row1['rec_id']] = $row1;
							break;
						}

						if ($row1['is_gift'] == $row2['act_id']) {
							$merchant_goods[$key]['new_list'][$row2['act_id']]['act_cart_gift'][$row1['rec_id']] = $row1;
						}
					}
				}
				else {
					$merchant_goods[$key]['new_list'][0]['act_goods_list'][$row1['rec_id']] = $row1;
				}
			}
			else {
				$merchant_goods[$key]['new_list'][0]['act_goods_list'][$row1['rec_id']] = $row1;
			}
		}

		$merchant_goods[$key]['goods_count'] = $goods_num;
		$merchant_goods[$key]['package_goods_num'] = $package_goods_num;
	}

	return $merchant_goods;
}

function favourable_list($user_rank, $user_id = -1, $fav_id = 0)
{
	$where = '';

	if (0 <= $user_id) {
		$where .= ' AND user_id = \'' . $user_id . '\'';
	}

	if (0 < $fav_id) {
		$where .= ' AND act_id = \'' . $fav_id . '\' ';
	}

	$used_list = cart_favourable();
	$favourable_list = array();
	$user_rank = ',' . $user_rank . ',';
	$now = gmtime();
	$sql = 'SELECT * ' . 'FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' WHERE CONCAT(\',\', user_rank, \',\') LIKE \'%' . $user_rank . '%\'' . (' AND start_time <= \'' . $now . '\' AND end_time >= \'' . $now . '\' AND review_status = 3 ') . $where . ' ORDER BY sort_order';
	$res = $GLOBALS['db']->query($sql);

	foreach ($res as $favourable) {
		$favourable['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $favourable['start_time']);
		$favourable['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $favourable['end_time']);
		$favourable['formated_min_amount'] = price_format($favourable['min_amount'], false);
		$favourable['formated_max_amount'] = price_format($favourable['max_amount'], false);
		$favourable['gift'] = unserialize($favourable['gift']);

		foreach ((array) $favourable['gift'] as $key => $value) {
			$favourable['gift'][$key]['formated_price'] = price_format($value['price'], false);
			$favourable['gift'][$key]['thumb_img'] = $GLOBALS['db']->getOne('SELECT goods_thumb FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $value['id'] . '\''));
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE is_on_sale = 1 AND goods_id = ' . $value['id'];
			$is_sale = $GLOBALS['db']->getOne($sql);

			if (!$is_sale) {
				unset($favourable['gift'][$key]);
			}
		}

		$favourable['act_range_desc'] = act_range_desc($favourable);
		$favourable['act_type_desc'] = sprintf($GLOBALS['_LANG']['fat_ext'][$favourable['act_type']], $favourable['act_type_ext']);
		$favourable['available'] = favourable_available($favourable);

		if ($favourable['available']) {
			$favourable['available'] = !favourable_used($favourable, $used_list);
		}

		$favourable_list[] = $favourable;
	}

	return $favourable_list;
}

function cart_favourable()
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	$list = array();
	$sql = 'SELECT is_gift, COUNT(*) AS num ' . 'FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . ' AND rec_type = \'' . CART_GENERAL_GOODS . '\'' . ' AND is_gift > 0' . ' GROUP BY is_gift';
	$res = $GLOBALS['db']->query($sql);

	foreach ($res as $row) {
		$list[$row['is_gift']] = $row['num'];
	}

	return $list;
}

function sort_favourable($favourable_list)
{
	$arr = array();

	foreach ($favourable_list as $key => $value) {
		switch ($value['act_range']) {
		case FAR_ALL:
			$arr['by_all'][$key] = $value;
			break;

		case FAR_CATEGORY:
			$arr['by_category'][$key] = $value;
			break;

		case FAR_BRAND:
			$arr['by_brand'][$key] = $value;
			break;

		case FAR_GOODS:
			$arr['by_goods'][$key] = $value;
			break;

		default:
			break;
		}
	}

	return $arr;
}

function act_range_desc($favourable)
{
	if ($favourable['act_range'] == FAR_BRAND) {
		$sql = 'SELECT brand_name FROM ' . $GLOBALS['ecs']->table('brand') . ' WHERE brand_id ' . db_create_in($favourable['act_range_ext']);
		return join(',', $GLOBALS['db']->getCol($sql));
	}
	else if ($favourable['act_range'] == FAR_CATEGORY) {
		$sql = 'SELECT cat_name FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE cat_id ' . db_create_in($favourable['act_range_ext']);
		return join(',', $GLOBALS['db']->getCol($sql));
	}
	else if ($favourable['act_range'] == FAR_GOODS) {
		$sql = 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id ' . db_create_in($favourable['act_range_ext']);
		return join(',', $GLOBALS['db']->getCol($sql));
	}
	else {
		return '';
	}
}

function favourable_available($favourable)
{
	$user_rank = $_SESSION['user_rank'];

	if (strpos(',' . $favourable['user_rank'] . ',', ',' . $user_rank . ',') === false) {
		return false;
	}

	$amount = cart_favourable_amount($favourable);
	return $favourable['min_amount'] <= $amount && ($amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0);
}

function cart_favourable_amount($favourable)
{
	if (!empty($_SESSION['user_id'])) {
		$c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	$fav_where = '';

	if ($favourable['userFav_type'] == 0) {
		$fav_where = ' AND g.user_id = \'' . $favourable['user_id'] . '\' ';
	}

	$sql = 'SELECT SUM(c.goods_price * c.goods_number) ' . 'FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'WHERE c.goods_id = g.goods_id ' . 'AND ' . $c_sess . ' AND c.rec_type = \'' . CART_GENERAL_GOODS . '\' ' . 'AND c.is_gift = 0 ' . 'AND c.goods_id > 0 ' . $fav_where;

	if ($favourable['act_range'] == FAR_ALL) {
	}
	else if ($favourable['act_range'] == FAR_CATEGORY) {
		$id_list = array();
		$cat_list = explode(',', $favourable['act_range_ext']);

		foreach ($cat_list as $id) {
			$id_list = array_merge($id_list, array_keys(cat_list(intval($id), 0)));
		}

		$sql .= 'AND g.cat_id ' . db_create_in($id_list);
	}
	else if ($favourable['act_range'] == FAR_BRAND) {
		$id_list = explode(',', $favourable['act_range_ext']);
		$sql .= 'AND g.brand_id ' . db_create_in($id_list);
	}
	else {
		$id_list = explode(',', $favourable['act_range_ext']);
		$sql .= 'AND g.goods_id ' . db_create_in($id_list);
	}

	return $GLOBALS['db']->getOne($sql);
}

function get_act_range_ext($user_rank, $user_id = -1, $act_range)
{
	if (0 <= $user_id) {
		$u_id = ' AND user_id = \'' . $user_id . '\'';
	}

	if (0 < $act_range) {
		$a_range = ' AND act_range = \'' . $act_range . '\' ';
	}

	$res = array();
	$user_rank = ',' . $user_rank . ',';
	$now = gmtime();
	$sql = 'SELECT act_range_ext ' . 'FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' WHERE CONCAT(\',\', user_rank, \',\') LIKE \'%' . $user_rank . '%\'' . (' AND start_time <= \'' . $now . '\' AND end_time >= \'' . $now . '\' AND review_status =3  ') . $u_id . $a_range . ' ORDER BY sort_order';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr = array_merge($arr, explode(',', $row['act_range_ext']));
	}

	return array_unique($arr);
}

function get_favourable_id($favourable)
{
	$arr = array();

	foreach ($favourable as $key => $value) {
		$arr[$key] = $value['act_id'];
	}

	return $arr;
}

function favourable_used($favourable, $cart_favourable)
{
	if ($favourable['act_type'] == FAT_GOODS) {
		return isset($cart_favourable[$favourable['act_id']]) && $favourable['act_type_ext'] <= $cart_favourable[$favourable['act_id']] && 0 < $favourable['act_type_ext'];
	}
	else {
		return isset($cart_favourable[$favourable['act_id']]);
	}
}

function flow_cart_stock($arr, $store_id = 0)
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	foreach ($arr as $key => $val) {
		$val = intval(make_semiangle($val));
		if ($val <= 0 || !is_numeric($key)) {
			continue;
		}

		$sql = 'SELECT `goods_id`, `goods_attr_id`, `extension_code`, `warehouse_id` FROM' . $GLOBALS['ecs']->table('cart') . (' WHERE rec_id=\'' . $key . '\' AND ') . $sess_id;
		$goods = $GLOBALS['db']->getRow($sql);
		$sql = 'SELECT g.goods_name, g.goods_number, g.goods_id, c.product_id, g.model_attr ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g, ' . $GLOBALS['ecs']->table('cart') . ' AS c ' . ('WHERE g.goods_id = c.goods_id AND c.rec_id = \'' . $key . '\'');
		$row = $GLOBALS['db']->getRow($sql);
		$sql = 'select IF(g.model_inventory < 1, g.goods_number, IF(g.model_inventory < 2, wg.region_number, wag.region_number)) AS goods_number ' . ' from ' . $GLOBALS['ecs']->table('goods') . ' as g ' . ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id' . ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id' . ' where g.goods_id = \'' . $row['goods_id'] . '\'';
		$goods_number = $GLOBALS['db']->getOne($sql);
		$row['goods_number'] = $goods_number;
		if (0 < intval($GLOBALS['_CFG']['use_storage']) && $goods['extension_code'] != 'package_buy' && $store_id == 0) {
			$row['product_id'] = trim($row['product_id']);

			if (!empty($row['product_id'])) {
				if ($row['model_attr'] == 1) {
					$table_products = 'products_warehouse';
				}
				else if ($row['model_attr'] == 2) {
					$table_products = 'products_area';
				}
				else {
					$table_products = 'products';
				}

				$sql = 'SELECT product_number FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $row['goods_id'] . '\' and product_id = \'' . $row['product_id'] . '\'';
				$product_number = $GLOBALS['db']->getOne($sql);

				if ($product_number < $val) {
					show_message(sprintf(L('stock_insufficiency'), $row['goods_name'], $product_number, $product_number));
					exit();
				}
			}
			else if ($row['goods_number'] < $val) {
				show_message(sprintf(L('stock_insufficiency'), $row['goods_name'], $row['goods_number'], $row['goods_number']));
				exit();
			}
		}
		else {
			if (0 < intval($GLOBALS['_CFG']['use_storage']) && 0 < $store_id && $row['cloud_id'] == 0) {
				$sql = 'SELECT goods_number,ru_id FROM' . $GLOBALS['ecs']->table('store_goods') . (' WHERE store_id = \'' . $store_id . '\' AND goods_id = \'') . $row['goods_id'] . '\' ';
				$goodsInfo = $GLOBALS['db']->getRow($sql);
				$products = get_warehouse_id_attr_number($row['goods_id'], $goods['goods_attr_id'], $goodsInfo['ru_id'], 0, 0, '', $store_id);
				$attr_number = $products['product_number'];

				if ($goods['goods_attr_id']) {
					$row['goods_number'] = $attr_number;
				}
				else {
					$row['goods_number'] = $goodsInfo['goods_number'];
				}

				if ($row['goods_number'] < $val) {
					show_message(sprintf(L('stock_store_shortage'), $row['goods_name'], $row['goods_number'], $row['goods_number']));
					exit();
				}
			}
			else {
				if (0 < intval($GLOBALS['_CFG']['use_storage']) && $goods['extension_code'] == 'package_buy') {
					if (judge_package_stock($goods['goods_id'], $val)) {
						show_message(L('package_stock_insufficiency'));
						exit();
					}
				}
				else if (0 < $row['cloud_id']) {
					$sql = 'SELECT product_number, cloud_product_id FROM ' . $GLOBALS['ecs']->table('products') . ' WHERE goods_id = \'' . $row['goods_id'] . '\' and product_id = \'' . $row['product_id'] . '\'';
					$cloud_product = $GLOBALS['db']->getRow($sql);
					$cloud_number = get_jigon_products_stock($cloud_product);

					if ($cloud_number < $val) {
						show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'], $cloud_number, $cloud_number));
						exit();
					}
				}
			}
		}
	}
}


?>
