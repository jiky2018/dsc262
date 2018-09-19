<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function cause_info($c_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('return_cause') . ' WHERE cause_id = ' . $c_id;
	$res = $GLOBALS['db']->getRow($sql);

	if ($res) {
		return $res;
	}
	else {
		return array();
	}
}

function get_status_list($type = 'all')
{
	global $_LANG;
	$list = array();
	if ($type == 'all' || $type == 'order') {
		$pre = $type == 'all' ? 'os_' : '';

		foreach ($_LANG['os'] as $key => $value) {
			$list[$pre . $key] = $value;
		}
	}

	if ($type == 'all' || $type == 'shipping') {
		$pre = $type == 'all' ? 'ss_' : '';

		foreach ($_LANG['ss'] as $key => $value) {
			$list[$pre . $key] = $value;
		}
	}

	if ($type == 'all' || $type == 'payment') {
		$pre = $type == 'all' ? 'ps_' : '';

		foreach ($_LANG['ps'] as $key => $value) {
			$list[$pre . $key] = $value;
		}
	}

	return $list;
}

function update_order_amount($order_id)
{
	include_once ROOT_PATH . 'includes/lib_order.php';
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET order_amount = ' . order_due_field() . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
	return $GLOBALS['db']->query($sql);
}

function operable_list($order)
{
	$os = $order['order_status'];
	$ss = $order['shipping_status'];
	$ps = $order['pay_status'];
	$chargeoff_status = $order['chargeoff_status'];
	$actions = $_SESSION['seller_action_list'];

	if ($actions == 'all') {
		$priv_list = array('os' => true, 'ss' => true, 'ps' => true, 'edit' => true);
	}
	else {
		$actions = ',' . $actions . ',';
		$priv_list = array('os' => strpos($actions, ',order_os_edit,') !== false, 'ss' => strpos($actions, ',order_ss_edit,') !== false, 'ps' => strpos($actions, ',order_ps_edit,') !== false, 'edit' => strpos($actions, ',order_edit,') !== false);
	}

	$payment = payment_info($order['pay_id']);
	$is_cod = $payment['is_cod'] == 1;
	$list = array();

	if (OS_UNCONFIRMED == $os) {
		if ($priv_list['os']) {
			$list['confirm'] = true;
			$list['invalid'] = true;
			$list['cancel'] = true;

			if ($is_cod) {
				if ($priv_list['ss']) {
					$list['prepare'] = true;
					$list['split'] = true;
				}
			}
			else if ($priv_list['ps']) {
				$list['pay'] = true;
			}
		}
	}
	else {
		if (OS_RETURNED_PART == $os || SS_RECEIVED != $ss && 0 < $chargeoff_status) {
			if ($priv_list['ps'] < 2) {
				$list['pay'] = true;
			}

			if ($ss != SS_RECEIVED) {
				$list['receive'] = true;
			}
		}
		else {
			if (OS_CONFIRMED == $os || OS_SPLITED == $os || OS_SPLITING_PART == $os) {
				if (PS_UNPAYED == $ps || PS_PAYED_PART == $ps) {
					if (SS_UNSHIPPED == $ss || SS_PREPARING == $ss) {
						if ($priv_list['os']) {
							$list['cancel'] = true;
							$list['invalid'] = true;
						}

						if ($is_cod) {
							if ($priv_list['ss']) {
								if (SS_UNSHIPPED == $ss) {
									$list['prepare'] = true;
								}

								$list['split'] = true;
							}
						}
						else if ($priv_list['ps']) {
							$list['pay'] = true;
						}
					}
					else {
						if (SS_SHIPPED_ING == $ss || SS_SHIPPED_PART == $ss) {
							if (OS_SPLITING_PART == $os) {
								$list['split'] = true;
							}

							$list['to_delivery'] = true;
						}
						else {
							if ($priv_list['ps']) {
								$list['pay'] = true;
							}

							if ($priv_list['ss']) {
								if (SS_SHIPPED == $ss) {
									$list['receive'] = true;
								}

								$list['unship'] = true;

								if ($priv_list['os']) {
									$list['return'] = true;
								}
							}
						}
					}
				}
				else {
					if (SS_UNSHIPPED == $ss || SS_PREPARING == $ss) {
						if ($priv_list['ss']) {
							if (SS_UNSHIPPED == $ss) {
								$list['prepare'] = true;
							}

							$list['split'] = true;
						}

						if ($priv_list['ps']) {
							$list['unpay'] = true;

							if ($priv_list['os']) {
							}
						}
					}
					else {
						if (SS_SHIPPED_ING == $ss || SS_SHIPPED_PART == $ss) {
							if (OS_SPLITING_PART == $os) {
								$list['split'] = true;
							}

							$list['to_delivery'] = true;
						}
						else {
							if ($priv_list['ss']) {
								if (SS_SHIPPED == $ss) {
									$list['receive'] = true;
								}

								if (!$is_cod) {
									$list['unship'] = true;
								}
							}

							if ($priv_list['ps'] && $is_cod) {
								$list['unpay'] = true;
							}

							if ($priv_list['os'] && $priv_list['ss'] && $priv_list['ps']) {
								$list['return'] = true;
							}
						}
					}
				}
			}
			else if (OS_CANCELED == $os) {
				if ($priv_list['os']) {
				}

				if ($priv_list['edit']) {
					$list['remove'] = true;
				}
			}
			else if (OS_INVALID == $os) {
				if ($priv_list['os']) {
				}

				if ($priv_list['edit']) {
					$list['remove'] = true;
				}
			}
			else if (OS_RETURNED == $os) {
				if ($priv_list['os']) {
					$list['confirm'] = true;
				}
			}
		}
	}

	if ((OS_CONFIRMED == $os || OS_SPLITED == $os || OS_SHIPPED_PART == $os) && PS_PAYED == $ps && (SS_UNSHIPPED == $ss || SS_SHIPPED_PART == $ss)) {
		if ($priv_list['os'] && $priv_list['ss'] && $priv_list['ps']) {
			$list['return'] = true;
		}
	}

	if (!empty($list['split'])) {
		if ($order['extension_code'] == 'group_buy') {
			include_once ROOT_PATH . 'includes/lib_goods.php';
			$group_buy = group_buy_info(intval($order['extension_id']));

			if ($group_buy['status'] != GBS_SUCCEED) {
				unset($list['split']);
				unset($list['to_delivery']);
			}
		}

		if (order_deliveryed($order['order_id'])) {
			$list['return'] = true;
			unset($list['cancel']);
		}
	}

	$list['after_service'] = true;
	$list['receive_goods'] = true;
	$list['agree_apply'] = true;
	$list['refound'] = true;
	$list['swapped_out_single'] = true;
	$list['swapped_out'] = true;
	$list['complete'] = true;
	$list['refuse_apply'] = true;
	return $list;
}

function handle_order_money_change($order, &$msgs, &$links)
{
	$order_id = $order['order_id'];
	if ($order['pay_status'] == PS_PAYED || $order['pay_status'] == PS_PAYING) {
		$money_dues = $order['order_amount'];

		if (0 < $money_dues) {
			update_order($order_id, array('pay_status' => PS_UNPAYED, 'pay_time' => 0));
			$msgs[] = $GLOBALS['_LANG']['amount_increase'];
			$links[] = array('text' => $GLOBALS['_LANG']['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
		}
		else if ($money_dues < 0) {
			$anonymous = 0 < $order['user_id'] ? 0 : 1;
			$msgs[] = $GLOBALS['_LANG']['amount_decrease'];
			$links[] = array('text' => $GLOBALS['_LANG']['refund'], 'href' => 'order.php?act=process&func=load_refund&anonymous=' . $anonymous . '&order_id=' . $order_id . '&refund_amount=' . abs($money_dues));
		}
	}
}

function order_list($page = 0)
{
	$adminru = get_admin_ru_id();
	$ruCat = '';
	$no_main_order = '';
	$noTime = gmtime();
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
		$filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
		$filter['address'] = empty($_REQUEST['address']) ? '' : trim($_REQUEST['address']);
		$filter['shipped_deal'] = empty($_REQUEST['shipped_deal']) ? '' : trim($_REQUEST['shipped_deal']);
		if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
			$filter['order_sn'] = json_str_iconv($filter['order_sn']);
			$filter['consignee'] = json_str_iconv($filter['consignee']);
			$filter['address'] = json_str_iconv($filter['address']);
		}

		$filter['email'] = empty($_REQUEST['email']) ? '' : trim($_REQUEST['email']);
		$filter['zipcode'] = empty($_REQUEST['zipcode']) ? '' : trim($_REQUEST['zipcode']);
		$filter['tel'] = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);
		$filter['mobile'] = empty($_REQUEST['mobile']) ? 0 : trim($_REQUEST['mobile']);
		$filter['country'] = empty($_REQUEST['order_country']) ? 0 : intval($_REQUEST['order_country']);
		$filter['province'] = empty($_REQUEST['order_province']) ? 0 : intval($_REQUEST['order_province']);
		$filter['city'] = empty($_REQUEST['order_city']) ? 0 : intval($_REQUEST['order_city']);
		$filter['district'] = empty($_REQUEST['order_district']) ? 0 : intval($_REQUEST['order_district']);
		$filter['street'] = empty($_REQUEST['order_street']) ? 0 : intval($_REQUEST['order_street']);
		$filter['shipping_id'] = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
		$filter['pay_id'] = empty($_REQUEST['pay_id']) ? 0 : intval($_REQUEST['pay_id']);
		$filter['order_status'] = isset($_REQUEST['order_status']) ? intval($_REQUEST['order_status']) : -1;
		$filter['shipping_status'] = isset($_REQUEST['shipping_status']) ? intval($_REQUEST['shipping_status']) : -1;
		$filter['pay_status'] = isset($_REQUEST['pay_status']) ? intval($_REQUEST['pay_status']) : -1;
		$filter['user_id'] = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
		$filter['user_name'] = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
		$filter['composite_status'] = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;
		$filter['group_buy_id'] = isset($_REQUEST['group_buy_id']) ? intval($_REQUEST['group_buy_id']) : 0;
		$filter['presale_id'] = isset($_REQUEST['presale_id']) ? intval($_REQUEST['presale_id']) : 0;
		$filter['store_id'] = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
		$filter['order_cat'] = isset($_REQUEST['order_cat']) ? trim($_REQUEST['order_cat']) : '';
		$filter['source'] = empty($_REQUEST['source']) ? '' : trim($_REQUEST['source']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (0 < strpos($_REQUEST['start_time'], '-') ? local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
		$filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (0 < strpos($_REQUEST['end_time'], '-') ? local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);
		$filter['start_take_time'] = empty($_REQUEST['start_take_time']) ? '' : (0 < strpos($_REQUEST['start_take_time'], '-') ? local_strtotime($_REQUEST['start_take_time']) : $_REQUEST['start_take_time']);
		$filter['end_take_time'] = empty($_REQUEST['end_take_time']) ? '' : (0 < strpos($_REQUEST['end_take_time'], '-') ? local_strtotime($_REQUEST['end_take_time']) : $_REQUEST['end_take_time']);
		$filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$filter['store_type'] = isset($_REQUEST['store_type']) ? trim($_REQUEST['store_type']) : '';
		$filter['serch_type'] = !isset($_REQUEST['serch_type']) ? -1 : intval($_REQUEST['serch_type']);
		$where = ' WHERE 1 ';

		if ($filter['keywords']) {
			$where .= ' AND (o.order_sn LIKE \'%' . $filter['keywords'] . '%\'';
			$where .= ' OR (iog.goods_name LIKE \'%' . $filter['keywords'] . '%\' OR iog.goods_sn LIKE \'%' . $filter['keywords'] . '%\'))';
		}

		if (0 < $adminru['ru_id']) {
			$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . ' WHERE og.order_id = o.order_id LIMIT 1) = \'' . $adminru['ru_id'] . '\' ';
		}

		$no_main_order = ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';

		if ($filter['shipped_deal']) {
			$where .= ' AND o.shipping_status<>' . SS_RECEIVED;
		}

		$leftJoin = '';
		$store_search = -1;
		$store_where = '';
		$store_search_where = '';

		if (-1 < $filter['store_search']) {
			if ($adminru['ru_id'] == 0) {
				if (0 < $filter['store_search']) {
					if ($filter['store_type']) {
						$store_search_where = 'AND msi.shopNameSuffix = \'' . $filter['store_type'] . '\'';
					}

					$no_main_order = ' and (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi2 where oi2.main_order_id = o.order_id) = 0 ';

					if ($filter['store_search'] == 1) {
						$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og' . ' WHERE og.order_id = o.order_id LIMIT 1) = \'' . $filter['merchant_id'] . '\' ';
					}
					else if ($filter['store_search'] == 2) {
						$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
					}
					else if ($filter['store_search'] == 3) {
						$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
					}

					if (1 < $filter['store_search']) {
						$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og, ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS msi ' . (' WHERE og.order_id = o.order_id AND msi.user_id = og.ru_id ' . $store_where . ' LIMIT 1) > 0 ');
					}
				}
				else {
					$store_search = 0;
				}
			}
		}

		if (0 < $filter['store_id']) {
			$leftJoin .= ' LEFT JOIN' . $GLOBALS['ecs']->table('store_order') . ' AS sto ON sto.order_id = o.order_id ';
			$where .= ' AND sto.store_id  = \'' . $filter['store_id'] . '\'';
		}

		if ($filter['order_sn']) {
			$where .= ' AND o.order_sn LIKE \'%' . mysql_like_quote($filter['order_sn']) . '%\'';
		}

		if ($filter['consignee']) {
			$where .= ' AND o.consignee LIKE \'%' . mysql_like_quote($filter['consignee']) . '%\'';
		}

		if ($filter['email']) {
			$where .= ' AND o.email LIKE \'%' . mysql_like_quote($filter['email']) . '%\'';
		}

		if ($filter['address']) {
			$where .= ' AND o.address LIKE \'%' . mysql_like_quote($filter['address']) . '%\'';
		}

		if ($filter['zipcode']) {
			$where .= ' AND o.zipcode LIKE \'%' . mysql_like_quote($filter['zipcode']) . '%\'';
		}

		if ($filter['tel']) {
			$where .= ' AND o.tel LIKE \'%' . mysql_like_quote($filter['tel']) . '%\'';
		}

		if ($filter['mobile']) {
			$where .= ' AND o.mobile LIKE \'%' . mysql_like_quote($filter['mobile']) . '%\'';
		}

		if ($filter['country']) {
			$where .= ' AND o.country = \'' . $filter['country'] . '\'';
		}

		if ($filter['province']) {
			$where .= ' AND o.province = \'' . $filter['province'] . '\'';
		}

		if ($filter['city']) {
			$where .= ' AND o.city = \'' . $filter['city'] . '\'';
		}

		if ($filter['district']) {
			$where .= ' AND o.district = \'' . $filter['district'] . '\'';
		}

		if ($filter['street']) {
			$where .= ' AND o.street = \'' . $filter['street'] . '\'';
		}

		if ($filter['shipping_id']) {
			$where .= ' AND o.shipping_id  = \'' . $filter['shipping_id'] . '\'';
		}

		if ($filter['pay_id']) {
			$where .= ' AND o.pay_id  = \'' . $filter['pay_id'] . '\'';
		}

		if ($filter['order_status'] != -1) {
			$where .= ' AND o.order_status  = \'' . $filter['order_status'] . '\'';
		}

		if ($filter['shipping_status'] != -1) {
			$where .= ' AND o.shipping_status = \'' . $filter['shipping_status'] . '\'';
		}

		if ($filter['pay_status'] != -1) {
			$where .= ' AND o.pay_status = \'' . $filter['pay_status'] . '\'';
		}

		if ($filter['user_id']) {
			$where .= ' AND o.user_id = \'' . $filter['user_id'] . '\'';
		}

		if ($filter['user_name']) {
			$where .= ' AND (SELECT u.user_id FROM ' . $GLOBALS['ecs']->table('users') . ' AS u WHERE u.user_name LIKE \'%' . mysql_like_quote($filter['user_name']) . '%\' LIMIT 1) = o.user_id';
		}

		if ($filter['start_time']) {
			$where .= ' AND o.add_time >= \'' . $filter['start_time'] . '\'';
		}

		if ($filter['end_time']) {
			$where .= ' AND o.add_time <= \'' . $filter['end_time'] . '\'';
		}

		$alias = 'o.';

		switch ($filter['composite_status']) {
		case CS_AWAIT_PAY:
			$where .= order_query_sql('await_pay', $alias);
			break;

		case CS_AWAIT_SHIP:
			$where .= order_query_sql('await_ship', $alias);
			break;

		case CS_FINISHED:
			$where .= order_query_sql('finished', $alias);
			break;

		case CS_CONFIRM_TAKE:
			$where .= order_query_sql('confirm_take', $alias);
			break;

		case PS_PAYING:
			if ($filter['composite_status'] != -1) {
				$where .= ' AND o.pay_status = \'' . $filter['composite_status'] . '\' ';
			}

			break;

		case OS_SHIPPED_PART:
			if ($filter['composite_status'] != -1) {
				$where .= ' AND o.shipping_status  = \'' . $filter['composite_status'] . '\'-2 ';
			}

			break;

		default:
			if ($filter['composite_status'] != -1) {
				$where .= ' AND o.order_status = \'' . $filter['composite_status'] . '\' ';
			}
		}

		if ($filter['group_buy_id']) {
			$where .= ' AND o.extension_code = \'group_buy\' AND o.extension_id = \'' . $filter['group_buy_id'] . '\' ';
		}

		if ($filter['presale_id']) {
			$where .= ' AND o.extension_code = \'presale\' AND o.extension_id = \'' . $filter['presale_id'] . '\' ';
		}

		$sql = 'SELECT agency_id FROM ' . $GLOBALS['ecs']->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['seller_id'] . '\' AND action_list <> \'all\'');
		$agency_id = $GLOBALS['db']->getOne($sql);

		if (0 < $agency_id) {
			$where .= ' AND o.agency_id = \'' . $agency_id . '\' ';
		}

		if ($filter['order_cat']) {
			switch ($filter['order_cat']) {
			case 'stages':
				$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('baitiao_log') . ' AS b ON b.order_id = o.order_id ';
				$where .= ' AND b.order_id > 0 ';
				break;

			case 'zc':
				$where .= ' AND o.is_zc_order = 1 ';
				break;

			case 'store':
				$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('store_order') . ' AS s ON s.order_id = o.order_id ';
				$where .= ' AND s.order_id > 0 ';
				break;

			case 'other':
				$where .= ' AND length(o.extension_code) > 0 ';
				break;

			case 'dbdd':
				$where .= ' AND o.extension_code = \'snatch\' ';
				break;

			case 'msdd':
				$where .= ' AND o.extension_code = \'seckill\' ';
				break;

			case 'tgdd':
				$where .= ' AND o.extension_code = \'group_buy\' ';
				break;

			case 'pmdd':
				$where .= ' AND o.extension_code = \'auction\' ';
				break;

			case 'jfdd':
				$where .= ' AND o.extension_code = \'exchange_goods\' ';
				break;

			case 'ysdd':
				$where .= ' AND o.extension_code = \'presale\' ';
				break;

			default:
			}
		}

		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);

		if (0 < $page) {
			$filter['page'] = $page;
		}

		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$where_store = '';
		if (empty($filter['start_take_time']) || empty($filter['end_take_time'])) {
			if ($store_search == 0 && $adminru['ru_id'] == 0) {
				$where_store = ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' WHERE o.order_id = og.order_id AND og.ru_id = 0 LIMIT 1) > 0 ' . ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi2 WHERE oi2.main_order_id = o.order_id) = 0';
			}
		}

		if (!empty($filter['start_take_time']) || !empty($filter['end_take_time'])) {
			$where_action = '';

			if ($filter['start_take_time']) {
				$where_action .= ' AND oa.log_time >= \'' . $filter['start_take_time'] . '\'';
			}

			if ($filter['end_take_time']) {
				$where_action .= ' AND oa.log_time <= \'' . $filter['end_take_time'] . '\'';
			}

			$where_action .= order_take_query_sql('finished', 'oa.');
			$where .= ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_action') . (' AS oa WHERE o.order_id = oa.order_id ' . $where_action . ') > 0');
		}

		$groupBy = '';
		$count_arr = array();
		$status_arr = array(-1, 0, 1, 2, 3, 4, 6, 100, 101, 102);

		if (!empty($filter['keywords'])) {
			$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS iog ON iog.order_id = o.order_id ';
			$sql = 'SELECT o.order_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $leftJoin . $where . $where_store . $no_main_order . ' GROUP BY o.order_id';
			$record_count = count($GLOBALS['db']->getAll($sql));
			$groupBy = ' GROUP BY o.order_id ';
		}
		else {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $leftJoin . $where . $where_store . $no_main_order;
			$record_count = $GLOBALS['db']->getOne($sql);
		}

		if (empty($filter['keywords'])) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $leftJoin . $where . $status_where . $where_store . $no_main_order;
			$record_count = $GLOBALS['db']->getOne($sql);
			$count_where = '';

			foreach ($status_arr as $status) {
				$c_leftJoin = '';

				switch ($status) {
				case 0:
					$count_where = ' AND o.order_status = 0 ';
					break;

				case 100:
					$count_where = ' AND o.pay_status = 0 AND o.order_status = 1  ';
					break;

				case 101:
					$count_where = ' AND (o.shipping_status = 0 OR o.shipping_status = 3) AND o.pay_status = 2 AND o.order_status = 1 AND (SELECT ore.ret_id FROM ' . $GLOBALS['ecs']->table('order_return') . ' as ore WHERE ore.order_id = o.order_id LIMIT 1) IS NULL ';
					break;

				case 102:
					$count_where = ' AND o.shipping_status = 2 ';
					break;

				case 1:
					$count_where = ' AND o.pay_status = 1 ';
					break;

				case 2:
					$count_where = ' AND o.order_status = 2 ';
					break;

				case 3:
					$count_where = ' AND o.order_status = 3 ';
					break;

				case 4:
					$c_leftJoin = ' JOIN ' . $GLOBALS['ecs']->table('order_return') . ' AS ore ON ore.order_id = o.order_id ';
					$count_where = ' AND o.order_status NOT IN (2,3,4,7,8) ';
					break;

				case 6:
					$count_where = ' AND o.shipping_status = 1 ';
				}

				$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $leftJoin . $c_leftJoin . $where . $count_where . $where_store . $no_main_order;
				$count_arr[$status] = $GLOBALS['db']->getOne($sql);
			}
		}

		$c_leftJoin = '';

		switch ($filter['serch_type']) {
		case 0:
			$where .= ' AND o.order_status = 0 ';
			break;

		case 100:
			$where .= ' AND o.pay_status = 0 AND o.order_status = 1  ';
			break;

		case 101:
			$where .= ' AND (o.shipping_status = 0 OR o.shipping_status = 3) AND o.pay_status = 2 AND o.order_status = 1 AND (SELECT ore.ret_id FROM ' . $GLOBALS['ecs']->table('order_return') . ' as ore WHERE ore.order_id = o.order_id LIMIT 1) IS NULL ';
			break;

		case 102:
			$where .= ' AND o.shipping_status = 2 ';
			break;

		case 1:
			$where .= ' AND o.pay_status = 1 ';
			break;

		case 2:
			$where .= ' AND o.order_status = 2 ';
			break;

		case 3:
			$where .= ' AND o.order_status = 3 ';
			break;

		case 4:
			$leftJoin .= ' JOIN ' . $GLOBALS['ecs']->table('order_return') . ' AS ore ON ore.order_id = o.order_id ';
			$where .= ' AND o.order_status NOT IN (2,3,4,7,8) ';
			break;

		case 6:
			$where .= ' AND o.shipping_status = 1 ';
		}

		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);

		if (0 < $page) {
			$filter['page'] = $page;
		}

		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$sql = 'SELECT ifnull(bai.is_stages,0) is_stages, o.order_id, o.main_order_id, o.order_sn, o.add_time, o.order_status, o.shipping_status, o.pay_status, o.order_amount, o.money_paid, o.is_delete,' . 'o.shipping_fee, o.insure_fee, o.pay_fee, o.surplus,o.tax, o.integral_money, o.bonus, o.discount, o.coupons,' . 'o.shipping_time, o.auto_delivery_time, o.consignee, o.address, o.email, o.tel, o.mobile, o.extension_code AS o_extension_code, ' . 'o.extension_id, o.user_id, o.referer, o.froms, o.chargeoff_status, o.pay_id, o.pay_name, o.shipping_id, o.shipping_name, o.goods_amount, ' . '(' . order_amount_field('o.') . ') AS total_fee, (o.goods_amount + o.tax + o.shipping_fee + o.insure_fee + o.pay_fee + o.pack_fee + o.card_fee - o.discount) AS total_fee_order, o.pay_id ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('baitiao_log') . ' AS bai ON o.order_id=bai.order_id ' . $leftJoin . $where . $where_store . $no_main_order . $groupBy . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);

		foreach (array('order_sn', 'consignee', 'email', 'address', 'zipcode', 'tel', 'user_name') as $val) {
			$filter[$val] = stripslashes($filter[$val]);
		}

		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table('store_order') . (' WHERE order_id = \'' . $value['order_id'] . '\'');
		$row[$key]['store_order_id'] = $GLOBALS['db']->getOne($sql);
		if ($value['shipping_status'] == 2 && empty($value['confirm_take_time'])) {
			$sql = 'SELECT MAX(log_time) AS log_time FROM ' . $GLOBALS['ecs']->table('order_action') . ' WHERE order_id = \'' . $value['order_id'] . '\' AND shipping_status = \'' . SS_RECEIVED . '\'';
			$confirm_take_time = $GLOBALS['db']->getOne($sql, true);
			$log_other = array('confirm_take_time' => $confirm_take_time);
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $log_other, 'UPDATE', 'order_id = \'' . $value['order_id'] . '\'');
			$value['confirm_take_time'] = $confirm_take_time;
		}

		if (($value['order_status'] == OS_UNCONFIRMED || $value['order_status'] == OS_CONFIRMED || $value['order_status'] == OS_SPLITED) && $value['pay_status'] == PS_UNPAYED) {
			$pay_log = get_pay_log($value['order_id'], 1);
			if ($pay_log && $pay_log['is_paid'] == 0) {
				$payment = payment_info($value['pay_id']);
				$file_pay = ROOT_PATH . 'includes/modules/payment/' . $payment['pay_code'] . '.php';
				if ($payment && file_exists($file_pay)) {
					include_once $file_pay;

					if (class_exists($payment['pay_code'])) {
						$pay_obj = new $payment['pay_code']();
						$is_callable = array($pay_obj, 'query');

						if (is_callable($is_callable)) {
							$order_other = array('order_sn' => $value['order_sn'], 'log_id' => $pay_log['log_id']);
							$pay_obj->query($order_other);
							$sql = 'SELECT order_status, shipping_status, pay_status, pay_time FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $value['order_id'] . '\' LIMIT 1';
							$order_info = $GLOBALS['db']->getRow($sql);

							if ($order_info) {
								$value['order_status'] = $order_info['order_status'];
								$value['shipping_status'] = $order_info['shipping_status'];
								$value['pay_status'] = $order_info['pay_status'];
								$value['pay_time'] = $order_info['pay_time'];
							}
						}
					}
				}
			}
		}

		if (($value['order_status'] == OS_CONFIRMED || $value['order_status'] == OS_SPLITED) && $value['pay_status'] == PS_PAYED && $value['shipping_status'] == SS_RECEIVED) {
			$bill_info = array('order_id' => $value['order_id']);
			$bill_order_info = get_bill_order($bill_info);

			if (!$bill_order_info) {
				$sql = 'SELECT order_id, user_id, order_sn , order_status, shipping_status, pay_status, ' . 'order_amount, goods_amount, tax, shipping_fee, insure_fee, pay_fee, pack_fee, card_fee, ' . 'bonus, integral_money, coupons, discount, money_paid, surplus, confirm_take_time ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $value['order_id'] . '\'';
				$bill_order = $GLOBALS['db']->GetRow($sql);
				$seller_id = $GLOBALS['db']->getOne('SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id = \'' . $value['order_id'] . '\'', true);
				$value_card = $GLOBALS['db']->getOne('SELECT use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . ' WHERE order_id = \'' . $value['order_id'] . '\'', true);
				$return_amount = get_order_return_amount($value['order_id']);
				$other = array('user_id' => $bill_order['user_id'], 'seller_id' => $seller_id, 'order_id' => $bill_order['order_id'], 'order_sn' => $bill_order['order_sn'], 'order_status' => $bill_order['order_status'], 'shipping_status' => SS_RECEIVED, 'pay_status' => $bill_order['pay_status'], 'order_amount' => $bill_order['order_amount'], 'return_amount' => $return_amount, 'goods_amount' => $bill_order['goods_amount'], 'tax' => $bill_order['tax'], 'shipping_fee' => $bill_order['shipping_fee'], 'insure_fee' => $bill_order['insure_fee'], 'pay_fee' => $bill_order['pay_fee'], 'pack_fee' => $bill_order['pack_fee'], 'card_fee' => $bill_order['card_fee'], 'bonus' => $bill_order['bonus'], 'integral_money' => $bill_order['integral_money'], 'coupons' => $bill_order['coupons'], 'discount' => $bill_order['discount'], 'value_card' => $value_card, 'money_paid' => $bill_order['money_paid'], 'surplus' => $bill_order['surplus'], 'confirm_take_time' => $value['confirm_take_time']);

				if ($seller_id) {
					get_order_bill_log($other);
				}
			}
		}

		if ($value['chargeoff_status'] == 1 || $value['chargeoff_status'] == 2) {
			$bill = $GLOBALS['db']->getRow(' SELECT scb.id, scb.bill_sn, scb.seller_id, scb.proportion, commission_model FROM ' . $GLOBALS['ecs']->table('seller_bill_order') . ' AS sbo ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('seller_commission_bill') . ' AS scb ON sbo.bill_id = scb.id ' . 'WHERE sbo.order_id = \'' . $value['order_id'] . '\' ');
			$row[$key]['bill_id'] = $bill['id'];
			$row[$key]['bill_sn'] = $bill['bill_sn'];
			$row[$key]['seller_id'] = $bill['seller_id'];
			$row[$key]['proportion'] = $bill['proportion'];
			$row[$key]['commission_model'] = $bill['commission_model'];
		}

		if ($value['extension_code'] == 'group_buy') {
			$group_buy = group_buy_info($value['extension_id'], 0, 'seller');
			$status = group_buy_status($group_buy);

			if ($status == 0) {
				$row[$key]['cur_status'] = '未开始';
			}
			else if ($status == 1) {
				$row[$key]['cur_status'] = '进行中';
			}
			else if ($status == 2) {
				$row[$key]['cur_status'] = '已结束,请去团购活动页确认！';
			}
			else if ($status == 3) {
				$row[$key]['cur_status'] = '团购成功';
			}
			else {
				$row[$key]['cur_status'] = '团购失败';
			}
		}

		$sql = 'SELECT ru_id, extension_code AS iog_extension_code FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id = \'' . $value['order_id'] . '\' ';
		$order_goods = $GLOBALS['db']->getAll($sql);
		$first_order = reset($order_goods);
		$value['ru_id'] = $first_order['ru_id'];

		if (1 < count($order_goods)) {
			$iog_extension_codes = array_column($order_goods, 'iog_extension_code');
			$row[$key]['iog_extension_codes'] = array_unique($iog_extension_codes);
		}
		else {
			$row[$key]['iog_extension_code'] = $first_order['iog_extension_code'];
		}

		$sql = ' SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $value['user_id'] . '\'';
		$value['buyer'] = $GLOBALS['db']->getOne($sql, true);
		$row[$key]['buyer'] = !empty($value['buyer']) ? $value['buyer'] : $GLOBALS['_LANG']['anonymous'];
		$row[$key]['formated_order_amount'] = price_format($value['order_amount']);
		$row[$key]['formated_money_paid'] = price_format($value['money_paid']);
		$row[$key]['formated_total_fee'] = price_format($value['total_fee']);
		$row[$key]['short_order_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
		$value_card = $GLOBALS['db']->getOne('SELECT use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . ' WHERE order_id = \'' . $value['order_id'] . '\'', true);
		$row[$key]['value_card'] = $value_card;
		$row[$key]['formated_value_card'] = price_format($value_card);
		$row[$key]['formated_total_fee_order'] = price_format($value['total_fee_order']);
		$row[$key]['region'] = get_user_region_address($value['order_id']);
		$is_store_order = $GLOBALS['db']->getOne(' SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('store_order') . ' WHERE order_id = \'' . $value['order_id'] . '\' ', true);
		$row[$key]['is_store_order'] = empty($is_store_order) ? 0 : 1;
		$sql = 'SELECT ret_id FROM ' . $GLOBALS['ecs']->table('order_return') . ' WHERE order_id =' . $value['order_id'];
		$row[$key]['is_order_return'] = $GLOBALS['db']->getOne($sql);
		$row[$key]['user_name'] = get_shop_name($value['ru_id'], 1);
		$order_id = $value['order_id'];
		$date = array('order_id');
		$order_child = count(get_table_date('order_info', 'main_order_id=\'' . $order_id . '\'', $date, 1));
		$row[$key]['order_child'] = $order_child;
		$date = array('order_sn');
		$child_list = get_table_date('order_info', 'main_order_id=\'' . $order_id . '\'', $date, 1);
		$row[$key]['child_list'] = $child_list;
		$order = array('order_id' => $value['order_id'], 'order_sn' => $value['order_sn']);
		$goods = get_order_goods($order);
		$row[$key]['goods_list'] = $goods['goods_list'];
		if ($value['order_status'] == OS_INVALID || $value['order_status'] == OS_CANCELED) {
			$row[$key]['can_remove'] = 1;
		}
		else {
			$row[$key]['can_remove'] = 0;
		}
	}

	$arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count'], 'count_arr' => $count_arr);
	return $arr;
}

function get_suppliers_list()
{
	$sql = "SELECT *\r\n            FROM " . $GLOBALS['ecs']->table('suppliers') . "\r\n            WHERE is_check = 1\r\n            ORDER BY suppliers_name ASC";
	$res = $GLOBALS['db']->getAll($sql);

	if (!is_array($res)) {
		$res = array();
	}

	return $res;
}

function get_order_goods($order)
{
	global $ecs;
	$goods_list = array();
	$goods_attr = array();
	$sql = 'SELECT o.*, g.model_inventory, g.model_attr AS model_attr, g.suppliers_id AS suppliers_id, g.goods_number AS storage, o.goods_attr, IFNULL(b.brand_name, \'\') AS brand_name, ' . 'g.goods_thumb, oi.order_sn,oi.extension_code as oi_extension_code,oi.extension_id ' . 'FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS o ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON o.goods_id = g.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON g.brand_id = b.brand_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ON oi.order_id = o.order_id ' . ('WHERE o.order_id = \'' . $order['order_id'] . '\' ');
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$sql = 'SELECT ret_id FROM ' . $GLOBALS['ecs']->table('order_return') . (' WHERE rec_id = \'' . $row['rec_id'] . '\'');
		$row['ret_id'] = $GLOBALS['db']->getOne($sql);

		if ($row['is_real'] == 0) {
			$filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $GLOBALS['_CFG']['lang'] . '.php';

			if (file_exists($filename)) {
				include_once $filename;

				if (!empty($GLOBALS['_LANG'][$row['extension_code'] . '_link'])) {
					$row['goods_name'] = $row['goods_name'] . sprintf($GLOBALS['_LANG'][$row['extension_code'] . '_link'], $row['goods_id'], $row['order_sn']);
				}
			}
		}

		if (0 < $row['product_id']) {
			$products = get_warehouse_id_attr_number($row['goods_id'], $row['goods_attr_id'], $row['ru_id'], $row['warehouse_id'], $row['area_id'], $row['model_attr']);
			$row['storage'] = $products['product_number'];
		}
		else if ($row['model_inventory'] == 1) {
			$row['storage'] = get_warehouse_area_goods($row['warehouse_id'], $row['goods_id'], 'warehouse_goods');
		}
		else if ($row['model_inventory'] == 2) {
			$row['storage'] = get_warehouse_area_goods($row['area_id'], $row['goods_id'], 'warehouse_area_goods');
		}

		$row['formated_subtotal'] = price_format($row['goods_price'] * $row['goods_number']);
		$row['formated_goods_price'] = price_format($row['goods_price']);
		$goods_attr[] = explode(' ', trim($row['goods_attr']));

		if ($row['extension_code'] == 'package_buy') {
			$row['storage'] = '';
			$row['brand_name'] = '';
			$row['package_goods_list'] = get_package_goods_list($row['goods_id']);
		}

		$row['product_id'] = empty($row['product_id']) ? 0 : $row['product_id'];

		if (empty($row['product_sn'])) {
			if ($row['model_attr'] == 1) {
				$table = 'products_warehouse';
			}
			else if ($row['model_attr'] == 2) {
				$table = 'products_area';
			}
			else {
				$table = 'products';
			}

			$sql = 'SELECT product_sn FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE product_id = \'' . $row['product_id'] . '\'';
			$row['product_sn'] = $GLOBALS['db']->getOne($sql);
		}

		$row['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$row['goods_img'] = get_image_path($row['goods_id'], $row['goods_img'], true);
		$trade_id = find_snapshot($row['order_sn'], $row['goods_id']);

		if ($trade_id) {
			$row['trade_url'] = '../trade_snapshot.php?act=trade&tradeId=' . $trade_id . '&snapshot=true';
		}

		$row['back_order'] = return_order_info($row['ret_id']);
		$row['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

		if ($row['extension_code'] == 'package_buy') {
			$row['storage'] = '';
			$row['brand_name'] = '';
			$row['package_goods_list'] = get_package_goods_list($row['goods_id']);
			$activity = get_goods_activity_info($row['goods_id'], array('act_id', 'activity_thumb'));

			if ($activity) {
				$row['goods_thumb'] = get_image_path($row['goods_id'], $row['activity_thumb'], true);
			}
		}

		$sql = 'SELECT is_reality, is_return, is_fast FROM ' . $GLOBALS['ecs']->table('goods_extend') . ' WHERE goods_id = \'' . $row['goods_id'] . '\' LIMIT 1';
		$goods_extend = $GLOBALS['db']->getRow($sql);
		if ($row['is_reality'] == -1 && $goods_extend) {
			$row['is_reality'] = $goods_extend['is_reality'];
		}

		if ($goods_extend['is_return'] == -1 && $goods_extend) {
			$row['is_return'] = $goods_extend['is_return'];
		}

		if ($goods_extend['is_fast'] == -1 && $goods_extend) {
			$row['is_fast'] = $goods_extend['is_fast'];
		}

		$sql = 'SELECT ret_id FROM ' . $GLOBALS['ecs']->table('order_return') . ' WHERE rec_id = \'' . $row['rec_id'] . '\'';
		$row['ret_id'] = $GLOBALS['db']->getOne($sql);
		$row['back_order'] = return_order_info($row['ret_id']);
		$goods_list[] = $row;
	}

	$attr = array();
	$arr = array();

	foreach ($goods_attr as $index => $array_val) {
		foreach ($array_val as $value) {
			$arr = explode(':', $value);
			$attr[$index][] = array('name' => $arr[0], 'value' => $arr[1]);
		}
	}

	return array('goods_list' => $goods_list, 'attr' => $attr);
}

function get_package_goods_list($package_id)
{
	$sql = "SELECT pg.goods_id, g.goods_name, (CASE WHEN pg.product_id > 0 THEN p.product_number ELSE g.goods_number END) AS goods_number, p.goods_attr, p.product_id, pg.goods_number AS\r\n            order_goods_number, g.goods_sn, g.is_real, p.product_sn\r\n            FROM " . $GLOBALS['ecs']->table('package_goods') . " AS pg\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON pg.goods_id = g.goods_id\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('products') . (" AS p ON pg.product_id = p.product_id\r\n            WHERE pg.package_id = '" . $package_id . '\'');
	$resource = $GLOBALS['db']->query($sql);

	if (!$resource) {
		return array();
	}

	$row = array();
	$good_product_str = '';

	while ($_row = $GLOBALS['db']->fetch_array($resource)) {
		if (0 < $_row['product_id']) {
			$good_product_str .= ',' . $_row['goods_id'];
			$_row['g_p'] = $_row['goods_id'] . '_' . $_row['product_id'];
		}
		else {
			$_row['g_p'] = $_row['goods_id'];
		}

		$row[] = $_row;
	}

	$good_product_str = trim($good_product_str, ',');
	unset($resource);
	unset($_row);
	unset($sql);

	if ($good_product_str != '') {
		$sql = "SELECT ga.goods_attr_id, ga.attr_value, ga.attr_price, a.attr_name\r\n                FROM " . $GLOBALS['ecs']->table('goods_attr') . ' AS ga, ' . $GLOBALS['ecs']->table('attribute') . (" AS a\r\n                WHERE a.attr_id = ga.attr_id\r\n                AND a.attr_type = 1\r\n                AND goods_id IN (" . $good_product_str . ') ORDER BY a.sort_order, a.attr_id, ga.goods_attr_id');
		$result_goods_attr = $GLOBALS['db']->getAll($sql);
		$_goods_attr = array();

		foreach ($result_goods_attr as $value) {
			$_goods_attr[$value['goods_attr_id']] = $value;
		}
	}

	$format[0] = '%s:%s[%d] <br>';
	$format[1] = '%s--[%d]';

	foreach ($row as $key => $value) {
		if ($value['goods_attr'] != '') {
			$goods_attr_array = explode('|', $value['goods_attr']);
			$goods_attr = array();

			foreach ($goods_attr_array as $_attr) {
				$goods_attr[] = sprintf($format[0], $_goods_attr[$_attr]['attr_name'], $_goods_attr[$_attr]['attr_value'], $_goods_attr[$_attr]['attr_price']);
			}

			$row[$key]['goods_attr_str'] = implode('', $goods_attr);
		}

		$row[$key]['goods_name'] = sprintf($format[1], $value['goods_name'], $value['order_goods_number']);
	}

	return $row;
}

function order_delivery_num($order_id, $goods_id, $product_id = 0)
{
	$sql = "SELECT SUM(G.send_number) AS sums\r\n            FROM " . $GLOBALS['ecs']->table('delivery_goods') . ' AS G, ' . $GLOBALS['ecs']->table('delivery_order') . " AS O\r\n            WHERE O.delivery_id = G.delivery_id\r\n            AND O.status = 0\r\n            AND O.order_id = " . $order_id . "\r\n            AND G.extension_code <> \"package_buy\"\r\n            AND G.goods_id = " . $goods_id;
	$sql .= 0 < $product_id ? ' AND G.product_id = \'' . $product_id . '\'' : '';
	$sum = $GLOBALS['db']->getOne($sql);

	if (empty($sum)) {
		$sum = 0;
	}

	return $sum;
}

function order_deliveryed($order_id)
{
	$return_res = 0;

	if (empty($order_id)) {
		return $return_res;
	}

	$sql = "SELECT COUNT(delivery_id)\r\n            FROM " . $GLOBALS['ecs']->table('delivery_order') . "\r\n            WHERE order_id = '" . $order_id . "'\r\n            AND status = 0";
	$sum = $GLOBALS['db']->getOne($sql);

	if ($sum) {
		$return_res = 1;
	}

	return $return_res;
}

function update_order_goods($order_id, $_sended, $goods_list = array())
{
	if (!is_array($_sended) || empty($order_id)) {
		return false;
	}

	foreach ($_sended as $key => $value) {
		if (is_array($value)) {
			if (!is_array($goods_list)) {
				$goods_list = array();
			}

			foreach ($goods_list as $goods) {
				if ($key != $goods['rec_id'] || (!isset($goods['package_goods_list']) || !is_array($goods['package_goods_list']))) {
					continue;
				}

				$goods['package_goods_list'] = package_goods($goods['package_goods_list'], $goods['goods_number'], $goods['order_id'], $goods['extension_code'], $goods['goods_id']);
				$pg_is_end = true;

				foreach ($goods['package_goods_list'] as $pg_key => $pg_value) {
					if ($pg_value['order_send_number'] != $pg_value['sended']) {
						$pg_is_end = false;
						break;
					}
				}

				if ($pg_is_end) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . ("\r\n                            SET send_number = goods_number\r\n                            WHERE order_id = '" . $order_id . "'\r\n                            AND goods_id = '") . $goods['goods_id'] . '\' ';
					$GLOBALS['db']->query($sql, 'SILENT');
				}
			}
		}
		else if (!is_array($value)) {
			foreach ($goods_list as $goods) {
				if ($goods['rec_id'] == $key && $goods['is_real'] == 1) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . ("\r\n                            SET send_number = send_number + " . $value . "\r\n                            WHERE order_id = '" . $order_id . "'\r\n                            AND rec_id = '" . $key . '\' ');
					$GLOBALS['db']->query($sql, 'SILENT');
					break;
				}
			}
		}
	}

	return true;
}

function update_order_virtual_goods($order_id, $_sended, $virtual_goods)
{
	if (!is_array($_sended) || empty($order_id)) {
		return false;
	}

	if (empty($virtual_goods)) {
		return true;
	}
	else if (!is_array($virtual_goods)) {
		return false;
	}

	foreach ($virtual_goods as $goods) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . "\r\n                SET send_number = send_number + '" . $goods['num'] . "'\r\n                WHERE order_id = '" . $order_id . "'\r\n                AND goods_id = '" . $goods['goods_id'] . '\' ';

		if (!$GLOBALS['db']->query($sql, 'SILENT')) {
			return false;
		}
	}

	return true;
}

function get_order_finish($order_id)
{
	$return_res = 0;

	if (empty($order_id)) {
		return $return_res;
	}

	$sql = "SELECT COUNT(rec_id)\r\n            FROM " . $GLOBALS['ecs']->table('order_goods') . "\r\n            WHERE order_id = '" . $order_id . "'\r\n            AND goods_number > send_number";
	$sum = $GLOBALS['db']->getOne($sql);

	if (empty($sum)) {
		$return_res = 1;
	}

	return $return_res;
}

function get_all_delivery_finish($order_id)
{
	$return_res = 0;

	if (empty($order_id)) {
		return $return_res;
	}

	if (!get_order_finish($order_id)) {
		return $return_res;
	}
	else {
		$sql = "SELECT COUNT(delivery_id)\r\n                FROM " . $GLOBALS['ecs']->table('delivery_order') . ("\r\n                WHERE order_id = '" . $order_id . "'\r\n                AND status = 2 ");
		$sum = $GLOBALS['db']->getOne($sql);

		if (empty($sum)) {
			$return_res = 1;
		}
		else {
			$sql = "SELECT COUNT(delivery_id)\r\n            FROM " . $GLOBALS['ecs']->table('delivery_order') . ("\r\n            WHERE order_id = '" . $order_id . "'\r\n            AND status <> 1 ");
			$_sum = $GLOBALS['db']->getOne($sql);

			if ($_sum == $sum) {
				$return_res = -2;
			}
			else {
				$return_res = -1;
			}
		}
	}

	return $return_res;
}

function trim_array_walk(&$array_value)
{
	if (is_array($array_value)) {
		array_walk($array_value, 'trim_array_walk');
	}
	else {
		$array_value = trim($array_value);
	}
}

function intval_array_walk(&$array_value)
{
	if (is_array($array_value)) {
		array_walk($array_value, 'intval_array_walk');
	}
	else {
		$array_value = intval($array_value);
	}
}

function del_order_delivery($order_id)
{
	$return_res = 0;

	if (empty($order_id)) {
		return $return_res;
	}

	$sql = "DELETE O, G\r\n            FROM " . $GLOBALS['ecs']->table('delivery_order') . ' AS O, ' . $GLOBALS['ecs']->table('delivery_goods') . " AS G\r\n            WHERE O.order_id = '" . $order_id . "'\r\n            AND O.status = 0\r\n            AND O.delivery_id = G.delivery_id";
	$query = $GLOBALS['db']->query($sql, 'SILENT');

	if ($query) {
		$return_res = 1;
	}

	return $return_res;
}

function del_delivery($order_id, $action_array)
{
	$return_res = 0;
	if (empty($order_id) || empty($action_array)) {
		return $return_res;
	}

	$query_delivery = 1;
	$query_back = 1;

	if (in_array('delivery', $action_array)) {
		$sql = "DELETE O, G\r\n                FROM " . $GLOBALS['ecs']->table('delivery_order') . ' AS O, ' . $GLOBALS['ecs']->table('delivery_goods') . " AS G\r\n                WHERE O.order_id = '" . $order_id . "'\r\n                AND O.delivery_id = G.delivery_id";
		$query_delivery = $GLOBALS['db']->query($sql, 'SILENT');
	}

	if (in_array('back', $action_array)) {
		$sql = "DELETE O, G\r\n                FROM " . $GLOBALS['ecs']->table('back_order') . ' AS O, ' . $GLOBALS['ecs']->table('back_goods') . " AS G\r\n                WHERE O.order_id = '" . $order_id . "'\r\n                AND O.back_id = G.back_id";
		$query_back = $GLOBALS['db']->query($sql, 'SILENT');
	}

	if ($query_delivery && $query_back) {
		$return_res = 1;
	}

	return $return_res;
}

function delivery_list()
{
	$where = 'WHERE 1 ';
	$adminru = get_admin_ru_id();

	if (0 < $adminru['ru_id']) {
		$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . ' WHERE og.order_id = do.order_id LIMIT 1) = \'' . $adminru['ru_id'] . '\' ';
	}

	$result = get_filter();

	if ($result === false) {
		$aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;
		$filter['delivery_sn'] = empty($_REQUEST['delivery_sn']) ? '' : trim($_REQUEST['delivery_sn']);
		$filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
		$filter['order_id'] = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
		$filter['goods_id'] = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
		if ($aiax == 1 && !empty($_REQUEST['consignee'])) {
			$_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
		}

		$filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
		$filter['status'] = isset($_REQUEST['status']) ? $_REQUEST['status'] : -1;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'do.update_time' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

		if ($filter['order_sn']) {
			$where .= ' AND do.order_sn LIKE \'%' . mysql_like_quote($filter['order_sn']) . '%\'';
		}

		if ($filter['goods_id']) {
			$where .= ' AND (SELECT dg.goods_id FROM ' . $GLOBALS['ecs']->table('delivery_goods') . ' AS dg WHERE dg.delivery_id = do.delivery_id LIMIT 1) = \'' . $filter['goods_id'] . '\' ';
		}

		if ($filter['consignee']) {
			$where .= ' AND do.consignee LIKE \'%' . mysql_like_quote($filter['consignee']) . '%\'';
		}

		if ($filter['delivery_sn']) {
			$where .= ' AND do.delivery_sn LIKE \'%' . mysql_like_quote($filter['delivery_sn']) . '%\'';
		}

		$admin_info = admin_info();

		if (0 < $admin_info['agency_id']) {
			$where .= ' AND do.agency_id = \'' . $admin_info['agency_id'] . '\' ';
		}

		if (0 < $admin_info['suppliers_id']) {
			$where .= ' AND do.suppliers_id = \'' . $admin_info['suppliers_id'] . '\' ';
		}

		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);
		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 10;
			}
		}

		if (-1 < $filter['status']) {
			$where .= ' AND do.status = \'' . $filter['status'] . '\' ';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('delivery_order') . ' as do ' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = "SELECT do.delivery_id, do.delivery_sn, do.order_sn, do.order_id, do.add_time, do.action_user, do.consignee, do.country,\r\n                       do.province, do.city, do.district, do.tel, do.status, do.update_time, do.email, do.suppliers_id\r\n                FROM " . $GLOBALS['ecs']->table('delivery_order') . ' as do ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . "\r\n                LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ', ' . $filter['page_size'] . ' ';
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$suppliers_list = get_suppliers_list();
	$_suppliers_list = array();

	foreach ($suppliers_list as $value) {
		$_suppliers_list[$value['suppliers_id']] = $value['suppliers_name'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$row[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
		$row[$key]['update_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['update_time']);

		if ($value['status'] == 1) {
			$row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][1];
		}
		else if ($value['status'] == 2) {
			$row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][2];
		}
		else {
			$row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][0];
		}

		$row[$key]['suppliers_name'] = isset($_suppliers_list[$value['suppliers_id']]) ? $_suppliers_list[$value['suppliers_id']] : '';
		$sql = 'SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id = \'' . $value['order_id'] . '\' LIMIT 0,1';
		$ru_id = $GLOBALS['db']->getOne($sql);
		$row[$key]['ru_name'] = get_shop_name($ru_id, 1);
	}

	$arr = array('delivery' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function back_list()
{
	$where = 'WHERE 1 ';
	$adminru = get_admin_ru_id();

	if (0 < $adminru['ru_id']) {
		$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . ' WHERE og.order_id = bo.order_id LIMIT 1) = \'' . $adminru['ru_id'] . '\' ';
	}

	$result = false;

	if ($result === false) {
		$aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;
		$filter['delivery_sn'] = empty($_REQUEST['delivery_sn']) ? '' : trim($_REQUEST['delivery_sn']);
		$filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
		$filter['order_id'] = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
		if ($aiax == 1 && !empty($_REQUEST['consignee'])) {
			$_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
		}

		$filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'bo.update_time' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

		if ($filter['order_sn']) {
			$where .= ' AND bo.order_sn LIKE \'%' . mysql_like_quote($filter['order_sn']) . '%\'';
		}

		if ($filter['consignee']) {
			$where .= ' AND bo.consignee LIKE \'%' . mysql_like_quote($filter['consignee']) . '%\'';
		}

		if ($filter['delivery_sn']) {
			$where .= ' AND bo.delivery_sn LIKE \'%' . mysql_like_quote($filter['delivery_sn']) . '%\'';
		}

		$admin_info = admin_info();

		if (0 < $admin_info['agency_id']) {
			$where .= ' AND bo.agency_id = \'' . $admin_info['agency_id'] . '\' ';
		}

		if (0 < $admin_info['suppliers_id']) {
			$where .= ' AND bo.suppliers_id = \'' . $admin_info['suppliers_id'] . '\' ';
		}

		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);
		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('back_order') . ' as bo ' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = "SELECT bo.back_id, bo.delivery_sn, bo.order_sn, bo.order_id, bo.add_time, bo.action_user, bo.consignee, bo.country,\r\n                       bo.province, bo.city, bo.district, bo.tel, bo.status, bo.update_time, bo.email, bo.return_time\r\n                FROM " . $GLOBALS['ecs']->table('back_order') . ' as bo ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . "\r\n                LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ', ' . $filter['page_size'] . ' ';
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$row[$key]['return_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['return_time']);
		$row[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
		$row[$key]['update_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['update_time']);

		if ($value['status'] == 1) {
			$row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][1];
		}
		else {
			$row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][0];
		}
	}

	$arr = array('back' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function delivery_order_info($delivery_id, $delivery_sn = '')
{
	$return_order = array();
	if (empty($delivery_id) || !is_numeric($delivery_id)) {
		return $return_order;
	}

	$where = '';
	$admin_info = admin_info();

	if (0 < $admin_info['agency_id']) {
		$where .= ' AND agency_id = \'' . $admin_info['agency_id'] . '\' ';
	}

	if (0 < $admin_info['suppliers_id']) {
		$where .= ' AND suppliers_id = \'' . $admin_info['suppliers_id'] . '\' ';
	}

	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('delivery_order');

	if (0 < $delivery_id) {
		$sql .= ' WHERE delivery_id = \'' . $delivery_id . '\'';
	}
	else {
		$sql .= ' WHERE delivery_sn = \'' . $delivery_sn . '\'';
	}

	$sql .= $where;
	$sql .= ' LIMIT 0, 1';
	$delivery = $GLOBALS['db']->getRow($sql);

	if ($delivery) {
		$delivery['formated_insure_fee'] = price_format($delivery['insure_fee'], false);
		$delivery['formated_shipping_fee'] = price_format($delivery['shipping_fee'], false);
		$delivery['formated_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $delivery['add_time']);
		$delivery['formated_update_time'] = local_date($GLOBALS['_CFG']['time_format'], $delivery['update_time']);
		$return_order = $delivery;
	}

	return $return_order;
}

function back_order_info($back_id)
{
	$return_order = array();
	if (empty($back_id) || !is_numeric($back_id)) {
		return $return_order;
	}

	$where = '';
	$admin_info = admin_info();

	if (0 < $admin_info['agency_id']) {
		$where .= ' AND agency_id = \'' . $admin_info['agency_id'] . '\' ';
	}

	if (0 < $admin_info['suppliers_id']) {
		$where .= ' AND suppliers_id = \'' . $admin_info['suppliers_id'] . '\' ';
	}

	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('back_order') . ("\r\n            WHERE back_id = '" . $back_id . "'\r\n            " . $where . "\r\n            LIMIT 0, 1");
	$back = $GLOBALS['db']->getRow($sql);

	if ($back) {
		$back['formated_insure_fee'] = price_format($back['insure_fee'], false);
		$back['formated_shipping_fee'] = price_format($back['shipping_fee'], false);
		$back['formated_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $back['add_time']);
		$back['formated_update_time'] = local_date($GLOBALS['_CFG']['time_format'], $back['update_time']);
		$back['formated_return_time'] = local_date($GLOBALS['_CFG']['time_format'], $back['return_time']);
		$return_order = $back;
	}

	return $return_order;
}

function package_goods(&$package_goods, $goods_number, $order_id, $extension_code, $package_id)
{
	$return_array = array();
	if (count($package_goods) == 0 || !is_numeric($goods_number)) {
		return $return_array;
	}

	foreach ($package_goods as $key => $value) {
		$return_array[$key] = $value;
		$return_array[$key]['order_send_number'] = $value['order_goods_number'] * $goods_number;
		$return_array[$key]['sended'] = package_sended($package_id, $value['goods_id'], $order_id, $extension_code, $value['product_id']);
		$return_array[$key]['send'] = $value['order_goods_number'] * $goods_number - $return_array[$key]['sended'];
		$return_array[$key]['storage'] = $value['goods_number'];

		if ($return_array[$key]['send'] <= 0) {
			$return_array[$key]['send'] = $GLOBALS['_LANG']['act_good_delivery'];
			$return_array[$key]['readonly'] = 'readonly="readonly"';
		}

		if ($return_array[$key]['storage'] <= 0 && $GLOBALS['_CFG']['use_storage'] == '1') {
			$return_array[$key]['send'] = $GLOBALS['_LANG']['act_good_vacancy'];
			$return_array[$key]['readonly'] = 'readonly="readonly"';
		}
	}

	return $return_array;
}

function package_sended($package_id, $goods_id, $order_id, $extension_code, $product_id = 0)
{
	if (empty($package_id) || empty($goods_id) || empty($order_id) || empty($extension_code)) {
		return false;
	}

	$sql = "SELECT SUM(DG.send_number)\r\n            FROM " . $GLOBALS['ecs']->table('delivery_goods') . ' AS DG, ' . $GLOBALS['ecs']->table('delivery_order') . (" AS o\r\n            WHERE o.delivery_id = DG.delivery_id\r\n            AND o.status IN (0, 2)\r\n            AND o.order_id = '" . $order_id . "'\r\n            AND DG.parent_id = '" . $package_id . "'\r\n            AND DG.goods_id = '" . $goods_id . "'\r\n            AND DG.extension_code = '" . $extension_code . '\'');
	$sql .= 0 < $product_id ? ' AND DG.product_id = \'' . $product_id . '\'' : '';
	$send = $GLOBALS['db']->getOne($sql);
	return empty($send) ? 0 : $send;
}

function change_order_goods_storage_split($order_id, $_sended, $goods_list = array())
{
	if (!is_array($_sended) || empty($order_id)) {
		return false;
	}

	foreach ($_sended as $key => $value) {
		if (is_array($value)) {
			if (!is_array($goods_list)) {
				$goods_list = array();
			}

			foreach ($goods_list as $goods) {
				if ($key != $goods['rec_id'] || (!isset($goods['package_goods_list']) || !is_array($goods['package_goods_list']))) {
					continue;
				}

				foreach ($goods['package_goods_list'] as $package_goods) {
					if (!isset($value[$package_goods['goods_id']])) {
						continue;
					}

					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . "\r\n                            SET goods_number = goods_number - '" . $value[$package_goods['goods_id']] . "'\r\n                            WHERE goods_id = '" . $package_goods['goods_id'] . '\' ';
					$GLOBALS['db']->query($sql);
				}
			}
		}
		else if (!is_array($value)) {
			foreach ($goods_list as $goods) {
				if ($goods['rec_id'] == $key && $goods['is_real'] == 1) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . "\r\n                            SET goods_number = goods_number - '" . $value . "'\r\n                            WHERE goods_id = '" . $goods['goods_id'] . '\' ';
					$GLOBALS['db']->query($sql, 'SILENT');
					break;
				}
			}
		}
	}

	return true;
}

function package_virtual_card_shipping($goods, $order_sn)
{
	if (!is_array($goods)) {
		return false;
	}

	include_once ROOT_PATH . 'includes/lib_code.php';

	foreach ($goods as $virtual_goods_key => $virtual_goods_value) {
		$sql = "SELECT card_id, card_sn, card_password, end_date, crc32\r\n                FROM " . $GLOBALS['ecs']->table('virtual_card') . "\r\n                WHERE goods_id = '" . $virtual_goods_value['goods_id'] . "'\r\n                AND is_saled = 0\r\n                LIMIT " . $virtual_goods_value['num'];
		$arr = $GLOBALS['db']->getAll($sql);

		if (count($arr) == 0) {
			continue;
		}

		$card_ids = array();
		$cards = array();

		foreach ($arr as $virtual_card) {
			$card_info = array();
			if ($virtual_card['crc32'] == 0 || $virtual_card['crc32'] == crc32(AUTH_KEY)) {
				$card_info['card_sn'] = decrypt($virtual_card['card_sn']);
				$card_info['card_password'] = decrypt($virtual_card['card_password']);
			}
			else if ($virtual_card['crc32'] == crc32(OLD_AUTH_KEY)) {
				$card_info['card_sn'] = decrypt($virtual_card['card_sn'], OLD_AUTH_KEY);
				$card_info['card_password'] = decrypt($virtual_card['card_password'], OLD_AUTH_KEY);
			}
			else {
				return false;
			}

			$card_info['end_date'] = date($GLOBALS['_CFG']['date_format'], $virtual_card['end_date']);
			$card_ids[] = $virtual_card['card_id'];
			$cards[] = $card_info;
		}

		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('virtual_card') . ' SET ' . 'is_saled = 1 ,' . ('order_sn = \'' . $order_sn . '\' ') . 'WHERE ' . db_create_in($card_ids, 'card_id');

		if (!$GLOBALS['db']->query($sql)) {
			return false;
		}

		$sql = 'SELECT order_id, order_sn, consignee, email FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_sn = \'' . $order_sn . '\'');
		$order = $GLOBALS['db']->GetRow($sql);
		$cfg = $GLOBALS['_CFG']['send_ship_email'];

		if ($cfg == '1') {
			$GLOBALS['smarty']->assign('virtual_card', $cards);
			$GLOBALS['smarty']->assign('order', $order);
			$GLOBALS['smarty']->assign('goods', $virtual_goods_value);
			$GLOBALS['smarty']->assign('send_time', date('Y-m-d H:i:s'));
			$GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
			$GLOBALS['smarty']->assign('send_date', date('Y-m-d'));
			$GLOBALS['smarty']->assign('sent_date', date('Y-m-d'));
			$tpl = get_mail_template('virtual_card');
			$content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
			send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
		}
	}

	return true;
}

function delivery_return_goods($delivery_id, $delivery_order)
{
	$goods_sql = "SELECT *\r\n                 FROM " . $GLOBALS['ecs']->table('delivery_goods') . "\r\n                 WHERE delivery_id = " . $delivery_order['delivery_id'];
	$goods_list = $GLOBALS['db']->getAll($goods_sql);

	foreach ($goods_list as $key => $val) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . ' SET send_number = send_number-\'' . $goods_list[$key]['send_number'] . '\'' . ' WHERE order_id = \'' . $delivery_order['order_id'] . '\' AND goods_id = \'' . $goods_list[$key]['goods_id'] . '\' LIMIT 1';
		$GLOBALS['db']->query($sql);
	}

	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET shipping_status = \'0\' , order_status = 1' . ' WHERE order_id = \'' . $delivery_order['order_id'] . '\' LIMIT 1';
	$GLOBALS['db']->query($sql);
}

function del_order_invoice_no($order_id, $delivery_invoice_no)
{
	$sql = "SELECT invoice_no\r\n            FROM " . $GLOBALS['ecs']->table('order_info') . ("\r\n            WHERE order_id = '" . $order_id . '\'');
	$order_invoice_no = $GLOBALS['db']->getOne($sql);

	if (empty($order_invoice_no)) {
		return NULL;
	}

	$order_array = explode(',', $order_invoice_no);
	$delivery_array = explode(',', $delivery_invoice_no);

	foreach ($order_array as $key => $invoice_no) {
		if ($ii = array_search($invoice_no, $delivery_array)) {
			unset($order_array[$key]);
			unset($delivery_array[$ii]);
		}
	}

	$arr['invoice_no'] = implode(',', $order_array);
	update_order($order_id, $arr);
}

function get_site_root_url()
{
	return 'http://' . $_SERVER['HTTP_HOST'] . str_replace('/' . SELLER_PATH . '/order.php', '', PHP_SELF);
}

function download_orderlist($result)
{
	if (empty($result)) {
		return i('没有符合您要求的数据！^_^');
	}

	$data = i('订单号,商家名称,下单会员,下单时间,收货人,联系电话,地址,(+)商品总金额,(+)发票税额,(+)配送费用,(+)保价费用,(+)支付费用,总金额,(-)折扣金额,订单总金额,(-)余额金额,(-)积分金额,(-)红包金额,(-)优惠券,(-)储值卡,(-)已付款金额,应付金额,确认状态,付款状态,发货状态,订单来源,支付方式' . "\n");
	$count = count($result);

	for ($i = 0; $i < $count; $i++) {
		$order_sn = i('#' . $result[$i]['order_sn']);
		$order_user = i($result[$i]['buyer']);
		$order_time = i($result[$i]['short_order_time']);
		$consignee = i($result[$i]['consignee']);
		$tel = !empty($result[$i]['mobile']) ? i($result[$i]['mobile']) : i($result[$i]['tel']);
		$address = i(addslashes(str_replace(',', '，', '[' . $result[$i]['region'] . '] ' . $result[$i]['address'])));
		$order_amount = i($result[$i]['order_amount']);
		$goods_amount = i($result[$i]['goods_amount']);
		$shipping_fee = i($result[$i]['shipping_fee']);
		$insure_fee = i($result[$i]['insure_fee']);
		$pay_fee = i($result[$i]['pay_fee']);
		$surplus = i($result[$i]['surplus']);
		$money_paid = i($result[$i]['money_paid']);
		$integral_money = i($result[$i]['integral_money']);
		$bonus = i($result[$i]['bonus']);
		$tax = i($result[$i]['tax']);
		$discount = i($result[$i]['discount']);
		$coupons = i($result[$i]['coupons']);
		$value_card = i($result[$i]['value_card']);
		$order_status = i(preg_replace('/\\<.+?\\>/', '', $GLOBALS['_LANG']['os'][$result[$i]['order_status']]));
		$seller_name = i($result[$i]['user_name']);
		$pay_status = i($GLOBALS['_LANG']['ps'][$result[$i]['pay_status']]);
		$shipping_status = i($GLOBALS['_LANG']['ss'][$result[$i]['shipping_status']]);
		$froms = i($result[$i]['referer']);

		if ($froms == 'touch') {
			$froms = 'WAP';
		}

		$pay_name = i($result[$i]['pay_name']);
		$total_fee = i($result[$i]['total_fee']);
		$total_fee_order = i($result[$i]['total_fee_order']);
		$data .= $order_sn . ',' . $seller_name . ',' . $order_user . ',' . $order_time . ',' . $consignee . ',' . $tel . ',' . $address . ',' . $goods_amount . ',' . $tax . ',' . $shipping_fee . ',' . $insure_fee . ',' . $pay_fee . ',' . $total_fee . ',' . $discount . ',' . $total_fee_order . ',' . $surplus . ',' . $integral_money . ',' . $bonus . ',' . $coupons . ',' . $value_card . ',' . $money_paid . ',' . $order_amount . ',' . $order_status . ',' . $pay_status . ',' . $shipping_status . ',' . $froms . ',' . $pay_name . "\n";
	}

	return $data;
}

function i($strInput)
{
	return iconv('utf-8', 'gb2312', $strInput);
}

function curlGet($url, $timeout = 5, $header = '')
{
	$defaultHeader = "\$header = \"User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12\\r\\n\";\r\n        \$header.=\"Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\\r\\n\";\r\n        \$header.=\"Accept-language: zh-cn,zh;q=0.5\\r\\n\";\r\n        \$header.=\"Accept-Charset: utf-8;q=0.7,*;q=0.7\\r\\n\";";
	$header = empty($header) ? $defaultHeader : $header;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . 'includes/lib_goods.php';
require_once ROOT_PATH . SELLER_PATH . '/includes/lib_comment.php';
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'order');
$user_action_list = get_user_action_list($_SESSION['seller_id']);
$order_back_apply = get_merchants_permissions($user_action_list, 'order_back_apply');
$smarty->assign('order_back_apply', $order_back_apply);
$order_os_remove = get_merchants_permissions($user_action_list, 'order_os_remove');
$smarty->assign('order_os_remove', $order_os_remove);
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('primary_cat', $_LANG['04_order']);

if ($_REQUEST['act'] == 'order_query') {
	admin_priv('order_view');
	$smarty->assign('menu_select', array('action' => '04_order', 'current' => '02_order_list'));
	$tab_menu = array();
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['02_order_list'], 'href' => 'order.php?act=list');
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['03_order_query'], 'href' => 'order.php?act=order_query');
	$smarty->assign('tab_menu', $tab_menu);
	$smarty->assign('ur_here', $_LANG['03_order_query']);
	$smarty->assign('shipping_list', shipping_list());
	$smarty->assign('pay_list', payment_list());
	$smarty->assign('country_list', get_regions());
	$smarty->assign('selProvinces_list', get_regions(1, 1));
	$smarty->assign('os_list', get_status_list('order'));
	$smarty->assign('ps_list', get_status_list('payment'));
	$smarty->assign('ss_list', get_status_list('shipping'));
	$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'');

	if ($priv_str == 'all') {
		$smarty->assign('priv_str', $priv_str);
	}
	else {
		$smarty->assign('priv_str', $priv_str);
	}

	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	assign_query_info();
	$smarty->display('order_query.dwt');
}
else if ($_REQUEST['act'] == 'edit_auto_delivery_time') {
	check_authz_json('order_edit');
	$order_id = intval($_POST['id']);
	$delivery_time = json_str_iconv(trim($_POST['val']));
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . (' SET auto_delivery_time = \'' . $delivery_time . '\'') . (' WHERE order_id = \'' . $order_id . '\'');
	$GLOBALS['db']->query($sql);
	clear_cache_files();
	make_json_result($delivery_time);
}
else if ($_REQUEST['act'] == 'list') {
	admin_priv('order_view');
	$smarty->assign('primary_cat', $_LANG['04_order']);
	$smarty->assign('menu_select', array('action' => '04_order', 'current' => '02_order_list'));
	$tab_menu = array();
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['02_order_list'], 'href' => 'order.php?act=list');
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['03_order_query'], 'href' => 'order.php?act=order_query');
	$smarty->assign('tab_menu', $tab_menu);
	$smarty->assign('ur_here', $_LANG['02_order_list']);
	$smarty->assign('action_link3', array('href' => 'javascript:download_orderlist();', 'text' => $_LANG['11_order_export']));
	$smarty->assign('status_list', $_LANG['cs']);
	$smarty->assign('os_unconfirmed', OS_UNCONFIRMED);
	$smarty->assign('cs_await_pay', CS_AWAIT_PAY);
	$smarty->assign('cs_await_ship', CS_AWAIT_SHIP);
	$smarty->assign('full_page', 1);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$serch_type = isset($_GET['serch_type']) ? $_GET['serch_type'] : -1;
	$smarty->assign('serch_type', $serch_type);
	$order_list = order_list();
	$page_count_arr = array();
	$page_count_arr = seller_page($order_list, $_REQUEST['page']);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$smarty->assign('count_arr', $order_list['count_arr']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('sort_order_time', '<img src="images/sort_desc.gif">');
	$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'');

	if ($priv_str == 'all') {
		$smarty->assign('priv_str', $priv_str);
	}
	else {
		$smarty->assign('priv_str', $priv_str);
	}

	assign_query_info();
	$smarty->display('store_order.dwt');
}
else if ($_REQUEST['act'] == 'return_list') {
	admin_priv('order_back_apply');
	$smarty->assign('current', '12_back_apply');
	$smarty->assign('primary_cat', $_LANG['04_order']);
	$smarty->assign('ur_here', $_LANG['02_order_list']);
	$smarty->assign('full_page', 1);
	$order_list = return_order_list();
	$page_count_arr = seller_page($order_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	assign_query_info();
	$smarty->display('return_list.dwt');
}
else if ($_REQUEST['act'] == 'return_list_query') {
	admin_priv('order_view');
	$order_list = return_order_list();
	$page_count_arr = seller_page($order_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	make_json_result($smarty->fetch('return_list.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}
else if ($_REQUEST['act'] == 'ajax_download') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('is_stop' => 0);
	$page = !empty($_REQUEST['page_down']) ? intval($_REQUEST['page_down']) : 0;
	$page_count = !empty($_REQUEST['page_count']) ? intval($_REQUEST['page_count']) : 0;
	$order_list = order_list($page);
	$admin_id = get_admin_id();
	$merchants_download_content = read_static_cache('order_download_content_' . $admin_id);
	$merchants_download_content[] = $order_list;
	write_static_cache('order_download_content_' . $admin_id, $merchants_download_content);
	$result['page'] = $page;

	if ($page < $page_count) {
		$result['is_stop'] = 1;
		$result['next_page'] = $page + 1;
	}

	exit($json->encode($result));
}

if ($_REQUEST['act'] == 'order_download') {
	header('Content-Disposition: attachment; filename=订单导出-' . date('YmdHis') . '.zip');
	header('Content-Type: application/unknown');
	include_once 'includes/cls_phpzip.php';
	$admin_id = get_admin_id();
	$order_list = read_static_cache('order_download_content_' . $admin_id);
	$zip = new PHPZip();

	if (!empty($order_list)) {
		foreach ($order_list as $k => $order) {
			$k++;
			$content = download_orderlist($order['orders']);
			$zip->add_file($content, date('YmdHis') . '-' . $k . '.csv');
		}
	}

	$dir = ROOT_PATH . '/temp/static_caches/order_download_content_' . $admin_id . '.php';

	if (is_file($dir)) {
		@unlink($dir);
	}

	exit($zip->file());
}
else if ($_REQUEST['act'] == 'query') {
	admin_priv('order_view');
	$order_list = order_list();
	$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'');

	if ($priv_str == 'all') {
		$smarty->assign('priv_str', $priv_str);
	}
	else {
		$smarty->assign('priv_str', $priv_str);
	}

	$page_count_arr = seller_page($order_list, $_REQUEST['page']);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('store_order.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}
else if ($_REQUEST['act'] == 'info') {
	$smarty->assign('menu_select', array('action' => '04_order', 'current' => '02_order_list'));
	$smarty->assign('current', '02_order_list');
	$smarty->assign('primary_cat', $_LANG['04_order']);

	if (isset($_REQUEST['order_id'])) {
		$order_id = intval($_REQUEST['order_id']);
		$order = order_info($order_id);
	}
	else if (isset($_REQUEST['order_sn'])) {
		$order_sn = trim($_REQUEST['order_sn']);
		$order = order_info(0, $order_sn);
	}
	else {
		exit('invalid parameter');
	}

	if ($order['shipping_status'] == 2 && empty($order['confirm_take_time'])) {
		$sql = 'SELECT MAX(log_time) AS log_time FROM ' . $GLOBALS['ecs']->table('order_action') . ' WHERE order_id = \'' . $order['order_id'] . '\' AND shipping_status = \'' . SS_RECEIVED . '\'';
		$log_time = $GLOBALS['db']->getOne($sql, true);
		$log_other = array('confirm_take_time' => $log_time);
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $log_other, 'UPDATE', 'order_id = \'' . $order['order_id'] . '\'');
		$order['confirm_take_time'] = $log_time;
	}

	if (($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED || $order['order_status'] == OS_SPLITED) && $order['pay_status'] == PS_UNPAYED) {
		$pay_log = get_pay_log($order['order_id'], 1);
		if ($pay_log && $pay_log['is_paid'] == 0) {
			$payment = payment_info($order['pay_id']);
			$file_pay = ROOT_PATH . 'includes/modules/payment/' . $payment['pay_code'] . '.php';
			if ($payment && file_exists($file_pay)) {
				include_once $file_pay;

				if (class_exists($payment['pay_code'])) {
					$pay_obj = new $payment['pay_code']();
					$is_callable = array($pay_obj, 'query');

					if (is_callable($is_callable)) {
						$order_other = array('order_sn' => $order['order_sn'], 'log_id' => $pay_log['log_id']);
						$pay_obj->query($order_other);
						$sql = 'SELECT order_status, shipping_status, pay_status, pay_time FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $order['order_id'] . '\' LIMIT 1';
						$order_info = $GLOBALS['db']->getRow($sql);

						if ($order_info) {
							$order['order_status'] = $order_info['order_status'];
							$order['shipping_status'] = $order_info['shipping_status'];
							$order['pay_status'] = $order_info['pay_status'];
							$order['pay_time'] = $order_info['pay_time'];
						}
					}
				}
			}
		}
	}

	if ($order['ru_id'] != $adminru['ru_id']) {
		$Loaction = 'order.php?act=list';
		ecs_header('Location: ' . $Loaction . "\n");
		exit();
	}

	$sql = 'SELECT pay_code FROM ' . $GLOBALS['ecs']->table('payment') . ' WHERE pay_id = \'' . $order['pay_id'] . '\'';
	$pay_code = $GLOBALS['db']->getOne($sql, true);
	if ($pay_code == 'cod' || $pay_code == 'bank') {
		$smarty->assign('pay_code', 1);
	}
	else {
		$smarty->assign('pay_code', 0);
	}

	if ($order['order_status'] == OS_INVALID || $order['order_status'] == OS_CANCELED) {
		$order['can_remove'] = 1;
	}
	else {
		$order['can_remove'] = 0;
	}

	$order['delivery_id'] = $GLOBALS['db']->getOne('SELECT delivery_id FROM ' . $ecs->table('delivery_order') . ' WHERE order_sn = \'' . $order['order_sn'] . '\'', true);

	if ($_CFG['open_delivery_time'] == 1) {
		$sql = 'SELECT order_id, user_id, order_sn , order_status, shipping_status, pay_status, auto_delivery_time, add_time, pay_time, ' . 'order_amount, goods_amount, tax, invoice_type, shipping_fee, insure_fee, pay_fee, pack_fee, card_fee, shipping_time, ' . 'bonus, integral_money, coupons, discount, money_paid, surplus, confirm_take_time, tax_id ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $order['order_id'] . '\' LIMIT 1';
		$orderInfo = $GLOBALS['db']->GetRow($sql);
		$confirm_take_time = gmtime();
		if (($orderInfo['order_status'] == OS_CONFIRMED || $orderInfo['order_status'] == OS_SPLITED) && $orderInfo['shipping_status'] == SS_SHIPPED && $orderInfo['pay_status'] == PS_PAYED) {
			$delivery_time = $orderInfo['shipping_time'] + 24 * 3600 * $orderInfo['auto_delivery_time'];

			if ($delivery_time < $confirm_take_time) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET order_status = \'' . OS_SPLITED . '\', ' . 'shipping_status = \'' . SS_RECEIVED . '\', pay_status = \'' . PS_PAYED . ('\', confirm_take_time = \'' . $confirm_take_time . '\' WHERE order_id = \'') . $order['order_id'] . '\'';

				if ($GLOBALS['db']->query($sql)) {
					$note = $GLOBALS['_LANG']['self_motion_goods'];
					order_action($orderInfo['order_sn'], OS_SPLITED, SS_RECEIVED, PS_PAYED, $note, $GLOBALS['_LANG']['buyer'], 0, $confirm_take_time);
					$seller_id = $GLOBALS['db']->getOne('SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id = \'' . $order['order_id'] . '\'', true);
					$value_card = $GLOBALS['db']->getOne('SELECT use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . ' WHERE order_id = \'' . $order['order_id'] . '\'', true);
					$return_amount = get_order_return_amount($order['order_id']);
					$other = array('user_id' => $orderInfo['user_id'], 'seller_id' => $seller_id, 'order_id' => $orderInfo['order_id'], 'order_sn' => $orderInfo['order_sn'], 'order_status' => $orderInfo['order_status'], 'shipping_status' => SS_RECEIVED, 'pay_status' => $orderInfo['pay_status'], 'order_amount' => $orderInfo['order_amount'], 'return_amount' => $return_amount, 'goods_amount' => $orderInfo['goods_amount'], 'tax' => $orderInfo['tax'], 'tax_id' => $orderInfo['tax_id'], 'invoice_type' => $orderInfo['invoice_type'], 'shipping_fee' => $orderInfo['shipping_fee'], 'insure_fee' => $orderInfo['insure_fee'], 'pay_fee' => $orderInfo['pay_fee'], 'pack_fee' => $orderInfo['pack_fee'], 'card_fee' => $orderInfo['card_fee'], 'bonus' => $orderInfo['bonus'], 'integral_money' => $orderInfo['integral_money'], 'coupons' => $orderInfo['coupons'], 'discount' => $orderInfo['discount'], 'value_card' => $value_card, 'money_paid' => $orderInfo['money_paid'], 'surplus' => $orderInfo['surplus'], 'confirm_take_time' => $confirm_take_time);

					if ($seller_id) {
						get_order_bill_log($other);
					}
				}
			}
		}
	}

	if (empty($order)) {
		exit('order does not exist');
	}

	if (order_finished($order)) {
		admin_priv('order_view_finished');
	}
	else {
		admin_priv('order_view');
	}

	$sql = 'SELECT agency_id FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'');
	$agency_id = $db->getOne($sql);

	if (0 < $agency_id) {
		if ($order['agency_id'] != $agency_id) {
			sys_msg($_LANG['priv_error']);
		}
	}

	if (!empty($_COOKIE['ECSCP']['lastfilter'])) {
		$filter = unserialize(urldecode($_COOKIE['ECSCP']['lastfilter']));

		if (!empty($filter['composite_status'])) {
			$where = '';

			switch ($filter['composite_status']) {
			case CS_AWAIT_PAY:
				$where .= order_query_sql('await_pay');
				break;

			case CS_AWAIT_SHIP:
				$where .= order_query_sql('await_ship');
				break;

			case CS_FINISHED:
				$where .= order_query_sql('finished');
				break;

			default:
				if ($filter['composite_status'] != -1) {
					$where .= ' AND o.order_status = \'' . $filter['composite_status'] . '\' ';
				}
			}
		}
	}

	$sql = 'SELECT MAX(order_id) FROM ' . $ecs->table('order_info') . (' as o WHERE order_id < \'' . $order['order_id'] . '\'');

	if (0 < $agency_id) {
		$sql .= ' AND agency_id = \'' . $agency_id . '\'';
	}

	if (!empty($where)) {
		$sql .= $where;
	}

	$smarty->assign('prev_id', $db->getOne($sql));
	$sql = 'SELECT MIN(order_id) FROM ' . $ecs->table('order_info') . (' as o WHERE order_id > \'' . $order['order_id'] . '\'');

	if (0 < $agency_id) {
		$sql .= ' AND agency_id = \'' . $agency_id . '\'';
	}

	if (!empty($where)) {
		$sql .= $where;
	}

	$smarty->assign('next_id', $db->getOne($sql));

	if (0 < $order['user_id']) {
		$user = user_info($order['user_id']);

		if (!empty($user)) {
			$order['user_name'] = $user['user_name'];
		}
	}

	$sql = 'SELECT agency_id, agency_name FROM ' . $ecs->table('agency');
	$smarty->assign('agency_list', $db->getAll($sql));
	$order['region'] = get_user_region_address($order['order_id']);

	if ($order['order_amount'] < 0) {
		$order['money_refund'] = abs($order['order_amount']);
		$order['formated_money_refund'] = price_format(abs($order['order_amount']));
	}

	$order['order_time'] = local_date($_CFG['time_format'], $order['add_time']);
	$order['pay_time'] = 0 < $order['pay_time'] ? local_date($_CFG['time_format'], $order['pay_time']) : $_LANG['ps'][PS_UNPAYED];
	$order['shipping_time'] = 0 < $order['shipping_time'] ? local_date($_CFG['time_format'], $order['shipping_time']) : $_LANG['ss'][SS_UNSHIPPED];
	$order['confirm_take_time'] = 0 < $order['confirm_take_time'] ? local_date($_CFG['time_format'], $order['confirm_take_time']) : ($order['shipping_status'] == 1 ? $_LANG['not_confirm_order'] : $_LANG['ss'][SS_UNSHIPPED]);
	$order['status'] = $_LANG['os'][$order['order_status']] . ',' . $_LANG['ps'][$order['pay_status']] . ',' . $_LANG['ss'][$order['shipping_status']];
	$order['invoice_no'] = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $_LANG['ss'][SS_UNSHIPPED] : $order['invoice_no'];

	if ($order['from_ad'] == 0) {
		$order['referer'] = empty($order['referer']) ? $_LANG['from_self_site'] : $order['referer'];
	}
	else if ($order['from_ad'] == -1) {
		$order['referer'] = $_LANG['from_goods_js'] . ' (' . $_LANG['from'] . $order['referer'] . ')';
	}
	else {
		$ad_name = $db->getOne('SELECT ad_name FROM ' . $ecs->table('ad') . (' WHERE ad_id=\'' . $order['from_ad'] . '\''));
		$order['referer'] = $_LANG['from_ad_js'] . $ad_name . ' (' . $_LANG['from'] . $order['referer'] . ')';
	}

	$sql = 'SELECT action_note FROM ' . $ecs->table('order_action') . (' WHERE order_id = \'' . $order['order_id'] . '\' AND shipping_status = 1 ORDER BY log_time DESC');
	$order['invoice_note'] = $db->getOne($sql);
	$sql = 'SELECT shipping_code FROM ' . $ecs->table('shipping') . (' WHERE shipping_id = \'' . $order['shipping_id'] . '\'');

	if ($db->getOne($sql) == 'cac') {
		$sql = 'SELECT * FROM ' . $ecs->table('shipping_point') . ' WHERE id IN (SELECT point_id FROM ' . $ecs->table('order_info') . ' WHERE order_id=\'' . $order['order_id'] . '\')';
		$order['point'] = $db->getRow($sql);
	}

	$sql = 'SELECT stages_total,stages_one_price,is_stages FROM ' . $ecs->table('baitiao_log') . (' WHERE order_id = \'' . $order_id . '\'');
	$baitiao_info = $db->getRow($sql);

	if ($baitiao_info['is_stages'] == 1) {
		$order['is_stages'] = 1;
		$order['stages_total'] = $baitiao_info['stages_total'];
		$order['stages_one_price'] = $baitiao_info['stages_one_price'];
	}

	if ($order['invoice_type'] == 1) {
		$user_id = $order['user_id'];
		$sql = ' SELECT * FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
		$res = $db->getRow($sql);
		$region = array('province' => $res['province'], 'city' => $res['city'], 'district' => $res['district']);
		$res['region'] = get_area_region_info($region);
		$smarty->assign('vat_info', $res);
	}

	$weight_price = order_weight_price($order['order_id']);
	$order['total_weight'] = $weight_price['formated_weight'];
	$order['is_comment'] = 0;
	$sql = ' SELECT comment_id , add_time FROM' . $ecs->table('comment') . ' WHERE order_id = \'' . $order['order_id'] . '\' AND user_id = \'' . $order['user_id'] . '\'';
	$comment = $db->getRow($sql);

	if ($comment) {
		$order['is_comment'] = 1;
		$order['comment_time'] = 0 < $comment['add_time'] ? local_date($_CFG['time_format'], $order['add_time']) : '尚未评论';
	}

	$smarty->assign('order', $order);

	if (0 < $order['user_id']) {
		if (0 < $user['user_rank']) {
			$where = ' WHERE rank_id = \'' . $user['user_rank'] . '\' ';
		}
		else {
			$where = ' WHERE min_points <= ' . intval($user['rank_points']) . ' ORDER BY min_points DESC ';
		}

		$sql = 'SELECT rank_name FROM ' . $ecs->table('user_rank') . $where;
		$user['rank_name'] = $db->getOne($sql);
		$day = getdate();
		$today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
		$sql = 'SELECT COUNT(*) ' . 'FROM ' . $ecs->table('bonus_type') . ' AS bt, ' . $ecs->table('user_bonus') . ' AS ub ' . 'WHERE bt.type_id = ub.bonus_type_id ' . ('AND ub.user_id = \'' . $order['user_id'] . '\' ') . 'AND ub.order_id = 0 ' . ('AND bt.use_start_date <= \'' . $today . '\' ') . ('AND bt.use_end_date >= \'' . $today . '\'');
		$user['bonus_count'] = $db->getOne($sql);
		$smarty->assign('user', $user);
		$sql = 'SELECT * FROM ' . $ecs->table('user_address') . (' WHERE user_id = \'' . $order['user_id'] . '\'');
		$smarty->assign('address_list', $db->getAll($sql));
	}

	$sql = 'SELECT store_id,pick_code,take_time  FROM' . $ecs->table('store_order') . ' WHERE order_id = \'' . $order['order_id'] . '\'';
	$stores = $db->getRow($sql);
	$order['store_id'] = $stores['store_id'];
	$offline_store = array();

	if (0 < $order['store_id']) {
		$sql = 'SELECT o.*,p.region_name as province,c.region_name as city,d.region_name as district FROM' . $ecs->table('offline_store') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS p ON p.region_id = o.province ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS c ON c.region_id = o.city ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS d ON d.region_id = o.district WHERE o.id = \'' . $order['store_id'] . '\'';
		$offline_store = $db->getRow($sql);
		$offline_store['pick_code'] = $stores['pick_code'];
		$offline_store['take_time'] = $stores['take_time'];
	}

	$smarty->assign('offline_store', $offline_store);
	$goods_list = array();
	$goods_attr = array();
	$sql = "SELECT o.*, c.measure_unit, g.goods_number AS storage, g.model_inventory, g.model_attr as model_attr, o.goods_attr, g.suppliers_id,g.goods_thumb,\r\n            g.user_id AS ru_id, g.brand_id, g.give_integral, g.bar_code, IF(oi.extension_code != '', oi.extension_code, o.extension_code), oi.extension_id , o.extension_code as o_extension_code, oi.extension_code as oi_extension_code   \r\n            FROM " . $ecs->table('order_goods') . " AS o\r\n                LEFT JOIN " . $ecs->table('products') . " AS p\r\n                    ON p.product_id = o.product_id\r\n                LEFT JOIN " . $ecs->table('goods') . " AS g\r\n                    ON o.goods_id = g.goods_id\r\n                LEFT JOIN " . $ecs->table('category') . " AS c\r\n                    ON g.cat_id = c.cat_id\r\n\t\t\t\tLEFT JOIN " . $ecs->table('order_info') . (" AS oi \r\n\t\t\t\t\tON o.order_id = oi.order_id\t\r\n            WHERE o.order_id = '" . $order['order_id'] . '\'');
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		if ($row['is_real'] == 0) {
			$filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $_CFG['lang'] . '.php';

			if (file_exists($filename)) {
				include_once $filename;

				if (!empty($_LANG[$row['extension_code'] . '_link'])) {
					$row['goods_name'] = $row['goods_name'] . sprintf($_LANG[$row['extension_code'] . '_link'], $row['goods_id'], $order['order_sn']);
				}
			}
		}

		if ($row['model_inventory'] == 1) {
			$row['storage'] = get_warehouse_area_goods($row['warehouse_id'], $row['goods_id'], 'warehouse_goods');
		}
		else if ($row['model_inventory'] == 2) {
			$row['storage'] = get_warehouse_area_goods($row['area_id'], $row['goods_id'], 'warehouse_area_goods');
		}

		$row['give_integral'] = $row['give_integral'];
		$row['goods_amount'] = $row['goods_price'] * $row['goods_number'];
		$goods_con = get_con_goods_amount($row['goods_amount'], $row['goods_id'], 0, 0, $row['parent_id']);
		$goods_con['amount'] = explode(',', $goods_con['amount']);
		$row['amount'] = min($goods_con['amount']);
		$row['dis_amount'] = $row['goods_amount'] - $row['amount'];
		$row['discount_amount'] = price_format($row['dis_amount'], false);
		$products = get_warehouse_id_attr_number($row['goods_id'], $row['goods_attr_id'], $row['ru_id'], $row['warehouse_id'], $row['area_id'], $row['model_attr']);
		$row['goods_storage'] = $products['product_number'];

		if ($row['product_id']) {
			$row['bar_code'] = $products['bar_code'];
		}

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

		if (empty($row['product_sn'])) {
			$row['product_sn'] = $products['product_sn'];
		}

		$brand = get_goods_brand_info($row['brand_id']);
		$row['brand_name'] = $brand['brand_name'];
		$row['formated_subtotal'] = price_format($row['amount']);
		$row['formated_goods_price'] = price_format($row['goods_price']);
		$row['warehouse_name'] = $db->getOne('select region_name from ' . $ecs->table('region_warehouse') . ' where region_id = \'' . $row['warehouse_id'] . '\'');
		$goods_attr[] = explode(' ', trim($row['goods_attr']));

		if ($row['extension_code'] == 'package_buy') {
			$row['storage'] = '';
			$row['brand_name'] = '';
			$row['package_goods_list'] = get_package_goods($row['goods_id']);
		}

		$row['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$goods_list[] = $row;
	}

	$attr = array();
	$arr = array();

	foreach ($goods_attr as $index => $array_val) {
		foreach ($array_val as $value) {
			$arr = explode(':', $value);
			$attr[$index][] = array('name' => $arr[0], 'value' => $arr[1]);
		}
	}

	$smarty->assign('goods_attr', $attr);
	$smarty->assign('goods_list', $goods_list);
	$operable_list = operable_list($order);
	$smarty->assign('operable_list', $operable_list);
	$sql = 'SELECT agree_apply FROM ' . $ecs->table('order_return') . (' WHERE order_id = \'' . $order['order_id'] . '\'');
	$is_apply = $db->getOne($sql);
	$smarty->assign('is_apply', $is_apply);
	$sql = 'SELECT log_time  FROM ' . $ecs->table('order_action') . (' WHERE order_id = \'' . $order['order_id'] . '\' ');
	$res_time = local_date($_CFG['time_format'], $db->getOne($sql));
	$smarty->assign('res_time', $res_time);
	$act_list = array();
	$sql = 'SELECT * FROM ' . $ecs->table('order_action') . (' WHERE order_id = \'' . $order['order_id'] . '\' ORDER BY log_time DESC,action_id DESC');
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		$row['order_status'] = $_LANG['os'][$row['order_status']];
		$row['pay_status'] = $_LANG['ps'][$row['pay_status']];
		$row['shipping_status'] = $_LANG['ss'][$row['shipping_status']];
		$row['action_time'] = local_date($_CFG['time_format'], $row['log_time']);
		$act_list[] = $row;
	}

	$smarty->assign('action_list', $act_list);
	$smarty->assign('exist_real_goods', exist_real_goods($order['order_id']));
	if ($order['pay_status'] == 2 && $order['shipping_status'] == 0) {
		$sql = ' SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('store_order') . (' WHERE order_id = \'' . $order['order_id'] . '\' AND store_id > 0 ');
		$have_store_order = $GLOBALS['db']->getOne($sql);

		if ($have_store_order == 0) {
			$smarty->assign('can_set_grab_order', 1);
		}
	}

	$sql = 'select shop_name,country,province,city,district,shop_address,kf_tel from ' . $ecs->table('seller_shopinfo') . ' where ru_id=\'' . $order['ru_id'] . '\'';
	$store = $db->getRow($sql);
	$store['shop_name'] = get_shop_name($order['ru_id'], 1);

	if (isset($_GET['print'])) {
		$smarty->assign('shop_name', $store['shop_name']);
		$smarty->assign('shop_url', $ecs->seller_url());
		$smarty->assign('shop_address', $store['shop_address']);
		$smarty->assign('service_phone', $store['kf_tel']);
		$smarty->assign('print_time', local_date($_CFG['time_format']));
		$smarty->assign('action_user', $_SESSION['seller_name']);
		$smarty->template_dir = '../' . DATA_DIR;
		$smarty->display('order_print.html');
	}
	else if (isset($_GET['shipping_print'])) {
		if (get_print_type($adminru['ru_id'])) {
			$url = 'tp_api.php?act=kdniao_print&order_id=' . $order_id;
			ecs_header('Location: ' . $url . "\n");
			exit();
		}

		$region_array = array();
		$region = $db->getAll('SELECT region_id, region_name FROM ' . $ecs->table('region'));

		if (!empty($region)) {
			foreach ($region as $region_data) {
				$region_array[$region_data['region_id']] = $region_data['region_name'];
			}
		}

		$smarty->assign('shop_name', $store['shop_name']);
		$smarty->assign('order_id', $order_id);
		$smarty->assign('province', $region_array[$store['province']]);
		$smarty->assign('city', $region_array[$store['city']]);
		$smarty->assign('shop_address', $store['shop_address']);
		$smarty->assign('service_phone', $store['kf_tel']);
		$shipping = $db->getRow('SELECT * FROM ' . $ecs->table('shipping_tpl') . ' WHERE shipping_id = \'' . $order['shipping_id'] . '\' and ru_id=\'' . $adminru['ru_id'] . '\'');

		if ($shipping['print_model'] == 2) {
			$shipping['print_bg'] = empty($shipping['print_bg']) ? '' : get_site_root_url() . $shipping['print_bg'];

			if (!empty($shipping['print_bg'])) {
				$_size = @getimagesize($shipping['print_bg']);

				if ($_size != false) {
					$shipping['print_bg_size'] = array('width' => $_size[0], 'height' => $_size[1]);
				}
			}

			if (empty($shipping['print_bg_size'])) {
				$shipping['print_bg_size'] = array('width' => '1024', 'height' => '600');
			}

			$lable_box = array();
			$lable_box['t_shop_country'] = $region_array[$store['country']];
			$lable_box['t_shop_city'] = $region_array[$store['city']];
			$lable_box['t_shop_province'] = $region_array[$store['province']];
			$lable_box['t_shop_name'] = get_shop_name($order['ru_id'], 1);
			$lable_box['t_shop_district'] = '';
			$lable_box['t_shop_tel'] = $store['kf_tel'];
			$lable_box['t_shop_address'] = $store['shop_address'];
			$lable_box['t_customer_country'] = $region_array[$order['country']];
			$lable_box['t_customer_province'] = $region_array[$order['province']];
			$lable_box['t_customer_city'] = $region_array[$order['city']];
			$lable_box['t_customer_district'] = $region_array[$order['district']];
			$lable_box['t_customer_tel'] = $order['tel'];
			$lable_box['t_customer_mobel'] = $order['mobile'];
			$lable_box['t_customer_post'] = $order['zipcode'];
			$lable_box['t_customer_address'] = $order['address'];
			$lable_box['t_customer_name'] = $order['consignee'];
			$gmtime_utc_temp = gmtime();
			$lable_box['t_year'] = date('Y', $gmtime_utc_temp);
			$lable_box['t_months'] = date('m', $gmtime_utc_temp);
			$lable_box['t_day'] = date('d', $gmtime_utc_temp);
			$lable_box['t_order_no'] = $order['order_sn'];
			$lable_box['t_order_postscript'] = $order['postscript'];
			$lable_box['t_order_best_time'] = $order['best_time'];
			$lable_box['t_pigeon'] = '√';
			$lable_box['t_custom_content'] = '';
			$temp_config_lable = explode('||,||', $shipping['config_lable']);

			if (!is_array($temp_config_lable)) {
				$temp_config_lable[] = $shipping['config_lable'];
			}

			foreach ($temp_config_lable as $temp_key => $temp_lable) {
				$temp_info = explode(',', $temp_lable);

				if (is_array($temp_info)) {
					$temp_info[1] = $lable_box[$temp_info[0]];
				}

				$temp_config_lable[$temp_key] = implode(',', $temp_info);
			}

			$shipping['config_lable'] = implode('||,||', $temp_config_lable);
			$smarty->assign('shipping', $shipping);
			$smarty->display('print.dwt');
		}
		else if (!empty($shipping['shipping_print'])) {
			echo $smarty->fetch('str:' . $shipping['shipping_print']);
		}
		else {
			$shipping_code = $db->getOne('SELECT shipping_code FROM ' . $ecs->table('shipping') . ' WHERE shipping_id=\'' . $order['shipping_id'] . '\'');

			if ($order['referer'] == 'mobile') {
				$shipping_code = str_replace('ship_', '', $shipping_code);
			}

			if ($shipping_code) {
				include_once ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';
			}

			if (!empty($_LANG['shipping_print'])) {
				echo $smarty->fetch('str:' . $_LANG['shipping_print']);
			}
			else {
				echo $_LANG['no_print_shipping'];
			}
		}
	}
	else {
		$smarty->assign('ur_here', $_LANG['order_info']);
		$smarty->assign('action_link', array('href' => 'order.php?act=list&' . list_link_postfix(), 'text' => $_LANG['02_order_list']));
		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_return') . (' WHERE order_id = \'' . $order_id . '\'');
		$GLOBALS['db']->getOne($sql);
		assign_query_info();
		$smarty->display('store_order_info.dwt');
	}
}
else if ($_REQUEST['act'] == 'return_info') {
	admin_priv('order_back_apply');
	$ret_id = intval(trim($_REQUEST['ret_id']));
	$rec_id = intval(trim($_REQUEST['rec_id']));
	$smarty->assign('menu_select', array('action' => '04_order', 'current' => '12_back_apply'));
	if (!empty($ret_id) || !empty($rec_id)) {
		$back_order = return_order_info($ret_id);
	}
	else {
		exit('order does not exist');
	}

	$sql = 'SELECT agency_id FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'');
	$agency_id = $db->getOne($sql);

	if (0 < $agency_id) {
		if ($back_order['agency_id'] != $agency_id) {
			sys_msg($_LANG['priv_error']);
		}

		$sql = 'SELECT agency_name FROM ' . $ecs->table('agency') . (' WHERE agency_id = \'' . $agency_id . '\' LIMIT 0, 1');
		$agency_name = $db->getOne($sql);
		$back_order['agency_name'] = $agency_name;
	}

	if (0 < $back_order['user_id']) {
		$user = user_info($back_order['user_id']);

		if (!empty($user)) {
			$back_order['user_name'] = $user['user_name'];
		}
	}

	$back_order['region'] = $back_order['address_detail'];
	$back_order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
	$goods_list = get_return_order_goods($rec_id);
	$return_list = get_return_goods($ret_id);
	$region_id_list = array($back_order['country'], $back_order['province'], $back_order['city'], $back_order['district']);
	$shipping_list = available_shipping_list($region_id_list, $back_order['ru_id']);
	$total = order_weight_price($order_id);

	foreach ($shipping_list as $key => $shipping) {
		$shipping_fee = shipping_fee($shipping['shipping_code'], unserialize($shipping['configure']), $total['weight'], $total['amount'], $total['number']);
		$free_price = free_price($shipping['configure']);
		$shipping_list[$key]['shipping_fee'] = $shipping_fee;
		$shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee);
		$shipping_list[$key]['free_money'] = price_format($free_price['configure']['free_money']);
	}

	$smarty->assign('shipping_list', $shipping_list);
	$action_list = get_return_action($ret_id);
	$smarty->assign('action_list', $action_list);
	$smarty->assign('back_order', $back_order);
	$smarty->assign('exist_real_goods', $exist_real_goods);
	$smarty->assign('goods_list', $goods_list);
	$smarty->assign('return_list', $return_list);
	$smarty->assign('back_id', $back_id);
	$smarty->assign('ur_here', $_LANG['back_operate'] . $_LANG['detail']);
	$smarty->assign('action_link', array('href' => 'order.php?act=return_list&' . list_link_postfix(), 'text' => $_LANG['12_back_apply'], 'class' => 'icon-reply'));
	assign_query_info();
	$smarty->display('return_order_info.dwt');
	exit();
}
else if ($_REQUEST['act'] == 'delivery_list') {
	admin_priv('delivery_view');
	$smarty->assign('menu_select', array('action' => '04_order', 'current' => '09_delivery_order'));
	$smarty->assign('current', '09_delivery_order');
	$result = delivery_list();
	$smarty->assign('ur_here', $_LANG['09_delivery_order']);
	$smarty->assign('os_unconfirmed', OS_UNCONFIRMED);
	$smarty->assign('cs_await_pay', CS_AWAIT_PAY);
	$smarty->assign('cs_await_ship', CS_AWAIT_SHIP);
	$smarty->assign('full_page', 1);
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('delivery_list', $result['delivery']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$smarty->assign('sort_update_time', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->display('delivery_list.dwt');
}
else if ($_REQUEST['act'] == 'delivery_query') {
	admin_priv('delivery_view');
	$smarty->assign('current', '09_delivery_order');
	$result = delivery_list();
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('delivery_list', $result['delivery']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$sort_flag = sort_flag($result['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('delivery_list.dwt'), '', array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}
else if ($_REQUEST['act'] == 'delivery_info') {
	admin_priv('delivery_view');
	$smarty->assign('menu_select', array('action' => '04_order', 'current' => '09_delivery_order'));
	$delivery_id = intval(trim($_REQUEST['delivery_id']));

	if (!empty($delivery_id)) {
		$delivery_order = delivery_order_info($delivery_id);
	}
	else {
		exit('order does not exist');
	}

	$sql = 'SELECT agency_id FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'';
	$agency_id = $db->getOne($sql);

	if (0 < $agency_id) {
		if ($delivery_order['agency_id'] != $agency_id) {
			sys_msg($_LANG['priv_error']);
		}

		$sql = 'SELECT agency_name FROM ' . $ecs->table('agency') . (' WHERE agency_id = \'' . $agency_id . '\' LIMIT 0, 1');
		$agency_name = $db->getOne($sql);
		$delivery_order['agency_name'] = $agency_name;
	}

	if (0 < $delivery_order['user_id']) {
		$user = user_info($delivery_order['user_id']);

		if (!empty($user)) {
			$delivery_order['user_name'] = $user['user_name'];
		}
	}

	$delivery_order['region'] = get_user_region_address($delivery_order['order_id']);
	$order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
	$goods_sql = 'SELECT dg.*, g.brand_id FROM ' . $ecs->table('delivery_goods') . ' AS dg ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = dg.goods_id ' . 'WHERE dg.delivery_id = \'' . $delivery_order['delivery_id'] . '\'';
	$goods_list = $GLOBALS['db']->getAll($goods_sql);

	foreach ($goods_list as $key => $row) {
		$brand = get_goods_brand_info($row['brand_id']);
		$goods_list[$key]['brand_name'] = $brand['brand_name'];
		$row['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$goods_list[$key]['goods_thumb'] = $row['goods_thumb'];
	}

	$exist_real_goods = 0;

	if ($goods_list) {
		foreach ($goods_list as $value) {
			if ($value['is_real']) {
				$exist_real_goods++;
			}
		}
	}

	$act_list = array();
	$sql = 'SELECT * FROM ' . $ecs->table('order_action') . ' WHERE order_id = \'' . $delivery_order['order_id'] . '\' AND action_place = 1 ORDER BY log_time DESC,action_id DESC';
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		$row['order_status'] = $_LANG['os'][$row['order_status']];
		$row['pay_status'] = $_LANG['ps'][$row['pay_status']];
		$row['shipping_status'] = $row['shipping_status'] == SS_SHIPPED_ING ? $_LANG['ss_admin'][SS_SHIPPED_ING] : $_LANG['ss'][$row['shipping_status']];
		$row['action_time'] = local_date($_CFG['time_format'], $row['log_time']);
		$act_list[] = $row;
	}

	$smarty->assign('action_list', $act_list);
	$smarty->assign('delivery_order', $delivery_order);
	$smarty->assign('exist_real_goods', $exist_real_goods);
	$smarty->assign('goods_list', $goods_list);
	$smarty->assign('delivery_id', $delivery_id);
	$smarty->assign('ur_here', $_LANG['delivery_operate'] . $_LANG['detail']);
	$smarty->assign('action_link', array('href' => 'order.php?act=delivery_list&' . list_link_postfix(), 'text' => $_LANG['09_delivery_order'], 'class' => 'icon-reply'));
	$smarty->assign('action_act', $delivery_order['status'] == 2 ? 'delivery_ship' : 'delivery_cancel_ship');
	assign_query_info();
	$smarty->display('delivery_info.dwt');
	exit();
}
else if ($_REQUEST['act'] == 'delivery_ship') {
	admin_priv('delivery_view');
	define('GMTIME_UTC', gmtime());
	$delivery = array();
	$order_id = intval(trim($_REQUEST['order_id']));
	$delivery_id = intval(trim($_REQUEST['delivery_id']));
	$delivery['invoice_no'] = isset($_REQUEST['invoice_no']) ? trim($_REQUEST['invoice_no']) : '';
	$action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';

	if (!empty($delivery_id)) {
		$delivery_order = delivery_order_info($delivery_id);
	}
	else {
		exit('order does not exist');
	}

	$order = order_info($order_id);
	$delivery_stock_sql = 'SELECT G.model_attr, G.model_inventory, DG.goods_id, DG.delivery_id, DG.is_real, DG.send_number AS sums, G.goods_number AS storage, G.goods_name, DG.send_number,' . ' OG.goods_attr_id, OG.warehouse_id, OG.area_id, OG.ru_id, OG.order_id, OG.product_id FROM ' . $GLOBALS['ecs']->table('delivery_goods') . ' AS DG, ' . $GLOBALS['ecs']->table('goods') . ' AS G, ' . $GLOBALS['ecs']->table('delivery_order') . ' AS D, ' . $GLOBALS['ecs']->table('order_goods') . ' AS OG ' . (' WHERE DG.goods_id = G.goods_id AND DG.delivery_id = D.delivery_id AND D.order_id = OG.order_id AND DG.delivery_id = \'' . $delivery_id . '\' GROUP BY OG.rec_id ');
	$delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);
	$virtual_goods = array();

	for ($i = 0; $i < count($delivery_stock_result); $i++) {
		if ($delivery_stock_result[$i]['model_attr'] == 1) {
			$table_products = 'products_warehouse';
			$type_files = ' and warehouse_id = \'' . $delivery_stock_result[$i]['warehouse_id'] . '\'';
		}
		else if ($delivery_stock_result[$i]['model_attr'] == 2) {
			$table_products = 'products_area';
			$type_files = ' and area_id = \'' . $delivery_stock_result[$i]['area_id'] . '\'';
		}
		else {
			$table_products = 'products';
			$type_files = '';
		}

		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $delivery_stock_result[$i]['goods_id'] . '\'' . $type_files . ' LIMIT 0, 1';
		$prod = $GLOBALS['db']->getRow($sql);

		if (empty($prod)) {
			if ($delivery_stock_result[$i]['model_inventory'] == 1) {
				$delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_goods');
			}
			else if ($delivery_stock_result[$i]['model_inventory'] == 2) {
				$delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_area_goods');
			}
		}
		else {
			$products = get_warehouse_id_attr_number($delivery_stock_result[$i]['goods_id'], $delivery_stock_result[$i]['goods_attr_id'], $delivery_stock_result[$i]['ru_id'], $delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['model_attr']);
			$delivery_stock_result[$i]['storage'] = $products['product_number'];
		}

		if (($delivery_stock_result[$i]['storage'] < $delivery_stock_result[$i]['sums'] || $delivery_stock_result[$i]['storage'] <= 0) && ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP || $_CFG['use_storage'] == '0' && $delivery_stock_result[$i]['is_real'] == 0)) {
			$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
			sys_msg(sprintf($_LANG['act_good_vacancy'], $value['goods_name']), 1, $links);
			break;
		}

		if ($delivery_stock_result[$i]['is_real'] == 0) {
			$virtual_goods[] = array('goods_id' => $delivery_stock_result[$i]['goods_id'], 'goods_name' => $delivery_stock_result[$i]['goods_name'], 'num' => $delivery_stock_result[$i]['send_number']);
		}
	}

	if ($virtual_goods && is_array($virtual_goods) && 0 < count($virtual_goods)) {
		foreach ($virtual_goods as $virtual_value) {
			virtual_card_shipping($virtual_value, $order['order_sn'], $msg, 'split');
		}

		if (!empty($msg)) {
			$links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
			sys_msg($msg, 1, $links);
		}
	}

	if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
		foreach ($delivery_stock_result as $value) {
			if ($value['is_real'] != 0) {
				if (!empty($value['product_id'])) {
					if ($value['model_attr'] == 1) {
						$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('products_warehouse') . "\r\n                                            SET product_number = product_number - " . $value['sums'] . "\r\n                                            WHERE product_id = " . $value['product_id'];
					}
					else if ($value['model_attr'] == 2) {
						$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('products_area') . "\r\n                                            SET product_number = product_number - " . $value['sums'] . "\r\n                                            WHERE product_id = " . $value['product_id'];
					}
					else {
						$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('products') . "\r\n                                            SET product_number = product_number - " . $value['sums'] . "\r\n                                            WHERE product_id = " . $value['product_id'];
					}
				}
				else if ($value['model_inventory'] == 1) {
					$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('warehouse_goods') . "\r\n                                            SET region_number = region_number - " . $value['sums'] . "\r\n                                            WHERE goods_id = " . $value['goods_id'] . ' AND region_id = ' . $value['warehouse_id'];
				}
				else if ($value['model_inventory'] == 2) {
					$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('warehouse_area_goods') . "\r\n                                            SET region_number = region_number - " . $value['sums'] . "\r\n                                            WHERE goods_id = " . $value['goods_id'] . ' AND region_id = ' . $value['area_id'];
				}
				else {
					$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . "\r\n                                            SET goods_number = goods_number - " . $value['sums'] . "\r\n                                            WHERE goods_id = " . $value['goods_id'];
				}

				$GLOBALS['db']->query($minus_stock_sql, 'SILENT');
				$logs_other = array('goods_id' => $value['goods_id'], 'order_id' => $value['order_id'], 'use_storage' => $_CFG['stock_dec_time'], 'admin_id' => $_SESSION['seller_id'], 'number' => '- ' . $value['sums'], 'model_inventory' => $value['model_inventory'], 'model_attr' => $value['model_attr'], 'product_id' => $value['product_id'], 'warehouse_id' => $value['warehouse_id'], 'area_id' => $value['area_id'], 'add_time' => gmtime());
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_inventory_logs'), $logs_other, 'INSERT');
			}
		}
	}

	$invoice_no = str_replace(',', ',', $delivery['invoice_no']);
	$invoice_no = trim($invoice_no, ',');
	$_delivery['invoice_no'] = $invoice_no;
	$_delivery['status'] = 0;
	$query = $db->autoExecute($ecs->table('delivery_order'), $_delivery, 'UPDATE', 'delivery_id = ' . $delivery_id, 'SILENT');

	if (!$query) {
		$links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
		sys_msg($_LANG['act_false'], 1, $links);
	}

	$order_finish = get_all_delivery_finish($order_id);
	$shipping_status = $order_finish == 1 ? SS_SHIPPED : SS_SHIPPED_PART;
	$arr['shipping_status'] = $shipping_status;
	$arr['shipping_time'] = GMTIME_UTC;
	$arr['invoice_no'] = !empty($invoice_no) ? $invoice_no : $order['invoice_no'];
	update_order($order_id, $arr);
	order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $action_note, $_SESSION['seller_name'], 1);

	if ($order_finish) {
		if (0 < $order['user_id']) {
			$user = user_info($order['user_id']);
			$integral = integral_to_give($order);

			if (!empty($child_order)) {
				$integral['custom_points'] = $integral['custom_points'] - $child_order['custom_points'];
				$integral['rank_points'] = $integral['rank_points'] - $child_order['rank_points'];
			}

			log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));
			send_order_bonus($order_id);
			send_order_coupons($order_id);
		}

		$cfg = $_CFG['send_ship_email'];

		if ($cfg == '1') {
			$order['invoice_no'] = $invoice_no;
			$tpl = get_mail_template('deliver_notice');
			$smarty->assign('order', $order);
			$smarty->assign('send_time', local_date($_CFG['time_format']));
			$smarty->assign('shop_name', $_CFG['shop_name']);
			$smarty->assign('send_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
			$smarty->assign('sent_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
			$smarty->assign('confirm_url', $ecs->url() . 'user.php?act=order_detail&order_id=' . $order['order_id']);
			$smarty->assign('send_msg_url', $ecs->url() . 'user.php?act=message_list&order_id=' . $order['order_id']);
			$content = $smarty->fetch('str:' . $tpl['template_content']);

			if (!send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
				$msg = $_LANG['send_mail_fail'];
			}
		}

		if ($GLOBALS['_CFG']['sms_order_shipped'] == '1' && $order['mobile'] != '') {
			if ($order['ru_id']) {
				$shop_name = get_shop_name($order['ru_id'], 1);
			}
			else {
				$shop_name = '';
			}

			$user_info = get_admin_user_info($order['user_id']);
			$smsParams = array('shop_name' => $shop_name, 'shopname' => $shop_name, 'user_name' => $user_info['user_name'], 'username' => $user_info['user_name'], 'consignee' => $order['consignee'], 'order_sn' => $order['order_sn'], 'ordersn' => $order['order_sn'], 'mobile_phone' => $order['mobile'], 'mobilephone' => $order['mobile']);

			if ($GLOBALS['_CFG']['sms_type'] == 0) {
				huyi_sms($smsParams, 'sms_order_shipped');
			}
			else if (1 <= $GLOBALS['_CFG']['sms_type']) {
				$result = sms_ali($smsParams, 'sms_order_shipped');

				if ($result) {
					$resp = $GLOBALS['ecs']->ali_yu($result);
				}
				else {
					sys_msg('阿里大鱼短信配置异常', 1);
				}
			}
		}

		$file = MOBILE_WECHAT . '/Controllers/IndexController.php';

		if (file_exists($file)) {
			$pushData = array(
				'first'    => array('value' => '您的订单已发货'),
				'keyword1' => array('value' => $order['order_sn']),
				'keyword2' => array('value' => $order['shipping_name']),
				'keyword3' => array('value' => $invoice_no),
				'keyword4' => array('value' => $order['consignee']),
				'remark'   => array('value' => '订单正在配送中，请您耐心等待')
				);
			$shop_url = str_replace('/seller', '', $GLOBALS['ecs']->url());
			$order_url = $shop_url . 'mobile/index.php?m=user&c=order&a=detail&order_id=' . $order_id;
			push_template_curl('OPENTM202243318', $pushData, $order_url, $order['user_id'], $shop_url);
		}

		get_goods_sale($order_id);
	}

	clear_cache_files();
	$links[] = array('text' => $_LANG['09_delivery_order'], 'href' => 'order.php?act=delivery_list');
	$links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
	sys_msg($_LANG['act_ok'], 0, $links);
}
else if ($_REQUEST['act'] == 'order_detection') {
	$store_list = get_common_store_list();
	$smarty->assign('ur_here', $_LANG['11_order_detection']);
	$smarty->assign('store_list', $store_list);
	$order_list = get_order_detection_list();
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$smarty->assign('sort_order_time', '<img src="images/sort_desc.gif">');
	$smarty->assign('full_page', 1);
	$smarty->assign('is_detection', 1);
	$page_count_arr = seller_page($order_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->display('order_detection_list.dwt');
}
else if ($_REQUEST['act'] == 'detection_query') {
	admin_priv('order_detection');
	$order_list = get_order_detection_list();
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$page_count_arr = seller_page($order_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('is_detection', 1);
	make_json_result($smarty->fetch('order_detection_list.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}
else if ($_REQUEST['act'] == 'auto_order_detection') {
	admin_priv('order_detection');
	$smarty->assign('menu_select', array('action' => '04_order', 'current' => '11_order_detection'));
	$smarty->assign('ur_here', $_LANG['11_order_detection']);
	$order_list = get_order_detection_list(3);

	if ($order_list['orders']) {
		$_SESSION['is_ajax_detection'] = 1;
	}
	else {
		$_SESSION['is_ajax_detection'] = 0;
	}

	$order_list = get_order_detection_list(1);
	$smarty->assign('is_detection', 2);
	$smarty->assign('full_page', 1);
	$smarty->display('order_detection_list.dwt');
}
else if ($_REQUEST['act'] == 'ajax_order_detection') {
	admin_priv('order_detection');
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	@set_time_limit(300);

	if ($_SESSION['is_ajax_detection'] == 1) {
		$order_list = get_order_detection_list(2);
		$result['page'] = $order_list['filter']['page'] + 1;
		$result['page_size'] = $order_list['filter']['page_size'];
		$result['record_count'] = $order_list['filter']['record_count'];
		$result['page_count'] = $order_list['filter']['page_count'];
		$result['order'] = $order_list['orders'][0];

		if ($result['order']) {
			$confirm_take_time = gmtime();
			$operator = $_SESSION['seller_name'];
			$note = $GLOBALS['_LANG']['self_motion_goods'];
			order_action($result['order']['order_sn'], OS_SPLITED, SS_RECEIVED, PS_PAYED, $note, $operator, 0, $confirm_take_time);
			$GLOBALS['db']->query('UPDATE ' . $GLOBALS['ecs']->table('order_info') . (' SET confirm_take_time = \'' . $confirm_take_time . '\', ') . 'order_status = \'' . OS_SPLITED . '\', ' . 'shipping_status = \'' . SS_RECEIVED . '\', pay_status = \'' . PS_PAYED . '\'' . ' WHERE order_id = \'' . $result['order']['order_id'] . '\'');
			$sql = 'SELECT order_id, user_id, order_sn , order_status, shipping_status, pay_status, ' . 'order_amount, goods_amount, tax, shipping_fee, insure_fee, pay_fee, pack_fee, card_fee, ' . 'bonus, integral_money, coupons, discount, money_paid, surplus, confirm_take_time ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $result['order']['order_id'] . '\'';
			$order = $GLOBALS['db']->GetRow($sql);
			$seller_id = $GLOBALS['db']->getOne('SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id = \'' . $order['order_id'] . '\'', true);
			$value_card = $GLOBALS['db']->getOne('SELECT use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . ' WHERE order_id = \'' . $order['order_id'] . '\'', true);
			$return_amount = get_order_return_amount($order['order_id']);
			$other = array('user_id' => $order['user_id'], 'seller_id' => $seller_id, 'order_id' => $order['order_id'], 'order_sn' => $order['order_sn'], 'order_status' => $order['order_status'], 'shipping_status' => $order['shipping_status'], 'pay_status' => $order['pay_status'], 'order_amount' => $order['total_fee'], 'return_amount' => $return_amount, 'goods_amount' => $order['goods_amount'], 'tax' => $order['tax'], 'shipping_fee' => $order['shipping_fee'], 'insure_fee' => $order['insure_fee'], 'pay_fee' => $order['pay_fee'], 'pack_fee' => $order['pack_fee'], 'card_fee' => $order['card_fee'], 'bonus' => $order['bonus'], 'integral_money' => $order['integral_money'], 'coupons' => $order['coupons'], 'discount' => $order['discount'], 'value_card' => $value_card, 'money_paid' => $order['money_paid'], 'surplus' => $order['surplus'], 'confirm_take_time' => $confirm_take_time);

			if ($seller_id) {
				get_order_bill_log($other);
			}
		}

		if ($order_list['filter']['page_count'] < $order_list['filter']['page']) {
			$result['stop_ajax'] = 0;
			$_SESSION['is_ajax_detection'] = 0;
		}
		else {
			$result['stop_ajax'] = 1;
		}
	}
	else {
		$result['order'] = '';
		$result['stop_ajax'] = 0;
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'delivery_cancel_ship') {
	admin_priv('delivery_view');
	$delivery = '';
	$order_id = intval(trim($_REQUEST['order_id']));
	$delivery_id = intval(trim($_REQUEST['delivery_id']));
	$delivery['invoice_no'] = isset($_REQUEST['invoice_no']) ? trim($_REQUEST['invoice_no']) : '';
	$action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';

	if (!empty($delivery_id)) {
		$delivery_order = delivery_order_info($delivery_id);
	}
	else {
		exit('order does not exist');
	}

	$order = order_info($order_id);
	$_delivery['invoice_no'] = '';
	$_delivery['status'] = 2;
	$query = $db->autoExecute($ecs->table('delivery_order'), $_delivery, 'UPDATE', 'delivery_id = ' . $delivery_id, 'SILENT');

	if (!$query) {
		$links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
		sys_msg($_LANG['act_false'], 1, $links);
		exit();
	}

	$invoice_no_order = explode(',', $order['invoice_no']);
	$invoice_no_delivery = explode(',', $delivery_order['invoice_no']);

	foreach ($invoice_no_order as $key => $value) {
		$delivery_key = array_search($value, $invoice_no_delivery);

		if ($delivery_key !== false) {
			unset($invoice_no_order[$key]);
			unset($invoice_no_delivery[$delivery_key]);

			if (count($invoice_no_delivery) == 0) {
				break;
			}
		}
	}

	$_order['invoice_no'] = implode(',', $invoice_no_order);
	$order_finish = get_all_delivery_finish($order_id);
	$shipping_status = $order_finish == -1 ? SS_SHIPPED_PART : SS_SHIPPED_ING;
	$arr['shipping_status'] = $shipping_status;

	if ($shipping_status == SS_SHIPPED_ING) {
		$arr['shipping_time'] = '';
	}

	$arr['invoice_no'] = $_order['invoice_no'];
	update_order($order_id, $arr);
	order_action($order['order_sn'], $order['order_status'], $shipping_status, $order['pay_status'], $action_note, $_SESSION['seller_name'], 1);
	if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
		$virtual_goods = array();
		$delivery_stock_sql = "SELECT DG.goods_id, DG.product_id, DG.is_real, SUM(DG.send_number) AS sums\r\n            FROM " . $GLOBALS['ecs']->table('delivery_goods') . (" AS DG\r\n            WHERE DG.delivery_id = '" . $delivery_id . "'\r\n            GROUP BY DG.goods_id ");
		$delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);

		foreach ($delivery_stock_result as $key => $value) {
			if ($value['is_real'] == 0) {
				continue;
			}

			if (!empty($value['product_id'])) {
				$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('products') . "\r\n                                    SET product_number = product_number + " . $value['sums'] . "\r\n                                    WHERE product_id = " . $value['product_id'];
				$GLOBALS['db']->query($minus_stock_sql, 'SILENT');
			}

			$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . "\r\n                                SET goods_number = goods_number + " . $value['sums'] . "\r\n                                WHERE goods_id = " . $value['goods_id'];
			$GLOBALS['db']->query($minus_stock_sql, 'SILENT');
		}
	}

	if ($order['order_status'] == SS_SHIPPED_ING) {
		if (0 < $order['user_id']) {
			$user = user_info($order['user_id']);
			$integral = integral_to_give($order);
			log_account_change($order['user_id'], 0, 0, -1 * intval($integral['rank_points']), -1 * intval($integral['custom_points']), sprintf($_LANG['return_order_gift_integral'], $order['order_sn']));
			return_order_bonus($order_id);
		}
	}

	clear_cache_files();
	$links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
	sys_msg($_LANG['act_ok'], 0, $links);
}
else if ($_REQUEST['act'] == 'back_list') {
	admin_priv('back_view');
	$smarty->assign('current', '10_back_order');
	$result = back_list();
	$smarty->assign('ur_here', $_LANG['10_back_order']);
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('os_unconfirmed', OS_UNCONFIRMED);
	$smarty->assign('cs_await_pay', CS_AWAIT_PAY);
	$smarty->assign('cs_await_ship', CS_AWAIT_SHIP);
	$smarty->assign('full_page', 1);
	$smarty->assign('back_list', $result['back']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$smarty->assign('sort_update_time', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->display('back_list.dwt');
}
else if ($_REQUEST['act'] == 'back_cause_list') {
	admin_priv('order_back_cause');
	$result = cause_list(0, 0, false);
	$smarty->assign('ur_here', $_LANG['10_back_order']);
	$smarty->assign('action_link', array('href' => 'order.php?act=add_return_cause', 'text' => '添加退换货原因'));
	$smarty->assign('os_unconfirmed', OS_UNCONFIRMED);
	$smarty->assign('cs_await_pay', CS_AWAIT_PAY);
	$smarty->assign('cs_await_ship', CS_AWAIT_SHIP);
	$smarty->assign('full_page', 1);
	$smarty->assign('cause_list', $result);
	$smarty->assign('sort_update_time', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->display('back_cause_list.htm');
}
else if ($_REQUEST['act'] == 'add_return_cause') {
	admin_priv('order_back_cause');
	$smarty->assign('action_link', array('href' => 'order.php?act=back_cause_list', 'text' => '退换货原因列表'));
	$cause_select = cause_list(0, 0, true);
	$smarty->assign('cause_list', $cause_select);
	$smarty->assign('form_act', 'inser_cause');
	$smarty->display('back_cause_info.htm');
}
else if ($_REQUEST['act'] == 'inser_cause') {
	$cause['cause_name'] = !empty($_REQUEST['cause_name']) ? $_REQUEST['cause_name'] : '';
	$cause['parent_id'] = !empty($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;
	$cause['sort_order'] = !empty($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : 50;
	$cause['is_show'] = !empty($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;

	if (cause_exists($cause['cause_name'], $cause['parent_id'])) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['cause_repeat'], 0, $link);
	}

	if ($db->autoExecute($ecs->table('return_cause'), $cause) !== false) {
		$link[0]['text'] = '继续添加';
		$link[0]['href'] = 'order.php?act=add_return_cause';
		$link[1]['text'] = '返回列表页';
		$link[1]['href'] = 'order.php?act=back_cause_list';
		sys_msg($_LANG['add_success'], 0, $link);
	}
}
else if ($_REQUEST['act'] == 'edit_cause') {
	admin_priv('order_back_cause');
	$smarty->assign('action_link', array('href' => 'order.php?act=back_cause_list', 'text' => '退换货原因列表'));
	$c_id = !empty($_REQUEST['c_id']) ? intval($_REQUEST['c_id']) : 0;
	$cause_info = cause_info($c_id);
	$cause_list = cause_list(0, $cause_info['parent_id'], true);
	$smarty->assign('c_id', $c_id);
	$smarty->assign('cause_info', $cause_info);
	$smarty->assign('cause_list', $cause_list);
	$smarty->assign('form_act', 'edit_cause_info');
	$smarty->display('back_cause_info.htm');
}
else if ($_REQUEST['act'] == 'edit_cause_info') {
	$c_id = !empty($_REQUEST['c_id']) ? $_REQUEST['c_id'] : 0;
	$cause['cause_name'] = !empty($_REQUEST['cause_name']) ? $_REQUEST['cause_name'] : '';
	$cause['parent_id'] = !empty($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;
	$cause['sort_order'] = !empty($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : 50;
	$cause['is_show'] = !empty($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;

	if (cause_exists($cause['cause_name'], $c_id)) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['cause_repeat'], 0, $link);
	}

	if ($cause['parent_id'] == $c_id) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['cause_set_self'], 0, $link);
	}

	if ($db->autoExecute($ecs->table('return_cause'), $cause, 'UPDATE', 'cause_id=\'' . $c_id . '\'') !== false) {
		$link[0]['text'] = '返回退换货原因列表';
		$link[0]['href'] = 'order.php?act=back_cause_list';
		sys_msg($_LANG['edit_success'], 0, $link);
	}
}
else if ($_REQUEST['act'] == 'remove_cause') {
	$c_id = $_REQUEST['id'];
	$sql = 'DELETE FROM ' . $ecs->table('return_cause') . (' WHERE cause_id = \'' . $c_id . '\'');
	$db->query($sql);
	$url = 'order.php?act=cause_query&' . str_replace('act=remove_cause', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'cause_query') {
	$result = cause_list(0, 0, false);
	$smarty->assign('cause_list', $result);
	make_json_result($smarty->fetch('back_cause_list.htm'));
}
else if ($_REQUEST['act'] == 'back_query') {
	admin_priv('back_view');
	$result = back_list();
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('back_list', $result['back']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$sort_flag = sort_flag($result['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('back_list.dwt'), '', array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}
else if ($_REQUEST['act'] == 'back_info') {
	admin_priv('back_view');
	$smarty->assign('menu_select', array('action' => '04_order', 'current' => '10_back_order'));
	$back_id = intval(trim($_REQUEST['back_id']));

	if (!empty($back_id)) {
		$back_order = back_order_info($back_id);
	}
	else {
		exit('order does not exist');
	}

	$sql = 'SELECT agency_id FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'');
	$agency_id = $db->getOne($sql);

	if (0 < $agency_id) {
		if ($back_order['agency_id'] != $agency_id) {
			sys_msg($_LANG['priv_error']);
		}

		$sql = 'SELECT agency_name FROM ' . $ecs->table('agency') . (' WHERE agency_id = \'' . $agency_id . '\' LIMIT 0, 1');
		$agency_name = $db->getOne($sql);
		$back_order['agency_name'] = $agency_name;
	}

	if (0 < $back_order['user_id']) {
		$user = user_info($back_order['user_id']);

		if (!empty($user)) {
			$back_order['user_name'] = $user['user_name'];
		}
	}

	$back_order['region'] = get_user_region_address($back_order['order_id']);
	$order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
	$goods_sql = 'SELECT bg.*, g.brand_id FROM ' . $ecs->table('back_goods') . ' AS bg ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = bg.goods_id ' . 'WHERE bg.back_id = \'' . $back_order['back_id'] . '\'';
	$goods_list = $GLOBALS['db']->getAll($goods_sql);

	foreach ($goods_list as $key => $row) {
		$brand = get_goods_brand_info($row['brand_id']);
		$goods_list[$key]['brand_name'] = $brand['brand_name'];
		$row['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$goods_list[$key]['goods_thumb'] = $row['goods_thumb'];
	}

	$exist_real_goods = 0;

	if ($goods_list) {
		foreach ($goods_list as $value) {
			if ($value['is_real']) {
				$exist_real_goods++;
			}
		}
	}

	$smarty->assign('back_order', $back_order);
	$smarty->assign('exist_real_goods', $exist_real_goods);
	$smarty->assign('goods_list', $goods_list);
	$smarty->assign('back_id', $back_id);
	$smarty->assign('ur_here', $_LANG['back_operate'] . $_LANG['detail']);
	$smarty->assign('action_link', array('href' => 'order.php?act=back_list&' . list_link_postfix(), 'text' => $_LANG['10_back_order'], 'class' => 'icon-reply'));
	assign_query_info();
	$smarty->display('back_info.dwt');
	exit();
}
else if ($_REQUEST['act'] == 'edit_refound_amount') {
	admin_priv('order_edit');
	check_authz_json('order_edit');
	$type = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
	$refound_amount = empty($_REQUEST['refound_amount']) ? 0 : floatval($_REQUEST['refound_amount']);
	$order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
	$rec_id = empty($_REQUEST['rec_id']) ? 0 : intval($_REQUEST['rec_id']);
	$ret_id = empty($_REQUEST['ret_d']) ? 0 : intval($_REQUEST['ret_d']);

	if ($type == 1) {
		$sql = 'SELECT should_return FROM ' . $GLOBALS['ecs']->table('order_return') . (' WHERE ret_id = \'' . $ret_id . '\' LIMIT 1');
		$order_return = $GLOBALS['db']->getRow($sql);
		$order = order_info($order_id);
		$paid_amount = $order['money_paid'] + $order['surplus'];
		if (0 < $paid_amount && $order['shipping_fee'] <= $paid_amount) {
			$paid_amount = $paid_amount - $order['shipping_fee'];
		}

		if (0 < $ret_id) {
			$refound_fee = order_refound_fee($order_id, $ret_id);
			if (0 < $refound_fee && $refound_fee < $order_return['should_return']) {
				$paid_amount = $paid_amount - $refound_fee;

				if ($paid_amount < $refound_amount) {
					$should_return = $paid_amount;
				}
			}
			else {
				$should_return = $order_return['should_return'];
			}
		}
		else {
			$should_return = $refound_amount;
		}

		if ($paid_amount < $should_return) {
			$should_return = $paid_amount;
		}
	}
	else if ($type == 2) {
		$sql = 'SELECT shipping_fee FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\'');
		$order_shipping_fee = $GLOBALS['db']->getOne($sql);
		$is_refound_shippfee = order_refound_shipping_fee($order_id, $ret_id);
		$is_refound_shippfee_amount = $is_refound_shippfee + $refound_amount;

		if ($order_shipping_fee < $is_refound_shippfee_amount) {
			$shipping_fee = $order_shipping_fee - $is_refound_shippfee;
		}
		else {
			$shipping_fee = $refound_amount;
		}

		$refound_amount = $shipping_fee;
	}

	$data = array('should_return' => !empty($should_return) ? $should_return : 0, 'refound_amount' => $refound_amount, 'order_shipping_fee' => $order_shipping_fee, 'return_shipping_fee' => $return_shipping_fee, 'surplus_fee' => $surplus_fee, 'shipping_fee' => $shipping_fee, 'type' => $type);
	clear_cache_files();
	make_json_result($data);
}
else if ($_REQUEST['act'] == 'edit_refound_value_card') {
	admin_priv('order_edit');
	check_authz_json('order_edit');
	$vc_id = empty($_REQUEST['vc_id']) ? 0 : intval($_REQUEST['vc_id']);
	$order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
	$ret_id = empty($_REQUEST['ret_id']) ? 0 : intval($_REQUEST['ret_id']);
	$refound_vcard = empty($_REQUEST['refound_vcard']) ? 0 : floatval($_REQUEST['refound_vcard']);

	if (0 < $ret_id) {
		$return_order = return_order_info($ret_id);
		$should_return = $return_order['should_return'] - $return_order['discount_amount'];
	}
	else {
		$order = order_info($order_id);
		$order_amount = $order['money_paid'] + $order['surplus'];
		if (0 < $order_amount && $order['shipping_fee'] < $order_amount) {
			$order_amount = $order_amount - $order['shipping_fee'];
		}
		else {
			$should_return = $order['total_fee'];
		}
	}

	$sql = 'SELECT vc_id, use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . (' WHERE vc_id = \'' . $vc_id . '\' AND order_id = \'' . $order_id . '\' LIMIT 1');
	$value_card = $GLOBALS['db']->getRow($sql);

	if ($value_card) {
		if ($should_return < $value_card['use_val']) {
			$value_card['use_val'] = $should_return;
		}
	}

	if ($value_card && $value_card['use_val'] < $refound_vcard) {
		$refound_vcard = $value_card['use_val'];
	}

	$data = array('refound_vcard' => !empty($refound_vcard) ? $refound_vcard : 0);
	clear_cache_files();
	make_json_result($data);
}
else if ($_REQUEST['act'] == 'step_post') {
	admin_priv('order_edit');
	$step_list = array('user', 'edit_goods', 'add_goods', 'goods', 'consignee', 'shipping', 'payment', 'other', 'money', 'invoice');
	$step = isset($_REQUEST['step']) && in_array($_REQUEST['step'], $step_list) ? $_REQUEST['step'] : 'user';
	$order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;

	if (0 < $order_id) {
		$old_order = order_info($order_id);
	}

	$step_act = isset($_REQUEST['step_act']) ? $_REQUEST['step_act'] : 'add';

	if ('user' == $step) {
		$user_id = $_POST['anonymous'] == 1 ? 0 : intval($_POST['user']);
		$order = array('user_id' => $user_id, 'add_time' => gmtime(), 'order_status' => OS_INVALID, 'shipping_status' => SS_UNSHIPPED, 'pay_status' => PS_UNPAYED, 'from_ad' => 0, 'referer' => $_LANG['admin']);

		do {
			$order['order_sn'] = get_order_sn();

			if ($db->autoExecute($ecs->table('order_info'), $order, 'INSERT', '', 'SILENT')) {
				break;
			}
			else if ($db->errno() != 1062) {
				exit($db->error());
			}
		} while (true);

		$order_id = $db->insert_id();
		admin_log($order['order_sn'], 'add', 'order');
		$action_note = sprintf($_LANG['add_order_info'], $_SESSION['seller_name']);
		order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], $action_note, $_SESSION['seller_name']);
		$sql = 'INSERT INTO ' . $ecs->table('pay_log') . ' (order_id, order_amount, order_type, is_paid)' . (' VALUES (\'' . $order_id . '\', 0, \'') . PAY_ORDER . '\', 0)';
		$db->query($sql);
		ecs_header('Location: order.php?act=' . $step_act . '&order_id=' . $order_id . "&step=goods\n");
		exit();
	}
	else if ('edit_goods' == $step) {
		if (isset($_POST['rec_id'])) {
			foreach ($_POST['rec_id'] as $key => $rec_id) {
				$sql = 'SELECT warehouse_id, area_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE rec_id = \'' . $rec_id . '\' LIMIT 1');
				$order_goods = $GLOBALS['db']->getRow($sql);
				$sql = 'SELECT goods_number ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . 'WHERE goods_id =' . $_POST['goods_id'][$key];
				$goods_price = floatval($_POST['goods_price'][$key]);
				$goods_number = intval($_POST['goods_number'][$key]);
				$goods_attr = $_POST['goods_attr'][$key];
				$product_id = intval($_POST['product_id'][$key]);

				if ($product_id) {
					$sql = 'SELECT product_number ' . 'FROM ' . $GLOBALS['ecs']->table('products') . ' WHERE product_id =' . $_POST['product_id'][$key];
				}

				$goods_number_all = $db->getOne($sql);

				if ($goods_number <= $goods_number_all) {
					$sql = 'UPDATE ' . $ecs->table('order_goods') . (' SET goods_price = \'' . $goods_price . '\', ') . ('goods_number = \'' . $goods_number . '\', ') . ('goods_attr = \'' . $goods_attr . '\', ') . 'warehouse_id = \'' . $order_goods['warehouse_id'] . '\', ' . 'area_id = \'' . $order_goods['area_id'] . '\' ' . ('WHERE rec_id = \'' . $rec_id . '\' LIMIT 1');
					$db->query($sql);
				}
				else {
					sys_msg($_LANG['goods_num_err']);
				}
			}

			$goods_amount = order_amount($order_id);
			update_order($order_id, array('goods_amount' => $goods_amount));
			update_order_amount($order_id);
			update_pay_log($order_id);
			$sn = $old_order['order_sn'];
			$new_order = order_info($order_id);

			if ($old_order['total_fee'] != $new_order['total_fee']) {
				$sn .= ',' . sprintf($_LANG['order_amount_change'], $old_order['total_fee'], $new_order['total_fee']);
			}

			admin_log($sn, 'edit', 'order');
		}

		ecs_header('Location: order.php?act=' . $step_act . '&order_id=' . $order_id . "&step=goods\n");
		exit();
	}
	else if ('add_goods' == $step) {
		$goods_id = intval($_POST['goodslist']);
		$warehouse_id = intval($_POST['warehouse_id']);
		$area_id = intval($_POST['area_id']);
		$model_attr = intval($_POST['model_attr']);
		$attr_price = $_POST['attr_price'];
		$goods_price = $_POST['add_price'] != 'user_input' ? floatval($_POST['add_price']) : floatval($_POST['input_price']);
		$goods_price = $goods_price + $attr_price;
		$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\' LIMIT 0, 1');
		$goods_info = $GLOBALS['db']->getRow($sql);
		$goods_attr = '0';

		for ($i = 0; $i < $_POST['spec_count']; $i++) {
			if (is_array($_POST['spec_' . $i])) {
				$temp_array = $_POST['spec_' . $i];
				$temp_array_count = count($_POST['spec_' . $i]);

				for ($j = 0; $j < $temp_array_count; $j++) {
					if ($temp_array[$j] !== NULL) {
						$goods_attr .= ',' . $temp_array[$j];
					}
				}
			}
			else if ($_POST['spec_' . $i] !== NULL) {
				$goods_attr .= ',' . $_POST['spec_' . $i];
			}
		}

		$goods_number = $_POST['add_number'];
		$attr_list = $goods_attr;
		$goods_attr = explode(',', $goods_attr);
		$k = array_search(0, $goods_attr);
		unset($goods_attr[$k]);
		$attr_leftJoin = '';
		$select = '';

		if ($model_attr == 1) {
			$select = ' wap.attr_price as warehouse_attr_price, ';
			$attr_leftJoin = 'LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_attr') . (' AS wap ON g.goods_attr_id = wap.goods_attr_id AND wap.warehouse_id = \'' . $warehouse_id . '\' ');
		}
		else if ($model_attr == 2) {
			$select = ' waa.attr_price as area_attr_price, ';
			$attr_leftJoin = 'LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_attr') . (' AS waa ON g.goods_attr_id = waa.goods_attr_id AND area_id = \'' . $area_id . '\' ');
		}

		$attr_value = '';

		if ($attr_list) {
			$where = 'g.goods_attr_id in(' . $attr_list . ')';
			$sql = 'SELECT g.attr_value, ' . $select . ' g.attr_price ' . 'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS g ' . 'LEFT JOIN' . $ecs->table('attribute') . ' AS a ON g.attr_id = a.attr_id ' . $attr_leftJoin . ('WHERE ' . $where . ' ORDER BY a.sort_order, a.attr_id, g.goods_attr_id');
			$res = $db->query($sql);

			while ($row = $db->fetchRow($res)) {
				if ($model_attr == 1) {
					$row['attr_price'] = $row['warehouse_attr_price'];
				}
				else if ($model_attr == 2) {
					$row['attr_price'] = $row['area_attr_price'];
				}
				else {
					$row['attr_price'] = $row['attr_price'];
				}

				$attr_price = '';

				if (0 < $row['attr_price']) {
					$attr_price = ':[' . price_format($row['attr_price']) . ']';
				}

				$attr_value[] = $row['attr_value'] . $attr_price;
			}

			if ($attr_value) {
				$attr_value = implode(',', $attr_value);
			}
		}

		if ($model_attr == 1) {
			$table_products = 'products_warehouse';
			$type_files = ' and warehouse_id = \'' . $warehouse_id . '\'';
		}
		else if ($model_attr == 2) {
			$table_products = 'products_area';
			$type_files = ' and area_id = \'' . $area_id . '\'';
		}
		else {
			$table_products = 'products';
			$type_files = '';
		}

		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . (' WHERE goods_id = \'' . $goods_id . '\'') . $type_files . ' LIMIT 0, 1';
		$prod = $GLOBALS['db']->getRow($sql);
		if (is_spec($goods_attr) && !empty($prod)) {
			$product_info = get_products_info($goods_id, $goods_attr, $warehouse_id, $area_id);
		}

		if (is_spec($goods_attr) && !empty($prod)) {
			if (!empty($goods_attr)) {
				if ($product_info['product_number'] < $goods_number) {
					$url = 'order.php?act=' . $step_act . '&order_id=' . $order_id . '&step=goods';
					echo '<a href="' . $url . '">' . $_LANG['goods_num_err'] . '</a>';
					exit();
					return false;
				}
			}
		}

		if (is_spec($goods_attr) && !empty($prod)) {
			$sql = 'INSERT INTO ' . $ecs->table('order_goods') . '(order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ' . 'goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id, model_attr, warehouse_id, area_id, ru_id) ' . ('SELECT \'' . $order_id . '\', goods_id, goods_name, goods_sn, ') . $product_info['product_id'] . ', ' . ('\'' . $goods_number . '\', market_price, \'' . $goods_price . '\', \'') . $attr_value . '\', ' . 'is_real, extension_code, 0, 0 , \'' . implode(',', $goods_attr) . ('\', \'' . $model_attr . '\', \'' . $warehouse_id . '\', \'' . $area_id . '\', \'') . $goods_info['user_id'] . '\' ' . 'FROM ' . $ecs->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
		}
		else {
			$sql = 'INSERT INTO ' . $ecs->table('order_goods') . ' (order_id, goods_id, goods_name, goods_sn, ' . 'goods_number, market_price, goods_price, goods_attr, ' . 'is_real, extension_code, parent_id, is_gift, model_attr, warehouse_id, area_id, ru_id) ' . ('SELECT \'' . $order_id . '\', goods_id, goods_name, goods_sn, ') . ('\'' . $goods_number . '\', market_price, \'' . $goods_price . '\', \'') . $attr_value . '\', ' . ('is_real, extension_code, 0, 0, \'' . $model_attr . '\', \'' . $warehouse_id . '\', \'' . $area_id . '\', \'') . $goods_info['user_id'] . '\' ' . 'FROM ' . $ecs->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
		}

		$db->query($sql);
		if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE) {
			$model_inventory = get_table_date('goods', 'goods_id = \'' . $goods_id . '\'', array('model_inventory'), 2);

			if (!empty($product_info['product_id'])) {
				if ($model_attr == 1) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('products_warehouse') . "\r\n                            SET product_number = product_number - " . $goods_number . "\r\n                            WHERE product_id = " . $product_info['product_id'];
				}
				else if ($model_attr == 2) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('products_area') . "\r\n                            SET product_number = product_number - " . $goods_number . "\r\n                            WHERE product_id = " . $product_info['product_id'];
				}
				else {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('products') . "\r\n                            SET product_number = product_number - " . $goods_number . "\r\n                            WHERE product_id = " . $product_info['product_id'];
				}
			}
			else if ($model_inventory == 1) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('warehouse_goods') . "\r\n                            SET region_number = region_number - " . $goods_number . ("\r\n                            WHERE goods_id = '" . $goods_id . '\' AND region_id = \'' . $warehouse_id . '\'');
			}
			else if ($model_inventory == 2) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('warehouse_area_goods') . "\r\n                            SET region_number = region_number - " . $goods_number . ("\r\n                            WHERE goods_id = '" . $goods_id . '\' AND region_id = \'' . $area_id . '\'');
			}
			else {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . "\r\n                            SET goods_number = goods_number - " . $goods_number . ("\r\n                            WHERE goods_id = '" . $goods_id . '\'');
			}

			$db->query($sql);
		}

		update_order($order_id, array('goods_amount' => order_amount($order_id)));
		update_order_amount($order_id);
		update_pay_log($order_id);
		$sn = $old_order['order_sn'];
		$new_order = order_info($order_id);

		if ($old_order['total_fee'] != $new_order['total_fee']) {
			$sn .= ',' . sprintf($_LANG['order_amount_change'], $old_order['total_fee'], $new_order['total_fee']);
		}

		admin_log($sn, 'edit', 'order');
		ecs_header('Location: order.php?act=' . $step_act . '&order_id=' . $order_id . "&step=goods\n");
		exit();
	}
	else if ('goods' == $step) {
		if (isset($_POST['next'])) {
			ecs_header('Location: order.php?act=' . $step_act . '&order_id=' . $order_id . "&step=consignee\n");
			exit();
		}
		else if (isset($_POST['finish'])) {
			$msgs = array();
			$links = array();
			$order = order_info($order_id);
			handle_order_money_change($order, $msgs, $links);

			if (!empty($msgs)) {
				sys_msg(join(chr(13), $msgs), 0, $links);
			}
			else {
				ecs_header('Location: order.php?act=info&order_id=' . $order_id . "\n");
				exit();
			}
		}
	}
	else if ('consignee' == $step) {
		$order = $_POST;
		$order['agency_id'] = get_agency_by_regions(array($order['country'], $order['province'], $order['city'], $order['district']));
		update_order($order_id, $order);
		$agency_changed = $old_order['agency_id'] != $order['agency_id'];
		$sn = $old_order['order_sn'];
		admin_log($sn, 'edit', 'order');

		if (isset($_POST['next'])) {
			if (exist_real_goods($order_id)) {
				ecs_header('Location: order.php?act=' . $step_act . '&order_id=' . $order_id . "&step=shipping\n");
				exit();
			}
			else {
				ecs_header('Location: order.php?act=' . $step_act . '&order_id=' . $order_id . "&step=payment\n");
				exit();
			}
		}
		else if (isset($_POST['finish'])) {
			if ('edit' == $step_act && exist_real_goods($order_id)) {
				$order = order_info($order_id);
				$region_id_list = array($order['country'], $order['province'], $order['city'], $order['district']);
				$shipping_list = available_shipping_list($region_id_list, $order['ru_id']);
				$exist = false;

				foreach ($shipping_list as $shipping) {
					if ($shipping['shipping_id'] == $order['shipping_id']) {
						$exist = true;
						break;
					}
				}

				if (!$exist) {
					update_order($order_id, array('shipping_id' => 0, 'shipping_name' => ''));
					$links[] = array('text' => $_LANG['step']['shipping'], 'href' => 'order.php?act=edit&order_id=' . $order_id . '&step=shipping');
					sys_msg($_LANG['continue_shipping'], 1, $links);
				}
			}

			if ($agency_changed) {
				ecs_header("Location: order.php?act=list\n");
			}
			else {
				ecs_header('Location: order.php?act=info&order_id=' . $order_id . "\n");
			}

			exit();
		}
	}
	else if ('shipping' == $step) {
		if (!$old_order['is_zc_order']) {
			if (!exist_real_goods($order_id)) {
				exit('Hacking Attemp');
			}
		}

		$orderInfo = order_info($order_id);
		$shipping_id = intval($_POST['shipping']);
		$shipping = shipping_info($shipping_id);
		$shipping_name = $shipping['shipping_name'];
		$order = array('shipping_id' => $shipping_id, 'shipping_name' => addslashes($shipping_name));
		update_order($order_id, $order);
		clear_cache_files('index.dwt');

		if (isset($_POST['next'])) {
			ecs_header('Location: order.php?act=' . $step_act . '&order_id=' . $order_id . "&step=payment\n");
			exit();
		}
		else if (isset($_POST['finish'])) {
			$msgs = array();
			$links = array();
			$order = order_info($order_id);
			if ('edit' == $step_act && $shipping['support_cod'] == 0) {
				$payment = payment_info($order['pay_id']);

				if ($payment['is_cod'] == 1) {
					update_order($order_id, array('pay_id' => 0, 'pay_name' => ''));
					$msgs[] = $_LANG['continue_payment'];
					$links[] = array('text' => $_LANG['step']['payment'], 'href' => 'order.php?act=' . $step_act . '&order_id=' . $order_id . '&step=payment');
				}
			}

			if (!empty($msgs)) {
				sys_msg(join(chr(13), $msgs), 0, $links);
			}
			else {
				if ($shipping_id != $orderInfo['shipping_id']) {
					$order_note = sprintf($_LANG['update_shipping'], $orderInfo['shipping_name'], $shipping_name);
					order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], $order_note, $_SESSION['seller_name'], 0, gmtime());
				}

				ecs_header('Location: order.php?act=info&order_id=' . $order_id . "\n");
				exit();
			}
		}
	}
	else if ('payment' == $step) {
		$pay_id = $_POST['payment'];
		$payment = payment_info($pay_id);
		$order_amount = order_amount($order_id);

		if ($payment['is_cod'] == 1) {
			$order = order_info($order_id);
			$region_id_list = array($order['country'], $order['province'], $order['city'], $order['district']);
			$shipping = shipping_info($order['shipping_id']);
			$pay_fee = pay_fee($pay_id, $order_amount, $shipping['pay_fee']);
		}
		else {
			$pay_fee = pay_fee($pay_id, $order_amount);
		}

		$order = array('pay_id' => $pay_id, 'pay_name' => addslashes($payment['pay_name']), 'pay_fee' => $pay_fee);
		update_order($order_id, $order);
		update_order_amount($order_id);
		update_pay_log($order_id);
		$sn = $old_order['order_sn'];
		$new_order = order_info($order_id);

		if ($old_order['total_fee'] != $new_order['total_fee']) {
			$sn .= ',' . sprintf($_LANG['order_amount_change'], $old_order['total_fee'], $new_order['total_fee']);
		}

		admin_log($sn, 'edit', 'order');

		if (isset($_POST['next'])) {
			ecs_header('Location: order.php?act=' . $step_act . '&order_id=' . $order_id . "&step=other\n");
			exit();
		}
		else if (isset($_POST['finish'])) {
			$msgs = array();
			$links = array();
			$order = order_info($order_id);
			handle_order_money_change($order, $msgs, $links);

			if (!empty($msgs)) {
				sys_msg(join(chr(13), $msgs), 0, $links);
			}
			else {
				ecs_header('Location: order.php?act=info&order_id=' . $order_id . "\n");
				exit();
			}
		}
	}
	else if ('other' == $step) {
		$order = array();
		if (isset($_POST['pack']) && 0 < $_POST['pack']) {
			$pack = pack_info($_POST['pack']);
			$order['pack_id'] = $pack['pack_id'];
			$order['pack_name'] = addslashes($pack['pack_name']);
			$order['pack_fee'] = $pack['pack_fee'];
		}
		else {
			$order['pack_id'] = 0;
			$order['pack_name'] = '';
			$order['pack_fee'] = 0;
		}

		if (isset($_POST['card']) && 0 < $_POST['card']) {
			$card = card_info($_POST['card']);
			$order['card_id'] = $card['card_id'];
			$order['card_name'] = addslashes($card['card_name']);
			$order['card_fee'] = $card['card_fee'];
			$order['card_message'] = $_POST['card_message'];
		}
		else {
			$order['card_id'] = 0;
			$order['card_name'] = '';
			$order['card_fee'] = 0;
			$order['card_message'] = '';
		}

		$order['inv_type'] = $_POST['inv_type'];
		$order['inv_payee'] = $_POST['inv_payee'];
		$order['inv_content'] = $_POST['inv_content'];
		$order['how_oos'] = $_POST['how_oos'];
		$order['postscript'] = $_POST['postscript'];
		$order['to_buyer'] = $_POST['to_buyer'];
		update_order($order_id, $order);
		update_order_amount($order_id);
		update_pay_log($order_id);
		$sn = $old_order['order_sn'];
		admin_log($sn, 'edit', 'order');

		if (isset($_POST['next'])) {
			ecs_header('Location: order.php?act=' . $step_act . '&order_id=' . $order_id . "&step=money\n");
			exit();
		}
		else if (isset($_POST['finish'])) {
			ecs_header('Location: order.php?act=info&order_id=' . $order_id . "\n");
			exit();
		}
	}
	else if ('money' == $step) {
		$old_order = order_info($order_id);

		if (0 < $old_order['user_id']) {
			$user = user_info($old_order['user_id']);
		}

		$order['goods_amount'] = $old_order['goods_amount'];
		$order['discount'] = isset($_POST['discount']) && 0 <= floatval($_POST['discount']) ? round(floatval($_POST['discount']), 2) : 0;
		$order['tax'] = round(floatval($_POST['tax']), 2);
		$order['shipping_fee'] = isset($_POST['shipping_fee']) && 0 <= floatval($_POST['shipping_fee']) ? round(floatval($_POST['shipping_fee']), 2) : 0;
		$order['insure_fee'] = isset($_POST['insure_fee']) && 0 <= floatval($_POST['insure_fee']) ? round(floatval($_POST['insure_fee']), 2) : 0;
		$order['pay_fee'] = 0 <= floatval($_POST['pay_fee']) ? round(floatval($_POST['pay_fee']), 2) : 0;
		$order['pack_fee'] = isset($_POST['pack_fee']) && 0 <= floatval($_POST['pack_fee']) ? round(floatval($_POST['pack_fee']), 2) : 0;
		$order['card_fee'] = isset($_POST['card_fee']) && 0 <= floatval($_POST['card_fee']) ? round(floatval($_POST['card_fee']), 2) : 0;
		$order['coupons'] = isset($_POST['coupons']) && 0 <= floatval($_POST['coupons']) ? round(floatval($_POST['coupons']), 2) : 0;
		$order['money_paid'] = $old_order['money_paid'];
		$order['surplus'] = 0;
		$order['integral'] = 0 <= intval($_POST['integral']) ? intval($_POST['integral']) : 0;
		$order['integral_money'] = 0;
		$order['bonus'] = isset($_POST['bonus']) && 0 <= floatval($_POST['bonus']) ? round(floatval($_POST['bonus']), 2) : 0;
		$order['bonus_id'] = 0;
		$order['bonus'] = isset($_POST['bonus']) && 0 <= floatval($_POST['bonus']) ? round(floatval($_POST['bonus']), 2) : 0;
		$_POST['bonus_id'] = isset($_POST['bonus_id']) && !empty($_POST['bonus_id']) ? intval($_POST['bonus_id']) : 0;
		$order['order_amount'] = $order['goods_amount'] + $order['tax'] + $order['shipping_fee'] + $order['insure_fee'] + $order['pay_fee'] + $order['pack_fee'] + $order['card_fee'] - $order['discount'];
		$money_paid = 0;
		$order_amount = 0;

		if (0 < $order['order_amount']) {
			$is_coupons = 0;

			if ($order['order_amount'] < $order['coupons']) {
				$order['coupons'] = $order['order_amount'];
				$is_coupons = 1;
			}

			$order['order_amount'] = $order['order_amount'] - ($order['coupons'] + $old_order['use_val']);
			$order_amount = $order['order_amount'];

			if (0 < $order['order_amount']) {
				$order['order_amount'] -= $order['money_paid'];

				if (0 < $order['order_amount']) {
					if (0 < $old_order['user_id']) {
						if (0 < $_POST['bonus_id'] && !isset($_POST['bonus'])) {
							$order['bonus_id'] = $_POST['bonus_id'];
							$bonus = bonus_info($_POST['bonus_id']);
							$order['bonus'] = $bonus['type_money'];
							$order['order_amount'] -= $order['bonus'];
						}

						if (0 < $order['order_amount']) {
							if ($old_order['extension_code'] != 'exchange_goods') {
								if (isset($_POST['integral']) && 0 < intval($_POST['integral'])) {
									$order['integral'] = intval($_POST['integral']);
									$order['integral_money'] = value_of_integral($order['integral']);

									if ($old_order['integral'] + $user['pay_points'] < $order['integral']) {
										sys_msg($_LANG['pay_points_not_enough']);
									}

									$order['order_amount'] -= $order['integral_money'];
								}
							}
							else if ($user['pay_points'] + $old_order['integral'] < intval($_POST['integral'])) {
								sys_msg($_LANG['pay_points_not_enough']);
							}

							if (0 < $order['order_amount']) {
								if (isset($_POST['surplus']) && 0 <= floatval($_POST['surplus'])) {
									$order['surplus'] = round(floatval($_POST['surplus']), 2);

									if ($old_order['surplus'] + $user['user_money'] + $user['credit_line'] < $order['surplus']) {
										sys_msg($_LANG['user_money_not_enough']);
									}

									$order['order_amount'] -= $order['surplus'];

									if ($order['order_amount'] < 0) {
										$order['surplus'] += $order['order_amount'];
										$order['order_amount'] = 0;
									}
								}
							}
							else {
								$order['integral_money'] += $order['order_amount'];
								$order['integral'] = integral_of_value($order['integral_money']);
								$order['order_amount'] = 0;
							}
						}
						else {
							$order['order_amount'] = 0;
						}
					}

					$return_type = 1;
				}
				else {
					if (0 < $order['money_paid']) {
						$money_paid = $order_amount - $old_order['integral_money'];
						$order_amount = $order['money_paid'] - $money_paid;

						if (0 <= $order_amount) {
							$order_amount += $old_order['surplus'];
							$order['surplus'] = 0;
						}
						else {
							$order['surplus'] = $old_order['surplus'];
						}

						$order['integral'] = $old_order['integral'];
						$order['integral_money'] = $old_order['integral_money'];
					}
					else {
						$order['coupons'] = $old_order['coupons'];
					}

					$return_type = 2;
				}
			}
			else {
				if ($is_coupons == 1) {
					$order['order_amount'] = -1 * ($old_order['surplus'] + $old_order['money_paid']);
					$order['surplus'] = 0;
					$order['money_paid'] = 0;
					$order['integral'] = 0;
					$order['integral_money'] = 0;
				}
				else {
					$order['order_amount'] = -1 * ($old_order['surplus'] + $old_order['money_paid'] + $order['order_amount']);
					$order['surplus'] = $order['surplus'] + $order['order_amount'];
					if (0 < $order['coupons'] && $order['coupons'] < $old_order['coupons']) {
						$order['coupons'] = $old_order['coupons'] - $order['coupons'];
					}

					$order['integral_money'] = $old_order['integral_money'];
				}

				$return_type = 3;
			}
		}
		else {
			$return_type = 0;
		}

		if ($order['order_amount'] <= 0) {
			if (0 < $old_order['surplus'] + $old_order['money_paid']) {
				if ($return_type == 1) {
					if (0 < $old_order['surplus'] - $order['surplus']) {
						$order['order_amount'] = -1 * ($old_order['surplus'] - $order['surplus']);
					}
					else {
						$order['order_amount'] = 0;
					}
				}
				else if ($return_type == 2) {
					if (0 < $order_amount) {
						$order['order_amount'] = -1 * $order_amount;
					}
					else {
						$order['order_amount'] = 0;
					}

					$order['money_paid'] = $money_paid;
					$order['bonus'] = $old_order['bonus'];
				}
				else if ($return_type == 3) {
					$order['bonus'] = $old_order['bonus'];
				}
				else {
					$order['order_amount'] = -1 * ($old_order['surplus'] + $old_order['money_paid'] - $old_order['coupons'] - $old_order['use_val'] - $old_order['integral_money'] - $old_order['bonus']);
					$order['surplus'] = 0;
					$order['money_paid'] = 0;
					$order['coupons'] = 0;
					$order['bonus'] = 0;
					$order['integral'] = 0;
					$order['integral_money'] = 0;
				}
			}
			else if ($order['integral_money'] <= 0) {
				$order['integral'] = 0;
			}
		}

		if ($order['bonus_id'] == 0) {
			$order['bonus'] = 0;
		}

		if ($order['order_amount'] == 0) {
			$order['order_amount'] = 0;
			$order['order_status'] = OS_CONFIRMED;
			$order['shipping_status'] = !empty($old_order['shipping_status']) ? $old_order['shipping_status'] : SS_UNSHIPPED;
			$order['pay_status'] = PS_PAYED;
		}

		$order_amount = $order['goods_amount'] + $order['tax'] + $order['shipping_fee'] + $order['insure_fee'] + $order['pay_fee'] + $order['pack_fee'] + $order['card_fee'];
		$activity_amount = $order['money_paid'] + $order['surplus'] + $order['coupons'] + $order['integral_money'] + $old_order['use_val'] + $old_order['discount'] + $order['bonus'];
		if ($order_amount < $activity_amount && (0 < $order['bonus'] && $order['order_amount'] < $order['bonus'] && $order['order_amount'] < 0)) {
			$order['bonus'] = $order['bonus'] + $order['order_amount'];
		}

		update_order($order_id, $order);
		update_pay_log($order_id);
		$sn = $old_order['order_sn'];
		$new_order = order_info($order_id);

		if ($old_order['total_fee'] != $new_order['total_fee']) {
			if ($step_act == 'edit') {
				$new_order_sn = correct_order_sn($old_order['order_sn']);
				$sn = $new_order_sn;
				$old_order['order_sn'] = $new_order_sn;
			}

			$sn .= ',' . sprintf($_LANG['order_amount_change'], $old_order['total_fee'], $new_order['total_fee']);
		}

		admin_log($sn, 'edit', 'order');

		if (0 < $old_order['user_id']) {
			$user_money_change = $old_order['surplus'] - $order['surplus'];

			if ($user_money_change != 0) {
				log_account_change($user['user_id'], $user_money_change, 0, 0, 0, sprintf($_LANG['change_use_surplus'], $old_order['order_sn']));
			}

			$pay_points_change = $old_order['integral'] - $order['integral'];

			if ($pay_points_change != 0) {
				log_account_change($user['user_id'], 0, 0, 0, $pay_points_change, sprintf($_LANG['change_use_integral'], $old_order['order_sn']));
			}

			if ($old_order['bonus_id'] != $order['bonus_id']) {
				if (0 < $old_order['bonus_id']) {
					$sql = 'UPDATE ' . $ecs->table('user_bonus') . ' SET used_time = 0, order_id = 0 ' . ('WHERE bonus_id = \'' . $old_order['bonus_id'] . '\' LIMIT 1');
					$db->query($sql);
				}

				if (0 < $order['bonus_id']) {
					$sql = 'UPDATE ' . $ecs->table('user_bonus') . ' SET used_time = \'' . gmtime() . ('\', order_id = \'' . $order_id . '\' ') . ('WHERE bonus_id = \'' . $order['bonus_id'] . '\' LIMIT 1');
					$db->query($sql);
				}
			}
		}

		if (isset($_POST['finish'])) {
			if ($step_act == 'add') {
				$arr['order_status'] = OS_CONFIRMED;
				$arr['confirm_time'] = gmtime();

				if ($order['order_amount'] <= 0) {
					$arr['pay_status'] = PS_PAYED;
					$arr['pay_time'] = gmtime();
				}

				update_order($order_id, $arr);
			}

			$msgs = array();
			$links = array();
			$order = order_info($order_id);
			handle_order_money_change($order, $msgs, $links);

			if ($step_act == 'add') {
				$action_note = sprintf($_LANG['add_order_info'], $_SESSION['seller_name']);
				order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], $action_note, $_SESSION['seller_name']);
			}

			if (!empty($msgs)) {
				sys_msg(join(chr(13), $msgs), 0, $links);
			}
			else {
				ecs_header('Location: order.php?act=info&order_id=' . $order_id . "\n");
				exit();
			}
		}
	}
	else if ('invoice' == $step) {
		if (!exist_real_goods($order_id)) {
			exit('Hacking Attemp');
		}

		$shipping_id = intval($_POST['shipping']);
		$shipping = shipping_info($shipping_id);
		$invoice_no = trim($_POST['invoice_no']);
		$invoice_no = str_replace(',', ',', $invoice_no);
		$order = array('shipping_id' => $shipping_id, 'shipping_name' => addslashes($shipping['shipping_name']), 'invoice_no' => $invoice_no);
		update_order($order_id, $order);
		$sn = $old_order['order_sn'];
		admin_log($sn, 'edit', 'order');

		if (isset($_POST['finish'])) {
			ecs_header('Location: order.php?act=info&order_id=' . $order_id . "\n");
			exit();
		}
	}
}
else if ($_REQUEST['act'] == 'return_edit') {
	include_once ROOT_PATH . 'includes/lib_transaction.php';
	admin_priv('order_edit');
	$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
	$ret_id = isset($_GET['ret_id']) ? intval($_GET['ret_id']) : 0;
	$smarty->assign('menu_select', array('action' => '04_order', 'current' => '12_back_apply'));
	$step_list = array('user', 'goods', 'consignee', 'back_shipping', 'payment', 'other', 'money');
	$step = isset($_GET['step']) && in_array($_GET['step'], $step_list) ? $_GET['step'] : 'user';
	$smarty->assign('step', $step);
	$act = $_GET['act'];
	$smarty->assign('ur_here', $_LANG['add_order']);
	$smarty->assign('step_act', $act);
	$smarty->assign('step', $act);
	$order = order_info($order_id);

	if (0 < $order_id) {
		$return = get_return_detail($ret_id);
		$smarty->assign('return', $return);
		$smarty->assign('order', $order);
	}

	if ('back_shipping' == $step) {
		$region_id_list = array($order['country'], $order['province'], $order['city'], $order['district']);
		$shipping_list = available_shipping_list($region_id_list, $order['ru_id']);
		$total = order_weight_price($order_id);

		foreach ($shipping_list as $key => $shipping) {
			$shipping_fee = shipping_fee($shipping['shipping_code'], unserialize($shipping['configure']), $total['weight'], $total['amount'], $total['number']);
			$free_price = free_price($shipping['configure']);
			$shipping_list[$key]['shipping_fee'] = $shipping_fee;
			$shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee);
			$shipping_list[$key]['free_money'] = price_format($free_price['configure']['free_money']);
		}

		$smarty->assign('shipping_list', $shipping_list);
	}

	assign_query_info();
	$smarty->display('order_step.dwt');
}
else if ($_REQUEST['act'] == 'edit_shipping') {
	$order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
	$ret_id = isset($_REQUEST['ret_id']) ? intval($_REQUEST['ret_id']) : 0;
	$rec_id = isset($_REQUEST['rec_id']) ? intval($_REQUEST['rec_id']) : 0;
	$shipping_id = isset($_REQUEST['shipping']) ? intval($_REQUEST['shipping']) : 0;
	$invoice_no = isset($_REQUEST['invoice_no']) ? $_REQUEST['invoice_no'] : '';
	$db->query('UPDATE ' . $ecs->table('order_return') . (' SET out_shipping_name = \'' . $shipping_id . '\' , out_invoice_no =\'' . $invoice_no . '\'') . ('WHERE ret_id = \'' . $ret_id . '\''));
	$links[] = array('text' => $_LANG['return_sn'] . $_LANG['detail'], 'href' => 'order.php?act=return_info&ret_id=' . $ret_id . 'rec_id=' . $rec_id);
	sys_msg($_LANG['act_ok'], 0, $links);
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		admin_priv('order_edit');
		$smarty->assign('menu_select', array('action' => '04_order', 'current' => '08_add_order'));
		$smarty->assign('current', '08_add_order');
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		$smarty->assign('order_id', $order_id);
		$step_list = array('user', 'goods', 'consignee', 'shipping', 'payment', 'other', 'money');
		$step = isset($_GET['step']) && in_array($_GET['step'], $step_list) ? $_GET['step'] : 'user';
		$smarty->assign('step', $step);
		$warehouse_list = get_warehouse_list_goods();
		$smarty->assign('warehouse_list', $warehouse_list);
		$act = $_GET['act'];
		$smarty->assign('ur_here', $_LANG['add_order']);
		$smarty->assign('step_act', $act);

		if (0 < $order_id) {
			$order = order_info($order_id);
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\'');
			$goods_count = $GLOBALS['db']->getOne($sql);

			if (0 < $goods_count) {
				if ($order['ru_id'] != $adminru['ru_id']) {
					$Loaction = 'order.php?act=list';
					ecs_header('Location: ' . $Loaction . "\n");
					exit();
				}
			}

			if ($order['invoice_type'] == 1) {
				$user_id = $order['user_id'];
				$sql = ' SELECT * FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
				$res = $db->getRow($sql);
				$smarty->assign('vat_info', $res);
			}

			$order['invoice_no'] = str_replace(',', ',', $order['invoice_no']);
			if ($order['shipping_status'] == SS_SHIPPED || $order['shipping_status'] == SS_RECEIVED) {
				if ($step != 'shipping') {
					sys_msg($_LANG['cannot_edit_order_shipped']);
				}
				else {
					if ($order['invoice_no']) {
						sys_msg($_LANG['cannot_edit_order_shipped']);
					}

					$step = 'invoice';
					$smarty->assign('step', $step);
				}
			}

			if ($order['pay_status'] == PS_PAYED || $order['pay_status'] == PS_PAYED_PART) {
				if ($step == 'payment') {
					sys_msg($_LANG['cannot_edit_order_payed']);
				}
			}

			$smarty->assign('order', $order);
		}
		else {
			if ($act != 'add' || $step != 'user') {
				exit('invalid params');
			}
		}

		if ('user' == $step) {
		}
		else if ('goods' == $step) {
			$goods_list = order_goods($order_id);

			if (!empty($goods_list)) {
				foreach ($goods_list as $key => $goods) {
					$attr = $goods['goods_attr'];

					if ($attr == '') {
						$goods_list[$key]['rows'] = 1;
					}
					else {
						$goods_list[$key]['rows'] = count(explode(chr(13), $attr));
					}
				}
			}

			$smarty->assign('goods_list', $goods_list);
			$smarty->assign('goods_amount', order_amount($order_id));
		}
		else if ('consignee' == $step) {
			$smarty->assign('menu_select', array('action' => '04_order', 'current' => '02_order_list'));
			$smarty->assign('ur_here', $_LANG['14_user_address_edit']);
			$exist_real_goods = exist_real_goods($order_id);
			$smarty->assign('exist_real_goods', $exist_real_goods);

			if (0 < $order['user_id']) {
				$smarty->assign('address_list', address_list($order['user_id']));
				$address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;

				if (0 < $address_id) {
					$address = address_info($address_id);

					if ($address) {
						$order['consignee'] = $address['consignee'];
						$order['country'] = $address['country'];
						$order['province'] = $address['province'];
						$order['city'] = $address['city'];
						$order['district'] = $address['district'];
						$order['street'] = $address['street'];
						$order['email'] = $address['email'];
						$order['address'] = $address['address'];
						$order['zipcode'] = $address['zipcode'];
						$order['tel'] = $address['tel'];
						$order['mobile'] = $address['mobile'];
						$order['sign_building'] = $address['sign_building'];
						$order['best_time'] = $address['best_time'];
						$smarty->assign('order', $order);
					}
				}
			}

			if ($exist_real_goods) {
				$smarty->assign('country_list', get_regions());

				if (0 < $order['country']) {
					$smarty->assign('province_list', get_regions(1, $order['country']));

					if (0 < $order['province']) {
						$smarty->assign('city_list', get_regions(2, $order['province']));

						if (0 < $order['city']) {
							$smarty->assign('district_list', get_regions(3, $order['city']));

							if (0 < $order['district']) {
								$smarty->assign('street_list', get_regions(4, $order['district']));
							}
						}
					}
				}
			}
		}
		else if ('shipping' == $step) {
			if (!exist_real_goods($order_id)) {
				exit('Hacking Attemp');
			}

			$region_id_list = array($order['country'], $order['province'], $order['city'], $order['district'], $order['street']);
			$shipping_list = available_shipping_list($region_id_list, $order['ru_id']);
			$consignee = array('country' => $order['country'], 'province' => $order['province'], 'city' => $order['city'], 'district' => $order['district']);
			$goods_list = order_goods($order_id);
			$cart_goods = $goods_list;
			$shipping_fee = 0;

			foreach ($shipping_list as $key => $val) {
				if (substr($val['shipping_code'], 0, 5) != 'ship_') {
					if ($GLOBALS['_CFG']['freight_model'] == 0) {
						if ($cart_goods) {
							if (count($cart_goods) == 1) {
								$cart_goods = array_values($cart_goods);
								if (!empty($cart_goods[0]['freight']) && $cart_goods[0]['is_shipping'] == 0) {
									if ($cart_goods[0]['freight'] == 1) {
										$configure_value = $cart_goods[0]['shipping_fee'] * $cart_goods[0]['goods_number'];
									}
									else {
										$trow = get_goods_transport($cart_goods[0]['tid']);

										if ($trow['freight_type']) {
											$cart_goods[0]['user_id'] = $cart_goods[0]['ru_id'];
											$transport_tpl = get_goods_transport_tpl($cart_goods[0], $region, $val, $cart_goods[0]['goods_number']);
											$configure_value = isset($transport_tpl['shippingFee']) ? $transport_tpl['shippingFee'] : 0;
										}
										else {
											$custom_shipping = get_goods_custom_shipping($cart_goods);
											$transport = array('top_area_id', 'area_id', 'tid', 'ru_id', 'sprice');
											$transport_where = ' AND ru_id = \'' . $cart_goods[0]['ru_id'] . '\' AND tid = \'' . $cart_goods[0]['tid'] . '\'';
											$goods_transport = $GLOBALS['ecs']->get_select_find_in_set(2, $consignee['city'], $transport, $transport_where, 'goods_transport_extend', 'area_id');
											$ship_transport = array('tid', 'ru_id', 'shipping_fee');
											$ship_transport_where = ' AND ru_id = \'' . $cart_goods[0]['ru_id'] . '\' AND tid = \'' . $cart_goods[0]['tid'] . '\'';
											$goods_ship_transport = $GLOBALS['ecs']->get_select_find_in_set(2, $val['shipping_id'], $ship_transport, $ship_transport_where, 'goods_transport_express', 'shipping_id');
											$goods_transport['sprice'] = isset($goods_transport['sprice']) ? $goods_transport['sprice'] : 0;
											$goods_ship_transport['shipping_fee'] = isset($goods_ship_transport['shipping_fee']) ? $goods_ship_transport['shipping_fee'] : 0;
											if ($custom_shipping && $trow['free_money'] <= $custom_shipping[$cart_goods[0]['tid']]['amount'] && 0 < $trow['free_money']) {
												$is_shipping = 1;
											}
											else {
												$is_shipping = 0;
											}

											if ($is_shipping == 0) {
												if ($trow['type'] == 1) {
													$configure_value = $goods_transport['sprice'] * $cart_goods[0]['goods_number'] + $goods_ship_transport['shipping_fee'] * $cart_goods[0]['goods_number'];
												}
												else {
													$configure_value = $goods_transport['sprice'] + $goods_ship_transport['shipping_fee'];
												}
											}
										}
									}
								}
								else {
									$configure_type = 1;
								}
							}
							else {
								$order_transpor = get_order_transport($cart_goods, $consignee, $val['shipping_id'], $val['shipping_code']);

								if ($order_transpor['freight']) {
									$configure_type = 1;
								}

								$configure_value = isset($order_transpor['sprice']) ? $order_transpor['sprice'] : 0;
							}
						}

						$shipping_fee = $configure_value;
					}

					$shipping_cfg = unserialize_config($val['configure']);
					$shipping_list[$key]['shipping_id'] = $val['shipping_id'];
					$shipping_list[$key]['shipping_name'] = $val['shipping_name'];
					$shipping_list[$key]['shipping_code'] = $val['shipping_code'];
					$shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
					$shipping_list[$key]['shipping_fee'] = $shipping_fee;
					$shipping_list[$key]['insure_formated'] = strpos($val['insure'], '%') === false ? price_format($val['insure'], false) : $val['insure'];
					$shipping_list[$key]['format_free_money'] = price_format($shipping_cfg['free_money'], false);
					$shipping_list[$key]['free_money'] = $shipping_cfg['free_money'];

					if ($val['shipping_id'] == $order['shipping_id']) {
						$insure_disabled = $val['insure'] == 0;
						$cod_disabled = $val['support_cod'] == 0;
					}

					$shipping_list[$key]['insure_disabled'] = $insure_disabled;
					$shipping_list[$key]['cod_disabled'] = $cod_disabled;
				}

				if (substr($val['shipping_code'], 0, 5) == 'ship_') {
					unset($shipping_list[$key]);
				}
			}

			$smarty->assign('shipping_list', $shipping_list);
		}
		else if ('payment' == $step) {
			$smarty->assign('menu_select', array('action' => '04_order', 'current' => '02_order_list'));
			$smarty->assign('ur_here', $_LANG['013_payment_edit']);

			if (exist_real_goods($order_id)) {
				$region_id_list = array($order['country'], $order['province'], $order['city'], $order['district'], $order['street']);
				$shipping_area = shipping_info($order['shipping_id']);
				$pay_fee = $shipping_area['support_cod'] == 1 ? $shipping_area['pay_fee'] : 0;
				$payment_list = available_payment_list($shipping_area['support_cod'], $pay_fee);
			}
			else {
				$payment_list = available_payment_list(false);
			}

			foreach ($payment_list as $key => $payment) {
				if ($payment['pay_code'] == 'balance') {
					unset($payment_list[$key]);
				}
			}

			$smarty->assign('payment_list', $payment_list);
		}
		else if ('other' == $step) {
			$smarty->assign('menu_select', array('action' => '04_order', 'current' => '02_order_list'));
			$smarty->assign('ur_here', $_LANG['013_fapiao_edit']);
			$exist_real_goods = exist_real_goods($order_id);
			$smarty->assign('exist_real_goods', $exist_real_goods);

			if ($exist_real_goods) {
				$smarty->assign('pack_list', pack_list());
				$smarty->assign('card_list', card_list());
			}
		}
		else if ('money' == $step) {
			$exist_real_goods = exist_real_goods($order_id);
			$smarty->assign('exist_real_goods', $exist_real_goods);

			if (0 < $order['user_id']) {
				$user = user_info($order['user_id']);
				$smarty->assign('available_user_money', $order['surplus'] + $user['user_money']);
				$smarty->assign('available_pay_points', $order['integral'] + $user['pay_points']);
				$user_bonus = user_bonus($order['user_id'], $order['goods_amount']);
				$arr = array();

				foreach ($user_bonus as $key => $row) {
					$sql = 'SELECT order_id FROM ' . $ecs->table('order_info') . ' WHERE bonus_id = \'' . $row['bonus_id'] . '\'';

					if (!$db->getOne($sql)) {
						$arr[] = $row;
					}
				}

				$smarty->assign('available_bonus', $arr);
			}
		}
		else if ('invoice' == $step) {
			$smarty->assign('menu_select', array('action' => '04_order', 'current' => '02_order_list'));
			$smarty->assign('ur_here', $_LANG['013_fahuodan_edit']);

			if (!exist_real_goods($order_id)) {
				exit('Hacking Attemp');
			}

			$region_id_list = array($order['country'], $order['province'], $order['city'], $order['district']);
			$shipping_list = available_shipping_list($region_id_list, $order['ru_id']);
			$smarty->assign('shipping_list', $shipping_list);
		}

		assign_query_info();
		$smarty->display('order_step.dwt');
	}
	else if ($_REQUEST['act'] == 'search_area') {
		check_authz_json('order_view');
		$warehouse_id = intval($_REQUEST['warehouse_id']);
		$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region_warehouse') . (' WHERE region_type = 1 AND parent_id = \'' . $warehouse_id . '\'');
		$region_list = $GLOBALS['db']->getAll($sql);
		$select = '<select name="area_id">';
		$select .= '<option value="0">请选择</option>';

		if ($region_list) {
			foreach ($region_list as $key => $row) {
				$select .= '<option value="' . $row['region_id'] . '">' . $row['region_name'] . '</option>';
			}
		}

		$select .= '</select>';
		$result = $select;
		make_json_result($result);
	}
	else if ($_REQUEST['act'] == 'process') {
		$func = isset($_GET['func']) ? $_GET['func'] : '';

		if ('drop_order_goods' == $func) {
			admin_priv('order_edit');
			$rec_id = intval($_GET['rec_id']);
			$step_act = $_GET['step_act'];
			$order_id = intval($_GET['order_id']);
			if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE) {
				$goods = $db->getRow('SELECT goods_id, goods_number FROM ' . $ecs->table('order_goods') . ' WHERE rec_id = ' . $rec_id);
				$sql = 'UPDATE ' . $ecs->table('goods') . ' SET `goods_number` = goods_number + \'' . $goods['goods_number'] . '\' ' . ' WHERE `goods_id` = \'' . $goods['goods_id'] . '\' LIMIT 1';
				$db->query($sql);
			}

			$sql = 'DELETE FROM ' . $ecs->table('order_goods') . (' WHERE rec_id = \'' . $rec_id . '\' LIMIT 1');
			$db->query($sql);
			update_order($order_id, array('goods_amount' => order_amount($order_id)));
			update_order_amount($order_id);
			ecs_header('Location: order.php?act=' . $step_act . '&order_id=' . $order_id . "&step=goods\n");
			exit();
		}
		else if ('cancel_order' == $func) {
			$step_act = $_GET['step_act'];
			$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

			if ($step_act == 'add') {
				if (0 < $order_id) {
					$sql = 'DELETE FROM ' . $ecs->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
					$db->query($sql);
				}

				ecs_header("Location: order.php?act=list\n");
				exit();
			}
			else {
				ecs_header('Location: order.php?act=info&order_id=' . $order_id . "\n");
				exit();
			}
		}
		else if ('refund' == $func) {
			$order_id = $_REQUEST['order_id'];
			$refund_type = intval($_REQUEST['refund']);
			$refund_note = $_REQUEST['refund_note'];
			$refund_amount = $_REQUEST['refund_amount'];
			$order = order_info($order_id);
			$refund_order_amount = $order['order_amount'] < 0 ? $order['order_amount'] * -1 : $order['order_amount'];
			if ($order['order_amount'] < 0 && $refund_order_amount < $refund_amount) {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'order.php?act=process&func=load_refund&anonymous=0&order_id=' . $order_id . '&refund_amount=' . $refund_amount);
				sys_msg('退款失败，退款金额大于实际退款金额', 1, $link);
				exit();
			}

			$is_ok = order_refund($order, $refund_type, '【' . $order['order_sn'] . '】' . $refund_note, $refund_amount);
			if ($is_ok == 2 && $refund_type == 1) {
				$links[] = array('href' => 'order.php?act=info&order_id=' . $order_id, 'text' => '返回订单信息');
				sys_msg('退款失败，您的账户资金负金额大于信用额度，请您进行账户资金充值或者联系客服', 1, $links);
				exit();
			}

			if ($order['order_amount'] < 0) {
				$update_order['order_amount'] = $order['order_amount'] + $refund_amount;
			}

			update_order($order_id, $update_order);

			if ($refund_type == 1) {
				$refund_note = '【' . $_LANG['return_user_money'] . '】' . $_LANG['shipping_refund'] . '，' . $refund_note;
			}
			else if ($refund_type == 2) {
				$refund_note = '【' . $_LANG['create_user_account'] . '】' . $_LANG['shipping_refund'] . '，' . $refund_note;
			}

			$action_note = sprintf($refund_note, price_format($refund_amount));
			order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $action_note, $_SESSION['seller_name']);
			ecs_header('Location: order.php?act=info&order_id=' . $order_id . "\n");
			exit();
		}
		else if ('load_refund' == $func) {
			$refund_amount = floatval($_REQUEST['refund_amount']);
			$smarty->assign('refund_amount', $refund_amount);
			$smarty->assign('formated_refund_amount', price_format($refund_amount));
			$anonymous = $_REQUEST['anonymous'];
			$smarty->assign('anonymous', $anonymous);
			$order_id = intval($_REQUEST['order_id']);
			$smarty->assign('order_id', $order_id);
			$smarty->assign('ur_here', $_LANG['refund']);
			assign_query_info();
			$smarty->display('order_refund.dwt');
		}
		else {
			exit('invalid params');
		}
	}
	else if ($_REQUEST['act'] == 'merge') {
		check_authz_json('order_os_edit');
		require ROOT_PATH . '/includes/cls_json.php';
		$data = array('error' => 0);
		$merchant_id = empty($adminru['ru_id']) ? 0 : intval($adminru['ru_id']);

		if (0 < $merchant_id) {
			$where = ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . (' WHERE og.order_id = o.order_id LIMIT 1) = \'' . $merchant_id . '\' ');
			$where .= ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
			$sql = 'SELECT o.order_sn, u.user_name ' . 'FROM ' . $ecs->table('order_info') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('users') . ' AS u ON o.user_id = u.user_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON o.order_id=og.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON og.goods_id=g.goods_id ' . 'WHERE o.user_id > 0 ' . $where . 'AND o.extension_code = \'\' ' . order_query_sql('unprocessed') . ' GROUP BY o.order_id';
			$order_list = $db->getAll($sql);
			$smarty->assign('order_list', $order_list);
		}

		$result['content'] = $smarty->fetch('templates/merge_order.dwt');
		exit(json_encode($result));
	}
	else if ($_REQUEST['act'] == 'templates') {
		admin_priv('order_print');
		$file_path = ROOT_PATH . DATA_DIR . '/order_print.html';
		$file_content = file_get_contents($file_path);
		@fclose($file_content);
		include_once ROOT_PATH . 'includes/fckeditor/fckeditor.php';
		$editor = new FCKeditor('FCKeditor1');
		$editor->BasePath = '../includes/fckeditor/';
		$editor->ToolbarSet = 'Normal';
		$editor->Width = '95%';
		$editor->Height = '500';
		$editor->Value = $file_content;
		$fckeditor = $editor->CreateHtml();
		$smarty->assign('fckeditor', $fckeditor);
		$smarty->assign('ur_here', $_LANG['edit_order_templates']);
		$smarty->assign('action_link', array('href' => 'order.php?act=list', 'text' => $_LANG['02_order_list']));
		$smarty->assign('act', 'edit_templates');
		assign_query_info();
		$smarty->display('order_templates.htm');
	}
	else if ($_REQUEST['act'] == 'edit_templates') {
		$file_name = @fopen('../' . DATA_DIR . '/order_print.html', 'w+');
		@fwrite($file_name, stripslashes($_POST['FCKeditor1']));
		@fclose($file_name);
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'order.php?act=list');
		sys_msg($_LANG['edit_template_success'], 0, $link);
	}
	else if ($_REQUEST['act'] == 'operate') {
		admin_priv('order_os_edit');
		$smarty->assign('menu_select', array('action' => '04_order', 'current' => '02_order_list'));
		$order_id = isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id']) ? trim($_REQUEST['order_id']) : 0;
		$rec_id = isset($_REQUEST['rec_id']) && !empty($_REQUEST['rec_id']) ? intval($_REQUEST['rec_id']) : 0;
		$ret_id = isset($_REQUEST['ret_id']) && !empty($_REQUEST['ret_id']) ? intval($_REQUEST['ret_id']) : 0;
		$batch = isset($_REQUEST['batch']);
		$action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';

		if (isset($_POST['confirm'])) {
			$require_note = false;
			$action = $_LANG['op_confirm'];
			$operation = 'confirm';
		}
		else if (isset($_POST['to_shipping'])) {
			$invoice_no = empty($_REQUEST['invoice_no']) ? '' : trim($_REQUEST['invoice_no']);

			if (empty($invoice_no)) {
				$links[] = array('text' => $_LANG['invoice_no_null'], 'href' => 'order.php?act=info&order_id=' . $order_id);
				sys_msg($_LANG['act_false'], 0, $links);
			}

			define('GMTIME_UTC', gmtime());
			$delivery_info = get_delivery_info($order_id);
			if (!empty($invoice_no) && !$delivery_info) {
				$order_id = intval(trim($order_id));

				if (!empty($order_id)) {
					$order = order_info($order_id);
				}
				else {
					exit('order does not exist');
				}

				if (order_finished($order)) {
					admin_priv('order_view_finished');
				}
				else {
					admin_priv('order_view');
				}

				$sql = 'SELECT agency_id FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'');
				$agency_id = $db->getOne($sql);

				if (0 < $agency_id) {
					if ($order['agency_id'] != $agency_id) {
						sys_msg($_LANG['priv_error'], 0);
					}
				}

				if (0 < $order['user_id']) {
					$user = user_info($order['user_id']);

					if (!empty($user)) {
						$order['user_name'] = $user['user_name'];
					}
				}

				$order['region'] = $db->getOne($sql);
				$order['order_time'] = local_date($_CFG['time_format'], $order['add_time']);
				$order['invoice_no'] = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $_LANG['ss'][SS_UNSHIPPED] : $order['invoice_no'];
				$order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
				$exist_real_goods = exist_real_goods($order_id);
				$_goods = get_order_goods(array('order_id' => $order['order_id'], 'order_sn' => $order['order_sn']));
				$attr = $_goods['attr'];
				$goods_list = $_goods['goods_list'];
				unset($_goods);

				if ($goods_list) {
					foreach ($goods_list as $key => $goods_value) {
						if (!$goods_value['goods_id']) {
							continue;
						}

						if ($goods_value['extension_code'] == 'package_buy' && 0 < count($goods_value['package_goods_list'])) {
							$goods_list[$key]['package_goods_list'] = package_goods($goods_value['package_goods_list'], $goods_value['goods_number'], $goods_value['order_id'], $goods_value['extension_code'], $goods_value['goods_id']);

							foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value) {
								$goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = '';
								if ($pg_value['storage'] <= 0 && $_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
									$goods_list[$key]['package_goods_list'][$pg_key]['send'] = $_LANG['act_good_vacancy'];
									$goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
								}
								else if ($pg_value['send'] <= 0) {
									$goods_list[$key]['package_goods_list'][$pg_key]['send'] = $_LANG['act_good_delivery'];
									$goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
								}
							}
						}
						else {
							$goods_list[$key]['sended'] = $goods_value['send_number'];
							$goods_list[$key]['sended'] = $goods_value['goods_number'];
							$goods_list[$key]['send'] = $goods_value['goods_number'] - $goods_value['send_number'];
							$goods_list[$key]['readonly'] = '';
							if ($goods_value['storage'] <= 0 && $_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
								$goods_list[$key]['send'] = $_LANG['act_good_vacancy'];
								$goods_list[$key]['readonly'] = 'readonly="readonly"';
							}
							else if ($goods_list[$key]['send'] <= 0) {
								$goods_list[$key]['send'] = $_LANG['act_good_delivery'];
								$goods_list[$key]['readonly'] = 'readonly="readonly"';
							}
						}
					}
				}

				$suppliers_id = 0;
				$delivery['order_sn'] = trim($order['order_sn']);
				$delivery['add_time'] = trim($order['order_time']);
				$delivery['user_id'] = intval(trim($order['user_id']));
				$delivery['how_oos'] = trim($order['how_oos']);
				$delivery['shipping_id'] = trim($order['shipping_id']);
				$delivery['shipping_fee'] = trim($order['shipping_fee']);
				$delivery['consignee'] = trim($order['consignee']);
				$delivery['address'] = trim($order['address']);
				$delivery['country'] = intval(trim($order['country']));
				$delivery['province'] = intval(trim($order['province']));
				$delivery['city'] = intval(trim($order['city']));
				$delivery['district'] = intval(trim($order['district']));
				$delivery['sign_building'] = trim($order['sign_building']);
				$delivery['email'] = trim($order['email']);
				$delivery['zipcode'] = trim($order['zipcode']);
				$delivery['tel'] = trim($order['tel']);
				$delivery['mobile'] = trim($order['mobile']);
				$delivery['best_time'] = trim($order['best_time']);
				$delivery['postscript'] = trim($order['postscript']);
				$delivery['how_oos'] = trim($order['how_oos']);
				$delivery['insure_fee'] = floatval(trim($order['insure_fee']));
				$delivery['shipping_fee'] = floatval(trim($order['shipping_fee']));
				$delivery['agency_id'] = intval(trim($order['agency_id']));
				$delivery['shipping_name'] = trim($order['shipping_name']);
				$operable_list = operable_list($order);
				$msg = '';
				$_goods = get_order_goods(array('order_id' => $order_id, 'order_sn' => $delivery['order_sn']));
				$goods_list = $_goods['goods_list'];
				$virtual_goods = array();
				$package_virtual_goods = array();
				$delivery['delivery_sn'] = get_delivery_sn();
				$delivery_sn = $delivery['delivery_sn'];
				$delivery['action_user'] = $_SESSION['seller_name'];
				$delivery['update_time'] = GMTIME_UTC;
				$delivery_time = $delivery['update_time'];
				$sql = 'select add_time from ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_sn = \'' . $delivery['order_sn'] . '\'';
				$delivery['add_time'] = $GLOBALS['db']->GetOne($sql);
				$delivery['suppliers_id'] = $suppliers_id;
				$delivery['status'] = 2;
				$delivery['order_id'] = $order_id;
				$filter_fileds = array('order_sn', 'add_time', 'user_id', 'how_oos', 'shipping_id', 'shipping_fee', 'consignee', 'address', 'country', 'province', 'city', 'district', 'sign_building', 'email', 'zipcode', 'tel', 'mobile', 'best_time', 'postscript', 'insure_fee', 'agency_id', 'delivery_sn', 'action_user', 'update_time', 'suppliers_id', 'status', 'order_id', 'shipping_name');
				$_delivery = array();

				foreach ($filter_fileds as $value) {
					$_delivery[$value] = $delivery[$value];
				}

				$query = $db->autoExecute($ecs->table('delivery_order'), $_delivery, 'INSERT', '', 'SILENT');
				$delivery_id = $db->insert_id();

				if ($delivery_id) {
					$delivery_goods = array();

					if (!empty($goods_list)) {
						foreach ($goods_list as $value) {
							if (empty($value['extension_code']) || $value['extension_code'] == 'virtual_card' || $value['extension_code'] == 'presale' || substr($value['extension_code'], 0, 7) == 'seckill') {
								$delivery_goods = array('delivery_id' => $delivery_id, 'goods_id' => $value['goods_id'], 'product_id' => $value['product_id'], 'product_sn' => $value['product_sn'], 'goods_id' => $value['goods_id'], 'goods_name' => $value['goods_name'], 'brand_name' => $value['brand_name'], 'goods_sn' => $value['goods_sn'], 'send_number' => $value['goods_number'], 'parent_id' => 0, 'is_real' => $value['is_real'], 'goods_attr' => $value['goods_attr']);

								if (!empty($value['product_id'])) {
									$delivery_goods['product_id'] = $value['product_id'];
								}

								$query = $db->autoExecute($ecs->table('delivery_goods'), $delivery_goods, 'INSERT', '', 'SILENT');
								$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . "\r\n                SET send_number = " . $value['goods_number'] . "\r\n                WHERE order_id = '" . $value['order_id'] . "'\r\n                AND goods_id = '" . $value['goods_id'] . '\' ';
								$GLOBALS['db']->query($sql, 'SILENT');
							}
							else if ($value['extension_code'] == 'package_buy') {
								foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
									$delivery_pg_goods = array('delivery_id' => $delivery_id, 'goods_id' => $pg_value['goods_id'], 'product_id' => $pg_value['product_id'], 'product_sn' => $pg_value['product_sn'], 'goods_name' => $pg_value['goods_name'], 'brand_name' => '', 'goods_sn' => $pg_value['goods_sn'], 'send_number' => $value['goods_number'], 'parent_id' => $value['goods_id'], 'extension_code' => $value['extension_code'], 'is_real' => $pg_value['is_real']);
									$query = $db->autoExecute($ecs->table('delivery_goods'), $delivery_pg_goods, 'INSERT', '', 'SILENT');
									$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . "\r\n                                        SET send_number = " . $value['goods_number'] . "\r\n                                        WHERE order_id = '" . $value['order_id'] . "'\r\n                                        AND goods_id = '" . $pg_value['goods_id'] . '\' ';
									$GLOBALS['db']->query($sql, 'SILENT');
								}
							}
						}
					}
				}
				else {
					$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
					sys_msg($_LANG['act_false'], 1, $links);
				}

				unset($filter_fileds);
				unset($delivery);
				unset($_delivery);
				unset($order_finish);

				if (true) {
					$order_finish = get_order_finish($order_id);
					$shipping_status = SS_SHIPPED_ING;
					if ($order['order_status'] != OS_CONFIRMED && $order['order_status'] != OS_SPLITED && $order['order_status'] != OS_SPLITING_PART) {
						$arr['order_status'] = OS_CONFIRMED;
						$arr['confirm_time'] = GMTIME_UTC;
					}

					$arr['order_status'] = $order_finish ? OS_SPLITED : OS_SPLITING_PART;
					$arr['shipping_status'] = $shipping_status;
					update_order($order_id, $arr);
				}

				order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $action_note, $_SESSION['seller_name']);
				clear_cache_files();

				if (!empty($delivery_id)) {
					$delivery_order = delivery_order_info($delivery_id);
				}
				else if (!empty($order_sn)) {
					$delivery_id = $GLOBALS['db']->getOne('SELECT delivery_id FROM ' . $ecs->table('delivery_order') . (' WHERE order_sn = \'' . $order_sn . '\''));
					$delivery_order = delivery_order_info($delivery_id);
				}
				else {
					exit('order does not exist');
				}

				$sql = 'SELECT agency_id FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'';
				$agency_id = $db->getOne($sql);

				if (0 < $agency_id) {
					if ($delivery_order['agency_id'] != $agency_id) {
						sys_msg($_LANG['priv_error']);
					}

					$sql = 'SELECT agency_name FROM ' . $ecs->table('agency') . (' WHERE agency_id = \'' . $agency_id . '\' LIMIT 0, 1');
					$agency_name = $db->getOne($sql);
					$delivery_order['agency_name'] = $agency_name;
				}

				if (0 < $delivery_order['user_id']) {
					$user = user_info($delivery_order['user_id']);

					if (!empty($user)) {
						$delivery_order['user_name'] = $user['user_name'];
					}
				}

				$sql = 'SELECT concat(IFNULL(c.region_name, \'\'), \'  \', IFNULL(p.region_name, \'\'), ' . '\'  \', IFNULL(t.region_name, \'\'), \'  \', IFNULL(d.region_name, \'\')) AS region ' . 'FROM ' . $ecs->table('order_info') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS c ON o.country = c.region_id ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS p ON o.province = p.region_id ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS t ON o.city = t.region_id ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS d ON o.district = d.region_id ' . 'WHERE o.order_id = \'' . $delivery_order['order_id'] . '\'';
				$delivery_order['region'] = $db->getOne($sql);
				$order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
				$goods_sql = "SELECT *\r\n                  FROM " . $ecs->table('delivery_goods') . "\r\n                  WHERE delivery_id = " . $delivery_order['delivery_id'];
				$goods_list = $GLOBALS['db']->getAll($goods_sql);
				$exist_real_goods = 0;

				if ($goods_list) {
					foreach ($goods_list as $value) {
						if ($value['is_real']) {
							$exist_real_goods++;
						}
					}
				}

				$act_list = array();
				$sql = 'SELECT * FROM ' . $ecs->table('order_action') . ' WHERE order_id = \'' . $delivery_order['order_id'] . '\' AND action_place = 1 ORDER BY log_time DESC,action_id DESC';
				$res = $db->query($sql);

				while ($row = $db->fetchRow($res)) {
					$row['order_status'] = $_LANG['os'][$row['order_status']];
					$row['pay_status'] = $_LANG['ps'][$row['pay_status']];
					$row['shipping_status'] = $row['shipping_status'] == SS_SHIPPED_ING ? $_LANG['ss_admin'][SS_SHIPPED_ING] : $_LANG['ss'][$row['shipping_status']];
					$row['action_time'] = local_date($_CFG['time_format'], $row['log_time']);
					$act_list[] = $row;
				}

				$alipay = false;
				$order = order_info($delivery_order['order_id']);
				$payment = payment_info($order['pay_id']);

				if (!empty($delivery_id)) {
					$delivery_order = delivery_order_info($delivery_id);
				}
				else {
					exit('order does not exist');
				}

				$delivery_stock_sql = 'SELECT DG.rec_id AS dg_rec_id, OG.rec_id AS og_rec_id, G.model_attr, G.model_inventory, DG.goods_id, DG.delivery_id, DG.is_real, DG.send_number AS sums, G.goods_number AS storage, G.goods_name, DG.send_number,' . ' OG.goods_attr_id, OG.warehouse_id, OG.area_id, OG.ru_id, OG.order_id, OG.product_id FROM ' . $GLOBALS['ecs']->table('delivery_goods') . ' AS DG, ' . $GLOBALS['ecs']->table('goods') . ' AS G, ' . $GLOBALS['ecs']->table('delivery_order') . ' AS D, ' . $GLOBALS['ecs']->table('order_goods') . ' AS OG ' . (' WHERE DG.goods_id = G.goods_id AND DG.delivery_id = D.delivery_id AND D.order_id = OG.order_id AND DG.goods_sn = OG.goods_sn AND DG.product_id = OG.product_id AND DG.delivery_id = \'' . $delivery_id . '\' GROUP BY OG.rec_id ');
				$delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);
				$virtual_goods = array();

				for ($i = 0; $i < count($delivery_stock_result); $i++) {
					if ($delivery_stock_result[$i]['model_attr'] == 1) {
						$table_products = 'products_warehouse';
						$type_files = ' and warehouse_id = \'' . $delivery_stock_result[$i]['warehouse_id'] . '\'';
					}
					else if ($delivery_stock_result[$i]['model_attr'] == 2) {
						$table_products = 'products_area';
						$type_files = ' and area_id = \'' . $delivery_stock_result[$i]['area_id'] . '\'';
					}
					else {
						$table_products = 'products';
						$type_files = '';
					}

					$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $delivery_stock_result[$i]['goods_id'] . '\'' . $type_files . ' LIMIT 0, 1';
					$prod = $GLOBALS['db']->getRow($sql);

					if (empty($prod)) {
						if ($delivery_stock_result[$i]['model_inventory'] == 1) {
							$delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_goods');
						}
						else if ($delivery_stock_result[$i]['model_inventory'] == 2) {
							$delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_area_goods');
						}
					}
					else {
						$products = get_warehouse_id_attr_number($delivery_stock_result[$i]['goods_id'], $delivery_stock_result[$i]['goods_attr_id'], $delivery_stock_result[$i]['ru_id'], $delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['model_attr']);
						$delivery_stock_result[$i]['storage'] = $products['product_number'];
					}

					if (($delivery_stock_result[$i]['storage'] < $delivery_stock_result[$i]['sums'] || $delivery_stock_result[$i]['storage'] <= 0) && ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP || $_CFG['use_storage'] == '0' && $delivery_stock_result[$i]['is_real'] == 0)) {
						$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
						sys_msg(sprintf($_LANG['act_good_vacancy'], $value['goods_name']), 1, $links);
						break;
					}

					if ($delivery_stock_result[$i]['is_real'] == 0) {
						$virtual_goods[] = array('goods_id' => $delivery_stock_result[$i]['goods_id'], 'goods_name' => $delivery_stock_result[$i]['goods_name'], 'num' => $delivery_stock_result[$i]['send_number']);
					}
				}

				if ($virtual_goods && is_array($virtual_goods) && 0 < count($virtual_goods)) {
					foreach ($virtual_goods as $virtual_value) {
						virtual_card_shipping($virtual_value, $order['order_sn'], $msg, 'split');
					}

					if (!empty($msg)) {
						$links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
						sys_msg($msg, 1, $links);
					}
				}

				if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
					foreach ($delivery_stock_result as $value) {
						if ($value['is_real'] != 0) {
							if (!empty($value['product_id'])) {
								if ($value['model_attr'] == 1) {
									$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('products_warehouse') . "\r\n                                            SET product_number = product_number - " . $value['sums'] . "\r\n                                            WHERE product_id = " . $value['product_id'];
								}
								else if ($value['model_attr'] == 2) {
									$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('products_area') . "\r\n                                            SET product_number = product_number - " . $value['sums'] . "\r\n                                            WHERE product_id = " . $value['product_id'];
								}
								else {
									$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('products') . "\r\n                                            SET product_number = product_number - " . $value['sums'] . "\r\n                                            WHERE product_id = " . $value['product_id'];
								}
							}
							else if ($value['model_inventory'] == 1) {
								$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('warehouse_goods') . "\r\n                                            SET region_number = region_number - " . $value['sums'] . "\r\n                                            WHERE goods_id = " . $value['goods_id'] . ' AND region_id = ' . $value['warehouse_id'];
							}
							else if ($value['model_inventory'] == 2) {
								$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('warehouse_area_goods') . "\r\n                                            SET region_number = region_number - " . $value['sums'] . "\r\n                                            WHERE goods_id = " . $value['goods_id'] . ' AND region_id = ' . $value['area_id'];
							}
							else {
								$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . "\r\n                                            SET goods_number = goods_number - " . $value['sums'] . "\r\n                                            WHERE goods_id = " . $value['goods_id'];
							}

							$GLOBALS['db']->query($minus_stock_sql, 'SILENT');
							$logs_other = array('goods_id' => $value['goods_id'], 'order_id' => $value['order_id'], 'use_storage' => $_CFG['stock_dec_time'], 'admin_id' => $_SESSION['seller_id'], 'number' => '- ' . $value['sums'], 'model_inventory' => $value['model_inventory'], 'model_attr' => $value['model_attr'], 'product_id' => $value['product_id'], 'warehouse_id' => $value['warehouse_id'], 'area_id' => $value['area_id'], 'add_time' => gmtime());
							$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_inventory_logs'), $logs_other, 'INSERT');
						}
					}
				}

				$invoice_no = trim($invoice_no);
				$_delivery['invoice_no'] = $invoice_no;
				$_delivery['status'] = 0;
				$query = $db->autoExecute($ecs->table('delivery_order'), $_delivery, 'UPDATE', 'delivery_id = ' . $delivery_id, 'SILENT');

				if (!$query) {
					$links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
					sys_msg($_LANG['act_false'], 1, $links);
				}

				$order_finish = get_all_delivery_finish($order_id);
				$shipping_status = $order_finish == 1 ? SS_SHIPPED : SS_SHIPPED_PART;
				$arr['shipping_status'] = $shipping_status;
				$arr['shipping_time'] = GMTIME_UTC;
				$arr['invoice_no'] = trim($order['invoice_no'] . ',' . $invoice_no, ',');

				if (empty($order['pay_time'])) {
					$arr['pay_time'] = gmtime();
				}

				update_order($order_id, $arr);
				order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $action_note, $_SESSION['seller_name'], 1);

				if ($order_finish) {
					if (0 < $order['user_id']) {
						$user = user_info($order['user_id']);
						$integral = integral_to_give($order);

						if (!empty($child_order)) {
							$integral['custom_points'] = $integral['custom_points'] - $child_order['custom_points'];
							$integral['rank_points'] = $integral['rank_points'] - $child_order['rank_points'];
						}

						log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));
						send_order_bonus($order_id);
						send_order_coupons($order_id);
					}

					$cfg = $_CFG['send_ship_email'];

					if ($cfg == '1') {
						$order['invoice_no'] = $invoice_no;
						$tpl = get_mail_template('deliver_notice');
						$smarty->assign('order', $order);
						$smarty->assign('send_time', local_date($_CFG['time_format']));
						$smarty->assign('shop_name', $_CFG['shop_name']);
						$smarty->assign('send_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
						$smarty->assign('sent_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
						$smarty->assign('confirm_url', $ecs->url() . 'user.php?act=order_detail&order_id=' . $order['order_id']);
						$smarty->assign('send_msg_url', $ecs->url() . 'user.php?act=message_list&order_id=' . $order['order_id']);
						$content = $smarty->fetch('str:' . $tpl['template_content']);

						if (!send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
							$msg = $_LANG['send_mail_fail'];
						}
					}

					if ($GLOBALS['_CFG']['sms_order_shipped'] == '1' && $order['mobile'] != '') {
						if ($order['ru_id']) {
							$shop_name = get_shop_name($order['ru_id'], 1);
						}
						else {
							$shop_name = '';
						}

						$user_info = get_admin_user_info($order['user_id']);
						$smsParams = array('shop_name' => $shop_name, 'shopname' => $shop_name, 'user_name' => $user_info['user_name'], 'username' => $user_info['user_name'], 'consignee' => $order['consignee'], 'order_sn' => $order['order_sn'], 'ordersn' => $order['order_sn'], 'mobile_phone' => $order['mobile'], 'mobilephone' => $order['mobile']);

						if ($GLOBALS['_CFG']['sms_type'] == 0) {
							huyi_sms($smsParams, 'sms_order_shipped');
						}
						else if (1 <= $GLOBALS['_CFG']['sms_type']) {
							$result = sms_ali($smsParams, 'sms_order_shipped');

							if ($result) {
								$resp = $GLOBALS['ecs']->ali_yu($result);
							}
							else {
								sys_msg('阿里大鱼短信配置异常', 1);
							}
						}
					}

					$file = MOBILE_WECHAT . '/Controllers/IndexController.php';

					if (file_exists($file)) {
						$pushData = array(
							'first'    => array('value' => '您的订单已发货'),
							'keyword1' => array('value' => $order['order_sn']),
							'keyword2' => array('value' => $order['shipping_name']),
							'keyword3' => array('value' => $invoice_no),
							'keyword4' => array('value' => $order['consignee']),
							'remark'   => array('value' => '订单正在配送中，请您耐心等待')
							);
						$shop_url = str_replace('/seller', '', $GLOBALS['ecs']->url());
						$order_url = $shop_url . 'mobile/index.php?m=user&c=order&a=detail&order_id=' . $order_id;
						push_template_curl('OPENTM202243318', $pushData, $order_url, $order['user_id'], $shop_url);
					}

					get_goods_sale($order_id);
				}

				clear_cache_files();
				$links[] = array('text' => $_LANG['09_delivery_order'], 'href' => 'order.php?act=delivery_list');
				$links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
				sys_msg($_LANG['act_ok'], 0, $links);
			}
		}
		else if (isset($_POST['pay'])) {
			admin_priv('order_ps_edit');
			$require_note = $_CFG['order_pay_note'] == 1;
			$action = $_LANG['op_pay'];
			$operation = 'pay';
		}
		else if (isset($_POST['prepare'])) {
			$require_note = false;
			$action = $_LANG['op_prepare'];
			$operation = 'prepare';
		}
		else if (isset($_POST['ship'])) {
			admin_priv('order_ss_edit');
			$order_id = intval(trim($order_id));
			$action_note = trim($action_note);

			if (!empty($order_id)) {
				$order = order_info($order_id);
			}
			else {
				exit('order does not exist');
			}

			if (order_finished($order)) {
				admin_priv('order_view_finished');
			}
			else {
				admin_priv('order_view');
			}

			$sql = 'SELECT agency_id FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'');
			$agency_id = $db->getOne($sql);

			if (0 < $agency_id) {
				if ($order['agency_id'] != $agency_id) {
					sys_msg($_LANG['priv_error'], 0);
				}
			}

			if (0 < $order['user_id']) {
				$user = user_info($order['user_id']);

				if (!empty($user)) {
					$order['user_name'] = $user['user_name'];
				}
			}

			$order['region'] = get_user_region_address($order['order_id']);
			$order['order_time'] = local_date($_CFG['time_format'], $order['add_time']);
			$order['invoice_no'] = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $_LANG['ss'][SS_UNSHIPPED] : $order['invoice_no'];
			$order['pay_time'] = 0 < $order['pay_time'] ? local_date($_CFG['time_format'], $order['pay_time']) : $_LANG['ps'][PS_UNPAYED];
			$order['shipping_time'] = 0 < $order['shipping_time'] ? local_date($_CFG['time_format'], $order['shipping_time']) : $_LANG['ss'][SS_UNSHIPPED];
			$order['confirm_time'] = local_date($_CFG['time_format'], $order['confirm_time']);
			$order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
			$exist_real_goods = exist_real_goods($order_id);
			$_goods = get_order_goods(array('order_id' => $order['order_id'], 'order_sn' => $order['order_sn']));
			$attr = $_goods['attr'];
			$goods_list = $_goods['goods_list'];
			unset($_goods);

			if ($goods_list) {
				foreach ($goods_list as $key => $goods_value) {
					if (!$goods_value['goods_id']) {
						continue;
					}

					if ($goods_value['extension_code'] == 'package_buy' && 0 < count($goods_value['package_goods_list'])) {
						$goods_list[$key]['package_goods_list'] = package_goods($goods_value['package_goods_list'], $goods_value['goods_number'], $goods_value['order_id'], $goods_value['extension_code'], $goods_value['goods_id']);

						foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value) {
							$goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = '';
							if ($pg_value['storage'] <= 0 && $_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
								$goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
							}
							else if ($pg_value['send'] <= 0) {
								$goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
							}
						}
					}
					else {
						$goods_list[$key]['sended'] = $goods_value['send_number'];
						$goods_list[$key]['send'] = $goods_value['goods_number'] - $goods_value['send_number'];
						$goods_list[$key]['readonly'] = '';
						if ($goods_value['storage'] <= 0 && $_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
							$goods_list[$key]['send'] = $_LANG['act_good_vacancy'];
							$goods_list[$key]['readonly'] = 'readonly="readonly"';
						}
						else if ($goods_list[$key]['send'] <= 0) {
							$goods_list[$key]['send'] = $_LANG['act_good_delivery'];
							$goods_list[$key]['readonly'] = 'readonly="readonly"';
						}
					}
				}
			}

			$smarty->assign('order', $order);
			$smarty->assign('exist_real_goods', $exist_real_goods);
			$smarty->assign('goods_attr', $attr);
			$smarty->assign('goods_list', $goods_list);
			$smarty->assign('order_id', $order_id);
			$smarty->assign('operation', 'split');
			$smarty->assign('action_note', $action_note);
			$suppliers_list = get_suppliers_list();
			$suppliers_list_count = count($suppliers_list);
			$smarty->assign('suppliers_name', suppliers_list_name());
			$smarty->assign('suppliers_list', $suppliers_list_count == 0 ? 0 : $suppliers_list);
			$smarty->assign('menu_select', array('action' => '04_order', 'current' => '09_delivery_order'));
			$smarty->assign('ur_here', $_LANG['order_operate'] . $_LANG['op_split']);
			$act_list = array();
			$sql = 'SELECT * FROM ' . $ecs->table('order_action') . (' WHERE order_id = \'' . $order_id . '\' ORDER BY log_time DESC,action_id DESC');
			$res = $db->query($sql);

			while ($row = $db->fetchRow($res)) {
				$row['order_status'] = $_LANG['os'][$row['order_status']];
				$row['pay_status'] = $_LANG['ps'][$row['pay_status']];
				$row['shipping_status'] = $_LANG['ss'][$row['shipping_status']];
				$row['action_time'] = local_date($_CFG['time_format'], $row['log_time']);
				$act_list[] = $row;
			}

			$smarty->assign('action_list', $act_list);
			assign_query_info();
			$smarty->display('order_delivery_info.dwt');
			exit();
		}
		else if (isset($_POST['unship'])) {
			admin_priv('order_ss_edit');
			$require_note = $_CFG['order_unship_note'] == 1;
			$action = $_LANG['op_unship'];
			$operation = 'unship';
		}
		else if (isset($_POST['receive'])) {
			$require_note = $_CFG['order_receive_note'] == 1;
			$action = $_LANG['op_receive'];
			$operation = 'receive';
		}
		else if (isset($_POST['cancel'])) {
			$require_note = $_CFG['order_cancel_note'] == 1;
			$action = $_LANG['op_cancel'];
			$operation = 'cancel';
			$show_cancel_note = true;
			$order = order_info($order_id);

			if (0 < $order['pay_status']) {
				$show_refund = true;
			}

			$anonymous = $order['user_id'] == 0;
		}
		else if (isset($_POST['invalid'])) {
			$require_note = $_CFG['order_invalid_note'] == 1;
			$action = $_LANG['op_invalid'];
			$operation = 'invalid';
		}
		else if (isset($_POST['after_service'])) {
			$require_note = true;
			$action = $_LANG['op_after_service'];
			$operation = 'after_service';
		}
		else if (isset($_POST['return'])) {
			$sql = 'SELECT ret_id FROM ' . $ecs->table('order_return') . ' WHERE order_id = \'' . $order_id . '\'';
			$ret_id = $db->getOne($sql);

			if (0 < $ret_id) {
				$links[] = array('text' => $_LANG['go_back'], 'href' => 'order.php?act=info&order_id=' . $order_id);
				sys_msg('该订单存在退换货商品，不能退货', 0, $links);
			}
			else {
				$require_note = $_CFG['order_return_note'] == 1;
				$order = order_info($order_id);

				if (0 < $order['pay_status']) {
					$show_refund = true;
				}

				$anonymous = $order['user_id'] == 0;
				$action = $_LANG['op_return'];
				$operation = 'return';
			}

			$sql = 'SELECT vc_id, use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . ' WHERE order_id = \'' . $order['order_id'] . '\' LIMIT 1';
			$value_card = $GLOBALS['db']->getRow($sql);
			$paid_amount = $order['money_paid'] + $order['surplus'];
			if (0 < $paid_amount && 0 < $order['shipping_fee'] && $order['shipping_fee'] <= $paid_amount) {
				$refound_amount = $paid_amount - $order['shipping_fee'];
			}
			else {
				$refound_amount = $paid_amount;
			}

			$smarty->assign('refound_amount', $refound_amount);
			$smarty->assign('shipping_fee', $order['shipping_fee']);
			$smarty->assign('value_card', $value_card);
			$smarty->assign('is_whole', 1);
		}
		else if (isset($_POST['agree_apply'])) {
			$require_note = false;
			$action = $_LANG['op_confirm'];
			$operation = 'agree_apply';
		}
		else if (isset($_POST['refound'])) {
			$require_note = $_CFG['order_return_note'] == 1;
			$order = order_info($order_id);
			$refound_amount = empty($_REQUEST['refound_amount']) ? 0 : floatval($_REQUEST['refound_amount']);
			$return_shipping_fee = empty($_REQUEST['return_shipping_fee']) ? 0 : floatval($_REQUEST['return_shipping_fee']);
			$is_refound_shippfee = order_refound_shipping_fee($order_id, $ret_id);
			$is_refound_shippfee_amount = $is_refound_shippfee + $return_shipping_fee;
			if ($order['shipping_fee'] < $is_refound_shippfee_amount || $return_shipping_fee == 0 && 0 < $is_refound_shippfee) {
				$return_shipping_fee = $order['shipping_fee'] - $is_refound_shippfee;
			}
			else {
				if ($return_shipping_fee == 0 && $is_refound_shippfee == 0) {
					$return_shipping_fee = $order['shipping_fee'];
				}
			}

			$count_goods = $db->getAll(' SELECT rec_id ,goods_id FROM ' . $ecs->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\''));

			if (1 < count($count_goods)) {
				foreach ($count_goods as $k => $v) {
					$all_goods_id[] = $v['goods_id'];
				}

				$count_integral = $db->getOne(' SELECT sum(integral) FROM' . $ecs->table('goods') . ' WHERE  goods_id' . db_create_in($all_goods_id));
				$return_integral = $db->getOne(' SELECT g.integral FROM' . $ecs->table('goods') . ' as g LEFT JOIN ' . $ecs->table('order_return') . (' as o on o.goods_id = g.goods_id  WHERE o.ret_id = \'' . $ret_id . '\''));
				$count_integral = !empty($count_integral) ? $count_integral : 1;
				$return_ratio = $return_integral / $count_integral;
				$return_price = (empty($order['pay_points']) ? '' : $order['pay_points']) * $return_ratio;
			}
			else {
				$return_price = empty($order['pay_points']) ? '' : $order['pay_points'];
			}

			$goods_number = $GLOBALS['db']->getOne(' SELECT goods_number FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE rec_id = \'' . $rec_id . '\''));
			$return_number = $GLOBALS['db']->getOne(' SELECT return_number FROM ' . $GLOBALS['ecs']->table('order_return_extend') . (' WHERE ret_id = \'' . $ret_id . '\''));

			if ($return_number < $goods_number) {
				$refound_pay_points = intval($return_price * ($return_number / $goods_number));
			}
			else {
				$refound_pay_points = intval($return_price);
			}

			if (0 < $order['pay_status']) {
				$show_refund1 = true;
			}

			$anonymous = $order['user_id'] == 0;
			$action = $_LANG['op_return'];
			$operation = 'refound';
			$sql = 'SELECT vc_id, use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . ' WHERE order_id = \'' . $order['order_id'] . '\' LIMIT 1';
			$value_card = $GLOBALS['db']->getRow($sql);
			$return_order = return_order_info($ret_id);
			$should_return = $return_order['should_return'] - $return_order['discount_amount'];

			if ($value_card) {
				if ($should_return < $value_card['use_val']) {
					$value_card['use_val'] = $should_return;
				}
			}

			$paid_amount = $order['money_paid'] + $order['surplus'];
			if (0 < $paid_amount && $order['shipping_fee'] <= $paid_amount) {
				$paid_amount = $paid_amount - $order['shipping_fee'];
			}

			if ($paid_amount < $refound_amount) {
				$refound_amount = $paid_amount;
			}

			$smarty->assign('refound_pay_points', $refound_pay_points);
			$smarty->assign('refound_amount', $refound_amount);
			$smarty->assign('shipping_fee', $return_shipping_fee);
			$smarty->assign('value_card', $value_card);
			$is_whole = 0;
			$is_diff = get_order_return_rec($order['order_id']);

			if ($is_diff) {
				$return_count = return_order_info_byId($order['order_id'], 0);

				if ($return_count == 1) {
					$is_whole = 1;
				}
			}

			$smarty->assign('is_whole', $is_whole);
		}
		else if (isset($_POST['receive_goods'])) {
			$require_note = false;
			$action = $_LANG['op_confirm'];
			$operation = 'receive_goods';
		}
		else if (isset($_POST['send_submit'])) {
			$shipping_id = $_POST['shipping_name'];
			$invoice_no = $_POST['invoice_no'];
			$action_note = $_POST['action_note'];
			$sql = 'SELECT shipping_name FROM ' . $ecs->table('shipping') . ' WHERE shipping_id =' . $shipping_id;
			$shipping_name = $db->getOne($sql);
			$require_note = false;
			$action = $_LANG['op_confirm'];
			$operation = 'receive_goods';
			$db->query('UPDATE ' . $ecs->table('order_return') . (' SET out_shipping_name = \'' . $shipping_id . '\' ,out_invoice_no =\'' . $invoice_no . '\'') . ('WHERE rec_id = \'' . $rec_id . '\''));
		}
		else if (isset($_POST['swapped_out'])) {
			$require_note = false;
			$action = $_LANG['op_confirm'];
			$operation = 'swapped_out';
		}
		else if (isset($_POST['swapped_out_single'])) {
			$require_note = false;
			$action = $_LANG['op_confirm'];
			$operation = 'swapped_out_single';
		}
		else if (isset($_POST['complete'])) {
			$require_note = false;
			$action = $_LANG['op_confirm'];
			$operation = 'complete';
		}
		else if (isset($_POST['refuse_apply'])) {
			$require_note = true;
			$action = $_LANG['refuse_apply'];
			$operation = 'refuse_apply';
		}
		else if (isset($_POST['assign'])) {
			$new_agency_id = isset($_POST['agency_id']) ? intval($_POST['agency_id']) : 0;

			if ($new_agency_id == 0) {
				sys_msg($_LANG['js_languages']['pls_select_agency']);
			}

			$order = order_info($order_id);
			$sql = 'SELECT agency_id FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'');
			$admin_agency_id = $db->getOne($sql);

			if (0 < $admin_agency_id) {
				if ($order['agency_id'] != $admin_agency_id) {
					sys_msg($_LANG['priv_error']);
				}
			}

			if ($new_agency_id != $order['agency_id']) {
				$query_array = array('order_info', 'delivery_order', 'back_order');

				foreach ($query_array as $value) {
					$db->query('UPDATE ' . $ecs->table($value) . (' SET agency_id = \'' . $new_agency_id . '\' ') . ('WHERE order_id = \'' . $order_id . '\''));
				}
			}

			$links[] = array('href' => 'order.php?act=list&' . list_link_postfix(), 'text' => $_LANG['02_order_list']);
			sys_msg($_LANG['act_ok'], 0, $links);
		}
		else if (isset($_POST['remove'])) {
			$require_note = false;
			$operation = 'remove';

			if (!$batch) {
				$order = order_info($order_id);

				if ($order['ru_id'] != $adminru['ru_id']) {
					sys_msg($_LANG['order_removed'], 0, array(
	array('href' => 'order.php?act=list&' . list_link_postfix(), 'text' => $_LANG['return_list'])
	));
					exit();
				}

				$operable_list = operable_list($order);

				if (!isset($operable_list['remove'])) {
					exit('Hacking attempt');
				}

				$return_order = return_order_info(0, '', $order['order_id']);

				if ($return_order) {
					sys_msg(sprintf($_LANG['order_remove_failure'], $order['order_sn']), 0, array(
	array('href' => 'order.php?act=list&' . list_link_postfix(), 'text' => $_LANG['return_list'])
	));
					exit();
				}

				$db->query('DELETE FROM ' . $ecs->table('order_info') . (' WHERE order_id = \'' . $order_id . '\''));
				$db->query('DELETE FROM ' . $ecs->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\''));
				$db->query('DELETE FROM ' . $ecs->table('order_action') . (' WHERE order_id = \'' . $order_id . '\''));
				$db->query('DELETE FROM ' . $ecs->table('store_order') . (' WHERE order_id = \'' . $order_id . '\''));
				$action_array = array('delivery', 'back');
				del_delivery($order_id, $action_array);
				admin_log($order['order_sn'], 'remove', 'order');
				sys_msg($_LANG['order_removed'], 0, array(
	array('href' => 'order.php?act=list&' . list_link_postfix(), 'text' => $_LANG['return_list'])
	));
			}
		}
		else if (isset($_REQUEST['remove_invoice'])) {
			$delivery_id = isset($_REQUEST['delivery_id']) ? $_REQUEST['delivery_id'] : $_REQUEST['checkboxes'];
			$delivery_id = is_array($delivery_id) ? $delivery_id : array($delivery_id);

			foreach ($delivery_id as $value_is) {
				$value_is = intval(trim($value_is));
				$delivery_order = delivery_order_info($value_is);

				if ($delivery_order['status'] != 1) {
					delivery_return_goods($value_is, $delivery_order);
				}

				if ($delivery_order['status'] == 0 && $delivery_order['invoice_no'] != '') {
					del_order_invoice_no($delivery_order['order_id'], $delivery_order['invoice_no']);
				}

				$sql = 'DELETE FROM ' . $ecs->table('delivery_order') . (' WHERE delivery_id = \'' . $value_is . '\'');
				$db->query($sql);
			}

			sys_msg($_LANG['tips_delivery_del'], 0, array(
	array('href' => 'order.php?act=delivery_list', 'text' => $_LANG['return_list'])
	));
		}
		else if (isset($_REQUEST['remove_back'])) {
			$back_id = isset($_REQUEST['back_id']) ? $_REQUEST['back_id'] : $_POST['checkboxes'];

			if (is_array($back_id)) {
				foreach ($back_id as $value_is) {
					$sql = 'DELETE FROM ' . $ecs->table('back_order') . (' WHERE back_id = \'' . $value_is . '\'');
					$db->query($sql);
				}
			}
			else {
				$sql = 'DELETE FROM ' . $ecs->table('back_order') . (' WHERE back_id = \'' . $back_id . '\'');
				$db->query($sql);
			}

			sys_msg($_LANG['tips_back_del'], 0, array(
	array('href' => 'order.php?act=back_list', 'text' => $_LANG['return_list'])
	));
		}
		else if (isset($_POST['print'])) {
			if (empty($_POST['order_id'])) {
				sys_msg($_LANG['pls_select_order']);
			}

			$url = 'tp_api.php?act=order_print&order_sn=' . $_POST['order_id'];
			ecs_header('Location: ' . $url . "\n");
			exit();
			$smarty->assign('print_time', local_date($_CFG['time_format']));
			$smarty->assign('action_user', $_SESSION['seller_name']);
			$html = '';
			$order_sn_list = explode(',', $_POST['order_id']);

			foreach ($order_sn_list as $order_sn) {
				if ($order_sn) {
					$order = order_info(0, $order_sn);

					if (empty($order)) {
						continue;
					}

					if (order_finished($order)) {
						if (!admin_priv('order_view_finished', '', false)) {
							continue;
						}
					}
					else if (!admin_priv('order_view', '', false)) {
						continue;
					}

					$sql = 'SELECT agency_id FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'');
					$agency_id = $db->getOne($sql);

					if (0 < $agency_id) {
						if ($order['agency_id'] != $agency_id) {
							continue;
						}
					}

					if (0 < $order['user_id']) {
						$user = user_info($order['user_id']);

						if (!empty($user)) {
							$order['user_name'] = $user['user_name'];
						}
					}

					$order['region'] = get_user_region_address($order['order_id']);
					$order['order_time'] = local_date($_CFG['time_format'], $order['add_time']);
					$order['pay_time'] = 0 < $order['pay_time'] ? local_date($_CFG['time_format'], $order['pay_time']) : $_LANG['ps'][PS_UNPAYED];
					$order['shipping_time'] = 0 < $order['shipping_time'] ? local_date($_CFG['time_format'], $order['shipping_time']) : $_LANG['ss'][SS_UNSHIPPED];
					$order['status'] = $_LANG['os'][$order['order_status']] . ',' . $_LANG['ps'][$order['pay_status']] . ',' . $_LANG['ss'][$order['shipping_status']];
					$order['invoice_no'] = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $_LANG['ss'][SS_UNSHIPPED] : $order['invoice_no'];
					$sql = 'SELECT action_note FROM ' . $ecs->table('order_action') . (' WHERE order_id = \'' . $order['order_id'] . '\' AND shipping_status = 1 ORDER BY log_time DESC');
					$order['invoice_note'] = $db->getOne($sql);
					$smarty->assign('order', $order);
					$goods_list = array();
					$goods_attr = array();
					$sql = 'SELECT o.*, c.measure_unit, g.goods_number AS storage, o.goods_attr, IFNULL(b.brand_name, \'\') AS brand_name, g.bar_code ' . 'FROM ' . $ecs->table('order_goods') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('goods') . ' AS g ON o.goods_id = g.goods_id ' . 'LEFT JOIN ' . $ecs->table('brand') . ' AS b ON g.brand_id = b.brand_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('category') . ' AS c ON g.cat_id = c.cat_id ' . ('WHERE o.order_id = \'' . $order['order_id'] . '\' ');
					$res = $db->query($sql);

					while ($row = $db->fetchRow($res)) {
						$products = get_warehouse_id_attr_number($row['goods_id'], $row['goods_attr_id'], $row['ru_id'], $row['warehouse_id'], $row['area_id'], $row['model_attr']);

						if ($row['product_id']) {
							$row['bar_code'] = $products['bar_code'];
						}

						if ($row['is_real'] == 0) {
							$filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $_CFG['lang'] . '.php';

							if (file_exists($filename)) {
								include_once $filename;

								if (!empty($_LANG[$row['extension_code'] . '_link'])) {
									$row['goods_name'] = $row['goods_name'] . sprintf($_LANG[$row['extension_code'] . '_link'], $row['goods_id'], $order['order_sn']);
								}
							}
						}

						$row['formated_subtotal'] = price_format($row['goods_price'] * $row['goods_number']);
						$row['formated_goods_price'] = price_format($row['goods_price']);
						$goods_attr[] = explode(' ', trim($row['goods_attr']));
						$goods_list[] = $row;
					}

					$attr = array();
					$arr = array();

					foreach ($goods_attr as $index => $array_val) {
						foreach ($array_val as $value) {
							$arr = explode(':', $value);
							$attr[$index][] = array('name' => $arr[0], 'value' => $arr[1]);
						}
					}

					$sql = 'select shop_name,country,province,city,shop_address,kf_tel from ' . $ecs->table('seller_shopinfo') . ' where ru_id=\'' . $order['ru_id'] . '\'';
					$store = $db->getRow($sql);
					$store['shop_name'] = get_shop_name($order['ru_id'], 1);
					$sql = 'SELECT domain_name FROM ' . $ecs->table('seller_domain') . ' WHERE ru_id = \'' . $order['ru_id'] . '\' AND  is_enable = 1';
					$domain_name = $db->getOne($sql);
					$smarty->assign('domain_name', $domain_name);
					$smarty->assign('shop_name', $store['shop_name']);
					$smarty->assign('shop_url', $ecs->seller_url());
					$smarty->assign('shop_address', $store['shop_address']);
					$smarty->assign('service_phone', $store['kf_tel']);
					$smarty->assign('goods_attr', $attr);
					$smarty->assign('goods_list', $goods_list);
					$smarty->template_dir = '../' . DATA_DIR;
					$html .= $smarty->fetch('order_print.html') . '<div style="PAGE-BREAK-AFTER:always"></div>';
				}
			}

			echo $html;
			exit();
		}
		else if (isset($_POST['to_delivery'])) {
			$url = 'order.php?act=delivery_list&order_sn=' . $_REQUEST['order_sn'];
			ecs_header('Location: ' . $url . "\n");
			exit();
		}
		else if (isset($_REQUEST['batch_delivery'])) {
			admin_priv('delivery_view');
			define('GMTIME_UTC', gmtime());
			$delivery_id = isset($_REQUEST['delivery_id']) ? $_REQUEST['delivery_id'] : $_REQUEST['checkboxes'];
			$delivery_id = is_array($delivery_id) ? $delivery_id : array($delivery_id);
			$invoice_nos = isset($_REQUEST['invoice_no']) ? $_REQUEST['invoice_no'] : array();
			$action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';

			foreach ($delivery_id as $value_is) {
				$msg = '';
				$value_is = intval(trim($value_is));
				$delivery_info = get_table_date('delivery_order', 'delivery_id=\'' . $value_is . '\'', array('order_id', 'status'));
				if ($delivery_info['status'] != 2 || !isset($invoice_nos[$value_is])) {
					continue;
				}

				$delivery = array();
				$order_id = $delivery_info['order_id'];
				$delivery_id = $value_is;
				$delivery['invoice_no'] = $invoice_nos[$value_is];
				$action_note = $action_note;

				if (!empty($delivery_id)) {
					$delivery_order = delivery_order_info($delivery_id);
				}
				else {
					exit('order does not exist');
				}

				$order = order_info($order_id);
				$delivery_stock_sql = 'SELECT DG.rec_id AS dg_rec_id, OG.rec_id AS og_rec_id, G.model_attr, G.model_inventory, DG.goods_id, DG.delivery_id, DG.is_real, DG.send_number AS sums, G.goods_number AS storage, G.goods_name, DG.send_number,' . ' OG.goods_attr_id, OG.warehouse_id, OG.area_id, OG.ru_id, OG.order_id, OG.product_id FROM ' . $GLOBALS['ecs']->table('delivery_goods') . ' AS DG, ' . $GLOBALS['ecs']->table('goods') . ' AS G, ' . $GLOBALS['ecs']->table('delivery_order') . ' AS D, ' . $GLOBALS['ecs']->table('order_goods') . ' AS OG ' . (' WHERE DG.goods_id = G.goods_id AND DG.delivery_id = D.delivery_id AND D.order_id = OG.order_id AND DG.goods_sn = OG.goods_sn AND DG.product_id = OG.product_id AND DG.delivery_id = \'' . $delivery_id . '\' GROUP BY OG.rec_id ');
				$delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);
				$virtual_goods = array();

				for ($i = 0; $i < count($delivery_stock_result); $i++) {
					if ($delivery_stock_result[$i]['model_attr'] == 1) {
						$table_products = 'products_warehouse';
						$type_files = ' and warehouse_id = \'' . $delivery_stock_result[$i]['warehouse_id'] . '\'';
					}
					else if ($delivery_stock_result[$i]['model_attr'] == 2) {
						$table_products = 'products_area';
						$type_files = ' and area_id = \'' . $delivery_stock_result[$i]['area_id'] . '\'';
					}
					else {
						$table_products = 'products';
						$type_files = '';
					}

					$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $delivery_stock_result[$i]['goods_id'] . '\'' . $type_files . ' LIMIT 0, 1';
					$prod = $GLOBALS['db']->getRow($sql);

					if (empty($prod)) {
						if ($delivery_stock_result[$i]['model_inventory'] == 1) {
							$delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_goods');
						}
						else if ($delivery_stock_result[$i]['model_inventory'] == 2) {
							$delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_area_goods');
						}
					}
					else {
						$products = get_warehouse_id_attr_number($delivery_stock_result[$i]['goods_id'], $delivery_stock_result[$i]['goods_attr_id'], $delivery_stock_result[$i]['ru_id'], $delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['model_attr']);
						$delivery_stock_result[$i]['storage'] = $products['product_number'];
					}

					if (($delivery_stock_result[$i]['storage'] < $delivery_stock_result[$i]['sums'] || $delivery_stock_result[$i]['storage'] <= 0) && ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP || $_CFG['use_storage'] == '0' && $delivery_stock_result[$i]['is_real'] == 0)) {
						$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
						break;
					}

					if ($delivery_stock_result[$i]['is_real'] == 0) {
						$virtual_goods[] = array('goods_id' => $delivery_stock_result[$i]['goods_id'], 'goods_name' => $delivery_stock_result[$i]['goods_name'], 'num' => $delivery_stock_result[$i]['send_number']);
					}
				}

				if ($virtual_goods && is_array($virtual_goods) && 0 < count($virtual_goods)) {
					foreach ($virtual_goods as $virtual_value) {
						virtual_card_shipping($virtual_value, $order['order_sn'], $msg, 'split');
					}

					if (!empty($msg)) {
						continue;
					}
				}

				if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
					foreach ($delivery_stock_result as $value) {
						if ($value['is_real'] != 0) {
							if (!empty($value['product_id'])) {
								if ($value['model_attr'] == 1) {
									$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('products_warehouse') . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tSET product_number = product_number - " . $value['sums'] . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tWHERE product_id = " . $value['product_id'];
								}
								else if ($value['model_attr'] == 2) {
									$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('products_area') . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tSET product_number = product_number - " . $value['sums'] . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tWHERE product_id = " . $value['product_id'];
								}
								else {
									$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('products') . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tSET product_number = product_number - " . $value['sums'] . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tWHERE product_id = " . $value['product_id'];
								}
							}
							else if ($value['model_inventory'] == 1) {
								$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('warehouse_goods') . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tSET region_number = region_number - " . $value['sums'] . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tWHERE goods_id = " . $value['goods_id'] . ' AND region_id = ' . $value['warehouse_id'];
							}
							else if ($value['model_inventory'] == 2) {
								$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('warehouse_area_goods') . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tSET region_number = region_number - " . $value['sums'] . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tWHERE goods_id = " . $value['goods_id'] . ' AND region_id = ' . $value['area_id'];
							}
							else {
								$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tSET goods_number = goods_number - " . $value['sums'] . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tWHERE goods_id = " . $value['goods_id'];
							}

							$GLOBALS['db']->query($minus_stock_sql, 'SILENT');
							$logs_other = array('goods_id' => $value['goods_id'], 'order_id' => $value['order_id'], 'use_storage' => $_CFG['stock_dec_time'], 'admin_id' => $_SESSION['admin_id'], 'number' => '- ' . $value['sums'], 'model_inventory' => $value['model_inventory'], 'model_attr' => $value['model_attr'], 'product_id' => $value['product_id'], 'warehouse_id' => $value['warehouse_id'], 'area_id' => $value['area_id'], 'add_time' => gmtime());
							$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_inventory_logs'), $logs_other, 'INSERT');
						}
					}
				}

				$invoice_no = str_replace(',', ',', $delivery['invoice_no']);
				$invoice_no = trim($invoice_no, ',');
				$_delivery['invoice_no'] = $invoice_no;
				$_delivery['status'] = 0;
				$query = $db->autoExecute($ecs->table('delivery_order'), $_delivery, 'UPDATE', 'delivery_id = ' . $delivery_id, 'SILENT');

				if (!$query) {
					$links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
					continue;
				}

				$order_finish = get_all_delivery_finish($order_id);
				$shipping_status = $order_finish == 1 ? SS_SHIPPED : SS_SHIPPED_PART;
				$arr['shipping_status'] = $shipping_status;
				$arr['shipping_time'] = GMTIME_UTC;
				$arr['invoice_no'] = trim($order['invoice_no'] . ',' . $invoice_no, ',');
				update_order($order_id, $arr);

				if (empty($_delivery['invoice_no'])) {
					$_delivery['invoice_no'] = 'N/A';
				}

				$_note = sprintf($_LANG['order_ship_delivery'], $delivery_order['delivery_sn']) . '<br/>' . sprintf($_LANG['order_ship_invoice'], $_delivery['invoice_no']);
				order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $_note . $action_note, $_SESSION['seller_name'], 1);

				if ($order_finish) {
					if (0 < $order['user_id']) {
						$user = user_info($order['user_id']);
						$integral = integral_to_give($order);

						if (!empty($child_order)) {
							$integral['custom_points'] = $integral['custom_points'] - $child_order['custom_points'];
							$integral['rank_points'] = $integral['rank_points'] - $child_order['rank_points'];
						}

						log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));
						send_order_bonus($order_id);
						send_order_coupons($order_id);
					}

					$cfg = $_CFG['send_ship_email'];

					if ($cfg == '1') {
						$order['invoice_no'] = $invoice_no;
						$tpl = get_mail_template('deliver_notice');
						$smarty->assign('order', $order);
						$smarty->assign('send_time', local_date($_CFG['time_format']));
						$smarty->assign('shop_name', $_CFG['shop_name']);
						$smarty->assign('send_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
						$smarty->assign('sent_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
						$smarty->assign('confirm_url', $ecs->url() . 'user.php?act=order_detail&order_id=' . $order['order_id']);
						$smarty->assign('send_msg_url', $ecs->url() . 'user.php?act=message_list&order_id=' . $order['order_id']);
						$content = $smarty->fetch('str:' . $tpl['template_content']);

						if (!send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
							$msg = $_LANG['send_mail_fail'];
						}
					}

					if ($GLOBALS['_CFG']['sms_order_shipped'] == '1' && $order['mobile'] != '') {
						if ($order['ru_id']) {
							$shop_name = get_shop_name($order['ru_id'], 1);
						}
						else {
							$shop_name = '';
						}

						$user_info = get_admin_user_info($order['user_id']);
						$smsParams = array('shop_name' => $shop_name, 'shopname' => $shop_name, 'user_name' => $user_info['user_name'], 'username' => $user_info['user_name'], 'consignee' => $order['consignee'], 'order_sn' => $order['order_sn'], 'ordersn' => $order['order_sn'], 'mobile_phone' => $order['mobile'], 'mobilephone' => $order['mobile']);

						if ($GLOBALS['_CFG']['sms_type'] == 0) {
							huyi_sms($smsParams, 'sms_order_shipped');
						}
						else if (1 <= $GLOBALS['_CFG']['sms_type']) {
							$result = sms_ali($smsParams, 'sms_order_shipped');

							if ($result) {
								$resp = $GLOBALS['ecs']->ali_yu($result);
							}
							else {
								continue;
							}
						}
					}

					$file = MOBILE_WECHAT . '/Controllers/IndexController.php';

					if (file_exists($file)) {
						$pushData = array(
							'first'    => array('value' => '您的订单已发货'),
							'keyword1' => array('value' => $order['order_sn']),
							'keyword2' => array('value' => $order['shipping_name']),
							'keyword3' => array('value' => $invoice_no),
							'keyword4' => array('value' => $order['consignee']),
							'remark'   => array('value' => '订单正在配送中，请您耐心等待')
							);
						$shop_url = str_replace('/seller', '', $GLOBALS['ecs']->url());
						$order_url = $shop_url . 'mobile/index.php?m=user&c=order&a=detail&order_id=' . $order_id;
						push_template_curl('OPENTM202243318', $pushData, $order_url, $order['user_id'], $shop_url);
					}

					get_goods_sale($order_id);
				}

				clear_cache_files();
				$links[] = array('text' => $_LANG['09_delivery_order'], 'href' => 'order.php?act=delivery_list');
				$links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
				continue;
			}

			sys_msg('批量发货成功', 0, array(
	array('href' => 'order.php?act=delivery_list', 'text' => $_LANG['return_list'])
	));
		}

		$sql = 'select log_id from ' . $ecs->table('baitiao_log') . ' where order_id' . db_create_in(explode(',', $order_id));
		$baitiao = $db->getOne($sql);

		if ($baitiao) {
			$smarty->assign('is_baitiao', $baitiao);
		}

		if ($require_note || isset($show_invoice_no) || isset($show_refund)) {
			$smarty->assign('require_note', $require_note);
			$smarty->assign('action_note', $action_note);
			$smarty->assign('show_cancel_note', isset($show_cancel_note));
			$smarty->assign('show_invoice_no', isset($show_invoice_no));
			$smarty->assign('show_refund', isset($show_refund));
			$smarty->assign('show_refund1', isset($show_refund1));
			$smarty->assign('anonymous', isset($anonymous) ? $anonymous : true);
			$smarty->assign('order_id', $order_id);
			$smarty->assign('rec_id', $rec_id);
			$smarty->assign('ret_id', $ret_id);
			$smarty->assign('batch', $batch);
			$smarty->assign('operation', $operation);
			$smarty->assign('menu_select', array('action' => '04_order', 'current' => '12_back_apply'));
			$smarty->assign('ur_here', $_LANG['order_operate'] . $action);
			assign_query_info();
			$smarty->display('order_operate.dwt');
		}
		else if (!$batch) {
			if ($_REQUEST['ret_id']) {
				ecs_header('Location: order.php?act=operate_post&order_id=' . $order_id . '&operation=' . $operation . '&action_note=' . urlencode($action_note) . '&rec_id=' . $rec_id . '&ret_id=' . $ret_id . "\n");
				exit();
			}
			else {
				ecs_header('Location: order.php?act=operate_post&order_id=' . $order_id . '&operation=' . $operation . '&action_note=' . urlencode($action_note) . "\n");
				exit();
			}
		}
		else {
			ecs_header('Location: order.php?act=batch_operate_post&order_id=' . $order_id . '&operation=' . $operation . '&action_note=' . urlencode($action_note) . "\n");
			exit();
		}
	}
	else if ($_REQUEST['act'] == 'batch_operate_post') {
		admin_priv('order_os_edit');
		$smarty->assign('menu_select', array('action' => '04_order', 'current' => '02_order_list'));
		$order_id = $_REQUEST['order_id'];
		$operation = $_REQUEST['operation'];
		$action_note = $_REQUEST['action_note'];

		if ($order_id) {
			$order_id = get_del_str_comma($order_id);
			$order_id_list = explode(',', $order_id);
		}
		else {
			$order_id_list = array();
		}

		$sn_list = array();
		$sn_not_list = array();

		if ('confirm' == $operation) {
			foreach ($order_id_list as $id_order) {
				$sql = 'SELECT * FROM ' . $ecs->table('order_info') . (' WHERE order_sn = \'' . $id_order . '\'') . ' AND order_status = \'' . OS_UNCONFIRMED . '\'';
				$order = $db->getRow($sql);

				if ($order) {
					$operable_list = operable_list($order);

					if (!isset($operable_list[$operation])) {
						$sn_not_list[] = $id_order;
						continue;
					}

					if ($order['order_status'] == OS_RETURNED || $order['order_status'] == OS_RETURNED_PART) {
						continue;
					}

					$order_id = $order['order_id'];
					update_order($order_id, array('order_status' => OS_CONFIRMED, 'confirm_time' => gmtime()));
					update_order_amount($order_id);
					order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_UNPAYED, $action_note, $_SESSION['seller_name']);

					if ($_CFG['send_confirm_email'] == '1') {
						$tpl = get_mail_template('order_confirm');
						$order['formated_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $order['add_time']);
						$smarty->assign('order', $order);
						$smarty->assign('shop_name', $_CFG['shop_name']);
						$smarty->assign('send_date', local_date($_CFG['date_format']));
						$smarty->assign('sent_date', local_date($_CFG['date_format']));
						$content = $smarty->fetch('str:' . $tpl['template_content']);
						send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
					}

					$sn_list[] = $order['order_sn'];
				}
				else {
					$sn_not_list[] = $id_order;
				}
			}

			$sn_str = $_LANG['confirm_order'];
			$smarty->assign('ur_here', $_LANG['order_operate'] . $_LANG['op_confirm']);
		}
		else if ('invalid' == $operation) {
			foreach ($order_id_list as $id_order) {
				$sql = 'SELECT * FROM ' . $ecs->table('order_info') . (' WHERE order_sn = ' . $id_order) . order_query_sql('unpay_unship');
				$order = $db->getRow($sql);
				$store_order_id = get_store_id($order['order_id']);
				$store_id = 0 < $store_order_id ? $store_order_id : 0;

				if ($order) {
					$operable_list = operable_list($order);

					if (!isset($operable_list[$operation])) {
						$sn_not_list[] = $id_order;
						continue;
					}

					$order_id = $order['order_id'];
					update_order($order_id, array('order_status' => OS_INVALID));
					order_action($order['order_sn'], OS_INVALID, SS_UNSHIPPED, PS_UNPAYED, $action_note, $_SESSION['seller_name']);
					if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE) {
						change_order_goods_storage($order_id, false, SDT_PLACE, 2, $_SESSION['seller_id'], $store_id);
					}

					if ($_CFG['send_invalid_email'] == '1') {
						$tpl = get_mail_template('order_invalid');
						$smarty->assign('order', $order);
						$smarty->assign('shop_name', $_CFG['shop_name']);
						$smarty->assign('send_date', local_date($_CFG['date_format']));
						$smarty->assign('sent_date', local_date($_CFG['date_format']));
						$content = $smarty->fetch('str:' . $tpl['template_content']);
						send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
					}

					return_user_surplus_integral_bonus($order);
					$sn_list[] = $order['order_sn'];
				}
				else {
					$sn_not_list[] = $id_order;
				}
			}

			$sn_str = $_LANG['invalid_order'];
		}
		else if ('cancel' == $operation) {
			foreach ($order_id_list as $id_order) {
				$sql = 'SELECT * FROM ' . $ecs->table('order_info') . (' WHERE order_sn = ' . $id_order) . order_query_sql('unpay_unship');
				$order = $db->getRow($sql);
				$store_order_id = get_store_id($order['order_id']);
				$store_id = 0 < $store_order_id ? $store_order_id : 0;

				if ($order) {
					$operable_list = operable_list($order);

					if (!isset($operable_list[$operation])) {
						$sn_not_list[] = $id_order;
						continue;
					}

					$order_id = $order['order_id'];
					$cancel_note = trim($_REQUEST['cancel_note']);
					update_order($order_id, array('order_status' => OS_CANCELED, 'to_buyer' => $cancel_note));
					order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, $action_note, $_SESSION['seller_name']);
					if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE) {
						change_order_goods_storage($order_id, false, SDT_PLACE, 3, $_SESSION['seller_id'], $store_id);
					}

					if ($_CFG['send_cancel_email'] == '1') {
						$tpl = get_mail_template('order_cancel');
						$smarty->assign('order', $order);
						$smarty->assign('shop_name', $_CFG['shop_name']);
						$smarty->assign('send_date', local_date($_CFG['date_format']));
						$smarty->assign('sent_date', local_date($_CFG['date_format']));
						$content = $smarty->fetch('str:' . $tpl['template_content']);
						send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
					}

					return_user_surplus_integral_bonus($order);
					$sn_list[] = $order['order_sn'];
				}
				else {
					$sn_not_list[] = $id_order;
				}
			}

			$sn_str = $_LANG['cancel_order'];
		}
		else if ('remove' == $operation) {
			foreach ($order_id_list as $id_order) {
				$order = order_info('', $id_order);
				$operable_list = operable_list($order);

				if (!isset($operable_list['remove'])) {
					$sn_not_list[] = $id_order;
					continue;
				}

				$return_order = return_order_info(0, '', $order['order_id']);

				if ($return_order) {
					sys_msg(sprintf($_LANG['order_remove_failure'], $order['order_sn']), 0, array(
	array('href' => 'order.php?act=list&' . list_link_postfix(), 'text' => $_LANG['return_list'])
	));
					exit();
				}

				$db->query('DELETE FROM ' . $ecs->table('order_info') . (' WHERE order_id = \'' . $order['order_id'] . '\''));
				$db->query('DELETE FROM ' . $ecs->table('order_goods') . (' WHERE order_id = \'' . $order['order_id'] . '\''));
				$db->query('DELETE FROM ' . $ecs->table('order_action') . (' WHERE order_id = \'' . $order['order_id'] . '\''));
				$db->query('DELETE FROM ' . $ecs->table('store_order') . (' WHERE order_id = \'' . $order['order_id'] . '\''));
				$action_array = array('delivery', 'back');
				del_delivery($order['order_id'], $action_array);
				admin_log($order['order_sn'], 'remove', 'order');
				$sn_list[] = $order['order_sn'];
			}

			$sn_str = $_LANG['remove_order'];
			$smarty->assign('ur_here', $_LANG['order_operate'] . $_LANG['remove']);
		}
		else {
			exit('invalid params');
		}

		if (empty($sn_not_list)) {
			$sn_list = empty($sn_list) ? '' : $_LANG['updated_order'] . join($sn_list, ',');
			$msg = $sn_list;
			$links[] = array('text' => $_LANG['return_list'], 'href' => 'order.php?act=list&' . list_link_postfix());
			sys_msg($msg, 0, $links);
		}
		else {
			$order_list_no_fail = array();
			$sql = 'SELECT * FROM ' . $ecs->table('order_info') . ' WHERE order_sn ' . db_create_in($sn_not_list);
			$res = $db->query($sql);

			while ($row = $db->fetchRow($res)) {
				$order_list_no_fail[$row['order_id']]['order_id'] = $row['order_id'];
				$order_list_no_fail[$row['order_id']]['order_sn'] = $row['order_sn'];
				$order_list_no_fail[$row['order_id']]['order_status'] = $row['order_status'];
				$order_list_no_fail[$row['order_id']]['shipping_status'] = $row['shipping_status'];
				$order_list_no_fail[$row['order_id']]['pay_status'] = $row['pay_status'];
				$order_list_fail = '';

				foreach (operable_list($row) as $key => $value) {
					if ($key != $operation) {
						$order_list_fail .= $_LANG['op_' . $key] . ',';
					}
				}

				$order_list_no_fail[$row['order_id']]['operable'] = $order_list_fail;
			}

			$smarty->assign('order_info', $sn_str);
			$smarty->assign('action_link', array('href' => 'order.php?act=list', 'text' => $_LANG['02_order_list'], 'class' => 'icon-reply'));
			$smarty->assign('order_list', $order_list_no_fail);
			assign_query_info();
			$smarty->display('order_operate_info.dwt');
		}
	}
	else if ($_REQUEST['act'] == 'operate_post') {
		admin_priv('order_os_edit');
		$order_id = intval(trim($_REQUEST['order_id']));
		$rec_id = empty($_REQUEST['rec_id']) ? 0 : $_REQUEST['rec_id'];
		$ret_id = empty($_REQUEST['ret_id']) ? 0 : $_REQUEST['ret_id'];
		$return = '';

		if ($ret_id) {
			$return = 1;
		}

		$operation = $_REQUEST['operation'];
		$order = order_info($order_id);
		$store_order_id = get_store_id($order_id);
		$store_id = 0 < $store_order_id ? $store_order_id : 0;
		$operable_list = operable_list($order);

		if (!isset($operable_list[$operation])) {
			exit('Hacking attempt');
		}

		$action_note = $_REQUEST['action_note'];
		$msg = '';

		if ('confirm' == $operation) {
			update_order($order_id, array('order_status' => OS_CONFIRMED, 'confirm_time' => gmtime()));
			update_order_amount($order_id);
			order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_UNPAYED, $action_note, $_SESSION['seller_name']);
			if ($order['order_status'] != OS_UNCONFIRMED && $_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE) {
				change_order_goods_storage($order_id, true, SDT_PLACE, 4, $_SESSION['seller_id'], $store_id);
			}

			$cfg = $_CFG['send_confirm_email'];

			if ($cfg == '1') {
				$tpl = get_mail_template('order_confirm');
				$smarty->assign('order', $order);
				$smarty->assign('shop_name', $_CFG['shop_name']);
				$smarty->assign('send_date', local_date($_CFG['date_format']));
				$smarty->assign('sent_date', local_date($_CFG['date_format']));
				$content = $smarty->fetch('str:' . $tpl['template_content']);

				if (!send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
					$msg = $_LANG['send_mail_fail'];
				}
			}
		}
		else if ('pay' == $operation) {
			admin_priv('order_ps_edit');

			if ($order['order_status'] != OS_CONFIRMED) {
				$arr['order_status'] = OS_CONFIRMED;
				$arr['confirm_time'] = gmtime();

				if ($_CFG['sales_volume_time'] == SALES_PAY) {
					$arr['is_update_sale'] = 1;
				}
			}

			$arr['pay_time'] = gmtime();
			if ($order['extension_code'] == 'presale' && $order['pay_status'] == 0) {
				$arr['pay_status'] = PS_PAYED_PART;
				$arr['money_paid'] = $order['money_paid'] + $order['order_amount'];
				$arr['order_amount'] = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['pay_fee'] + $order['tax'] + $order['pack_fee'] + $order['card_fee'] - $order['surplus'] - $order['money_paid'] - $order['integral_money'] - $order['bonus'] - $order['order_amount'] - $order['discount'];
			}
			else {
				$arr['pay_status'] = PS_PAYED;
				$arr['money_paid'] = $order['money_paid'] + $order['order_amount'];
				$arr['order_amount'] = 0;
			}

			$payment = payment_info($order['pay_id']);

			if ($payment['is_cod']) {
				$arr['shipping_status'] = SS_RECEIVED;
				$order['shipping_status'] = SS_RECEIVED;
			}

			update_order($order_id, $arr);
			create_snapshot($order_id);
			$sql = 'SELECT store_id FROM ' . $GLOBALS['ecs']->table('store_order') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
			$store_id = $GLOBALS['db']->getOne($sql);
			if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PAID) {
				change_order_goods_storage($order_id, true, SDT_PAID, $_CFG['stock_dec_time'], 0, $store_id);
			}

			get_goods_sale($order_id);
			$sql = 'SELECT store_id,pick_code,order_id FROM ' . $GLOBALS['ecs']->table('store_order') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
			$stores_order = $GLOBALS['db']->getRow($sql);
			$user_mobile_phone = '';
			$sql = 'SELECT mobile_phone,user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $order['user_id'] . '\' LIMIT 1';
			$orderUsers = $GLOBALS['db']->getRow($sql);

			if (0 < $stores_order['store_id']) {
				if ($order['mobile']) {
					$user_mobile_phone = $order['mobile'];
				}
				else {
					$user_mobile_phone = $orderUsers['mobile_phone'];
				}
			}

			if ($user_mobile_phone != '') {
				$store_smsParams = '';
				$sql = 'SELECT id, country, province, city, district, stores_address, stores_name, stores_tel FROM ' . $GLOBALS['ecs']->table('offline_store') . ' WHERE id = \'' . $stores_order['store_id'] . '\' LIMIT 1';
				$stores_info = $GLOBALS['db']->getRow($sql);
				$store_address = get_area_region_info($stores_info) . $stores_info['stores_address'];
				$user_name = !empty($orderUsers['user_name']) ? $orderUsers['user_name'] : '';
				$store_smsParams = array('user_name' => $user_name, 'username' => $user_name, 'order_sn' => $order['order_sn'], 'ordersn' => $order['order_sn'], 'code' => $stores_order['pick_code'], 'store_address' => $store_address, 'storeaddress' => $store_address, 'mobile_phone' => $user_mobile_phone, 'mobilephone' => $user_mobile_phone);

				if ($GLOBALS['_CFG']['sms_type'] == 0) {
					if (0 < $stores_order['store_id'] && !empty($store_smsParams)) {
						huyi_sms($store_smsParams, 'store_order_code');
					}
				}
				else if (1 <= $GLOBALS['_CFG']['sms_type']) {
					if (0 < $stores_order['store_id'] && !empty($store_smsParams)) {
						$store_result = sms_ali($store_smsParams, 'store_order_code');
						$GLOBALS['ecs']->ali_yu($store_result);
					}
				}
			}

			$confirm_take_time = gmtime();
			if (($arr['order_status'] == OS_CONFIRMED || $arr['order_status'] == OS_SPLITED) && $arr['pay_status'] == PS_PAYED && $arr['shipping_status'] == SS_RECEIVED) {
				$sql = 'SELECT order_id, user_id, order_sn , order_status, shipping_status, pay_status, ' . 'order_amount, goods_amount, tax, shipping_fee, insure_fee, pay_fee, pack_fee, card_fee, ' . 'bonus, integral_money, coupons, discount, money_paid, surplus, confirm_take_time ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\'');
				$bill_order = $GLOBALS['db']->GetRow($sql);
				$seller_id = $GLOBALS['db']->getOne('SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\''), true);
				$value_card = $GLOBALS['db']->getOne('SELECT use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . (' WHERE order_id = \'' . $order_id . '\''), true);
				$return_amount = get_order_return_amount($order_id);
				$other = array('user_id' => $bill_order['user_id'], 'seller_id' => $seller_id, 'order_id' => $bill_order['order_id'], 'order_sn' => $bill_order['order_sn'], 'order_status' => $bill_order['order_status'], 'shipping_status' => SS_RECEIVED, 'pay_status' => $bill_order['pay_status'], 'order_amount' => $bill_order['order_amount'], 'return_amount' => $return_amount, 'goods_amount' => $bill_order['goods_amount'], 'tax' => $bill_order['tax'], 'shipping_fee' => $bill_order['shipping_fee'], 'insure_fee' => $bill_order['insure_fee'], 'pay_fee' => $bill_order['pay_fee'], 'pack_fee' => $bill_order['pack_fee'], 'card_fee' => $bill_order['card_fee'], 'bonus' => $bill_order['bonus'], 'integral_money' => $bill_order['integral_money'], 'coupons' => $bill_order['coupons'], 'discount' => $bill_order['discount'], 'value_card' => $value_card, 'money_paid' => $bill_order['money_paid'], 'surplus' => $bill_order['surplus'], 'confirm_take_time' => $confirm_take_time);

				if ($seller_id) {
					get_order_bill_log($other);
				}
			}

			if ($order['extension_code'] == 'presale' && $order['pay_status'] == 0) {
				order_action($order['order_sn'], OS_CONFIRMED, $order['shipping_status'], PS_PAYED_PART, $action_note, $_SESSION['seller_name']);
				update_pay_log($order_id);
			}
			else {
				order_action($order['order_sn'], OS_CONFIRMED, $order['shipping_status'], PS_PAYED, $action_note, $_SESSION['seller_name'], 0, $confirm_take_time);
			}
		}
		else if ('prepare' == $operation) {
			if ($order['order_status'] != OS_CONFIRMED) {
				$arr['order_status'] = OS_CONFIRMED;
				$arr['confirm_time'] = gmtime();
			}

			$arr['shipping_status'] = SS_PREPARING;
			update_order($order_id, $arr);
			order_action($order['order_sn'], OS_CONFIRMED, SS_PREPARING, $order['pay_status'], $action_note, $_SESSION['seller_name']);
			clear_cache_files();
		}
		else if ('split' == $operation) {
			admin_priv('order_ss_edit');
			define('GMTIME_UTC', gmtime());
			$delivery_info = get_delivery_info($order_id);

			if (true) {
				$suppliers_id = isset($_REQUEST['suppliers_id']) ? intval(trim($_REQUEST['suppliers_id'])) : '0';
				array_walk($_REQUEST['delivery'], 'trim_array_walk');
				$delivery = $_REQUEST['delivery'];
				array_walk($_REQUEST['send_number'], 'trim_array_walk');
				array_walk($_REQUEST['send_number'], 'intval_array_walk');
				$send_number = $_REQUEST['send_number'];
				$action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';
				$delivery['user_id'] = intval($delivery['user_id']);
				$delivery['country'] = intval($delivery['country']);
				$delivery['province'] = intval($delivery['province']);
				$delivery['city'] = intval($delivery['city']);
				$delivery['district'] = intval($delivery['district']);
				$delivery['agency_id'] = intval($delivery['agency_id']);
				$delivery['insure_fee'] = floatval($delivery['insure_fee']);
				$delivery['shipping_fee'] = floatval($delivery['shipping_fee']);

				if ($order['order_status'] == OS_SPLITED) {
					$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
					sys_msg(sprintf($_LANG['order_splited_sms'], $order['order_sn'], $_LANG['os'][OS_SPLITED], $_LANG['ss'][SS_SHIPPED_ING], $GLOBALS['_CFG']['shop_name']), 1, $links);
				}

				$_goods = get_order_goods(array('order_id' => $order_id, 'order_sn' => $delivery['order_sn']));
				$goods_list = $_goods['goods_list'];
				if (!empty($send_number) && !empty($goods_list)) {
					$goods_no_package = array();

					foreach ($goods_list as $key => $value) {
						if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list'])) {
							$_key = empty($value['product_id']) ? $value['goods_id'] : $value['goods_id'] . '_' . $value['product_id'];

							if (empty($goods_no_package[$_key])) {
								$goods_no_package[$_key] = $send_number[$value['rec_id']];
							}
							else {
								$goods_no_package[$_key] += $send_number[$value['rec_id']];
							}

							if ($send_number[$value['rec_id']] <= 0) {
								unset($send_number[$value['rec_id']]);
								unset($goods_list[$key]);
								continue;
							}
						}
						else {
							$goods_list[$key]['package_goods_list'] = package_goods($value['package_goods_list'], $value['goods_number'], $value['order_id'], $value['extension_code'], $value['goods_id']);

							foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
								$_key = empty($pg_value['product_id']) ? $pg_value['goods_id'] : $pg_value['goods_id'] . '_' . $pg_value['product_id'];

								if (empty($goods_no_package[$_key])) {
									$goods_no_package[$_key] = $send_number[$value['rec_id']][$pg_value['g_p']];
								}
								else {
									$goods_no_package[$_key] += $send_number[$value['rec_id']][$pg_value['g_p']];
								}

								if ($send_number[$value['rec_id']][$pg_value['g_p']] <= 0) {
									unset($send_number[$value['rec_id']][$pg_value['g_p']]);
									unset($goods_list[$key]['package_goods_list'][$pg_key]);
								}
							}

							if (count($goods_list[$key]['package_goods_list']) <= 0) {
								unset($send_number[$value['rec_id']]);
								unset($goods_list[$key]);
								continue;
							}
						}

						if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list'])) {
							$sended = order_delivery_num($order_id, $value['goods_id'], $value['product_id']);

							if ($value['goods_number'] - $sended - $send_number[$value['rec_id']] < 0) {
								$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
								sys_msg($_LANG['act_ship_num'], 1, $links);
							}
						}
						else {
							foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value) {
								if ($pg_value['order_send_number'] - $pg_value['sended'] - $send_number[$value['rec_id']][$pg_value['g_p']] < 0) {
									$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
									sys_msg($_LANG['act_ship_num'], 1, $links);
								}
							}
						}
					}
				}

				if (empty($send_number) || empty($goods_list)) {
					$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
					sys_msg($_LANG['act_false'], 1, $links);
				}

				$virtual_goods = array();
				$package_virtual_goods = array();

				foreach ($goods_list as $key => $value) {
					if ($value['extension_code'] == 'package_buy') {
						foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
							if ($pg_value['goods_number'] < $goods_no_package[$pg_value['g_p']] && ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP || $_CFG['use_storage'] == '0' && $pg_value['is_real'] == 0)) {
								$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
								sys_msg(sprintf($_LANG['act_good_vacancy'], $pg_value['goods_name']), 1, $links);
							}

							if ($pg_value['is_real'] == 0) {
								$package_virtual_goods[] = array('goods_id' => $pg_value['goods_id'], 'goods_name' => $pg_value['goods_name'], 'num' => $send_number[$value['rec_id']][$pg_value['g_p']]);
							}
						}
					}
					else {
						if ($value['extension_code'] == 'virtual_card' || $value['is_real'] == 0) {
							$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('virtual_card') . ' WHERE goods_id = \'' . $value['goods_id'] . '\' AND is_saled = 0 ';
							$num = $GLOBALS['db']->GetOne($sql);
							if ($num < $goods_no_package[$value['goods_id']] && !($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)) {
								$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
								sys_msg(sprintf($GLOBALS['_LANG']['virtual_card_oos'] . '【' . $value['goods_name'] . '】'), 1, $links);
							}

							if ($value['extension_code'] == 'virtual_card') {
								$virtual_goods[$value['extension_code']][] = array('goods_id' => $value['goods_id'], 'goods_name' => $value['goods_name'], 'num' => $send_number[$value['rec_id']]);
							}
						}
						else {
							$_key = empty($value['product_id']) ? $value['goods_id'] : $value['goods_id'] . '_' . $value['product_id'];
							$num = $value['storage'];
							if ($num < $goods_no_package[$_key] && $_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
								$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
								sys_msg(sprintf($_LANG['act_good_vacancy'], $value['goods_name']), 1, $links);
							}
						}
					}
				}

				$delivery['delivery_sn'] = get_delivery_sn();
				$delivery_sn = $delivery['delivery_sn'];
				$delivery['action_user'] = $_SESSION['seller_name'];
				$delivery['update_time'] = GMTIME_UTC;
				$delivery_time = $delivery['update_time'];
				$sql = 'select add_time from ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_sn = \'' . $delivery['order_sn'] . '\'';
				$delivery['add_time'] = $GLOBALS['db']->GetOne($sql);
				$delivery['suppliers_id'] = $suppliers_id;
				$delivery['status'] = 2;
				$delivery['order_id'] = $order_id;
				$filter_fileds = array('order_sn', 'add_time', 'user_id', 'how_oos', 'shipping_id', 'shipping_fee', 'consignee', 'address', 'country', 'province', 'city', 'district', 'sign_building', 'email', 'zipcode', 'tel', 'mobile', 'best_time', 'postscript', 'insure_fee', 'agency_id', 'delivery_sn', 'action_user', 'update_time', 'suppliers_id', 'status', 'order_id', 'shipping_name');
				$_delivery = array();

				foreach ($filter_fileds as $value) {
					$_delivery[$value] = $delivery[$value];
				}

				$query = $db->autoExecute($ecs->table('delivery_order'), $_delivery, 'INSERT', '', 'SILENT');
				$delivery_id = $db->insert_id();

				if ($delivery_id) {
					$delivery_goods = array();

					if (!empty($goods_list)) {
						$split_action_note = '';

						foreach ($goods_list as $value) {
							if (empty($value['extension_code']) || $value['extension_code'] == 'virtual_card') {
								$delivery_goods = array('delivery_id' => $delivery_id, 'goods_id' => $value['goods_id'], 'product_id' => $value['product_id'], 'product_sn' => $value['product_sn'], 'goods_id' => $value['goods_id'], 'goods_name' => addslashes($value['goods_name']), 'brand_name' => addslashes($value['brand_name']), 'goods_sn' => $value['goods_sn'], 'send_number' => $send_number[$value['rec_id']], 'parent_id' => 0, 'is_real' => $value['is_real'], 'goods_attr' => addslashes($value['goods_attr']));

								if (!empty($value['product_id'])) {
									$delivery_goods['product_id'] = $value['product_id'];
								}

								$query = $db->autoExecute($ecs->table('delivery_goods'), $delivery_goods, 'INSERT', '', 'SILENT');
								$split_action_note .= sprintf($_LANG['split_action_note'], $value['goods_sn'], $send_number[$value['rec_id']]) . '<br/>';
							}
							else if ($value['extension_code'] == 'package_buy') {
								foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
									$delivery_pg_goods = array('delivery_id' => $delivery_id, 'goods_id' => $pg_value['goods_id'], 'product_id' => $pg_value['product_id'], 'product_sn' => $pg_value['product_sn'], 'goods_name' => $pg_value['goods_name'], 'brand_name' => '', 'goods_sn' => $pg_value['goods_sn'], 'send_number' => $send_number[$value['rec_id']][$pg_value['g_p']], 'parent_id' => $value['goods_id'], 'extension_code' => $value['extension_code'], 'is_real' => $pg_value['is_real']);
									$query = $db->autoExecute($ecs->table('delivery_goods'), $delivery_pg_goods, 'INSERT', '', 'SILENT');
								}

								$split_action_note .= sprintf($_LANG['split_action_note'], $_LANG['14_package_list'], 1) . '<br/>';
							}
						}
					}
				}
				else {
					$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
					sys_msg($_LANG['act_false'], 1, $links);
				}

				$_note = sprintf($_LANG['order_ship_delivery'], $delivery['delivery_sn']) . '<br/>';
				unset($filter_fileds);
				unset($delivery);
				unset($_delivery);
				unset($order_finish);

				if (true) {
					$_sended = &$send_number;

					foreach ($_goods['goods_list'] as $key => $value) {
						if ($value['extension_code'] != 'package_buy') {
							unset($_goods['goods_list'][$key]);
						}
					}

					foreach ($goods_list as $key => $value) {
						if ($value['extension_code'] == 'package_buy') {
							unset($goods_list[$key]);
						}
					}

					$_goods['goods_list'] = $goods_list + $_goods['goods_list'];
					unset($goods_list);
					$_virtual_goods = isset($virtual_goods['virtual_card']) ? $virtual_goods['virtual_card'] : '';
					update_order_virtual_goods($order_id, $_sended, $_virtual_goods);
					update_order_goods($order_id, $_sended, $_goods['goods_list']);
					$order_finish = get_order_finish($order_id);
					$shipping_status = SS_SHIPPED_ING;
					if ($order['order_status'] != OS_CONFIRMED && $order['order_status'] != OS_SPLITED && $order['order_status'] != OS_SPLITING_PART) {
						$arr['order_status'] = OS_CONFIRMED;
						$arr['confirm_time'] = GMTIME_UTC;
					}

					$arr['order_status'] = $order_finish ? OS_SPLITED : OS_SPLITING_PART;
					$arr['shipping_status'] = $shipping_status;
					update_order($order_id, $arr);
				}

				$action_note = $split_action_note . $action_note;
				order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $_note . $action_note, $_SESSION['seller_name']);
				clear_cache_files();
			}
		}
		else if ('unship' == $operation) {
			admin_priv('order_ss_edit');
			update_order($order_id, array('shipping_status' => SS_UNSHIPPED, 'shipping_time' => 0, 'invoice_no' => '', 'order_status' => OS_CONFIRMED));
			order_action($order['order_sn'], $order['order_status'], SS_UNSHIPPED, $order['pay_status'], $action_note, $_SESSION['seller_name']);

			if (0 < $order['user_id']) {
				$user = user_info($order['user_id']);
				$integral = integral_to_give($order);
				log_account_change($order['user_id'], 0, 0, -1 * intval($integral['rank_points']), -1 * intval($integral['custom_points']), sprintf($_LANG['return_order_gift_integral'], $order['order_sn']));
				return_order_bonus($order_id);
			}

			if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
				change_order_goods_storage($order['order_id'], false, SDT_SHIP, 5, $_SESSION['seller_id'], $store_id);
			}

			del_order_delivery($order_id);
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . ("\r\n                SET send_number = 0\r\n                WHERE order_id = '" . $order_id . '\'');
			$GLOBALS['db']->query($sql, 'SILENT');
			clear_cache_files();
		}
		else if ('receive' == $operation) {
			$confirm_take_time = gmtime();
			$arr = array('shipping_status' => SS_RECEIVED, 'confirm_take_time' => $confirm_take_time);
			$payment = payment_info($order['pay_id']);

			if ($payment['is_cod']) {
				$arr['pay_status'] = PS_PAYED;
				$order['pay_status'] = PS_PAYED;
			}

			update_order($order_id, $arr);
			$sql = 'SELECT goods_id, goods_number FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id =' . $order_id;
			$res = $GLOBALS['db']->getAll($sql);

			foreach ($res as $val) {
				$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE goods_id=' . $val['goods_id'] . ' GROUP BY user_id';
				$user_number = COUNT($GLOBALS['db']->getAll($sql));
				$num = array('goods_number' => $val['goods_number'], 'goods_id' => $val['goods_id'], 'user_number' => $user_number);
				update_manual($val['goods_id'], $num);
			}

			order_action($order['order_sn'], $order['order_status'], SS_RECEIVED, $order['pay_status'], $action_note, $_SESSION['seller_name'], 0, $confirm_take_time);
			$bill = array('order_id' => $order['order_id']);
			$bill_order = get_bill_order($bill);

			if (!$bill_order) {
				$seller_id = $GLOBALS['db']->getOne('SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id = \'' . $order['order_id'] . '\'', true);
				$value_card = $GLOBALS['db']->getOne('SELECT use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . ' WHERE order_id = \'' . $order['order_id'] . '\'', true);
				$return_amount = get_order_return_amount($order['order_id']);
				$other = array('user_id' => $order['user_id'], 'seller_id' => $seller_id, 'order_id' => $order['order_id'], 'order_sn' => $order['order_sn'], 'order_status' => $order['order_status'], 'shipping_status' => SS_RECEIVED, 'pay_status' => $order['pay_status'], 'order_amount' => $order['order_amount'], 'return_amount' => $return_amount, 'goods_amount' => $order['goods_amount'], 'tax' => $order['tax'], 'shipping_fee' => $order['shipping_fee'], 'insure_fee' => $order['insure_fee'], 'pay_fee' => $order['pay_fee'], 'pack_fee' => $order['pack_fee'], 'card_fee' => $order['card_fee'], 'bonus' => $order['bonus'], 'integral_money' => $order['integral_money'], 'coupons' => $order['coupons'], 'discount' => $order['discount'], 'value_card' => $value_card, 'money_paid' => $order['money_paid'], 'surplus' => $order['surplus'], 'confirm_take_time' => $confirm_take_time);

				if ($seller_id) {
					get_order_bill_log($other);
				}
			}
		}
		else if ('agree_apply' == $operation) {
			$arr = array('agree_apply' => 1);
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_return'), $arr, 'UPDATE', 'rec_id = \'' . $rec_id . '\'');
			return_action($ret_id, RF_AGREE_APPLY, '', $action_note);
		}
		else if ('receive_goods' == $operation) {
			$arr = array('return_status' => 1);
			$res = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_return'), $arr, 'UPDATE', 'rec_id = \'' . $rec_id . '\'');

			if ($res) {
				$sql = 'SELECT goods_id,return_number FROM ' . $GLOBALS['ecs']->table('return_goods') . ' WHERE rec_id = ' . $rec_id;
				$res = $GLOBALS['db']->getRow($sql);
				$return_num = array('return_number' => $res['return_number'], 'goods_id' => $res['goods_id']);
				update_return_num($res['goods_id'], $return_num);
			}

			$arr['pay_status'] = PS_PAYED;
			$order['pay_status'] = PS_PAYED;
			return_action($ret_id, RF_RECEIVE, '', $action_note);
		}
		else if ('swapped_out_single' == $operation) {
			$arr = array('return_status' => 2);
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_return'), $arr, 'UPDATE', 'rec_id = \'' . $rec_id . '\'');
			return_action($ret_id, RF_SWAPPED_OUT_SINGLE, '', $action_note);
		}
		else if ('swapped_out' == $operation) {
			$arr = array('return_status' => 3);
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_return'), $arr, 'UPDATE', 'rec_id = \'' . $rec_id . '\'');
			return_action($ret_id, RF_SWAPPED_OUT, '', $action_note);
		}
		else if ('refuse_apply' == $operation) {
			$arr = array('return_status' => 6);
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_return'), $arr, 'UPDATE', 'rec_id = \'' . $rec_id . '\'');
			return_action($ret_id, REFUSE_APPLY, '', $action_note);
		}
		else if ('complete' == $operation) {
			$arr = array('return_status' => 4);
			$sql = 'SELECT return_type FROM ' . $ecs->table('order_return') . (' WHERE rec_id = \'' . $rec_id . '\'');
			$return_type = $db->getOne($sql);

			if ($return_type == 0) {
				$return_note = FF_MAINTENANCE;
			}
			else if ($return_type == 1) {
				$return_note = FF_REFOUND;
			}
			else if ($return_type == 2) {
				$return_note = FF_EXCHANGE;
			}

			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_return'), $arr, 'UPDATE', 'rec_id = \'' . $rec_id . '\'');
			return_action($ret_id, RF_COMPLETE, $return_note, $action_note);
		}
		else if ('cancel' == $operation) {
			$cancel_note = isset($_REQUEST['cancel_note']) ? trim($_REQUEST['cancel_note']) : '';
			$arr = array('order_status' => OS_CANCELED, 'to_buyer' => $cancel_note, 'pay_status' => PS_UNPAYED, 'pay_time' => 0, 'money_paid' => 0, 'order_amount' => $order['money_paid']);
			update_order($order_id, $arr);

			if (0 < $order['money_paid']) {
				$refund_type = isset($_REQUEST['refund']) && !empty($_REQUEST['refund']) ? tirm($_REQUEST['refund']) : '';
				$refund_note = isset($_REQUEST['refund']) && !empty($_REQUEST['refund_note']) ? tirm($_REQUEST['refund_note']) : '';

				if ($refund_note) {
					$refund_note = '【' . $_LANG['setorder_cancel'] . '】【' . $order['order_sn'] . '】' . $refund_note;
				}

				order_refund($order, $refund_type, $refund_note);
			}

			order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, $action_note, $_SESSION['seller_name']);
			if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE) {
				change_order_goods_storage($order_id, false, SDT_PLACE, 3, $_SESSION['seller_id'], $store_id);
			}

			return_user_surplus_integral_bonus($order);
			$cfg = $_CFG['send_cancel_email'];

			if ($cfg == '1') {
				$tpl = get_mail_template('order_cancel');
				$smarty->assign('order', $order);
				$smarty->assign('shop_name', $_CFG['shop_name']);
				$smarty->assign('send_date', local_date($_CFG['date_format']));
				$smarty->assign('sent_date', local_date($_CFG['date_format']));
				$content = $smarty->fetch('str:' . $tpl['template_content']);

				if (!send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
					$msg = $_LANG['send_mail_fail'];
				}
			}
		}
		else if ('invalid' == $operation) {
			update_order($order_id, array('order_status' => OS_INVALID));
			order_action($order['order_sn'], OS_INVALID, $order['shipping_status'], PS_UNPAYED, $action_note, $_SESSION['seller_name']);
			if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE) {
				change_order_goods_storage($order_id, false, SDT_PLACE, 2, $_SESSION['seller_id'], $store_id);
			}

			$cfg = $_CFG['send_invalid_email'];

			if ($cfg == '1') {
				$tpl = get_mail_template('order_invalid');
				$smarty->assign('order', $order);
				$smarty->assign('shop_name', $_CFG['shop_name']);
				$smarty->assign('send_date', local_date($_CFG['date_format']));
				$smarty->assign('sent_date', local_date($_CFG['date_format']));
				$content = $smarty->fetch('str:' . $tpl['template_content']);

				if (!send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
					$msg = $_LANG['send_mail_fail'];
				}
			}

			return_user_surplus_integral_bonus($order);
		}
		else if ('refound' == $operation) {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			define('GMTIME_UTC', gmtime());
			$is_whole = 0;
			$is_diff = get_order_return_rec($order_id);

			if ($is_diff) {
				$return_count = return_order_info_byId($order_id, 0);

				if ($return_count == 1) {
					$bonus = $order['bonus'];
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_bonus') . ' SET used_time = \'\' , order_id = \'\' WHERE order_id = ' . $order_id;
					$GLOBALS['db']->query($sql);
					unuse_coupons($order_id);
					$is_whole = 1;
				}
			}

			$_REQUEST['refund'] = isset($_REQUEST['refund']) ? $_REQUEST['refund'] : '';
			$_REQUEST['refund_amount'] = isset($_REQUEST['refund_amount']) ? $_REQUEST['refund_amount'] : $_REQUEST['action_note'] = isset($_REQUEST['action_note']) ? $_REQUEST['action_note'] : '';
			$_REQUEST['refound_pay_points'] = isset($_REQUEST['refound_pay_points']) ? $_REQUEST['refound_pay_points'] : 0;
			$return_amount = isset($_REQUEST['refound_amount']) && !empty($_REQUEST['refound_amount']) ? floatval($_REQUEST['refound_amount']) : 0;
			$is_shipping = isset($_REQUEST['is_shipping']) && !empty($_REQUEST['is_shipping']) ? intval($_REQUEST['is_shipping']) : 0;
			$shippingFee = !empty($is_shipping) ? floatval($_REQUEST['shipping']) : 0;
			$refound_vcard = isset($_REQUEST['refound_vcard']) && !empty($_REQUEST['refound_vcard']) ? floatval($_REQUEST['refound_vcard']) : 0;
			$vc_id = isset($_REQUEST['vc_id']) && !empty($_REQUEST['vc_id']) ? intval($_REQUEST['vc_id']) : 0;
			$return_goods = get_return_order_goods1($rec_id);
			$return_info = return_order_info($ret_id);

			if ($order['pay_status'] != PS_UNPAYED) {
				$order_goods = get_order_goods($order);
				$refund_type = intval($_REQUEST['refund']);
				$refound_fee = order_refound_fee($order_id, $ret_id);
				$paid_amount = $order['money_paid'] + $order['surplus'] - $refound_fee;

				if ($paid_amount < $return_amount) {
					$return_amount = $paid_amount - $order['shipping_fee'];
				}

				$is_refound_shippfee = order_refound_shipping_fee($order_id, $ret_id);
				$is_refound_shippfee_amount = $is_refound_shippfee + $shippingFee;

				if ($order['shipping_fee'] < $is_refound_shippfee_amount) {
					$shippingFee = $order['shipping_fee'] - $is_refound_shippfee;
				}

				$refund_amount = $return_amount + $shippingFee;
				$get_order_arr = get_order_arr($return_info['return_number'], $return_info['rec_id'], $order_goods['goods_list'], $order);
				$refund_note = addslashes(trim($_REQUEST['refund_note']));

				if (!empty($_REQUEST['action_note'])) {
					$order['should_return'] = $return_info['should_return'];
					$is_ok = order_refound($order, $refund_type, $refund_note, $refund_amount, $operation);
					if ($is_ok == 2 && $refund_type == 1) {
						$links[] = array('href' => 'order.php?act=return_info&ret_id=' . $ret_id . '&rec_id=' . $return_info['rec_id'], 'text' => '返回退货单操作');
						sys_msg('退款失败，您的账户资金负金额大于信用额度，请您进行账户资金充值或者联系客服', 1, $links);
						exit();
					}

					$return_status = array('refound_status' => 1, 'agree_apply' => 1, 'actual_return' => $refund_amount, 'return_shipping_fee' => $shippingFee, 'refund_type' => $refund_type, 'return_time' => gmtime());
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_return'), $return_status, 'UPDATE', 'ret_id = \'' . $ret_id . '\'');
					update_order($order_id, $get_order_arr);
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . ' SET sales_volume = sales_volume - \'' . $return_info['return_number'] . '\' WHERE goods_id = \'' . $return_goods['goods_id'] . '\'';
					$GLOBALS['db']->query($sql);
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('seller_bill_order') . (' SET return_amount = return_amount + \'' . $return_amount . '\', ') . ('return_shippingfee = \'' . $shippingFee . '\' WHERE order_id = \'' . $order_id . '\'');
					$GLOBALS['db']->query($sql);
				}
			}

			if (0 < $_REQUEST['refound_pay_points']) {
				log_account_change($order['user_id'], 0, 0, 0, $_REQUEST['refound_pay_points'], ' 订单退款，退回订单 ' . $order['order_sn'] . ' 购买的积分');
			}

			return_integral_rank($ret_id, $order['user_id'], $order['order_sn'], $rec_id, $_REQUEST['refound_pay_points']);

			if ($is_whole == 1) {
				return_card_money($order_id, $ret_id, $return_info['return_sn']);
			}
			else {
				get_return_vcard($order_id, $vc_id, $refound_vcard, $return_info['return_sn'], $ret_id);
			}

			if ($_CFG['use_storage'] == '1') {
				if ($_CFG['stock_dec_time'] == SDT_SHIP) {
					change_order_goods_storage($order_id, false, SDT_SHIP, 6, $_SESSION['seller_id'], $store_id);
				}
				else if ($_CFG['stock_dec_time'] == SDT_PLACE) {
					change_order_goods_storage($order_id, false, SDT_PLACE, 6, $_SESSION['seller_id'], $store_id);
				}
				else if ($_CFG['stock_dec_time'] == SDT_PAID) {
					change_order_goods_storage($order_id, false, SDT_PAID, 6, $_SESSION['seller_id'], $store_id);
				}
			}

			return_action($ret_id, '', FF_REFOUND, $action_note);
		}
		else if ('return' == $operation) {
			define('GMTIME_UTC', gmtime());
			$_REQUEST['refund'] = isset($_REQUEST['refund']) ? $_REQUEST['refund'] : '';
			$_REQUEST['refund_note'] = isset($_REQUEST['refund_note']) ? $_REQUEST['refund'] : '';
			$return_amount = isset($_REQUEST['refound_amount']) && !empty($_REQUEST['refound_amount']) ? floatval($_REQUEST['refound_amount']) : 0;
			$is_shipping = isset($_REQUEST['is_shipping']) && !empty($_REQUEST['is_shipping']) ? intval($_REQUEST['is_shipping']) : 0;
			$shipping_fee = !empty($is_shipping) ? floatval($_REQUEST['shipping']) : 0;
			$refound_vcard = isset($_REQUEST['refound_vcard']) && !empty($_REQUEST['refound_vcard']) ? floatval($_REQUEST['refound_vcard']) : 0;
			$vc_id = isset($_REQUEST['vc_id']) && !empty($_REQUEST['vc_id']) ? intval($_REQUEST['vc_id']) : 0;
			$order_return_amount = $return_amount + $shipping_fee;

			if ($order['pay_status'] != PS_UNPAYED) {
				$order['order_status'] = OS_RETURNED;
				$order['pay_status'] = PS_UNPAYED;
				$order['shipping_status'] = SS_UNSHIPPED;
				$refund_type = intval($_REQUEST['refund']);
				$refund_note = isset($_REQUEST['action_note']) ? addslashes(trim($_REQUEST['action_note'])) : '';
				$refund_note = '【' . $_LANG['refund'] . '】' . '【' . $order['order_sn'] . '】' . $refund_note;
				$is_ok = order_refund($order, $refund_type, $refund_note, $return_amount, $shipping_fee);
				if ($is_ok == 2 && $refund_type == 1) {
					$links[] = array('href' => 'order.php?act=info&order_id=' . $order_id, 'text' => '返回订单信息');
					sys_msg('退款失败，您的账户资金负金额大于信用额度，请您进行账户资金充值或者联系客服', 1, $links);
					exit();
				}

				$order['surplus'] = 0;
			}

			$arr = array('order_status' => OS_RETURNED, 'pay_status' => PS_UNPAYED, 'shipping_status' => SS_UNSHIPPED, 'money_paid' => 0, 'invoice_no' => '', 'return_amount' => $return_amount, 'order_amount' => $order_amount);
			update_order($order_id, $arr);
			order_action($order['order_sn'], OS_RETURNED, SS_UNSHIPPED, PS_UNPAYED, $action_note, $_SESSION['seller_name']);

			if (0 < $order['user_id']) {
				$user = user_info($order['user_id']);
				$sql = 'SELECT  goods_number, send_number FROM' . $GLOBALS['ecs']->table('order_goods') . "\r\n                WHERE order_id = '" . $order['order_id'] . '\'';
				$goods_num = $db->query($sql);
				$goods_num = $db->fetchRow($goods_num);

				if ($goods_num['goods_number'] == $goods_num['send_number']) {
					$integral = integral_to_give($order);
					log_account_change($order['user_id'], 0, 0, -1 * intval($integral['rank_points']), -1 * intval($integral['custom_points']), sprintf($_LANG['return_order_gift_integral'], $order['order_sn']));
				}

				return_order_bonus($order_id);
			}

			if ($_CFG['use_storage'] == '1') {
				if ($_CFG['stock_dec_time'] == SDT_SHIP) {
					change_order_goods_storage($order['order_id'], false, SDT_SHIP, 6, $_SESSION['seller_id'], $store_id);
				}
				else if ($_CFG['stock_dec_time'] == SDT_PLACE) {
					change_order_goods_storage($order['order_id'], false, SDT_PLACE, 6, $_SESSION['seller_id'], $store_id);
				}
				else if ($_CFG['stock_dec_time'] == SDT_PAID) {
					change_order_goods_storage($order_id, false, SDT_PAID, 6, $_SESSION['seller_id'], $store_id);
				}
			}

			return_card_money($order_id);
			return_user_surplus_integral_bonus($order);
			$delivery['action_user'] = $_SESSION['seller_name'];
			$delivery_list = array();
			$sql_delivery = "SELECT *\r\n                         FROM " . $ecs->table('delivery_order') . "\r\n                         WHERE status IN (0, 2)\r\n                         AND order_id = " . $order['order_id'];
			$delivery_list = $GLOBALS['db']->getAll($sql_delivery);

			if ($delivery_list) {
				foreach ($delivery_list as $list) {
					$sql_back = 'INSERT INTO ' . $ecs->table('back_order') . ' (delivery_sn, order_sn, order_id, add_time, shipping_id, user_id, action_user, consignee, address, Country, province, City, district, sign_building, Email,Zipcode, Tel, Mobile, best_time, postscript, how_oos, insure_fee, shipping_fee, update_time, suppliers_id, return_time, agency_id, invoice_no) VALUES ';
					$sql_back .= ' ( \'' . $list['delivery_sn'] . '\', \'' . $list['order_sn'] . "',\r\n                              '" . $list['order_id'] . '\', \'' . $list['add_time'] . "',\r\n                              '" . $list['shipping_id'] . '\', \'' . $list['user_id'] . "',\r\n                              '" . $delivery['action_user'] . '\', \'' . $list['consignee'] . "',\r\n                              '" . $list['address'] . '\', \'' . $list['country'] . '\', \'' . $list['province'] . "',\r\n                              '" . $list['city'] . '\', \'' . $list['district'] . '\', \'' . $list['sign_building'] . "',\r\n                              '" . $list['email'] . '\', \'' . $list['zipcode'] . '\', \'' . $list['tel'] . "',\r\n                              '" . $list['mobile'] . '\', \'' . $list['best_time'] . '\', \'' . $list['postscript'] . "',\r\n                              '" . $list['how_oos'] . '\', \'' . $list['insure_fee'] . "',\r\n                              '" . $list['shipping_fee'] . '\', \'' . $list['update_time'] . "',\r\n                              '" . $list['suppliers_id'] . '\', \'' . GMTIME_UTC . "',\r\n                              '" . $list['agency_id'] . '\', \'' . $list['invoice_no'] . "'\r\n                              )";
					$GLOBALS['db']->query($sql_back, 'SILENT');
					$back_id = $GLOBALS['db']->insert_id();
					$sql_back_goods = 'INSERT INTO ' . $ecs->table('back_goods') . (" (back_id, goods_id, product_id, product_sn, goods_name,goods_sn, is_real, send_number, goods_attr)\r\n                                   SELECT '" . $back_id . "', goods_id, product_id, product_sn, goods_name, goods_sn, is_real, send_number, goods_attr\r\n                                   FROM ") . $ecs->table('delivery_goods') . "\r\n                                   WHERE delivery_id = " . $list['delivery_id'];
					$res = $GLOBALS['db']->query($sql_back_goods, 'SILENT');

					if ($res) {
						$sql = 'SELECT goods_id,send_number FROM ' . $GLOBALS['ecs']->table('back_goods') . ' WHERE back_id = ' . $back_id;
						$res = $GLOBALS['db']->getRow($sql);
						$return_num = array('return_number' => $res['send_number'], 'goods_id' => $res['goods_id']);
						update_return_num($res['goods_id'], $return_num);
					}
				}
			}

			$sql_delivery = 'UPDATE ' . $ecs->table('delivery_order') . "\r\n                         SET status = 1\r\n                         WHERE status IN (0, 2)\r\n                         AND order_id = '" . $order['order_id'] . '\'';
			$GLOBALS['db']->query($sql_delivery, 'SILENT');
			$sql = 'SELECT goods_id, send_number FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id = \'' . $order['order_id'] . '\' AND extension_code <> \'virtual_card\' AND send_number >= 1';
			$goods_list = $GLOBALS['db']->getAll($sql);

			if ($goods_list) {
				foreach ($goods_list as $key => $row) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . ' SET sales_volume = sales_volume - \'' . $row['send_number'] . '\' WHERE goods_id = \'' . $row['goods_id'] . '\'';
					$GLOBALS['db']->query($sql);
				}
			}

			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . ("\r\n                SET send_number = 0\r\n                WHERE order_id = '" . $order_id . '\'');
			$GLOBALS['db']->query($sql, 'SILENT');
			clear_cache_files();
		}
		else if ('after_service' == $operation) {
			order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '[' . $_LANG['op_after_service'] . '] ' . $action_note, $_SESSION['seller_name']);
		}
		else {
			exit('invalid params');
		}

		if ($return) {
			$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=return_info&ret_id=' . $ret_id . '&rec_id=' . $rec_id);
			sys_msg($_LANG['act_ok'] . $msg, 0, $links);
		}
		else {
			$links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
			sys_msg($_LANG['act_ok'] . $msg, 0, $links);
		}
	}
	else if ($_REQUEST['act'] == 'json') {
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$goods_id = isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
		$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
		$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
		$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		$model_attr = isset($_REQUEST['model_attr']) ? intval($_REQUEST['model_attr']) : 0;
		$goods_number = isset($_REQUEST['goods_number']) ? intval($_REQUEST['goods_number']) : 0;
		$func = $_REQUEST['func'];

		if ($func == 'get_goods_info') {
			$leftJoin = ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
			$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
			$sql = 'SELECT g.goods_id, c.cat_name, g.goods_sn, g.goods_name, b.brand_name, g.market_price, g.model_attr, g.user_id, ' . 'IF(g.model_price < 1, g.goods_number, IF(g.model_price < 2, wg.region_number, wag.region_number)) AS goods_number, ' . 'IFNULL(IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)), g.shop_price) AS shop_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_type, g.is_promote ' . 'FROM ' . $ecs->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $ecs->table('brand') . ' AS b ON g.brand_id = b.brand_id ' . 'LEFT JOIN ' . $ecs->table('category') . ' AS c ON g.cat_id = c.cat_id ' . (' WHERE g.goods_id = \'' . $goods_id . '\'');
			$goods = $db->getRow($sql);
			$today = gmtime();
			$goods['goods_price'] = $goods['is_promote'] == 1 && $goods['promote_start_date'] <= $today && $today <= $goods['promote_end_date'] ? $goods['promote_price'] : $goods['shop_price'];
			$goods['warehouse_id'] = $warehouse_id;
			$goods['area_id'] = $area_id;
			$sql = 'SELECT p.user_price, r.rank_name ' . 'FROM ' . $ecs->table('member_price') . ' AS p, ' . $ecs->table('user_rank') . ' AS r ' . 'WHERE p.user_rank = r.rank_id ' . ('AND p.goods_id = \'' . $goods_id . '\' ');
			$goods['user_price'] = $db->getAll($sql);
			$attr_leftJoin = '';
			$select = '';

			if ($goods['model_attr'] == 1) {
				$select = ' wap.attr_price as warehouse_attr_price, ';
				$attr_leftJoin = 'LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_attr') . (' AS wap ON g.goods_attr_id = wap.goods_attr_id AND wap.warehouse_id = \'' . $warehouse_id . '\' ');
			}
			else if ($goods['model_attr'] == 2) {
				$select = ' waa.attr_price as area_attr_price, ';
				$attr_leftJoin = 'LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_attr') . (' AS waa ON g.goods_attr_id = waa.goods_attr_id AND area_id = \'' . $area_id . '\' ');
			}

			$sql = 'SELECT a.attr_id, a.attr_name, g.goods_attr_id, g.attr_value, ' . $select . ' g.attr_price, a.attr_input_type, a.attr_type ' . 'FROM ' . $ecs->table('goods_attr') . ' AS g ' . 'LEFT JOIN' . $ecs->table('attribute') . ' AS a ON g.attr_id = a.attr_id ' . $attr_leftJoin . ('WHERE g.goods_id = \'' . $goods_id . '\' ORDER BY a.sort_order, a.attr_id, g.goods_attr_id');
			$goods['attr_list'] = array();
			$res = $db->query($sql);

			while ($row = $db->fetchRow($res)) {
				if ($goods['model_attr'] == 1) {
					$row['attr_price'] = $row['warehouse_attr_price'];
				}
				else if ($goods['model_attr'] == 2) {
					$row['attr_price'] = $row['area_attr_price'];
				}
				else {
					$row['attr_price'] = $row['attr_price'];
				}

				$goods['attr_list'][$row['attr_id']][] = $row;
			}

			$goods['attr_list'] = array_values($goods['attr_list']);

			if ($goods['attr_list']) {
				foreach ($goods['attr_list'] as $attr_key => $attr_row) {
					$goods_attr_id .= $attr_row[0]['goods_attr_id'] . ',';
					$attr_price += $attr_row[0]['attr_price'];
				}

				$goods_attr_id = substr($goods_attr_id, 0, -1);
				$goods['attr_price'] = $attr_price;
			}

			$products = get_warehouse_id_attr_number($goods_id, $goods_attr_id, $goods['user_id'], $warehouse_id, $area_id);
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

			$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . (' WHERE goods_id = \'' . $goods_id . '\'') . $type_files . ' LIMIT 1';
			$prod = $GLOBALS['db']->getRow($sql);

			if (empty($prod)) {
				$attr_number = $goods['goods_number'];
			}

			$attr_number = !empty($attr_number) ? $attr_number : 0;
			$goods['goods_storage'] = $attr_number;
			echo $json->encode($goods);
		}
		else if ($func == 'get_goods_attr_number') {
			$products = get_warehouse_id_attr_number($goods_id, $_REQUEST['attr'], $user_id, $warehouse_id, $area_id);
			$attr_number = $products['product_number'];

			if ($model_attr == 1) {
				$table_products = 'products_warehouse';
				$type_files = ' and warehouse_id = \'' . $warehouse_id . '\'';
			}
			else if ($model_attr == 2) {
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
				$attr_number = $goods_number;
			}

			$attr_number = !empty($attr_number) ? $attr_number : 0;
			$attr_leftJoin = '';
			$select = '';

			if ($model_attr == 1) {
				$select = ' wap.attr_price as warehouse_attr_price, ';
				$attr_leftJoin = 'LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_attr') . (' AS wap ON g.goods_attr_id = wap.goods_attr_id AND wap.warehouse_id = \'' . $warehouse_id . '\' ');
			}
			else if ($model_attr == 2) {
				$select = ' waa.attr_price as area_attr_price, ';
				$attr_leftJoin = 'LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_attr') . (' AS waa ON g.goods_attr_id = waa.goods_attr_id AND area_id = \'' . $area_id . '\' ');
			}

			$goodsAttr = '';
			if (isset($_REQUEST['attr']) && !empty($_REQUEST['attr'])) {
				$goodsAttr = ' and g.goods_attr_id in(' . $_REQUEST['attr'] . ') ';
			}

			$sql = 'SELECT a.attr_id, a.attr_name, g.goods_attr_id, g.attr_value, ' . $select . ' g.attr_price, a.attr_input_type, a.attr_type ' . 'FROM ' . $ecs->table('goods_attr') . ' AS g ' . 'LEFT JOIN' . $ecs->table('attribute') . ' AS a ON g.attr_id = a.attr_id ' . $attr_leftJoin . ('WHERE g.goods_id = \'' . $goods_id . '\' ') . $goodsAttr . ' ORDER BY a.sort_order, a.attr_id, g.goods_attr_id';
			$goods['attr_list'] = array();
			$res = $db->query($sql);

			while ($row = $db->fetchRow($res)) {
				if ($model_attr == 1) {
					$row['attr_price'] = $row['warehouse_attr_price'];
				}
				else if ($model_attr == 2) {
					$row['attr_price'] = $row['area_attr_price'];
				}
				else {
					$row['attr_price'] = $row['attr_price'];
				}

				$goods['attr_list'][$row['attr_id']][] = $row;
			}

			$goods['attr_list'] = array_values($goods['attr_list']);
			$goods['attr_price'] = 0;

			if ($goods['attr_list']) {
				foreach ($goods['attr_list'] as $attr_key => $attr_row) {
					$attr_price += $attr_row[0]['attr_price'];
				}

				$goods['attr_price'] = $attr_price;
			}

			$goods['goods_id'] = $goods_id;
			$goods['warehouse_id'] = $warehouse_id;
			$goods['area_id'] = $area_id;
			$goods['user_id'] = $user_id;
			$goods['attr'] = $_REQUEST['attr'];
			$goods['model_attr'] = $model_attr;
			$goods['goods_number'] = $goods_number;
			$goods['goods_storage'] = $attr_number;
			echo $json->encode($goods);
		}
	}
	else if ($_REQUEST['act'] == 'ajax_merge_order_list') {
		admin_priv('order_os_edit');
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$merchant_id = empty($_POST['merchant_id']) ? '' : intval($_POST['merchant_id']);
		$store_search = empty($_POST['store_search']) ? -1 : intval($_POST['store_search']);

		if ($store_search != 1) {
			$merchant_id = 0;
		}

		$where = ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . (' WHERE og.order_id = o.order_id limit 0, 1) = \'' . $merchant_id . '\' ');
		$where .= ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
		$sql = 'SELECT o.order_sn, u.user_name ' . 'FROM ' . $ecs->table('order_info') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('users') . ' AS u ON o.user_id = u.user_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON o.order_id=og.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON og.goods_id=g.goods_id ' . 'WHERE o.user_id > 0 ' . $where . 'AND o.extension_code = \'\' ' . order_query_sql('unprocessed') . ' GROUP BY o.order_id';
		$order_list = $db->getAll($sql);
		$smarty->assign('order_list', $order_list);
		make_json_result($smarty->fetch('merge_order_list.htm'));
	}
	else if ($_REQUEST['act'] == 'ajax_merge_order') {
		admin_priv('order_os_edit');
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$from_order_sn = empty($_POST['from_order_sn']) ? '' : json_str_iconv(substr($_POST['from_order_sn'], 1));
		$to_order_sn = empty($_POST['to_order_sn']) ? '' : json_str_iconv(substr($_POST['to_order_sn'], 1));
		$m_result = merge_order($from_order_sn, $to_order_sn);
		$result = array('error' => 0, 'content' => '');

		if ($m_result === true) {
			$result['message'] = $GLOBALS['_LANG']['act_ok'];
		}
		else {
			$result['error'] = 1;
			$result['message'] = $m_result;
		}

		exit($json->encode($result));
	}
	else if ($_REQUEST['act'] == 'remove_order') {
		admin_priv('order_edit');
		$order_id = intval($_REQUEST['id']);
		check_authz_json('order_edit');
		$order = order_info($order_id);
		$operable_list = operable_list($order);

		if (!isset($operable_list['remove'])) {
			make_json_error('Hacking attempt');
			exit();
		}

		$return_order = return_order_info(0, '', $order['order_id']);

		if ($return_order) {
			make_json_error(sprintf($_LANG['order_remove_failure'], $order['order_sn']));
			exit();
		}

		$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\''));
		$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\''));
		$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('order_action') . (' WHERE order_id = \'' . $order_id . '\''));
		$action_array = array('delivery', 'back');
		del_delivery($order_id, $action_array);

		if ($GLOBALS['db']->errno() == 0) {
			$url = 'order.php?act=query&' . str_replace('act=remove_order', '', $_SERVER['QUERY_STRING']);
			ecs_header('Location: ' . $url . "\n");
			exit();
		}
		else {
			make_json_error($GLOBALS['db']->errorMsg());
		}
	}
	else if ($_REQUEST['act'] == 'search_users') {
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$id_name = empty($_GET['id_name']) ? '' : json_str_iconv(trim($_GET['id_name']));
		$result = array('error' => 0, 'message' => '', 'content' => '');

		if ($id_name != '') {
			$sql = 'SELECT user_id, user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_name LIKE \'%' . mysql_like_quote($id_name) . '%\'' . ' LIMIT 20';
			$res = $GLOBALS['db']->query($sql);
			$result['userlist'] = array();

			while ($row = $GLOBALS['db']->fetchRow($res)) {
				$result['userlist'][] = array('user_id' => $row['user_id'], 'user_name' => $row['user_name']);
			}
		}
		else {
			$result['error'] = 1;
			$result['message'] = 'NO KEYWORDS!';
		}

		exit($json->encode($result));
	}
	else if ($_REQUEST['act'] == 'search_goods') {
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$keyword = empty($_GET['keyword']) ? '' : json_str_iconv(trim($_GET['keyword']));
		$order_id = empty($_GET['order_id']) ? '' : intval($_GET['order_id']);
		$warehouse_id = empty($_GET['warehouse_id']) ? '' : intval($_GET['warehouse_id']);
		$area_id = empty($_GET['area_id']) ? '' : intval($_GET['area_id']);
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\'');
		$goods_count = $GLOBALS['db']->getOne($sql);

		if ($goods_count) {
			$sql = 'SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\'');
			$ru_id = $GLOBALS['db']->getAll($sql);
		}
		else {
			$ru_id = array(
				array('ru_id' => $adminru['ru_id'])
				);
		}

		$where = '';

		if ($ru_id) {
			foreach ($ru_id as $key => $row) {
				$ru_str .= $row['ru_id'] . ',';
			}

			$ru_str = substr($ru_str, 0, -1);
			$ru_str = explode(',', $ru_str);
			$ru_str = array_unique($ru_str);
			$ru_str = implode(',', $ru_str);
			$where = ' AND user_id IN(' . $ru_str . ')';
		}

		$result = array('error' => 0, 'message' => '', 'content' => '');

		if ($keyword != '') {
			$sql = 'SELECT goods_id, goods_name, goods_sn, user_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE is_delete = 0' . ' AND is_on_sale = 1' . $where . ' AND is_alone_sale = 1' . ' AND (goods_id LIKE \'%' . mysql_like_quote($keyword) . '%\'' . ' OR goods_name LIKE \'%' . mysql_like_quote($keyword) . '%\'' . ' OR goods_sn LIKE \'%' . mysql_like_quote($keyword) . '%\')' . ' LIMIT 20';
			$res = $GLOBALS['db']->query($sql);
			$result['goodslist'] = array();

			while ($row = $GLOBALS['db']->fetchRow($res)) {
				$result['warehouse_id'] = $warehouse_id;
				$result['area_id'] = $area_id;
				$result['goodslist'][] = array('goods_id' => $row['goods_id'], 'name' => $row['goods_id'] . '  ' . $row['goods_name'] . '  ' . $row['goods_sn'], 'user_id' => $row['user_id']);
			}
		}
		else {
			$result['error'] = 1;
			$result['message'] = 'NO KEYWORDS';
		}

		exit($json->encode($result));
	}
	else if ($_REQUEST['act'] == 'edit_invoice_no') {
		check_authz_json('order_edit');
		$no = empty($_POST['val']) ? 'N/A' : json_str_iconv(trim($_POST['val']));
		$no = $no == 'N/A' ? '' : $no;
		$order_id = empty($_POST['id']) ? 0 : intval($_POST['id']);

		if ($order_id == 0) {
			make_json_error('NO ORDER ID');
			exit();
		}

		$sql = 'SELECT order_sn, order_status, shipping_status, pay_status, invoice_no FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
		$order = $GLOBALS['db']->getRow($sql);
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . (' SET invoice_no=\'' . $no . '\' WHERE order_id = \'' . $order_id . '\'');

		if ($GLOBALS['db']->query($sql)) {
			if (!empty($no) && $no != $order['invoice_no']) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('delivery_order') . (' SET invoice_no = \'' . $no . '\' WHERE order_id = \'' . $order_id . '\'');
				$GLOBALS['db']->query($sql);

				if (empty($order['invoice_no'])) {
					$order['invoice_no'] = 'N/A';
				}

				$note = sprintf($_LANG['edit_order_invoice'], $order['invoice_no'], $no);
				order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], $note, $_SESSION['seller_name']);
			}

			if (empty($no)) {
				make_json_result('N/A');
			}
			else {
				make_json_result(stripcslashes($no));
			}
		}
		else {
			make_json_error($GLOBALS['db']->errorMsg());
		}
	}
	else if ($_REQUEST['act'] == 'edit_pay_note') {
		check_authz_json('order_edit');
		$no = empty($_POST['val']) ? 'N/A' : json_str_iconv(trim($_POST['val']));
		$no = $no == 'N/A' ? '' : $no;
		$order_id = empty($_POST['id']) ? 0 : intval($_POST['id']);

		if ($order_id == 0) {
			make_json_error('NO ORDER ID');
			exit();
		}

		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . (' SET pay_note=\'' . $no . '\' WHERE order_id = \'' . $order_id . '\'');

		if ($GLOBALS['db']->query($sql)) {
			if (empty($no)) {
				make_json_result('N/A');
			}
			else {
				make_json_result(stripcslashes($no));
			}
		}
		else {
			make_json_error($GLOBALS['db']->errorMsg());
		}
	}
	else if ($_REQUEST['act'] == 'get_goods_info') {
		$order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;

		if (empty($order_id)) {
			make_json_response('', 1, $_LANG['error_get_goods_info']);
		}

		$goods_list = array();
		$goods_attr = array();
		$sql = 'SELECT o.*, g.goods_thumb, g.brand_id, g.user_id AS ru_id, g.goods_number AS storage, o.goods_attr ' . 'FROM ' . $ecs->table('order_goods') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('goods') . ' AS g ON o.goods_id = g.goods_id ' . ('WHERE o.order_id = \'' . $order_id . '\' ');
		$res = $db->query($sql);

		while ($row = $db->fetchRow($res)) {
			if ($row['is_real'] == 0) {
				$filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $_CFG['lang'] . '.php';

				if (file_exists($filename)) {
					include_once $filename;

					if (!empty($_LANG[$row['extension_code'] . '_link'])) {
						$row['goods_name'] = $row['goods_name'] . sprintf($_LANG[$row['extension_code'] . '_link'], $row['goods_id'], $order['order_sn']);
					}
				}
			}

			$brand = get_goods_brand_info($row['brand_id']);
			$row['brand_name'] = $brand['brand_name'];
			$row['formated_subtotal'] = price_format($row['goods_price'] * $row['goods_number']);
			$row['formated_goods_price'] = price_format($row['goods_price']);
			$goods['goods_thumb'] = get_image_path($goods['goods_id'], $goods['goods_thumb'], true);
			$goods_attr[] = explode(' ', trim($row['goods_attr']));
			$goods_list[] = $row;
		}

		$attr = array();
		$arr = array();

		foreach ($goods_attr as $index => $array_val) {
			foreach ($array_val as $value) {
				$arr = explode(':', $value);
				$attr[$index][] = array('name' => $arr[0], 'value' => $arr[1]);
			}
		}

		$smarty->assign('goods_attr', $attr);
		$smarty->assign('goods_list', $goods_list);
		$str = $smarty->fetch('order_goods_info.htm');
		$goods[] = array('order_id' => $order_id, 'str' => $str);
		make_json_result($goods);
	}
	else if ($_REQUEST['act'] == 'update_info') {
		$sign_time = local_strtotime($_REQUEST['time']);
		$order_id = $_REQUEST['order_id'];
		$sql = 'UPDATE ' . $ecs->table('order_info') . 'set sign_time =' . $sign_time . ' WHERE order_id =' . $order_id;
		$db->query($sql);
	}
	else if ($_REQUEST['act'] == 'set_grab_order') {
		$order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
		$result = array('error' => 0, 'message' => '', 'content' => '');
		$smarty->assign('order_id', $order_id);
		$store_list = get_store_list($order_id);
		$store_order_info = get_store_order_info($order_id, 'order_id');

		if (!empty($store_order_info)) {
			$grab_store_arr = explode(',', $store_order_info['grab_store_list']);

			foreach ($store_list as $key => $val) {
				$store_list[$key]['is_check'] = 0;

				if (in_array($val['id'], $grab_store_arr)) {
					$store_list[$key]['is_check'] = 1;
				}
			}
		}

		$smarty->assign('store_list', $store_list);
		$result['content'] = $smarty->fetch('library/set_grab_order.lbi');
		exit(json_encode($result));
	}
	else if ($_REQUEST['act'] == 'set_grab') {
		$order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
		$ru_id = get_ru_id($order_id);
		if (isset($_REQUEST['checkboxes']) && 0 < count($_REQUEST['checkboxes'])) {
			$grab_store_list = implode(',', $_REQUEST['checkboxes']);
			$store_order_info = get_store_order_info($order_id, 'order_id');

			if (empty($store_order_info)) {
				$sql = ' INSERT INTO ' . $GLOBALS['ecs']->table('store_order') . ' (order_id, store_id, ru_id, is_grab_order, grab_store_list) ' . (' VALUES (\'' . $order_id . '\', \'0\', \'' . $ru_id . '\', \'1\', \'' . $grab_store_list . '\') ');
				$GLOBALS['db']->query($sql);
			}
			else {
				$sql = ' UPDATE ' . $GLOBALS['ecs']->table('store_order') . (' SET grab_store_list = \'' . $grab_store_list . '\' WHERE order_id = \'' . $order_id . '\' ');
				$GLOBALS['db']->query($sql);
			}
		}
		else {
			$sql = ' DELETE FROM' . $GLOBALS['ecs']->table('store_order') . (' WHERE order_id = \'' . $order_id . '\' AND ru_id = \'' . $ru_id . '\' AND store_id = 0');
			$GLOBALS['db']->query($sql);
		}

		sys_msg($_LANG['set_success']);
	}
	else if ($_REQUEST['act'] == 'part_ship') {
		check_authz_json('order_ss_edit');
		$rec_id = empty($_REQUEST['rec_id']) ? 0 : intval($_REQUEST['rec_id']);
		$sql = 'SELECT o.*, g.model_inventory, g.model_attr AS model_attr, g.suppliers_id AS suppliers_id, g.goods_number AS storage, g.goods_thumb, o.goods_attr, IFNULL(b.brand_name, \'\') AS brand_name, p.product_sn, g.bar_code  ' . 'FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS o ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('products') . ' AS p ON o.product_id = p.product_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON o.goods_id = g.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON g.brand_id = b.brand_id ' . ('WHERE o.rec_id = \'' . $rec_id . '\' ');
		$row = $GLOBALS['db']->getRow($sql);
		$row['left_number'] = $row['goods_number'] - $row['send_number'];

		if (0 < $row['product_id']) {
			$products = get_warehouse_id_attr_number($row['goods_id'], $row['goods_attr_id'], $row['ru_id'], $row['warehouse_id'], $row['area_id'], $row['model_attr']);
			$row['storage'] = $products['product_number'];
		}
		else if ($row['model_inventory'] == 1) {
			$row['storage'] = get_warehouse_area_goods($row['warehouse_id'], $row['goods_id'], 'warehouse_goods');
		}
		else if ($row['model_inventory'] == 2) {
			$row['storage'] = get_warehouse_area_goods($row['area_id'], $row['goods_id'], 'warehouse_area_goods');
		}

		if ($row['extension_code'] == 'package_buy') {
			$row['storage'] = '';
		}

		$order_goods = $row;
		$smarty->assign('order_goods', $order_goods);
		$order = order_info($order_goods['order_id']);
		$smarty->assign('order', $order);
		$smarty->assign('operation', 'split');
		$content = $smarty->fetch('library/order_part_ship.lbi');
		make_json_result($content);
	}
	else if ($_REQUEST['act'] == 'batch_ship') {
		check_authz_json('order_ss_edit');
		$delivery_ids = empty($_REQUEST['delivery_ids']) ? '' : trim($_REQUEST['delivery_ids']);
		$sql = ' SELECT delivery_id, delivery_sn, order_sn, order_id, shipping_id, shipping_name, ' . ' consignee, mobile, email, country, province, city, district, address ' . ' FROM ' . $GLOBALS['ecs']->table('delivery_order') . (' WHERE delivery_id IN (' . $delivery_ids . ') AND status = 2 ');
		$delivery_orders = $GLOBALS['db']->getAll($sql);
		$new_array = array();

		foreach ($delivery_orders as $key => $val) {
			$new_array[$val['shipping_name']][] = $val;
		}

		foreach ($new_array as $key => $val) {
			foreach ($val as $k => $v) {
				$val[$k]['region'] = get_user_region_address($v['order_id']);
			}

			$arr = array();
			$arr['count'] = count($val);
			$arr['list'] = $val;
			$new_array[$key] = $arr;
		}

		$smarty->assign('delivery_orders', $new_array);
		$content = $smarty->fetch('library/order_batch_ship.lbi');
		make_json_result($content);
	}
}

?>
