<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function flow_available_points($cart_value, $warehouse_id = 0, $area_id = 0)
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

	$leftJoin = ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg ON g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag ON g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$sql = 'SELECT SUM(IF(g.model_price < 1, g.integral, IF(g.model_price < 2, wg.pay_integral, wag.pay_integral)) * c.goods_number ) ' . 'FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON c.goods_id = g.goods_id ' . $leftJoin . 'WHERE IF(g.model_price < 1, g.integral, IF(g.model_price < 2, wg.pay_integral, wag.pay_integral)) > 0 AND ' . $c_sess . (' AND c.is_gift = 0 ' . $where) . 'AND c.rec_type = \'' . CART_GENERAL_GOODS . '\'';
	$val = intval($GLOBALS['db']->getOne($sql));
	return integral_of_value($val);
}

function flow_update_cart($arr)
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
		$a_sess = ' a.user_id = \'' . $_SESSION['user_id'] . '\' ';
		$b_sess = ' b.user_id = \'' . $_SESSION['user_id'] . '\' ';
		$c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
		$sess = '';
	}
	else {
		$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
		$a_sess = ' a.session_id = \'' . real_cart_mac_ip() . '\' ';
		$b_sess = ' b.session_id = \'' . real_cart_mac_ip() . '\' ';
		$c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
		$sess = real_cart_mac_ip();
	}

	foreach ($arr as $key => $val) {
		$val = intval(make_semiangle($val));
		if ($val <= 0 || !is_numeric($key)) {
			continue;
		}

		$sql = 'SELECT `goods_id`, `goods_attr_id`, `product_id`, `extension_code` FROM' . $GLOBALS['ecs']->table('cart') . (' WHERE rec_id=\'' . $key . '\' AND ') . $sess_id;
		$goods = $GLOBALS['db']->getRow($sql);
		$sql = 'SELECT g.goods_name, g.goods_number ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g, ' . $GLOBALS['ecs']->table('cart') . ' AS c ' . ('WHERE g.goods_id = c.goods_id AND c.rec_id = \'' . $key . '\'');
		$row = $GLOBALS['db']->getRow($sql);
		$nowTime = gmtime();
		$xiangouInfo = get_purchasing_goods_info($goods['goods_id']);
		$start_date = $xiangouInfo['xiangou_start_date'];
		$end_date = $xiangouInfo['xiangou_end_date'];
		if ($xiangouInfo['is_xiangou'] == 1 && $start_date < $nowTime && $nowTime < $end_date) {
			$user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
			$orderGoods = get_for_purchasing_goods($start_date, $end_date, $goods['goods_id'], $user_id);

			if ($xiangouInfo['xiangou_num'] <= $orderGoods['goods_number']) {
				$max_num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
				$result['message'] = sprintf($GLOBALS['_LANG']['purchase_Prompt'], $row['goods_name']);
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number = 0 WHERE rec_id=\'' . $key . '\'');
				$GLOBALS['db']->query($sql);
				show_message($result['message'], $_LANG['back_to_cart'], 'flow.php');
				exit();
			}
			else if (0 < $xiangouInfo['xiangou_num']) {
				if ($xiangouInfo['is_xiangou'] == 1 && $xiangouInfo['xiangou_num'] < $orderGoods['goods_number'] + $val) {
					$max_num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
					$result['message'] = sprintf($GLOBALS['_LANG']['purchasing_prompt'], $row['goods_name']);
					$cart_Num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number = \'' . $cart_Num . '\' WHERE rec_id=\'' . $key . '\'');
					$GLOBALS['db']->query($sql);
					show_message($result['message'], $_LANG['back_to_cart'], 'flow.php');
					exit();
				}
			}
		}

		if (0 < intval($GLOBALS['_CFG']['use_storage']) && $goods['extension_code'] != 'package_buy') {
			if ($row['goods_number'] < $val) {
				show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'], $row['goods_number'], $row['goods_number']));
				exit();
			}

			$goods['product_id'] = trim($goods['product_id']);

			if (!empty($goods['product_id'])) {
				$sql = 'SELECT product_number FROM ' . $GLOBALS['ecs']->table('products') . ' WHERE goods_id = \'' . $goods['goods_id'] . '\' AND product_id = \'' . $goods['product_id'] . '\' LIMIT 1';
				$product_number = $GLOBALS['db']->getOne($sql);

				if ($product_number < $val) {
					show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'], $product_number, $product_number));
					exit();
				}
			}
		}
		else {
			if (0 < intval($GLOBALS['_CFG']['use_storage']) && $goods['extension_code'] == 'package_buy') {
				if (judge_package_stock($goods['goods_id'], $val)) {
					show_message($GLOBALS['_LANG']['package_stock_insufficiency']);
					exit();
				}
			}
		}

		$sql = "SELECT b.goods_number, b.rec_id\r\n                FROM " . $GLOBALS['ecs']->table('cart') . ' a, ' . $GLOBALS['ecs']->table('cart') . (" b\r\n                WHERE a.rec_id = '" . $key . "'\r\n                AND ") . $a_sess . "\r\n                AND a.extension_code <> 'package_buy'\r\n                AND b.parent_id = a.goods_id\r\n                AND " . $b_sess;
		$offers_accessories_res = $GLOBALS['db']->query($sql);

		if (0 < $val) {
			$row_num = 1;

			while ($offers_accessories_row = $GLOBALS['db']->fetchRow($offers_accessories_res)) {
				if ($val < $row_num) {
					$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . ' AND rec_id = \'' . $offers_accessories_row['rec_id'] . '\' AND group_id=\'\' LIMIT 1';
					$GLOBALS['db']->query($sql);
				}

				$row_num++;
			}

			if ($goods['extension_code'] == 'package_buy') {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number = \'' . $val . '\' WHERE rec_id=\'' . $key . '\' AND ') . $sess_id . ' AND group_id=\'\'';
			}
			else {
				$attr_id = empty($goods['goods_attr_id']) ? array() : explode(',', $goods['goods_attr_id']);
				$goods_price = get_final_price($goods['goods_id'], $val, true, $attr_id);
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number = \'' . $val . '\', goods_price = \'' . $goods_price . '\' WHERE rec_id=\'' . $key . '\' AND ') . $sess_id . ' AND group_id=\'\'';
			}
		}
		else {
			while ($offers_accessories_row = $GLOBALS['db']->fetchRow($offers_accessories_res)) {
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . ' AND rec_id = \'' . $offers_accessories_row['rec_id'] . '\' AND group_id=\'\' LIMIT 1';
				$GLOBALS['db']->query($sql);
			}

			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . (' WHERE rec_id=\'' . $key . '\' AND ') . $sess_id . ' AND group_id=\'\'';
		}

		$GLOBALS['db']->query($sql);
	}

	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . ' AND is_gift <> 0';
	$GLOBALS['db']->query($sql);
}

function flow_cart_stock($arr, $store_id = 0, $warehouse_id = 0, $area_id = 0)
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
		$sql = 'SELECT g.goods_name, g.goods_number, g.goods_id, c.product_id, g.model_attr, c.goods_attr_id, g.cloud_id ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g, ' . $GLOBALS['ecs']->table('cart') . ' AS c ' . ('WHERE g.goods_id = c.goods_id AND c.rec_id = \'' . $key . '\'');
		$row = $GLOBALS['db']->getRow($sql);

		if (0 < $store_id) {
			$sql = 'SELECT  goods_number FROM' . $GLOBALS['ecs']->table('store_goods') . (' WHERE goods_id = \'' . $goods_id . '\' AND store_id = \'' . $store_id . '\'');
		}
		else {
			$leftJoin = ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
			$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
			$sql = 'SELECT IF(g.model_inventory < 1, g.goods_number, IF(g.model_inventory < 2, wg.region_number, wag.region_number)) AS goods_number ' . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . ' WHERE g.goods_id = \'' . $row['goods_id'] . '\'';
		}

		$goods_number = $GLOBALS['db']->getOne($sql);
		$row['goods_number'] = $goods_number;
		if (0 < intval($GLOBALS['_CFG']['use_storage']) && $goods['extension_code'] != 'package_buy' && $store_id == 0 && $row['cloud_id'] == 0) {
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
					show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'], $product_number, $product_number));
					exit();
				}
			}
			else if ($row['goods_number'] < $val) {
				show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'], $row['goods_number'], $row['goods_number']));
				exit();
			}
		}
		else {
			if (0 < intval($GLOBALS['_CFG']['use_storage']) && 0 < $store_id && $row['cloud_id'] == 0) {
				$sql = 'SELECT goods_number,ru_id FROM' . $GLOBALS['ecs']->table('store_goods') . (' WHERE store_id = \'' . $store_id . '\' AND goods_id = \'') . $row['goods_id'] . '\' ';
				$goodsInfo = $GLOBALS['db']->getRow($sql);
				$products = get_warehouse_id_attr_number($row['goods_id'], $row['goods_attr_id'], $goodsInfo['ru_id'], 0, 0, '', $store_id);
				$attr_number = $products['product_number'];

				if ($row['goods_attr_id']) {
					$row['goods_number'] = $attr_number;
				}
				else {
					$row['goods_number'] = $goodsInfo['goods_number'];
				}

				if ($row['goods_number'] < $val) {
					show_message(sprintf($GLOBALS['_LANG']['stock_store_shortage'], $row['goods_name'], $row['goods_number'], $row['goods_number']));
					exit();
				}
			}
			else {
				if (0 < intval($GLOBALS['_CFG']['use_storage']) && $goods['extension_code'] == 'package_buy') {
					if (judge_package_stock($goods['goods_id'], $val)) {
						show_message($GLOBALS['_LANG']['package_stock_insufficiency']);
						exit();
					}
				}
				else if (0 < $row['cloud_id']) {
					$cloud_number = 0;
					$sql = 'SELECT cloud_product_id FROM ' . $GLOBALS['ecs']->table('products') . ' WHERE goods_id = \'' . $row['goods_id'] . '\' and product_id = \'' . $row['product_id'] . '\'';
					$cloud_product_id = $GLOBALS['db']->getOne($sql);

					if ($cloud_product_id) {
						$plugin_file = ROOT_PATH . '/plugins/cloudApi/cloudApi.php';
						$productIds = array($cloud_product_id);

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
												$cloud_number = $v['taxNum'];
											}
											else {
												$cloud_number = $v['noTaxNum'];
											}

											break;
										}
									}
								}
							}
						}
					}

					if ($cloud_number < $val) {
						show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'], $cloud_number, $cloud_number));
						exit();
					}
				}
			}
		}
	}
}

function cmp_favourable($a, $b)
{
	if ($a['available'] == $b['available']) {
		if ($a['sort_order'] == $b['sort_order']) {
			return 0;
		}
		else {
			return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
		}
	}
	else {
		return $a['available'] ? -1 : 1;
	}
}

function add_gift_to_cart($act_id, $id, $price, $ru_id)
{
	if (!empty($_SESSION['user_id'])) {
		$sess = '';
	}
	else {
		$sess = real_cart_mac_ip();
	}

	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('cart') . ' (' . 'user_id, session_id, goods_id, goods_sn, goods_name, market_price, goods_price, ' . 'goods_number, is_real, extension_code, parent_id, is_gift, rec_type, ru_id ) ' . ('SELECT \'' . $_SESSION['user_id'] . '\', \'') . $sess . '\', goods_id, goods_sn, goods_name, market_price, ' . ('\'' . $price . '\', 1, is_real, extension_code, 0, \'' . $act_id . '\', \'') . CART_GENERAL_GOODS . ('\', ' . $ru_id . ' ') . 'FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $id . '\'');
	$GLOBALS['db']->query($sql);
}

function add_favourable_to_cart($act_id, $act_name, $amount)
{
	if (!empty($_SESSION['user_id'])) {
		$sess = '';
	}
	else {
		$sess = real_cart_mac_ip();
	}

	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('cart') . '(' . 'user_id, session_id, goods_id, goods_sn, goods_name, market_price, goods_price, ' . 'goods_number, is_real, extension_code, parent_id, is_gift, rec_type ) ' . ('VALUES(\'' . $_SESSION['user_id'] . '\', \'') . $sess . ('\', 0, \'\', \'' . $act_name . '\', 0, ') . '\'' . -1 * $amount . ('\', 1, 0, \'\', 0, \'' . $act_id . '\', \'') . CART_GENERAL_GOODS . '\')';
	$GLOBALS['db']->query($sql);
}

function get_cart_value($flow_type = 0)
{
	if (!empty($_SESSION['user_id'])) {
		$c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	if ($_REQUEST['stages_qishu']) {
		$where .= 'AND c.stages_qishu = ' . trim($_REQUEST['stages_qishu']);
	}

	$sql = 'SELECT c.rec_id FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON c.goods_id = g.goods_id WHERE ' . $c_sess . ('AND rec_type = \'' . $flow_type . '\' ' . $where . ' order by c.rec_id asc');
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

function cart_add_favourable_box($favourable_id, $act_sel_id = array(), $ru_id = 0)
{
	$fav_res = favourable_list($_SESSION['user_rank'], -1, $favourable_id, $act_sel_id);
	$favourable_activity = $fav_res[0];
	$cart_goods = get_cart_goods('', 1);
	$merchant_goods = $cart_goods['goods_list'];
	$favourable_box = array();

	if ($cart_goods['total']['goods_price']) {
		$favourable_box['goods_amount'] = $cart_goods['total']['goods_price'];
	}

	$list_array = array();

	foreach ($merchant_goods as $key => $row) {
		$user_cart_goods = $row['goods_list'];

		if ($row['ru_id'] == $ru_id) {
			foreach ($user_cart_goods as $key1 => $row1) {
				$row1['original_price'] = $row1['goods_price'] * $row1['goods_number'];

				if (!empty($act_sel_id)) {
					$row1['sel_checked'] = strstr(',' . $act_sel_id['act_sel_id'] . ',', ',' . $row1['rec_id'] . ',') ? 1 : 0;
				}

				if ($favourable_activity['act_range'] == 0 && $row1['extension_code'] != 'package_buy') {
					if ($row1['is_gift'] == FAR_ALL) {
						if ($row1) {
							$favourable_box['act_id'] = $favourable_activity['act_id'];
							$favourable_box['act_name'] = $favourable_activity['act_name'];
							$favourable_box['act_type'] = $favourable_activity['act_type'];

							switch ($favourable_activity['act_type']) {
							case 0:
								$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['With_a_gift'];
								$favourable_box['act_type_ext_format'] = intval($favourable_activity['act_type_ext']);
								break;

							case 1:
								$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['Full_reduction'];
								$favourable_box['act_type_ext_format'] = number_format($favourable_activity['act_type_ext'], 2);
								break;

							case 2:
								$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['discount'];
								$favourable_box['act_type_ext_format'] = floatval($favourable_activity['act_type_ext'] / 10);
								break;

							default:
								break;
							}

							$favourable_box['min_amount'] = $favourable_activity['min_amount'];
							$favourable_box['act_type_ext'] = intval($favourable_activity['act_type_ext']);
							$favourable_box['cart_fav_amount'] = cart_favourable_amount($favourable_activity, $act_sel_id);
							$favourable_box['available'] = favourable_available($favourable_activity, $act_sel_id);
							$cart_favourable = cart_favourable($row['ru_id']);
							$favourable_box['cart_favourable_gift_num'] = empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]);
							$favourable_box['favourable_used'] = favourable_used($favourable_activity, $cart_favourable);
							$favourable_box['left_gift_num'] = intval($favourable_activity['act_type_ext']) - (empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]));

							if ($favourable_activity['gift']) {
								$favourable_box['act_gift_list'] = $favourable_activity['gift'];
							}

							$favourable_box['act_goods_list'][$row1['rec_id']] = $row1;
							unset($row1);
						}
					}
					else {
						$favourable_box['act_cart_gift'][$row1['rec_id']] = $row1;
					}

					continue;
				}

				if ($favourable_activity['act_range'] == FAR_CATEGORY && $row1['extension_code'] != 'package_buy') {
					$get_act_range_ext = get_act_range_ext($_SESSION['user_rank'], $row['ru_id'], 1);
					$str_cat = '';

					foreach ($get_act_range_ext as $id) {
						$cat_keys = get_array_keys_cat(intval($id));

						if ($cat_keys) {
							$str_cat .= implode(',', $cat_keys);
						}
					}

					if ($str_cat) {
						$list_array = explode(',', $str_cat);
					}

					$list_array = !empty($list_array) ? array_merge($get_act_range_ext, $list_array) : $get_act_range_ext;
					$id_list = arr_foreach($list_array);
					$id_list = array_unique($id_list);
					$cat_id = $row1['cat_id'];
					if (in_array(trim($cat_id), $id_list) && $row1['is_gift'] == 0 || $row1['is_gift'] == $favourable_activity['act_id']) {
						if ($row1) {
							$fav_act_range_ext = !empty($favourable_activity['act_range_ext']) ? explode(',', $favourable_activity['act_range_ext']) : array();

							foreach ($fav_act_range_ext as $id) {
								$cat_keys = get_array_keys_cat(intval($id));
								$fav_act_range_ext = array_merge($fav_act_range_ext, $cat_keys);
							}

							if ($row1['is_gift'] == 0 && in_array($cat_id, $fav_act_range_ext)) {
								$favourable_box['act_id'] = $favourable_activity['act_id'];
								$favourable_box['act_name'] = $favourable_activity['act_name'];
								$favourable_box['act_type'] = $favourable_activity['act_type'];

								switch ($favourable_activity['act_type']) {
								case 0:
									$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['With_a_gift'];
									$favourable_box['act_type_ext_format'] = intval($favourable_activity['act_type_ext']);
									break;

								case 1:
									$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['Full_reduction'];
									$favourable_box['act_type_ext_format'] = number_format($favourable_activity['act_type_ext'], 2);
									break;

								case 2:
									$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['discount'];
									$favourable_box['act_type_ext_format'] = floatval($favourable_activity['act_type_ext'] / 10);
									break;

								default:
									break;
								}

								$favourable_box['min_amount'] = $favourable_activity['min_amount'];
								$favourable_box['act_type_ext'] = intval($favourable_activity['act_type_ext']);
								$favourable_box['cart_fav_amount'] = cart_favourable_amount($favourable_activity, $act_sel_id);
								$favourable_box['available'] = favourable_available($favourable_activity, $act_sel_id);
								$cart_favourable = cart_favourable($row['ru_id']);
								$favourable_box['cart_favourable_gift_num'] = empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]);
								$favourable_box['favourable_used'] = favourable_used($favourable_activity, $cart_favourable);
								$favourable_box['left_gift_num'] = intval($favourable_activity['act_type_ext']) - (empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]));

								if ($favourable_activity['gift']) {
									$favourable_box['act_gift_list'] = $favourable_activity['gift'];
								}

								$favourable_box['act_goods_list'][$row1['rec_id']] = $row1;

								if (defined('THEME_EXTENSION')) {
									$favourable_box['act_goods_list_num'] = count($favourable_box['act_goods_list']);
								}
							}

							if ($row1['is_gift'] == $favourable_activity['act_id']) {
								$favourable_box['act_cart_gift'][$row1['rec_id']] = $row1;
							}

							unset($row1);
						}

						continue;
					}
				}

				if ($favourable_activity['act_range'] == FAR_BRAND && $row1['extension_code'] != 'package_buy') {
					$get_act_range_ext = get_act_range_ext($_SESSION['user_rank'], $row['ru_id'], 2);
					$brand_id = $row1['brand_id'];
					if (in_array(trim($brand_id), $get_act_range_ext) && $row1['is_gift'] == 0 || $row1['is_gift'] == $favourable_activity['act_id']) {
						if ($row1) {
							$act_range_ext_str = ',' . $favourable_activity['act_range_ext'] . ',';
							$brand_id_str = ',' . $brand_id . ',';
							if ($row1['is_gift'] == 0 && strstr($act_range_ext_str, trim($brand_id_str))) {
								$favourable_box['act_id'] = $favourable_activity['act_id'];
								$favourable_box['act_name'] = $favourable_activity['act_name'];
								$favourable_box['act_type'] = $favourable_activity['act_type'];

								switch ($favourable_activity['act_type']) {
								case 0:
									$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['With_a_gift'];
									$favourable_box['act_type_ext_format'] = intval($favourable_activity['act_type_ext']);
									break;

								case 1:
									$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['Full_reduction'];
									$favourable_box['act_type_ext_format'] = number_format($favourable_activity['act_type_ext'], 2);
									break;

								case 2:
									$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['discount'];
									$favourable_box['act_type_ext_format'] = floatval($favourable_activity['act_type_ext'] / 10);
									break;

								default:
									break;
								}

								$favourable_box['min_amount'] = $favourable_activity['min_amount'];
								$favourable_box['act_type_ext'] = intval($favourable_activity['act_type_ext']);
								$favourable_box['cart_fav_amount'] = cart_favourable_amount($favourable_activity, $act_sel_id);
								$favourable_box['available'] = favourable_available($favourable_activity, $act_sel_id);
								$cart_favourable = cart_favourable();
								$favourable_box['cart_favourable_gift_num'] = empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]);
								$favourable_box['favourable_used'] = favourable_used($favourable_activity, $cart_favourable);
								$favourable_box['left_gift_num'] = intval($favourable_activity['act_type_ext']) - (empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]));

								if ($favourable_activity['gift']) {
									$favourable_box['act_gift_list'] = $favourable_activity['gift'];
								}

								$favourable_box['act_goods_list'][$row1['rec_id']] = $row1;
							}

							if ($row1['is_gift'] == $favourable_activity['act_id']) {
								$favourable_box['act_cart_gift'][$row1['rec_id']] = $row1;
							}

							unset($row1);
						}

						continue;
					}
				}

				if ($favourable_activity['act_range'] == FAR_GOODS && $row1['extension_code'] != 'package_buy') {
					$get_act_range_ext = get_act_range_ext($_SESSION['user_rank'], $row['ru_id'], 3);
					if (in_array($row1['goods_id'], $get_act_range_ext) || $row1['is_gift'] == $favourable_activity['act_id']) {
						if ($row1) {
							$act_range_ext_str = ',' . $favourable_activity['act_range_ext'] . ',';
							$goods_id_str = ',' . $row1['goods_id'] . ',';
							if (strstr($act_range_ext_str, trim($goods_id_str)) && $row1['is_gift'] == 0) {
								$favourable_box['act_id'] = $favourable_activity['act_id'];
								$favourable_box['act_name'] = $favourable_activity['act_name'];
								$favourable_box['act_type'] = $favourable_activity['act_type'];

								switch ($favourable_activity['act_type']) {
								case 0:
									$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['With_a_gift'];
									$favourable_box['act_type_ext_format'] = intval($favourable_activity['act_type_ext']);
									break;

								case 1:
									$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['Full_reduction'];
									$favourable_box['act_type_ext_format'] = number_format($favourable_activity['act_type_ext'], 2);
									break;

								case 2:
									$favourable_box['act_type_txt'] = $GLOBALS['_LANG']['discount'];
									$favourable_box['act_type_ext_format'] = floatval($favourable_activity['act_type_ext'] / 10);
									break;

								default:
									break;
								}

								$favourable_box['min_amount'] = $favourable_activity['min_amount'];
								$favourable_box['act_type_ext'] = intval($favourable_activity['act_type_ext']);
								$favourable_box['cart_fav_amount'] = cart_favourable_amount($favourable_activity, $act_sel_id);
								$favourable_box['available'] = favourable_available($favourable_activity, $act_sel_id);
								$cart_favourable = cart_favourable($row['ru_id']);
								$favourable_box['cart_favourable_gift_num'] = empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]);
								$favourable_box['favourable_used'] = favourable_used($favourable_box, $cart_favourable);
								$favourable_box['left_gift_num'] = intval($favourable_activity['act_type_ext']) - (empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]));

								if ($favourable_activity['gift']) {
									$favourable_box['act_gift_list'] = $favourable_activity['gift'];
								}

								$favourable_box['act_goods_list'][$row1['rec_id']] = $row1;
							}

							if ($row1['is_gift'] == $favourable_activity['act_id']) {
								$favourable_box['act_cart_gift'][$row1['rec_id']] = $row1;
							}

							unset($row1);
						}
					}
				}
				else {
					$favourable_box[$row1['rec_id']] = $row1;
				}
			}
		}
	}

	return $favourable_box;
}

function get_regions_log($type = 0, $parent = 0)
{
	$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE region_type = \'' . $type . '\' AND parent_id = \'' . $parent . '\'');
	return $GLOBALS['db']->GetAll($sql);
}

function get_new_group_cart_goods($cart_goods_list_new)
{
	$car_goods = array();

	foreach ($cart_goods_list_new as $key => $goods) {
		foreach ($goods['goods_list'] as $k => $list) {
			$car_goods[] = $list;
		}
	}

	return $car_goods;
}

function get_is_cart_goods($user_id, $cart_value)
{
	$order_cart_where = '';
	$order_cart_value = explode(',', $cart_value);

	foreach ($order_cart_value as $key => $val) {
		$order_cart_where .= ' AND FIND_IN_SET(\'' . $val . '\', cart_recid) ';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE user_id = \'' . $user_id . '\' AND order_id = 0 ' . $order_cart_where);
	return $GLOBALS['db']->getOne($sql);
}

function get_order_cart_value($user_id, $cart_alue = '')
{
	$order_goods = array();

	if ($cart_alue) {
		$order_cart_where = '';
		$order_cart_value = explode(',', $cart_alue);

		foreach ($order_cart_value as $key => $val) {
			$order_cart_where .= ' AND FIND_IN_SET(\'' . $val . '\', og.cart_recid) ';
		}

		$sql = 'SELECT og.order_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og, ' . $GLOBALS['ecs']->table('order_info') . 'o ' . (' WHERE og.user_id = \'' . $user_id . '\' AND og.order_id = o.order_id AND o.main_order_id = 0 ' . $order_cart_where . ' LIMIT 1');
		$order_goods = $GLOBALS['db']->getRow($sql);
	}

	return $order_goods;
}

function del_unique_goods($order_id = 0)
{
	$sql = 'SELECT GROUP_CONCAT(cart_recid) AS cart_recid FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\'');
	$order_list = $GLOBALS['db']->getOne($sql);

	if ($order_list) {
		$order_list = explode(',', $order_list);
		$unique_values = array_count_values($order_list);

		foreach ($unique_values as $key => $row) {
			if (1 < $row) {
				$num = $row - 1;
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' AND cart_recid = \'' . $key . '\' ORDER BY rec_id DESC LIMIT ' . $num);
				$GLOBALS['db']->query($sql);
			}
		}
	}
}

function get_cart_goods_salve($goods_orec, $orec_goods)
{
	$orec_str = array();
	$is_solve_user = array();
	$no_solve_user = array();
	$cart_number = array();
	$goods_inventory = array();

	if ($goods_orec) {
		foreach ($goods_orec as $key => $row) {
			$orec_str[$key] = array_count_values($row);
			unset($row);
		}

		foreach ($orec_str as $key => $row) {
			foreach ($row as $k => $v) {
				if (1 < $v) {
					$ogoods = explode('|', $k);
					$ogoods_id = isset($ogoods[0]) && !empty($ogoods[0]) ? $ogoods[0] : 0;
					$omodel_attr = isset($ogoods[1]) && !empty($ogoods[1]) ? $ogoods[1] : 0;
					$ogoods_attr_id = isset($ogoods[2]) && !empty($ogoods[2]) ? $ogoods[2] : '';
					$owarehouse_id = isset($ogoods[3]) && !empty($ogoods[3]) ? $ogoods[3] : 0;
					$oarea_id = isset($ogoods[4]) && !empty($ogoods[4]) ? $ogoods[4] : 0;

					if (!empty($ogoods_attr_id)) {
						$products = get_warehouse_id_attr_number($ogoods_id, $ogoods_attr_id, 0, $owarehouse_id, $oarea_id);
						$goods_inventory[$key]['ogoods_number'] = !empty($products) ? $products['product_number'] : 0;
					}
					else if ($omodel_attr == 1) {
						$sql = 'SELECT region_number FROM ' . $GLOBALS['ecs']->table('warehouse_goods') . (' WHERE goods_id = \'' . $ogoods_id . '\' AND region_id = \'' . $owarehouse_id . '\'');
						$goods_inventory[$key]['ogoods_number'] = $GLOBALS['db']->getOne($sql, true);
					}
					else if ($omodel_attr == 2) {
						$sql = 'SELECT region_number FROM ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' WHERE goods_id = \'' . $ogoods_id . '\' AND region_id = \'' . $oarea_id . '\'');
						$goods_inventory[$key]['ogoods_number'] = $GLOBALS['db']->getOne($sql, true);
					}
					else {
						$sql = 'SELECT goods_number FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $ogoods_id . '\'');
						$goods_inventory[$key]['ogoods_number'] = $GLOBALS['db']->getOne($sql, true);
					}
				}
			}

			foreach ($orec_goods as $orkey => $orrow) {
				if ($orrow['goods_id'] == $key) {
					$cart_number[$key]['cgoods_number'] += $orrow['goods_number'];
				}
			}
		}
	}

	if ($cart_number) {
		krsort($goods_inventory);
		krsort($cart_number);

		foreach ($cart_number as $ckey => $crow) {
			$solve_number = $goods_inventory[$ckey]['ogoods_number'];

			if ($solve_number < $crow['cgoods_number']) {
				foreach ($orec_goods as $orkey => $orrow) {
					if ($orrow['goods_id'] == $ckey) {
						if ($orrow['goods_number'] <= $solve_number) {
							$solve_number = $solve_number - $orrow['goods_number'];
							$is_solve_user[$ckey] = $orrow['user_id'];
						}
						else {
							$no_solve_user[$ckey] = $orrow['user_id'];
						}
					}
				}
			}
		}
	}

	$arr = array('is_solve' => $is_solve_user, 'no_solve' => $no_solve_user);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/lib_area.php';
require ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/shopping_flow.php';
$warehouse_other = array('province_id' => $province_id, 'city_id' => $city_id);
$warehouse_area_info = get_warehouse_area_info($warehouse_other);
$region_id = $warehouse_area_info['region_id'];
$area_id = $warehouse_area_info['area_id'];
$area_city = $warehouse_area_info['city_id'];
if (isset($_COOKIE['region_id']) && !empty($_COOKIE['region_id'])) {
	$region_id = $_COOKIE['region_id'];
}

$smarty->assign('keywords', htmlspecialchars($_CFG['shop_keywords']));
$smarty->assign('description', htmlspecialchars($_CFG['shop_desc']));
$smarty->assign('flow_region', $_COOKIE['flow_region']);
$user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

if (!isset($_REQUEST['step'])) {
	$_REQUEST['step'] = 'cart';
}

if (!empty($_SESSION['user_id'])) {
	$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
	$a_sess = ' a.user_id = \'' . $_SESSION['user_id'] . '\' ';
	$b_sess = ' b.user_id = \'' . $_SESSION['user_id'] . '\' ';
	$c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
	$sess = '';
}
else {
	$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
	$a_sess = ' a.session_id = \'' . real_cart_mac_ip() . '\' ';
	$b_sess = ' b.session_id = \'' . real_cart_mac_ip() . '\' ';
	$c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
	$sess = real_cart_mac_ip();
}

assign_template();
$position = assign_ur_here(0, $_LANG['shopping_flow']);
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here', $position['ur_here']);
$smarty->assign('helps', get_shop_help());
$smarty->assign('lang', $_LANG);
$smarty->assign('show_marketprice', $_CFG['show_marketprice']);
$smarty->assign('data_dir', DATA_DIR);
$smarty->assign('user_id', $_SESSION['user_id']);

