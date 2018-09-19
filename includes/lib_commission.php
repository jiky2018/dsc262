<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_seller_commission_info($ru_id = 0)
{
	$sql = 'SELECT ms.server_id, ms.commission_model, ms.suppliers_percent, mp.percent_value FROM ' . $GLOBALS['ecs']->table('merchants_server') . ' AS ms ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_percent') . ' AS mp ON ms.suppliers_percent = mp.percent_id ' . ('WHERE ms.user_id = \'' . $ru_id . '\' LIMIT 1');
	return $GLOBALS['db']->getRow($sql);
}

function get_cache_seller_list()
{
	$sql = 'SELECT u.user_id AS seller_id, IFNULL(s.cycle, 0) AS cycle, p.percent_value, s.day_number, s.bill_time FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS u ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_server') . ' AS s ON u.user_id = s.user_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_percent') . ' AS p ON s.suppliers_percent = p.percent_id' . ' WHERE u.merchants_audit = 1 ORDER BY u.sort_order ASC';
	$seller_list = $GLOBALS['db']->getAll($sql);
	write_static_cache('seller_list', $seller_list, '/data/sc_file/');
	return $seller_list;
}

function get_bill_detail($select = array())
{
	$where = 1;
	if (isset($select['id']) && $select['id']) {
		$where .= ' AND id = \'' . $select['id'] . '\'';
	}
	else {
		if (isset($select['start_time'])) {
			$where .= ' AND start_time >= \'' . $select['start_time'] . '\'';
		}

		if (isset($select['end_time'])) {
			$where .= ' AND end_time <= \'' . $select['end_time'] . '\'';
		}
	}

	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('seller_commission_bill') . (' WHERE ' . $where . ' LIMIT 1');
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		$row['settle_accounts'] = 0;
		if ($row['chargeoff_status'] == 1 || $row['chargeoff_status'] == 3) {
			$order_id = get_bill_order_info($row['id'], $row['start_time'], $row['end_time']);
			$row['settle_accounts'] = get_order_take_brokerage($row['seller_id'], $order_id, $row['chargeoff_time']);
		}
		else if ($row['chargeoff_status'] == 2) {
			if (0 < $row['actual_amount']) {
				$row['settle_accounts'] = $row['should_amount'] - $row['actual_amount'];
			}
		}

		$row['format_order_amount'] = price_format($row['order_amount'], false);
		$row['format_return_fee'] = price_format($row['return_amount'], false);
		$row['format_shipping_fee'] = price_format($row['return_shippingfee'], false);
		$row['format_shipping_amount'] = price_format($row['shipping_amount'], false);
		$row['format_return_amount'] = price_format($row['return_amount'] + $row['return_shippingfee'], false);
		$row['gain_proportion'] = round(100 - $row['proportion'], 2);
		$row['should_proportion'] = $row['proportion'];
		$detail_other = array('seller_id' => $row['seller_id'], 'bill_id' => $row['id'], 'start_time' => $row['start_time'], 'end_time' => $row['end_time'], 'proportion' => $row['proportion'], 'chargeoff_time' => $row['chargeoff_time'], 'commission_model' => $row['commission_model'], 'order_status' => OS_RETURNED);
		$detail_list = bill_detail_list(1, $detail_other);
		$row['gain_commission'] = floatval($row['gain_commission']);
		$row['should_amount'] = floatval($row['should_amount']);

		if ($detail_list) {
			if (0 < $row['gain_commission']) {
				$row['gain_commission'] = number_format($row['gain_commission'] - $detail_list['all_gain_commission'], 2, '.', '');
			}

			if (0 < $row['should_amount']) {
				$row['should_amount'] = number_format($row['should_amount'] - $detail_list['all_should_amount'], 2, '.', '');
			}
		}

		$row['format_gain_commission'] = price_format($row['gain_commission'], false);
		$row['format_should_amount'] = price_format($row['should_amount'] - $row['settle_accounts'], false);
		$row['format_frozen_money'] = price_format($row['frozen_money'], false);
		$row['format_chargeoff_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['chargeoff_time']);
		$row['format_start_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['start_time']);
		$row['format_end_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['end_time']);
		$row['format_settleaccounts_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['settleaccounts_time']);
		$row['format_apply_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['apply_time']);
	}

	return $row;
}

function get_bill_order($select = array())
{
	$where = 1;

	if (isset($select['id'])) {
		$where .= ' AND id = \'' . $select['id'] . '\'';
	}

	if (isset($select['bill_id'])) {
		$where .= ' AND bill_id = \'' . $select['bill_id'] . '\'';
	}

	if (isset($select['user_id'])) {
		$where .= ' AND user_id = \'' . $select['user_id'] . '\'';
	}

	if (isset($select['seller_id'])) {
		$where .= ' AND seller_id = \'' . $select['seller_id'] . '\'';
	}

	if (isset($select['order_id'])) {
		$where .= ' AND order_id = \'' . $select['order_id'] . '\'';
	}

	if (isset($select['order_sn'])) {
		$where .= ' AND order_sn = \'' . $select['order_sn'] . '\'';
	}

	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('seller_bill_order') . (' WHERE ' . $where . ' LIMIT 1');
	$row = $GLOBALS['db']->getRow($sql);
	return $row;
}

function bill_detail_list($type = 0, $bill = array())
{
	$result = get_filter();

	if ($result === false) {
		if ($type == 1) {
			if (isset($bill['seller_id'])) {
				$filter['seller_id'] = $bill['seller_id'];
			}

			if (isset($bill['bill_id'])) {
				$filter['bill_id'] = $bill['bill_id'];
			}
		}
		else {
			$aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;
			$filter['bill_id'] = empty($_REQUEST['bill_id']) ? 0 : intval($_REQUEST['bill_id']);
			$filter['commission_model'] = empty($_REQUEST['commission_model']) ? 0 : intval($_REQUEST['commission_model']);
			$filter['seller_id'] = empty($_REQUEST['seller_id']) ? 0 : intval($_REQUEST['seller_id']);
			$filter['proportion'] = empty($_REQUEST['proportion']) ? 0 : floatval($_REQUEST['proportion']);
			$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'sbo.order_id' : trim($_REQUEST['sort_by']);
			$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
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
		}

		$where = 1;
		$where .= ' AND sbo.seller_id = \'' . $filter['seller_id'] . '\'';
		$proportion = 0;

		if ($filter['bill_id']) {
			if (!$bill) {
				$bill_detail = array('id' => $filter['bill_id']);
				$bill = get_bill_detail($bill_detail);
			}

			$where .= ' AND (sbo.confirm_take_time >= \'' . $bill['start_time'] . '\' AND sbo.confirm_take_time <= \'' . $bill['end_time'] . '\')';
			$proportion = $bill['proportion'];
		}

		$where .= ' AND IF((SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('seller_account_log') . ' AS sal WHERE sal.add_time < \'' . $bill['chargeoff_time'] . '\') > 0, (SELECT sal.order_id FROM ' . $GLOBALS['ecs']->table('seller_account_log') . ' AS sal WHERE sal.add_time < \'' . $bill['chargeoff_time'] . '\' LIMIT 1) <> sbo.order_id, 1) ';

		if ($type == 0) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('seller_bill_order') . ' AS sbo ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . 'AS o ON o.order_id = sbo.order_id ' . ('WHERE ' . $where);
			$filter['record_count'] = $GLOBALS['db']->getOne($sql);
			$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		}

		$select_where = ', IF((SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('seller_account_log') . ' AS sal WHERE sal.order_id = sbo.order_id) > 0, 1, 0) AS is_settlement';
		$limit = '';

		if ($type == 1) {
			if (isset($bill['order_status']) == OS_RETURNED) {
				$filter['order_status'] = $bill['order_status'];
				$limit .= ' AND o.order_status = \'' . $bill['order_status'] . '\'';
			}
		}
		else {
			$limit = ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ' . 'LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
		}

		$sql = 'SELECT sbo.*, (' . order_amount_field('sbo.') . ') AS total_fee, (' . order_activity_field_add('sbo.') . ') AS activity_fee, (' . order_commission_field('sbo.') . ') AS commission_total_fee,  ' . ('ms.commission_model, sbo.goods_amount ' . $select_where . ' FROM ') . $GLOBALS['ecs']->table('seller_bill_order') . ' AS sbo ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . 'AS o ON o.order_id = sbo.order_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_server') . ' AS ms ON sbo.seller_id = ms.user_id ' . 'WHERE IF(sbo.chargeoff_status > 0, sbo.bill_id = \'' . $filter['bill_id'] . ('\', ' . $where . ')') . $limit;

		foreach (array('order_sn') as $val) {
			$filter[$val] = stripslashes($filter[$val]);
		}

		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);
	$all_gain_commission = 0;
	$all_should_amount = 0;

	foreach ($row as $key => $value) {
		$return_amount = $value['return_amount'];
		$return_shippingfee = $value['return_shippingfee'];
		$value['bill_return_amount'] = $return_amount;
		$value['bill_return_shippingfee'] = $return_shippingfee;
		$value['return_amount'] = 0;
		$value['return_shippingfee'] = 0;
		$value['order_amount'] = $value['total_fee'] - $value['discount'];
		$order = array('goods_amount' => $value['goods_amount'], 'activity_fee' => $value['activity_fee']);
		$row[$key]['is_goods_rate'] = 0;
		$goods_rate = get_alone_goods_rate($value['order_id'], 0, $order);
		$row[$key]['goods_rate'] = $goods_rate;

		if ($goods_rate) {
			$value['commission_total_fee'] = $value['commission_total_fee'] - $goods_rate['total_fee'];

			if ($goods_rate['total_fee']) {
				if ($value['commission_total_fee'] <= 0) {
					$row[$key]['is_goods_rate'] = 1;
				}

				if ($value['commission_total_fee'] < 0) {
					$value['commission_total_fee'] = 0;
				}
			}
		}

		if ($bill['commission_model'] != -1) {
			$value['commission_model'] = $bill['commission_model'];
		}

		if ($value['commission_model']) {
			$cat_commission = get_cat_gain_should_amount($value);
			$format_gain_commission = $cat_commission['gain_commission'] + $goods_rate['gain_commission'];
			$format_gain_commission = number_format($format_gain_commission, 2, '.', '');
			$should_amount = $cat_commission['should_amount'];
			$gain_commission = $cat_commission['gain_commission'];
		}
		else {
			$commission = get_gain_should_amount($proportion, $value);
			$should_amount = $commission['should_amount'];
			$format_gain_commission = $commission['gain_commission'] + $goods_rate['gain_commission'];
			$format_gain_commission = number_format($format_gain_commission, 2, '.', '');
			$row[$key]['format_gain_commission'] = price_format($format_gain_commission, false);
			$row[$key]['format_drp_money'] = price_format($goods['drp_money'], false);
			$row[$key]['format_integral_money'] = price_format($value['integral_money'], false);
			$gain_commission = $commission['gain_commission'];
		}

		$should_amount = number_format($should_amount, 2, '.', '');
		$row[$key]['gain_commission'] = number_format($gain_commission, 2, '.', '');
		$row[$key]['should_amount'] = $should_amount;
		$format_should_amount = $should_amount + $goods_rate['should_amount'];
		$format_should_amount = number_format($format_should_amount, 2, '.', '');
		if ($type == 1 && $value['is_settlement'] == 1) {
			$all_gain_commission += $format_gain_commission;
			$all_should_amount += $format_should_amount;
		}

		if ($value['order_status'] == OS_RETURNED) {
			$format_gain_commission = 0;
			$format_should_amount = 0;
		}

		if (0 < $format_should_amount) {
			$format_should_amount -= $return_amount;
		}

		$row[$key]['format_gain_commission'] = price_format($format_gain_commission, false);
		$row[$key]['format_should_amount'] = price_format($format_should_amount, false);
		$row[$key]['format_drp_money'] = price_format($goods['drp_money'], false);
		$row[$key]['format_integral_money'] = price_format($value['integral_money'], false);
		$row[$key]['format_order_amount'] = price_format($value['order_amount'], false);
		$row[$key]['commission_total_fee'] = $value['commission_total_fee'];
		$row[$key]['format_commission_total_fee'] = price_format($value['commission_total_fee'], false);
		$row[$key]['format_shipping_fee'] = price_format($value['shipping_fee'], false);
		$row[$key]['format_return_amount'] = price_format($return_amount + $return_shippingfee, false);
		$row[$key]['gain_proportion'] = round(100 - $filter['proportion'], 2);
		$row[$key]['should_proportion'] = $filter['proportion'];

		if ($bill) {
			$sql = 'SELECT log_id FROM ' . $GLOBALS['ecs']->table('seller_account_log') . ' AS sal, ' . $GLOBALS['ecs']->table('seller_bill_order') . ' AS sbo ' . ' WHERE sal.is_paid = 1 AND sal.order_id = sbo.order_id' . ' AND sal.ru_id = \'' . $value['seller_id'] . '\'' . ' AND sal.order_id = \'' . $value['order_id'] . '\'' . ' AND sal.log_type = 2 AND add_time < \'' . $bill['chargeoff_time'] . '\'';

			if ($GLOBALS['db']->getOne($sql)) {
				$row[$key]['chargeoff_before'] = 1;
			}
			else {
				$row[$key]['chargeoff_before'] = 2;
			}
		}
		else {
			$row[$key]['chargeoff_before'] = 0;
		}

		if ($value['order_status'] == OS_RETURNED) {
			$row[$key]['gain_commission'] = price_format(0, false);
			$row[$key]['should_amount'] = price_format(0, false);
			$row[$key]['goods_rate']['gain_commission'] = price_format(0, false);
			$row[$key]['goods_rate']['should_amount'] = price_format(0, false);
		}
	}

	if ($type == 1) {
		$row['all_gain_commission'] = $all_gain_commission;
		$row['all_should_amount'] = $all_should_amount;
		$arr = $row;
	}
	else {
		$arr = array('bill_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	}

	return $arr;
}

function commission_bill_list($ajax_bill = 0)
{
	$adminru = get_admin_ru_id();
	$seller_path = is_admin_seller_path();
	$result = get_filter();

	if ($result === false) {
		$aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

		if ($seller_path == 2) {
			$filter['id'] = $adminru['ru_id'];
		}
		else {
			$filter['id'] = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'b.end_time' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['bill_sn'] = empty($_REQUEST['bill_sn']) ? '' : trim($_REQUEST['bill_sn']);
		$filter['start_time'] = empty($_REQUEST['start_time']) ? '' : trim($_REQUEST['start_time']);
		$filter['start_time'] = !empty($filter['start_time']) ? local_strtotime($filter['start_time']) : '';
		$filter['end_time'] = empty($_REQUEST['end_time']) ? '' : trim($_REQUEST['end_time']);
		$filter['end_time'] = !empty($filter['end_time']) ? local_strtotime($filter['end_time']) : '';
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

		$where = 1;

		if ($filter['id']) {
			$where .= ' AND b.seller_id = \'' . $filter['id'] . '\'';
		}

		if ($filter['bill_sn']) {
			$where .= ' AND b.bill_sn LIKE \'%' . $filter['bill_sn'] . '%\'';
		}

		if ($filter['start_time']) {
			$where .= ' AND b.start_time >= \'' . $filter['start_time'] . '\'';
		}

		if ($filter['end_time']) {
			$where .= ' AND b.end_time <= \'' . $filter['end_time'] . '\'';
		}

		$select_limit = '';

		if ($ajax_bill == 0) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('seller_commission_bill') . ' AS b ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_server') . ' AS s ON b.seller_id = s.user_id AND b.bill_cycle = s.cycle ' . ('WHERE ' . $where . ' ');
			$filter['record_count'] = $GLOBALS['db']->getOne($sql);
			$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
			$select_limit = 'ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
		}
		else {
			$where .= ' AND b.chargeoff_status = 0';
		}

		$sql = 'SELECT b.*, b.commission_model AS model, s.commission_model, s.bill_freeze_day FROM ' . $GLOBALS['ecs']->table('seller_commission_bill') . ' AS b ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_server') . ' AS s ON b.seller_id = s.user_id AND b.bill_cycle = s.cycle ' . (' WHERE ' . $where . ' ') . $select_limit;

		if ($ajax_bill == 0) {
			foreach (array('bill_sn') as $val) {
				$filter[$val] = stripslashes($filter[$val]);
			}

			set_filter($filter, $sql);
		}
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$gmtime = gmtime();
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$row[$key]['format_settleaccounts_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['settleaccounts_time']);
		$row[$key]['format_start_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['start_time']);
		$row[$key]['format_end_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['end_time']);
		$chargeoff_time = $value['chargeoff_time'] + 24 * 3600 * $value['bill_freeze_day'];

		if ($chargeoff_time < $gmtime) {
			$row[$key]['is_bill_freeze'] = 1;
		}
		else {
			$row[$key]['is_bill_freeze'] = 0;
		}

		$detail_list = array();

		if (empty($value['chargeoff_status'])) {
			$detail = get_bill_amount_detail($value['id'], $value['seller_id'], $value['proportion'], $value['start_time'], $value['end_time'], $value['chargeoff_status'], $value['commission_model']);
			if ($detail && $detail['notake_order_count'] == 0 && 1 <= $detail['take_order_count'] && $value['end_time'] < $gmtime) {
				$other['chargeoff_status'] = 1;
				$other['order_amount'] = $detail['order_amount'];
				$other['shipping_amount'] = $detail['shipping_amount'];
				$other['return_amount'] = $detail['return_amount'];
				$other['return_shippingfee'] = $detail['return_shippingfee'];
				$other['gain_commission'] = $detail['gain_commission'];
				$other['should_amount'] = $detail['should_amount'];
				$other['drp_money'] = $detail['drp_money'];
				$other['commission_model'] = $detail['commission_model'];
				$other['chargeoff_time'] = gmtime();
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_commission_bill'), $other, 'UPDATE', 'id = \'' . $value['id'] . '\'');
				$row[$key]['chargeoff_status'] = $other['chargeoff_status'];
				$bill_order_other['bill_id'] = $value['id'];
				$bill_order_other['chargeoff_status'] = $other['chargeoff_status'];
				$where_bill_order = order_query_sql('confirm_take') . ' AND confirm_take_time >= \'' . $value['start_time'] . '\' AND confirm_take_time <= \'' . $value['end_time'] . '\' AND seller_id = \'' . $value['seller_id'] . '\' AND chargeoff_status <> 2';
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_bill_order'), $bill_order_other, 'UPDATE', '1 ' . $where_bill_order);
				$sql = 'SELECT GROUP_CONCAT(order_id) AS order_list FROM ' . $GLOBALS['ecs']->table('seller_bill_order') . ' WHERE 1 ' . $where_bill_order;
				$res = $GLOBALS['db']->getRow($sql);

				if ($res) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET chargeoff_status = \'' . $other['chargeoff_status'] . '\' WHERE order_id ' . db_create_in($res['order_list']);
					$GLOBALS['db']->query($sql);
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_return') . ' SET chargeoff_status = \'' . $other['chargeoff_status'] . '\' WHERE order_id ' . db_create_in($res['order_list']);
					$GLOBALS['db']->query($sql);
				}

				$value['chargeoff_time'] = $other['chargeoff_time'];
			}

			$value['order_amount'] = $detail['order_amount'];
			$value['shipping_amount'] = $detail['shipping_amount'];
			$value['return_amount'] = $detail['return_amount'] + $detail['return_shippingfee'];
			$value['gain_commission'] = $detail['gain_commission'];
			$value['should_amount'] = $detail['should_amount'];
			$value['drp_money'] = $detail['drp_money'];
			$row[$key]['notake_order_count'] = $detail['notake_order_count'];
		}
		else {
			if ($value['chargeoff_status'] == 1) {
				$where_order = ' AND ' . db_create_in(array(OS_UNCONFIRMED), 'o.shipping_status') . ' AND ' . db_create_in(array(PS_UNPAYED), 'o.pay_status') . ' AND ' . db_create_in(array(OS_RETURNED), 'o.order_status');
				$sql = 'SELECT SUM(sbo.return_amount) AS return_amount, SUM(sbo.return_shippingfee) AS return_shippingfee FROM ' . $GLOBALS['ecs']->table('seller_bill_order') . ' AS sbo ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS o ON sbo.order_id = o.order_id' . ' WHERE sbo.bill_id = \'' . $value['id'] . '\'' . $where_order . ' LIMIT 1';
				$return_info = $GLOBALS['db']->getRow($sql);
				if ($return_info && (!empty($return_info['return_amount']) || !empty($return_info['return_shippingfee']))) {
					$value['return_amount'] = empty($return_info['return_amount']) ? $return_info['return_amount'] : 0;
					$value['return_shippingfee'] = empty($return_info['return_shippingfee']) ? $return_info['return_shippingfee'] : 0;
					if ($value['return_amount'] && empty($value['return_shippingfee'])) {
						$set_return = 'return_amount = \'' . $return_info['return_amount'] . '\'';
					}
					else {
						if ($value['return_shippingfee'] && empty($value['return_amount'])) {
							$set_return = 'return_shippingfee = \'' . $return_info['return_shippingfee'] . '\'';
						}
						else {
							if ($value['return_amount'] && $value['return_shippingfee']) {
								$set_return = 'return_amount = \'' . $return_info['return_amount'] . '\', return_shippingfee = \'' . $return_info['return_shippingfee'] . '\'';
							}
						}
					}

					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('seller_commission_bill') . (' SET ' . $set_return . ' WHERE id = \'') . $value['id'] . '\'';
					$GLOBALS['db']->query($sql);
				}

				$detail_other = array('seller_id' => $value['seller_id'], 'bill_id' => $value['id'], 'start_time' => $value['start_time'], 'end_time' => $value['end_time'], 'proportion' => $value['proportion'], 'chargeoff_time' => $value['chargeoff_time'], 'commission_model' => $value['commission_model'], 'order_status' => OS_RETURNED);
				$detail_list = bill_detail_list(1, $detail_other);
			}

			$row[$key]['notake_order_count'] = 0;
			$value['return_amount'] = $value['return_amount'] + $value['return_shippingfee'];
		}

		$value['gain_commission'] = floatval($value['gain_commission']);
		$value['should_amount'] = floatval($value['should_amount']);

		if ($detail_list) {
			if (0 < $value['gain_commission']) {
				$value['gain_commission'] = number_format($value['gain_commission'] - $detail_list['all_gain_commission'], 2, '.', '');
			}

			if (0 < $value['should_amount']) {
				$value['should_amount'] = number_format($value['should_amount'] - $detail_list['all_should_amount'], 2, '.', '');
			}
		}

		$row[$key]['gain_proportion'] = round(100 - $value['proportion'], 2);
		$row[$key]['should_proportion'] = $value['proportion'];
		$row[$key]['settle_accounts'] = 0;
		if ($value['chargeoff_status'] == 1 || $value['chargeoff_status'] == 3) {
			$order_id = get_bill_order_info($value['id'], $value['start_time'], $value['end_time']);
			$settle_accounts = get_order_take_brokerage($value['seller_id'], $order_id, $value['chargeoff_time']);
			if (0 < $settle_accounts && $settle_accounts <= $value['should_amount']) {
				$value['should_amount'] = $value['should_amount'] - $settle_accounts;
			}

			$row[$key]['settle_accounts'] = $settle_accounts;
		}
		else if ($value['chargeoff_status'] == 2) {
			if (0 < $value['actual_amount'] && $value['actual_amount'] < $value['should_amount']) {
				$settle_accounts = $value['should_amount'] - $value['actual_amount'];
				$value['should_amount'] = $value['should_amount'] - $settle_accounts;
				$row[$key]['settle_accounts'] = $settle_accounts;
			}
		}

		$row[$key]['format_order_amount'] = price_format($value['order_amount'], false);
		$row[$key]['format_shipping_amount'] = price_format($value['shipping_amount'], false);
		$row[$key]['format_return_amount'] = price_format($value['return_amount'], false);
		$row[$key]['format_gain_commission'] = price_format($value['gain_commission'], false);
		$row[$key]['format_should_amount'] = price_format($value['should_amount'], false);
		$row[$key]['format_drp_money'] = price_format($value['drp_money'], false);
		$row[$key]['format_frozen_money'] = price_format($value['frozen_money'], false);
		$row[$key]['format_chargeoff_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['chargeoff_time']);
		$filter['commission_model'] = $value['commission_model'];
	}

	if ($ajax_bill == 0) {
		$arr = array('bill_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
		return $arr;
	}
	else {
		return $row;
	}
}

function get_bill_amount_detail($bill_id = 0, $seller_id = 0, $proportion = 100, $start_time = 0, $end_time = 0, $chargeoff_status = 0, $commission_model = 0)
{
	$where = 'sbo.seller_id = \'' . $seller_id . '\'';
	$where .= order_query_sql('confirm_take', 'sbo.');
	$where .= ' AND sbo.confirm_take_time >= \'' . $start_time . '\' AND sbo.confirm_take_time <= \'' . $end_time . '\'';

	if ($chargeoff_status <= 1) {
		$where .= ' AND sbo.chargeoff_status ' . db_create_in(array(0, 1));
	}
	else {
		$where .= ' AND sbo.chargeoff_status = \'' . $chargeoff_status . '\'';
	}

	$where .= ' AND o.is_settlement = 0';
	$sql = 'SELECT GROUP_CONCAT(sbo.order_id) AS order_list, SUM((' . order_amount_field('sbo.') . ')) AS total_fee, ' . 'SUM((' . order_commission_field('sbo.') . ')) AS commission_total_fee, ' . 'SUM(sbo.return_amount) AS return_amount, ' . 'SUM(sbo.shipping_fee) AS shipping_fee, ' . 'SUM(sbo.return_shippingfee) AS return_shippingfee, ' . 'SUM(sbo.goods_amount) AS goods_amount, ' . 'SUM(sbo.discount) AS discount, ' . 'SUM(sbo.coupons) AS coupons, ' . 'SUM(sbo.integral_money) AS integral_money, ' . 'SUM(sbo.bonus) AS bonus, ' . 'SUM(sbo.value_card) AS value_card, ' . '(SELECT SUM(sbg.drp_money) FROM ' . $GLOBALS['ecs']->table('seller_bill_goods') . ' AS sbg WHERE sbg.order_id = sbo.order_id) AS drp_money ' . 'FROM ' . $GLOBALS['ecs']->table('seller_bill_order') . ' AS sbo ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . 'AS o ON o.order_id = sbo.order_id ' . (' WHERE ' . $where);
	$order = $GLOBALS['db']->getRow($sql);
	$order['bill_id'] = $bill_id;
	$order['return_amount'] = isset($order['return_amount']) ? $order['return_amount'] : 0;
	$order['return_shippingfee'] = isset($order['return_shippingfee']) ? $order['return_shippingfee'] : 0;
	$order['integral_money'] = isset($order['integral_money']) ? $order['integral_money'] : 0;
	$order['order_amount'] = isset($order['total_fee']) ? $order['total_fee'] - $order['discount'] : 0;
	$order['shipping_amount'] = isset($order['shipping_fee']) ? $order['shipping_fee'] : 0;
	$order['drp_money'] = isset($order['drp_money']) ? $order['drp_money'] : 0;
	$order['commission_model'] = $commission_model;
	$bill_order = get_bill_order_amount($seller_id, $start_time, $end_time, $commission_model, $proportion);
	$order['gain_commission'] = $bill_order['gain_commission'];
	$order['should_amount'] = $bill_order['should_amount'];
	$where_order = ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 WHERE oi2.main_order_id = o.order_id) = 0';
	$where_order .= ' AND (SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' as og WHERE o.order_id = og.order_id LIMIT 1) = \'' . $seller_id . '\'');
	$where_order .= ' AND IF(o.confirm_take_time = 0, oa.log_time >= \'' . $start_time . '\' AND oa.log_time <= \'' . $end_time . '\', o.confirm_take_time >= \'' . $start_time . '\' AND o.confirm_take_time <= \'' . $end_time . '\')';
	$where_wait_order = ' AND IF(o.confirm_take_time = 0, ';
	$where_wait_order .= db_create_in(array(OS_CONFIRMED, OS_SPLITED), 'oa.order_status') . ' AND oa.shipping_status ' . db_create_in(array(SS_SHIPPED)) . ' AND oa.pay_status ' . db_create_in(array(PS_PAYED)) . ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_action') . ' AS oa2 WHERE oa2.shipping_status = 2) = 0';
	$where_wait_order .= ', ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED), 'o.order_status') . ' AND ' . db_create_in(array(SS_RECEIVED), 'o.shipping_status', 'NOT') . ' AND IF(o.shipping_status = ' . SS_RECEIVED . ', ' . db_create_in(array(PS_PAYED), 'o.pay_status', 'NOT') . ', ' . db_create_in(array(PS_PAYED), 'o.pay_status') . ')' . ' AND ' . db_create_in(array(OS_RETURNED), 'o.order_status', 'NOT') . ')';
	$where_wait_order .= $where_order;
	$sql = 'SELECT o.order_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_action') . ' AS oa ON o.order_id = oa.order_id ' . 'WHERE 1 ' . $where_wait_order . ' AND o.is_settlement = 0 GROUP BY o.order_id';
	$order['notake_order_count'] = count($GLOBALS['db']->getAll($sql));
	$where_take_order = order_query_sql('confirm_take', 'oa.');
	$where_take_order .= $where_order;
	$sql = 'SELECT o.order_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . 'LEFT JOIN' . $GLOBALS['ecs']->table('order_action') . ' AS oa ON o.order_id = oa.order_id ' . 'WHERE 1 ' . $where_take_order . ' AND o.is_settlement = 0 GROUP BY o.order_id';
	$order['take_order_count'] = count($GLOBALS['db']->getAll($sql));
	$order['start_time_format'] = local_date($GLOBALS['_CFG']['time_format'], $start_time);
	$order['end_time_format'] = local_date($GLOBALS['_CFG']['time_format'], $end_time);
	return $order;
}

