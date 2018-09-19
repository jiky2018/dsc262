<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function return_url($code)
{
	$url = $GLOBALS['ecs']->url();
	$self = explode('/', substr(PHP_SELF, 1));
	$count = count($self);

	if (1 < $count) {
		$real_path = $self[$count - 2];

		if ($real_path == SELLER_PATH) {
			$str_len = 0 - (str_len(SELLER_PATH) + 1);
			$url = substr($GLOBALS['ecs']->url(), 0, $str_len);
		}
	}

	return $url . 'respond.php?code=' . $code;
}

function notify_url($code)
{
	$url = $GLOBALS['ecs']->url();
	$self = explode('/', substr(PHP_SELF, 1));
	$count = count($self);

	if (1 < $count) {
		$real_path = $self[$count - 2];

		if ($real_path == SELLER_PATH) {
			$str_len = 0 - (str_len(SELLER_PATH) + 1);
			$url = substr($GLOBALS['ecs']->url(), 0, $str_len);
		}
	}

	return $url . 'api/notify/' . $code . '.php';
}


function get_order_id_by_sn($order_sn, $voucher = 'false')
{
	if ($voucher == 'true') {
		if (is_numeric($order_sn)) {
			return $GLOBALS['db']->getOne('SELECT log_id FROM ' . $GLOBALS['ecs']->table('pay_log') . ' WHERE order_id=' . $order_sn . ' AND order_type=1');
		}
		else {
			return '';
		}
	}
	else {
		if (is_numeric($order_sn)) {
			$sql = 'SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_sn = \'' . $order_sn . '\'');
			$order_id = $GLOBALS['db']->getOne($sql);
		}

		if (!empty($order_id)) {
			$pay_log_id = $GLOBALS['db']->getOne('SELECT log_id FROM ' . $GLOBALS['ecs']->table('pay_log') . ' WHERE order_id=\'' . $order_id . '\'');
			return $pay_log_id;
		}
		else {
			return '';
		}
	}
}

function get_goods_name_by_id($order_id)
{
	$sql = 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\'');
	$goods_name = $GLOBALS['db']->getCol($sql);
	return implode(',', $goods_name);
}

function check_money($log_id, $money)
{
	if (is_numeric($log_id)) {
		$sql = 'SELECT order_id, order_amount FROM ' . $GLOBALS['ecs']->table('pay_log') . (' WHERE log_id = \'' . $log_id . '\' LIMIT 1');
		$pay = $GLOBALS['db']->getRow($sql);
		$pay['order_id'] = isset($pay['order_id']) ? $pay['order_id'] : 0;
		$pay['order_amount'] = isset($pay['order_amount']) ? $pay['order_amount'] : 0;
		$sql = 'SELECT order_id, order_amount, surplus FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id IN (' . $pay['order_id'] . ') LIMIT 1';
		$order = $GLOBALS['db']->getRow($sql);
		$order['order_amount'] = isset($order['order_amount']) ? $order['order_amount'] : 0;
		$order['surplus'] = isset($order['surplus']) ? $order['surplus'] : 0;

		if (0 < $order['surplus']) {
			$amount = $order['order_amount'];
		}
		else {
			$amount = $pay['order_amount'];
		}
	}
	else {
		return false;
	}

	if ($money == $amount) {
		return true;
	}
	else {
		return false;
	}
}