if ($_REQUEST['step'] == 'add_to_cart') {
	include_once 'includes/cls_json.php';
	$_POST['goods'] = strip_tags(urldecode($_POST['goods']));
	$_POST['goods'] = json_str_iconv($_POST['goods']);
	if (!empty($_REQUEST['goods_id']) && empty($_POST['goods'])) {
		if (!is_numeric($_REQUEST['goods_id']) || intval($_REQUEST['goods_id']) <= 0) {
			ecs_header("Location:./\n");
		}

		$goods_id = intval($_REQUEST['goods_id']);
		exit();
	}

	$result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '', 'divId' => '', 'confirm_type' => '', 'number' => '');
	$json = new JSON();

	if (empty($_POST['goods'])) {
		$result['error'] = 1;
		exit($json->encode($result));
	}

	$goods = $json->decode($_POST['goods']);
	$goods->stages_qishu = isset($goods->stages_qishu) && !empty($goods->stages_qishu) ? intval($goods->stages_qishu) : -1;

	if (0 < $goods->stages_qishu) {
		bt_auth_check($goods->stages_qishu);
	}

	$warehouse_id = intval($goods->warehouse_id);
	$area_id = intval($goods->area_id);
	$confirm_type = isset($goods->confirm_type) ? $goods->confirm_type : 0;

	if (0 < $goods->store_id) {
		clear_store_goods();
	}

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin = '';
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
		$sql = 'SELECT g.user_id, g.review_status, g.model_attr, g.goods_name, ' . ' IF(g.model_price < 1, g.goods_number, IF(g.model_price < 2, wg.region_number, wag.region_number)) AS goods_number ' . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . $leftJoin . ' WHERE g.goods_id = \'' . $goods->goods_id . '\'';
		$goodsInfo = $GLOBALS['db']->getRow($sql);
		$area_list = get_goods_link_area_list($goods->goods_id, $goodsInfo['user_id']);

		if ($area_list['goods_area']) {
			if (!in_array($area_id, $area_list['goods_area'])) {
				$no_area = 2;
			}
		}
		else {
			$no_area = 2;
		}

		if ($no_area == 2) {
			$result['error'] = 1;
			$result['message'] = $_LANG['shiping_prompt'];
			exit($json->encode($result));
		}
		else if ($goodsInfo['review_status'] <= 2) {
			$result['error'] = 1;
			$result['message'] = $_LANG['shelves_goods'];
			exit($json->encode($result));
		}
	}

	if (empty($goods->spec) && empty($goods->quick)) {
		$leftJoin = '';
		$select = '';
		$model_attr = get_table_date('goods', 'goods_id = \'' . $goods->goods_id . '\'', array('model_attr'), 2);

		if ($model_attr == 1) {
			$select = ' wap.attr_price as warehouse_attr_price, ';
			$leftJoin = 'LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_attr') . (' AS wap ON ga.goods_attr_id = wap.goods_attr_id AND wap.warehouse_id = \'' . $warehouse_id . '\' ');
		}
		else if ($model_attr == 2) {
			$select = ' waa.attr_price as area_attr_price, ';
			$leftJoin = 'LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_attr') . (' AS waa ON ga.goods_attr_id = waa.goods_attr_id AND area_id = \'' . $area_id . '\' ');
		}

		$sql = 'SELECT a.attr_id, a.attr_name, a.attr_type, ' . $select . 'ga.goods_attr_id, ga.attr_value, ga.attr_checked, ga.attr_sort, ga.attr_img_flie, ga.attr_img_site, ga.attr_price ' . 'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS ga ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.attr_id = ga.attr_id ' . $leftJoin . 'WHERE a.attr_type != 0 AND ga.goods_id = \'' . $goods->goods_id . '\' ' . $groupBy . 'ORDER BY a.sort_order, a.attr_id, ga.goods_attr_id';
		$res = $GLOBALS['db']->getAll($sql);

		if (!empty($res)) {
			$spe_arr = array();

			foreach ($res as $row) {
				if ($model_attr == 1) {
					$row['attr_price'] = $row['warehouse_attr_price'];
				}
				else if ($model_attr == 2) {
					$row['attr_price'] = $row['area_attr_price'];
				}

				$img_site = array('attr_img_flie' => $row['attr_img_flie'], 'attr_img_site' => $row['attr_img_site']);
				$attr_info = get_has_attr_info($row['attr_id'], $row['attr_value'], $img_site);
				$row['img_flie'] = !empty($attr_info['attr_img']) ? get_image_path($row['attr_id'], $attr_info['attr_img'], true) : '';
				$row['img_site'] = $attr_info['attr_site'];
				$spe_arr[$row['attr_id']]['attr_type'] = $row['attr_type'];
				$spe_arr[$row['attr_id']]['name'] = $row['attr_name'];
				$spe_arr[$row['attr_id']]['attr_id'] = $row['attr_id'];
				$spe_arr[$row['attr_id']]['values'][] = array('img_flie' => $row['img_flie'], 'img_site' => $row['img_site'], 'label' => $row['attr_value'], 'price' => $row['attr_price'], 'checked' => $row['attr_checked'], 'attr_sort' => $row['attr_sort'], 'format_price' => price_format($row['attr_price'], false), 'id' => $row['goods_attr_id']);
				$spe_arr[$row['attr_id']]['values'] = get_array_sort($spe_arr[$row['attr_id']]['values'], 'attr_sort');
				$spe_arr[$row['attr_id']]['is_checked'] = get_attr_values($spe_arr[$row['attr_id']]['values']);
			}

			$i = 0;
			$spe_array = array();

			foreach ($spe_arr as $row) {
				$spe_array[] = $row;
			}

			if (!empty($goods->divId)) {
				$result['divId'] = $goods->divId;
			}

			if (!empty($goods->confirm_type)) {
				$result['confirm_type'] = $goods->confirm_type;
			}

			if (!empty($goods->number)) {
				$result['number'] = $goods->number;
			}

			if ($GLOBALS['_CFG']['add_shop_price'] == 1) {
				$add_tocart = 1;
			}
			else {
				$add_tocart = 0;
			}

			$shop_price = get_final_price($goods->goods_id, 1, true, $attr_id, $warehouse_id, $area_id, 0, 0, $add_tocart);
			$result['error'] = ERR_NEED_SELECT_ATTR;
			$result['goods_id'] = $goods->goods_id;
			$result['warehouse_id'] = $warehouse_id;
			$result['area_id'] = $area_id;
			$result['parent'] = $goods->parent;
			$smarty->assign('spe_array', $spe_array);
			$smarty->assign('goods_id', $goods->goods_id);
			$smarty->assign('region_id', $warehouse_id);
			$smarty->assign('area_id', $area_id);
			$goods = get_goods_info($goods->goods_id, $warehouse_id, $area_id, array('is_xiangou', 'xiangou_num', 'xiangou_start_date', 'xiangou_end_date'));
			$user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
			$start_date = $goods['xiangou_start_date'];
			$end_date = $goods['xiangou_end_date'];
			$nowTime = gmtime();
			if ($start_date < $nowTime && $nowTime < $end_date) {
				$xiangou = 1;
			}
			else {
				$xiangou = 0;
			}

			$order_goods = get_for_purchasing_goods($start_date, $end_date, $goods->goods_id, $user_id);
			$smarty->assign('xiangou', $xiangou);
			$smarty->assign('orderG_number', $order_goods['goods_number']);
			$smarty->assign('goods', $goods);
			$smarty->assign('cfg', $_CFG);
			$result['message'] = $smarty->fetch('library/goods_attr.lbi');
			exit($json->encode($result));
		}
	}

	if ($_CFG['one_step_buy'] == '1') {
		clear_cart();
	}

	if (!is_numeric($goods->number) || intval($goods->number) <= 0) {
		$result['error'] = 1;
		$result['message'] = $_LANG['invalid_number'];
	}
	else {
		$nowTime = gmtime();
		$xiangouInfo = get_purchasing_goods_info($goods->goods_id);
		$start_date = $xiangouInfo['xiangou_start_date'];
		$end_date = $xiangouInfo['xiangou_end_date'];
		if ($xiangouInfo['is_xiangou'] == 1 && $start_date < $nowTime && $nowTime < $end_date) {
			$user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
			$sql = 'SELECT goods_number FROM ' . $ecs->table('cart') . ' WHERE goods_id = \'' . $goods->goods_id . '\' AND ' . $sess_id . ' LIMIT 1';
			$cartGoodsNumInfo = $db->getRow($sql);
			$orderGoods = get_for_purchasing_goods($start_date, $end_date, $goods->goods_id, $user_id);

			if ($xiangouInfo['xiangou_num'] <= $orderGoods['goods_number']) {
				$result['error'] = 1;
				$max_num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
				$result['message'] = sprintf($_LANG['purchasing_prompt'], $xiangouInfo['goods_name']);
				exit($json->encode($result));
			}
			else if (0 < $xiangouInfo['xiangou_num']) {
				if ($xiangouInfo['xiangou_num'] < $cartGoodsNumInfo['goods_number'] + $orderGoods['goods_number'] + $goods->number) {
					$result['error'] = 1;
					$result['message'] = $_LANG['purchasing_prompt_two'];
					exit($json->encode($result));
				}
			}
		}

		if (addto_cart($goods->goods_id, $goods->number, $goods->spec, $goods->parent, $warehouse_id, $area_id, $goods->stages_qishu, $goods->store_id, $goods->end_time, $goods->store_mobile)) {
			if (!empty($goods->divId)) {
				$result['divId'] = $goods->divId;
			}

			if (2 < $_CFG['cart_confirm']) {
				$result['message'] = '';
			}
			else {
				$result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
			}

			$result['goods_id'] = $goods->goods_id;
			$result['content'] = insert_cart_info(4);
			$result['one_step_buy'] = $_CFG['one_step_buy'];

			if (-1 < $goods->stages_qishu) {
				$_SESSION['flow_type'] = CART_GENERAL_GOODS;
			}
		}
		else {
			$result['message'] = $err->last_message();
			$result['error'] = $err->error_no;
			$result['goods_id'] = stripslashes($goods->goods_id);

			if (is_array($goods->spec)) {
				$result['product_spec'] = implode(',', $goods->spec);
			}
			else {
				$result['product_spec'] = $goods->spec;
			}
		}
	}

	if (!empty($goods->stages_qishu) || 0 < $goods->store_id) {
		if (!empty($goods->spec)) {
			$goods_attr_ids = implode(',', $goods->spec);
		}
		else {
			$goods_attr_ids = '';
		}

		$goods_attr_id_in = ' AND goods_attr_id = \'' . $goods_attr_ids . '\' ';
		if (isset($goods->store_id) && 0 < $goods->store_id) {
			$goods_attr_id_in .= ' AND store_id = \'' . $goods->store_id . '\'';
			$result['store_id'] = $goods->store_id;
		}

		$cart_value = $db->getOne('SELECT rec_id FROM ' . $ecs->table('cart') . (' WHERE goods_id=\'' . $goods->goods_id . '\' AND user_id=\'') . $_SESSION['user_id'] . ('\' ' . $goods_attr_id_in . ' '));
		$result['cart_value'] = $cart_value;
	}

	if (0 < $confirm_type) {
		$result['confirm_type'] = $confirm_type;
	}
	else {
		$result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
	}

	if (!empty($goods->number)) {
		$result['number'] = $goods->number;
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'add_to_cart_showDiv') {
	include_once 'includes/cls_json.php';
	$_POST['goods'] = strip_tags(urldecode($_POST['goods']));
	$_POST['goods'] = json_str_iconv($_POST['goods']);
	if (!empty($_REQUEST['goods_id']) && empty($_POST['goods'])) {
		if (!is_numeric($_REQUEST['goods_id']) || intval($_REQUEST['goods_id']) <= 0) {
			ecs_header("Location:./\n");
		}

		$goods_id = intval($_REQUEST['goods_id']);
		exit();
	}

	$result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '', 'goods_number' => '', 'subtotal' => '', 'script_name' => '', 'goods_recommend' => '');
	$json = new JSON();

	if (empty($_POST['goods'])) {
		$result['error'] = 1;
		exit($json->encode($result));
	}

	$goods = $json->decode($_POST['goods']);
	$goods->stages_qishu = isset($goods->stages_qishu) && !empty($goods->stages_qishu) ? intval($goods->stages_qishu) : -1;
	bt_auth_check($goods->stages_qishu);

	if (!empty($goods->script_name)) {
		$result['script_name'] = $goods->script_name;
	}
	else {
		$result['script_name'] = 0;
	}

	if (!empty($goods->goods_recommend)) {
		$result['goods_recommend'] = $goods->goods_recommend;
	}
	else {
		$result['goods_recommend'] = '';
	}

	if (empty($goods->spec) && empty($goods->quick)) {
		$groupBy = ' group by ga.goods_attr_id ';
		$leftJoin = '';
		$shop_price = 'wap.attr_price, wa.attr_price, g.model_attr, ';
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('goods') . ' as g on g.goods_id = ga.goods_id';
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_attr') . ' as wap on ga.goods_id = wap.goods_id and wap.warehouse_id = \'' . $goods->warehouse_id . '\' and ga.goods_attr_id = wap.goods_attr_id ';
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_attr') . ' as wa on ga.goods_id = wa.goods_id and wa.area_id = \'' . $goods->area_id . '\' and ga.goods_attr_id = wa.goods_attr_id ';
		$sql = 'SELECT a.attr_id, a.attr_name, a.attr_type, ' . 'ga.goods_attr_id, ga.attr_value, IF(g.model_attr < 1, ga.attr_price, IF(g.model_attr < 2, wap.attr_price, wa.attr_price)) as attr_price ' . 'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS ga ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.attr_id = ga.attr_id ' . $leftJoin . 'WHERE a.attr_type != 0 AND ga.goods_id = \'' . $goods->goods_id . '\' ' . $groupBy . 'ORDER BY a.sort_order, a.attr_id, ga.goods_attr_id';
		$res = $GLOBALS['db']->getAll($sql);

		if (!empty($res)) {
			$spe_arr = array();

			foreach ($res as $row) {
				$spe_arr[$row['attr_id']]['attr_type'] = $row['attr_type'];
				$spe_arr[$row['attr_id']]['name'] = $row['attr_name'];
				$spe_arr[$row['attr_id']]['attr_id'] = $row['attr_id'];
				$spe_arr[$row['attr_id']]['values'][] = array('label' => $row['attr_value'], 'price' => $row['attr_price'], 'format_price' => price_format($row['attr_price'], false), 'id' => $row['goods_attr_id']);
			}

			$i = 0;
			$spe_array = array();

			foreach ($spe_arr as $row) {
				$spe_array[] = $row;
			}

			$result['error'] = ERR_NEED_SELECT_ATTR;
			$result['goods_id'] = $goods->goods_id;
			$result['parent'] = $goods->parent;
			$result['message'] = $spe_array;

			if (!empty($goods->script_name)) {
				$result['script_name'] = $goods->script_name;
			}
			else {
				$result['script_name'] = 0;
			}

			exit($json->encode($result));
		}
	}

	if ($_CFG['one_step_buy'] == '1') {
		clear_cart();
	}

	if (!is_numeric($goods->number) || intval($goods->number) <= 0) {
		$result['error'] = 1;
		$result['message'] = $_LANG['invalid_number'];
	}
	else {
		$nowTime = gmtime();
		$xiangouInfo = get_purchasing_goods_info($goods->goods_id);
		$start_date = $xiangouInfo['xiangou_start_date'];
		$end_date = $xiangouInfo['xiangou_end_date'];
		if ($xiangouInfo['is_xiangou'] == 1 && $start_date < $nowTime && $nowTime < $end_date) {
			$user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
			$sql = 'SELECT goods_number FROM ' . $ecs->table('cart') . 'WHERE goods_id = ' . $goods->goods_id . ' and ' . $sess_id;
			$cartGoodsNumInfo = $db->getRow($sql);
			$orderGoods = get_for_purchasing_goods($start_date, $end_date, $goods->goods_id, $user_id);

			if ($xiangouInfo['xiangou_num'] <= $orderGoods['goods_number']) {
				$result['error'] = 1;
				$max_num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
				$result['message'] = sprintf($_LANG['purchasing_prompt'], get_table_date('goods', 'goods_id=\'' . $goods->goods_id . '\'', array('goods_name'), 2));
				$result['show_info'] = '';
				exit($json->encode($result));
			}
			else if (0 < $xiangouInfo['xiangou_num']) {
				if ($xiangouInfo['xiangou_num'] < $cartGoodsNumInfo['goods_number'] + $orderGoods['goods_number'] + $goods->number) {
					$result['error'] = 1;
					$result['message'] = $_LANG['purchasing_prompt_two'];
					$result['show_info'] = '';
					exit($json->encode($result));
				}
			}
		}

		if (addto_cart($goods->goods_id, $goods->number, $goods->spec, $goods->parent, $goods->warehouse_id, $goods->area_id, $goods->stages_qishu)) {
			if (2 < $_CFG['cart_confirm']) {
				$result['message'] = '';
			}
			else {
				$result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
			}

			$result['content'] = insert_cart_info(4);
			$result['one_step_buy'] = $_CFG['one_step_buy'];
		}
		else {
			$result['message'] = $err->last_message();
			$result['error'] = $err->error_no;
			$result['goods_id'] = stripslashes($goods->goods_id);

			if (is_array($goods->spec)) {
				$result['product_spec'] = implode(',', $goods->spec);
			}
			else {
				$result['product_spec'] = $goods->spec;
			}
		}
	}

	$result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$result['goods_id'] = $goods->goods_id;
	$cart_goods = get_cart_goods();
	$result['goods_number'] = 0;

	foreach ($cart_goods['goods_list'] as $val) {
		$result['goods_number'] += $val['goods_number'];
	}

	$result['show_info'] = insert_show_div_info($result['goods_number'], $result['script_name'], $result['goods_id'], $result['goods_recommend'], $cart_goods['total']['goods_amount'], $cart_goods['total']['real_goods_count']);
	$result['cart_num'] = $result['goods_number'];
	$cart_info = array('goods_list' => $cart_goods['goods_list'], 'number' => $result['goods_number'], 'amount' => $cart_goods['total']['goods_amount']);
	$GLOBALS['smarty']->assign('cart_info', $cart_info);
	$result['cart_content'] = $GLOBALS['smarty']->fetch('library/cart_menu_info.lbi');

	if (!empty($goods->stages_qishu)) {
		if (!empty($goods->spec)) {
			$goods_attr_ids = implode(',', $goods->spec);
		}
		else {
			$goods_attr_ids = '';
		}

		$goods_attr_id_in = ' AND ' . db_create_in($goods_attr_ids, 'goods_attr_id');
		$cart_value = $db->getOne('SELECT rec_id FROM ' . $ecs->table('cart') . (' WHERE goods_id=\'' . $goods->goods_id . '\' AND user_id=\'') . $_SESSION['user_id'] . ('\' ' . $goods_attr_id_in . ' '));
		$result['cart_value'] = $cart_value;
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'add_to_cart_combo') {
	include_once 'includes/cls_json.php';
	$_POST['goods'] = strip_tags(urldecode($_POST['goods']));
	$_POST['goods'] = json_str_iconv($_POST['goods']);
	if (!empty($_REQUEST['goods_id']) && empty($_POST['goods'])) {
		if (!is_numeric($_REQUEST['goods_id']) || intval($_REQUEST['goods_id']) <= 0) {
			ecs_header("Location:./\n");
		}

		$goods_id = intval($_REQUEST['goods_id']);
		exit();
	}

	$result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
	$json = new JSON();

	if (empty($_POST['goods'])) {
		$result['error'] = 1;
		exit($json->encode($result));
	}

	$goods = $json->decode($_POST['goods']);

	if ($_CFG['one_step_buy'] == '1') {
		clear_cart();
	}

	if (!is_numeric($goods->number) || intval($goods->number) <= 0) {
		$result['error'] = 1;
		$result['message'] = $_LANG['invalid_number'];
	}
	else {
		$nowTime = gmtime();
		$xiangouInfo = get_purchasing_goods_info($goods->goods_id);
		$start_date = $xiangouInfo['xiangou_start_date'];
		$end_date = $xiangouInfo['xiangou_end_date'];
		if ($xiangouInfo['is_xiangou'] == 1 && $start_date < $nowTime && $nowTime < $end_date) {
			$user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
			$sql = 'SELECT goods_number FROM ' . $ecs->table('cart') . 'WHERE goods_id = ' . $goods->goods_id . ' and ' . $sess_id;
			$cartGoodsNumInfo = $db->getRow($sql);
			$orderGoods = get_for_purchasing_goods($start_date, $end_date, $goods->goods_id, $user_id);

			if ($xiangouInfo['xiangou_num'] <= $orderGoods['goods_number']) {
				$result['error'] = 1;
				$max_num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
				$result['message'] = $_LANG['purchasing_prompt'];
				exit($json->encode($result));
			}
			else if (0 < $xiangouInfo['xiangou_num']) {
				if ($xiangouInfo['xiangou_num'] < $cartGoodsNumInfo['goods_number'] + $orderGoods['goods_number'] + $goods->number) {
					$result['error'] = 1;
					$result['message'] = $_LANG['purchasing_prompt_two'];
					exit($json->encode($result));
				}
			}
		}

		if (addto_cart_combo($goods->goods_id, $goods->number, $goods->spec, $goods->parent, $goods->group, $goods->warehouse_id, $goods->area_id, $goods->goods_attr)) {
			if (2 < $_CFG['cart_confirm']) {
				$result['message'] = '';
			}
			else {
				$result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
			}

			$result['group'] = $goods->group;
			$result['goods_id'] = stripslashes($goods->goods_id);
			$result['content'] = '';
			$result['one_step_buy'] = $_CFG['one_step_buy'];
			$warehouse_area['warehouse_id'] = $goods->warehouse_id;
			$warehouse_area['area_id'] = $goods->area_id;
			$combo_goods_info = get_combo_goods_info($goods->goods_id, $goods->number, $goods->spec, $goods->parent, $warehouse_area);
			$result['fittings_price'] = $combo_goods_info['fittings_price'];
			$result['spec_price'] = $combo_goods_info['spec_price'];
			$result['goods_price'] = $combo_goods_info['goods_price'];
			$result['stock'] = $combo_goods_info['stock'];
			$result['parent'] = $goods->parent;
		}
		else {
			$result['message'] = $err->last_message();
			$result['error'] = $err->error_no;
			$result['group'] = $goods->group;
			$result['goods_id'] = stripslashes($goods->goods_id);

			if (is_array($goods->spec)) {
				$result['product_spec'] = implode(',', $goods->spec);
			}
			else {
				$result['product_spec'] = $goods->spec;
			}
		}
	}

	$result['warehouse_id'] = $goods->warehouse_id;
	$result['area_id'] = $goods->area_id;
	$result['goods_attr'] = $goods->goods_attr;
	$result['goods_group'] = str_replace('_' . $goods->parent, '', $goods->group);
	$combo_goods = get_cart_combo_goods_list($goods->goods_id, $goods->parent, $goods->group);
	$result['combo_amount'] = $combo_goods['combo_amount'];
	$result['combo_number'] = $combo_goods['combo_number'];
	$result['add_group'] = $goods->add_group;
	$parent_id = $goods->parent;
	$warehouse_id = $goods->warehouse_id;
	$area_id = $goods->area_id;
	$rev = $goods->group;
	$fitt_goods = isset($goods->fitt_goods) ? $goods->fitt_goods : array();

	if (!in_array($goods->goods_id, $fitt_goods)) {
		array_unshift($fitt_goods, $goods->goods_id);
	}

	$goods_info = get_goods_fittings_info($parent_id, $warehouse_id, $area_id, $rev);
	$fittings = get_goods_fittings(array($parent_id), $warehouse_id, $area_id, $rev, 1);
	$fittings = array_merge($goods_info, $fittings);
	$fittings = array_values($fittings);
	$fittings_interval = get_choose_goods_combo_cart($fittings);

	if ($fittings_interval['return_attr'] < 1) {
		$result['fittings_minMax'] = price_format($fittings_interval['all_price_ori']);
		$result['market_minMax'] = price_format($fittings_interval['all_market_price']);
		$result['save_minMaxPrice'] = price_format($fittings_interval['save_price_amount']);
	}
	else {
		$result['fittings_minMax'] = price_format($fittings_interval['fittings_min']) . '-' . number_format($fittings_interval['fittings_max'], 2, '.', '');
		$result['market_minMax'] = price_format($fittings_interval['market_min']) . '-' . number_format($fittings_interval['market_max'], 2, '.', '');

		if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
			$result['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']);
		}
		else {
			$result['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']) . '-' . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
		}
	}

	$goodsGroup = explode('_', $goods->group);
	$result['groupId'] = $goodsGroup[2];
	$result['fitt_goods'] = $fitt_goods;
	$result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'del_in_cart_combo') {
	include_once 'includes/cls_json.php';
	$_POST['goods'] = strip_tags(urldecode($_POST['goods']));
	$_POST['goods'] = json_str_iconv($_POST['goods']);
	if (!empty($_REQUEST['goods_id']) && empty($_POST['goods'])) {
		if (!is_numeric($_REQUEST['goods_id']) || intval($_REQUEST['goods_id']) <= 0) {
			ecs_header("Location:./\n");
		}

		$goods_id = intval($_REQUEST['goods_id']);
		exit();
	}

	$result = array('error' => 0, 'message' => '');
	$json = new JSON();

	if (empty($_POST['goods'])) {
		$result['error'] = 1;
		exit($json->encode($result));
	}

	$goods = $json->decode($_POST['goods']);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart_combo') . ' WHERE ' . $sess_id . ' AND goods_id = \'' . $goods->goods_id . '\' AND group_id = \'' . $goods->group . '\'';
	$GLOBALS['db']->query($sql);
	$sql = 'select count(*) from ' . $GLOBALS['ecs']->table('cart_combo') . ' where ' . $sess_id . ' and parent_id = \'' . $goods->parent . '\' AND group_id = \'' . $goods->group . '\'';
	$rec_count = $GLOBALS['db']->getOne($sql);

	if ($rec_count < 1) {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart_combo') . ' WHERE ' . $sess_id . ' AND goods_id = \'' . $goods->parent . '\' AND parent_id = 0  AND group_id = \'' . $goods->group . '\'';
		$GLOBALS['db']->query($sql);
	}

	$result['error'] = 0;
	$result['group'] = substr($goods->group, 0, strrpos($goods->group, '_'));
	$result['parent'] = $goods->parent;
	$combo_goods = get_cart_combo_goods_list($goods->goods_id, $goods->parent, $goods->group);

	if (empty($combo_goods['shop_price'])) {
		$shop_price = get_final_price($goods->parent, 1, true, $goods->goods_attr, $goods->warehouse_id, $goods->area_id);
		$combo_goods['combo_amount'] = price_format($shop_price, false);
	}

	$result['combo_amount'] = $combo_goods['combo_amount'];
	$result['combo_number'] = $combo_goods['combo_number'];
	$parent_id = $goods->parent;
	$warehouse_id = $goods->warehouse_id;
	$area_id = $goods->area_id;
	$rev = $goods->group;

	if (0 < $combo_goods['combo_number']) {
		$goods_info = get_goods_fittings_info($parent_id, $warehouse_id, $area_id, $rev);
		$fittings = get_goods_fittings(array($parent_id), $warehouse_id, $area_id, $rev, 1);
	}
	else {
		$goods_info = get_goods_fittings_info($parent_id, $warehouse_id, $area_id, '', 1);
		$fittings = get_goods_fittings(array($parent_id), $warehouse_id, $area_id);
	}

	$fittings = array_merge($goods_info, $fittings);
	$fittings = array_values($fittings);
	$fittings_interval = get_choose_goods_combo_cart($fittings);

	if (0 < $combo_goods['combo_number']) {
		if ($fittings_interval['return_attr'] < 1) {
			$result['fittings_minMax'] = price_format($fittings_interval['all_price_ori']);
			$result['market_minMax'] = price_format($fittings_interval['all_market_price']);
			$result['save_minMaxPrice'] = price_format($fittings_interval['save_price_amount']);
		}
		else {
			$result['fittings_minMax'] = price_format($fittings_interval['fittings_min']) . '-' . number_format($fittings_interval['fittings_max'], 2, '.', '');
			$result['market_minMax'] = price_format($fittings_interval['market_min']) . '-' . number_format($fittings_interval['market_max'], 2, '.', '');

			if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
				$result['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']);
			}
			else {
				$result['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']) . '-' . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
			}
		}
	}
	else {
		$result['fittings_minMax'] = price_format($fittings_interval['fittings_min']) . '-' . number_format($fittings_interval['fittings_max'], 2, '.', '');
		$result['market_minMax'] = price_format($fittings_interval['market_min']) . '-' . number_format($fittings_interval['market_max'], 2, '.', '');

		if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
			$result['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']);
		}
		else {
			$result['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']) . '-' . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
		}
	}

	$goodsGroup = explode('_', $goods->group);
	$result['groupId'] = $goodsGroup[2];
	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'add_to_cart_group') {
	include_once 'includes/cls_json.php';
	$_POST['goods'] = strip_tags(urldecode($_POST['goods']));
	$_POST['goods'] = json_str_iconv($_POST['goods']);
	$result = array('error' => 0, 'message' => '');
	$json = new JSON();

	if (empty($_POST['goods'])) {
		$result['error'] = 1;
		$result['message'] = $_LANG['system_error'];
		exit($json->encode($result));
	}

	$goods = $json->decode($_POST['goods']);
	$group = $goods->group . '_' . $goods->goods_id;
	$sql = 'SELECT rec_id FROM ' . $GLOBALS['ecs']->table('cart_combo') . ' WHERE ' . $sess_id . ' AND group_id = \'' . $group . '\' ORDER BY parent_id limit 1';
	$res = $GLOBALS['db']->query($sql);

	if ($res) {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . ' AND group_id = \'' . $group . '\'';
		$GLOBALS['db']->query($sql);
		$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('cart') . '(' . 'user_id, session_id, goods_id, goods_sn, product_id, group_id, goods_name, market_price, goods_price, goods_number, goods_attr, is_real, ' . 'extension_code, parent_id, rec_type, is_gift, is_shipping, can_handsel, model_attr, goods_attr_id, warehouse_id, area_id, add_time' . ')' . ' SELECT ' . 'user_id, session_id, goods_id, goods_sn, product_id, group_id, goods_name, market_price, goods_price, goods_number, goods_attr, is_real, ' . 'extension_code, parent_id, rec_type, is_gift, is_shipping, can_handsel, model_attr, goods_attr_id, warehouse_id, area_id, add_time' . ' FROM ' . $GLOBALS['ecs']->table('cart_combo') . ' WHERE ' . $sess_id . ' AND group_id = \'' . $group . '\'';
		$GLOBALS['db']->query($sql);
		$sql = ' SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id = \'' . $goods->goods_id . '\' ';
		$ru_id = $GLOBALS['db']->getOne($sql, true);
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number = \'' . $goods->number . '\', ru_id = \'' . $ru_id . '\' WHERE ') . $sess_id . ' AND group_id = \'' . $group . '\'';
		$GLOBALS['db']->query($sql);
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart_combo') . ' WHERE ' . $sess_id . ' AND group_id = \'' . $group . '\'';
		$GLOBALS['db']->query($sql);
	}
	else {
		$result['error'] = 1;
		$result['message'] = $_LANG['data_null'];
		exit($json->encode($result));
	}

	$result['error'] = 0;
	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'add_cart_combo_list') {
	include_once 'includes/cls_json.php';
	$_POST['group'] = strip_tags(urldecode($_POST['group']));
	$_POST['group'] = json_str_iconv($_POST['group']);
	$result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
	$json = new JSON();

	if (empty($_POST['group'])) {
		$result['error'] = 1;
		exit($json->encode($result));
	}

	$group = $json->decode($_POST['group']);
	$number = $group->number;
	$goods = explode('_', $group->rev);
	$goodSEqual = isset($group->fitt_goods) ? $group->fitt_goods : array();
	$goods_id = $goods[3];
	$warehouse_id = $goods[4];
	$area_id = $goods[5];
	$rev = $goods[0] . '_' . $goods[1] . '_' . $goods[2] . '_' . $goods[3];
	$group = $goods[0] . '_' . $goods[1] . '_' . $goods[2];
	$result['groupId'] = $goods[2];
	$result['number'] = $number;

	if (!empty($number)) {
		$smarty->assign('number', $number);
	}

	$result['group'] = $group;
	$result['goods_id'] = $goods_id;
	$result['warehouse_id'] = $warehouse_id;
	$result['area_id'] = $area_id;
	$smarty->assign('group', $group);
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$smarty->assign('goods_id', $goods_id);
	$list_select = get_combo_goods_list_select(0, $goods[3], $rev);
	$combo_goods = get_cart_combo_goods_list(0, $goods[3], $rev);
	$result['group_rev'] = $goods[0] . '_' . $goods[1] . '_' . $goods[2] . '_' . $goods[3] . '_' . $goods[4] . '_' . $goods[5];
	$smarty->assign('group_rev', $result['group_rev']);
	$fittings_top = get_goods_fittings(array($goods_id), $warehouse_id, $area_id, $goods[2], 2);
	$fittings_top = array_values($fittings_top);
	$smarty->assign('fittings_top', $fittings_top);
	$smarty->assign('list_select', $list_select);

	if ($goodSEqual) {
		$goods_info = get_goods_fittings_info($goods_id, $warehouse_id, $area_id, $rev);
		$fittings = get_goods_fittings(array($goods_id), $warehouse_id, $area_id, $rev, 1, $goodSEqual);
		$fittings = array_merge($goods_info, $fittings);
		$fittings = array_values($fittings);
		$fittings_interval = get_choose_goods_combo_cart($fittings, $number);
		$result['amount'] = !empty($fittings_interval['fittings_price']) ? $fittings_interval['fittings_price'] : 0;

		if ($list_select == 1) {
			$result['goods_amount'] = !empty($fittings_interval['fittings_price']) ? price_format($fittings_interval['fittings_price']) : 0;
			$result['goods_market_amount'] = !empty($fittings_interval['all_market_price']) ? price_format($fittings_interval['all_market_price']) : 0;
			$result['save_amount'] = price_format($fittings_interval['save_price_amount']);
		}
		else {
			$result['goods_amount'] = price_format($fittings_interval['fittings_min']) . '-' . number_format($fittings_interval['fittings_max'], 2, '.', '');

			if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
				$result['save_amount'] = price_format($fittings_interval['save_minPrice']);
			}
			else {
				$result['save_amount'] = price_format($fittings_interval['save_minPrice']) . '-' . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
			}

			$result['goods_market_amount'] = price_format($fittings_interval['market_min']) . '-' . number_format($fittings_interval['market_max'], 2, '.', '');
		}

		$result['fittings_minMax'] = $result['goods_amount'];
		$result['market_minMax'] = $result['goods_market_amount'];
		$result['save_minMaxPrice'] = $result['save_amount'];
	}
	else {
		if (0 < $combo_goods['combo_number']) {
			$goods_info = get_goods_fittings_info($goods_id, $warehouse_id, $area_id, $rev);
			$fittings = get_goods_fittings(array($goods_id), $warehouse_id, $area_id, $rev, 1);
		}
		else {
			$goods_info = get_goods_fittings_info($goods_id, $warehouse_id, $area_id, '', 1);
			$fittings = get_goods_fittings(array($goods_id), $warehouse_id, $area_id);
		}

		$fittings = array_merge($goods_info, $fittings);
		$fittings = array_values($fittings);
		$fittings_interval = get_choose_goods_combo_cart($fittings);

		if (0 < $combo_goods['combo_number']) {
			if ($list_select == 1) {
				$result['fittings_minMax'] = price_format($fittings_interval['all_price_ori']);
				$result['market_minMax'] = price_format($fittings_interval['all_market_price']);
				$result['save_minMaxPrice'] = price_format($fittings_interval['save_price_amount']);
			}
			else {
				if ($fittings_interval['return_attr'] < 1) {
					$result['fittings_minMax'] = price_format($fittings_interval['all_price_ori']);
					$result['save_minMaxPrice'] = price_format($fittings_interval['save_price_amount']);
				}
				else {
					$result['fittings_minMax'] = price_format($fittings_interval['fittings_min']) . '-' . number_format($fittings_interval['fittings_max'], 2, '.', '');

					if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
						$result['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']);
					}
					else {
						$result['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']) . '-' . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
					}
				}

				if ($fittings_interval['return_attr'] < 1) {
					$result['market_minMax'] = price_format($fittings_interval['all_market_price']);
				}
				else {
					$result['market_minMax'] = price_format($fittings_interval['market_min']) . '-' . number_format($fittings_interval['market_max'], 2, '.', '');
				}
			}
		}
		else {
			$result['fittings_minMax'] = price_format($fittings_interval['fittings_min']) . '-' . number_format($fittings_interval['fittings_max'], 2, '.', '');

			if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
				$result['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']);
			}
			else {
				$result['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']) . '-' . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
			}

			$result['market_minMax'] = price_format($fittings_interval['market_min']) . '-' . number_format($fittings_interval['market_max'], 2, '.', '');
		}
	}

	$result['list_select'] = $list_select;
	$result['null_money'] = price_format(0);
	$result['collocation_number'] = $fittings_interval['collocation_number'];

	if ($combo_goods['combo_number'] < 1) {
		$fittings = array();
	}

	$result['spe_conut'] = 0;

	if ($fittings) {
		foreach ($fittings as $k => $v) {
			if ($v['properties']['spe']) {
				$result['spe_conut']++;
			}
		}
	}

	$smarty->assign('fittings', $fittings);
	$smarty->assign('fittings_minMax', $result['fittings_minMax']);
	$smarty->assign('market_minMax', $result['market_minMax']);
	$smarty->assign('save_minMaxPrice', $result['save_minMaxPrice']);
	$smarty->assign('collocation_number', $result['collocation_number']);
	$sql = 'SELECT group_number FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\'');
	$group_number = $GLOBALS['db']->getOne($sql, true);
	$smarty->assign('group_number', $group_number);
	$smarty->assign('null_money', price_format(0));
	$smarty->assign('goods_id', $goods_id);
	$result['content'] = $smarty->fetch('library/goods_fittings_result.lbi');
	$result['content_type'] = $smarty->fetch('library/goods_fittings_result_type.lbi');
	exit($json->encode($result));
}

