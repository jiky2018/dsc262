<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function order_download_list($result)
{
	if (empty($result)) {
		return i('没有符合您要求的数据！^_^');
	}

	$downLang = $GLOBALS['_LANG']['down']['order_sn'] . ',' . $GLOBALS['_LANG']['down']['short_order_time'] . ',' . $GLOBALS['_LANG']['down']['consignee_address'] . ',' . $GLOBALS['_LANG']['down']['total_fee'] . ',' . $GLOBALS['_LANG']['down']['shipping_fee'] . ',' . $GLOBALS['_LANG']['down']['discount'] . ',' . $GLOBALS['_LANG']['down']['coupons'] . ',' . $GLOBALS['_LANG']['down']['integral_money'] . ',' . $GLOBALS['_LANG']['down']['bonus'] . ',' . $GLOBALS['_LANG']['down']['return_amount_price'] . ',' . $GLOBALS['_LANG']['down']['brokerage_amount_price'] . ',' . $GLOBALS['_LANG']['down']['effective_amount_price'] . ',' . $GLOBALS['_LANG']['down']['settlement_status'] . ',' . $GLOBALS['_LANG']['down']['ordersTatus'];
	$data = i($downLang . "\n");
	$count = count($result);

	for ($i = 0; $i < $count; $i++) {
		$order_sn = i('#' . $result[$i]['order_sn']);
		$short_order_time = i($result[$i]['short_order_time']);
		$consignee = i($result[$i]['consignee']) . '' . i($result[$i]['address']);
		$total_fee = i($result[$i]['order_total_fee']);
		$shipping_fee = i($result[$i]['shipping_fee']);
		$discount = i($result[$i]['discount']);
		$coupons = i($result[$i]['coupons']);
		$integral_money = i($result[$i]['integral_money']);
		$bonus = i($result[$i]['bonus']);
		$return_amount_price = i($result[$i]['return_amount_price']);
		$brokerage_amount_price = i($result[$i]['brokerage_amount_price']);
		$effective_amount_price = i($result[$i]['effective_amount_price'] + $result[$i]['shipping_fee']);
		$is_settlement = i($result[$i]['settlement_status']);
		$status = i($result[$i]['ordersTatus']);

		if ($consignee) {
			$consignee = str_replace(array(',', '，'), '_', $consignee);
		}

		$data .= $order_sn . ',' . $short_order_time . ',' . $consignee . ',' . $total_fee . ',' . $shipping_fee . ',' . $discount . ',' . $coupons . ',' . $integral_money . ',' . $bonus . ',' . $return_amount_price . ',' . $brokerage_amount_price . ',' . $status . ',' . $effective_amount_price . ',' . $is_settlement . ',' . $is_frozen . "\n";
	}

	return $data;
}