function get_goods_cat_commission($order_id = 0)
{
	$sql = 'SELECT SUM(goods_price * goods_number * proportion) AS commission, SUM(drp_money) AS drp_money, SUM(goods_price * goods_number) AS goods_amount FROM ' . $GLOBALS['ecs']->table('seller_bill_goods') . (' WHERE order_id = \'' . $order_id . '\' AND commission_rate = 0');
	$row = $GLOBALS['db']->getRow($sql);

	if (file_exists(MOBILE_DRP)) {
		$row[$key]['commission'] = $val['commission'] - $val['drp_money'];
	}

	return $row;
}

function bill_goods_list()
{
	$result = get_filter();

	if ($result === false) {
		$aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;
		$filter['order_id'] = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
		$filter['type'] = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
		$filter['commission_model'] = empty($_REQUEST['commission_model']) ? 0 : intval($_REQUEST['commission_model']);
		$filter['seller_id'] = empty($_REQUEST['seller_id']) ? 0 : intval($_REQUEST['seller_id']);
		$filter['proportion'] = empty($_REQUEST['proportion']) ? 0 : intval($_REQUEST['proportion']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'rec_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
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

		$where = 1;

		if ($filter['order_id']) {
			$where .= ' AND order_id = \'' . $filter['order_id'] . '\'';
		}

		if ($filter['type'] == 1) {
			$table = 'order_goods';
		}
		else {
			$table = 'seller_bill_goods';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table($table) . ' AS sbo ' . ('WHERE ' . $where . ' ');
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);

		foreach (array('order_sn') as $val) {
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
		$goods = get_admin_goods_info($value['goods_id'], array('goods_name', 'cat_id'));

		if ($filter['type'] == 1) {
			$value['cat_id'] = $goods['cat_id'];
		}

		$cat = get_cat_info($value['cat_id'], array('cat_name'));
		$row[$key]['format_goods_price'] = price_format($value['goods_price'], false);
		$row[$key]['format_drp_money'] = price_format($value['drp_money'], false);
		$row[$key]['proportion'] = $value['proportion'] * 100;
		$row[$key]['cat_name'] = $cat['cat_name'];
		$row[$key]['goods_name'] = $goods['goods_name'];
		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('return_goods') . ' AS rg, ' . $GLOBALS['ecs']->table('order_return') . ' AS o ' . ' WHERE rg.ret_id = o.ret_id AND rg.goods_id = \'' . $value['goods_id'] . '\' ' . ' AND o.refound_status = 1 ' . ' AND o.order_id = \'' . $value['order_id'] . '\'';

		if ($GLOBALS['db']->getOne($sql)) {
			$row[$key]['is_return'] = 1;
		}
		else {
			$row[$key]['is_return'] = 0;
		}

		if (0 < $value['commission_rate']) {
			$row[$key]['commission_rate'] = $value['commission_rate'] * 100;
		}
	}

	$arr = array('goods_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function bill_notake_order_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['bill_id'] = empty($_REQUEST['bill_id']) ? 0 : intval($_REQUEST['bill_id']);
		$filter['seller_id'] = empty($_REQUEST['seller_id']) ? 0 : intval($_REQUEST['seller_id']);
		$filter['commission_model'] = empty($_REQUEST['commission_model']) ? 0 : intval($_REQUEST['commission_model']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'o.order_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
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

		$proportion = 0;
		$chargeoff_status = 0;
		$settleaccounts_time = '';

		if ($filter['bill_id']) {
			$bill_detail = array('id' => $filter['bill_id']);
			$bill = get_bill_detail($bill_detail);
			$start_time = $bill['start_time'];
			$end_time = $bill['end_time'];
			$proportion = $bill['proportion'];
			$chargeoff_status = $bill['chargeoff_status'];
			$settleaccounts_time = $bill['format_settleaccounts_time'];
		}

		$where = '';

		if ($filter['keywords']) {
			$where .= ' AND o.order_sn LIKE \'%' . $filter['keywords'] . '%\'';
		}

		$where_order = ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 WHERE oi2.main_order_id = o.order_id) = 0';
		$where_order .= ' AND (SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og WHERE o.order_id = og.order_id LIMIT 1) = \'' . $filter['seller_id'] . '\'';
		$where_order .= ' AND IF(o.confirm_take_time = 0, oa.log_time >= \'' . $start_time . '\' AND oa.log_time <= \'' . $end_time . '\', o.confirm_take_time >= \'' . $start_time . '\' AND o.confirm_take_time <= \'' . $end_time . '\')';
		$where_wait_order = ' AND IF(o.confirm_take_time = 0, ';
		$where_wait_order .= db_create_in(array(OS_CONFIRMED, OS_SPLITED), 'oa.order_status') . ' AND oa.shipping_status ' . db_create_in(array(SS_SHIPPED)) . ' AND oa.pay_status ' . db_create_in(array(PS_PAYED)) . ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_action') . ' AS oa2 WHERE oa2.shipping_status = 2) = 0';
		$where_wait_order .= ', ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED), 'o.order_status') . ' AND ' . db_create_in(array(SS_RECEIVED), 'o.shipping_status', 'NOT') . ' AND IF(o.shipping_status = 2, ' . db_create_in(array(PS_PAYED), 'o.pay_status', 'NOT') . ', ' . db_create_in(array(PS_PAYED), 'o.pay_status') . ')' . ' AND ' . db_create_in(array(OS_RETURNED), 'o.order_status', 'NOT') . ')';
		$where_wait_order .= $where_order;
		$sql = 'SELECT o.order_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . 'LEFT JOIN' . $GLOBALS['ecs']->table('order_action') . ' AS oa ON o.order_id = oa.order_id ' . 'WHERE 1 ' . $where_wait_order . $where . ' GROUP BY o.order_id';
		$filter['record_count'] = count($GLOBALS['db']->getAll($sql));
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT o.*, (' . order_amount_field('o.') . ') AS total_fee, ' . '(' . order_commission_field('o.') . ') AS commission_total_fee, ' . '(' . order_activity_field_add('sbo.') . ') AS activity_fee, ' . 'sbo.goods_amount FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . 'LEFT JOIN' . $GLOBALS['ecs']->table('order_action') . ' AS oa ON o.order_id = oa.order_id ' . 'LEFT JOIN' . $GLOBALS['ecs']->table('seller_bill_order') . ' AS sbo ON o.order_id = sbo.order_id ' . 'WHERE 1 ' . $where_wait_order . $where . (' GROUP BY o.order_id ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . 'LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);

		foreach (array('order_sn') as $val) {
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
		$row[$key]['chargeoff_status'] = $chargeoff_status;
		$row[$key]['format_settleaccounts_time'] = $settleaccounts_time;
		$order_id = $value['order_id'];
		$value['drp_money'] = $GLOBALS['db']->getOne('SELECT SUM(drp_money) AS drp_money FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\''), true);
		$row[$key]['format_drp_money'] = price_format($value['drp_money'], false);
		$row[$key]['format_integral_money'] = price_format($value['integral_money'], false);
		$row[$key]['format_total_fee'] = price_format($value['total_fee'], false);
		$row[$key]['format_shipping_fee'] = price_format($value['shipping_fee'], false);
		$return_amount = get_order_return_amount($order_id);
		$row[$key]['format_return_amount'] = price_format($return_amount, false);
		$order = array('goods_amount' => $value['goods_amount'], 'activity_fee' => $value['activity_fee']);
		$goods_rate = get_alone_goods_rate($value['order_id'], 0, $order);
		$row[$key]['goods_rate'] = $goods_rate;

		if ($goods_rate) {
			$value['commission_total_fee'] = $value['commission_total_fee'] - $goods_rate['total_fee'];

			if ($goods_rate['total_fee']) {
				if ($value['commission_total_fee'] <= 0) {
					$row[$key]['is_goods_rate'] = 1;
				}

				if ($value['commission_total_fee'] < 0) {
					$value['commission_total_fee'] = 0;
				}
			}
		}

		if ($filter['commission_model']) {
			$cat_commission = get_cat_gain_should_amount($value);
			$value['gain_commission'] = $cat_commission['gain_commission'];
			$value['should_amount'] = $cat_commission['should_amount'];
		}
		else {
			$commission = get_gain_should_amount($proportion, $value);
			$value['gain_commission'] = $commission['gain_commission'];
			$value['should_amount'] = $commission['should_amount'];
			$row[$key]['gain_proportion'] = round(100 - $proportion, 2);
			$row[$key]['should_proportion'] = $proportion;
		}

		$row[$key]['format_gain_commission'] = price_format($value['gain_commission'], false);
		$row[$key]['format_should_amount'] = price_format($value['should_amount'], false);
	}

	$arr = array('order_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_gain_should_amount($proportion, $order)
{
	$gain_proportion = round(100 - $proportion, 2);
	$should_proportion = $proportion;
	$arr = array();
	$order['return_amount'] = $order['bill_return_amount'] + $order['bill_return_shippingfee'];

	if ($order['return_amount'] == $order['total_fee']) {
		$arr['gain_commission'] = 0;
		$arr['should_amount'] = 0;
	}
	else {
		$gain_commission = ($order['commission_total_fee'] - $order['drp_money'] - $order['return_amount']) * ($gain_proportion / 100);
		$gain_commission = number_format($gain_commission, 2, '.', '');
		$arr['gain_commission'] = $gain_commission;
		$arr['should_amount'] = $order['commission_total_fee'] - $gain_commission - $order['return_amount'] + $order['shipping_fee'];
	}

	return $arr;
}

function get_cat_gain_should_amount($value = array())
{
	if ($value['goods_amount'] <= 0) {
		$value['goods_amount'] = 1;
	}

	$value['return_amount'] = $value['bill_return_amount'] + $value['bill_return_shippingfee'];

	if ($value['total_fee'] == $value['return_amount']) {
		$gain_commission = 0;
		$should_amount = 0;
	}
	else {
		$goods = get_goods_cat_commission($value['order_id']);

		if ($goods['commission']) {
			$goods_commission = $goods['commission'] * ($value['commission_total_fee'] - $value['return_amount']) / $goods['goods_amount'];
			$should_amount = $goods_commission + $value['shipping_fee'];
			$gain_commission = $value['commission_total_fee'] - $goods_commission - $value['drp_money'] - $value['return_amount'];
		}
		else {
			$should_amount = $value['commission_total_fee'] - $value['return_amount'] + $value['shipping_fee'];
			$gain_commission = 0;
		}
	}

	$arr = array('gain_commission' => $gain_commission, 'should_amount' => $should_amount);
	return $arr;
}

function get_bill_order_amount($seller_id, $start_time, $end_time, $commission_model, $proportion)
{
	$where = '';
	$where .= ' AND sbo.seller_id = \'' . $seller_id . '\'';
	$where .= order_query_sql('confirm_take', 'sbo.');
	$where .= ' AND sbo.confirm_take_time >= \'' . $start_time . '\' AND sbo.confirm_take_time <= \'' . $end_time . '\'';

	if ($chargeoff_status <= 1) {
		$where .= ' AND sbo.chargeoff_status ' . db_create_in(array(0, 1));
	}
	else {
		$where .= ' AND sbo.chargeoff_status = \'' . $chargeoff_status . '\'';
	}

	$sql = 'SELECT sbo.*, sbo.goods_amount, ' . '(' . order_amount_field('sbo.') . ') AS total_fee, (' . order_commission_field('sbo.') . ') AS commission_total_fee, ' . '(SELECT SUM(sbg.drp_money) FROM ' . $GLOBALS['ecs']->table('seller_bill_goods') . ' AS sbg WHERE sbg.order_id = sbo.order_id) AS drp_money, ' . '(' . order_activity_field_add('sbo.') . ') AS activity_fee ' . 'FROM ' . $GLOBALS['ecs']->table('seller_bill_order') . ' AS sbo ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . 'AS o ON o.order_id = sbo.order_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_server') . ' AS ms ON sbo.seller_id = ms.user_id ' . ' WHERE 1 ' . $where . ' AND o.is_settlement = 0 ORDER BY sbo.order_id DESC';
	$order_list = $GLOBALS['db']->getAll($sql);
	$arr['gain_commission'] = 0;
	$arr['should_amount'] = 0;

	foreach ($order_list as $key => $value) {
		$order = array('goods_amount' => $value['goods_amount'], 'activity_fee' => $value['activity_fee']);
		$goods_rate = get_alone_goods_rate($value['order_id'], 0, $order);

		if ($goods_rate) {
			$value['commission_total_fee'] = $value['commission_total_fee'] - $goods_rate['total_fee'];

			if ($goods_rate['total_fee']) {
				if ($value['commission_total_fee'] < 0) {
					$value['commission_total_fee'] = 0;
				}
			}
		}

		if ($commission_model) {
			$cat_commission = get_cat_gain_should_amount($value);
			$gain_commission = $cat_commission['gain_commission'];
			$should_amount = $cat_commission['should_amount'];
			$arr['gain_commission'] += $gain_commission + $goods_rate['gain_commission'];
			$arr['should_amount'] += $should_amount + $goods_rate['should_amount'];
		}
		else {
			$commission = get_gain_should_amount($proportion, $value);
			$arr['gain_commission'] += $commission['gain_commission'] + $goods_rate['gain_commission'];
			$arr['should_amount'] += $commission['should_amount'] + $goods_rate['should_amount'];
		}
	}

	return $arr;
}

function get_order_return_list($order_id, $type = 0)
{
	$sql = 'SELECT SUM(actual_return) AS actual_return FROM ' . $GLOBALS['ecs']->table('order_return') . (' WHERE order_id = \'' . $order_id . '\' AND return_type IN(1, 3)');
	$actual_return = $GLOBALS['db']->getOne($sql);
	return $actual_return;
}

function merchants_is_settlement($ru_id = 0, $state = '', $filter = array())
{
	$where = 'WHERE 1';

	if ($filter) {
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

		if (isset($filter['state']) && -1 < $filter['state'] && !empty($filter['state'])) {
			$where .= ' AND is_settlement = \'' . $filter['state'] . '\' ';
		}

		if (!empty($filter['start_time'])) {
			$where .= ' AND o.add_time >= \'' . $filter['start_time'] . '\' ';
		}

		if (!empty($filter['end_time'])) {
			$where .= ' AND o.add_time <= \'' . $filter['end_time'] . '\' ';
		}
	}

	if (is_numeric($state)) {
		$where .= ' AND o.is_settlement = \'' . $state . '\' ';
	}

	$where .= order_query_sql('confirm_take', 'o.');
	$where .= ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi2 WHERE oi2.main_order_id = o.order_id) = 0 ';
	$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' AS og WHERE o.order_id = og.order_id LIMIT 1) = \'' . $ru_id . '\' ');
	$sql = 'SELECT o.order_id, o.main_order_id, o.order_sn, o.add_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid,' . 'o.shipping_time, o.auto_delivery_time, o.pay_status, o.consignee, o.address, o.email, o.tel, o.extension_code, o.extension_id, ' . 'o.is_delete, o.is_settlement, o.goods_amount, o.shipping_fee, (' . order_commission_field('o.') . ') AS total_fee, ' . '(' . order_activity_field_add('o.') . ') AS activity_fee ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $where;
	$row = $GLOBALS['db']->getAll($sql);
	$count = count($row);

	for ($i = 0; $i < $count; $i++) {
		$sql = 'SELECT bill_id FROM ' . $GLOBALS['ecs']->table('seller_bill_order') . ' WHERE order_id = \'' . $row[$i]['order_id'] . '\'';
		$bill_id = $GLOBALS['db']->getOne($sql, true);

		if ($bill_id) {
			$bill_detail = array('id' => $bill_id);
			$bill = get_bill_detail($bill_detail);
			$commission_info = array('commission_model' => $bill['commission_model'], 'percent_value' => $bill['proportion']);
		}
		else {
			$commission_info = get_seller_commission_info($ru_id);
		}

		$percent_value = !empty($commission_info) && !empty($commission_info['percent_value']) ? $commission_info['percent_value'] / 100 : 1;
		$row[$i]['formated_order_amount'] = price_format($row[$i]['order_amount'], true);
		$row[$i]['formated_money_paid'] = price_format($row[$i]['money_paid'], true);
		$row[$i]['formated_total_fee'] = price_format($row[$i]['total_fee'], true);
		$row[$i]['short_order_time'] = local_date($GLOBALS['_CFG']['time_format'], $row[$i]['add_time']);
		$row[$i]['return_amount'] = get_order_return_list($row[$i]['order_id']);
		$row[$i]['formated_return_amount'] = price_format($row[$i]['return_amount'], true);
		$row[$i]['formated_brokerage_amount'] = price_format(($row[$i]['total_fee'] - $row[$i]['return_amount']) * $percent_value, true);
		$row[$i]['formated_effective_amount'] = price_format($row[$i]['total_fee'] - $row[$i]['return_amount'], true);
		$order = array('goods_amount' => $row[$i]['goods_amount'], 'activity_fee' => $row[$i]['activity_fee']);
		$goods_rate = get_alone_goods_rate($row[$i]['order_id'], 0, $order);

		if (file_exists(MOBILE_DRP)) {
			$brokerage_amount = get_order_drp_money($row[$i]['total_fee'], $ru_id, $row[$i]['order_id']);

			if ($goods_rate) {
				$brokerage_amount['total_fee'] = $brokerage_amount['total_fee'] - $goods_rate['total_fee'];

				if ($goods_rate['total_fee']) {
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

			$row[$i]['formated_brokerage_amount'] = price_format($total_return_amount * $percent_value, true);
			$row[$i]['formated_effective_amount'] = price_format($total_return_amount, true);

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

				$row['all_brokerage_amount'] += $order_commission + $row[$i]['shipping_fee'];
			}
			else {
				$row['all_brokerage_amount'] += $total_return_amount * $percent_value + $row[$i]['shipping_fee'] + $brokerage_amount['should_amount'];
			}

			$row['all_drp'] += $brokerage_amount['drp_money'];
		}
		else {
			if ($goods_rate) {
				$row[$i]['total_fee'] = $row[$i]['total_fee'] - $goods_rate['total_fee'];

				if ($goods_rate['total_fee']) {
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

			$row[$i]['formated_brokerage_amount'] = price_format($total_return_amount * $percent_value, true);
			$row[$i]['formated_effective_amount'] = price_format($total_return_amount, true);

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

				$row['all_brokerage_amount'] += $order_commission + $row[$i]['shipping_fee'];
			}
			else {
				$row['all_brokerage_amount'] += $total_return_amount * $percent_value + $row[$i]['shipping_fee'] + $goods_rate['should_amount'];
			}
		}
	}

	if (file_exists(MOBILE_DRP)) {
		$row['all_price'] = number_format($row['all_brokerage_amount'], 2, '.', '');
		$row['all_drp_price'] = number_format($row['all_drp'], 2, '.', '');
		$row['all'] = price_format($row['all_brokerage_amount'], true);
		$row['all_drp'] = price_format($row['all_drp'], true);
		return $row;
	}
	else {
		return number_format($row['all_brokerage_amount'], 2, '.', '');
	}
}

function get_gift_gard_log($id = 0)
{
	$result = get_filter();

	if ($result === false) {
		if (0 < $id) {
			$filter['id'] = $id;
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('gift_gard_log') . ' WHERE gift_gard_id = \'' . $filter['id'] . '\'  AND handle_type=\'toggle_on_settlement\'';
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT a.id,a.addtime,b.user_name,a.delivery_status,a.gift_gard_id FROM' . $GLOBALS['ecs']->table('gift_gard_log') . ' AS a LEFT JOIN ' . $GLOBALS['ecs']->table('admin_user') . ' AS b ON a.admin_id = b.user_id WHERE a.gift_gard_id = \'' . $filter['id'] . '\' AND a.handle_type=\'toggle_on_settlement\'  ORDER BY a.addtime DESC LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $k => $v) {
		if (0 < $v['addtime']) {
			$row[$k]['add_time'] = local_date('Y-m-d  H:i:s', $v['addtime']);
		}

		if ($v['delivery_status'] == 0) {
			$row[$k]['delivery_status'] = $GLOBALS['_LANG']['no_settlement'];
		}
		else if ($v['delivery_status'] == 1) {
			$row[$k]['delivery_status'] = $GLOBALS['_LANG']['is_settlement'];
		}
		else if ($v['delivery_status'] == 2) {
			$row[$k]['delivery_status'] = '解除冻结';
		}
		else if ($v['delivery_status'] == 3) {
			$row[$k]['delivery_status'] = '冻结';
		}

		if ($v['gift_gard_id']) {
			$row[$k]['gift_sn'] = $GLOBALS['db']->getOne(' SELECT order_sn FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $v['gift_gard_id'] . '\'');
		}
	}

	$arr = array('pzd_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_merchants_order_valid_refund($ru_id, $type = 0)
{
	if ($type == 1) {
		$where = order_query_sql('finished', 'oi.');
		$sql = 'SELECT SUM(oreturn.actual_return) AS total_fee, (' . order_activity_field_add('oi.') . ') AS activity_fee, oi.goods_amount FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi,' . $GLOBALS['ecs']->table('order_return') . ' as oreturn ' . ' WHERE 1' . (' ' . $where . ' AND oi.order_id = oreturn.order_id AND oreturn.back IN (1, 3) AND (SELECT og.ru_id FROM ') . $GLOBALS['ecs']->table('order_goods') . ' as og' . (' WHERE og.order_id = oi.order_id limit 0, 1) = \'' . $ru_id . '\'') . ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id limit 0, 1) = 0';
		$res = $GLOBALS['db']->getRow($sql);
		if ($res && 0 < $res['total_fee'] && 0 < $goods_rate['total_fee']) {
			$order = array('goods_amount' => $res['goods_amount'], 'activity_fee' => $res['activity_fee']);
			$goods_rate = get_alone_goods_rate(0, $ru_id, $order);

			if ($goods_rate['rate_activity']) {
				$res['total_fee'] = $res['total_fee'] + $goods_rate['rate_activity'];
			}

			$res['order_total_fee'] = $res['total_fee'] - $goods_rate['total_fee'];
			$res['goods_total_fee'] = $goods_rate['total_fee'];
		}
	}
	else {
		$where = order_query_sql('confirm_take', 'oi.');
		$total_fee = 'SUM((' . order_commission_field('oi.') . ')) AS total_fee, (' . order_activity_field_add('oi.') . ') AS activity_fee, oi.goods_amount ';
		$sql = 'SELECT oi.order_id, oi.order_sn, ' . $total_fee . '  FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi ' . (' WHERE 1 ' . $where . ' AND (SELECT og.ru_id FROM ') . $GLOBALS['ecs']->table('order_goods') . ' as og' . (' WHERE og.order_id = oi.order_id LIMIT 1) = \'' . $ru_id . '\'') . ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id LIMIT 1) = 0 LIMIT 1';
		$res = $GLOBALS['db']->getRow($sql);
		if ($res && 0 < $res['total_fee'] && 0 < $goods_rate['total_fee']) {
			$order = array('goods_amount' => $res['goods_amount'], 'activity_fee' => $res['activity_fee']);
			$goods_rate = get_alone_goods_rate(0, $ru_id, $order);

			if ($goods_rate['rate_activity']) {
				$res['total_fee'] = $res['total_fee'] + $goods_rate['rate_activity'];
			}

			$res['order_total_fee'] = $res['total_fee'] - $goods_rate['total_fee'];
			$res['goods_total_fee'] = $goods_rate['total_fee'];
			$total_fee = $res['order_total_fee'];
		}
		else {
			$total_fee = $res['total_fee'];
		}

		if (file_exists(MOBILE_DRP) && $res) {
			$order_drp = get_order_drp_money($total_fee, $ru_id);
			$res['total_fee'] = $order_drp['total_fee'];
			$res['drp_money'] = $order_drp['drp_money'];
		}
	}

	if (isset($res['order_total_fee']) && $res['order_total_fee'] < 0) {
		$res['order_total_fee'] = 0;
	}

	if ($goods_rate) {
		$res['is_goods_rate'] = 1;
	}
	else {
		$res['is_goods_rate'] = 0;
	}

	return $res;
}

function get_order_take_brokerage($seller_id = 0, $order_id = 0, $chargeoff_time = 0)
{
	$where = 1;

	if ($seller_id) {
		$where .= ' AND sal.ru_id = \'' . $seller_id . '\'';
	}

	if ($order_id) {
		$where .= ' AND sal.order_id ' . db_create_in($order_id);
	}

	$sql = 'SELECT SUM(sal.amount) FROM ' . $GLOBALS['ecs']->table('seller_account_log') . ' AS sal, ' . $GLOBALS['ecs']->table('seller_bill_order') . ' AS sbo ' . (' WHERE ' . $where . ' AND sal.is_paid = 1 AND sal.order_id = sbo.order_id AND sal.log_type = 2 AND add_time > \'' . $chargeoff_time . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function get_alone_goods_rate($order_id = 0, $ru_id = 0, $order = array())
{
	$where = 1;

	if ($order_id) {
		$where .= ' AND sbg.order_id = \'' . $order_id . '\'';
	}
	else if ($ru_id) {
		$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' AS og WHERE og.rec_id = sbg.rec_id LIMIT 1) = \'' . $ru_id . '\'');
	}

	$res['order_activity'] = 0;
	$res['rate_activity'] = 0;

	if ($order) {
		if ($order['goods_amount'] <= 0) {
			$order['goods_amount'] = 1;
		}

		$sql = 'SELECT SUM(sbg.goods_price * sbg.goods_number - sbg.dis_amount) AS total_fee ' . 'FROM ' . $GLOBALS['ecs']->table('seller_bill_goods') . (' AS sbg WHERE ' . $where . ' AND sbg.commission_rate > 0');
		$res = $GLOBALS['db']->getRow($sql);
		$res['order_percent'] = round(($order['goods_amount'] - $res['total_fee']) / $order['goods_amount'], 2);
		$res['rate_percent'] = round($res['total_fee'] / $order['goods_amount'], 2);
		$res['order_activity'] = round($order['activity_fee'] * $res['order_percent'], 2);
		$res['rate_activity'] = round($order['activity_fee'] * $res['rate_percent'], 2);
		$res = !empty($res) ? array_merge($order, $res) : $order;

		if ($res['rate_activity'] < 0) {
			$res['rate_activity'] = 0;
		}

		if ($res['order_activity'] < 0) {
			$res['order_activity'] = 0;
		}
	}

	$sql = 'SELECT rec_id, sbg.commission_rate, (sbg.goods_price * sbg.goods_number - sbg.dis_amount) AS goods_amount ' . 'FROM ' . $GLOBALS['ecs']->table('seller_bill_goods') . (' AS sbg WHERE ' . $where . ' AND sbg.commission_rate > 0');
	$goods_list = $GLOBALS['db']->getAll($sql);
	$row = array();

	if ($goods_list) {
		$row['rec_id'] = 0;
		$row['total_fee'] = 0;

		foreach ($goods_list as $key => $goods) {
			$rec_id .= $goods['rec_id'] . ',';
			$row['total_fee'] += $goods['goods_amount'];
			$row['goods'][$goods['rec_id']]['goods_amount'] = $goods['goods_amount'];
			$row['goods'][$goods['rec_id']]['commission_rate'] = $goods['commission_rate'];
		}

		$row['rec_id'] = get_del_str_comma($rec_id);
		$row['format_total_fee'] = number_format($row['total_fee'], 2, '.', '');
		$gain_commission = 0;
		$should_amount = 0;

		foreach ($row['goods'] as $key => $goods) {
			$row[$key]['order_percent'] = round($goods['goods_amount'] / $row['format_total_fee'], 2);
			$row[$key]['rate_activity'] = round($row[$key]['order_percent'] * $res['rate_activity'], 2);
			$row[$key]['goods_amount'] = round($goods['goods_amount'] - $row[$key]['rate_activity'], 2);
			$sql = 'SELECT (actual_return - return_shipping_fee) AS actual_return FROM ' . $GLOBALS['ecs']->table('return_goods') . ' AS rg, ' . $GLOBALS['ecs']->table('order_return') . ' AS ro' . (' WHERE rg.rec_id = \'' . $key . '\' AND rg.ret_id = ro.ret_id');
			$actual_return = $GLOBALS['db']->getOne($sql);
			$row[$key]['should_amount'] = number_format(($row[$key]['goods_amount'] - $actual_return) * $goods['commission_rate'], 2, '.', '');
			$row[$key]['gain_commission'] = $row[$key]['goods_amount'] - $actual_return - $row[$key]['should_amount'];
			$gain_commission += $row[$key]['gain_commission'];
			$should_amount += $row[$key]['should_amount'];
		}

		$row['gain_commission'] = number_format($gain_commission, 2, '.', '');
		$row['should_amount'] = number_format($should_amount, 2, '.', '');
		$row['total_fee'] = number_format($row['format_total_fee'] - $res['rate_activity'], 2, '.', '');
		$row['format_total_fee'] = price_format($row['total_fee'], false);
		$row['order_activity'] = $res['order_activity'];
		$row['rate_activity'] = $res['rate_activity'];
	}

	return $row;
}

function get_bill_order_info($id = 0, $start_time = 0, $end_time = 0)
{
	$sql = 'SELECT GROUP_CONCAT(order_id) FROM ' . $GLOBALS['ecs']->table('seller_bill_order') . (' WHERE bill_id = \'' . $id . '\' OR ') . ('confirm_take_time >= \'' . $start_time . '\' AND confirm_take_time <= \'' . $end_time . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function get_weeks_list($month)
{
	$weekinfo = array();
	$end_date = local_date('d', local_strtotime($month . ' +1 month -1 day'));

	for ($i = 1; $i < $end_date; $i = $i + 7) {
		$w = local_date('N', local_strtotime($month . '-' . $i));
		$weekinfo[] = array(local_date('Y-m-d', local_strtotime($month . '-' . $i . ' -' . ($w - 1) . ' days')), local_date('Y-m-d', local_strtotime($month . '-' . $i . ' +' . (7 - $w) . ' days')));
	}

	return $weekinfo;
}

function get_bill_per_day($seller_id = 0, $cycle = 0)
{
	$day_array = array();
	$bill = get_bill_minmax_time($seller_id, $cycle);
	$mintime = 0;
	$maxtime = 0;

	if ($bill) {
		$mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
		$maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
	}

	if ($mintime && $maxtime) {
		$min_time = local_date('Y-m-d', $mintime);
		$max_time = local_date('Y-m-d', $maxtime);
		$min_time = explode('-', $min_time);
		$max_time = explode('-', $max_time);
		$min_year = intval($min_time[0]);
		$max_year = intval($max_time[0]);
		$min_month = intval($min_time[1]);
		$max_month = intval($max_time[1]);
		$min_day = intval($min_time[2]);
		$max_day = intval($max_time[2]);
		$day_number = 0;

		if ($min_year < $max_year) {
			$min_count = 12 - $min_month;

			if (0 < $min_count) {
				for ($i = $min_month; $i <= 12; $i++) {
					$days = cal_days_in_month(CAL_GREGORIAN, $i, $min_year);

					if (!($i == $min_month)) {
						$day_number += $days;
					}
					else if ($i == $min_month) {
						$min_day = $days - $min_day;
						$day_number += $min_day;
					}
				}
			}
			else {
				$min_month_day = cal_days_in_month(CAL_GREGORIAN, $min_month, $min_year);
				$min_day = $min_month_day - $min_day;
				$day_number += $min_day;
			}

			for ($i = 1; $i <= $max_month; $i++) {
				$days = cal_days_in_month(CAL_GREGORIAN, $i, $max_year);

				if (!($i == $max_month)) {
					$day_number += $days;
				}

				if ($i == $max_month) {
					$max_day = $max_day;
					$day_number += $max_day;
				}
			}
		}
		else if ($min_month < $max_month) {
			for ($i = $min_month; $i <= $max_month; $i++) {
				$days = cal_days_in_month(CAL_GREGORIAN, $i, $min_year);
				if (!($i == $min_month || $i == $max_month)) {
					$day_number += $days;
				}
				else {
					if ($i == $min_month) {
						$min_day = $days - $min_day;
						$day_number += $min_day;
					}

					if ($i == $max_month) {
						$max_day = $max_day;
						$day_number += $max_day;
					}
				}
			}
		}
		else if ($min_day < $max_day) {
			$day_number = $max_day - $min_day - 1;
		}

		if (0 < $day_number) {
			$idx = 0;

			for ($i = 1; $i <= $day_number; $i++) {
				$bill_day = local_date('Y-m-d', $mintime + 24 * 60 * 60 * $i);
				$bill_day_start = $bill_day . ' 00:00:00';
				$bill_day_end = $bill_day . ' 23:59:59';
				$day_start = local_strtotime($bill_day_start);
				$day_end = local_strtotime($bill_day_end);
				$bill_id = get_bill_id($seller_id, $cycle, $day_start, $day_end);
				if (!$bill_id && ($mintime <= $day_start && $day_end <= $maxtime)) {
					$day_array[$idx]['last_year_start'] = $bill_day_start;
					$day_array[$idx]['last_year_end'] = $bill_day_end;
				}

				$idx++;
			}
		}
	}

	return $day_array;
}

function get_bill_seven_day($seller_id = 0, $cycle = 0)
{
	$bill = get_bill_minmax_time($seller_id, $cycle);
	$mintime = 0;
	$maxtime = 0;
	$day_array = array();

	if ($bill) {
		$mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
		$maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
	}

	$week_array = array();
	if ($mintime && $maxtime) {
		$min_time = local_date('Y-m-d', $mintime);
		$max_time = local_date('Y-m-d', $maxtime);
		$min_time = explode('-', $min_time);
		$max_time = explode('-', $max_time);
		$min_year = intval($min_time[0]);
		$max_year = intval($max_time[0]);
		$min_month = intval($min_time[1]);
		$max_month = intval($max_time[1]);

		if ($min_year < $max_year) {
			$min_count = 12 - $min_month;

			for ($i = 0; $i <= $min_count; $i++) {
				$minmonth = $min_month + $i;
				$min_weeks[] = get_weeks_list($min_year . '-' . $minmonth);
			}

			for ($i = 1; $i <= $max_month; $i++) {
				$max_weeks[] = get_weeks_list($max_year . '-' . $i);
			}

			if ($min_weeks && $max_weeks) {
				$weeks = array_merge($min_weeks, $max_weeks);
			}
			else if ($min_weeks) {
				$weeks = $min_weeks;
			}
			else if ($max_weeks) {
				$weeks = $max_weeks;
			}
		}
		else if ($min_month < $max_month) {
			$m_count = $max_month - $min_month;

			for ($i = 0; $i <= $m_count; $i++) {
				$month = $min_month + $i;
				$weeks[] = get_weeks_list($max_year . '-' . $month);
			}
		}

		if ($weeks) {
			$start_mintime = $mintime;
			$end_mintime = $maxtime + 6 * 24 * 3600;

			foreach ($weeks as $key => $row) {
				foreach ($row as $keys => $rows) {
					$start_time = local_strtotime($rows[0]);
					$end_time = local_strtotime($rows[1]);
					if ($start_mintime <= $start_time && $end_time <= $end_mintime) {
						$week_array[] = $rows;
					}
				}
			}
		}

		$idx = 0;

		if ($week_array) {
			foreach ($week_array as $wkey => $wrow) {
				$bill_day_start = $wrow[0] . ' 00:00:00';
				$bill_day_end = $wrow[1] . ' 23:59:59';
				$day_start = local_strtotime($bill_day_start);
				$day_end = local_strtotime($bill_day_end);
				$bill_id = get_bill_id($seller_id, $cycle, $day_start, $day_end);
				if (!$bill_id && ($mintime <= $day_start && $day_end <= $maxtime)) {
					$day_array[$idx]['last_year_start'] = $bill_day_start;
					$day_array[$idx]['last_year_end'] = $bill_day_end;
				}

				$idx++;
			}
		}
	}

	return $day_array;
}

function get_bill_half_month($seller_id = 0, $cycle = 0)
{
	$bill = get_bill_minmax_time($seller_id, $cycle);
	$mintime = 0;
	$maxtime = 0;
	$day_array = array();

	if ($bill) {
		$mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
		$maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
	}

	if ($mintime && $maxtime) {
		$min_time = local_date('Y-m-d', $mintime);
		$max_time = local_date('Y-m-d', $maxtime);
		$min_time = explode('-', $min_time);
		$max_time = explode('-', $max_time);
		$min_year = intval($min_time[0]);
		$max_year = intval($max_time[0]);
		$min_month = intval($min_time[1]);
		$max_month = intval($max_time[1]);

		if ($min_year < $max_year) {
			$month_array = array();
			$min_count = 12 - $min_month;

			if (0 < $min_count) {
				for ($i = $min_month; $i <= 12; $i++) {
					$days = cal_days_in_month(CAL_GREGORIAN, $i, $min_year);
					$halfMonth = intval($days / 2);

					if ($i <= 9) {
						$upper_start_time = $min_year . '-0' . $i . '-01' . ' 00:00:00';
						$upper_end_time = $min_year . '-0' . $i . '-' . $halfMonth . ' 23:59:59';
						$lower_start_time = $min_year . '-0' . $i . '-' . ($halfMonth + 1) . ' 00:00:00';
						$lower_end_time = $min_year . '-0' . $i . '-' . $days . ' 23:59:59';
					}
					else {
						$upper_start_time = $min_year . '-' . $i . '-01' . ' 00:00:00';
						$upper_end_time = $min_year . '-' . $i . '-' . $halfMonth . ' 23:59:59';
						$lower_start_time = $min_year . '-' . $i . '-' . ($halfMonth + 1) . ' 00:00:00';
						$lower_end_time = $min_year . '-' . $i . '-' . $days . ' 23:59:59';
					}

					$min_month_array[] = array(
	'upper' => array('start_time' => $upper_start_time, 'end_time' => $upper_end_time),
	'lower' => array('start_time' => $lower_start_time, 'end_time' => $lower_end_time)
	);
				}
			}
			else {
				$days = cal_days_in_month(CAL_GREGORIAN, $min_month, $min_year);
				$halfMonth = intval($days / 2);
				$upper_start_time = $min_year . '-12-01' . ' 00:00:00';
				$upper_end_time = $min_year . '-12-' . $halfMonth . ' 23:59:59';
				$lower_start_time = $min_year . '-12-' . ($halfMonth + 1) . ' 00:00:00';
				$lower_end_time = $min_year . '-12-' . $days . ' 23:59:59';
				$min_month_array[] = array(
	'upper' => array('start_time' => $upper_start_time, 'end_time' => $upper_end_time),
	'lower' => array('start_time' => $lower_start_time, 'end_time' => $lower_end_time)
	);
			}

			for ($i = 1; $i <= $max_month; $i++) {
				$days = cal_days_in_month(CAL_GREGORIAN, $i, $max_year);
				$halfMonth = intval($days / 2);

				if ($i <= 9) {
					$upper_start_time = $max_year . '-0' . $i . '-01' . ' 00:00:00';
					$upper_end_time = $max_year . '-0' . $i . '-' . $halfMonth . ' 23:59:59';
					$lower_start_time = $max_year . '-0' . $i . '-' . ($halfMonth + 1) . ' 00:00:00';
					$lower_end_time = $max_year . '-0' . $i . '-' . $days . ' 23:59:59';
				}
				else {
					$upper_start_time = $max_year . '-' . $i . '-01' . ' 00:00:00';
					$upper_end_time = $max_year . '-' . $i . '-' . $halfMonth . ' 23:59:59';
					$lower_start_time = $max_year . '-' . $i . '-' . ($halfMonth + 1) . ' 00:00:00';
					$lower_end_time = $max_year . '-' . $i . '-' . $days . ' 23:59:59';
				}

				$max_month_array[] = array(
	'upper' => array('start_time' => $upper_start_time, 'end_time' => $upper_end_time),
	'lower' => array('start_time' => $lower_start_time, 'end_time' => $lower_end_time)
	);
			}

			if ($min_month_array && $max_month_array) {
				$month_list = array_merge($min_month_array, $max_month_array);
			}
			else if ($min_month_array) {
				$month_list = $min_month_array;
			}
			else if ($max_month_array) {
				$month_list = $max_month_array;
			}

			if ($month_list) {
				foreach ($month_list as $key => $row) {
					$upper_day_start = local_strtotime($row['upper']['start_time']);
					$upper_day_end = local_strtotime($row['upper']['end_time']);
					$lower_day_start = local_strtotime($row['lower']['start_time']);
					$lower_day_end = local_strtotime($row['lower']['end_time']);
					$upper_id = get_bill_id($seller_id, $cycle, $upper_day_start, $upper_day_end);
					if (!$upper_id && ($mintime <= $upper_day_start && $upper_day_end <= $maxtime)) {
						$upper_array['last_year_start'] = $row['upper']['start_time'];
						$upper_array['last_year_end'] = $row['upper']['end_time'];
						array_push($day_array, $upper_array);
					}

					$lower_id = get_bill_id($seller_id, $cycle, $lower_day_start, $lower_day_end);
					if (!$lower_id && ($mintime <= $lower_day_start && $lower_day_end <= $maxtime)) {
						$lower_array['last_year_start'] = $row['lower']['start_time'];
						$lower_array['last_year_end'] = $row['lower']['end_time'];
						array_push($day_array, $lower_array);
					}
				}
			}
		}
		else if ($min_month < $max_month) {
			$month_array = array();

			for ($i = $min_month; $i <= $max_month; $i++) {
				$days = cal_days_in_month(CAL_GREGORIAN, $i, $min_year);
				$halfMonth = intval($days / 2);

				if ($i <= 9) {
					$upper_start_time = $min_year . '-0' . $i . '-01' . ' 00:00:00';
					$upper_end_time = $min_year . '-0' . $i . '-' . $halfMonth . ' 23:59:59';
					$lower_start_time = $min_year . '-0' . $i . '-' . ($halfMonth + 1) . ' 00:00:00';
					$lower_end_time = $min_year . '-0' . $i . '-' . $days . ' 23:59:59';
				}
				else {
					$upper_start_time = $min_year . '-' . $i . '-01' . ' 00:00:00';
					$upper_end_time = $min_year . '-' . $i . '-' . $halfMonth . ' 23:59:59';
					$lower_start_time = $min_year . '-' . $i . '-' . ($halfMonth + 1) . ' 00:00:00';
					$lower_end_time = $min_year . '-' . $i . '-' . $days . ' 23:59:59';
				}

				$month_array[] = array(
	'upper' => array('start_time' => $upper_start_time, 'end_time' => $upper_end_time),
	'lower' => array('start_time' => $lower_start_time, 'end_time' => $lower_end_time)
	);
			}

			if ($month_array) {
				foreach ($month_array as $key => $row) {
					$upper_day_start = local_strtotime($row['upper']['start_time']);
					$upper_day_end = local_strtotime($row['upper']['end_time']);
					$lower_day_start = local_strtotime($row['lower']['start_time']);
					$lower_day_end = local_strtotime($row['lower']['end_time']);
					$upper_id = get_bill_id($seller_id, $cycle, $upper_day_start, $upper_day_end);
					if (!$upper_id && ($mintime <= $upper_day_start && $upper_day_end <= $maxtime)) {
						$upper_array['last_year_start'] = $row['upper']['start_time'];
						$upper_array['last_year_end'] = $row['upper']['end_time'];
						array_push($day_array, $upper_array);
					}

					$lower_id = get_bill_id($seller_id, $cycle, $lower_day_start, $lower_day_end);
					if (!$lower_id && ($mintime <= $lower_day_start && $lower_day_end <= $maxtime)) {
						$lower_array['last_year_start'] = $row['lower']['start_time'];
						$lower_array['last_year_end'] = $row['lower']['end_time'];
						array_push($day_array, $lower_array);
					}
				}
			}
		}
	}

	return $day_array;
}

function get_bill_one_month($seller_id = 0, $cycle = 0)
{
	$bill = get_bill_minmax_time($seller_id, $cycle);
	$mintime = 0;
	$maxtime = 0;
	$day_array = array();

	if ($bill) {
		$mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
		$maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
	}

	if ($mintime && $maxtime) {
		$min_time = local_date('Y-m-d', $mintime);
		$max_time = local_date('Y-m-d', $maxtime);
		$min_time = explode('-', $min_time);
		$max_time = explode('-', $max_time);
		$min_year = intval($min_time[0]);
		$max_year = intval($max_time[0]);
		$min_month = intval($min_time[1]);
		$max_month = intval($max_time[1]);

		if ($min_year < $max_year) {
			$iidx = 0;
			$min_array = array();
			$min_count = 12 - $min_month;

			if (0 < $min_count) {
				for ($i = $min_month; $i <= 12; $i++) {
					$days = cal_days_in_month(CAL_GREGORIAN, $i, $min_year);
					$nowMonth = $i;

					if ($nowMonth <= 9) {
						$nowMonth = '0' . $nowMonth;
					}

					$last_year_start = $min_year . '-' . $nowMonth . '-01 00:00:00';
					$last_year_end = $min_year . '-' . $nowMonth . '-' . $days . ' 23:59:59';
					$day_start = local_strtotime($last_year_start);
					$day_end = local_strtotime($last_year_end);
					$bill_id = get_bill_id($seller_id, $cycle, $day_start, $day_end);
					if (!$bill_id && ($mintime <= $day_start && $day_end <= $maxtime)) {
						$min_array[$iidx]['last_year_start'] = $last_year_start;
						$min_array[$iidx]['last_year_end'] = $last_year_end;
					}

					$iidx++;
				}
			}
			else {
				$last_year_start = $min_year . '-12-01 00:00:00';
				$last_year_end = $min_year . '-12-' . $days . ' 23:59:59';
				$day_start = local_strtotime($last_year_start);
				$day_end = local_strtotime($last_year_end);
				$bill_id = get_bill_id($seller_id, $cycle, $day_start, $day_end);
				if (!$bill_id && ($mintime <= $day_start && $day_end <= $maxtime)) {
					$min_array[$iidx]['last_year_start'] = $last_year_start;
					$min_array[$iidx]['last_year_end'] = $last_year_end;
				}
			}

			$aidx = 0;
			$max_array = array();

			for ($i = 1; $i <= $max_month; $i++) {
				$days = cal_days_in_month(CAL_GREGORIAN, $i, $max_year);
				$nowMonth = $i;

				if ($nowMonth <= 9) {
					$nowMonth = '0' . $nowMonth;
				}

				$last_year_start = $max_year . '-' . $nowMonth . '-01 00:00:00';
				$last_year_end = $max_year . '-' . $nowMonth . '-' . $days . ' 23:59:59';
				$day_start = local_strtotime($last_year_start);
				$day_end = local_strtotime($last_year_end);
				$bill_id = get_bill_id($seller_id, $cycle, $day_start, $day_end);
				if (!$bill_id && ($mintime <= $day_start && $day_end <= $maxtime)) {
					$max_array[$aidx]['last_year_start'] = $last_year_start;
					$max_array[$aidx]['last_year_end'] = $last_year_end;
				}

				$aidx++;
			}

			if ($min_array && $max_array) {
				$day_array = array_merge($min_array, $max_array);
			}
			else if ($min_array) {
				$day_array = $min_array;
			}
			else if ($max_array) {
				$day_array = $max_array;
			}
		}
		else if ($min_month < $max_month) {
			$idx = 0;

			for ($i = $min_month; $i <= $max_month; $i++) {
				$days = cal_days_in_month(CAL_GREGORIAN, $i, $min_year);
				$nowMonth = $i;

				if ($nowMonth <= 9) {
					$nowMonth = '0' . $nowMonth;
				}

				$last_year_start = $min_year . '-' . $nowMonth . '-01 00:00:00';
				$last_year_end = $min_year . '-' . $nowMonth . '-' . $days . ' 23:59:59';
				$day_start = local_strtotime($last_year_start);
				$day_end = local_strtotime($last_year_end);
				$bill_id = get_bill_id($seller_id, $cycle, $day_start, $day_end);
				if (!$bill_id && ($mintime <= $day_start && $day_end <= $maxtime)) {
					$day_array[$idx]['last_year_start'] = $last_year_start;
					$day_array[$idx]['last_year_end'] = $last_year_end;
				}

				$idx++;
			}
		}
	}

	return $day_array;
}

function get_bill_quarter($seller_id = 0, $cycle = 0)
{
	$bill = get_bill_minmax_time($seller_id, $cycle);
	$mintime = 0;
	$maxtime = 0;
	$day_array = array();

	if ($bill) {
		$mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
		$maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
	}

	if ($mintime && $maxtime) {
		$min_time = local_date('Y-m-d', $mintime);
		$max_time = local_date('Y-m-d', $maxtime);
		$min_time = explode('-', $min_time);
		$max_time = explode('-', $max_time);
		$min_year = intval($min_time[0]);
		$max_year = intval($max_time[0]);
		$min_month = intval($min_time[1]);
		$max_month = intval($max_time[1]);

		if ($min_year < $max_year) {
			$iidx = 0;
			$min_array = array();
			$min_count = 12 - $min_month;

			if (0 < $min_count) {
				for ($i = $min_month; $i <= 12; $i++) {
					$nowMonth = $i;
					$month_year = get_month_year($nowMonth, $min_year);
					if ($month_year && $month_year['last_year_start'] && $month_year['last_year_end']) {
						$day_start = local_strtotime($month_year['last_year_start']);
						$day_end = local_strtotime($month_year['last_year_end']);
						$bill_id = get_bill_id($seller_id, $cycle, $day_start, $day_end);
						if (!$bill_id && ($mintime < $day_start && $day_end < $maxtime)) {
							$min_array[$month_year['quarter']]['last_year_start'] = $month_year['last_year_start'];
							$min_array[$month_year['quarter']]['last_year_end'] = $month_year['last_year_end'];
						}
					}

					$iidx++;
				}
			}
			else {
				$month_year = get_month_year(12, $min_year);
				if ($month_year && $month_year['last_year_start'] && $month_year['last_year_end']) {
					$day_start = local_strtotime($month_year['last_year_start']);
					$day_end = local_strtotime($month_year['last_year_end']);
					$bill_id = get_bill_id($seller_id, $cycle, $day_start, $day_end);
					if (!$bill_id && ($mintime < $day_start && $day_end < $maxtime)) {
						$min_array[$month_year['quarter']]['last_year_start'] = $month_year['last_year_start'];
						$min_array[$month_year['quarter']]['last_year_end'] = $month_year['last_year_end'];
					}
				}
			}

			$aidx = 0;
			$max_array = array();

			for ($i = 1; $i <= $max_month; $i++) {
				$nowMonth = $i;
				$month_year = get_month_year($nowMonth, $max_year);
				if ($month_year && $month_year['last_year_start'] && $month_year['last_year_end']) {
					$day_start = local_strtotime($month_year['last_year_start']);
					$day_end = local_strtotime($month_year['last_year_end']);
					$bill_id = get_bill_id($seller_id, $cycle, $day_start, $day_end);
					if (!$bill_id && ($mintime < $day_start && $day_end < $maxtime)) {
						$max_array[$month_year['quarter']]['last_year_start'] = $month_year['last_year_start'];
						$max_array[$month_year['quarter']]['last_year_end'] = $month_year['last_year_end'];
					}
				}

				$aidx++;
			}

			if ($min_array && $max_array) {
				$day_array = array_merge($min_array, $max_array);
			}
			else if ($min_array) {
				$day_array = $min_array;
			}
			else if ($max_array) {
				$day_array = $max_array;
			}
		}
		else if ($min_month < $max_month) {
			$idx = 0;

			for ($i = $min_month; $i <= $max_month; $i++) {
				$nowMonth = $i;
				$month_year = get_month_year($nowMonth, $max_year);
				if ($month_year && $month_year['last_year_start'] && $month_year['last_year_end']) {
					$day_start = local_strtotime($month_year['last_year_start']);
					$day_end = local_strtotime($month_year['last_year_end']);
					$bill_id = get_bill_id($seller_id, $cycle, $day_start, $day_end);
					if (!$bill_id && ($mintime < $day_start && $day_end < $maxtime)) {
						$day_array[$month_year['quarter']]['last_year_start'] = $month_year['last_year_start'];
						$day_array[$month_year['quarter']]['last_year_end'] = $month_year['last_year_end'];
					}
				}

				$idx++;
			}
		}
	}

	return $day_array;
}

function get_month_year($nowMonth, $nowYear)
{
	if ($nowMonth == 1 && $nowMonth <= 3) {
		$last_year_start = $nowYear . '-01-01 00:00:00';
		$last_year_end = $nowYear . '-03-31 23:59:59';
		$quarter = 1;
	}
	else {
		if (3 < $nowMonth && $nowMonth <= 6) {
			$last_year_start = $nowYear . '-04-01 00:00:00';
			$last_year_end = $nowYear . '-06-30 23:59:59';
			$quarter = 2;
		}
		else {
			if (6 < $nowMonth && $nowMonth <= 9) {
				$last_year_start = $nowYear . '-07-01 00:00:00';
				$last_year_end = $nowYear . '-09-30 23:59:59';
				$quarter = 3;
			}
			else {
				if (9 < $nowMonth && $nowMonth <= 12) {
					$last_year_start = $nowYear . '-10-01 00:00:00';
					$last_year_end = $nowYear . '-12-31 23:59:59';
					$quarter = 4;
				}
			}
		}
	}

	$arr = array('last_year_start' => $last_year_start, 'last_year_end' => $last_year_end, 'quarter' => $quarter);
	return $arr;
}

function get_bill_half_year($seller_id = 0, $cycle = 0)
{
	$bill = get_bill_minmax_time($seller_id, $cycle);
	$mintime = 0;
	$maxtime = 0;
	$day_array = array();

	if ($bill) {
		$mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
		$maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
	}

	if ($mintime && $maxtime) {
		$min_time = local_date('Y-m-d', $mintime);
		$max_time = local_date('Y-m-d', $maxtime);
		$min_time = explode('-', $min_time);
		$max_time = explode('-', $max_time);
		$min_year = intval($min_time[0]);
		$max_year = intval($max_time[0]);
		$year_array = array();

		if ($min_year < $max_year) {
			$year_count = $max_year - $min_year;

			for ($i = 0; $i <= $year_count; $i++) {
				$year = $min_year + $i;
				$upper_year_start = $year . '-01-01 00:00:00';
				$upper_year_end = $year . '-06-30 23:59:59';
				$upper = array('start_time' => $upper_year_start, 'end_time' => $upper_year_end);
				$year_array[$i]['upper'] = $upper;
				$lower_year_start = $year . '-07-01 00:00:00';
				$lower_year_end = $year . '-12-31 23:59:59';
				$lower = array('start_time' => $lower_year_start, 'end_time' => $lower_year_end);
				$year_array[$i]['lower'] = $lower;
			}
		}

		if ($year_array) {
			foreach ($year_array as $key => $row) {
				$upper_day_start = local_strtotime($row['upper']['start_time']);
				$upper_day_end = local_strtotime($row['upper']['end_time']);
				$lower_day_start = local_strtotime($row['lower']['start_time']);
				$lower_day_end = local_strtotime($row['lower']['end_time']);
				$upper_id = get_bill_id($seller_id, $cycle, $upper_day_start, $upper_day_end);
				if (!$upper_id && ($mintime <= $upper_day_start && $upper_day_end <= $maxtime)) {
					$upper_array['last_year_start'] = $row['upper']['start_time'];
					$upper_array['last_year_end'] = $row['upper']['end_time'];
					array_push($day_array, $upper_array);
				}

				$lower_id = get_bill_id($seller_id, $cycle, $lower_day_start, $lower_day_end);
				if (!$lower_id && ($mintime <= $lower_day_start && $lower_day_end <= $maxtime)) {
					$lower_array['last_year_start'] = $row['lower']['start_time'];
					$lower_array['last_year_end'] = $row['lower']['end_time'];
					array_push($day_array, $lower_array);
				}
			}
		}
	}

	return $day_array;
}

function get_bill_one_year($seller_id = 0, $cycle = 0)
{
	$bill = get_bill_minmax_time($seller_id, $cycle);
	$mintime = 0;
	$maxtime = 0;
	$day_array = array();

	if ($bill) {
		$mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
		$maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
	}

	if ($mintime && $maxtime) {
		$min_time = local_date('Y-m-d', $mintime);
		$max_time = local_date('Y-m-d', $maxtime);
		$min_time = explode('-', $min_time);
		$max_time = explode('-', $max_time);
		$min_year = intval($min_time[0]);
		$max_year = intval($max_time[0]);
		$year_array = array();

		if ($min_year < $max_year) {
			$year_count = $max_year - $min_year;

			for ($i = 0; $i <= $year_count; $i++) {
				$year = $min_year + $i;
				$year_start = $year . '-01-01 00:00:00';
				$year_end = $year . '-12-31 23:59:59';
				$year_array[$i]['last_year_start'] = $year_start;
				$year_array[$i]['last_year_end'] = $year_end;
			}
		}

		if ($year_array) {
			foreach ($year_array as $key => $row) {
				$year_start = local_strtotime($row['last_year_start']);
				$year_end = local_strtotime($row['last_year_end']);
				$bill_id = get_bill_id($seller_id, $cycle, $year_start, $year_end);
				if (!$bill_id && ($mintime < $year_start && $year_end < $maxtime)) {
					$day_array[$key]['last_year_start'] = $row['last_year_start'];
					$day_array[$key]['last_year_end'] = $row['last_year_end'];
				}
			}
		}
	}

	return $day_array;
}

function get_bill_days_number($seller_id = 0, $cycle = 0)
{
	$bill = get_bill_minmax_time($seller_id, $cycle);
	$mintime = 0;
	$maxtime = 0;
	$day_array = array();

	if ($bill) {
		$mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
		$maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
	}

	if ($mintime && $maxtime) {
		$min_time = local_date('Y-m-d', $mintime);
		$max_time = local_date('Y-m-d', $maxtime);
		$min_time = explode('-', $min_time);
		$max_time = explode('-', $max_time);
		$min_year = intval($min_time[0]);
		$max_year = intval($max_time[0]);
		$min_month = intval($min_time[1]);
		$max_month = intval($max_time[1]);
		$min_day = intval($min_time[2]);
		$max_day = intval($max_time[2]);
		$sql = 'SELECT day_number FROM ' . $GLOBALS['ecs']->table('merchants_server') . (' WHERE user_id = \'' . $seller_id . '\' LIMIT 1');
		$server = $GLOBALS['db']->getRow($sql);

		if ($min_year < $max_year) {
			$min_count = 12 - $min_month;

			if (0 < $min_count) {
				for ($i = $min_month; $i <= 12; $i++) {
					$days = cal_days_in_month(CAL_GREGORIAN, $i, $min_year);

					if (!($i == $min_month)) {
						$day_number += $days;
					}
					else if ($i == $min_month) {
						$minDay = $days - $min_day;
						$day_number += $minDay;
					}
				}
			}
			else {
				$min_month_day = cal_days_in_month(CAL_GREGORIAN, $min_month, $min_year);
				$minDay = $min_month_day - $min_day;
				$day_number += $minDay;
			}

			for ($i = 1; $i <= $max_month; $i++) {
				$days = cal_days_in_month(CAL_GREGORIAN, $i, $max_year);

				if (!($i == $max_month)) {
					$day_number += $days;
				}

				if ($i == $max_month) {
					$maxday = $max_day;
					$day_number += $maxday;
				}
			}

			if ($day_number && $server && $server['day_number'] < $day_number) {
				$number = round($day_number / $server['day_number']);

				for ($i = 0; $i <= $number; $i++) {
					$year_start = local_date('Y-m-d', local_strtotime(local_date('Y-m-d', $bill['min_time'])) + (($i + 1) * ($server['day_number'] - 1) - $server['day_number'] + 1) * 24 * 60 * 60);
					$year_end = local_date('Y-m-d', local_strtotime(local_date('Y-m-d', $bill['min_time'])) + ($i + 1) * ($server['day_number'] - 1) * 24 * 60 * 60);
					$year_start = $year_start . ' 00:00:00';
					$year_end = $year_end . ' 23:59:59';
					$year_array[$i]['last_year_start'] = $year_start;
					$year_array[$i]['last_year_end'] = $year_end;
				}
			}
		}
		else if ($min_month < $max_month) {
			$m_count = $max_month - $min_month;

			for ($i = 0; $i <= $m_count; $i++) {
				$month = $min_month + $i;
				$days = cal_days_in_month(CAL_GREGORIAN, $month, $min_year);
				if (!($month == $min_month || $month == $max_month)) {
					$day_number += $days;
				}
				else {
					if ($month == $min_month) {
						$minDay = $days - $min_day;
						$day_number += $minDay;
					}

					if ($month == $max_month) {
						$maxday = $max_day;
						$day_number += $maxday;
					}
				}
			}

			if ($day_number && $server && $server['day_number'] < $day_number) {
				$number = round($day_number / $server['day_number']);

				for ($i = 0; $i <= $number; $i++) {
					$year_start = local_date('Y-m-d', local_strtotime(local_date('Y-m-d', $bill['min_time'])) + (($i + 1) * ($server['day_number'] - 1) - $server['day_number'] + 1) * 24 * 60 * 60);
					$year_end = local_date('Y-m-d', local_strtotime(local_date('Y-m-d', $bill['min_time'])) + ($i + 1) * ($server['day_number'] - 1) * 24 * 60 * 60);
					$year_start = $year_start . ' 00:00:00';
					$year_end = $year_end . ' 23:59:59';
					$year_array[$i]['last_year_start'] = $year_start;
					$year_array[$i]['last_year_end'] = $year_end;
				}
			}
		}
		else if ($min_day < $max_day) {
			$day_number = $max_day - $min_day - 1;
		}

		if ($year_array) {
			foreach ($year_array as $key => $row) {
				$year_start = local_strtotime($row['last_year_start']);
				$year_end = local_strtotime($row['last_year_end']);
				$bill_id = get_bill_id($seller_id, $cycle, $year_start, $year_end);
				if (!$bill_id && ($mintime < $year_start && $year_end < $maxtime)) {
					$day_array[$key]['last_year_start'] = $row['last_year_start'];
					$day_array[$key]['last_year_end'] = $row['last_year_end'];
				}
			}
		}
	}

	return $day_array;
}

function get_bill_minmax_time($seller_id = 0, $cycle = 0)
{
	$sql = 'SELECT MIN(start_time) AS min_time, MAX(end_time) AS max_time FROM ' . $GLOBALS['ecs']->table('seller_commission_bill') . (' WHERE seller_id = \'' . $seller_id . '\' AND bill_cycle = \'' . $cycle . '\' LIMIT 1');
	$bill = $GLOBALS['db']->getRow($sql);
	return $bill;
}

function get_bill_id($seller_id, $cycle, $day_start, $day_end)
{
	$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table('seller_commission_bill') . (' WHERE start_time = \'' . $day_start . '\' AND end_time = \'' . $day_end . '\' AND bill_cycle = \'' . $cycle . '\' AND seller_id = \'' . $seller_id . '\'');
	return $GLOBALS['db']->getOne($sql, true);
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