if ($_REQUEST['step'] == 'add_cart_combo_goodsAttr') {
	include_once 'includes/cls_json.php';
	$_POST['group'] = strip_tags(urldecode($_POST['group']));
	$_POST['group'] = json_str_iconv($_POST['group']);
	$result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '', 'goods_amount' => 0);
	$json = new JSON();

	if (empty($_POST['group'])) {
		$result['error'] = 1;
		exit($json->encode($result));
	}

	$group = $json->decode($_POST['group']);
	$goodsRow = explode('_', $group->group_rev);
	$goodSEqual = $group->fitt_goods;
	$type = $group->type;
	$tImg = $group->tImg;
	$attr_id = $group->attr;
	$number = 1;
	$goods_id = $group->goods_id;
	$fittings_goods = $group->fittings_goods;
	$fittings_attr = $group->fittings_attr;
	$warehouse_id = $goodsRow[4];
	$area_id = $goodsRow[5];
	$group_id = $goodsRow[0] . '_' . $goodsRow[1] . '_' . $goodsRow[2] . '_' . $goodsRow[3];
	$goods = get_goods_info($goods_id, $warehouse_id, $area_id);

	if ($goods_id == 0) {
		$result['message'] = $_LANG['err_change_attr'];
		$result['error'] = 1;
	}
	else {
		if ($number == 0) {
			$result['qty'] = $number = 1;
		}
		else {
			$result['qty'] = $number;
		}

		$group_attr = implode('|', $group->attr);
		$products = get_warehouse_id_attr_number($goods_id, $group_attr, $goods['user_id'], $warehouse_id, $area_id);
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
		$result['attr_number'] = $attr_number;

		if ($GLOBALS['_CFG']['add_shop_price'] == 1) {
			$add_tocart = 1;
		}
		else {
			$add_tocart = 0;
		}

		$shop_price = get_final_price($goods_id, $number, true, $attr_id, $warehouse_id, $area_id, 0, 0, $add_tocart);
		$prod_attr = array();

		if (!empty($prod['goods_attr'])) {
			$prod_attr = explode('|', $prod['goods_attr']);
		}

		if (count($prod_attr) <= 1) {
			if (empty($result['attr_number'])) {
				$result['message'] = $_LANG['Stock_goods_null'];
			}
		}
		else if (1 < count($prod_attr)) {
			if (count($prod_attr) == count($attr_id)) {
				if (empty($result['attr_number'])) {
					$result['message'] = $_LANG['Stock_goods_null'];
				}
			}
			else {
				unset($result['attr_number']);
			}
		}

		if (is_spec($prod_attr) && !empty($prod)) {
			$product_info = get_products_info($goods_id, $prod_attr, $warehouse_id, $area_id);
		}

		$warehouse_area = array('warehouse_id' => $warehouse_id, 'area_id' => $area_id);
		$spec_price = spec_price($attr_id, $goods_id, $warehouse_area);
		$goods_attr = get_goods_attr_info($attr_id, 'pice', $warehouse_id, $area_id);
		$parent = array('goods_attr_id' => implode(',', $attr_id), 'product_id' => $product_info['product_id'], 'goods_attr' => addslashes($goods_attr));
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart_combo'), $parent, 'UPDATE', 'group_id = \'' . $group_id . '\' AND goods_id = \'' . $goods_id . '\' AND ' . $sess_id);

		if ($type == 1) {
			$goods_price = $shop_price;
		}
		else {
			$sql = 'select goods_price from ' . $GLOBALS['ecs']->table('group_goods') . ' where parent_id = \'' . $goodsRow[3] . ('\' and goods_id = \'' . $goods_id . '\' and group_id = \'') . $goodsRow[2] . '\'';
			$goods_price = $GLOBALS['db']->getOne($sql);

			if ($GLOBALS['_CFG']['add_shop_price'] == 1) {
				$goods_price = $goods_price + $spec_price;
			}
		}

		$img_flie = '';

		if (!empty($tImg)) {
			$img_flie = ', img_flie = \'' . $tImg . '\'';
		}

		$sql = 'update ' . $GLOBALS['ecs']->table('cart_combo') . (' set goods_price = \'' . $goods_price . '\' ') . $img_flie . (' where group_id = \'' . $group_id . '\' AND goods_id = \'' . $goods_id . '\' AND ') . $sess_id;
		$GLOBALS['db']->query($sql);
		$result['goods_id'] = $goods_id;
		$result['shop_price'] = price_format($shop_price);
		$result['market_price'] = $goods['market_price'];
		$result['result'] = price_format($shop_price * $number);
		$result['groupId'] = $goodsRow[2];
		$attr_type_list = get_goods_attr_type_list($goods_id, 1);

		if ($attr_type_list == count($attr_id)) {
			$result['attr_equal'] = 1;
		}
		else {
			$result['attr_equal'] = 0;
		}

		$goods_info = get_goods_fittings_info($goodsRow[3], $warehouse_id, $area_id, $group_id, 0, $fittings_goods, $fittings_attr);
		$fittings = get_goods_fittings(array($goodsRow[3]), $warehouse_id, $area_id, $group_id, 1, $goodSEqual);
		$fittings = array_merge($goods_info, $fittings);
		$fittings = array_values($fittings);
		$fittings_interval = get_choose_goods_combo_cart($fittings);

		if ($fittings_interval['return_attr'] < 1) {
			$result['amount'] = !empty($fittings_interval['all_price_ori']) ? $fittings_interval['all_price_ori'] : 0;
			$result['goods_amount'] = !empty($fittings_interval['all_price_ori']) ? price_format($fittings_interval['all_price_ori']) : 0;
		}
		else {
			$result['amount'] = !empty($fittings_interval['fittings_price']) ? $fittings_interval['fittings_price'] : 0;
			$result['goods_amount'] = !empty($fittings_interval['fittings_price']) ? price_format($fittings_interval['fittings_price']) : 0;
		}

		$result['goods_market_amount'] = !empty($fittings_interval['all_market_price']) ? price_format($fittings_interval['all_market_price']) : 0;
		$result['save_amount'] = price_format($fittings_interval['save_price_amount']);
	}

	$list_select = get_combo_goods_list_select(0, $goodsRow[3], $group_id);
	$result['list_select'] = $list_select;
	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'add_del_cart_combo_list') {
	include_once 'includes/cls_json.php';
	$_POST['group'] = strip_tags(urldecode($_POST['group']));
	$_POST['group'] = json_str_iconv($_POST['group']);
	$result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
	$json = new JSON();

	if (empty($_POST['group'])) {
		$result['error'] = 1;
		exit($json->encode($result));
	}

	$group = $json->decode($_POST['group']);
	$goodsRow = explode('|', $group->group_rev);
	$goods_id = $goodsRow[0];
	$group_id = str_replace('=', '_', $goodsRow[3]);
	$goodsRow2 = explode('=', $goodsRow[3]);
	$parent_id = $goodsRow2[1];
	$goodSEqual = $group->fitt_goods;
	$sql = 'delete from ' . $GLOBALS['ecs']->table('cart_combo') . (' where goods_id = \'' . $goods_id . '\' and group_id = \'' . $group_id . '\' and ') . $sess_id;
	$GLOBALS['db']->query($sql);
	$sql = 'select count(*) from ' . $GLOBALS['ecs']->table('cart_combo') . ' where ' . $sess_id . (' and parent_id = \'' . $parent_id . '\' AND group_id = \'' . $group_id . '\'');
	$rec_count = $GLOBALS['db']->getOne($sql);

	if ($rec_count < 1) {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart_combo') . ' WHERE ' . $sess_id . (' AND goods_id = \'' . $parent_id . '\' AND parent_id = 0  AND group_id = \'' . $group_id . '\'');
		$GLOBALS['db']->query($sql);
		$result['fitt_goods'] = '';
	}
	else {
		$arr = array();

		foreach ($goodSEqual as $key => $row) {
			if ($row != $goods_id) {
				$arr[$key] = $row;
			}
		}
	}

	$result['fitt_goods'] = $arr;
	$result['add_group'] = $group->add_group;
	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'link_buy') {
	$goods_id = intval($_GET['goods_id']);

	if (!cart_goods_exists($goods_id, array())) {
		addto_cart($goods_id);
	}

	ecs_header("Location:./flow.php\n");
	exit();
}
else if ($_REQUEST['step'] == 'login') {
	include_once 'languages/' . $_CFG['lang'] . '/user.php';

	if (0 < $_SESSION['user_id']) {
		ecs_header("Location:./flow.php?step=consignee\n");
		exit();
	}

	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		$smarty->assign('anonymous_buy', $_CFG['anonymous_buy']);
		$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('cart') . ' WHERE ' . $sess_id . ' AND is_gift > 0';

		if (0 < $db->getOne($sql)) {
			$smarty->assign('need_rechoose_gift', 1);
		}

		$captcha = intval($_CFG['captcha']);
		if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
			$smarty->assign('enabled_login_captcha', 1);
			$smarty->assign('rand', mt_rand());
		}

		if ($captcha & CAPTCHA_REGISTER) {
			$smarty->assign('enabled_register_captcha', 1);
			$smarty->assign('rand', mt_rand());
		}
	}
	else {
		include_once 'includes/lib_passport.php';
		if (!empty($_POST['act']) && $_POST['act'] == 'signin') {
			$captcha = intval($_CFG['captcha']);
			if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
				if (empty($_POST['captcha'])) {
					show_message($_LANG['invalid_captcha']);
				}

				include_once 'includes/cls_captcha.php';
				$validator = new captcha();
				$validator->session_word = 'captcha_login';

				if (!$validator->check_word($_POST['captcha'])) {
					show_message($_LANG['invalid_captcha']);
				}
			}

			if ($user->login($_POST['username'], $_POST['password'], isset($_POST['remember']))) {
				update_user_info();
				recalculate_price();

				if (!empty($_SESSION['user_id'])) {
					$login_sess = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
				}
				else {
					$login_sess = ' session_id = \'' . real_cart_mac_ip() . '\' ';
				}

				$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('cart') . ' WHERE ' . $login_sess;

				if (0 < $db->getOne($sql)) {
					ecs_header("Location: flow.php\n");
				}
				else {
					ecs_header("Location:index.php\n");
				}

				exit();
			}
			else {
				$_SESSION['login_fail']++;
				show_message($_LANG['signin_failed'], '', 'user.php');
			}
		}
		else {
			if (!empty($_POST['act']) && $_POST['act'] == 'signup') {
				if (intval($_CFG['captcha']) & CAPTCHA_REGISTER && 0 < gd_version()) {
					if (empty($_POST['captcha'])) {
						show_message($_LANG['invalid_captcha']);
					}

					include_once 'includes/cls_captcha.php';
					$validator = new captcha();

					if (!$validator->check_word($_POST['captcha'])) {
						show_message($_LANG['invalid_captcha']);
					}
				}

				if (register(trim($_POST['username']), trim($_POST['password']), trim($_POST['email']))) {
					ecs_header("Location: flow.php?step=consignee\n");
					exit();
				}
				else {
					$err->show();
				}
			}
		}
	}
}
else if ($_REQUEST['step'] == 'consignee') {
	include_once 'includes/lib_transaction.php';

	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		require ROOT_PATH . '/includes/lib_area.php';

		if (isset($_REQUEST['direct_shopping'])) {
			$_SESSION['direct_shopping'] = 1;
		}

		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$smarty->assign('country_list', get_regions());
		$smarty->assign('shop_country', $_CFG['shop_country']);
		$smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));

		if (0 < $_SESSION['user_id']) {
			$consignee_list = get_consignee_list($_SESSION['user_id']);

			if (count($consignee_list) < 5) {
				$consignee_list[] = array('country' => $_CFG['shop_country'], 'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '');
			}
		}
		else if (isset($_SESSION['flow_consignee'])) {
			$consignee_list = array($_SESSION['flow_consignee']);
		}
		else {
			$consignee_list[] = array('country' => $_CFG['shop_country'], 'province' => $province_id, 'city' => $city_id, 'district' => $district_id);
		}

		$smarty->assign('name_of_region', array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));
		$smarty->assign('consignee_list', $consignee_list);
		$province_list = array();
		$city_list = array();
		$district_list = array();

		foreach ($consignee_list as $region_id => $consignee) {
			$consignee['country'] = isset($consignee['country']) ? intval($consignee['country']) : 1;
			$consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : $province_id;
			$consignee['city'] = isset($consignee['city']) ? intval($consignee['city']) : $city_id;
			$province_list[$region_id] = get_regions(1, $consignee['country']);
			$city_list[$region_id] = get_regions(2, $consignee['province']);
			$district_list[$region_id] = get_regions(3, $consignee['city']);
		}

		$smarty->assign('province_list', $province_list);
		$smarty->assign('city_list', $city_list);
		$smarty->assign('district_list', $district_list);
		$smarty->assign('real_goods_count', exist_real_goods(0, $flow_type) ? 1 : 0);
	}
	else {
		$consignee = array('address_id' => !isset($_POST['address_id']) && empty($_POST['address_id']) ? 0 : intval($_POST['address_id']), 'consignee' => !isset($_POST['consignee']) && empty($_POST['consignee']) ? '' : compile_str(trim($_POST['consignee'])), 'country' => !isset($_POST['country']) && empty($_POST['country']) ? 0 : intval($_POST['country']), 'province' => !isset($_POST['province']) && empty($_POST['province']) ? 0 : intval($_POST['province']), 'city' => !isset($_POST['city']) && empty($_POST['city']) ? 0 : intval($_POST['city']), 'district' => !isset($_POST['district']) && empty($_POST['district']) ? 0 : intval($_POST['district']), 'street' => !isset($_POST['street']) && empty($_POST['street']) ? 0 : intval($_POST['street']), 'email' => !isset($_POST['email']) && empty($_POST['email']) ? '' : compile_str($_POST['email']), 'address' => !isset($_POST['address']) && empty($_POST['address']) ? '' : compile_str($_POST['address']), 'zipcode' => !isset($_POST['zipcode']) && empty($_POST['zipcode']) ? '' : compile_str(make_semiangle(trim($_POST['zipcode']))), 'tel' => !isset($_POST['tel']) && empty($_POST['tel']) ? '' : compile_str(make_semiangle(trim($_POST['tel']))), 'mobile' => !isset($_POST['mobile']) && empty($_POST['mobile']) ? '' : compile_str(make_semiangle(trim($_POST['mobile']))), 'sign_building' => !isset($_POST['sign_building']) && empty($_POST['sign_building']) ? '' : compile_str($_POST['sign_building']), 'best_time' => !isset($_POST['best_time']) && empty($_POST['best_time']) ? '' : compile_str($_POST['best_time']));

		if (0 < $_SESSION['user_id']) {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			$consignee['user_id'] = $_SESSION['user_id'];
			save_consignee($consignee, true);
		}

		$_SESSION['flow_consignee'] = stripslashes_deep($consignee);
		ecs_header("Location: flow.php?step=checkout&direct_shopping=1\n");
		exit();
	}
}
else if ($_REQUEST['step'] == 'drop_consignee') {
	include_once 'includes/lib_transaction.php';
	$consignee_id = intval($_GET['id']);

	if (drop_consignee($consignee_id)) {
		ecs_header("Location: flow.php?step=consignee\n");
		exit();
	}
	else {
		show_message($_LANG['not_fount_consignee']);
	}
}
else if ($_REQUEST['step'] == 'checkout') {
	unset($_SESSION['flow_order']['bonus_id']);
	unset($_SESSION['flow_order']['uc_id']);
	unset($_SESSION['flow_order']['vc_id']);
	$sc_rand = rand(1000, 9999);
	$sc_guid = sc_guid();
	$account_cookie = MD5($sc_guid . '-' . $sc_rand);
	setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$smarty->assign('sc_guid', $sc_guid);
	$smarty->assign('sc_rand', $sc_rand);
	bt_auth_check($stges_qishu = NULL, $is_jiesuan = true);
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$_SESSION['merchants_shipping'] = array();
	$direct_shopping = isset($_REQUEST['direct_shopping']) ? $_REQUEST['direct_shopping'] : 0;
	$cart_value = isset($_REQUEST['cart_value']) ? dsc_addslashes($_REQUEST['cart_value']) : '';
	$store_seller = isset($_REQUEST['store_seller']) ? dsc_addslashes($_REQUEST['store_seller']) : '';
	$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;

	if (empty($cart_value)) {
		$cart_value = get_cart_value($flow_type);
	}
	else if (count(explode(',', $cart_value)) == 1) {
		$cart_value = intval($cart_value);
	}

	$store_order = isset($_REQUEST['store_order']) && !empty($_REQUEST['store_order']) ? 1 : 0;

	if ($store_order) {
		$store_ids_set = array();
		$cart_value_array = explode(',', $cart_value);

		foreach ($cart_value_array as $key => $val) {
			$cart_goods_info = get_table_date('cart', 'rec_id=\'' . $val . '\'', array('goods_id'));
			$store_ids = judge_store_goods($cart_goods_info['goods_id']);

			if (!empty($store_ids)) {
				$store_ids_set = array_merge($store_ids_set, $store_ids);
			}
			else {
				unset($cart_value_array[$key]);
			}
		}

		$cart_value = implode(',', $cart_value_array);

		if (!empty($store_ids_set)) {
			$store_id = reset($store_ids_set);
			$db->autoExecute($ecs->table('cart'), array('store_id' => $store_id), 'UPDATE', 'rec_id' . db_create_in($cart_value));
		}
		else {
			show_message($_LANG['have_no_store_goods'], $_LANG['back'], '', 'error');
		}
	}

	$_SESSION['cart_value'] = $cart_value;
	$smarty->assign('cart_value', $cart_value);

	if ($flow_type == CART_GROUP_BUY_GOODS) {
		$smarty->assign('is_group_buy', 1);
	}
	else if ($flow_type == CART_EXCHANGE_GOODS) {
		$smarty->assign('is_exchange_goods', 1);
	}
	else if ($flow_type == CART_PRESALE_GOODS) {
		$smarty->assign('is_presale_goods', 1);
	}
	else {
		$_SESSION['flow_order']['extension_code'] = '';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('cart') . ' WHERE ' . $sess_id . ('AND parent_id = 0 AND is_gift = 0 AND rec_type = \'' . $flow_type . '\'');

	if ($db->getOne($sql) == 0) {
		show_message($_LANG['no_goods_in_cart'], '', '', 'warning');
	}

	if (empty($direct_shopping) && $_SESSION['user_id'] == 0) {
		ecs_header("Location: user.php\n");
		exit();
	}

	$consignee = get_consignee($_SESSION['user_id']);

	if ($consignee) {
		setcookie('province', $consignee['province'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('city', $consignee['city'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('district', $consignee['district'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('street', $consignee['street'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('street_area', '', gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$flow_warehouse = get_warehouse_goods_region($consignee['province']);
		setcookie('area_region', $flow_warehouse['region_id'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('flow_region', $flow_warehouse['region_id'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}

	$region_id = get_province_id_warehouse($consignee['province']);
	$area_info = get_area_info($consignee['province']);
	$smarty->assign('warehouse_id', $region_id);
	$smarty->assign('area_id', $area_info['region_id']);
	$user_address = get_order_user_address_list($_SESSION['user_id']);
	if ($direct_shopping != 1 && !empty($_SESSION['user_id'])) {
		$_SESSION['browse_trace'] = 'flow.php';
	}
	else {
		$_SESSION['browse_trace'] = 'flow.php?step=checkout';
	}

	if (!$user_address && $consignee) {
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['street_name'] = get_goods_region_name($consignee['street']);
		$consignee['region'] = $consignee['province_name'] . '&nbsp;' . $consignee['city_name'] . '&nbsp;' . $consignee['district_name'] . '&nbsp;' . $consignee['street_name'];
		$user_address = array($consignee);
	}

	$smarty->assign('user_address', $user_address);
	$smarty->assign('auditStatus', $_CFG['auditStatus']);
	get_goods_flow_type($cart_value);
	$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
	$smarty->assign('user_id', $user_id);
	$consignee['country'] = !isset($consignee['country']) && empty($consignee['country']) ? 0 : intval($consignee['country']);
	$consignee['province'] = !isset($consignee['province']) && empty($consignee['province']) ? 0 : intval($consignee['province']);
	$consignee['city'] = !isset($consignee['city']) && empty($consignee['city']) ? 0 : intval($consignee['city']);
	$consignee['district'] = !isset($consignee['district']) && empty($consignee['district']) ? 0 : intval($consignee['district']);
	$consignee['street'] = !isset($consignee['street']) && empty($consignee['street']) ? 0 : intval($consignee['street']);
	$_SESSION['flow_consignee'] = $consignee;
	$consignee['province_name'] = get_goods_region_name($consignee['province']);
	$consignee['city_name'] = get_goods_region_name($consignee['city']);
	$consignee['district_name'] = get_goods_region_name($consignee['district']);
	$consignee['street_name'] = get_goods_region_name($consignee['street']);
	$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
	$smarty->assign('consignee', $consignee);
	$cart_goods_list = cart_goods($flow_type, $cart_value, 1, $region_id, $area_info['region_id'], $consignee, $store_id);
	$cart_goods_list_new = cart_by_favourable($cart_goods_list);
	$smarty->assign('goods_list', $cart_goods_list_new);
	$smarty->assign('seckill_id', $_SESSION['extension_id']);
	$smarty->assign('country_list', get_regions());
	$smarty->assign('provinces', get_regions(1, 1));

	if (0 < $store_id) {
		$sql = 'SELECT c.goods_id,o.id,o.stores_name,o.stores_address,p.region_name as province,ci.region_name as city ,d.region_name as district ' . 'FROM' . $ecs->table('cart') . ' AS c ' . 'LEFT JOIN ' . $ecs->table('offline_store') . ' AS o ON c.store_id=o.id ' . 'LEFT JOIN' . $ecs->table('store_goods') . ' AS s ON s.store_id=o.id ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS p ON p.region_id = o.province ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS ci ON ci.region_id = o.city ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS d ON d.region_id = o.district WHERE c.rec_id' . db_create_in($cart_value) . (' AND c.store_id = \'' . $store_id . '\' LIMIT 1');
		$seller_store = $db->getRow($sql);
		$smarty->assign('seller_store', $seller_store);
		$sql = 'SELECT store_mobile,take_time FROM' . $ecs->table('cart') . 'WHERE rec_id ' . db_create_in($cart_value) . (' AND store_id = \'' . $store_id . '\' LIMIT 1');
		$store_info = $db->getRow($sql);

		if (!$store_info['store_mobile']) {
			$sql = 'SELECT mobile_phone FROM ' . $ecs->table('users') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\'';
			$store_info['store_mobile'] = $db->getOne($sql, true);
		}

		if (!$store_info['take_time']) {
			$store_info['take_time'] = date('Y-m-d H:i:s', strtotime('+1 day'));
		}

		$now_time = date('Y-m-d H:i:s', gmtime());
		$smarty->assign('now_time', $now_time);
		$smarty->assign('store_info', $store_info);
	}

	$smarty->assign('store_id', $store_id);
	$smarty->assign('cart_value', $cart_value);
	$smarty->assign('store_seller', $store_seller);
	$smarty->assign('is_address', $is_address);
	$cart_goods_number = get_buy_cart_goods_number($flow_type, $cart_value);
	$smarty->assign('cart_goods_number', $cart_goods_number);
	$cart_goods = get_new_group_cart_goods($cart_goods_list_new);
	if ($flow_type != CART_GENERAL_GOODS || $_CFG['one_step_buy'] == '1') {
		$smarty->assign('allow_edit_cart', 0);
	}
	else {
		$smarty->assign('allow_edit_cart', 1);
	}

	$smarty->assign('config', $_CFG);
	$order = flow_order_info();
	$smarty->assign('order', $order);
	if ((!isset($_CFG['can_invoice']) || $_CFG['can_invoice'] == '1') && isset($_CFG['invoice_content']) && trim($_CFG['invoice_content']) != '' && $flow_type != CART_EXCHANGE_GOODS) {
		$inv_content_list = explode("\n", str_replace("\r", '', $_CFG['invoice_content']));
		$smarty->assign('inv_content', $inv_content_list[0]);
		$order['need_inv'] = 1;
		$order['inv_type'] = $_CFG['invoice_type']['type'][0];
		$order['inv_payee'] = '个人';
		$order['inv_content'] = $inv_content_list[0];
	}

	if ($flow_type != CART_EXCHANGE_GOODS && $flow_type != CART_GROUP_BUY_GOODS) {
		$discount = compute_discount(3, $cart_value);
		$smarty->assign('discount', $discount['discount']);
		$favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
		$smarty->assign('your_discount', sprintf($_LANG['your_discount'], $favour_name, price_format($discount['discount'])));
	}

	if (!$user_address) {
		$consignee = array('province' => 0, 'city' => 0);
		$smarty->assign('country_list', get_regions());
		$smarty->assign('please_select', $_LANG['please_select']);
		$province_list = get_regions_log(1, 1);
		$city_list = get_regions_log(2, $consignee['province']);
		$district_list = get_regions_log(3, $consignee['city']);
		$smarty->assign('province_list', $province_list);
		$smarty->assign('city_list', $city_list);
		$smarty->assign('district_list', $district_list);
		$smarty->assign('consignee', $consignee);
	}

	$total = order_fee($order, $cart_goods, $consignee, 0, $cart_value, 0, $cart_goods_list, 0, 0, $store_id, $store_seller);
	$smarty->assign('total', $total);
	$smarty->assign('shopping_money', sprintf($_LANG['shopping_money'], $total['formated_goods_price']));
	$smarty->assign('market_price_desc', sprintf($_LANG['than_market_price'], $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));

	if ($order['shipping_id'] == 0) {
		$cod = true;
		$cod_fee = 0;
	}
	else {
		$shipping = shipping_info($order['shipping_id']);
		$cod = $shipping['support_cod'];

		if ($cod) {
			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$group_buy_id = $_SESSION['extension_id'];

				if ($group_buy_id <= 0) {
					show_message('error group_buy_id');
				}

				$group_buy = group_buy_info($group_buy_id);

				if (empty($group_buy)) {
					show_message('group buy not exists: ' . $group_buy_id);
				}

				if (0 < $group_buy['deposit']) {
					$cod = false;
					$cod_fee = 0;
					$smarty->assign('gb_deposit', $group_buy['deposit']);
				}
			}

			if ($cod) {
				$shipping_area_info = shipping_info($order['shipping_id']);
				$cod_fee = isset($shipping_area_info['pay_fee']) ? $shipping_area_info['pay_fee'] : 0;
			}
		}
		else {
			$cod_fee = 0;
		}
	}

	$payment_list = available_payment_list(1, $cod_fee);

	if (isset($payment_list)) {
		foreach ($payment_list as $key => $payment) {
			if (substr($payment['pay_code'], 0, 4) == 'pay_') {
				unset($payment_list[$key]);
				continue;
			}

			if ($payment['is_cod'] == '1') {
				$payment_list[$key]['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment['format_pay_fee'] . '</span>';
			}

			if ($payment['pay_code'] == 'yeepayszx' && 300 < $total['amount']) {
				unset($payment_list[$key]);
			}

			if ($payment['pay_code'] == 'alipay_wap') {
				unset($payment_list[$key]);
			}

			if ($payment['pay_code'] == 'balance') {
				if ($_SESSION['user_id'] == 0) {
					unset($payment_list[$key]);
				}
				else if ($_SESSION['flow_order']['pay_id'] == $payment['pay_id']) {
					$smarty->assign('disable_surplus', 1);
				}
			}
		}
	}

	foreach ($payment_list as $k => $v) {
		if ($v['is_online'] == 1) {
			unset($payment_list[$k]);
		}
	}

	if ($cart_goods && count($cart_goods) == 1 && 0 < $cart_goods[0]['stages_qishu']) {
		foreach ($payment_list as $k => $v) {
			if ($v['pay_code'] != 'onlinepay') {
				unset($payment_list[$k]);
			}
		}

		$smarty->assign('is_stages', 0);
	}
	else {
		$smarty->assign('is_stages', 1);
	}

	$smarty->assign('payment_list', $payment_list);

	if (0 < $total['real_goods_count']) {
		if (!isset($_CFG['use_package']) || $_CFG['use_package'] == '1') {
			$smarty->assign('pack_list', pack_list());
		}

		if (!isset($_CFG['use_card']) || $_CFG['use_card'] == '1') {
			$smarty->assign('card_list', card_list());
		}
	}

	$user_info = user_info($_SESSION['user_id']);
	$sql_pay = 'SELECT pay_online, ec_salt, pay_password, user_surplus FROM ' . $ecs->table('users_paypwd') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\' LIMIT 1';
	$pay_online = $db->getRow($sql_pay);
	if ($pay_online['pay_online'] || $pay_online['user_surplus'] && 0 < $user_info['user_money']) {
		$smarty->assign('open_pay_password', 1);
		$smarty->assign('pay_pwd_error', 1);
	}

	if ((!isset($_CFG['use_surplus']) || $_CFG['use_surplus'] == '1') && 0 < $_SESSION['user_id'] && 0 < $user_info['user_money']) {
		if ($db->getOne('SELECT enabled FROM ' . $ecs->table('payment') . ' WHERE pay_code = \'balance\'')) {
			$smarty->assign('allow_use_surplus', 1);
			$smarty->assign('your_surplus', $user_info['user_money']);
		}
	}

	if ((!isset($_CFG['use_integral']) || $_CFG['use_integral'] == '1') && 0 < $_SESSION['user_id'] && 0 < $user_info['pay_points'] && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS)) {
		$smarty->assign('allow_use_integral', 1);
		$smarty->assign('order_max_integral', flow_available_points($cart_value, $region_id, $area_id));
		$smarty->assign('your_integral', $user_info['pay_points']);
	}

	$cart_ru_id = '';

	if ($cart_value) {
		$cart_ru_id = get_cart_seller($cart_value);
	}

	if ((!isset($_CFG['use_bonus']) || $_CFG['use_bonus'] == '1') && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS)) {
		$user_bonus = user_bonus($_SESSION['user_id'], $total['goods_price'], $cart_value, $total['seller_amount'], $cart_ru_id);

		if (!empty($user_bonus)) {
			foreach ($user_bonus as $key => $val) {
				$user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);

				if (defined('THEME_EXTENSION')) {
					$bonus_ids[] = $val['bonus_id'];
					$user_bonus[$key]['use_end_date'] = local_date('Y-m-d', $val['use_end_date']);
				}
			}

			$smarty->assign('bonus_list', $user_bonus);
		}

		if (defined('THEME_EXTENSION')) {
			$bonus = get_user_bouns_new_list($_SESSION['user_id'], 1, 0, 'bouns_available_gotoPage', 0, 20, $cart_ru_id);

			if (!empty($bonus['available_list'])) {
				foreach ($bonus['available_list'] as $k => $v) {
					foreach ($user_bonus as $bk => $br) {
						if ($br['bonus_id'] == $v['bonus_id']) {
							unset($bonus['available_list'][$k]);
							continue;
						}
					}
				}

				$no_bonuslist = !empty($bonus['available_list']) ? $bonus['available_list'] : array();
				$smarty->assign('no_bonuslist', $no_bonuslist);
			}
		}

		$smarty->assign('allow_use_bonus', 1);
	}

	if ($_CFG['use_value_card'] == '1' && $flow_type != CART_EXCHANGE_GOODS) {
		$value_card = get_user_value_card($_SESSION['user_id'], $cart_goods, $cart_value);

		if (!empty($value_card)) {
			foreach ($value_card as $key => $val) {
				$value_card[$key]['card_money_formated'] = price_format($val['card_money'], false);
			}

			$smarty->assign('value_card_list', $value_card);
		}

		if ($value_card && isset($value_card['is_value_cart'])) {
			$value_card = array();
			$smarty->assign('is_value_cart', 0);
		}
		else {
			$smarty->assign('is_value_cart', 1);
		}

		$smarty->assign('allow_use_value_card', 1);
	}
	else {
		$smarty->assign('allow_use_value_card', 0);
	}

	if ($_CFG['use_coupons'] == 1 && $flow_type == CART_GENERAL_GOODS) {
		$user_coupons = get_user_coupons_list($_SESSION['user_id'], true, $total['goods_price'], $cart_goods, true, $cart_ru_id, '', $consignee['province']);

		if (defined('THEME_EXTENSION')) {
			$coupons_list = get_user_coupons_list($_SESSION['user_id'], true, '', false, true, $cart_ru_id, 'cart');

			if (!empty($coupons_list)) {
				foreach ($coupons_list as $k => $v) {
					$coupons_list[$k]['cou_type_name'] = $v['cou_type'];
					$coupons_list[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
					$coupons_list[$k]['cou_type'] = $v['cou_type'] == 3 ? $_LANG['lang_goods_coupons']['all_pay'] : ($v['cou_type'] == 4 ? $_LANG['lang_goods_coupons']['user_pay'] : ($v['cou_type'] == 2 ? $_LANG['lang_goods_coupons']['goods_pay'] : ($v['cou_type'] == 1 ? $_LANG['lang_goods_coupons']['reg_pay'] : $_LANG['lang_goods_coupons']['not_pay'])));

					if ($v['spec_cat']) {
						$coupons_list[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_cate'];
					}
					else if ($v['cou_goods']) {
						$coupons_list[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_goods'];
					}
					else {
						$coupons_list[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_all'];
					}

					if (!empty($user_coupons)) {
						foreach ($user_coupons as $uk => $ur) {
							if ($v['cou_id'] == $ur['cou_id']) {
								unset($coupons_list[$k]);
								continue;
							}
						}
					}
				}
			}

			$smarty->assign('coupons_list', $coupons_list);
		}

		foreach ($user_coupons as $k => $v) {
			$user_coupons[$k]['cou_type_name'] = $v['cou_type'];
			$user_coupons[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
			$user_coupons[$k]['cou_type'] = $v['cou_type'] == 3 ? $_LANG['lang_goods_coupons']['all_pay'] : ($v['cou_type'] == 4 ? $_LANG['lang_goods_coupons']['user_pay'] : ($v['cou_type'] == 2 ? $_LANG['lang_goods_coupons']['goods_pay'] : ($v['cou_type'] == 1 ? $_LANG['lang_goods_coupons']['reg_pay'] : ($v['cou_type'] == 5 ? $_LANG['lang_goods_coupons']['free_pay'] : $_LANG['lang_goods_coupons']['not_pay']))));
			$user_coupons[$k]['cou_goods_name'] = $v['cou_goods'] ? $_LANG['lang_goods_coupons']['is_goods'] : $_LANG['lang_goods_coupons']['is_all'];

			if ($v['spec_cat']) {
				$user_coupons[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_cate'];
			}
			else if ($v['cou_goods']) {
				$user_coupons[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_goods'];
			}
			else {
				$user_coupons[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_all'];
			}
		}

		$smarty->assign('user_coupons', $user_coupons);
	}

	if (!isset($_CFG['use_how_oos']) || $_CFG['use_how_oos'] == '1') {
		if (is_array($GLOBALS['_LANG']['oos']) && !empty($GLOBALS['_LANG']['oos'])) {
			$smarty->assign('how_oos_list', $GLOBALS['_LANG']['oos']);
		}
	}

	$_SESSION['flow_order'] = $order;
}
else if ($_REQUEST['step'] == 'select_shipping') {
	include_once 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'content' => '', 'need_insure' => 0);
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$consignee = get_consignee($_SESSION['user_id']);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		$result['error'] = $_LANG['no_goods_in_cart'];
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		$order['shipping_id'] = intval($_REQUEST['shipping']);
		$regions = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
		$shipping_info = shipping_info($order['shipping_id'], $regions);
		$total = order_fee($order, $cart_goods, $consignee);
		$smarty->assign('total', $total);
		$smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
		$smarty->assign('total_bonus', price_format(get_total_bonus(), false));

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		$result['cod_fee'] = $shipping_info['pay_fee'];

		if (strpos($result['cod_fee'], '%') === false) {
			$result['cod_fee'] = price_format($result['cod_fee'], false);
		}

		$ru_list = get_ru_info_list($total['ru_list']);
		$smarty->assign('warehouse_fee', $ru_list);
		$smarty->assign('freight_model', $GLOBALS['_CFG']['freight_model']);
		$result['need_insure'] = 0 < $shipping_info['insure'] && !empty($order['need_insure']) ? 1 : 0;
		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/order_total.lbi');
	}

	echo $json->encode($result);
	exit();
}
else if ($_REQUEST['step'] == 'select_insure') {
	include_once 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'content' => '', 'need_insure' => 0);
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$consignee = get_consignee($_SESSION['user_id']);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		$result['error'] = $_LANG['no_goods_in_cart'];
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		$order['need_insure'] = intval($_REQUEST['insure']);
		$_SESSION['flow_order'] = $order;
		$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
		$smarty->assign('cart_goods_number', $cart_goods_number);
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['street_name'] = get_goods_region_name($consignee['street']);
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
		$smarty->assign('goods_list', $cart_goods_list);
		$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
		$smarty->assign('total', $total);
		$smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
		$smarty->assign('total_bonus', price_format(get_total_bonus(), false));

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/order_total.lbi');
	}

	echo $json->encode($result);
	exit();
}
else if ($_REQUEST['step'] == 'pickSite') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'result' => '');
	$mark = isset($_REQUEST['mark']) ? intval($_REQUEST['mark']) : 0;

	if ($mark == 1) {
		$days = array();

		for ($i = 0; $i <= 6; $i++) {
			$days[$i]['shipping_date'] = date('Y-m-d', strtotime(' +' . $i . 'day'));
			$days[$i]['date_year'] = $days[$i]['shipping_date'];
			$days[$i]['week'] = $_LANG['unit']['week'] . transition_date($days[$i]['shipping_date']);
			$days[$i]['date'] = substr($days[$i]['shipping_date'], 5);
		}

		$shipping_date_list = $db->getAll('SELECT * FROM ' . $ecs->table('shipping_date'));
		$select = array();

		foreach ($shipping_date_list as $key => $val) {
			$m = 0;

			for ($s = 0; $s < 7; $s++) {
				if ($s < $val['select_day']) {
					$select[$m]['day'] = 0;
					$select[$m]['date'] = $days[$m]['date'];
					$select[$m]['week'] = $days[$m]['week'];
					$select[$m]['shipping_date'] = $days[$m]['shipping_date'];
				}
				else {
					$strtime = $days[$m]['date_year'] . ' ' . $val['end_date'];
					$strtime = strtotime($strtime);
					$select[$m]['day'] = 1;

					if ($strtime < gmtime() + 8 * 3600) {
						$select[$m]['day'] = 0;
					}

					$select[$m]['date'] = $days[$m]['date'];
					$select[$m]['week'] = $days[$m]['week'];
					$select[$m]['shipping_date'] = $days[$m]['shipping_date'];
				}

				$m++;
			}

			$shipping_date_list[$key]['select_day'] = $select;
			$select = array();
		}

		$smarty->assign('years', local_date('Y', gmtime()));
		$smarty->assign('days', $days);
		$smarty->assign('shipping_date_list', $shipping_date_list);
		$res['result'] = $GLOBALS['smarty']->fetch('library/picksite_date.lbi');
	}
	else {
		$district = $_SESSION['flow_consignee']['district'];
		$city = $_SESSION['flow_consignee']['city'];
		$sql = 'SELECT * FROM ' . $ecs->table('region') . (' WHERE parent_id = \'' . $city . '\'');
		$district_list = $db->getAll($sql);
		$picksite_list = get_self_point($district);
		$smarty->assign('picksite_list', $picksite_list);
		$smarty->assign('district_list', $district_list);
		$smarty->assign('district', $district);
		$smarty->assign('city', $city);
		$res['result'] = $GLOBALS['smarty']->fetch('library/picksite.lbi');
	}

	exit($json->encode($res));
}
else if ($_REQUEST['step'] == 'getPickSiteList') {
	include_once 'includes/cls_json.php';
	$district = !empty($_POST['id']) ? intval($_POST['id']) : 0;
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$json = new JSON();

	if ($district == 0) {
		$sql = 'SELECT a.region_id ,a.shipping_area_id,b.region_name,b.parent_id as city,c.name,c.user_name,c.id as point_id,c.address,c.mobile,c.img_url,c.anchor,c.line FROM ' . $GLOBALS['ecs']->table('area_region') . " AS a\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('region') . ' AS b ON a.region_id=b.region_id  LEFT JOIN ' . $GLOBALS['ecs']->table('shipping_point') . ' AS c ON c.shipping_area_id=a.shipping_area_id ' . 'WHERE c.name != \'\' AND a.region_id IN (SELECT region_id FROM ' . $ecs->table('region') . (' WHERE parent_id=\'' . $_SESSION[flow_consignee][city] . '\')');
		$self_point = $db->getAll($sql);
	}
	else {
		$self_point = get_self_point($district);
	}

	if (empty($self_point)) {
		$result['error'] = 1;
	}

	exit($json->encode($self_point));
}
else if ($_REQUEST['step'] == 'select_picksite') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('error' => 0, 'err_msg' => '', 'content' => '');
	$picksite_id = isset($_REQUEST['picksite_id']) ? intval($_REQUEST['picksite_id']) : 0;
	$district = isset($_REQUEST['district']) ? intval($_REQUEST['district']) : 0;
	$shipping_date = isset($_REQUEST['shipping_date']) ? htmlspecialchars($_REQUEST['shipping_date']) : '';
	$time_range = isset($_REQUEST['time_range']) ? htmlspecialchars($_REQUEST['time_range']) : '';
	$mark = isset($_REQUEST['mark']) ? intval($_REQUEST['mark']) : 0;

	if ($mark == 0) {
		$_SESSION['flow_consignee']['point_id'] = $picksite_id;
	}
	else {
		if ($shipping_date) {
			$week = $_LANG['unit']['week'] . transition_date($shipping_date);
		}

		$shipping_dateStr = date('m', strtotime($shipping_date)) . '月' . date('d', strtotime($shipping_date)) . '日【' . $week . '】' . $time_range;
		$_SESSION['flow_consignee']['shipping_dateStr'] = $shipping_dateStr;
	}

	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$consignee = get_consignee($_SESSION['user_id']);
	$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $region_id, $area_id);
	$cart_goods_list_new = cart_by_favourable($cart_goods_list);
	$cart_goods = get_new_group_cart_goods($cart_goods_list_new);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		if (empty($cart_goods)) {
			$result['error'] = 1;
			$result['err_msg'] = $_LANG['no_goods_in_cart'];
		}
		else if (!check_consignee_info($consignee, $flow_type)) {
			$result['error'] = 2;
			$result['err_msg'] = $_LANG['au_buy_after_login'];
		}
	}

	$smarty->assign('goods_list', $cart_goods_list_new);
	$smarty->assign('shipping_code', 'cac');
	get_goods_flow_type($_SESSION['cart_value']);
	$res['content'] = $GLOBALS['smarty']->fetch('library/flow_cart_goods.lbi');
	exit($json->encode($res));
}
else if ($_REQUEST['step'] == 'pay_pwd') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('error' => 0, 'err_msg' => '', 'content' => '');
	$pay_pwd = isset($_POST['pay_pwd']) && !empty($_POST['pay_pwd']) ? addslashes(trim($_POST['pay_pwd'])) : '';
	$sql = 'SELECT pay_online, ec_salt, pay_password FROM ' . $ecs->table('users_paypwd') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\' LIMIT 1';
	$pay = $db->getRow($sql);
	$ec_salt = $pay['ec_salt'];
	$new_password = md5(md5($pay_pwd) . $ec_salt);

	if (empty($pay_pwd)) {
		$res['error'] = 1;
	}
	else if ($new_password != $pay['pay_password']) {
		$res['error'] = 2;
	}

	exit($json->encode($res));
}
else if ($_REQUEST['step'] == 'checkorder') {
	$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
	$sql = 'SELECT pay_status, pay_id FROM ' . $ecs->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
	$order_info = $db->getRow($sql);
	$sql = 'SELECT pay_name, pay_code FROM ' . $ecs->table('payment') . ' WHERE pay_id = \'' . $order_info['pay_id'] . '\' LIMIT 1';
	$pay = $db->getRow($sql);
	if ($order_info && $order_info['pay_status'] == PS_PAYED || $order_info['pay_status'] == PS_PAYED_PART) {
		$json = array('code' => 1, 'pay_name' => $pay['pay_name'], 'pay_code' => $pay['pay_code']);
		exit(json_encode($json));
	}
	else {
		$json = array('code' => 0, 'pay_name' => $pay['pay_name'], 'pay_code' => $pay['pay_code']);
		exit(json_encode($json));
	}
}
else if ($_REQUEST['step'] == 'select_payment') {
	include_once 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => 0, 'massage' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
	$store_seller = isset($_REQUEST['store_seller']) ? dsc_addslashes($_REQUEST['store_seller']) : '';
	$store_seller = 0 < $store_id ? 'store_seller' : $store_seller;
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$smarty->assign('store_id', $store_id);
	$smarty->assign('seller_store', $store_seller);
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);

	if (0 < $store_id) {
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $region_id, $area_id, '', $store_id);
		$cart_goods_list_new = cart_by_favourable($cart_goods_list);
		$cart_goods = get_new_group_cart_goods($cart_goods_list_new);

		if (empty($cart_goods)) {
			if (empty($cart_goods)) {
				$result['error'] = 1;
			}
		}
		else {
			$smarty->assign('config', $_CFG);
			$order = flow_order_info();
			$order['pay_id'] = intval($_REQUEST['payment']);
			$payment_info = payment_info($order['pay_id']);
			$result['pay_code'] = $payment_info['pay_code'];
			$_SESSION['flow_order'] = $order;
			$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
			$smarty->assign('cart_goods_number', $cart_goods_number);
			$smarty->assign('goods_list', $cart_goods_list_new);
			$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
			$total = order_fee($order, $cart_goods, '', 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id, $store_seller);
			$smarty->assign('total', $total);
			$smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
			$smarty->assign('total_bonus', price_format(get_total_bonus(), false));
			get_goods_flow_type($_SESSION['cart_value']);
			$sc_rand = rand(1000, 9999);
			$sc_guid = sc_guid();
			$account_cookie = MD5($sc_guid . '-' . $sc_rand);
			setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
			$smarty->assign('sc_guid', $sc_guid);
			$smarty->assign('sc_rand', $sc_rand);
			$result['content'] = $smarty->fetch('library/order_total.lbi');
		}
	}
	else {
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $region_id, $area_id);
		$cart_goods_list_new = cart_by_favourable($cart_goods_list);
		$cart_goods = get_new_group_cart_goods($cart_goods_list_new);
		if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
			if (empty($cart_goods)) {
				$result['error'] = 1;
			}
			else if (!check_consignee_info($consignee, $flow_type)) {
				$result['error'] = 2;
			}
		}
		else {
			$smarty->assign('config', $_CFG);
			$order = flow_order_info();
			$order['surplus'] = 0;
			$order['pay_id'] = intval($_REQUEST['payment']);
			$payment_info = payment_info($order['pay_id']);
			$result['pay_code'] = $payment_info['pay_code'];
			$_SESSION['flow_order'] = $order;
			$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
			$smarty->assign('cart_goods_number', $cart_goods_number);
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['street_name'] = get_goods_region_name($consignee['street']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
			$smarty->assign('consignee', $consignee);
			$smarty->assign('goods_list', $cart_goods_list_new);
			$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id, $store_seller);
			$smarty->assign('total', $total);
			$smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
			$smarty->assign('total_bonus', price_format(get_total_bonus(), false));

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$smarty->assign('is_group_buy', 1);
			}
			else if ($flow_type == CART_EXCHANGE_GOODS) {
				$smarty->assign('is_exchange_goods', 1);
			}

			get_goods_flow_type($_SESSION['cart_value']);
			$sc_rand = rand(1000, 9999);
			$sc_guid = sc_guid();
			$account_cookie = MD5($sc_guid . '-' . $sc_rand);
			setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
			$smarty->assign('sc_guid', $sc_guid);
			$smarty->assign('sc_rand', $sc_rand);
			$result['content'] = $smarty->fetch('library/order_total.lbi');
		}
	}

	echo $json->encode($result);
	exit();
}
else if ($_REQUEST['step'] == 'select_pack') {
	include_once 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'content' => '', 'need_insure' => 0);
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$consignee = get_consignee($_SESSION['user_id']);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		$result['error'] = $_LANG['no_goods_in_cart'];
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		$order['pack_id'] = intval($_REQUEST['pack']);
		$_SESSION['flow_order'] = $order;
		$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
		$smarty->assign('cart_goods_number', $cart_goods_number);
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['street_name'] = get_goods_region_name($consignee['street']);
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
		$smarty->assign('goods_list', $cart_goods_list);
		$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
		$smarty->assign('total', $total);
		$smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
		$smarty->assign('total_bonus', price_format(get_total_bonus(), false));

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/order_total.lbi');
	}

	echo $json->encode($result);
	exit();
}
else if ($_REQUEST['step'] == 'select_card') {
	include_once 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'content' => '', 'need_insure' => 0);
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$consignee = get_consignee($_SESSION['user_id']);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		$result['error'] = $_LANG['no_goods_in_cart'];
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		$order['card_id'] = intval($_REQUEST['card']);
		$_SESSION['flow_order'] = $order;
		$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
		$smarty->assign('cart_goods_number', $cart_goods_number);
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['street_name'] = get_goods_region_name($consignee['street']);
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
		$smarty->assign('goods_list', $cart_goods_list);
		$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
		$smarty->assign('total', $total);
		$smarty->assign('total_integral', cart_amount(false, $flow_type) - $order['bonus'] - $total['integral_money']);
		$smarty->assign('total_bonus', price_format(get_total_bonus(), false));

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/order_total.lbi');
	}

	echo $json->encode($result);
	exit();
}
else if ($_REQUEST['step'] == 'change_surplus') {
	include_once 'includes/cls_json.php';
	$json = new JSON();
	$surplus = floatval($_GET['surplus']);
	$user_info = user_info($_SESSION['user_id']);
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$smarty->assign('store_id', $store_id);

	if ($user_info['user_money'] + $user_info['credit_line'] < $surplus) {
		$result['error'] = $_LANG['surplus_not_enough'];
	}
	else {
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$smarty->assign('config', $_CFG);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type, $_SESSION['cart_value'], 0, 0, 0, '', $store_id);

		if (empty($cart_goods)) {
			$result['error'] = $_LANG['no_goods_in_cart'];
		}
		else if (!check_consignee_info($consignee, $flow_type)) {
			$result['error'] = $_LANG['no_consignee'];
		}
		else {
			$order = flow_order_info();
			$order['surplus'] = $surplus;
			$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
			$smarty->assign('cart_goods_number', $cart_goods_number);
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['street_name'] = get_goods_region_name($consignee['street']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
			$smarty->assign('consignee', $consignee);
			$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, 0, 0, '', $store_id);
			$smarty->assign('goods_list', $cart_goods_list);
			$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
			$smarty->assign('total', $total);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$smarty->assign('is_group_buy', 1);
			}
			else if ($flow_type == CART_EXCHANGE_GOODS) {
				$smarty->assign('is_exchange_goods', 1);
			}

			$sc_rand = rand(1000, 9999);
			$sc_guid = sc_guid();
			$account_cookie = MD5($sc_guid . '-' . $sc_rand);
			setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
			$smarty->assign('sc_guid', $sc_guid);
			$smarty->assign('sc_rand', $sc_rand);
			$result['content'] = $smarty->fetch('library/order_total.lbi');
		}
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'change_integral') {
	include_once 'includes/cls_json.php';
	$json = new JSON();
	$points = floatval($_GET['points']);
	$user_info = user_info($_SESSION['user_id']);
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$order = flow_order_info();
	$flow_points = flow_available_points($_SESSION['cart_value'], $region_id, $area_id);
	$user_points = $user_info['pay_points'];
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$smarty->assign('store_id', $store_id);

	if ($user_points < $points) {
		$result['error'] = $_LANG['integral_not_enough'];
	}
	else if ($flow_points < $points) {
		$result['error'] = sprintf($_LANG['integral_too_much'], $flow_points);
	}
	else {
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$order['integral'] = $points;
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type, $_SESSION['cart_value'], 0, 0, 0, '', $store_id);

		if (empty($cart_goods)) {
			$result['error'] = $_LANG['no_goods_in_cart'];
		}
		else if (!check_consignee_info($consignee, $flow_type)) {
			$result['error'] = $_LANG['no_consignee'];
		}
		else {
			$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
			$smarty->assign('cart_goods_number', $cart_goods_number);
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['street_name'] = get_goods_region_name($consignee['street']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
			$smarty->assign('consignee', $consignee);
			$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, 0, 0, '', $order_id);
			$smarty->assign('goods_list', $cart_goods_list);
			$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
			$smarty->assign('total', $total);
			$smarty->assign('config', $_CFG);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$smarty->assign('is_group_buy', 1);
			}
			else if ($flow_type == CART_EXCHANGE_GOODS) {
				$smarty->assign('is_exchange_goods', 1);
			}

			$sc_rand = rand(1000, 9999);
			$sc_guid = sc_guid();
			$account_cookie = MD5($sc_guid . '-' . $sc_rand);
			setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
			$smarty->assign('sc_guid', $sc_guid);
			$smarty->assign('sc_rand', $sc_rand);
			$result['content'] = $smarty->fetch('library/order_total.lbi');
			$result['error'] = '';
		}
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'change_bonus') {
	include_once 'includes/cls_json.php';
	$result = array('error' => '', 'content' => '');
	$json = new JSON();
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$consignee = get_consignee($_SESSION['user_id']);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		if (empty($cart_goods)) {
			$result['error'] = 1;
		}
		else {
			$result['error'] = 2;
		}
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		$bonus = bonus_info(intval($_GET['bonus']));
		if (!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id'] || $_GET['bonus'] == 0) {
			$order['bonus_id'] = intval($_GET['bonus']);
		}
		else {
			$order['bonus_id'] = 0;
			$result['error'] = $_LANG['invalid_bonus'];
		}

		$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
		$smarty->assign('cart_goods_number', $cart_goods_number);
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['street_name'] = get_goods_region_name($consignee['street']);
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
		$smarty->assign('goods_list', $cart_goods_list);
		$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
		$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
		$smarty->assign('total', $total);

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/order_total.lbi');
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'change_value_card') {
	include_once 'includes/cls_json.php';
	$result = array('error' => '', 'content' => '');
	$json = new JSON();
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$smarty->assign('store_id', $store_id);
	$consignee = get_consignee($_SESSION['user_id']);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value'], 0, 0, 0, '', $store_id);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		if (empty($cart_goods)) {
			$result['error'] = 1;
		}
		else {
			$result['error'] = 2;
		}
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		$vc_id = intval($_GET['value_card']);
		$value_card = value_card_info($vc_id);
		if (!empty($value_card) && $value_card['user_id'] == $_SESSION['user_id'] || $_GET['bonus'] == 0) {
			$order['vc_id'] = intval($vc_id);
		}
		else {
			$order['vc_id'] = 0;
			$result['error'] = $_LANG['invalid_value_card'];
		}

		$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
		$smarty->assign('cart_goods_number', $cart_goods_number);
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['street_name'] = get_goods_region_name($consignee['street']);
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, 0, 0, '', $store_id);
		$smarty->assign('goods_list', $cart_goods_list);
		$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
		$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
		$smarty->assign('total', $total);

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/order_total.lbi');
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'change_coupons') {
	include_once 'includes/cls_json.php';
	$result = array('error' => 0, 'content' => '');
	$json = new JSON();
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$uc_id = isset($_GET['uc_id']) && !empty($_GET['uc_id']) ? intval($_GET['uc_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$consignee = get_consignee($_SESSION['user_id']);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		if (empty($cart_goods)) {
			$result['error'] = 1;
		}
		else {
			$result['error'] = 2;
		}
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		$_SESSION['flow_order'] = NULL;
		$coupons_info = get_coupons($uc_id, array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
		$coupons_info['cou_money'] = !empty($coupons_info['uc_money']) ? $coupons_info['uc_money'] : $coupons_info['cou_money'];
		if (!empty($coupons_info) && $coupons_info['user_id'] == $user_id || $uc_id == 0) {
			$order['uc_id'] = $uc_id;
		}
		else {
			$order['uc_id'] = 0;
			$result['error'] = $_LANG['Coupon_null'];
		}

		$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
		$smarty->assign('cart_goods_number', $cart_goods_number);
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['street_name'] = get_goods_region_name($consignee['street']);
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
		$smarty->assign('goods_list', $cart_goods_list);
		$not_freightfree = 0;
		if (!empty($coupons_info) && $cart_goods_list) {
			if ($coupons_info['cou_type'] == 5) {
				$cou_goods = array();
				$goods_amount = 0;

				foreach ($cart_goods_list as $key => $row) {
					if ($row['ru_id'] == $coupons_info['ru_id']) {
						$cou_goods = $row['goods_list'];

						foreach ($row['goods_list'] as $gkey => $grow) {
							$goods_amount += $grow['goods_price'] * $grow['goods_number'];
						}
					}
				}

				if ($coupons_info['cou_man'] <= $goods_amount || $coupons_info['cou_man'] == 0) {
					$cou_region = get_coupons_region($coupons_info['cou_id']);
					$cou_region = !empty($cou_region) ? explode(',', $cou_region) : array();
					if ($cou_region && in_array($consignee['province'], $cou_region)) {
						$not_freightfree = 1;
					}
				}
			}
		}

		$result['not_freightfree'] = $not_freightfree;
		$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
		$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
		$smarty->assign('total', $total);

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/order_total.lbi');
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'change_needinv') {
	include_once 'includes/cls_json.php';
	$result = array('error' => '', 'content' => '');
	$json = new JSON();
	$_GET['inv_type'] = !empty($_GET['inv_type']) ? json_str_iconv(urldecode($_GET['inv_type'])) : '';
	$_GET['invPayee'] = !empty($_GET['invPayee']) ? json_str_iconv(urldecode($_GET['invPayee'])) : '';
	$_GET['inv_content'] = !empty($_GET['inv_content']) ? json_str_iconv(urldecode($_GET['inv_content'])) : '';
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$consignee = get_consignee($_SESSION['user_id']);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		$result['error'] = $_LANG['no_goods_in_cart'];
		exit($json->encode($result));
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		if (isset($_GET['need_inv']) && intval($_GET['need_inv']) == 1) {
			$order['need_inv'] = 1;
			$order['inv_type'] = trim(stripslashes($_GET['inv_type']));
			$order['inv_payee'] = trim(stripslashes($_GET['inv_payee']));
			$order['inv_content'] = trim(stripslashes($_GET['inv_content']));
			$order['tax_id'] = trim(stripslashes($_GET['tax_id']));
		}
		else {
			$order['need_inv'] = 0;
			$order['inv_type'] = '';
			$order['inv_payee'] = '';
			$order['inv_content'] = '';
			$order['tax_id'] = '';
		}

		$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
		$smarty->assign('cart_goods_number', $cart_goods_number);
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['street_name'] = get_goods_region_name($consignee['street']);
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
		$smarty->assign('goods_list', $cart_goods_list);
		$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
		$smarty->assign('total', $total);

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		exit($smarty->fetch('library/order_total.lbi'));
	}
}
else if ($_REQUEST['step'] == 'change_oos') {
	$order = flow_order_info();
	$order['how_oos'] = intval($_GET['oos']);
	$_SESSION['flow_order'] = $order;
}
else if ($_REQUEST['step'] == 'check_surplus') {
	$surplus = floatval($_GET['surplus']);
	$user_info = user_info($_SESSION['user_id']);

	if ($user_info['user_money'] + $user_info['credit_line'] < $surplus) {
		exit($_LANG['surplus_not_enough']);
	}

	exit();
}
else if ($_REQUEST['step'] == 'check_integral') {
	$cart_value = $_SESSION['cart_value'];
	$points = floatval($_GET['integral']);
	$user_info = user_info($_SESSION['user_id']);
	$_SESSION['cart_value'] = $cart_value;
	$flow_points = flow_available_points($cart_value, $region_id, $area_id);
	$user_points = $user_info['pay_points'];

	if ($user_points < $points) {
		exit($_LANG['integral_not_enough']);
	}

	if ($flow_points < $points) {
		exit(sprintf($_LANG['integral_too_much'], $flow_points));
	}

	exit();
}
else if ($_REQUEST['step'] == 'done') {
	include_once 'includes/lib_clips.php';
	include_once 'includes/lib_payment.php';
	$store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$_REQUEST['act'] = isset($_REQUEST['act']) && !empty($_REQUEST['act']) ? dsc_addslashes(trim($_REQUEST['act'])) : '';

	if ($_REQUEST['act'] == 'balance') {
		$balance_info = $db->getRow('SELECT pay_name,pay_id, pay_code FROM ' . $ecs->table('payment') . ' WHERE pay_code=\'balance\'');
		$order_sn = addslashes_deep($_REQUEST['order_sn']);
		$sql = 'SELECT * FROM ' . $ecs->table('order_info') . (' WHERE order_sn = \'' . $order_sn . '\' AND user_id = \'' . $user_id . '\' LIMIT 1');
		$order_info = $db->getRow($sql);
		$order_amount = floatval($order_info['order_amount']);
		$user_money = $db->getOne('SELECT user_money FROM ' . $ecs->table('users') . (' WHERE user_id=\'' . $user_id . '\''));
		if ($order_info && $order_amount <= $user_money) {
			if (0 < $order_info['order_id']) {
				$child_info = $db->getRow('SELECT GROUP_CONCAT(order_id) AS order_id, GROUP_CONCAT(order_sn) AS order_sn FROM ' . $ecs->table('order_info') . ' WHERE main_order_id=\'' . $order_info['order_id'] . '\'');
				$child_ids = isset($child_info['order_id']) && !empty($child_info['order_id']) ? $child_info['order_id'] : '';
				$child_sn = isset($child_info['order_sn']) && !empty($child_info['order_sn']) ? explode(',', $child_info['order_sn']) : '';
			}
			else {
				$child_ids = array();
				$child_sn = array();
			}

			if (!empty($child_ids)) {
				$order_ids = $order_info['order_id'] . ',' . $child_ids;
			}
			else {
				$order_ids = $order_info['order_id'];
			}

			if (0 < $order_info['user_id']) {
				log_account_change($order_info['user_id'], $order_amount * -1, 0, 0, 0, sprintf($_LANG['pay_order'], $order_info['order_sn']));
				$order['order_status'] = OS_CONFIRMED;
				$order['confirm_time'] = gmtime();

				if ($order_info['extension_code'] == 'presale') {
					$order['pay_status'] = PS_PAYED_PART;
					$order['surplus'] = $order_info['order_amount'];
					$order['order_amount'] = $order_info['goods_amount'] + $order_info['shipping_fee'] + $order_info['insure_fee'] + $order_info['tax'] - $order_info['discount'] - $order['surplus'];
				}
				else {
					$order['pay_status'] = PS_PAYED;
					$order['surplus'] = $order_amount + $order_info['surplus'];
					$order['order_amount'] = 0;
				}

				if ($_CFG['sales_volume_time'] == SALES_PAY) {
					$order['is_update_sale'] = 1;
				}

				$order['pay_time'] = gmtime();
				$order['pay_name'] = $balance_info['pay_name'];
				$order['pay_id'] = $balance_info['pay_id'];
				$order['pay_code'] = $balance_info['pay_code'];
				order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $GLOBALS['_LANG']['flow_surplus_pay'], $GLOBALS['_LANG']['buyer']);

				if ($child_sn) {
					foreach ($child_sn as $key => $row) {
						order_action($row, OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $GLOBALS['_LANG']['flow_surplus_pay'], $GLOBALS['_LANG']['buyer']);
					}
				}

				$order['money_paid'] = 0;

				if (!empty($child_ids)) {
					$child_order_amounts = $db->getAll('SELECT order_id,order_amount FROM ' . $ecs->table('order_info') . (' WHERE order_id IN(' . $order_ids . ')'));

					foreach ($child_order_amounts as $k => $v) {
						$order['surplus'] = $v['order_amount'] + $v['surplus'];
						$re = $db->autoExecute($ecs->table('order_info'), $order, 'UPDATE', 'order_id = \'' . $v['order_id'] . '\'');
					}
				}
				else {
					$re = $db->autoExecute($ecs->table('order_info'), $order, 'UPDATE', 'order_id in(' . $order_ids . ')');
				}

				$db->query('UPDATE ' . $ecs->table('pay_log') . (' SET is_paid=1 WHERE order_id IN(' . $order_ids . ')'));
				$order_arr = explode(',', $order_ids);

				foreach ($order_arr as $order_one) {
					if ($order['order_amount'] <= 0) {
						$orderInfo = $GLOBALS['db']->getRow(' SELECT * FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_one . '\' '));
						$sql = 'SELECT goods_id, goods_name, goods_number AS num FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE is_real = 0 AND extension_code = \'virtual_card\'' . (' AND order_id = \'' . $orderInfo['order_id'] . '\'');
						$res = $GLOBALS['db']->getAll($sql);
						$virtual_goods = array();

						foreach ($res as $row) {
							$virtual_goods['virtual_card'][] = array('goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']);
						}

						if ($virtual_goods && $flow_type != CART_GROUP_BUY_GOODS) {
							if (virtual_goods_ship($virtual_goods, $msg, $orderInfo['order_sn'], true)) {
								$sql = 'SELECT COUNT(*)' . ' FROM ' . $ecs->table('order_goods') . (' WHERE order_id = \'' . $orderInfo['order_id'] . '\' ') . ' AND is_real = 1';

								if ($db->getOne($sql) <= 0) {
									update_order($orderInfo['order_id'], array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));

									if (0 < $orderInfo['user_id']) {
										$user = user_info($orderInfo['user_id']);
										$integral = integral_to_give($orderInfo);
										$gave_custom_points = integral_of_value($integral['custom_points']) - $orderInfo['integral'];

										if ($gave_custom_points < 0) {
											$gave_custom_points = 0;
										}

										log_account_change($orderInfo['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $orderInfo['order_sn']));
										send_order_bonus($orderInfo['order_id']);
										send_order_coupons($orderInfo['order_id']);
									}
								}
							}
						}
					}
				}

				update_zc_project($order_info['order_id']);
				create_snapshot($order_info['order_id']);

				if ($order_info['extension_code'] == 'presale') {
					get_presale_num($order_info['order_id']);
				}

				get_goods_sale($order_info['order_id']);
				$sql = 'SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id = \'' . $order_info['order_id'] . '\'';
				$ru_id = $GLOBALS['db']->getOne($sql, true);
				$shop_name = get_shop_name($ru_id, 1);

				if ($ru_id == 0) {
					$sms_shop_mobile = $GLOBALS['_CFG']['sms_shop_mobile'];
				}
				else {
					$sql = 'SELECT mobile FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' WHERE ru_id = \'' . $ru_id . '\'');
					$sms_shop_mobile = $GLOBALS['db']->getOne($sql, true);
				}

				$order_result = array();
				if ($GLOBALS['_CFG']['sms_order_payed'] == '1' && $sms_shop_mobile) {
					$order_region = get_flow_user_region($order_info['order_id']);
					$smsParams = array('shop_name' => $shop_name, 'shopname' => $shop_name, 'order_sn' => $order_info['order_sn'], 'ordersn' => $order_info['order_sn'], 'consignee' => $order_info['consignee'], 'order_region' => $order_region, 'orderregion' => $order_region, 'address' => $order_info['address'], 'order_mobile' => $order_info['mobile'], 'ordermobile' => $order_info['mobile'], 'mobile_phone' => $sms_shop_mobile, 'mobilephone' => $sms_shop_mobile);

					if ($GLOBALS['_CFG']['sms_type'] == 0) {
						huyi_sms($smsParams, 'sms_order_payed');
					}
					else if (1 <= $GLOBALS['_CFG']['sms_type']) {
						$order_result = sms_ali($smsParams, 'sms_order_payed');
					}
				}

				$sql = 'SELECT id, store_id, order_id FROM ' . $GLOBALS['ecs']->table('store_order') . ' WHERE order_id = \'' . $order_info['order_id'] . '\' LIMIT 1';
				$stores_order = $GLOBALS['db']->getRow($sql);
				$store_result = array();
				if ($stores_order && 0 < $stores_order['store_id']) {
					if ($order_info['mobile']) {
						$user_mobile_phone = $order_info['mobile'];
					}
					else {
						$sql = 'SELECT mobile_phone FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\'';
						$user_mobile_phone = $GLOBALS['db']->getOne($sql, true);
					}

					if ($user_mobile_phone) {
						$pick_code = substr($order['order_sn'], -3) . rand(0, 9) . rand(0, 9) . rand(0, 9);
						$sql = 'UPDATE ' . $GLOBALS['ecs']->table('store_order') . (' SET pick_code = \'' . $pick_code . '\' WHERE id = \'') . $stores_order['id'] . '\'';
						$db->query($sql);
						$sql = 'SELECT id, country, province, city, district, stores_address, stores_name, stores_tel FROM ' . $GLOBALS['ecs']->table('offline_store') . ' WHERE id = \'' . $stores_order['store_id'] . '\' LIMIT 1';
						$stores_info = $GLOBALS['db']->getRow($sql);
						$store_address = get_area_region_info($stores_info) . $stores_info['stores_address'];
						$user_name = isset($_SESSION['user_name']) && !empty($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
						$store_smsParams = array('user_name' => $user_name, 'username' => $user_name, 'order_sn' => $order_info['order_sn'], 'ordersn' => $order_info['order_sn'], 'code' => $pick_code, 'store_address' => $store_address, 'storeaddress' => $store_address, 'mobile_phone' => $user_mobile_phone, 'mobilephone' => $user_mobile_phone);

						if ($GLOBALS['_CFG']['sms_type'] == 0) {
							if (0 < $stores_order['store_id'] && !empty($store_smsParams)) {
								huyi_sms($store_smsParams, 'store_order_code');
							}
						}
						else if (1 <= $GLOBALS['_CFG']['sms_type']) {
							if (0 < $stores_order['store_id'] && !empty($store_smsParams)) {
								$store_result = sms_ali($store_smsParams, 'store_order_code');
							}
						}
					}
				}

				if (1 <= $GLOBALS['_CFG']['sms_type']) {
					$sms_send = array($store_result, $order_result);
					$resp = $GLOBALS['ecs']->ali_yu($sms_send, 1);
				}

				header('location:flow.php?step=pay_success&order_id=' . $order_info['order_id'] . ('&store_id=' . $store_id));
				exit();
			}
		}
		else {
			show_message($GLOBALS['_LANG']['balance_not_enough'], $GLOBALS['_LANG']['go_pay'], '');
			exit();
		}
	}
	else if ($_REQUEST['act'] == 'chunsejinrong') {
		$_REQUEST['order_sn'] = trim($_REQUEST['order_sn']);
		$order_sn = dsc_addslashes($_REQUEST['order_sn']);
		$bt_payment_info = $db->getRow('SELECT pay_name,pay_id,pay_code FROM ' . $ecs->table('payment') . ' WHERE pay_code=\'chunsejinrong\' LIMIT 1');
		$sql = 'SELECT * FROM ' . $ecs->table('order_info') . (' WHERE order_sn = \'' . $order_sn . '\' AND user_id = \'' . $user_id . '\' LIMIT 1');
		$order_info = $db->getRow($sql);
		$bt_other = array('user_id' => $user_id);
		$bt_info = get_baitiao_info($bt_other);
		$sql = 'SELECT SUM(b.stages_one_price * (b.stages_total - b.yes_num)) AS total_amount FROM ' . $ecs->table('baitiao_log') . ' AS b ' . (' WHERE b.user_id = \'' . $user_id . '\' AND b.is_repay = 0 AND b.is_refund = 0');
		$repay_bt = $db->getOne($sql, true);

		if (!$bt_info) {
			show_message($_LANG['Ious_error_one'], $_LANG['back_up_page'], '');
		}

		$remain_amount = floatval($bt_info['amount']) - floatval($repay_bt);

		if ($remain_amount < $order_info['order_amount']) {
			show_message($_LANG['Ious_error_two'], $_LANG['back_up_page'], '');
		}
		else {
			if ($order_info && 0 < $order_info['order_id']) {
				$user_baitiao_info = $db->getAll('SELECT * FROM ' . $ecs->table('baitiao_log') . (' WHERE is_repay = 0 AND user_id = \'' . $user_id . '\''));

				foreach ($user_baitiao_info as $k => $v) {
					if ($user_baitiao_info[$k]['is_stages'] == 1) {
						$repay_date = unserialize($user_baitiao_info[$k]['repay_date']);
						$over_date[] = strtotime($repay_date[$user_baitiao_info[$k]['yes_num'] + 1]);
					}
					else {
						$over_date[] = $user_baitiao_info[$k]['repay_date'];
					}

					if ($over_date[$k] <= gmtime()) {
						show_message($_LANG['Ious_error_Three'], $_LANG['back_up_page'], '');
					}
				}

				$order['order_status'] = OS_CONFIRMED;
				$order['pay_time'] = gmtime();
				$order['pay_name'] = $bt_payment_info['pay_name'];
				$order['pay_code'] = $bt_payment_info['pay_code'];
				$order['pay_id'] = $bt_payment_info['pay_id'];
				$order['money_paid'] = floatval($order_info['order_amount']);
				$order['confirm_time'] = gmtime();
				$order['pay_status'] = PS_PAYED;
				$order['order_amount'] = 0;
				$db->autoExecute($ecs->table('order_info'), $order, 'UPDATE', 'order_id = \'' . $order_info['order_id'] . '\'');
				$note = $_LANG['user_baitiao_pay'];
				order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $note, $GLOBALS['_LANG']['buyer']);
				$db->query('UPDATE ' . $ecs->table('pay_log') . ' SET is_paid = 1 WHERE order_id = \'' . $order_info['order_id'] . '\'');
				$child_ids = $db->getOne('SELECT GROUP_CONCAT(order_id) order_id FROM ' . $ecs->table('order_info') . ' WHERE main_order_id = \'' . $order_info['order_id'] . '\'');

				if (!empty($child_ids)) {
					$child_order_amounts = $db->getAll('SELECT order_id, order_sn, order_amount FROM ' . $ecs->table('order_info') . (' WHERE order_id IN(' . $child_ids . ')'));

					foreach ($child_order_amounts as $k => $v) {
						$child_order_other['order_status'] = OS_CONFIRMED;
						$child_order_other['pay_time'] = gmtime();
						$child_order_other['pay_name'] = $bt_payment_info['pay_name'];
						$child_order_other['pay_code'] = $bt_payment_info['pay_code'];
						$child_order_other['pay_id'] = $bt_payment_info['pay_id'];
						$child_order_other['money_paid'] = floatval($v['order_amount']);
						$child_order_other['confirm_time'] = gmtime();
						$child_order_other['pay_status'] = PS_PAYED;
						$child_order_other['order_amount'] = 0;
						$db->autoExecute($ecs->table('order_info'), $order, 'UPDATE', 'order_id = \'' . $v['order_id'] . '\'');
						cloud_confirmorder($v['order_id']);
						order_action($v['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $note, $GLOBALS['_LANG']['buyer']);
						$db->query('UPDATE ' . $ecs->table('pay_log') . ' SET is_paid = 1 WHERE order_id = \'' . $v['order_id'] . '\'');
					}
				}

				$stages_total = 0;
				$stages_one_price = 0;
				$stages_other = array('order_sn' => $order_sn);
				$stages_info = get_stages_info($stages_other);

				if ($stages_info) {
					$is_stages = 1;
					$stages_total = $stages_info['stages_total'];
					$stages_one_price = $stages_info['stages_one_price'];
					$repay_date = $stages_info['repay_date'];
				}
				else {
					$repcheckorderay_date = gmtime() + $bt_info['repay_term'] * 24 * 3600;
				}

				$bt_insert_other = array('baitiao_id' => $bt_info['baitiao_id'], 'user_id' => $user_id, 'use_date' => gmtime(), 'repay_date' => $repay_date, 'order_id' => $order_info['order_id'], 'is_repay' => 0, 'is_stages' => $is_stages, 'stages_total' => $stages_total, 'stages_one_price' => $stages_one_price, 'yes_num' => 0);
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('baitiao_log'), $bt_insert_other, 'INSERT');
				$bt_log_id = $GLOBALS['db']->insert_id();
				if (0 < $stages_total && $is_stages == 1) {
					for ($i = 1; $i <= $stages_total; $i++) {
						$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table('baitiao_pay_log') . (' WHERE log_id = \'' . $bt_log_id . '\' AND baitiao_id = \'') . $bt_info['baitiao_id'] . '\' AND stages_num = \'' . $i . '\'';

						if (!$GLOBALS['db']->getOne($sql, true)) {
							$pay_log_other = array('log_id' => $bt_log_id, 'baitiao_id' => $bt_info['baitiao_id'], 'stages_num' => $i, 'stages_price' => $stages_one_price, 'add_time' => gmtime());
							$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('baitiao_pay_log'), $pay_log_other, 'INSERT');
						}
					}

					$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('baitiao_pay_log') . (' WHERE log_id = \'' . $bt_log_id . '\'');
					$bt_pay_count = $db->getOne($sql);

					if ($stages_total == $bt_pay_count) {
						$baitiao_log_other['pay_num'] = 1;
						$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('baitiao_log'), $baitiao_log_other, 'UPDATE', 'log_id = \'' . $bt_log_id . '\'');
					}
				}

				create_snapshot($order_info['order_id']);
				if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PAID && $order['order_amount'] <= 0) {
					change_order_goods_storage($order_info['order_id'], true, SDT_PAID, $_CFG['stock_dec_time'], 0, $store_id);
				}

				if ($order_info['extension_code'] == 'presale') {
					get_presale_num($order_info['order_id']);
				}
			}

			header('location:flow.php?step=pay_success&order_id=' . $order_info['order_id'] . ('&store_id=' . $store_id));
		}
	}
	else {
		$order_time = gmtime();
		$where_flow = '';
		$pay_type = isset($_POST['pay_type']) ? intval($_POST['pay_type']) : 0;
		$done_cart_value = isset($_POST['done_cart_value']) ? dsc_addslashes($_POST['done_cart_value'], 0) : 0;

		if (empty($done_cart_value)) {
			header('Location:' . $GLOBALS['ecs']->url() . "\n");
			exit();
		}

		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$sql = ' SELECT COUNT(*) FROM ' . $ecs->table('cart') . ' WHERE ' . $sess_id . ' AND rec_id ' . db_create_in($done_cart_value);

		if (!$db->getOne($sql)) {
			$sse_order_id = isset($_SESSION['order_reload'][$user_id]['order_id']) && !empty($_SESSION['order_reload'][$user_id]['order_id']) ? intval($_SESSION['order_reload'][$user_id]['order_id']) : 0;
			$where_reload = '';

			if ($sse_order_id == 0) {
				$order_goods = get_order_cart_value($user_id, $done_cart_value);
				$where_reload = '&order_id=' . $order_goods['order_id'];
				$_SESSION['order_reload'][$user_id]['order_id'] = $order_goods['order_id'];
			}

			ecs_header('Location:flow.php?step=order_reload' . $where_reload . "\n");
			exit();
		}

		if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE) {
			$cart_goods_stock = get_cart_goods($done_cart_value);
			$_cart_goods_stock = array();

			foreach ($cart_goods_stock['goods_list'] as $value) {
				$_cart_goods_stock[$value['rec_id']] = $value['goods_number'];
			}

			flow_cart_stock($_cart_goods_stock, $store_id, $warehouse_id, $area_id);
			unset($cart_goods_stock);
			unset($_cart_goods_stock);
		}

		if (empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0) {
			ecs_header("Location: user.php\n");
			exit();
		}

		$consignee = get_consignee($_SESSION['user_id']);
		if (!check_consignee_info($consignee, $flow_type) && $store_id == 0) {
			ecs_header("Location: user.php?act=address_list\n");
			exit();
		}

		$_POST['how_oos'] = isset($_POST['how_oos']) ? intval($_POST['how_oos']) : 0;
		$_POST['card_message'] = isset($_POST['card_message']) ? compile_str($_POST['card_message']) : '';
		$_POST['inv_type'] = !empty($_POST['inv_type']) ? compile_str($_POST['inv_type']) : '';
		$_POST['inv_payee'] = isset($_POST['inv_payee']) ? compile_str($_POST['inv_payee']) : '';
		$_POST['tax_id'] = isset($_POST['tax_id']) ? compile_str($_POST['tax_id']) : '';
		$_POST['inv_content'] = isset($_POST['inv_content']) ? compile_str($_POST['inv_content']) : '';
		$_POST['postscript'] = isset($_POST['postscript']) ? compile_str($_POST['postscript']) : '';

		if (count($_POST['shipping']) == 1) {
			$shipping['shipping_id'] = $_POST['shipping'][0];
		}
		else {
			$shipping = get_order_post_shipping($_POST['shipping'], $_POST['shipping_code'], $_POST['shipping_type'], $_POST['ru_id']);
		}

		$order = array('shipping_id' => $shipping['shipping_id'], 'shipping_type' => $shipping['shipping_type'], 'pay_id' => isset($_POST['payment']) ? intval($_POST['payment']) : 0, 'pay_code' => isset($_POST['pay_code']) ? addslashes(trim($_POST['pay_code'])) : '', 'pack_id' => isset($_POST['pack']) ? intval($_POST['pack']) : 0, 'card_id' => isset($_POST['card']) ? intval($_POST['card']) : 0, 'card_message' => trim($_POST['card_message']), 'surplus' => isset($_POST['surplus']) ? floatval($_POST['surplus']) : 0, 'integral' => isset($_POST['integral']) ? intval($_POST['integral']) : 0, 'bonus_id' => isset($_POST['bonus']) ? intval($_POST['bonus']) : 0, 'uc_id' => isset($_POST['uc_id']) ? intval($_POST['uc_id']) : 0, 'not_freightfree' => isset($_POST['not_freightfree']) ? intval($_POST['not_freightfree']) : 0, 'vc_id' => isset($_POST['vc_id']) ? intval($_POST['vc_id']) : 0, 'need_inv' => isset($_POST['inv_payee']) ? 1 : 0, 'inv_type' => isset($_CFG['invoice_type']['type'][0]) ? $_CFG['invoice_type']['type'][0] : '', 'inv_payee' => isset($_POST['inv_payee']) ? trim($_POST['inv_payee']) : '', 'tax_id' => isset($_POST['tax_id']) ? trim($_POST['tax_id']) : '', 'inv_content' => isset($_POST['inv_content']) ? trim($_POST['inv_content']) : '', 'postscript' => trim($_POST['postscript']), 'how_oos' => isset($_LANG['oos'][$_POST['how_oos']]) ? addslashes($_LANG['oos'][$_POST['how_oos']]) : '', 'need_insure' => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0, 'user_id' => $_SESSION['user_id'], 'add_time' => $order_time, 'order_status' => OS_UNCONFIRMED, 'shipping_status' => SS_UNSHIPPED, 'mobile' => isset($_POST['store_mobile']) && !empty($_POST['store_mobile']) ? addslashes(trim($_POST['store_mobile'])) : '', 'pay_status' => PS_UNPAYED, 'agency_id' => get_agency_by_regions(array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'])), 'point_id' => isset($_POST['point_id']) ? intval($_POST['point_id']) : 0, 'shipping_dateStr' => isset($_POST['shipping_dateStr']) ? trim($_POST['shipping_dateStr']) : '', 'invoice_type' => isset($_POST['invoice_type']) ? intval($_POST['invoice_type']) : 0);
		if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS) {
			$order['extension_code'] = $_SESSION['extension_code'];
			$order['extension_id'] = $_SESSION['extension_id'];
		}
		else {
			$order['extension_code'] = '';
			$order['extension_id'] = 0;
		}

		$user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
		$sql_pay = 'SELECT pay_online, ec_salt, pay_password FROM ' . $ecs->table('users_paypwd') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\' LIMIT 1';
		$pay_online = $db->getRow($sql_pay);

		if ($pay_online['pay_online']) {
			$pay_surplus = isset($_REQUEST['surplus']) ? addslashes(trim($_REQUEST['surplus'])) : '';
			$pay_integral = isset($_REQUEST['integral']) ? addslashes(trim($_REQUEST['integral'])) : '';
			$pay_pwd = isset($_REQUEST['pay_pwd']) ? addslashes(trim($_REQUEST['pay_pwd'])) : '';
			$pay_pwd_error = isset($_REQUEST['pay_pwd_error']) ? intval($_REQUEST['pay_pwd_error']) : 0;
			if ($order['pay_code'] == 'onlinepay' || 0 < $pay_surplus || 0 < $pay_integral) {
				if ($pay_pwd_error == 0 && !empty($pay_pwd)) {
					$ec_salt = $pay_online['ec_salt'];
					$new_password = md5(md5($pay_pwd) . $ec_salt);
					if (!empty($pay_pwd) && $new_password != $pay_online['pay_password']) {
						show_message($_LANG['pay_password_packup_error'], $_LANG['back'], '', 'error');
					}
				}
			}
		}

		if (0 < $user_id) {
			$user_info = user_info($user_id);
			$order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);

			if ($order['surplus'] < 0) {
				$order['surplus'] = 0;
			}

			$flow_points = flow_available_points($done_cart_value, $region_id, $area_id);
			$user_points = $user_info['pay_points'];
			$order['integral'] = min($order['integral'], $user_points, $flow_points);

			if ($order['integral'] < 0) {
				$order['integral'] = 0;
			}
		}
		else {
			$order['surplus'] = 0;
			$order['integral'] = 0;
		}

		if (0 < $order['bonus_id']) {
			$bonus = bonus_info($order['bonus_id']);
			if (empty($bonus) || $bonus['user_id'] != $user_id || 0 < $bonus['order_id'] || cart_amount(true, $flow_type) < $bonus['min_goods_amount']) {
				$order['bonus_id'] = 0;
			}
		}
		else if (isset($_POST['bonus_psd'])) {
			$bonus_psd = trim($_POST['bonus_psd']);
			$bonus = bonus_info(0, $bonus_psd);
			$now = gmtime();
			if (empty($bonus) || 0 < $bonus['user_id'] || 0 < $bonus['order_id'] || cart_amount(true, $flow_type) < $bonus['min_goods_amount'] || $bonus['use_end_date'] < $now) {
			}
			else {
				if (0 < $user_id) {
					$sql = 'UPDATE ' . $ecs->table('user_bonus') . (' SET user_id = \'' . $user_id . '\' WHERE bonus_id = \'' . $bonus['bonus_id'] . '\' LIMIT 1');
					$db->query($sql);
				}

				$order['bonus_id'] = $bonus['bonus_id'];
				$order['bonus_psd'] = $bonus_psd;
			}
		}

		if (0 < $order['vc_id']) {
			$value_card = value_card_info($order['vc_id']);
			if (empty($value_card) || $value_card['user_id'] != $user_id) {
				$order['vc_id'] = 0;
			}
		}
		else if (isset($_POST['value_card_psd'])) {
			$value_card_psd = trim($_POST['value_card_psd']);
			$value_card = value_card_info(0, $value_card_psd);
			$now = gmtime();
			if (!(empty($value_card) || 0 < $value_card['user_id'])) {
				if (0 < $user_id && empty($value_card['end_time'])) {
					$end_time = ', end_time = \'' . local_strtotime('+' . $value_card['vc_indate'] . ' months ') . '\' ';
					$sql = ' UPDATE ' . $ecs->table('value_card') . (' SET user_id = \'' . $user_id . '\', bind_time = \'') . gmtime() . '\'' . $end_time . (' WHERE vid = \'' . $value_card['vid'] . '\' ');
					$db->query($sql);
					$order['vc_id'] = $value_card['vid'];
					$order['vc_psd'] = $value_card_psd;
				}
				else if ($value_card['end_time'] < $now) {
					$order['vc_id'] = 0;
				}
			}
		}

		if (0 < $order['uc_id']) {
			$coupons = get_coupons($order['uc_id']);
			if (empty($coupons) || $coupons['user_id'] != $user_id || $coupons['is_use'] == 1 || cart_amount(true, $flow_type) < $coupons['cou_man']) {
				$order['uc_id'] = 0;
			}
		}

		$cart_goods_list = cart_goods($flow_type, $done_cart_value, 1, $region_id, $area_id);
		$cart_goods = cart_goods($flow_type, $done_cart_value);

		if (empty($cart_goods)) {
			show_message($_LANG['no_goods_in_cart'], $_LANG['back_home'], './', 'warning');
		}

		if ($flow_type == CART_GENERAL_GOODS && cart_amount(true, CART_GENERAL_GOODS) < $_CFG['min_goods_amount']) {
			show_message(sprintf($_LANG['goods_amount_not_enough'], price_format($_CFG['min_goods_amount'], false)));
		}

		foreach ($consignee as $key => $value) {
			if ($key == 'mobile' && !empty($order['mobile'])) {
				$order[$key] = $order['mobile'];
			}
			else {
				$order[$key] = addslashes($value);
			}
		}

		foreach ($cart_goods as $val) {
			if ($val['is_real']) {
				$is_real_good = 1;
			}
		}

		foreach ($cart_goods_list as $key => $val) {
			foreach ($_POST['ru_id'] as $kk => $vv) {
				if ($val['ru_id'] == $vv) {
					$cart_goods_list[$key]['tmp_shipping_id'] = $_POST['shipping'][$kk];
					continue;
				}
			}
		}

		$total = order_fee($order, $cart_goods, $consignee, 1, $done_cart_value, $pay_type, $cart_goods_list, $region_id, $area_id, $store_id);
		$order['bonus'] = $total['bonus'];
		$order['coupons'] = $total['coupons'];
		$order['use_value_card'] = $total['use_value_card'];
		$order['goods_amount'] = $total['goods_price'];
		$order['cost_amount'] = $total['cost_price'];
		$order['discount'] = $total['discount'];
		$order['surplus'] = $total['surplus'];
		$order['tax'] = $total['tax'];
		$discount_amout = compute_discount_amount($done_cart_value);
		$temp_amout = $order['goods_amount'] - $discount_amout;

		if ($temp_amout <= 0) {
			$order['bonus_id'] = 0;
		}

		if (!empty($order['shipping_id'])) {
			$order['shipping_code'] = addslashes($shipping['shipping_code']);

			if (count($_POST['shipping']) == 1) {
				$shipping = shipping_info($order['shipping_id']);
			}

			$order['shipping_isarr'] = 0;
			$order['shipping_name'] = addslashes($shipping['shipping_name']);
			$shipping_name = !empty($order['shipping_name']) ? explode(',', $order['shipping_name']) : '';
			if ($shipping_name && 1 < count($shipping_name)) {
				$order['shipping_isarr'] = 1;
			}
		}

		$order['shipping_fee'] = $total['shipping_fee'];
		$order['insure_fee'] = $total['shipping_insure'];

		if (0 < $order['pay_id']) {
			$payment = payment_info($order['pay_id']);
			$order['pay_name'] = addslashes($payment['pay_name']);
			$order['pay_code'] = addslashes($payment['pay_code']);
		}

		$order['pay_fee'] = $total['pay_fee'];
		$order['cod_fee'] = $total['cod_fee'];

		if (0 < $order['pack_id']) {
			$pack = pack_info($order['pack_id']);
			$order['pack_name'] = addslashes($pack['pack_name']);
		}

		$order['pack_fee'] = $total['pack_fee'];

		if (0 < $order['card_id']) {
			$card = card_info($order['card_id']);
			$order['card_name'] = addslashes($card['card_name']);
		}

		$order['card_fee'] = $total['card_fee'];
		$order['order_amount'] = number_format($total['amount'], 2, '.', '');
		if (isset($_SESSION['direct_shopping']) && !empty($_SESSION['direct_shopping'])) {
			$where_flow = '?step=checkout&direct_shopping=' . $_SESSION['direct_shopping'];
		}

		$snapshot = false;
		if ($payment['pay_code'] == 'balance' && 0 < $order['order_amount']) {
			if (0 < $order['surplus']) {
				$order['order_amount'] = $order['order_amount'] + $order['surplus'];
				$order['surplus'] = 0;
			}

			if ($user_info['user_money'] + $user_info['credit_line'] < $order['order_amount']) {
				$location = 'flow.php';

				if ($_SESSION['flow_type'] == CART_PRESALE_GOODS) {
					$location = 'presale.php';
					$where_flow = '?id=' . $order['extension_id'] . '&act=view';
				}
				else if ($_SESSION['flow_type'] == CART_GROUP_BUY_GOODS) {
					$location = 'group_buy.php';
					$where_flow = '?act=view&id=' . $order['extension_id'];
				}

				show_message($_LANG['balance_not_enough'], $_LANG['back_up_page'], $location . $where_flow);
			}
			else {
				if ($_SESSION['flow_type'] == CART_PRESALE_GOODS || $_SESSION['flow_type'] == CART_GROUP_BUY_GOODS) {
					$order['surplus'] = $order['order_amount'];
					$order['pay_status'] = PS_PAYED_PART;
					$order['order_status'] = OS_CONFIRMED;
					$order['order_amount'] = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['tax'] - $order['discount'] - $order['surplus'];
				}
				else {
					$order['surplus'] = $order['order_amount'];
					$order['order_amount'] = 0;
					$snapshot = true;
				}
			}
		}

		$stores_sms = 0;

		if ($order['order_amount'] <= 0) {
			$order['order_status'] = OS_CONFIRMED;
			$order['confirm_time'] = gmtime();
			$order['pay_status'] = PS_PAYED;
			$order['pay_time'] = gmtime();
			$order['order_amount'] = 0;
			$stores_sms = 1;
		}

		$order['integral_money'] = $total['integral_money'];
		$order['integral'] = $total['integral'];

		if ($order['extension_code'] == 'exchange_goods') {
			$order['integral_money'] = value_of_integral($total['exchange_integral']);
			$order['integral'] = $total['exchange_integral'];
			$order['goods_amount'] = value_of_integral($total['exchange_integral']);
		}

		$order['from_ad'] = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';
		$order['referer'] = !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : '';

		if ($flow_type != CART_GENERAL_GOODS) {
			$order['extension_code'] = $_SESSION['extension_code'];
			$order['extension_id'] = $_SESSION['extension_id'];
		}

		$cloud_order_list = array();
		$parentordersn = '';
		$requ = set_cloud_order_goods($cart_goods, $order);

		if (!empty($requ)) {
			if ($requ['code'] == '10000') {
				$parentordersn = $requ['data']['result'];
				$cloud_order_list = $requ['data']['orderDetailList'];
			}
			else {
				show_message($requ['message'], '', '', 'info', true);
			}
		}

		$affiliate = unserialize($_CFG['affiliate']);
		if (isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 1) {
			$parent_id = get_affiliate();

			if ($user_id == $parent_id) {
				$parent_id = 0;
			}
		}
		else {
			if (isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 0) {
				$parent_id = 0;
			}
			else {
				$parent_id = 0;
			}
		}

		$order['parent_id'] = $parent_id;
		$goodsIn = '';
		$cartValue = !empty($done_cart_value) ? $done_cart_value : 0;

		if ($cartValue) {
			if (file_exists(MOBILE_DRP)) {
				$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('drp_config') . ' WHERE code = \'drp_affiliate\' LIMIT 1';
				$drp_affiliate = $GLOBALS['db']->getRow($sql);
				$drp_affiliate = unserialize($drp_affiliate['value']);
				empty($drp_affiliate) && ($drp_affiliate = array());
				if (isset($drp_affiliate['on']) && $drp_affiliate['on'] == 1) {
					$parent_id = get_affiliate();

					if ($parent_id) {
						$is_distribution = 1;
					}
					else {
						$is_distribution = 0;
					}
				}

				if (!empty($cartValue)) {
					$goodsIn = ' AND ca.rec_id ' . db_create_in($cartValue);
				}

				$sql = 'INSERT INTO ' . $ecs->table('order_goods') . '( ' . 'user_id, cart_recid, goods_id, goods_name, goods_sn, product_id,is_reality,is_return,is_fast, goods_number, market_price, goods_price, stages_qishu,' . 'goods_attr, is_real, extension_code, parent_id, is_gift, model_attr, goods_attr_id, ru_id, shopping_fee, warehouse_id, area_id, freight, tid, shipping_fee, is_distribution, drp_money, commission_rate) ' . (' SELECT \'' . $user_id . '\', \'' . $cartValue . '\', ca.goods_id, ca.goods_name, ca.goods_sn, ca.product_id,(SELECT is_reality FROM ') . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id),(SELECT is_return FROM ' . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id),(SELECT is_fast FROM ' . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id), ca.goods_number, ca.market_price, ca.goods_price, ca.stages_qishu, ca.goods_attr, ' . 'ca.is_real, ca.extension_code, ca.parent_id, ca.is_gift, ca.model_attr, ca.goods_attr_id, ca.ru_id, ca.shopping_fee, ca.warehouse_id, ca.area_id, ca.freight, ca.tid, ca.shipping_fee,' . ('g.is_distribution*\'' . $is_distribution . '\' AS is_distribution, ') . ('g.dis_commission * g.is_distribution * ca.goods_price * ca.goods_number / 100 * \'' . $is_distribution . '\' AS drp_money, ca.commission_rate') . ' FROM ' . $ecs->table('cart') . ' ca' . ' LEFT JOIN  ' . $ecs->table('goods') . ' as g ON ca.goods_id = g.goods_id' . ' WHERE ca.' . $sess_id . (' AND ca.rec_type = \'' . $flow_type . '\'') . $goodsIn;
				$db->query($sql);
			}
			else {
				if (!empty($cartValue)) {
					$goodsIn = ' AND rec_id ' . db_create_in($cartValue);
				}

				$sql = 'INSERT INTO ' . $ecs->table('order_goods') . '( ' . 'user_id, cart_recid, goods_id, goods_name, goods_sn, product_id,is_reality,is_return,is_fast, goods_number, market_price, commission_rate, stages_qishu, ' . 'goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, model_attr, goods_attr_id, ru_id, shopping_fee, warehouse_id, area_id, freight, tid, shipping_fee) ' . (' SELECT \'' . $user_id . '\', \'' . $cartValue . '\', ca.goods_id, ca.goods_name, ca.goods_sn, ca.product_id,(SELECT is_reality FROM ') . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id),(SELECT is_return FROM ' . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id),(SELECT is_fast FROM ' . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id), ca.goods_number, ca.market_price, ca.commission_rate, ca.stages_qishu, ' . 'ca.goods_price, ca.goods_attr, ca.is_real, ca.extension_code, ca.parent_id, ca.is_gift, ca.model_attr, ca.goods_attr_id, ca.ru_id, ca.shopping_fee, ca.warehouse_id, ca.area_id, ca.freight, ca.tid, ca.shipping_fee' . ' FROM ' . $ecs->table('cart') . ' AS ca' . ' WHERE ' . $sess_id . (' AND rec_type = \'' . $flow_type . '\'') . $goodsIn;
				$db->query($sql);
			}

			clear_cart($flow_type, $cartValue);
		}

		$new_order_id = 0;

		if ($cartValue) {
			$is_cart = get_is_cart_goods($user_id, $cartValue);

			if ($is_cart) {
				$error_no = 0;

				do {
					if ($order['not_freightfree'] == 1) {
						$order['uc_id'] = 0;
					}

					$order['order_sn'] = get_order_sn();
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');
					$error_no = $GLOBALS['db']->errno();
					if (0 < $error_no && $error_no != 1062) {
						exit($GLOBALS['db']->errorMsg());
					}
				} while ($error_no == 1062);

				$new_order_id = $db->insert_id();

				if (0 < $new_order_id) {
					$order_cart_where = '';
					$order_cart_value = explode(',', $cartValue);

					foreach ($order_cart_value as $key => $val) {
						$order_cart_where .= ' AND FIND_IN_SET(\'' . $val . '\', cart_recid) ';
					}

					$sql = 'SELECT rec_id,model_attr,product_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE user_id = \'' . $user_id . '\' AND order_id = 0 ' . $order_cart_where);
					$goods_product = $GLOBALS['db']->getAll($sql);

					foreach ($goods_product as $gkey => $gval) {
						if ($gval['model_attr'] == 1) {
							$table = 'products_warehouse';
						}
						else if ($gval['model_attr'] == 2) {
							$table = 'products_area';
						}
						else {
							$table = 'products';
						}

						$sql = 'SELECT product_sn FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE product_id = \'' . $gval['product_id'] . '\'';
						$product_sn = $GLOBALS['db']->getOne($sql);
						$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . (' SET order_id = \'' . $new_order_id . '\',product_sn = \'' . $product_sn . '\' WHERE rec_id = ') . $gval['rec_id'];
						$db->query($sql);
					}

					$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $new_order_id . '\'');

					if (!$GLOBALS['db']->getOne($sql)) {
						$sql = 'DELETE FROM' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $new_order_id . '\'');
						$db->query($sql);
						$new_order_id = 0;
					}
					else {
						del_unique_goods($new_order_id);
					}
				}
			}
		}

		$order['order_id'] = $new_order_id;

		if (!empty($cloud_order_list)) {
			foreach ($cloud_order_list as $k => $v) {
				$cloud_order = array();
				$cloud_order['apiordersn'] = trim($v['apiOrderSn']);
				$cloud_order['parentordersn'] = trim($requ['data']['result']);
				$cloud_order['goods_id'] = intval($v['goodId']);
				$cloud_order['user_id'] = $order['user_id'];
				$cloud_order['cloud_orderid'] = $v['orderId'];
				$cloud_order['cloud_detailed_id'] = $v['id'];
				$totalprice = !empty($v['totalPrice']) ? trim($v['totalPrice']) : 0;

				if (0 < $totalprice) {
					$totalprice = $totalprice / 100;
				}

				$totalprice = floatval($totalprice);
				$cloud_order['totalprice'] = $totalprice;
				$sql = 'SELECT og.rec_id FROM' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = \'' . $order['order_id'] . '\' AND (SELECT g.goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g WHERE g.cloud_id = \'' . $v['goodId'] . '\') = og.goods_id AND og.user_id = \'' . $order['user_id'] . '\'';
				$cloud_order['rec_id'] = $GLOBALS['db']->getOne($sql);
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_cloud'), $cloud_order, 'INSERT');
			}
		}

		$order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);
		$good_ru_id = !empty($_REQUEST['ru_id']) ? dsc_addslashes($_REQUEST['ru_id'], 1) : 0;
		if (0 < $new_order_id && 0 < $store_id && $good_ru_id) {
			$take_time = isset($_POST['take_time']) && !empty($_POST['take_time']) ? addslashes(trim($_POST['take_time'])) : '';

			foreach ($good_ru_id as $v) {
				$pick_code = substr($order['order_sn'], -3) . rand(0, 9) . rand(0, 9) . rand(0, 9);

				if ($stores_sms != 1) {
					$pick_code = '';
				}

				if (0 < $new_order_id) {
					$sql = 'INSERT INTO' . $ecs->table('store_order') . (' (`order_id`,`store_id`,`ru_id`,`pick_code`,`take_time`) VALUES (\'' . $new_order_id . '\',\'' . $store_id . '\',\'' . $v . '\',\'' . $pick_code . '\',\'' . $take_time . '\')');
					$db->query($sql);
				}
			}
		}

		if (0 < $new_order_id && $order['extension_code'] == 'auction') {
			$is_finished = 2;
			$sql = 'SELECT ext_info FROM' . $ecs->table('goods_activity') . 'WHERE act_id=\'' . $order['extension_id'] . '\' LIMIT 1';
			$activity_ext_info = $db->getOne($sql);

			if ($activity_ext_info) {
				$activity_ext_info = unserialize($activity_ext_info);

				if (0 < $activity_ext_info['deposit']) {
					$is_finished = 1;
				}
			}

			$sql = 'UPDATE ' . $ecs->table('goods_activity') . (' SET is_finished=\'' . $is_finished . '\' WHERE act_id = \'') . $order['extension_id'] . '\'';
			$db->query($sql);
		}

		if (0 < $new_order_id && 0 < $order['user_id'] && (0 < $order['surplus'] || 0 < $order['integral'])) {
			if (0 < $order['surplus']) {
				$order_surplus = $order['surplus'] * -1;
			}
			else {
				$order_surplus = 0;
			}

			if (0 < $order['integral']) {
				$order_integral = $order['integral'] * -1;
			}
			else {
				$order_integral = 0;
			}

			log_account_change($order['user_id'], $order_surplus, 0, 0, $order_integral, sprintf($_LANG['pay_order'], $order['order_sn']));
		}

		if (0 < $new_order_id && 0 < $order['bonus_id'] && 0 < $temp_amout) {
			use_bonus($order['bonus_id'], $new_order_id);
		}

		if (0 < $new_order_id && 0 < $order['vc_id']) {
			use_value_card($order['vc_id'], $new_order_id, $order['use_value_card']);
		}

		if (0 < $new_order_id && 0 < $order['uc_id']) {
			use_coupons($order['uc_id'], $new_order_id);
		}

		if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE) {
			change_order_goods_storage($new_order_id, true, SDT_PLACE, $_CFG['stock_dec_time'], 0, $store_id);
		}

		if (0 < $new_order_id && $_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PAID && $order['order_amount'] <= 0) {
			change_order_goods_storage($order['order_id'], true, SDT_PAID, $_CFG['stock_dec_time'], 0, $store_id);
		}

		$order_info = get_main_order_info($order['order_id'], 1);
		$ru_id = explode(',', $order_info['all_ruId']['ru_id']);
		if (0 < $new_order_id && $order['order_amount'] <= 0 && count($ru_id) == 1) {
			cloud_confirmorder($new_order_id);
			$virtual_goods = get_virtual_goods($new_order_id);
			if ($virtual_goods && $flow_type != CART_GROUP_BUY_GOODS) {
				if (virtual_goods_ship($virtual_goods, $msg, $order['order_sn'], true)) {
					$sql = 'SELECT COUNT(*)' . ' FROM ' . $ecs->table('order_goods') . (' WHERE order_id = \'' . $order['order_id'] . '\' ') . ' AND is_real = 1';

					if ($db->getOne($sql) <= 0) {
						update_order($order['order_id'], array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));

						if (0 < $order['user_id']) {
							$user = user_info($order['user_id']);
							$integral = integral_to_give($order);
							$gave_custom_points = integral_of_value($integral['custom_points']) - $order['integral'];

							if ($gave_custom_points < 0) {
								$gave_custom_points = 0;
							}

							log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));
							send_order_bonus($order['order_id']);
							send_order_coupons($order['order_id']);
						}
					}
				}
			}
		}

		$stages_intro = array();
		if (0 < $new_order_id && 0 < $order['order_amount']) {
			$onlinepay_pay_id = $db->getOne('SELECT pay_id FROM ' . $ecs->table('payment') . ' WHERE pay_code=\'onlinepay\'');

			if ($order['pay_id'] == $onlinepay_pay_id) {
				$baitiao_balance = get_baitiao_balance($user_id);
				$payment_list = available_payment_list(0, $cod_fee, false, $order['order_amount']);

				foreach ($payment_list as $K => $v) {
					if ($v['is_online'] == 1) {
						$payment_file = 'includes/modules/payment/' . $v['pay_code'] . '.php';

						if (file_exists($payment_file)) {
							include_once $payment_file;
							$pay_obj = new $v['pay_code']();
							$payment = payment_info($v['pay_id']);
							$par_order = $order;
							$par_order['order_amount'] = $par_order['order_amount'] + $v['pay_fee_amount'];
							$pay_online_button[$v['pay_code']] = '      ' . $pay_obj->get_code($par_order, unserialize_config($v['pay_config']));

							if ($v['pay_code'] == 'alipay_bank') {
								$smarty->assign('is_alipay_bank', $pay_online_button['alipay_bank']);
								unset($pay_online_button['alipay_bank']);
							}

							if ($v['pay_code'] == 'balance') {
								$pay_online_button['balance'] = '                    <a href="flow.php?step=done&act=balance&order_sn=' . $order['order_sn'] . '&store_id=' . $store_id . '" id="balance" style="float: left;" order_sn="' . $order['order_sn'] . '" flag="balance" >' . $_LANG['balance_pay'] . '</a>';
							}

							if ($baitiao_balance && 0 < $baitiao_balance['balance']) {
								$smarty->assign('is_chunsejinrong', true);

								if ($v['pay_code'] == 'chunsejinrong') {
									$pay_online_button['chunsejinrong'] = '                            <a href="flow.php?step=done&act=chunsejinrong&order_sn=' . $order['order_sn'] . '&store_id=' . $store_id . '" id="chunsejinrong" style="height:36px; line-height:36px; float: left;" order_sn="' . $order['order_sn'] . '" flag="chunsejinrong" >' . $_LANG['ious_pay'] . '</a>';
								}
							}
						}
					}
				}

				$smarty->assign('pay_online_button', $pay_online_button);
				$smarty->assign('is_onlinepay', true);
			}
			else {
				$payment = payment_info($order['pay_id']);
				$payment_file = 'includes/modules/payment/' . $payment['pay_code'] . '.php';

				if (file_exists($payment_file)) {
					include_once $payment_file;
					$pay_obj = new $payment['pay_code']();
					$pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));
				}
				else {
					$pay_online = '';
				}

				$order['pay_desc'] = $payment['pay_desc'];
			}

			if ($_SESSION['flow_type'] == 5) {
				$smarty->assign('is_presale_goods', true);
			}

			$smarty->assign('pay_online', $pay_online);
			$sql = 'SELECT goods_id, stages_qishu, goods_number FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $new_order_id . '\' LIMIT 1');
			$cart_info = $GLOBALS['db']->getRow($sql);

			if (0 < $cart_info['stages_qishu']) {
				$stages_rate = $db->getOne('SELECT stages_rate FROM ' . $ecs->table('goods') . ' WHERE goods_id = \'' . $cart_info['goods_id'] . '\'', true);
				$order_sn = $order['order_sn'];
				$stages_qishu = $cart_info['stages_qishu'];
				$goods_number = $cart_info['goods_number'];
				$shop_price_total = $order['order_amount'];
				$stages_one_price = round($shop_price_total * ($stages_rate / 100) + $shop_price_total / $stages_qishu, 2);

				if ($stages_qishu == 1) {
					$repay_datee[1] = local_date('Y-m-d', local_strtotime('+1 month'));
					$stages_one_price = round($shop_price_total, 2);
				}
				else {
					$bt_other = array('user_id' => $user_id);
					$bt_info = get_baitiao_info($bt_other);

					for ($i = 1; $i <= $stages_qishu; $i++) {
						$value = $i * $bt_info['repay_term'];
						$repay_datee[$i] = local_date('Y-m-d', local_strtotime('+' . $value . ' day'));
					}
				}

				$repay_date = serialize($repay_datee);
				$stages_other = array('order_sn' => $order_sn, 'stages_total' => $stages_qishu, 'stages_one_price' => $stages_one_price, 'yes_num' => '0', 'create_date' => gmtime(), 'repay_date' => $repay_date);
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('stages'), $stages_other, 'INSERT');
				$stages_intro = array('stages_qishu' => $stages_qishu, 'goods_number' => $goods_number, 'stages_one_price' => $stages_one_price, 'repay_date' => $repay_datee[1], 'baitiao' => isset($baitiao_balance['balance']) && !empty($baitiao_balance['balance']) ? $baitiao_balance['balance'] : 0);
			}
		}

		$smarty->assign('stages_info', $stages_intro);

		if (0 < $store_id) {
			$sql = 'SELECT id, country, province, city, district, stores_address, stores_name, stores_tel FROM ' . $ecs->table('offline_store') . (' WHERE id = \'' . $store_id . '\' LIMIT 1');
			$stores_info = $db->getRow($sql);
			$smarty->assign('stores_info', $stores_info);
		}

		if ($stages_intro) {
			$order_reload = $stages_intro;
			$_SESSION['order_reload'][$user_id] = $order_reload;
		}

		$_SESSION['order_reload'][$user_id]['order_id'] = $new_order_id;
		clear_all_files();

		if (!empty($order['shipping_name'])) {
			$order['shipping_name'] = trim(stripcslashes($order['shipping_name']));
		}

		if (isset($_SESSION['direct_shopping'])) {
			$smarty->assign('direct_shopping', $_SESSION['direct_shopping']);
		}

		if (0 < $new_order_id) {
			if ($order['extension_code'] == 'presale') {
				get_presale_num($new_order_id);
			}

			get_goods_sale($new_order_id);

			if ($snapshot) {
				create_snapshot($new_order_id);
			}
		}

		$order['format_shipping_fee'] = price_format($order['shipping_fee']);
		$order['format_order_amount'] = price_format($order['order_amount']);
		$smarty->assign('order', $order);
		$smarty->assign('total', $total);
		$smarty->assign('order_submit_back', sprintf($_LANG['order_submit_back'], $_LANG['back_home'], $_LANG['goto_user_center']));
		user_uc_call('add_feed', array($order['order_id'], BUY_GOODS));
		unset($_SESSION['flow_consignee']);
		unset($_SESSION['flow_order']);
		unset($_SESSION['direct_shopping']);
		$order_id = $order['order_id'];
		$row = get_main_order_info($order_id);
		$order_info = get_main_order_info($order_id, 1);
		$ru_id = explode(',', $order_info['all_ruId']['ru_id']);
		$seller_grade = 1;

		if (1 < count($ru_id)) {
			$is_payment = get_payment_code();

			if ($is_payment) {
				$sg_ru_id = get_array_flip(0, $ru_id);
				$seller_grade = get_seller_grade($sg_ru_id, 1);
			}

			$smarty->assign('seller_grade', $seller_grade);
		}
		else if ($ru_id[0] == 0) {
			$smarty->assign('seller_grade', $seller_grade);
		}
		else {
			$is_payment = get_payment_code();

			if ($is_payment) {
				$seller_grade = get_seller_grade($ru_id, 1);
			}

			$smarty->assign('seller_grade', $seller_grade);
		}

		$ru_number = count($ru_id);
		if (0 < $order_id && 1 < $ru_number) {
			get_insert_order_goods_single($order_info, $row, $order_id, $ru_number);
		}

		$child_order = 0;
		if (0 < $order_id || !isset($_SESSION['order_reload'])) {
			$sql = 'SELECT count(*) FROM ' . $ecs->table('order_info') . ' WHERE main_order_id = \'' . $order['order_id'] . '\'';
			$child_order = $db->getOne($sql, true);

			if (1 < $child_order) {
				$child_order_info = get_child_order_info($order['order_id']);
				$smarty->assign('child_order_info', $child_order_info);
			}

			$smarty->assign('pay_type', $pay_type);
			$smarty->assign('child_order', $child_order);
			$goods_buy_list = get_order_goods_buy_list($region_id, $area_id);
			$smarty->assign('goods_buy_list', $goods_buy_list);
			if ($stores_sms == 1 && 0 < $store_id) {
				$user_name = isset($_SESSION['user_name']) && !empty($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

				if ($store_id) {
					if ($order['mobile']) {
						$user_mobile_phone = $order['mobile'];
					}
					else {
						$sql = 'SELECT mobile_phone FROM ' . $ecs->table('users') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\'';
						$user_mobile_phone = $db->getOne($sql, true);
					}

					$store_address = get_area_region_info($stores_info) . $stores_info['stores_address'];
					$store_smsParams = array('user_name' => $user_name, 'username' => $user_name, 'order_sn' => $order['order_sn'], 'ordersn' => $order['order_sn'], 'code' => $pick_code, 'store_address' => $store_address, 'storeaddress' => $store_address, 'mobile_phone' => $user_mobile_phone, 'mobilephone' => $user_mobile_phone);
					if ($GLOBALS['_CFG']['sms_type'] == 0 && $user_mobile_phone) {
						huyi_sms($store_smsParams, 'store_order_code');
					}
					else {
						if (1 <= $GLOBALS['_CFG']['sms_type'] && $user_mobile_phone) {
							$store_result = sms_ali($store_smsParams, 'store_order_code');
							$resp = $GLOBALS['ecs']->ali_yu($store_result);
						}
					}
				}
			}

			if (count($ru_id) == 1) {
				$sellerId = $ru_id[0];
				$shop_name = get_shop_name($sellerId, 1);

				if ($sellerId == 0) {
					$sms_shop_mobile = $_CFG['sms_shop_mobile'];
					$service_email = $_CFG['service_email'];
				}
				else {
					$sql = 'SELECT mobile, seller_email FROM ' . $ecs->table('seller_shopinfo') . (' WHERE ru_id = \'' . $sellerId . '\' LIMIT 1');
					$seller_shopinfo = $db->getRow($sql);
					$sms_shop_mobile = isset($seller_shopinfo['mobile']) && !empty($seller_shopinfo['mobile']) ? $seller_shopinfo['mobile'] : '';
					$service_email = isset($seller_shopinfo['seller_email']) && !empty($seller_shopinfo['seller_email']) ? $seller_shopinfo['seller_email'] : '';
				}

				$sql = ' select * from ' . $GLOBALS['ecs']->table('crons') . ' where cron_code=\'auto_sms\' and enable=1 LIMIT 1';
				$auto_sms = $GLOBALS['db']->getRow($sql);
				if ($_CFG['sms_order_placed'] == '1' && $sms_shop_mobile) {
					if (!empty($auto_sms)) {
						$sql = ' insert into ' . $GLOBALS['ecs']->table('auto_sms') . ' (item_id,item_type,user_id,ru_id,order_id,add_time) ' . ' VALUES ' . '(NULL,1,\'' . $order['user_id'] . '\',\'' . $sellerId . '\',\'' . $order['order_id'] . '\',\'' . gmtime() . '\')';
						$GLOBALS['db']->query($sql);
					}
					else {
						$order_region = get_flow_user_region($order_id);
						$pt_smsParams = array('shop_name' => $shop_name, 'shopname' => $shop_name, 'order_sn' => $order['order_sn'], 'ordersn' => $order['order_sn'], 'consignee' => $order['consignee'], 'order_region' => $order_region, 'orderregion' => $order_region, 'address' => $order['address'], 'order_mobile' => $order['mobile'], 'ordermobile' => $order['mobile'], 'mobile_phone' => $sms_shop_mobile, 'mobilephone' => $sms_shop_mobile);

						if ($GLOBALS['_CFG']['sms_type'] == 0) {
							huyi_sms($pt_smsParams, 'sms_order_placed');
						}
						else if (1 <= $GLOBALS['_CFG']['sms_type']) {
							$pt_result = sms_ali($pt_smsParams, 'sms_order_placed');
							$resp = $GLOBALS['ecs']->ali_yu($pt_result);
						}
					}
				}

				if (($sellerId == 0 && $_CFG['send_service_email'] == '1' || 0 < $sellerId && $_CFG['seller_email'] == '1') && $service_email) {
					$smarty->assign('done_cart_value', $cartValue);
					$smarty->assign('order_id', $order_id);
					$smarty->assign('ajax_send_mail', true);
					$smarty->assign('send_time', 'send_service_email');
				}
			}
		}
		else {
			if ($stages_intro) {
				$order_reload = $stages_intro;
				$_SESSION['order_reload'][$user_id] = $order_reload;
			}

			$order_goods = get_order_cart_value($user_id, $cartValue);
			$where_reload = '&order_id=' . $order_goods['order_id'];
			$_SESSION['order_reload'][$user_id]['order_id'] = $order_goods['order_id'];
			ecs_header('Location:flow.php?step=order_reload' . $where_reload . "\n");
			exit();
		}
	}

	if (defined('THEME_EXTENSION')) {
		$region = array('province' => $order['province'], 'city' => $order['city'], 'district' => $order['district'], 'street' => $order['street']);
		$address_info = get_area_region_info($region);
		$smarty->assign('address_info', $address_info);
	}
}
else if ($_REQUEST['step'] == 'ajax_update_cart') {
	require_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => 0, 'message' => '');
	if (isset($_POST['rec_id']) && isset($_POST['goods_number'])) {
		$key = !empty($_POST['rec_id']) ? intval($_POST['rec_id']) : 0;
		$val = !empty($_POST['goods_number']) ? intval($_POST['goods_number']) : 0;
		$warehouse_id = !empty($_POST['warehouse_id']) ? intval($_POST['warehouse_id']) : 0;
		$area_id = !empty($_POST['area_id']) ? intval($_POST['area_id']) : 0;
		$val = intval(make_semiangle($val));
		if ($val <= 0 && !is_numeric($key)) {
			$result['error'] = 99;
			$result['message'] = '';
			exit($json->encode($result));
		}

		$leftJoin = ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_goods') . (' AS wg ON g.goods_id = wg.goods_id AND wg.region_id = \'' . $warehouse_id . '\' ');
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' AS wag ON g.goods_id = wag.goods_id AND wag.region_id = \'' . $area_id . '\' ');
		$sql = 'SELECT g.goods_name, wg.region_number as wg_number, wag.region_number as wag_number, g.model_price, g.model_inventory, g.model_attr, g.goods_number, g.group_number, ' . 'c.goods_id, c.goods_attr_id, c.product_id, c.extension_code, c.warehouse_id, c.area_id, c.ru_id, ' . 'c.group_id, c.extension_code, c.goods_name AS act_name, g.freight, g.tid, g.shipping_fee, g.cloud_id ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('cart') . ' AS c on g.goods_id = c.goods_id ' . $leftJoin . ('WHERE c.rec_id = \'' . $key . '\' LIMIT 1');
		$row = $GLOBALS['db']->getRow($sql);
		$result['ru_id'] = $row['ru_id'];
		$nowTime = gmtime();
		$xiangouInfo = get_purchasing_goods_info($row['goods_id']);
		$start_date = $xiangouInfo['xiangou_start_date'];
		$end_date = $xiangouInfo['xiangou_end_date'];
		if ($xiangouInfo['is_xiangou'] == 1 && $start_date < $nowTime && $nowTime < $end_date) {
			$user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
			$orderGoods = get_for_purchasing_goods($start_date, $end_date, $row['goods_id'], $user_id);

			if ($xiangouInfo['xiangou_num'] <= $orderGoods['goods_number']) {
				$max_num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
				$result['message'] = sprintf($_LANG['purchase_Prompt'], $row['goods_name']);
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number = 0 WHERE rec_id=\'' . $key . '\'');
				$GLOBALS['db']->query($sql);
				$result['error'] = 1;
				exit($json->encode($result));
			}
			else if (0 < $xiangouInfo['xiangou_num']) {
				if ($xiangouInfo['is_xiangou'] == 1 && $xiangouInfo['xiangou_num'] < $orderGoods['goods_number'] + $val) {
					$max_num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
					$result['message'] = sprintf($_LANG['purchasing_prompt'], $row['goods_name']);
					$cart_Num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number = \'' . $cart_Num . '\' WHERE rec_id=\'' . $key . '\'');
					$GLOBALS['db']->query($sql);
					$result['error'] = 1;
					$result['cart_Num'] = $cart_Num;
					$result['rec_id'] = $key;
					exit($json->encode($result));
				}
			}
		}

		if (0 < intval($GLOBALS['_CFG']['use_storage']) && $row['extension_code'] != 'package_buy') {
			if ($row['model_inventory'] == 1) {
				$row['goods_number'] = $row['wg_number'];
			}
			else if ($row['model_inventory'] == 2) {
				$row['goods_number'] = $row['wag_number'];
			}

			if (!empty($row['product_id'])) {
				$select = '';

				if ($row['model_attr'] == 1) {
					$table_products = 'products_warehouse';
				}
				else if ($row['model_attr'] == 2) {
					$table_products = 'products_area';
				}
				else {
					$table_products = 'products';
					$select = ',cloud_product_id ';
				}

				$sql = 'SELECT product_number ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $row['goods_id'] . '\' and product_id = \'' . $row['product_id'] . '\' LIMIT 1';
				$prod = $GLOBALS['db']->getRow($sql);
				$product_number = $prod['product_number'];

				if (0 < $row['cloud_id']) {
					$product_number = 0;
					$plugin_file = ROOT_PATH . '/plugins/cloudApi/cloudApi.php';
					$productIds = array($prod['cloud_product_id']);

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
											$product_number = $v['taxNum'];
										}
										else {
											$product_number = $v['noTaxNum'];
										}

										break;
									}
								}
							}
						}
					}
				}

				if ($product_number < $val) {
					$result['error'] = 2;
					$result['message'] = sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'], $product_number, $product_number);
					exit($json->encode($result));
				}
			}
			else if ($row['goods_number'] < $val) {
				$result['error'] = 1;
				$result['message'] = sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'], $row['goods_number'], $row['goods_number']);
				exit($json->encode($result));
			}
		}
		else {
			if (0 < intval($GLOBALS['_CFG']['use_storage']) && $row['extension_code'] == 'package_buy') {
				if (judge_package_stock($row['goods_id'], $val)) {
					$result['error'] = 3;
					$result['message'] = $GLOBALS['_LANG']['package_stock_insufficiency'];
					exit($json->encode($result));
				}
			}
		}

		$sql = "SELECT b.goods_number,b.rec_id\r\n                FROM " . $GLOBALS['ecs']->table('cart') . ' a, ' . $GLOBALS['ecs']->table('cart') . (" b\r\n                WHERE a.rec_id = '" . $key . "'\r\n                AND ") . $a_sess . "\r\n                AND a.extension_code <>'package_buy'\r\n                AND b.parent_id = a.goods_id\r\n                AND " . $b_sess;
		$offers_accessories_res = $GLOBALS['db']->getAll($sql);

		if (0 < $val) {
			if (0 < $row['group_number'] && $row['group_number'] < $val && !empty($row['group_id'])) {
				$result['error'] = 1;
				$result['message'] = sprintf($GLOBALS['_LANG']['group_stock_insufficiency'], $row['goods_name'], $row['group_number'], $row['group_number']);
				exit($json->encode($result));
			}

			for ($i = 0; $i < count($offers_accessories_res); $i++) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number = \'' . $val . '\'') . ' WHERE rec_id =\'' . $offers_accessories_res[$i]['rec_id'] . '\'';
				$GLOBALS['db']->query($sql);
			}

			if ($row['extension_code'] == 'package_buy') {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number= \'' . $val . '\' WHERE rec_id = \'' . $key . '\'');
				$GLOBALS['db']->query($sql);
			}
			else {
				if ($GLOBALS['_CFG']['add_shop_price'] == 1) {
					$add_tocart = 1;
				}
				else {
					$add_tocart = 0;
				}

				$attr_id = empty($row['goods_attr_id']) ? array() : explode(',', $row['goods_attr_id']);
				$goods_price = get_final_price($row['goods_id'], $val, true, $attr_id, $_POST['warehouse_id'], $_POST['area_id'], 0, 0, $add_tocart);
				$set = '';

				if (0 < $goods_price) {
					$set = 'goods_price = \'' . $goods_price . '\', ';
				}

				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number = \'' . $val . '\', ') . $set . ' freight = \'' . $row['freight'] . '\', tid = \'' . $row['tid'] . '\', shipping_fee = \'' . $row['shipping_fee'] . ('\' WHERE rec_id = \'' . $key . '\'');
				$GLOBALS['db']->query($sql);
			}
		}
		else {
			for ($i = 0; $i < count($offers_accessories_res); $i++) {
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE rec_id =\'' . $offers_accessories_res[$i]['rec_id'] . '\'';
				$GLOBALS['db']->query($sql);
			}

			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . (' WHERE rec_id = \'' . $key . '\'');
			$GLOBALS['db']->query($sql);
		}

		$result['rec_id'] = $key;
		$result['goods_number'] = $val;
		$result['total_desc'] = '';

		if ($row['extension_code'] == 'package_buy') {
			$result['ext_info'] = $GLOBALS['db']->getOne('SELECT ext_info FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' WHERE review_status = 3 AND act_name = \'' . $row['act_name'] . '\'');
			$ext_arr = unserialize($result['ext_info']);
			unset($result['ext_info']);
			$goods_price = $ext_arr['package_price'];
		}

		$result['goods_price'] = price_format($goods_price);
		$result['cart_info'] = insert_cart_info(4);
		$cValue = htmlspecialchars($_POST['cValue']);
		$cart_goods = get_cart_goods($cValue, 0, $warehouse_id, $area_id);

		foreach ($cart_goods['goods_list'] as $goods) {
			if ($goods['rec_id'] == $key) {
				if (0 < $goods['dis_amount']) {
					$result['goods_subtotal'] = price_format($goods['subtotal']);
					$result['discount_amount'] = $goods['discount_amount'];
				}
				else {
					$result['goods_subtotal'] = price_format($goods['subtotal']);
					$result['discount_amount'] = price_format(0);
				}

				$result['rec_goods'] = $goods['goods_id'];
				break;
			}
		}

		$discount = compute_discount(3, $cValue);
		$goods_discount_amount = get_cart_check_goods($cart_goods['goods_list'], $key, 1);
		$fav_amount = $discount['discount'];
		$result['save_total_amount'] = price_format($fav_amount + $goods_discount_amount['save_amount']);
		$result['dis_amount'] = $goods_discount_amount['save_amount'];
		$result['group'] = array();
		$subtotal_number = 0;

		foreach ($cart_goods['goods_list'] as $goods) {
			$subtotal_number += $goods['goods_number'];
			if (isset($result['rec_goods']) && 0 < $goods['parent_id'] && $result['rec_goods'] == $goods['parent_id']) {
				if ($goods['rec_id'] != $key) {
					$result['group'][$goods['rec_id']]['rec_group'] = $goods['group_id'] . '_' . $goods['rec_id'];
					$result['group'][$goods['rec_id']]['rec_group_number'] = $goods['goods_number'];
					$result['group'][$goods['rec_id']]['rec_group_talId'] = $goods['group_id'] . '_' . $goods['rec_id'] . '_subtotal';
					$result['group'][$goods['rec_id']]['rec_group_subtotal'] = price_format($goods['goods_amount'], false);
				}
			}
		}

		$result['subtotal_number'] = $subtotal_number;

		if ($result['group']) {
			$result['group'] = array_values($result['group']);
		}

		$goods_amount = $cart_goods['total']['goods_amount'] - $fav_amount;
		$total_goods_price = price_format($goods_amount, false);
		$result['flow_info'] = insert_flow_info($total_goods_price, $cart_goods['total']['market_price'], $cart_goods['total']['saving'], $cart_goods['total']['save_rate'], $goods_amount, $cart_goods['total']['real_goods_count']);
		$act_id = isset($_POST['favourable_id']) ? intval($_POST['favourable_id']) : 0;
		$act_sel_id = !empty($_REQUEST['sel_id']) ? addslashes($_REQUEST['sel_id']) : '';
		$act_pro_sel_id = !empty($_REQUEST['pro_sel_id']) ? addslashes($_REQUEST['pro_sel_id']) : '';
		$sel_flag = !empty($_REQUEST['sel_flag']) ? addslashes($_REQUEST['sel_flag']) : '';
		$act_sel = array('act_sel_id' => $act_sel_id, 'act_pro_sel_id' => $act_pro_sel_id, 'act_sel' => $sel_flag);

		if (0 < $act_id) {
			$favourable = favourable_info($act_id);
			$favourable_available = favourable_available($favourable, $act_sel);

			if (!$favourable_available) {
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . (' AND is_gift = \'' . $act_id . '\' AND ru_id = \'') . $row['ru_id'] . '\'';
				$GLOBALS['db']->query($sql);
			}

			$cart_fav_box = cart_favourable_box($act_id, $act_sel);
			$smarty->assign('activity', $cart_fav_box);
			$smarty->assign('ru_id', $row['ru_id']);
			$result['favourable_box_content'] = $smarty->fetch('library/cart_favourable_box.lbi');
			$result['act_id'] = $act_id;
		}

		exit($json->encode($result));
	}
	else {
		$result['error'] = 100;
		$result['message'] = '';
		exit($json->encode($result));
	}
}
else if ($_REQUEST['step'] == 'ajax_cart_goods_amount') {
	require_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => 0, 'message' => '');
	get_request_filter();
	$rec_id = !empty($_REQUEST['rec_id']) ? dsc_addslashes($_REQUEST['rec_id']) : '';
	$act_sel_id = !empty($_REQUEST['sel_id']) ? dsc_addslashes($_REQUEST['sel_id']) : '';
	$sel_flag = !empty($_REQUEST['sel_flag']) ? dsc_addslashes($_REQUEST['sel_flag']) : '';
	$act_sel = array('act_sel_id' => $act_sel_id, 'act_sel' => $sel_flag);
	$ru_id = !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;
	$result['ru_id'] = $ru_id;
	$_SESSION['cart_value'] = $rec_id;
	$discount = compute_discount(3, $rec_id);
	$favour_name = empty($discount['name']) ? '' : implode(',', $discount['name']);
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$cart_goods = cart_goods($flow_type, $rec_id, 0, $region_id, $area_id);
	$goods_amount = get_cart_check_goods($cart_goods, $rec_id);
	$fav_amount = $discount['discount'];
	$save_total_amount = price_format($fav_amount + $goods_amount['save_amount']);
	$result['save_total_amount'] = $save_total_amount;
	$result['dis_amount'] = $goods_amount['save_amount'];
	$result['error'] = 0;
	$result['message'] = '';

	if ($goods_amount['subtotal_amount']) {
		$goods_amount['subtotal_amount'] = $goods_amount['subtotal_amount'] - $fav_amount;
	}
	else {
		$result['save_total_amount'] = 0;
	}

	$result['goods_amount'] = price_format($goods_amount['subtotal_amount'], false);
	$result['subtotal_number'] = $goods_amount['subtotal_number'];
	$result['discount'] = $discount['discount'];
	$is_gift = 0;
	$act_id = isset($_POST['favourable_id']) ? intval($_POST['favourable_id']) : 0;

	if ($act_id) {
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . (' AND is_gift = \'' . $act_id . '\' LIMIT 1');
		$is_gift = $GLOBALS['db']->getOne($sql);
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . (' AND is_gift = \'' . $act_id . '\'');
		$GLOBALS['db']->query($sql);
		$cart_fav_box = cart_favourable_box($act_id, $act_sel);
		$smarty->assign('activity', $cart_fav_box);
		$result['favourable_box_content'] = $smarty->fetch('library/cart_favourable_box.lbi');
	}

	$result['act_id'] = $act_id;
	$result['is_gift'] = $is_gift;
	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'update_cart') {
	if (isset($_POST['goods_number']) && is_array($_POST['goods_number'])) {
		flow_update_cart($_POST['goods_number']);
	}

	show_message($_LANG['update_cart_notice'], $_LANG['back_to_cart'], 'flow.php');
	exit();
}
else if ($_REQUEST['step'] == 'pdf') {
	$order_id = isset($_REQUEST['order']) && !empty($_REQUEST['order']) ? $_REQUEST['order'] : 0;
	get_go_index();
	get_go_index(1, $order_id);
	$consignee = get_consignee($user_id);
	$userinfo = get_user_info($consignee['user_id']);
	$consignee['user_name'] = $userinfo['user_name'];
	$consignee['country'] = !isset($consignee['country']) && empty($consignee['country']) ? 0 : intval($consignee['country']);
	$consignee['province'] = !isset($consignee['province']) && empty($consignee['province']) ? 0 : intval($consignee['province']);
	$consignee['city'] = !isset($consignee['city']) && empty($consignee['city']) ? 0 : intval($consignee['city']);
	$consignee['district'] = !isset($consignee['district']) && empty($consignee['district']) ? 0 : intval($consignee['district']);
	$consignee['street'] = !isset($consignee['street']) && empty($consignee['street']) ? 0 : intval($consignee['street']);
	$_SESSION['flow_consignee'] = $consignee;
	$smarty->assign('consignee', $consignee);
	$order_info = order_info($order_id);

	if (empty($order_info['order_id'])) {
		get_go_index(1, false);
	}
	else if ($order_info['user_id'] != $user_id) {
		get_go_index(1, false);
	}

	$order_info['add_time'] = local_date('Y-m-d H:i:s', $order_info['add_time']);

	if ($order_info['shipping_name']) {
		$shipping_list = explode(',', $order_info['shipping_name']);

		if (1 < count($shipping_list)) {
			foreach ($shipping_list as $key => $row) {
				if ($row) {
					$shipping = explode('|', $row);
					$shipping_list[$key] = $shipping[1];
				}
			}

			$shipping_list = array_unique($shipping_list);
			$order_info['shipping_name'] = implode(',', $shipping_list);
		}
	}

	$order_goods = get_order_pdf_goods($order_id);
	$shop_info = get_seller_shopinfo($order_info['ru_id']);
	ob_start();
	include './html/order_info.php';
	$content = ob_get_clean();
	require_once './html/html2pdf.class.php';
	ob_clean();

	try {
		$html2pdf = new HTML2PDF('P', 'A3', 'fr');
		$html2pdf->setDefaultFont('stsongstdlight');
		$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
		$html2pdf->Output('orderDdf_' . $order_id . '.pdf');
	}
	catch (HTML2PDF_exception $e) {
		echo $e;
		exit();
	}
}
else if ($_REQUEST['step'] == 'drop_goods') {
	if (!empty($_GET['sig'])) {
		$n_id = explode('@', $_GET['id']);

		foreach ($n_id as $val) {
			flow_drop_cart_goods($val);
		}
	}
	else {
		$rec_id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
		flow_drop_cart_goods($rec_id);
	}

	ecs_header("Location: flow.php\n");
	exit();
}
else if ($_REQUEST['step'] == 'add_favourable') {
	include_once 'includes/cls_json.php';
	$json = new JSON();
	$ru_id = !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;
	$act_sel_id = !empty($_REQUEST['sel_id']) ? addslashes($_REQUEST['sel_id']) : '';
	$sel_flag = !empty($_REQUEST['sel_flag']) ? addslashes($_REQUEST['sel_flag']) : '';
	$act_sel = array('act_sel_id' => $act_sel_id, 'act_sel' => $sel_flag);
	$select_gift = explode(',', $_POST['select_gift']);
	$act_id = intval($_POST['act_id']);
	$favourable = favourable_info($act_id);

	if (empty($favourable)) {
		$result['error'] = 1;
		$result['message'] = $_LANG['favourable_not_exist'];
		exit($json->encode($result));
	}

	if (!favourable_available($favourable)) {
		$result['error'] = 2;
		$result['message'] = $_LANG['favourable_not_available'];
		exit($json->encode($result));
	}

	$cart_favourable = cart_favourable($ru_id);

	if (favourable_used($favourable, $cart_favourable)) {
		$result['error'] = 3;
		$result['message'] = $_LANG['favourable_used'];
		exit($json->encode($result));
	}

	if ($favourable['act_type'] == FAT_GOODS) {
		if (empty($select_gift)) {
			$result['error'] = 4;
			$result['message'] = $_LANG['pls_select_gift'];
			exit($json->encode($result));
		}

		$sql = 'SELECT goods_name' . ' FROM ' . $ecs->table('cart') . ' WHERE ' . $sess_id . ' AND rec_type = \'' . CART_GENERAL_GOODS . '\'' . (' AND is_gift = \'' . $act_id . '\'') . ' AND goods_id ' . db_create_in($select_gift);
		$gift_name = $db->getCol($sql);

		if (!empty($gift_name)) {
			$result['error'] = 5;
			$result['message'] = sprintf($_LANG['gift_in_cart'], join(',', $gift_name));
			exit($json->encode($result));
		}

		$count = isset($cart_favourable[$act_id]) ? $cart_favourable[$act_id] : 0;
		if (0 < $favourable['act_type_ext'] && $favourable['act_type_ext'] < $count + count($select_gift)) {
			$result['error'] = 6;
			$result['message'] = $_LANG['gift_count_exceed'];
			exit($json->encode($result));
		}

		foreach ($favourable['gift'] as $gift) {
			if (in_array($gift['id'], $select_gift)) {
				add_gift_to_cart($act_id, $gift['id'], $gift['price'], $ru_id);
			}
		}

		$favourable_box = cart_add_favourable_box($act_id, $act_sel, $ru_id);
		$cart_value = array();

		if ($favourable_box['act_goods_list']) {
			foreach ($favourable_box['act_goods_list'] as $kk => $vv) {
				$cart_value[] = $vv['rec_id'];
			}
		}

		if ($favourable_box['act_cart_gift']) {
			foreach ($favourable_box['act_cart_gift'] as $kk => $vv) {
				$cart_value[] = $vv['rec_id'];
			}
		}

		if ($cart_value) {
			$new_cart_value = implode(',', $cart_value);

			if (!empty($new_cart_value)) {
				$_SESSION['cart_value'] = empty($_SESSION['cart_value']) ? $new_cart_value : $_SESSION['cart_value'] . ',' . $new_cart_value;
			}

			$result['cart_value'] = implode(',', $cart_value);
		}

		$result['goods_amount'] = $favourable_box['goods_amount'];
		unset($favourable_box['goods_amount']);
		$smarty->assign('activity', $favourable_box);
		$smarty->assign('ru_id', $ru_id);
		$result['content'] = $smarty->fetch('library/cart_favourable_box.lbi');
		$result['act_id'] = $act_id;
	}
	else if ($favourable['act_type'] == FAT_DISCOUNT) {
		add_favourable_to_cart($act_id, $favourable['act_name'], cart_favourable_amount($favourable) * (100 - $favourable['act_type_ext']) / 100);
	}
	else if ($favourable['act_type'] == FAT_PRICE) {
		add_favourable_to_cart($act_id, $favourable['act_name'], $favourable['act_type_ext']);
	}

	$result['ru_id'] = $ru_id;
	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'clear') {
	$sql = 'DELETE FROM ' . $ecs->table('cart') . ' WHERE ' . $sess_id;
	$db->query($sql);
	ecs_header("Location:./\n");
}
else if ($_REQUEST['step'] == 'drop_to_collect') {
	$rec_id = intval($_GET['id']);

	if (0 < $_SESSION['user_id']) {
		$goods_id = $db->getOne('SELECT  goods_id FROM ' . $ecs->table('cart') . (' WHERE rec_id = \'' . $rec_id . '\' AND ') . $sess_id);
		$count = $db->getOne('SELECT goods_id FROM ' . $ecs->table('collect_goods') . (' WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND goods_id = \'' . $goods_id . '\''));

		if (empty($count)) {
			$time = gmtime();
			$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('collect_goods') . ' (user_id, goods_id, add_time)' . ('VALUES (\'' . $_SESSION['user_id'] . '\', \'' . $goods_id . '\', \'' . $time . '\')');
			$db->query($sql);
		}
	}

	flow_drop_cart_goods($rec_id, $_REQUEST['step']);
	ecs_header("Location: flow.php\n");
	exit();
}
else if ($_REQUEST['step'] == 'validate_bonus') {
	$bonus_psd = dsc_addslashes(trim($_REQUEST['bonus_psd']));

	if (!empty($bonus_psd)) {
		$bonus = bonus_info(0, $bonus_psd, $_SESSION['cart_value']);
	}
	else {
		$bonus = array();
	}

	$bonus_kill = price_format($bonus['type_money'], false);
	include_once 'includes/cls_json.php';
	$result = array('error' => '', 'content' => '');
	$json = new JSON();
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$consignee = get_consignee($_SESSION['user_id']);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		$result['error'] = $_LANG['no_goods_in_cart'];
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		if ((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id'] || 0 < $bonus['type_money'] && empty($bonus['user_id'])) && $bonus['order_id'] <= 0) {
			$now = gmtime();

			if ($bonus['use_end_date'] < $now) {
				$order['bonus_id'] = '';
				$result['error'] = $_LANG['bonus_use_expire'];
			}
			else {
				$order['bonus_id'] = $bonus['bonus_id'];
				$order['bonus_psd'] = $bonus_psd;
			}
		}
		else {
			$order['bonus_id'] = '';
			$result['error'] = $_LANG['invalid_bonus'];
		}

		$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
		$smarty->assign('cart_goods_number', $cart_goods_number);
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['street_name'] = get_goods_region_name($consignee['street']);
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
		$smarty->assign('goods_list', $cart_goods_list);
		$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
		$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
		$smarty->assign('total', $total);

		if ($total['goods_price'] < $bonus['min_goods_amount']) {
			$order['bonus_id'] = '';
			$result['error'] = sprintf($_LANG['bonus_min_amount_error'], $bonus['min_goods_amount']);
		}

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/order_total.lbi');
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'validate_value_card') {
	$vc_psd = dsc_addslashes(trim($_REQUEST['vc_psd']));

	if (!empty($vc_psd)) {
		$value_card = value_card_info(0, $vc_psd);
	}
	else {
		$value_card = array();
	}

	include_once 'includes/cls_json.php';
	$result = array('error' => '', 'content' => '');
	$json = new JSON();
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$smarty->assign('warehouse_id', $warehouse_id);
	$smarty->assign('area_id', $area_id);
	$consignee = get_consignee($_SESSION['user_id']);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);

	if (!empty($value_card)) {
		if ($value_card['use_condition'] == 1 && !comparison_cat($cart_goods, $value_card['spec_cat'])) {
			$value_card['error'] = true;
		}

		if ($value_card['use_condition'] == 2 && !comparison_goods($cart_goods, $value_card['spec_goods'])) {
			$value_card['error'] = true;
		}
	}

	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		$result['error'] = $_LANG['no_goods_in_cart'];
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		if (!empty($value_card) && (0 < $value_card['card_money'] && $value_card['user_id'] == 0) && empty($value_card['error'])) {
			$now = gmtime();
			if ($value_card['end_time'] < $now && 0 < $value_card['end_time']) {
				$order['vc_id'] = '';
				$result['error'] = $_LANG['vc_use_expire'];
			}
			else {
				$sql = ' SELECT COUNT(*) FROM ' . $ecs->table('value_card') . (' WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND tid = \'' . $value_card['tid'] . '\' ');
				$count = $db->getOne($sql);

				if ($value_card['vc_limit'] <= $count) {
					$order['vc_id'] = '';
					$result['error'] = $_LANG['over_bind_limit'];
				}
				else {
					$order['vc_id'] = $value_card['vid'];
					$order['vc_psd'] = $vc_psd;
				}
			}
		}
		else if ($value_card['error']) {
			$result['error'] = $_LANG['vc_no_use_order'];
			$order['vid'] = '';
		}
		else {
			$order['vid'] = '';
			$result['error'] = $_LANG['vc_not_exist'];
		}

		$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
		$smarty->assign('cart_goods_number', $cart_goods_number);
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['street_name'] = get_goods_region_name($consignee['street']);
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
		$smarty->assign('goods_list', $cart_goods_list);
		$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
		$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
		$smarty->assign('total', $total);

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/order_total.lbi');
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'add_package_to_cart') {
	include_once 'includes/cls_json.php';
	$_POST['package_info'] = json_str_iconv($_POST['package_info']);
	$result = array('error' => 0, 'message' => '', 'content' => '', 'package_id' => '');
	$json = new JSON();

	if (empty($_POST['package_info'])) {
		$result['error'] = 1;
		exit($json->encode($result));
	}

	$package = $json->decode($_POST['package_info']);
	$package->type = isset($package->type) && !empty($package->type) ? $package->type : 0;

	if ($_CFG['one_step_buy'] == '1') {
		clear_cart();
	}

	if (!is_numeric($package->number) || intval($package->number) <= 0) {
		$result['error'] = 1;
		$result['message'] = $_LANG['invalid_number'];
	}
	else {
		$res = add_package_to_cart($package->package_id, $package->number, $package->warehouse_id, $package->area_id, $package->type);
		if (!is_array($res) && $res) {
			if (2 < $_CFG['cart_confirm']) {
				$result['message'] = '';
			}
			else {
				$result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
			}

			$result['content'] = insert_cart_info(4);
			$result['one_step_buy'] = $_CFG['one_step_buy'];
		}
		else {
			if (is_array($res)) {
				$result['message'] = sprintf($GLOBALS['_LANG']['package_null'], $res['goods_name']);
				$result['error'] = $res['error'];
			}
			else {
				$result['message'] = $err->last_message();
				$result['error'] = $err->error_no;
			}

			$result['package_id'] = stripslashes($package->package_id);
		}
	}

	$confirm_type = isset($package->confirm_type) ? $package->confirm_type : 0;

	if (0 < $confirm_type) {
		$result['confirm_type'] = $confirm_type;
	}
	else {
		$result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'show_gift_div') {
	include_once 'includes/cls_json.php';
	$json = new JSON();
	$favourable_id = $_POST['favourable_id'];
	$ru_id = !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;
	$act_sel_id = !empty($_REQUEST['sel_id']) ? addslashes($_REQUEST['sel_id']) : '';
	$sel_flag = !empty($_REQUEST['sel_flag']) ? addslashes($_REQUEST['sel_flag']) : '';
	$act_sel = array('act_sel_id' => $act_sel_id, 'act_sel' => $sel_flag);
	$favourable = favourable_list($_SESSION['user_rank'], -1, $favourable_id, $act_sel, $ru_id);
	$activity = $favourable[0];
	$activity['act_type_ext'] = intval($activity['act_type_ext']);
	$cart_favourable_num = cart_favourable($ru_id);
	$activity['cart_favourable_gift_num'] = !empty($cart_favourable_num[$favourable_id]) ? intval($cart_favourable_num[$favourable_id]) : 0;
	$activity['favourable_used'] = favourable_used($activity, $cart_favourable_num);
	$activity['left_gift_num'] = intval($activity['act_type_ext']) - (empty($cart_favourable_num[$activity['act_id']]) ? 0 : intval($cart_favourable_num[$activity['act_id']]));

	foreach ($activity['gift'] as $key => $row) {
		$activity['act_gift_list'][$key] = $row;
		$activity['act_gift_list'][$key]['url'] = build_uri('goods', array('gid' => $row['id']), $row['name']);
	}

	$smarty->assign('activity', $activity);
	$smarty->assign('ru_id', $ru_id);
	$smarty->assign('gift_div', 1);
	$smarty->assign('act_id', $favourable_id);
	$result['content'] = $smarty->fetch('library/cart_gift_box.lbi');
	$result['act_id'] = $favourable_id;
	$result['ru_id'] = $ru_id;
	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'edit_Consignee') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('message' => '', 'result' => '', 'qty' => 1);
	$address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;

	if ($address_id == 0) {
		$consignee['country'] = 1;
		$consignee['province'] = 0;
		$consignee['city'] = 0;
		$consignee['district'] = 0;
	}
	else {
		$consignee = get_update_flow_Consignee($address_id);
	}

	$smarty->assign('consignee', $consignee);
	$smarty->assign('country_list', get_regions());
	$smarty->assign('please_select', '请选择');
	$province_list = get_regions_log(1, $consignee['country']);
	$city_list = get_regions_log(2, $consignee['province']);
	$district_list = get_regions_log(3, $consignee['city']);
	$street_list = get_regions_log(4, $consignee['district']);
	$smarty->assign('province_list', $province_list);
	$smarty->assign('city_list', $city_list);
	$smarty->assign('district_list', $district_list);
	$smarty->assign('street_list', $street_list);
	get_goods_flow_type($_SESSION['cart_value']);

	if ($_SESSION['user_id'] <= 0) {
		$result['error'] = 2;
		$result['message'] = $_LANG['lang_crowd_not_login'];
	}
	else {
		$result['error'] = 0;
		$result['content'] = $smarty->fetch('library/consignee_new.lbi');
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'insert_Consignee') {
	include 'includes/cls_json.php';
	$time = gmtime();
	$json = new JSON();
	$result = array('message' => '', 'result' => '', 'error' => 0);
	$uc_id = isset($_REQUEST['uc_id']) ? intval($_REQUEST['uc_id']) : 0;
	$_REQUEST['csg'] = isset($_REQUEST['csg']) ? json_str_iconv($_REQUEST['csg']) : '';
	$csg = $json->decode($_REQUEST['csg']);
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$consignee = array('address_id' => empty($csg->address_id) ? 0 : intval($csg->address_id), 'consignee' => empty($csg->consignee) ? '' : compile_str(trim($csg->consignee)), 'country' => empty($csg->country) ? 0 : intval($csg->country), 'province' => empty($csg->province) ? 0 : intval($csg->province), 'city' => empty($csg->city) ? 0 : intval($csg->city), 'district' => empty($csg->district) ? 0 : intval($csg->district), 'street' => empty($csg->street) ? 0 : intval($csg->street), 'email' => empty($csg->email) ? '' : compile_str($csg->email), 'address' => empty($csg->address) ? '' : compile_str($csg->address), 'zipcode' => empty($csg->zipcode) ? '' : compile_str(make_semiangle(trim($csg->zipcode))), 'tel' => empty($csg->tel) ? '' : compile_str(make_semiangle(trim($csg->tel))), 'mobile' => empty($csg->mobile) ? '' : compile_str(make_semiangle(trim($csg->mobile))), 'sign_building' => empty($csg->sign_building) ? '' : compile_str($csg->sign_building), 'userUp_time' => $time, 'best_time' => empty($csg->best_time) ? '' : compile_str($csg->best_time));

	if ($consignee) {
		setcookie('province', $consignee['province'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('city', $consignee['city'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('district', $consignee['district'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('street', $consignee['street'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('street_area', '', gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$flow_warehouse = get_warehouse_goods_region($consignee['province']);
		setcookie('area_region', $flow_warehouse['region_id'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('flow_region', $flow_warehouse['region_id'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}

	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

	if ($result['error'] == 0) {
		if (0 < $_SESSION['user_id']) {
			$sql = 'SELECT COUNT(*) FROM' . $ecs->table('region') . 'WHERE parent_id = \'' . $consignee['city'] . '\'';
			$district_count = $db->getOne($sql);
			$sql = 'SELECT COUNT(*) FROM' . $ecs->table('region') . 'WHERE parent_id = \'' . $consignee['district'] . '\'';
			$street_count = $db->getOne($sql);
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			if ($consignee['district'] == '' && $district_count && $csg->goods_flow_type == 101) {
				$result['error'] = 4;
				$result['message'] = $_LANG['district_null'];
			}
			else {
				if ($consignee['street'] == '' && $street_count && $csg->goods_flow_type == 101) {
					$result['error'] = 4;
					$result['message'] = $_LANG['street_null'];
				}
			}

			if ($result['error'] == 0) {
				if (0 < $consignee['address_id']) {
					$addressId = ' and address_id <> \'' . $consignee['address_id'] . '\' ';
				}

				$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('user_address') . ' WHERE consignee = \'' . $consignee['consignee'] . '\'' . ' AND country = \'' . $consignee['country'] . '\'' . ' AND province = \'' . $consignee['province'] . '\'' . ' AND city = \'' . $consignee['city'] . '\'' . ' AND district = \'' . $consignee['district'] . '\'' . ' AND user_id = \'' . $_SESSION['user_id'] . '\'' . $addressId;
				$row = $db->getOne($sql);

				if (0 < $row) {
					$result['error'] = 4;
					$result['message'] = $_LANG['Distribution_exists'];
				}
				else {
					$result['error'] = 0;
					$consignee['user_id'] = $_SESSION['user_id'];
					$saveConsignee = save_consignee($consignee, true);
					$sql = 'select address_id from ' . $GLOBALS['ecs']->table('users') . ' where user_id = \'' . $_SESSION['user_id'] . '\'';
					$user_address_id = $GLOBALS['db']->getOne($sql);

					if (0 < $user_address_id) {
						$consignee['address_id'] = $user_address_id;
					}

					$sql = 'select count(*) from ' . $GLOBALS['ecs']->table('user_address') . ' where user_id = \'' . $_SESSION['user_id'] . '\'';
					$count = $GLOBALS['db']->getOne($sql);

					if ($_CFG['auditStatus'] == 1) {
						if ($count <= $_CFG['auditCount']) {
							$result['message'] = '';
						}
						else if ($saveConsignee['update'] == false) {
							if (0 < $consignee['address_id']) {
								$result['message'] = $_LANG['edit_success_one'];
							}
							else {
								$result['message'] = $_LANG['add_success_one'];
							}
						}
						else {
							$result['message'] = '';
						}
					}
					else if (0 < $consignee['address_id']) {
						$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET address_id = \'' . $consignee['address_id'] . '\' ' . ' WHERE user_id = \'' . $consignee['user_id'] . '\'';
						$GLOBALS['db']->query($sql);
						$_SESSION['flow_consignee'] = $consignee;
						$result['message'] = $_LANG['edit_success_two'];
					}
					else {
						$result['message'] = $_LANG['add_success_two'];
					}
				}
			}

			$user_address = get_order_user_address_list($_SESSION['user_id']);
			$smarty->assign('user_address', $user_address);
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['street_name'] = get_goods_region_name($consignee['street']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
			$smarty->assign('consignee', $consignee);
			$result['content'] = $smarty->fetch('library/consignee_flow.lbi');
			$region_id = get_province_id_warehouse($consignee['province']);
			$area_info = get_area_info($consignee['province']);
			$smarty->assign('warehouse_id', $region_id);
			$smarty->assign('area_id', $area_info['region_id']);
			$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $region_id, $area_info['region_id']);
			$cart_goods_list_new = cart_by_favourable($cart_goods_list);
			$cart_goods = get_new_group_cart_goods($cart_goods_list_new);
			get_goods_flow_type($_SESSION['cart_value']);
			$smarty->assign('goods_list', $cart_goods_list_new);
			$result['goods_list'] = $smarty->fetch('library/flow_cart_goods.lbi');
			$order = flow_order_info();
			$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
			$smarty->assign('cart_goods_number', $cart_goods_number);
			$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
			$coupons_info = get_coupons($uc_id, array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
			$not_freightfree = 0;
			if (!empty($coupons_info) && $cart_goods_list) {
				if ($coupons_info['cou_type'] == 5) {
					$cou_goods = array();
					$goods_amount = 0;

					foreach ($cart_goods_list as $key => $row) {
						if ($row['ru_id'] == $coupons_info['ru_id']) {
							$cou_goods = $row['goods_list'];

							foreach ($row['goods_list'] as $gkey => $grow) {
								$goods_amount += $grow['goods_price'] * $grow['goods_number'];
							}
						}
					}

					if ($coupons_info['cou_man'] <= $goods_amount || $coupons_info['cou_man'] == 0) {
						$cou_region = get_coupons_region($coupons_info['cou_id']);
						$cou_region = !empty($cou_region) ? explode(',', $cou_region) : array();
						if ($cou_region && in_array($consignee['province'], $cou_region)) {
							$not_freightfree = 1;
						}
					}
				}
			}

			$result['not_freightfree'] = $not_freightfree;
			$type = array('type' => 0, 'shipping_list' => $tmp_shipping_id_arr, 'step' => $_REQUEST['step']);
			$total = order_fee($order, $cart_goods, $consignee, $type, $_SESSION['cart_value'], 0, $cart_goods_list);
			$smarty->assign('total', $total);
			if ($_CFG['use_coupons'] == 1 && $flow_type == CART_GENERAL_GOODS) {
				$cart_ru_id = '';
				$cart_value = $_SESSION['cart_value'];

				if ($cart_value) {
					$cart_ru_id = get_cart_seller($cart_value);
				}

				$user_coupons = get_user_coupons_list($_SESSION['user_id'], true, $total['goods_price'], $cart_goods, true, $cart_ru_id, '', $consignee['province']);

				if (defined('THEME_EXTENSION')) {
					$coupons_list = get_user_coupons_list($_SESSION['user_id'], true, '', false, true, $cart_ru_id, 'cart');

					if (!empty($coupons_list)) {
						foreach ($coupons_list as $k => $v) {
							$coupons_list[$k]['cou_type_name'] = $v['cou_type'];
							$coupons_list[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
							$coupons_list[$k]['cou_type'] = $v['cou_type'] == 3 ? $_LANG['lang_goods_coupons']['all_pay'] : ($v['cou_type'] == 4 ? $_LANG['lang_goods_coupons']['user_pay'] : ($v['cou_type'] == 2 ? $_LANG['lang_goods_coupons']['goods_pay'] : ($v['cou_type'] == 1 ? $_LANG['lang_goods_coupons']['reg_pay'] : $_LANG['lang_goods_coupons']['not_pay'])));

							if ($v['spec_cat']) {
								$coupons_list[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_cate'];
							}
							else if ($v['cou_goods']) {
								$coupons_list[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_goods'];
							}
							else {
								$coupons_list[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_all'];
							}

							if (!empty($user_coupons)) {
								foreach ($user_coupons as $uk => $ur) {
									if ($v['cou_id'] == $ur['cou_id']) {
										unset($coupons_list[$k]);
										continue;
									}
								}
							}
						}

						$result['is_coupons_list'] = 1;
					}

					$smarty->assign('coupons_list', $coupons_list);
				}

				foreach ($user_coupons as $k => $v) {
					$user_coupons[$k]['cou_type_name'] = $v['cou_type'];
					$user_coupons[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
					$user_coupons[$k]['cou_type'] = $v['cou_type'] == 3 ? $_LANG['lang_goods_coupons']['all_pay'] : ($v['cou_type'] == 4 ? $_LANG['lang_goods_coupons']['user_pay'] : ($v['cou_type'] == 2 ? $_LANG['lang_goods_coupons']['goods_pay'] : ($v['cou_type'] == 1 ? $_LANG['lang_goods_coupons']['reg_pay'] : ($v['cou_type'] == 5 ? $_LANG['lang_goods_coupons']['free_pay'] : $_LANG['lang_goods_coupons']['not_pay']))));
					$user_coupons[$k]['cou_goods_name'] = $v['cou_goods'] ? $_LANG['lang_goods_coupons']['is_goods'] : $_LANG['lang_goods_coupons']['is_all'];

					if ($v['spec_cat']) {
						$user_coupons[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_cate'];
					}
					else if ($v['cou_goods']) {
						$user_coupons[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_goods'];
					}
					else {
						$user_coupons[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_all'];
					}
				}

				if (!empty($user_coupons)) {
					$result['is_user_coupons'] = 1;
				}

				$smarty->assign('user_coupons', $user_coupons);
				$result['order_coupoms_list'] = $smarty->fetch('library/order_coupoms_list.lbi');
			}

			$sc_rand = rand(1000, 9999);
			$sc_guid = sc_guid();
			$account_cookie = MD5($sc_guid . '-' . $sc_rand);
			setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
			$smarty->assign('sc_guid', $sc_guid);
			$smarty->assign('sc_rand', $sc_rand);
			$result['order_total'] = $smarty->fetch('library/order_total.lbi');
		}
		else {
			$result['error'] = 2;
			$result['message'] = $_LANG['lang_crowd_not_login'];
		}
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'delete_Consignee') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('message' => '', 'result' => '', 'qty' => 1);
	$result['error'] = 0;
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;
	$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
	$sql = 'delete from ' . $ecs->table('user_address') . (' where address_id = \'' . $address_id . '\'');
	$db->query($sql);
	$consignee = $_SESSION['flow_consignee'];
	$smarty->assign('consignee', $consignee);

	if ($consignee) {
		setcookie('province', $consignee['province'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('city', $consignee['city'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('district', $consignee['district'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('street', $consignee['street'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('street_area', '', gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$flow_warehouse = get_warehouse_goods_region($consignee['province']);
		setcookie('area_region', $flow_warehouse['region_id'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('flow_region', $flow_warehouse['region_id'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}

	$region_id = get_province_id_warehouse($consignee['province']);
	$area_info = get_area_info($consignee['province']);
	$smarty->assign('warehouse_id', $region_id);
	$smarty->assign('area_id', $area_info['region_id']);
	$sc_rand = rand(1000, 9999);
	$sc_guid = sc_guid();
	$account_cookie = MD5($sc_guid . '-' . $sc_rand);
	setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$smarty->assign('sc_guid', $sc_guid);
	$smarty->assign('sc_rand', $sc_rand);
	$user_address = get_order_user_address_list($_SESSION['user_id']);
	$smarty->assign('user_address', $user_address);
	get_goods_flow_type($_SESSION['cart_value']);

	if (!$user_address) {
		$consignee = array('province' => 0, 'city' => 0);
		$smarty->assign('country_list', get_regions());
		$smarty->assign('please_select', $_LANG['please_select']);
		$province_list = get_regions_log(1, 1);
		$city_list = get_regions_log(2, $consignee['province']);
		$district_list = get_regions_log(3, $consignee['city']);
		$smarty->assign('province_list', $province_list);
		$smarty->assign('city_list', $city_list);
		$smarty->assign('district_list', $district_list);
		$smarty->assign('consignee', $consignee);
		$result['error'] = 2;

		if ($type == 1) {
			$result['content'] = $smarty->fetch('library/consignee_flow.lbi');
		}
		else {
			$result['content'] = $smarty->fetch('library/consignee_new.lbi');
		}
	}
	else {
		$result['content'] = $smarty->fetch('library/consignee_flow.lbi');
	}

	$consignee = get_consignee($_SESSION['user_id']);
	$consignee['country'] = !isset($consignee['country']) && empty($consignee['country']) ? 0 : intval($consignee['country']);
	$consignee['province'] = !isset($consignee['province']) && empty($consignee['province']) ? 0 : intval($consignee['province']);
	$consignee['city'] = !isset($consignee['city']) && empty($consignee['city']) ? 0 : intval($consignee['city']);
	$consignee['district'] = !isset($consignee['district']) && empty($consignee['district']) ? 0 : intval($consignee['district']);
	$consignee['street'] = !isset($consignee['street']) && empty($consignee['street']) ? 0 : intval($consignee['street']);
	$consignee['address'] = !isset($consignee['address']) && empty($consignee['address']) ? '' : intval($consignee['address']);

	if (empty($consignee)) {
		$consignee = array('country' => 0, 'province' => 0, 'city' => 0, 'district' => 0, 'street' => 0, 'province_name' => '', 'city_name' => '', 'district_name' => '', 'street_name' => '', 'address' => '');
	}

	$consignee['province_name'] = get_goods_region_name($consignee['province']);
	$consignee['city_name'] = get_goods_region_name($consignee['city']);
	$consignee['district_name'] = get_goods_region_name($consignee['district']);
	$consignee['street_name'] = get_goods_region_name($consignee['street']);
	$consignee['region'] = $consignee['province_name'] . '&nbsp;' . $consignee['city_name'] . '&nbsp;' . $consignee['district_name'] . '&nbsp;' . $consignee['street_name'];
	$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
	$_SESSION['flow_consignee'] = $consignee;
	$smarty->assign('consignee', $consignee);
	$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $region_id, $area_info['region_id']);
	$cart_goods_list_new = cart_by_favourable($cart_goods_list);
	$smarty->assign('goods_list', $cart_goods_list_new);
	$cart_goods = get_new_group_cart_goods($cart_goods_list_new);
	get_goods_flow_type($_SESSION['cart_value']);
	$result['goods_list'] = $smarty->fetch('library/flow_cart_goods.lbi');
	$order = flow_order_info();
	$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
	$smarty->assign('cart_goods_number', $cart_goods_number);
	$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
	$smarty->assign('total', $total);
	$result['order_total'] = $smarty->fetch('library/order_total.lbi');
	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'edit_consignee_checked') {
	include 'includes/cls_json.php';
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$_SESSION['cart_value'] = isset($_SESSION['cart_value']) ? $_SESSION['cart_value'] : '';
	$json = new JSON();
	$res = array('msg' => '', 'result' => '', 'qty' => 1);
	$result['error'] = 0;
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$uc_id = isset($_REQUEST['uc_id']) ? intval($_REQUEST['uc_id']) : 0;
	$address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;
	$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
	$store_seller = 0 < $store_id ? 'store_seller' : '';
	$smarty->assign('store_seller', $store_seller);
	$_SESSION['merchants_shipping'] = array();
	$consignee = get_update_flow_Consignee($address_id);
	$consignee['country'] = !isset($consignee['country']) && empty($consignee['country']) ? 0 : intval($consignee['country']);
	$consignee['province'] = !isset($consignee['province']) && empty($consignee['province']) ? 0 : intval($consignee['province']);
	$consignee['city'] = !isset($consignee['city']) && empty($consignee['city']) ? 0 : intval($consignee['city']);
	$consignee['district'] = !isset($consignee['district']) && empty($consignee['district']) ? 0 : intval($consignee['district']);
	$consignee['street'] = !isset($consignee['street']) && empty($consignee['street']) ? 0 : intval($consignee['street']);
	$_SESSION['flow_consignee'] = $consignee;

	if ($consignee) {
		setcookie('province', $consignee['province'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('city', $consignee['city'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('district', $consignee['district'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('street', $consignee['street'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('street_area', '', gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$flow_warehouse = get_warehouse_goods_region($consignee['province']);
		setcookie('area_region', $flow_warehouse['region_id'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('flow_region', $flow_warehouse['region_id'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}

	$region_id = get_province_id_warehouse($consignee['province']);
	$area_info = get_area_info($consignee['province']);
	$smarty->assign('warehouse_id', $region_id);
	$smarty->assign('area_id', $area_info['region_id']);
	$smarty->assign('store_id', $store_id);
	$consignee['province_name'] = get_goods_region_name($consignee['province']);
	$consignee['city_name'] = get_goods_region_name($consignee['city']);
	$consignee['district_name'] = get_goods_region_name($consignee['district']);
	$consignee['street_name'] = get_goods_region_name($consignee['street']);
	$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
	$smarty->assign('consignee', $consignee);
	$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
	$smarty->assign('cart_goods_number', $cart_goods_number);
	$user_address = get_order_user_address_list($_SESSION['user_id']);
	if (!$user_address && $consignee) {
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['street_name'] = get_goods_region_name($consignee['street']);
		$consignee['region'] = $consignee['province_name'] . '&nbsp;' . $consignee['city_name'] . '&nbsp;' . $consignee['district_name'] . '&nbsp;' . $consignee['street_name'];
		$user_address = array($consignee);
	}

	$smarty->assign('user_address', $user_address);
	$result['content'] = $smarty->fetch('library/consignee_flow.lbi');
	$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $region_id, $area_info['region_id'], '', $store_id);
	$cart_goods_list_new = cart_by_favourable($cart_goods_list);
	$smarty->assign('goods_list', $cart_goods_list_new);
	$cart_goods = get_new_group_cart_goods($cart_goods_list_new);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		if (empty($cart_goods)) {
			$result['error'] = 1;
			$result['msg'] = $_LANG['cart_or_login_not'];
		}
		else if (!check_consignee_info($consignee, $flow_type)) {
			$result['error'] = 2;
			$result['msg'] = $_LANG['address_Prompt'];
		}
	}
	else {
		$region_id = get_province_id_warehouse($consignee['province']);
		$area_info = get_area_info($consignee['province']);
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
		$coupons_info = get_coupons($uc_id, array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
		$not_freightfree = 0;
		if (!empty($coupons_info) && $cart_goods_list) {
			if ($coupons_info['cou_type'] == 5) {
				$cou_goods = array();
				$goods_amount = 0;

				foreach ($cart_goods_list as $key => $row) {
					if ($row['ru_id'] == $coupons_info['ru_id']) {
						$cou_goods = $row['goods_list'];

						foreach ($row['goods_list'] as $gkey => $grow) {
							$goods_amount += $grow['goods_price'] * $grow['goods_number'];
						}
					}
				}

				if ($coupons_info['cou_man'] <= $goods_amount || $coupons_info['cou_man'] == 0) {
					$cou_region = get_coupons_region($coupons_info['cou_id']);
					$cou_region = !empty($cou_region) ? explode(',', $cou_region) : array();
					if ($cou_region && in_array($consignee['province'], $cou_region)) {
						$not_freightfree = 1;
					}
				}
			}
		}

		$result['not_freightfree'] = $not_freightfree;
		$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
		$smarty->assign('total', $total);
		if ($_CFG['use_coupons'] == 1 && $flow_type == CART_GENERAL_GOODS) {
			$cart_ru_id = '';
			$cart_value = $_SESSION['cart_value'];

			if ($cart_value) {
				$cart_ru_id = get_cart_seller($cart_value);
			}

			$user_coupons = get_user_coupons_list($_SESSION['user_id'], true, $total['goods_price'], $cart_goods, true, $cart_ru_id, '', $consignee['province']);

			if (defined('THEME_EXTENSION')) {
				$coupons_list = get_user_coupons_list($_SESSION['user_id'], true, '', false, true, $cart_ru_id, 'cart');

				if (!empty($coupons_list)) {
					foreach ($coupons_list as $k => $v) {
						$coupons_list[$k]['cou_type_name'] = $v['cou_type'];
						$coupons_list[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
						$coupons_list[$k]['cou_type'] = $v['cou_type'] == 3 ? $_LANG['lang_goods_coupons']['all_pay'] : ($v['cou_type'] == 4 ? $_LANG['lang_goods_coupons']['user_pay'] : ($v['cou_type'] == 2 ? $_LANG['lang_goods_coupons']['goods_pay'] : ($v['cou_type'] == 1 ? $_LANG['lang_goods_coupons']['reg_pay'] : $_LANG['lang_goods_coupons']['not_pay'])));

						if ($v['spec_cat']) {
							$coupons_list[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_cate'];
						}
						else if ($v['cou_goods']) {
							$coupons_list[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_goods'];
						}
						else {
							$coupons_list[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_all'];
						}

						if (!empty($user_coupons)) {
							foreach ($user_coupons as $uk => $ur) {
								if ($v['cou_id'] == $ur['cou_id']) {
									unset($coupons_list[$k]);
									continue;
								}
							}
						}
					}

					$result['is_coupons_list'] = 1;
				}

				$smarty->assign('coupons_list', $coupons_list);
			}

			foreach ($user_coupons as $k => $v) {
				$user_coupons[$k]['cou_type_name'] = $v['cou_type'];
				$user_coupons[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
				$user_coupons[$k]['cou_type'] = $v['cou_type'] == 3 ? $_LANG['lang_goods_coupons']['all_pay'] : ($v['cou_type'] == 4 ? $_LANG['lang_goods_coupons']['user_pay'] : ($v['cou_type'] == 2 ? $_LANG['lang_goods_coupons']['goods_pay'] : ($v['cou_type'] == 1 ? $_LANG['lang_goods_coupons']['reg_pay'] : ($v['cou_type'] == 5 ? $_LANG['lang_goods_coupons']['free_pay'] : $_LANG['lang_goods_coupons']['not_pay']))));
				$user_coupons[$k]['cou_goods_name'] = $v['cou_goods'] ? $_LANG['lang_goods_coupons']['is_goods'] : $_LANG['lang_goods_coupons']['is_all'];

				if ($v['spec_cat']) {
					$user_coupons[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_cate'];
				}
				else if ($v['cou_goods']) {
					$user_coupons[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_goods'];
				}
				else {
					$user_coupons[$k]['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_all'];
				}
			}

			if (!empty($user_coupons)) {
				$result['is_user_coupons'] = 1;
			}

			$smarty->assign('user_coupons', $user_coupons);
			$result['order_coupoms_list'] = $smarty->fetch('library/order_coupoms_list.lbi');
		}

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		get_goods_flow_type($_SESSION['cart_value']);
		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['goods_list'] = $smarty->fetch('library/flow_cart_goods.lbi');
		$result['order_total'] = $smarty->fetch('library/order_total.lbi');
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'edit_offline_store') {
	include 'includes/cls_json.php';
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$cart_value = isset($_REQUEST['cart_value']) && !empty($_REQUEST['cart_value']) ? addslashes($_REQUEST['cart_value']) : $_SESSION['cart_value'];
	$json = new JSON();
	$res = array('msg' => '', 'result' => '', 'qty' => 1);
	$result['error'] = 0;
	$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
	$store_seller = 0 < $store_id ? 'store_seller' : '';
	$smarty->assign('store_seller', $store_seller);
	$smarty->assign('store_id', $store_id);

	if (0 < $store_id) {
		$sql = 'UPDATE' . $ecs->table('cart') . ('SET store_id = \'' . $store_id . '\' WHERE rec_id ') . db_create_in($_SESSION['cart_value']);
		$db->query($sql);
	}

	$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $region_id, $area_info['region_id'], '', $store_id);
	$cart_goods_list_new = cart_by_favourable($cart_goods_list);
	$smarty->assign('goods_list', $cart_goods_list_new);
	$cart_goods = get_new_group_cart_goods($cart_goods_list_new);
	$cart_goods_number = get_buy_cart_goods_number($flow_type, $cart_value);
	$smarty->assign('cart_goods_number', $cart_goods_number);

	if (empty($cart_goods)) {
		$result['error'] = 1;
		$result['msg'] = $_LANG['cart_or_login_not'];
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		$total = order_fee($order, $cart_goods, '', 0, $cart_value, 0, $cart_goods_list, 0, 0, $store_id);
		$smarty->assign('total', $total);

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$smarty->assign('is_exchange_goods', 1);
		}

		get_goods_flow_type($cart_value);
		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['goods_list'] = $smarty->fetch('library/flow_cart_goods.lbi');
		$result['order_total'] = $smarty->fetch('library/order_total.lbi');
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'order_reload') {
	include_once 'includes/lib_clips.php';
	include_once 'includes/lib_payment.php';
	$order_id = isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
	$order_info = $_SESSION['order_reload'][$user_id];

	if (0 < $order_id) {
		$order_info['order_id'] = $order_id;
		$_SESSION['order_reload'][$user_id]['order_id'] = $order_id;
	}

	$order = $db->getRow('SELECT * FROM ' . $ecs->table('order_info') . ' WHERE order_id=\'' . $order_info['order_id'] . '\' LIMIT 1');
	$order['log_id'] = $GLOBALS['db']->getOne(' SELECT log_id FROM ' . $GLOBALS['ecs']->table('pay_log') . ' WHERE order_id = \'' . $order_info['order_id'] . '\' LIMIT 1 ');

	if (0 < $order['order_amount']) {
		$onlinepay_pay_id = $db->getOne('SELECT pay_id FROM ' . $ecs->table('payment') . ' WHERE pay_code=\'onlinepay\'');

		if ($order['pay_id'] == $onlinepay_pay_id) {
			$baitiao_balance = get_baitiao_balance($user_id);
			$payment_list = available_payment_list(0, $cod_fee);

			foreach ($payment_list as $k => $v) {
				if ($v['is_online'] == 1) {
					include_once 'includes/modules/payment/' . $v['pay_code'] . '.php';

					if (class_exists($v['pay_code'])) {
						$pay_obj = new $v['pay_code']();
						$payment = payment_info($v['pay_id']);
						$pay_online_button[$v['pay_code']] = "\r\n      " . $pay_obj->get_code($order, unserialize_config($v['pay_config']));

						if ($v['pay_code'] == 'alipay_bank') {
							$smarty->assign('is_alipay_bank', $pay_online_button['alipay_bank']);
							unset($pay_online_button['alipay_bank']);
						}

						if ($v['pay_code'] == 'balance') {
							$pay_online_button['balance'] = '                    <a href="flow.php?step=done&act=balance&order_sn=' . $order['order_sn'] . '" id="balance" order_sn="' . $order['order_sn'] . '" flag="balance" >' . $_LANG['balance_pay'] . '</a>';
						}

						if ($baitiao_balance && 0 < $baitiao_balance['balance']) {
							$smarty->assign('is_chunsejinrong', true);

							if ($v['pay_code'] == 'chunsejinrong') {
								$pay_online_button['chunsejinrong'] = '                            <a href="flow.php?step=done&act=chunsejinrong&order_sn=' . $order['order_sn'] . '" id="chunsejinrong" style="height:36px; line-height:36px; float: left;" order_sn="' . $order['order_sn'] . '" flag="chunsejinrong" >' . $_LANG['ious_pay'] . '</a>';
							}
						}
					}
				}
			}

			$smarty->assign('pay_online_button', $pay_online_button);
			$smarty->assign('is_onlinepay', true);

			if ($_SESSION['flow_type'] == 5) {
				$smarty->assign('is_presale_goods', true);
			}
		}
		else {
			$payment = payment_info($order['pay_id']);
			include_once 'includes/modules/payment/' . $payment['pay_code'] . '.php';
			$pay_obj = new $payment['pay_code']();
			$pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));
			$order['pay_desc'] = $payment['pay_desc'];
		}

		$smarty->assign('pay_online', $pay_online);
	}
	else {
		$payment = payment_info($order['pay_id']);
		$order['pay_code'] = $payment['pay_code'];
	}

	$order['format_shipping_fee'] = price_format($order['shipping_fee']);
	$order['format_order_amount'] = price_format($order['order_amount']);

	if (isset($order_info['stages_qishu'])) {
		$order_info['baitiao'] = isset($baitiao_balance['balance']) && !empty($baitiao_balance['balance']) ? $baitiao_balance['balance'] : 0;
		$smarty->assign('stages_info', $order_info);
	}

	$goods_buy_list = get_order_goods_buy_list($region_id, $area_id);
	$smarty->assign('goods_buy_list', $goods_buy_list);

	if (defined('THEME_EXTENSION')) {
		$region = array('province' => $order['province'], 'city' => $order['city'], 'district' => $order['district'], 'street' => $order['street']);
		$address_info = get_area_region_info($region);
		$smarty->assign('address_info', $address_info);
		$order['region'] = $address_info;
	}

	$smarty->assign('order', $order);
	$sql = 'SELECT count(*) FROM ' . $ecs->table('order_info') . ' WHERE main_order_id = \'' . $order['order_id'] . '\'';
	$child_order = $db->getOne($sql, true);

	if (1 < $child_order) {
		$child_order_info = get_child_order_info($order['order_id']);
		$smarty->assign('child_order_info', $child_order_info);
	}

	$smarty->assign('child_order', $child_order);
}
else if ($_REQUEST['step'] == 'pay_success') {
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

	if ($flow_type == CART_GROUP_BUY_GOODS) {
		$smarty->assign('is_group_buy', 1);
	}

	$order_id = intval(trim($_GET['order_id']));
	$order_status = $db->getOne('SELECT pay_status FROM ' . $ecs->table('order_info') . (' WHERE order_id=\'' . $order_id . '\''));
	if ($order_status != 2 && $order_status != 3) {
		ecs_header("Location: index.php\n");
		exit();
	}

	$store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;

	if (0 < $store_id) {
		$sql = 'SELECT stores_name,id FROM' . $ecs->table('offline_store') . (' WHERE id = \'' . $store_id . '\'');
		$smarty->assign('stores_info', $db->getRow($sql));
	}

	$order = get_main_order_info($order_id);
	$order['order_amount'] = $order['money_paid'] + $order['surplus'];
	$sql = 'SELECT COUNT(order_id) AS child_num,money_paid,surplus FROM ' . $ecs->table('order_info') . (' WHERE main_order_id =\'' . $order_id . '\'');
	$child_order = $db->getOne($sql);

	if (1 < $child_order) {
		$sql = 'SELECT money_paid,consignee,mobile,surplus,order_sn,order_id,shipping_name,shipping_fee,order_amount FROM ' . $ecs->table('order_info') . (' WHERE main_order_id =\'' . $order_id . '\'');
		$child_order_info = $db->getAll($sql);

		foreach ($child_order_info as $k => $v) {
			$child_order_info[$k]['order_amount'] = price_format($v['money_paid'] + $v['surplus']);
		}

		$smarty->assign('child_order_info', $child_order_info);
	}

	if (defined('THEME_EXTENSION')) {
		$region = array('province' => $order['province'], 'city' => $order['city'], 'district' => $order['district'], 'street' => $order['street']);
		$address_info = get_area_region_info($region);
		$smarty->assign('address_info', $address_info);
		$region_id = get_province_id_warehouse($address_info['province']);
		$goods_buy_list = get_order_goods_buy_list($region_id, $area_id);
		$smarty->assign('goods_buy_list', $goods_buy_list);
	}

	$smarty->assign('child_order', $child_order);
	$smarty->assign('order', $order);
	$smarty->assign('is_zc_order', $order['is_zc_order']);
	$smarty->assign('pay_success', true);
}
else if ($_REQUEST['act'] == 'check_cart_goods') {
	require_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => 0, 'message' => '');

	if (isset($_POST['rec_id'])) {
		$rec_id = !empty($_POST['rec_id']) ? trim($_POST['rec_id']) : 0;
		$sql = 'SELECT goods_id, goods_name, goods_number, extension_code, is_invalid ' . ' FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE rec_id ' . db_create_in($rec_id);
		$res = $GLOBALS['db']->getAll($sql);

		if ($res) {
			foreach ($res as $val) {
				if (empty($val['is_invalid'])) {
					$nowTime = gmtime();
					$xiangouInfo = get_purchasing_goods_info($val['goods_id']);
					$start_date = $xiangouInfo['xiangou_start_date'];
					$end_date = $xiangouInfo['xiangou_end_date'];
					if ($xiangouInfo['is_xiangou'] == 1 && $start_date < $nowTime && $nowTime < $end_date) {
						$user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
						$orderGoods = get_for_purchasing_goods($start_date, $end_date, $val['goods_id'], $user_id);

						if ($xiangouInfo['xiangou_num'] <= $orderGoods['goods_number']) {
							$max_num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
							$result['message'] = sprintf($_LANG['purchase_Prompt'], $val['goods_name']);
							$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number = 0 WHERE rec_id=\'' . $rec_id . '\'');
							$GLOBALS['db']->query($sql);
							$result['error'] = 1;
							exit($json->encode($result));
						}
						else if (0 < $xiangouInfo['xiangou_num']) {
							if ($xiangouInfo['is_xiangou'] == 1 && $xiangouInfo['xiangou_num'] < $orderGoods['goods_number'] + $val['goods_number']) {
								$max_num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
								$result['message'] = sprintf($_LANG['purchasing_prompt'], $val['goods_name']);
								$cart_Num = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
								$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . (' SET goods_number = \'' . $cart_Num . '\' WHERE rec_id=\'' . $rec_id . '\'');
								$GLOBALS['db']->query($sql);
								$result['error'] = 1;
								$result['cart_Num'] = $cart_Num;
								$result['rec_id'] = $key;
								exit($json->encode($result));
							}
						}
					}

					if ($val['extension_code'] == 'package_buy') {
						if ($GLOBALS['_CFG']['use_storage'] == '1' && judge_package_stock($val['goods_id'], $val['goods_number'])) {
							$result['error'] = 1;
							$result['message'] = sprintf('该 %s 礼包库存不足，无法再购买', $val['goods_name']);
							$result['rec_id'] = $key;
							exit($json->encode($result));
						}
					}
				}
				else {
					$result['error'] = 1;
					$result['message'] = sprintf('该 %s 商品已下架或商品不存在', $val['goods_name']);
					$result['rec_id'] = $key;
				}
			}
		}

		exit($json->encode($result));
	}
}
else {
	$_SESSION['flow_type'] = CART_GENERAL_GOODS;

	if ($_CFG['one_step_buy'] == '1') {
		ecs_header("Location: flow.php?step=checkout\n");
		exit();
	}

	$smarty->assign('area_id', $area_id);
	$smarty->assign('flow_region', $_COOKIE['flow_region']);

	if (!$_COOKIE['flow_region']) {
		$_COOKIE['flow_region'] = $_COOKIE['area_region'];
	}

	$cart_goods = get_cart_goods('', 1, $_COOKIE['flow_region'], $area_id);
	$merchant_goods = $cart_goods['goods_list'];
	$merchant_goods_list = cart_by_favourable($merchant_goods);
	$smarty->assign('goods_list', $merchant_goods_list);
	$smarty->assign('total', $cart_goods['total']);
	$smarty->assign('shopping_money', sprintf($_LANG['shopping_money'], $cart_goods['total']['goods_price']));
	$smarty->assign('market_price_desc', sprintf($_LANG['than_market_price'], $cart_goods['total']['market_price'], $cart_goods['total']['saving'], $cart_goods['total']['save_rate']));
	$discount = compute_discount();
	$smarty->assign('discount', $discount['discount']);
	$favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
	$smarty->assign('your_discount', sprintf($_LANG['your_discount'], $favour_name, price_format($discount['discount'])));
	$smarty->assign('show_goods_thumb', $GLOBALS['_CFG']['show_goods_in_cart']);
	$smarty->assign('show_goods_attribute', $GLOBALS['_CFG']['show_attr_in_cart']);
	$sql = 'SELECT goods_id ' . 'FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . 'AND rec_type = \'' . CART_GENERAL_GOODS . '\' ' . 'AND is_gift = 0 ' . 'AND extension_code <> \'package_buy\' ' . 'AND parent_id = 0 ';
	$parent_list = $GLOBALS['db']->getCol($sql);
	$fittings_list = get_goods_fittings($parent_list);
	$smarty->assign('fittings_list', $fittings_list);
	$guess_goods = get_guess_goods($_SESSION['usre_id'], 1, 1, 18, $region_id, $area_id);
	$best_goods = get_recommend_goods('best', '', $region_id, $area_id);
	$smarty->assign('guess_goods', $guess_goods);
	$smarty->assign('guessGoods_count', count($guess_goods));
	$smarty->assign('best_goods', $best_goods);
	$smarty->assign('bestGoods_count', count($best_goods));
	$smarty->assign('province_row', get_region_info($province_id));
	$smarty->assign('city_row', get_region_info($city_id));
	$smarty->assign('district_row', get_region_info($district_id));
	$province_list = get_warehouse_province();
	$city_list = get_region_city_county($province_id);
	$district_list = get_region_city_county($city_id);

	foreach ($province_list as $k => $v) {
		$province_list[$k]['choosable'] = true;
	}

	foreach ($city_list as $k => $v) {
		$city_list[$k]['choosable'] = true;
	}

	foreach ($district_list as $k => $v) {
		$district_list[$k]['choosable'] = true;
	}

	$smarty->assign('province_list', $province_list);
	$smarty->assign('city_list', $city_list);
	$smarty->assign('district_list', $district_list);
}

$history_goods = get_history_goods(0, $region_id, $area_id, $area_city);
$smarty->assign('history_goods', $history_goods);
$smarty->assign('historyGoods_count', count($history_goods));
$smarty->assign('currency_format', $_CFG['currency_format']);
$smarty->assign('integral_scale', price_format($_CFG['integral_scale']));
$smarty->assign('step', $_REQUEST['step']);
assign_dynamic('shopping_flow');
$smarty->display('flow.dwt');

if (@$_REQUEST['act'] == 'onlinepay_edit') {
	$sql = 'SELECT * FROM ' . $ecs->table('payment') . ' WHERE pay_code=\'' . $_GET['onlinepay_type'] . '\'';
	$res = $db->getRow($sql);
	$sql = 'UPDATE ' . $ecs->table('order_info') . ' set pay_id=\'' . $res['pay_id'] . '\',pay_name=\'' . $res['pay_name'] . '\' WHERE order_sn = \'' . $_GET['order_sn'] . '\'';
	$db->query($sql);
}

?>