function merchants_commission_list()
{
	$adminru = get_admin_ru_id();
	$result = get_filter();

	if ($result === false) {
		$aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 's.server_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
		$where = 'WHERE 1 ';

		if (0 < $adminru['ru_id']) {
			$where .= ' AND s.user_id = \'' . $adminru['ru_id'] . '\'';
		}

		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($adminru['ru_id'] == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$where .= ' AND mis.user_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$where .= ' AND mis.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$where .= ' AND mis.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}
			}
		}

		$where .= ' AND mis.merchants_audit = 1 ';
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

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_server') . ' as s ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as mis on s.user_id = mis.user_id ' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = "SELECT u.user_name, mis.*, msf.*, s.server_id, s.user_id, s.suppliers_desc, s.suppliers_percent  \n                FROM " . $GLOBALS['ecs']->table('merchants_server') . ' as s ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as mis on s.user_id = mis.user_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' as u on s.user_id = u.user_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_steps_fields') . ' as msf on s.user_id = msf.user_id ' . (' ' . $where . ' ') . ' group by s.user_id ' . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
		$sql .= ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . ', ' . $filter['page_size'] . ' ';
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);
	$count = count($row);

	for ($i = 0; $i < $count; $i++) {
		$row[$i]['server_id'] = $row[$i]['server_id'];
		$valid = get_merchants_order_valid_refund($row[$i]['user_id']);
		$row[$i]['order_valid_total'] = price_format($valid['total_fee']);
		$refund = get_merchants_order_valid_refund($row[$i]['user_id'], 1);
		$row[$i]['order_refund_total'] = price_format($refund['total_fee']);
		$row[$i]['store_name'] = get_shop_name($row[$i]['user_id'], 1);
		$row[$i]['order_valid_total'] = price_format($valid['total_fee']);
		$row[$i]['is_goods_rate'] = $valid['is_goods_rate'];
		$row[$i]['order_total_fee'] = price_format($valid['order_total_fee']);
		$row[$i]['goods_total_fee'] = price_format($valid['goods_total_fee']);

		if (file_exists(MOBILE_DRP)) {
			$row[$i]['order_drp_commission'] = price_format($valid['drp_money']);
		}

		if (file_exists(MOBILE_DRP)) {
			$is_settlement = merchants_is_settlement($row[$i]['user_id'], 1);
			$row[$i]['is_settlement'] = $is_settlement['all'];
			$no_settlement = merchants_is_settlement($row[$i]['user_id'], 0);
			$row[$i]['no_settlement'] = $no_settlement['all'];
		}
		else {
			$is_settlement = merchants_is_settlement($row[$i]['user_id'], 1);
			$row[$i]['is_settlement'] = price_format($is_settlement);
			$no_settlement = merchants_is_settlement($row[$i]['user_id'], 0);
			$row[$i]['no_settlement'] = price_format($no_settlement);
		}

		$row[$i]['total_fee_price'] = number_format($valid['total_fee'], 2, '.', '');
		$row[$i]['total_fee_refund'] = number_format($refund['total_fee'], 2, '.', '');
		$row[$i]['is_settlement_price'] = $is_settlement;
		$row[$i]['no_settlement_price'] = $no_settlement;
		$sql = 'SELECT ss.shop_name, ss.shop_address, ss.mobile, ' . 'concat(IFNULL(p.region_name, \'\'), ' . '\'  \', IFNULL(t.region_name, \'\'), \'  \', IFNULL(d.region_name, \'\')) AS region ' . ' FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS ss ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON ss.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON ss.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON ss.district = d.region_id ' . ' WHERE ss.ru_id = \'' . $row[$i]['user_id'] . '\' LIMIT 1';
		$seller_shopinfo = $GLOBALS['db']->getRow($sql);

		if ($seller_shopinfo['shop_name']) {
			$row[$i]['companyName'] = $seller_shopinfo['shop_name'];
			$row[$i]['company_adress'] = '[' . $seller_shopinfo['region'] . '] ' . $seller_shopinfo['shop_address'];
		}

		if ($seller_shopinfo['mobile']) {
			$row[$i]['company_contactTel'] = $seller_shopinfo['mobile'];
		}
		else {
			$row[$i]['company_contactTel'] = $row[$i]['contactPhone'];
		}
	}

	$arr = array('result' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_suppliers_percent()
{
	$sql = 'select percent_id, percent_value from ' . $GLOBALS['ecs']->table('merchants_percent') . ' where 1 order by sort_order asc';
	$res = $GLOBALS['db']->getAll($sql);
	return $res;
}

function merchants_order_list($page = 0)
{
	$adminru = get_admin_ru_id();
	$result = get_filter();

	if ($result === false) {
		$aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;
		$filter['id'] = !isset($_REQUEST['id']) && empty($_REQUEST['id']) ? $adminru['ru_id'] : trim($_REQUEST['id']);
		$filter['order_sn'] = !isset($_REQUEST['order_sn']) && empty($_REQUEST['order_sn']) ? '' : dsc_addslashes($_REQUEST['order_sn']);
		$filter['consignee'] = !isset($_REQUEST['consignee']) && empty($_REQUEST['consignee']) ? '' : dsc_addslashes($_REQUEST['consignee']);
		$filter['order_cat'] = !isset($_REQUEST['order_cat']) && empty($_REQUEST['order_cat']) ? '' : dsc_addslashes($_REQUEST['order_cat']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'o.order_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['start_time'] = empty($_REQUEST['start_time']) ? '' : local_strtotime(trim($_REQUEST['start_time']));
		$filter['end_time'] = empty($_REQUEST['end_time']) ? '' : local_strtotime(trim($_REQUEST['end_time']));
		$filter['state'] = isset($_REQUEST['state']) ? trim($_REQUEST['state']) : '';
		$commission_info = get_seller_commission_info($filter['id']);
		$commission_basic = $commission_info;
		if ($commission_info && $commission_info['percent_value']) {
			$percent_value = $commission_info['percent_value'] / 100;
		}
		else {
			$percent_value = 1;
		}

		$filter['commission_model'] = $commission_info['commission_model'];
		$where = 'WHERE 1';

		if ($filter['order_sn']) {
			$where .= ' AND o.order_sn LIKE \'%' . mysql_like_quote($filter['order_sn']) . '%\'';
		}

		if ($filter['consignee']) {
			$where .= ' AND o.consignee LIKE \'%' . mysql_like_quote($filter['consignee']) . '%\'';
		}

		if ($filter['order_cat']) {
			switch ($filter['order_cat']) {
			case 'stages':
				$where .= ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('baitiao_log') . ' AS b WHERE b.order_id = o.order_id) > 0 ';
				break;

			case 'zc':
				$where .= ' AND o.is_zc_order = 1 ';
				break;

			case 'store':
				$where .= ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('store_order') . ' AS s WHERE s.order_id = o.order_id) > 0 ';
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

		if (isset($filter['state']) && $filter['state'] != '') {
			$where .= ' AND is_settlement = \'' . $filter['state'] . '\' ';
		}

		if (!empty($filter['start_time'])) {
			$where .= ' AND o.add_time >= \'' . $filter['start_time'] . '\' ';
		}

		if (!empty($filter['end_time'])) {
			$where .= ' AND o.add_time <= \'' . $filter['end_time'] . '\' ';
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

		$where .= order_query_sql('confirm_take', 'o.');
		$where .= ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
		$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . ' WHERE og.order_id = o.order_id LIMIT 1) = \'' . $filter['id'] . '\' ';
		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT o.is_frozen,o.order_id, o.main_order_id, o.order_sn, o.add_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid, o.is_delete, o.is_settlement, chargeoff_status,' . '(' . order_amount_field('o.') . ') AS order_total_fee,' . 'o.shipping_time, o.auto_delivery_time, o.pay_status, o.consignee, o.address, o.email, o.tel, o.extension_code, o.extension_id, o.goods_amount, o.shipping_fee, ' . '(' . order_commission_field('o.') . ') AS total_fee, o.discount, o.coupons, o.integral_money, o.bonus, o.user_id, ' . '(' . order_activity_field_add('o.') . ') AS activity_fee ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $where . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ');
		$sql .= ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);
	$count = count($row);

	for ($i = 0; $i < $count; $i++) {
		$sql = 'SELECT percent_value FROM ' . $GLOBALS['ecs']->table('seller_account_log') . ' WHERE order_id = \'' . $row[$i]['order_id'] . '\' AND log_type = 2 LIMIT 1';
		$account_log = $GLOBALS['db']->getRow($sql);
		$row[$i]['is_rectified'] = 0;
		if ($account_log && $account_log['percent_value']) {
			$percent_value = $account_log['percent_value'] / 100;
		}
		else {
			$sql = 'SELECT bill_id FROM ' . $GLOBALS['ecs']->table('seller_bill_order') . ' WHERE order_id = \'' . $row[$i]['order_id'] . '\'';
			$bill_id = $GLOBALS['db']->getOne($sql, true);
			if ($bill_id && 0 < $row[$i]['chargeoff_status']) {
				$bill_detail = array('id' => $bill_id);
				$bill = get_bill_detail($bill_detail);
				$commission_info = array('commission_model' => $bill['commission_model'], 'percent_value' => $bill['proportion']);
				$percent_value = $commission_info['percent_value'];

				if (!empty($percent_value)) {
					if (1 < $percent_value) {
						$percent_value = $percent_value / 100;
					}
				}

				$row[$i]['bill_sn'] = $bill['bill_sn'];
			}
			else {
				$row[$i]['is_rectified'] = 1;
				$commission_info = $commission_basic;
			}
		}

		$row[$i]['commission_model'] = $commission_info['commission_model'];
		$row[$i]['account_log'] = $account_log;
		$row[$i]['percent_value'] = $percent_value * 100;
		$row[$i]['is_commission'] = $is_commission;
		$sql = ' SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $row[$i]['user_id'] . '\'';
		$row[$i]['buyer'] = $GLOBALS['db']->getOne($sql, true);
		$row[$i]['buyer'] = !empty($row[$i]['buyer']) ? $row[$i]['buyer'] : $GLOBALS['_LANG']['anonymous'];
		$filter['percent_value'] = $percent_value * 100;
		$row[$i]['ru_id'] = $filter['id'];
		$row[$i]['formated_order_amount'] = price_format($row[$i]['order_amount'], false);
		$row[$i]['formated_money_paid'] = price_format($row[$i]['money_paid'], false);
		$row[$i]['formated_total_fee'] = price_format($row[$i]['total_fee'], false);
		$row[$i]['short_order_time'] = local_date($GLOBALS['_CFG']['time_format'], $row[$i]['add_time']);
		$row[$i]['ordersTatus'] = $GLOBALS['_LANG']['os'][$row[$i]['order_status']] . '|' . $GLOBALS['_LANG']['ps'][$row[$i]['pay_status']] . '|' . $GLOBALS['_LANG']['ss'][$row[$i]['shipping_status']];
		$row[$i]['formated_discount'] = price_format($row[$i]['discount'], false);
		$row[$i]['formated_coupons'] = price_format($row[$i]['coupons'], false);
		$row[$i]['formated_integral_money'] = price_format($row[$i]['integral_money'], false);
		$row[$i]['formated_bonus'] = price_format($row[$i]['bonus'], false);
		$row[$i]['formated_order_total_fee'] = price_format($row[$i]['order_total_fee'] - $row[$i]['discount'], true);
		$row[$i]['formated_order_amount_field'] = price_format($row[$i]['total_fee'] + $row[$i]['shipping_fee'], false);
		$row[$i]['formated_shipping_fee'] = price_format($row[$i]['shipping_fee'], false);

		if ($row[$i]['is_settlement']) {
			$row[$i]['settlement_status'] = '已结算';
		}
		else {
			$row[$i]['settlement_status'] = '未结算';
		}

		$row[$i]['settlement_frozen'] = '';

		if ($row[$i]['is_frozen']) {
			$row[$i]['settlement_frozen'] = '冻结';
		}

		$row[$i]['consignee'] = '【' . $row[$i]['consignee'] . '】';
		$row[$i]['return_amount'] = get_order_return_list($row[$i]['order_id']);
		$row[$i]['return_amount'] = !empty($row[$i]['return_amount']) ? $row[$i]['return_amount'] : '0.00';
		$row[$i]['formated_return_amount'] = price_format($row[$i]['return_amount'], false);
		$row[$i]['is_goods_rate'] = 0;
		$order = array('goods_amount' => $row[$i]['goods_amount'], 'activity_fee' => $row[$i]['activity_fee']);
		$goods_rate = get_alone_goods_rate($row[$i]['order_id'], 0, $order);
		$row[$i]['goods_rate'] = $goods_rate;

		if ($goods_rate) {
			if (0 < $goods_rate['should_amount']) {
				$row[$i]['is_goods_rate'] = 1;
			}
		}

		if (file_exists(MOBILE_DRP)) {
			$brokerage_amount = get_order_drp_money($row[$i]['total_fee'], $filter['id'], $row[$i]['order_id'], $order);

			if ($goods_rate) {
				$brokerage_amount['total_fee'] = $brokerage_amount['total_fee'] - $goods_rate['total_fee'];

				if ($goods_rate['total_fee']) {
					if ($brokerage_amount['total_fee'] <= 0) {
						$row[$i]['is_goods_rate'] = 1;
					}

					if ($brokerage_amount['total_fee'] < 0) {
						$brokerage_amount['total_fee'] = 0;
					}
				}
			}

			if (0 < $brokerage_amount['total_fee']) {
				$total_return_amount = $brokerage_amount['total_fee'] - $row[$i]['return_amount'];
			}
			else {
				$total_return_amount = 0;
			}

			if ($commission_info['commission_model']) {
				$order_goods_commission = get_order_goods_commission($row[$i]['order_id']);

				if ($row[$i]['goods_amount'] <= 0) {
					$row[$i]['goods_amount'] = 1;
				}

				if (0 < $order_goods_commission['commission']) {
					$order_commission = $order_goods_commission['commission'] * $total_return_amount / ($order_goods_commission['goods_amount'] - $brokerage_amount['rate_activity']) + $brokerage_amount['should_amount'];
				}
				else {
					$order_commission = $total_return_amount + $brokerage_amount['should_amount'];
				}

				$row[$i]['formated_brokerage_amount'] = price_format($order_commission + $row[$i]['shipping_fee'], false);
				$effective_amount_price = $order_commission;
				$row[$i]['effective_amount_price'] = number_format($effective_amount_price, 2, '.', '');
			}
			else {
				$row[$i]['formated_brokerage_amount'] = price_format($total_return_amount * $percent_value + $row[$i]['shipping_fee'] + $brokerage_amount['should_amount'], false);
				$effective_amount_price = $total_return_amount * $percent_value + $brokerage_amount['should_amount'];
				$row[$i]['effective_amount_price'] = number_format($effective_amount_price, 2, '.', '');
			}

			if (0 < $brokerage_amount['should_amount']) {
				$row[$i]['is_goods_rate'] = 1;
			}

			$row[$i]['formated_effective_amount'] = price_format($total_return_amount, false);
			$row[$i]['formated_drp_commission'] = price_format($brokerage_amount['drp_money'], false);
			$row[$i]['brokerage_amount_price'] = $total_return_amount;
			$row[$i]['total_fee'] = $brokerage_amount['total_fee'];
		}
		else {
			if ($goods_rate) {
				$row[$i]['total_fee'] = $row[$i]['total_fee'] - $goods_rate['total_fee'];

				if ($goods_rate['total_fee']) {
					if ($row[$i]['total_fee'] <= 0) {
						$row[$i]['is_goods_rate'] = 1;
					}

					if ($row[$i]['total_fee'] < 0) {
						$row[$i]['total_fee'] = 0;
					}
				}
			}

			if (0 < $row[$i]['total_fee']) {
				$total_return_amount = $row[$i]['total_fee'] - $row[$i]['return_amount'];
			}
			else {
				$total_return_amount = 0;
			}

			if ($commission_info['commission_model']) {
				$order_goods_commission = get_order_goods_commission($row[$i]['order_id']);

				if ($row[$i]['goods_amount'] <= 0) {
					$row[$i]['goods_amount'] = 1;
				}

				if (0 < $order_goods_commission['commission']) {
					$order_commission = $order_goods_commission['commission'] * $total_return_amount / $order_goods_commission['goods_amount'] + $goods_rate['should_amount'];
				}
				else {
					$order_commission = $total_return_amount + $goods_rate['should_amount'];
				}

				$row[$i]['formated_brokerage_amount'] = price_format($order_commission + $row[$i]['shipping_fee'], false);
				$effective_amount_price = $order_commission;
				$row[$i]['effective_amount_price'] = number_format($effective_amount_price, 2, '.', '');
			}
			else {
				$row[$i]['formated_brokerage_amount'] = price_format($total_return_amount * $percent_value + $row[$i]['shipping_fee'] + $goods_rate['should_amount'], false);
				$effective_amount_price = $total_return_amount * $percent_value + $goods_rate['should_amount'];
				$row[$i]['effective_amount_price'] = number_format($effective_amount_price, 2, '.', '');
			}

			$row[$i]['formated_effective_amount'] = price_format($total_return_amount, false);
			$row[$i]['brokerage_amount_price'] = $total_return_amount;
		}

		$row[$i]['formated_effective_amount_price'] = price_format($row[$i]['effective_amount_price'], false);
		$row[$i]['total_fee_price'] = $row[$i]['total_fee'];
		$row[$i]['return_amount_price'] = $row[$i]['return_amount'];
		$row['brokerage_amount']['ru_id'] = $filter['id'];
	}

	if ($count) {
		if (file_exists(MOBILE_DRP)) {
			$is_settlement = merchants_is_settlement($filter['id'], 1, $filter);
			$is_settlement = $is_settlement['all'];
			$no_settlement = merchants_is_settlement($filter['id'], 0, $filter);
			$no_settlement = $no_settlement['all'];
			$all_commission = merchants_is_settlement($filter['id'], '', $filter);
			$row['brokerage_amount']['all_price'] = number_format($all_commission['all_prcie'], 2, '.', '');
			$row['brokerage_amount']['all'] = $all_commission['all'];
			$row['brokerage_amount']['all_drp'] = $all_commission['all_drp'];
			$row['brokerage_amount']['is_settlement'] = $is_settlement;
			$row['brokerage_amount']['no_settlement'] = $no_settlement;
		}
		else {
			$is_settlement = merchants_is_settlement($filter['id'], 1, $filter);
			$no_settlement = merchants_is_settlement($filter['id'], 0, $filter);
			$all_commission = merchants_is_settlement($filter['id'], '', $filter);
			$row['brokerage_amount']['is_settlement'] = price_format($is_settlement, false);
			$row['brokerage_amount']['no_settlement'] = price_format($no_settlement, false);
			$row['brokerage_amount']['all_price'] = number_format($all_commission, 2, '.', '');
			$row['brokerage_amount']['all'] = price_format($all_commission, false);
			$row['brokerage_amount']['is_settlement_price'] = $is_settlement;
			$row['brokerage_amount']['no_settlement_price'] = $no_settlement;
		}
	}

	$arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function merchants_order_list_checked($user_id = 0, $checked_id = 0)
{
	$where = 'WHERE 1';
	$where .= ' and o.is_settlement = 0 ';
	$where .= ' and o.order_id ' . $checked_id;
	$where .= order_query_sql('finished', 'o.');
	$where .= ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
	$where .= ' and og.ru_id = \'' . $user_id . '\' group by o.order_id ';
	$sql = 'SELECT og.ru_id, o.user_id, o.order_id, o.main_order_id, o.order_sn, o.add_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid, o.is_delete, o.is_settlement,' . 'o.shipping_time, o.auto_delivery_time, o.pay_status, o.consignee, o.address, o.email, o.tel, o.extension_code, o.extension_id, ' . '(' . order_amount_field('o.') . ') AS total_fee, ' . 'IFNULL(u.user_name, \'' . $GLOBALS['_LANG']['anonymous'] . '\') AS buyer ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id=o.user_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON o.order_id=og.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON og.goods_id=g.goods_id ' . $where;
	$row = $GLOBALS['db']->getAll($sql);
	return $row;
}

function findNum($str = '')
{
	$str = trim($str);

	if (empty($str)) {
		return '';
	}

	$temp = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
	$result = '';

	for ($i = 0; $i < strlen($str); $i++) {
		if (in_array($str[$i], $temp)) {
			$result .= $str[$i];
		}
	}

	if ($result == '000') {
		$result = 0;
	}

	return $result;
}

function changeSettlement($str)
{
	if ($str == '0') {
		$str = '未结算';
	}
	else {
		$str = '已结算';
	}

	return $str;
}

function commission_download_list($result)
{
	if (empty($result)) {
		return i('没有符合您要求的数据！^_^');
	}

	$data = i('商家名称,店铺名称,公司名称,公司地址,联系方式,订单有效总金额,订单退款总金额,已结算订单金额,未结算订单金额' . "\n");
	$count = count($result);

	for ($i = 0; $i < $count; $i++) {
		$user_name = i($result[$i]['user_name']);
		$store_name = i($result[$i]['store_name']);
		$companyName = i($result[$i]['companyName']);
		$company_adress = i($result[$i]['company_adress']);
		$company_contactTel = i($result[$i]['company_contactTel']);
		$order_valid_total = i($result[$i]['total_fee_price']);
		$order_refund_total = i($result[$i]['total_fee_refund']);
		$is_settlement = i($result[$i]['is_settlement_price']);
		$no_settlement = i($result[$i]['no_settlement_price']);

		if ($company_adress) {
			$company_adress = str_replace(array(',', '，'), '_', $company_adress);
		}

		$data .= $user_name . ',' . $store_name . ',' . $companyName . ',' . $company_adress . ',' . $company_contactTel . ',' . $order_valid_total . ',' . $order_refund_total . ',' . $is_settlement . ',' . $no_settlement . "\n";
	}

	return $data;
}

function i($strInput)
{
	return iconv('utf-8', 'gb2312', $strInput);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'merchants_commission');
define('SUPPLIERS_ACTION_LIST', 'delivery_view,back_view');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('primary_cat', $_LANG['17_merchants']);
$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => '03_merchants_commission'));
$smarty->assign('commission_model', $_CFG['commission_model']);

if ($_REQUEST['act'] == 'list') {
	admin_priv('merchants_commission');
	$tab_menu = array();
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['seller_commission'], 'href' => 'merchants_commission.php?act=list');
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['commission_setup'], 'href' => 'merchants_commission.php?act=edit&id=' . $adminru['ru_id']);
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['commission_bill'], 'href' => 'merchants_commission.php?act=commission_bill&id=' . $adminru['ru_id']);
	$smarty->assign('tab_menu', $tab_menu);
	$smarty->assign('current', 'merchants_commission_list');
	$smarty->assign('ur_here', $_LANG['brokerage_amount_list']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$result = merchants_commission_list();
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('admin_user') . ' WHERE user_id = ' . $_SESSION['seller_id']);

	if ($priv_str != 'all') {
		$smarty->assign('no_all', 0);
		$ser_name = $_LANG['suppliers_list_server'];
	}
	else {
		$smarty->assign('no_all', 1);
		$smarty->assign('action_link', array('href' => 'merchants_commission.php?act=add', 'text' => $_LANG['add_suppliers_server']));
		$smarty->assign('action_link2', array('href' => 'merchants_percent.php?act=list', 'text' => $_LANG['suppliers_percent_list']));
		$smarty->assign('action_link3', array('href' => 'javascript:download_list();', 'text' => $_LANG['export_all_suppliers']));
		$ser_name = $_LANG['suppliers_server_center'];
	}

	$smarty->assign('full_page', 1);
	$smarty->assign('merchants_commission_list', $result['result']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->display('merchants_commission_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	check_authz_json('merchants_commission');
	$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('admin_user') . ' WHERE user_id = ' . $_SESSION['seller_id']);

	if ($priv_str != 'all') {
		$smarty->assign('no_all', 0);
		$ser_name = $_LANG['suppliers_list_server'];
	}
	else {
		$smarty->assign('no_all', 1);
	}

	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$result = merchants_commission_list();
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('merchants_commission_list', $result['result']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$sort_flag = sort_flag($result['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('merchants_commission_list.dwt'), '', array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('merchants_commission');
	$tab_menu = array();
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['seller_commission'], 'href' => 'merchants_commission.php?act=list');
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['commission_setup'], 'href' => 'merchants_commission.php?act=edit&id=' . $adminru['ru_id']);
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['commission_bill'], 'href' => 'merchants_commission.php?act=commission_bill&id=' . $adminru['ru_id']);
	$smarty->assign('tab_menu', $tab_menu);
	$smarty->assign('ur_here', $_LANG['suppliers_list_server']);
	$suppliers = array();
	$sql = 'SELECT ms.*, mp.percent_value FROM ' . $ecs->table('merchants_server') . ' AS ms ' . 'LEFT JOIN ' . $ecs->table('merchants_percent') . ' AS mp ON mp.percent_id = ms.suppliers_percent' . ' WHERE user_id = \'' . $adminru['ru_id'] . '\'';
	$server = $db->getRow($sql);

	if ($server['bill_time']) {
		$server['bill_time'] = local_date('Y-m-d', $server['bill_time']);
	}

	$settlement_cycle = $GLOBALS['_LANG']['cfg_range']['settlement_cycle'];
	$smarty->assign('user_id', $id);
	$smarty->assign('server', $server);
	$smarty->assign('settlement_cycle', $settlement_cycle[$server['cycle']]);
	$smarty->assign('nowtime', local_date('Y-m-d', gmtime()));
	assign_query_info();
	$smarty->display('merchants_commission_info.dwt');
}
else if ($_REQUEST['act'] == 'order_list') {
	admin_priv('merchants_commission');
	$date = array('suppliers_percent');
	$percent_id = get_table_date('merchants_server', 'user_id = \'' . $adminru['ru_id'] . '\' ', $date, $sqlType = 2);
	$date = array('percent_value');
	$percent_value = get_table_date('merchants_percent', 'percent_id = \'' . $percent_id . '\'', $date, $sqlType = 2) . '%';
	$smarty->assign('percent_value', $percent_value);

	if (0 < $adminru['ru_id']) {
		$smarty->assign('no_all', 0);
	}
	else {
		$smarty->assign('no_all', 1);
	}

	$smarty->assign('action_link2', array('text' => $_LANG['03_merchants_commission'], 'href' => 'merchants_commission.php?act=list', 'class' => 'icon-reply'));
	$smarty->assign('action_link', array('href' => 'javascript:order_downloadList();', 'text' => $_LANG['export_merchant_commission']));
	$smarty->assign('ur_here', $_LANG['brokerage_order_list']);
	$smarty->assign('full_page', 1);
	$order_list = merchants_order_list();
	$page_count_arr = seller_page($order_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('user_id', $adminru['ru_id']);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$smarty->assign('server_id', '<img src="images/sort_desc.gif">');
	$commission_info = get_seller_commission_info($filter['id']);

	if (file_exists(MOBILE_DRP)) {
		$smarty->assign('is_dir', 1);
	}
	else {
		$smarty->assign('is_dir', 0);
	}

	assign_query_info();
	$smarty->display('merchants_order_list.dwt');
}
else if ($_REQUEST['act'] == 'order_query') {
	check_authz_json('merchants_commission');
	$order_list = merchants_order_list();
	$date = array('suppliers_percent');
	$percent_id = get_table_date('merchants_server', 'user_id = \'' . $adminru['ru_id'] . '\' ', $date, $sqlType = 2);
	$date = array('percent_value');
	$percent_value = get_table_date('merchants_percent', 'percent_id = \'' . $percent_id . '\'', $date, $sqlType = 2) . '%';
	$smarty->assign('percent_value', $percent_value);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$smarty->assign('user_id', $adminru['ru_id']);
	$page_count_arr = seller_page($order_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);

	if (file_exists(MOBILE_DRP)) {
		$smarty->assign('is_dir', 1);
	}
	else {
		$smarty->assign('is_dir', 0);
	}

	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('merchants_order_list.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}
else if ($_REQUEST['act'] == 'ajax_download') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('is_stop' => 0);
	$page = !empty($_REQUEST['page_down']) ? intval($_REQUEST['page_down']) : 0;
	$page_count = !empty($_REQUEST['page_count']) ? intval($_REQUEST['page_count']) : 0;
	$merchants_order_list = merchants_order_list($page);
	$admin_id = get_admin_id();
	$merchants_download_content = read_static_cache('merchants_download_content_' . $admin_id);
	$merchants_download_content[] = $merchants_order_list;
	write_static_cache('merchants_download_content_' . $admin_id, $merchants_download_content);
	$result['page'] = $page;

	if ($page < $page_count) {
		$result['is_stop'] = 1;
		$result['next_page'] = $page + 1;
	}

	exit($json->encode($result));
}

if ($_REQUEST['act'] == 'merchant_download') {
	$shop_name = get_shop_name($adminru['ru_id'], 1);
	header('Content-Disposition: attachment; filename=' . $shop_name . '-' . date('YmdHis') . '.zip');
	header('Content-Type: application/unknown');
	include_once 'includes/cls_phpzip.php';
	$admin_id = get_admin_id();
	$merchants_download_content = read_static_cache('merchants_download_content_' . $admin_id);
	$zip = new PHPZip();

	if (!empty($merchants_download_content)) {
		foreach ($merchants_download_content as $k => $merchants_order_list) {
			$k++;
			$content = order_download_list($merchants_order_list['orders']);
			$content .= ecs_iconv(EC_CHARSET, 'GB2312', '佣金总金额：');
			$content .= ecs_iconv(EC_CHARSET, 'GB2312', $merchants_order_list['orders']['brokerage_amount']['all_price']) . "\t\n";
			$content .= ecs_iconv(EC_CHARSET, 'GB2312', '已结算：');
			$content .= ecs_iconv(EC_CHARSET, 'GB2312', $merchants_order_list['orders']['brokerage_amount']['is_settlement_price']) . "\t\n";
			$content .= ecs_iconv(EC_CHARSET, 'GB2312', '未结算：');
			$content .= ecs_iconv(EC_CHARSET, 'GB2312', $merchants_order_list['orders']['brokerage_amount']['no_settlement_price']) . "\t\n";
			$zip->add_file($content, date('YmdHis') . '-' . $k . '.csv');
		}
	}

	$dir = ROOT_PATH . '/temp/static_caches/merchants_download_content_' . $admin_id . '.php';

	if (is_file($dir)) {
		@unlink($dir);
	}

	exit($zip->file());
}
else if ($_REQUEST['act'] == 'handle_log') {
	admin_priv('merchants_commission');
	$smarty->assign('ur_here', $_LANG['handle_log']);
	$user_id = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
	$smarty->assign('action_link', array('text' => $_LANG['brokerage_order_list'], 'href' => 'merchants_commission.php?act=order_list&id=' . $user_id, 'class' => 'icon-reply'));
	$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$gift_gard_log = get_gift_gard_log($id);
	$smarty->assign('full_page', 1);
	$smarty->assign('gift_gard_log', $gift_gard_log['pzd_list']);
	$smarty->assign('filter', $gift_gard_log['filter']);
	$smarty->assign('record_count', $gift_gard_log['record_count']);
	$smarty->assign('page_count', $gift_gard_log['page_count']);
	$smarty->assign('order_id', $id);
	$page_count_arr = seller_page($gift_gard_log, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->display('merchants_order_log.dwt');
}
else if ($_REQUEST['act'] == 'Ajax_handle_log') {
	$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$gift_gard_log = get_gift_gard_log($id);
	$smarty->assign('gift_gard_log', $gift_gard_log['pzd_list']);
	$smarty->assign('filter', $gift_gard_log['filter']);
	$smarty->assign('record_count', $gift_gard_log['record_count']);
	$smarty->assign('page_count', $gift_gard_log['page_count']);
	$page_count_arr = seller_page($gift_gard_log, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	make_json_result($smarty->fetch('merchants_order_log.dwt'), '', array('filter' => $gift_gard_log['filter'], 'page_count' => $gift_gard_log['page_count']));
}
else if ($_REQUEST['act'] == 'commission_bill') {
	admin_priv('merchants_commission');
	$tab_menu = array();
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['brokerage_amount_list'], 'href' => 'merchants_commission.php?act=list');
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['commission_setup'], 'href' => 'merchants_commission.php?act=edit&id=' . $adminru['ru_id']);
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['commission_bill'], 'href' => 'merchants_commission.php?act=commission_bill&id=' . $adminru['ru_id']);
	$smarty->assign('tab_menu', $tab_menu);
	$user_id = $adminru['ru_id'];
	$smarty->assign('user_id', $user_id);
	$smarty->assign('ur_here', $_LANG['commission_bill']);
	$smarty->assign('full_page', 1);
	$result = commission_bill_list();
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('full_page', 1);
	$smarty->assign('bill_list', $result['bill_list']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');

	if (file_exists(MOBILE_DRP)) {
		$smarty->assign('is_dir', 1);
	}
	else {
		$smarty->assign('is_dir', 0);
	}

	assign_query_info();
	$smarty->display('merchants_commission_bill.dwt');
}
else if ($_REQUEST['act'] == 'commission_bill_query') {
	check_authz_json('merchants_commission');
	$result = commission_bill_list();
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('bill_list', $result['bill_list']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$sort_flag = sort_flag($result['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('merchants_commission_bill.dwt'), '', array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}
else if ($_REQUEST['act'] == 'apply_for') {
	admin_priv('merchants_commission');
	$tab_menu = array();
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['brokerage_amount_list'], 'href' => 'merchants_commission.php?act=list');
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['commission_setup'], 'href' => 'merchants_commission.php?act=edit&id=' . $adminru['ru_id']);
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['commission_bill'], 'href' => 'merchants_commission.php?act=commission_bill&id=' . $adminru['ru_id']);
	$smarty->assign('tab_menu', $tab_menu);
	$gmtime = gmtime();
	$smarty->assign('ur_here', $_LANG['commission_bill']);
	$bill_id = isset($_REQUEST['bill_id']) && !empty($_REQUEST['bill_id']) ? intval($_REQUEST['bill_id']) : 0;
	$seller_id = $adminru['ru_id'];
	$smarty->assign('user_id', $seller_id);
	$smarty->assign('form_act', 'bill_update');
	$bill_detail = array('id' => $bill_id);
	$bill = get_bill_detail($bill_detail);

	if ($bill['seller_id'] != $seller_id) {
		$Loaction = 'merchants_commission.php?act=list';
		ecs_header('Location: ' . $Loaction . "\n");
		exit();
	}

	$chargeoff_time = $bill['chargeoff_time'] + 24 * 3600 * $bill['bill_freeze_day'];

	if ($gmtime < $chargeoff_time) {
		$Loaction = 'merchants_commission.php?act=commission_bill&id=' . $seller_id;
		ecs_header('Location: ' . $Loaction . "\n");
		exit();
	}

	$smarty->assign('bill', $bill);

	if ($bill['chargeoff_status'] == 0) {
		$link[0] = array('href' => 'merchants_commission.php?act=commission_bill&id=' . $seller_id, 'text' => $_LANG['commission_bill']);

		if ($gmtime < $bill['end_time']) {
			sys_msg($sys_msg = $_LANG['apply_for_failure_time'], 1, $link);
		}
		else {
			sys_msg($sys_msg = $_LANG['apply_for_failure'], 1, $link);
		}
	}

	assign_query_info();
	$smarty->display('merchants_bill_applyfor.dwt');
}
else if ($_REQUEST['act'] == 'bill_update') {
	admin_priv('merchants_commission');
	$bill_id = isset($_REQUEST['bill_id']) && !empty($_REQUEST['bill_id']) ? intval($_REQUEST['bill_id']) : 0;
	$seller_id = $adminru['ru_id'];
	$apply_note = isset($_REQUEST['apply_note']) && !empty($_REQUEST['apply_note']) ? addslashes($_REQUEST['apply_note']) : '';

	if (empty($bill_id)) {
		$Loaction = 'merchants_commission.php?act=list';
		ecs_header('Location: ' . $Loaction . "\n");
		exit();
	}

	$bill_detail = array('id' => $bill_id);
	$bill = get_bill_detail($bill_detail);

	if ($bill['bill_apply'] == 0) {
		$other = array('apply_note' => $apply_note, 'bill_apply' => 1, 'apply_time' => gmtime());
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_commission_bill'), $other, 'UPDATE', 'id = \'' . $bill_id . '\'');
		$sys_msg = $_LANG['apply_for_success'];
	}
	else {
		$sys_msg = $_LANG['apply_for_failure'];
	}

	$link[0] = array('href' => 'merchants_commission.php?act=commission_bill&id=' . $seller_id, 'text' => $_LANG['commission_bill']);
	sys_msg($sys_msg, 0, $link);
}
else if ($_REQUEST['act'] == 'bill_detail') {
	admin_priv('merchants_commission');
	$tab_menu = array();
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['brokerage_amount_list'], 'href' => 'merchants_commission.php?act=list');
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['commission_setup'], 'href' => 'merchants_commission.php?act=edit&id=' . $adminru['ru_id']);
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['commission_bill'], 'href' => 'merchants_commission.php?act=commission_bill&id=' . $adminru['ru_id']);
	$smarty->assign('tab_menu', $tab_menu);
	$user_id = $adminru['ru_id'];
	$smarty->assign('user_id', $user_id);
	$smarty->assign('ur_here', $_LANG['commission_bill_detail']);
	$smarty->assign('full_page', 1);
	$result = bill_detail_list();
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('full_page', 1);
	$smarty->assign('bill_list', $result['bill_list']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');
	$bill_detail = array('id' => $result['filter']['bill_id']);
	$bill = get_bill_detail($bill_detail);
	$smarty->assign('bill', $bill);

	if (file_exists(MOBILE_DRP)) {
		$smarty->assign('is_dir', 1);
	}
	else {
		$smarty->assign('is_dir', 0);
	}

	assign_query_info();
	$smarty->display('merchants_bill_detail.dwt');
}
else if ($_REQUEST['act'] == 'bill_detail_query') {
	check_authz_json('merchants_commission');
	$result = bill_detail_list();
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('bill_list', $result['bill_list']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$sort_flag = sort_flag($result['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('merchants_bill_detail.dwt'), '', array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}
else if ($_REQUEST['act'] == 'bill_goods') {
	admin_priv('merchants_commission');
	$tab_menu = array();
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['brokerage_amount_list'], 'href' => 'merchants_commission.php?act=list');
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['commission_setup'], 'href' => 'merchants_commission.php?act=edit&id=' . $adminru['ru_id']);
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['commission_bill'], 'href' => 'merchants_commission.php?act=commission_bill&id=' . $adminru['ru_id']);
	$smarty->assign('tab_menu', $tab_menu);
	$user_id = $adminru['ru_id'];
	$smarty->assign('user_id', $user_id);
	$smarty->assign('ur_here', $_LANG['commission_bill_detail']);
	$smarty->assign('full_page', 1);
	$result = bill_goods_list();
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('full_page', 1);
	$smarty->assign('goods_list', $result['goods_list']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');

	if (file_exists(MOBILE_DRP)) {
		$smarty->assign('is_dir', 1);
	}
	else {
		$smarty->assign('is_dir', 0);
	}

	assign_query_info();
	$smarty->display('merchants_bill_goods.dwt');
}
else if ($_REQUEST['act'] == 'bill_notake_order') {
	admin_priv('merchants_commission');
	$tab_menu = array();
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['brokerage_amount_list'], 'href' => 'merchants_commission.php?act=list');
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['commission_setup'], 'href' => 'merchants_commission.php?act=edit&id=' . $adminru['ru_id']);
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['commission_bill'], 'href' => 'merchants_commission.php?act=commission_bill&id=' . $adminru['ru_id']);
	$smarty->assign('tab_menu', $tab_menu);
	$user_id = $adminru['ru_id'];
	$smarty->assign('user_id', $user_id);
	$smarty->assign('ur_here', $_LANG['commission_bill_detail']);
	$smarty->assign('full_page', 1);
	$result = bill_notake_order_list();
	$smarty->assign('full_page', 1);
	$smarty->assign('order_list', $result['order_list']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');
	$bill_detail = array('id' => $result['filter']['bill_id']);
	$bill = get_bill_detail($bill_detail);
	$smarty->assign('bill', $bill);

	if (file_exists(MOBILE_DRP)) {
		$smarty->assign('is_dir', 1);
	}
	else {
		$smarty->assign('is_dir', 0);
	}

	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	assign_query_info();
	$smarty->display('merchants_bill_notake_order.dwt');
}
else if ($_REQUEST['act'] == 'bill_notake_order_query') {
	check_authz_json('merchants_commission');
	$result = bill_notake_order_list();
	$smarty->assign('order_list', $result['order_list']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$sort_flag = sort_flag($result['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('merchants_bill_notake_order.dwt'), '', array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}
else if ($_REQUEST['act'] == 'bill_goods_query') {
	check_authz_json('merchants_commission');
	$result = bill_goods_list();
	$page_count_arr = seller_page($result, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('goods_list', $result['goods_list']);
	$smarty->assign('filter', $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count', $result['page_count']);
	$sort_flag = sort_flag($result['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('merchants_bill_goods.dwt'), '', array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

?>