function order_paid($log_id, $pay_status = PS_PAYED, $note = '', $order_sn = '')
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('pay_log') . (' WHERE log_id = \'' . $log_id . '\'');
	$pay_log = $GLOBALS['db']->getRow($sql);
	$pay_order = array();
	if (!empty($order_sn) && $pay_log['order_type'] == PAY_ORDER) {
		$sql = 'SELECT ifnull(bai.is_stages, 0) is_stages, o.order_id, order_sn FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('baitiao_log') . ' AS bai ON o.order_id = bai.order_id ' . ('WHERE order_sn = \'' . $order_sn . '\' LIMIT  1');
		$pay_order = $GLOBALS['db']->getRow($sql);
	}

	if (!empty($order_sn) && $pay_order && $pay_order['is_stages'] == 1) {
		$bt_time = gmtime();
		$where_other = array('id' => $log_id);
		$log_info = get_baitiao_pay_log_info($where_other);
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('baitiao_pay_log') . (' SET is_pay = 1, pay_time = \'' . $bt_time . '\' ') . (' WHERE id = \'' . $log_id . '\'');
		$GLOBALS['db']->query($sql);
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('stages') . (' SET yes_num = yes_num + 1, repay_date = \'' . $bt_time . '\' WHERE order_sn = \'' . $order_sn . '\'');
		$GLOBALS['db']->query($sql);
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('baitiao_log') . (' SET yes_num = yes_num + 1, repayed_date = \'' . $bt_time . '\' WHERE log_id = \'') . $log_info['log_id'] . '\'';
		$GLOBALS['db']->query($sql);
		$baitiao_log_other = array('log_id' => $log_info['log_id']);
		$baitiao_log_info = get_baitiao_log_info($baitiao_log_other);
		if ($baitiao_log_info['stages_total'] == $baitiao_log_info['yes_num'] && $baitiao_log_info['is_repay'] == 0) {
			$GLOBALS['db']->query('UPDATE ' . $GLOBALS['ecs']->table('baitiao_log') . ' SET is_repay = 1 WHERE log_id = \'' . $log_info['log_id'] . '\'');
		}
	}
	else {
		$log_id = intval($log_id);

		if (0 < $log_id) {
			$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('pay_log') . (' WHERE log_id = \'' . $log_id . '\'');
			$pay_log = $GLOBALS['db']->getRow($sql);
			if ($pay_log && $pay_log['is_paid'] == 0) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_log') . (' SET is_paid = \'1\' WHERE log_id = \'' . $log_id . '\'');
				$GLOBALS['db']->query($sql);

				if ($pay_log['order_type'] == PAY_ORDER) {
					$order_id_arr = explode(',', $pay_log['order_id']);

					foreach ($order_id_arr as $o_key => $o_val) {
						$sql = 'SELECT main_order_id, order_id, user_id, order_sn, consignee, address, tel, mobile, shipping_id, pay_status, extension_code, extension_id, goods_amount, ' . 'shipping_fee, insure_fee, pay_fee, tax, pack_fee, card_fee, surplus, money_paid, integral, integral_money, bonus, order_amount, discount, pay_id, shipping_status ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $o_val . '\' LIMIT 1');
						$order = $GLOBALS['db']->getRow($sql);
						$main_order_id = $order['main_order_id'];
						$order_id = $order['order_id'];
						$order_sn = $order['order_sn'];
						$pay_fee = order_pay_fee($order['pay_id'], $pay_log['order_amount']);

						if (0 < $order_id) {
							update_zc_project($order_id);
						}

						if ($order['extension_code'] == 'presale') {
							$money_paid = $order['money_paid'] + $order['order_amount'];

							if ($order['pay_status'] == 0) {
								$order_amount = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['pay_fee'] + $order['tax'] - $order['money_paid'] - $order['order_amount'];
								$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET order_status = \'' . OS_CONFIRMED . '\', ' . ' confirm_time = \'' . gmtime() . '\', ' . ' pay_status = \'' . PS_PAYED_PART . '\', ' . ' pay_time = \'' . gmtime() . '\', ' . (' pay_fee = \'' . $pay_fee . '\', ') . (' money_paid = \'' . $money_paid . '\',') . (' order_amount = \'' . $order_amount . '\' ') . ('WHERE order_id = \'' . $order_id . '\'');
								$GLOBALS['db']->query($sql);

								if (0 < $order_id) {
									order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED_PART, $note, $GLOBALS['_LANG']['buyer']);
									update_pay_log($order_id);
								}
							}
							else {
								$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET pay_status = \'' . PS_PAYED . '\', ' . ' pay_time = \'' . gmtime() . '\', ' . (' pay_fee = \'' . $pay_fee . '\', ') . (' money_paid = \'' . $money_paid . '\',') . ' order_amount = 0 ' . ('WHERE order_id = \'' . $order_id . '\'');
								$GLOBALS['db']->query($sql);

								if ($order_id) {
									order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $note, $GLOBALS['_LANG']['buyer']);
									get_presale_num($order_id);
								}
							}
						}
						else if (0 < $order_id) {
							$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET order_status = \'' . OS_CONFIRMED . '\', ' . ' confirm_time = \'' . gmtime() . '\', ' . (' pay_status = \'' . $pay_status . '\', ') . (' pay_fee = \'' . $pay_fee . '\', ') . ' pay_time = \'' . gmtime() . '\', ' . ' money_paid = money_paid + order_amount,' . ' order_amount = 0 ' . ('WHERE order_id = \'' . $order_id . '\'');
							$GLOBALS['db']->query($sql);
							create_snapshot($order_id);
							$sql = 'SELECT store_id FROM ' . $GLOBALS['ecs']->table('store_order') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
							$store_id = $GLOBALS['db']->getOne($sql);
							if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PAID && $order['order_amount'] <= 0) {
								change_order_goods_storage($order_id, true, SDT_PAID, $_CFG['stock_dec_time'], 0, $store_id);
							}

							check_main_order_status($order_id);
							order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);
							$order_sale = array('order_id' => $order_id, 'pay_status' => $pay_status, 'shipping_status' => $order['shipping_status']);
							get_goods_sale($order_id, $order_sale);
						}

						$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE main_order_id = \'' . $order_id . '\'');
						$child_num = $GLOBALS['db']->getOne($sql);
						if ($main_order_id == 0 && 0 < $child_num && 0 < $order_id) {
							$sql = 'SELECT order_id, order_sn, pay_id, order_amount ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE main_order_id = \'' . $order_id . '\'');
							$order_res = $GLOBALS['db']->getAll($sql);

							foreach ($order_res as $row) {
								$child_pay_fee = order_pay_fee($row['pay_id'], $row['order_amount']);
								$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET order_status = \'' . OS_CONFIRMED . '\', ' . ' confirm_time = \'' . gmtime() . '\', ' . (' pay_status = \'' . $pay_status . '\', ') . ' pay_time = \'' . gmtime() . '\', ' . (' pay_fee = \'' . $child_pay_fee . '\', ') . ' money_paid = order_amount,' . ' order_amount = 0 ' . 'WHERE order_id = \'' . $row['order_id'] . '\'';
								$GLOBALS['db']->query($sql);

								if ($pay_status == PS_PAYED) {
									cloud_confirmorder($row['order_id']);
								}

								order_action($row['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);
							}
						}

						$sql = 'SELECT ru_id, stages_qishu FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
						$order_goods = $GLOBALS['db']->getRow($sql);
						$ru_id = $order_goods['ru_id'];
						$stages_qishu = $order_goods['stages_qishu'];
						$shop_name = get_shop_name($ru_id, 1);

						if ($ru_id == 0) {
							$sms_shop_mobile = $GLOBALS['_CFG']['sms_shop_mobile'];
						}
						else {
							$sql = 'SELECT mobile FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' WHERE ru_id = \'' . $ru_id . '\'');
							$sms_shop_mobile = $GLOBALS['db']->getOne($sql, true);
						}

						$order_result = array();
						if ($GLOBALS['_CFG']['sms_order_payed'] == '1' && $sms_shop_mobile != '') {
							$order_region = get_flow_user_region($order_id);
							$smsParams = array('shop_name' => $shop_name, 'shopname' => $shop_name, 'order_sn' => $order_sn, 'ordersn' => $order_sn, 'consignee' => $order['consignee'], 'order_region' => $order_region, 'orderregion' => $order_region, 'address' => $order['address'], 'order_mobile' => $order['mobile'], 'ordermobile' => $order['mobile'], 'mobile_phone' => $sms_shop_mobile, 'mobilephone' => $sms_shop_mobile);

							if ($GLOBALS['_CFG']['sms_type'] == 0) {
								huyi_sms($smsParams, 'sms_order_payed');
							}
							else if (1 <= $GLOBALS['_CFG']['sms_type']) {
								$order_result = sms_ali($smsParams, 'sms_order_payed');
							}
						}

						$sql = 'SELECT id, store_id, order_id FROM ' . $GLOBALS['ecs']->table('store_order') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
						$stores_order = $GLOBALS['db']->getRow($sql);
						$store_result = array();
						if ($stores_order && 0 < $stores_order['store_id'] && 0 < $order_id) {
							if ($order['mobile']) {
								$user_mobile_phone = $order['mobile'];
							}
							else {
								$sql = 'SELECT mobile_phone FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\'';
								$user_mobile_phone = $GLOBALS['db']->getOne($sql, true);
							}

							if (!empty($user_mobile_phone)) {
								$pick_code = substr($order['order_sn'], -3) . rand(0, 9) . rand(0, 9) . rand(0, 9);
								$sql = 'UPDATE ' . $GLOBALS['ecs']->table('store_order') . (' SET pick_code = \'' . $pick_code . '\' WHERE id = \'') . $stores_order['id'] . '\'';
								$db->query($sql);
								$sql = 'SELECT id, country, province, city, district, stores_address, stores_name, stores_tel FROM ' . $GLOBALS['ecs']->table('offline_store') . ' WHERE id = \'' . $stores_order['store_id'] . '\' LIMIT 1';
								$stores_info = $GLOBALS['db']->getRow($sql);
								$store_address = get_area_region_info($stores_info) . $stores_info['stores_address'];
								$user_name = isset($_SESSION['user_name']) && !empty($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
								$store_smsParams = array('user_name' => $user_name, 'username' => $user_name, 'order_sn' => $order_sn, 'ordersn' => $order_sn, 'code' => $pick_code, 'store_address' => $store_address, 'storeaddress' => $store_address, 'mobile_phone' => $user_mobile_phone, 'mobilephone' => $user_mobile_phone);

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

						if (1 <= $GLOBALS['_CFG']['sms_type'] && 0 < $order_id) {
							$sms_send = array($store_result, $order_result);
							$resp = $GLOBALS['ecs']->ali_yu($sms_send, 1);
						}

						if (0 < $stages_qishu) {
							$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . (' SET stages_qishu = \'-1\' WHERE order_id = \'' . $order_id . '\'');
							$GLOBALS['db']->query($sql);
						}

						$virtual_goods = get_virtual_goods($order_id);

						if (!empty($virtual_goods)) {
							if ($virtual_goods) {
								if (virtual_goods_ship($virtual_goods, $msg, $order['order_sn'], true)) {
									if ($order['shipping_id'] == -1 || empty($order['shipping_id'])) {
										$sql = 'SELECT COUNT(*)' . ' FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' ') . ' AND is_real = 1';

										if ($GLOBALS['db']->getOne($sql) <= 0) {
											update_order($order_id, array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));
											order_action($order_sn, OS_CONFIRMED, SS_SHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);

											if (0 < $order['user_id']) {
												$integral = integral_to_give($order);
												$gave_custom_points = integral_of_value($integral['custom_points']) - $order['integral'];

												if ($gave_custom_points < 0) {
													$gave_custom_points = 0;
												}

												log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($GLOBALS['_LANG']['order_gift_integral'], $order['order_sn']));
												send_order_bonus($order_id);
												send_order_coupons($order_id);
											}
										}
									}
								}
							}
						}
					}
				}
				else if ($pay_log['order_type'] == PAY_SURPLUS) {
					$sql = 'SELECT id, user_id, amount, is_paid FROM ' . $GLOBALS['ecs']->table('user_account') . ' WHERE `id` = \'' . $pay_log['order_id'] . '\' LIMIT 1';
					$user_account = $GLOBALS['db']->getRow($sql);

					if ($user_account) {
						if ($user_account['is_paid'] == 0) {
							$sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_account') . ' SET paid_time = \'' . gmtime() . '\', is_paid = 1' . ' WHERE id = \'' . $pay_log['order_id'] . '\'';
							$GLOBALS['db']->query($sql);
							include_once ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/user.php';
							log_account_change($user_account['user_id'], $user_account['amount'], 0, 0, 0, $GLOBALS['_LANG']['surplus_type_0'], ACT_SAVING);
						}
					}
				}
				else if ($pay_log['order_type'] == PAY_APPLYTEMP) {
					require_once ROOT_PATH . 'includes/lib_visual.php';
					$sql = 'SELECT ru_id,temp_id,temp_code,apply_sn FROM' . $GLOBALS['ecs']->table('seller_template_apply') . 'WHERE apply_id = \'' . $pay_log['order_id'] . '\'';
					$seller_template_apply = $GLOBALS['db']->getRow($sql);
					$new_suffix = get_new_dirName($seller_template_apply['ru_id']);
					Import_temp($seller_template_apply['temp_code'], $new_suffix, $seller_template_apply['ru_id']);
					$sql = 'UPDATE' . $GLOBALS['ecs']->table('template_mall') . 'SET sales_volume = sales_volume+1 WHERE temp_id = \'' . $seller_template_apply['temp_id'] . '\'';
					$GLOBALS['db']->query($sql);
					$sql = ' UPDATE ' . $GLOBALS['ecs']->table('seller_template_apply') . ' SET pay_status = 1 ,pay_time = \'' . gmtime() . '\' , apply_status = 1 WHERE apply_id= \'' . $pay_log['order_id'] . '\'';
					$GLOBALS['db']->query($sql);
				}
				else if ($pay_log['order_type'] == PAY_APPLYGRADE) {
					$sql = ' UPDATE ' . $GLOBALS['ecs']->table('seller_apply_info') . ' SET is_paid = 1 ,pay_time = \'' . gmtime() . '\' ,pay_status = 1 WHERE apply_id= \'' . $pay_log['order_id'] . '\'';
					$GLOBALS['db']->query($sql);
				}
				else if ($pay_log['order_type'] == PAY_TOPUP) {
					$sql = 'SELECT ru_id FROM ' . $GLOBALS['ecs']->table('seller_account_log') . ' WHERE log_id = \'' . $pay_log['order_id'] . '\' LIMIT 1';
					$account_log = $GLOBALS['db']->getRow($sql);
					$sql = ' UPDATE ' . $GLOBALS['ecs']->table('seller_account_log') . ' SET is_paid = 1 WHERE log_id = \'' . $pay_log['order_id'] . '\'';
					$GLOBALS['db']->query($sql);
					$sql = ' UPDATE ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' SET seller_money = seller_money + ' . $pay_log['order_amount'] . ' WHERE ru_id = \'' . $account_log['ru_id'] . '\'';
					$GLOBALS['db']->query($sql);
					$change_desc = '商家充值，操作员：商家本人线上支付';
					$log = array('user_id' => $account_log['ru_id'], 'user_money' => $pay_log['order_amount'], 'change_time' => gmtime(), 'change_desc' => $change_desc, 'change_type' => 2);
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_account_log'), $log, 'INSERT');
				}
				else if ($pay_log['order_type'] == PAY_WHOLESALE) {
					$order_id = $pay_log['order_id'];
					$time = gmtime();
					$sql = ' UPDATE ' . $GLOBALS['ecs']->table('wholesale_order_info') . (' SET pay_status = \'' . $pay_status . '\' ,pay_time = \'' . $time . '\'  WHERE order_id = \'' . $order_id . '\'');
					$GLOBALS['db']->query($sql);
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_log') . 'SET is_paid = 1 WHERE order_id = \'' . $order_id . '\' AND order_type = \'' . PAY_WHOLESALE . '\'';
					$GLOBALS['db']->query($sql);
					$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('wholesale_order_info') . (' WHERE main_order_id = \'' . $order_id . '\'');
					$child_num = $GLOBALS['db']->getOne($sql);
					if (0 < $child_num && 0 < $order_id) {
						$sql = 'SELECT order_id, order_sn, pay_id, order_amount ' . 'FROM ' . $GLOBALS['ecs']->table('wholesale_order_info') . (' WHERE main_order_id = \'' . $order_id . '\'');
						$order_res = $GLOBALS['db']->getAll($sql);

						foreach ($order_res as $row) {
							$sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_log') . 'SET is_paid = 1 WHERE order_id = \'' . $row['order_id'] . '\' AND order_type = \'' . PAY_WHOLESALE . '\'';
							$GLOBALS['db']->query($sql);
							$child_pay_fee = order_pay_fee($row['pay_id'], $row['order_amount']);
							$sql = 'UPDATE ' . $GLOBALS['ecs']->table('wholesale_order_info') . (' SET pay_status = \'' . $pay_status . '\', ') . (' pay_time = \'' . $time . '\', ') . (' pay_fee = \'' . $child_pay_fee . '\' ') . 'WHERE order_id = \'' . $row['order_id'] . '\'';
							$GLOBALS['db']->query($sql);
						}
					}
				}
			}
			else {
				$order_id = $pay_log['order_id'];
				$sql = 'SELECT main_order_id, order_id, user_id, order_sn, consignee, address, tel, mobile, shipping_id, pay_status, extension_code, extension_id, goods_amount, ' . 'shipping_fee, insure_fee, pay_fee, tax, pack_fee, card_fee, surplus, money_paid, integral, integral_money, bonus, order_amount, discount, pay_id, shipping_status ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $pay_log['order_id'] . '\'');
				$order = $GLOBALS['db']->getRow($sql);
				$virtual_goods = get_virtual_goods($order_id);

				if (!empty($virtual_goods)) {
					if ($virtual_goods) {
						if (virtual_goods_ship($virtual_goods, $msg, $order['order_sn'], true)) {
							if ($order['shipping_id'] == -1 || empty($order['shipping_id'])) {
								$sql = 'SELECT COUNT(*)' . ' FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' ') . ' AND is_real = 1';

								if ($GLOBALS['db']->getOne($sql) <= 0) {
									update_order($order_id, array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));
									order_action($order_sn, OS_CONFIRMED, SS_SHIPPED, $order['pay_status'], $note, $GLOBALS['_LANG']['buyer']);

									if (0 < $order['user_id']) {
										$user = user_info($order['user_id']);
										$integral = integral_to_give($order);
										$gave_custom_points = integral_of_value($integral['custom_points']) - $order['integral'];

										if ($gave_custom_points < 0) {
											$gave_custom_points = 0;
										}

										log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($GLOBALS['_LANG']['order_gift_integral'], $order['order_sn']));
										send_order_bonus($order_id);
										send_order_coupons($order_id);
									}
								}
							}
						}
					}
				}

				$is_number = order_virtual_card_count($order_id);

				if ($is_number == 1) {
					$GLOBALS['_LANG']['pay_success'] .= '<br />' . $GLOBALS['_LANG']['virtual_goods_ship_fail'];
				}
			}
		}
	}
}

function order_payment_info($pay_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('payment') . (' WHERE pay_id = \'' . $pay_id . '\' AND enabled = 1');
	return $GLOBALS['db']->getRow($sql);
}

function order_pay_fee($payment_id, $order_amount, $cod_fee = NULL)
{
	$pay_fee = 0;
	$payment = order_payment_info($payment_id);
	$rate = $payment['is_cod'] && !is_null($cod_fee) ? $cod_fee : $payment['pay_fee'];

	if (strpos($rate, '%') !== false) {
		$val = floatval($rate) / 100;
		$pay_fee = 0 < $val ? $order_amount * $val / (1 - $val) : 0;
	}
	else {
		$pay_fee = floatval($rate);
	}

	return round($pay_fee, 2);
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
