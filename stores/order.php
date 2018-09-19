<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
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

function get_order_goods($order, $store_id = 0)
{
	$goods_list = array();
	$goods_attr = array();
	$sql = 'SELECT o.*, g.model_inventory, g.model_attr AS model_attr, g.suppliers_id AS suppliers_id, g.goods_number AS storage, g.goods_thumb, o.goods_attr, IFNULL(b.brand_name, \'\') AS brand_name, p.product_sn, g.bar_code  ' . 'FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS o ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('products') . ' AS p ON o.product_id = p.product_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON o.goods_id = g.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON g.brand_id = b.brand_id ' . ('WHERE o.order_id = \'' . $order['order_id'] . '\' ');
	$res = $GLOBALS['db']->query($sql);
	$http = $GLOBALS['ecs']->http();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if ($row['is_real'] == 0) {
			$filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $GLOBALS['_CFG']['lang'] . '.php';

			if (file_exists($filename)) {
				include_once $filename;

				if (!empty($GLOBALS['_LANG'][$row['extension_code'] . '_link'])) {
					$row['goods_name'] = $row['goods_name'] . sprintf($GLOBALS['_LANG'][$row['extension_code'] . '_link'], $row['goods_id'], $order['order_sn']);
				}
			}
		}

		if (0 < $row['product_id']) {
			$products = get_warehouse_id_attr_number($row['goods_id'], $row['goods_attr_id'], $row['ru_id'], $row['warehouse_id'], $row['area_id'], $row['model_attr'], $store_id);
			$row['storage'] = $products['product_number'];
		}
		else if ($row['model_inventory'] == 1) {
			$row['storage'] = get_warehouse_area_goods($row['warehouse_id'], $row['goods_id'], 'warehouse_goods');
		}
		else if ($row['model_inventory'] == 2) {
			$row['storage'] = get_warehouse_area_goods($row['area_id'], $row['goods_id'], 'warehouse_area_goods');
		}

		$row['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$row['formated_subtotal'] = price_format($row['goods_price'] * $row['goods_number']);
		$row['formated_goods_price'] = price_format($row['goods_price']);
		$goods_attr[] = explode(' ', trim($row['goods_attr']));

		if ($row['extension_code'] == 'package_buy') {
			$row['storage'] = '';
			$row['brand_name'] = '';
			$row['package_goods_list'] = get_package_goods_list($row['goods_id']);
		}

		$row['product_id'] = empty($row['product_id']) ? 0 : $row['product_id'];
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

function get_warehouse_area_goods($warehouse_id, $goods_id, $table)
{
	$sql = 'SELECT region_number FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE region_id = \'' . $warehouse_id . '\' AND goods_id = \'' . $goods_id . '\'');
	return $GLOBALS['db']->getOne($sql);
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
		$sql = "SELECT ga.goods_attr_id, ga.attr_value, ga.attr_price, a.attr_name\r\n                FROM " . $GLOBALS['ecs']->table('goods_attr') . ' AS ga, ' . $GLOBALS['ecs']->table('attribute') . (" AS a\r\n                WHERE a.attr_id = ga.attr_id\r\n                AND a.attr_type = 1\r\n                AND goods_id IN (" . $good_product_str . ') ORDER BY a.sort_order, a.attr_id, g.goods_attr_id');
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

function store_order_list($ru_id = 0, $store_id = 0)
{
	$filter = array();
	$filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
	if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
		$_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
	}

	$filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
	$filter['email'] = empty($_REQUEST['email']) ? '' : trim($_REQUEST['email']);
	$filter['address'] = empty($_REQUEST['address']) ? '' : trim($_REQUEST['address']);
	$filter['tel'] = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);
	$filter['mobile'] = empty($_REQUEST['mobile']) ? 0 : trim($_REQUEST['mobile']);
	$filter['order_status'] = isset($_REQUEST['order_status']) ? intval($_REQUEST['order_status']) : -1;
	$filter['shipping_status'] = isset($_REQUEST['shipping_status']) ? intval($_REQUEST['shipping_status']) : -1;
	$filter['pay_status'] = isset($_REQUEST['pay_status']) ? intval($_REQUEST['pay_status']) : -1;
	$filter['user_id'] = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
	$filter['user_name'] = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
	$filter['composite_status'] = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;
	$filter['pick_code'] = empty($_REQUEST['pick_code']) ? '' : intval($_REQUEST['pick_code']);
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$filter['order_type'] = isset($_REQUEST['order_type']) ? $_REQUEST['order_type'] : -1;
	$where = ' WHERE 1 AND (so.store_id = \'' . $store_id . '\' OR (so.store_id = \'0\' AND so.is_grab_order = \'1\' )) ';

	switch ($filter['order_type']) {
	case 0:
		$where .= ' AND so.store_id = \'0\' AND so.is_grab_order = \'1\' AND (select count(*) from ' . $GLOBALS['ecs']->table('store_back_order') . (' as sbo where sbo.store_id = ' . $store_id . ' and sbo.order_id = so.order_id) = 0 ');
		break;

	case 1:
		$where .= ' AND store_id = ' . $store_id . ' AND (shipping_status = 0 OR shipping_status = 3) AND order_status < 2 ';
		break;

	case 2:
		$where .= ' AND store_id = ' . $store_id . '  AND shipping_status = 3 AND order_status < 2 ';
		break;

	case 3:
		$where .= ' AND store_id = ' . $store_id . '  AND shipping_status = 1 AND (order_status < 2 OR order_status = 5) ';
		break;
	}

	$where .= ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi1 WHERE oi1.main_order_id = so.order_id) = 0 ';

	if ($filter['order_sn']) {
		$where .= ' AND o.order_sn LIKE \'%' . mysql_like_quote($filter['order_sn']) . '%\'';
	}

	if ($filter['consignee']) {
		$where .= ' AND o.consignee LIKE \'%' . mysql_like_quote($filter['consignee']) . '%\'';
	}

	if ($filter['tel']) {
		$where .= ' AND o.tel = \'' . $filter['tel'] . '\'';
	}

	if ($filter['mobile']) {
		$where .= ' AND o.mobile = \'' . $filter['mobile'] . '\'';
	}

	if ($filter['pick_code']) {
		$where .= ' AND so.pick_code = \'' . $filter['pick_code'] . '\'';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('store_order') . ' AS so, ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $where . ' AND o.order_id = so.order_id ';
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT order_id FROM ' . $GLOBALS['ecs']->table('store_back_order') . (' WHERE store_id = ' . $store_id);
	$back_order = $GLOBALS['db']->getAll($sql);
	$back_arr = array();

	foreach ($back_order as $key => $val) {
		$back_arr[$val['order_id']] = $val['order_id'];
	}

	$arr = array();
	$sql = 'SELECT so.*, o.order_id, o.order_sn, o.add_time, o.consignee, o.order_status, o.shipping_status, o.pay_status, so.store_id, ' . '(' . order_amount_field('o.') . ') AS total_fee,o.shipping_fee,o.shipping_name,o.invoice_no ' . 'FROM ' . $GLOBALS['ecs']->table('store_order') . ' AS so, ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $where . ' AND o.order_id = so.order_id ' . 'ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$idx = 0;

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$rows['shipping_fee'] = price_format($rows['shipping_fee']);
		if ($rows['is_grab_order'] && !$rows['store_id'] && !empty($rows['grab_store_list'])) {
			$store_arr = explode(',', $rows['grab_store_list']);

			if (!in_array($store_id, $store_arr)) {
				continue;
			}
		}

		if (in_array($rows['order_id'], $back_arr)) {
			continue;
		}

		$info = array();
		$rows['complete_user_address'] = get_complete_address($info) . ' ' . $rows['address'];
		$rows['order_goods_list'] = get_order_goods_list($rows['order_id']);
		$rows['rowspan'] = count($rows['order_goods_list']);
		$rows['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);
		$rows['formated_total_fee'] = price_format($rows['total_fee']);
		$rows['take_time'] = $rows['take_time'];
		$rows['mobile'] = $rows['mobile'];
		$arr[$idx] = $rows;
		$idx++;
	}

	return array('orders' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_order_goods_list($order_id = 0)
{
	$sql = ' SELECT og.order_id, og.goods_id, og.goods_name, og.goods_price, og.goods_number, g.goods_thumb, og.goods_sn, og.goods_attr,og.product_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = og.goods_id ' . (' WHERE og.order_id = \'' . $order_id . '\' ');
	$order_goods_list = $GLOBALS['db']->getAll($sql);

	foreach ($order_goods_list as $key => $val) {
		$order_goods_list[$key]['formated_goods_price'] = price_format($val['goods_price']);
		$val['goods_thumb'] = get_image_path($val['goods_id'], $val['goods_thumb'], true);
		$order_goods_list[$key]['goods_thumb'] = $val['goods_thumb'];
		$sql = 'SELECT product_sn FROM ' . $GLOBALS['ecs']->table('products') . (' WHERE product_id = \'' . $val['product_id'] . '\'');
		$order_goods_list[$key]['product_sn'] = $GLOBALS['db']->getOne($sql);
	}

	return $order_goods_list;
}

function get_store_info($store_id = 0)
{
	$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('offline_store') . (' WHERE id = \'' . $store_id . '\' ');
	$store_info = $GLOBALS['db']->getRow($sql);

	if ($store_info) {
		$info = array('country' => $store_info['country'], 'province' => $store_info['province'], 'city' => $store_info['city'], 'district' => $store_info['district']);
		$store_info['complete_store_address'] = get_complete_address($info) . ' ' . $store_info['stores_address'];
	}

	return $store_info;
}

function operable_list($order)
{
	$os = $order['order_status'];
	$ss = $order['shipping_status'];
	$ps = $order['pay_status'];
	$actions = $_SESSION['action_list'];

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
	return $list;
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

function delivery_order_info($delivery_id, $delivery_sn = '')
{
	$return_order = array();
	if (empty($delivery_id) || !is_numeric($delivery_id)) {
		return $return_order;
	}

	$where = '';
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

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . 'includes/lib_goods.php';
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$store_id = $_SESSION['stores_id'];
$ru_id = $GLOBALS['db']->getOne(' SELECT ru_id FROM ' . $GLOBALS['ecs']->table('offline_store') . (' WHERE id = \'' . $store_id . '\' '));
$smarty->assign('app', 'order');
$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'stores_logo\'';
$stores_logo = strstr($GLOBALS['db']->getOne($sql), 'images');
$smarty->assign('stores_logo', $stores_logo);

if ($_REQUEST['act'] == 'list') {
	store_priv('order_manage');
	$list = store_order_list($ru_id, $store_id);
	$smarty->assign('order_list', $list['orders']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$order_type = isset($_REQUEST['order_type']) ? $_REQUEST['order_type'] : '-1';
	$smarty->assign('order_type', $order_type);
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->assign('page_title', $_LANG['store_order']);
	$smarty->display('order_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$list = store_order_list($ru_id, $store_id);
	$smarty->assign('order_list', $list['orders']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('order_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'deal_store_order') {
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
	$operate = empty($_REQUEST['operate']) ? '' : trim($_REQUEST['operate']);
	$store_order_info = get_store_order_info($id);
	$order_id = $store_order_info['order_id'];
	$order_info = order_info($order_id);

	if ($operate == 'grab_order') {
		$info = array('country' => $order_info['country'], 'province' => $order_info['province'], 'city' => $order_info['city'], 'district' => $order_info['district']);
		$smarty->assign('complete_user_address', get_complete_address($info) . ' ' . $order_info['address']);
	}

	if ($operate == 'delivery') {
		$info = array('country' => $order_info['country'], 'province' => $order_info['province'], 'city' => $order_info['city'], 'district' => $order_info['district']);
		$smarty->assign('complete_user_address', get_complete_address($info) . ' ' . $order_info['address']);
		$smarty->assign('order_goods_list', get_order_goods_list($order_id));
		$smarty->assign('store_info', get_store_info($store_id));
	}

	if ($operate == 'pick_goods') {
		$smarty->assign('order_goods_list', get_order_goods_list($order_id));
		$smarty->assign('store_info', get_store_info($store_id));
	}

	if ($operate == 'achieve') {
		$info = array('country' => $order_info['country'], 'province' => $order_info['province'], 'city' => $order_info['city'], 'district' => $order_info['district']);
		$smarty->assign('complete_user_address', get_complete_address($info) . ' ' . $order_info['address']);
		$smarty->assign('order_goods_list', get_order_goods_list($order_id));
		$smarty->assign('store_info', get_store_info($store_id));
	}

	if ($operate == 'back') {
		$arr = explode(',', $store_order_info['grab_store_list']);

		foreach ($arr as $key => $val) {
			if ($val == $store_id) {
				unset($arr[$key]);
			}
		}

		$grab_store_list = implode(',', $arr);
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('store_order') . (' SET grab_store_list = \'' . $grab_store_list . '\' WHERE id = ' . $id);
		$GLOBALS['db']->query($sql);
		$sql = 'INSERT INTO dsc_store_back_order (store_id,order_id) VALUES (' . $store_id . ',' . $store_order_info['order_id'] . ') ';
		$GLOBALS['db']->query($sql);
		$result['error'] = 1;
		$result['message'] = '退回抢单成功';
		exit(json_encode($result));
	}

	$smarty->assign('form_action', $operate);
	$smarty->assign('order_info', $order_info);
	$smarty->assign('store_id', $store_id);
	$smarty->assign('order_id', $order_id);
	$smarty->assign('id', $id);
	$result['content'] = $smarty->fetch('deal_store_order.dwt');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'pick_goods') {
	store_priv('order_manage');
	$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
	$order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
	$pick_code = empty($_REQUEST['pick_code']) ? '' : intval($_REQUEST['pick_code']);
	$link[] = array('href' => 'order.php?act=list', 'text' => '门店订单列表');
	$result = array('error' => 0, 'message' => '', 'content' => '');

	if ($pick_code) {
		$store_order_info = get_store_order_info($id);
		$sql = ' SELECT order_status, pay_status, shipping_status, pay_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' ');
		$order_info = $GLOBALS['db']->getRow($sql);
		$payment = payment_info($order_info['pay_id']);
		if ($order_info['pay_status'] != 2 && !in_array($payment['pay_code'], array('bank', 'cod'))) {
			$result['error'] = 0;
			$result['message'] = $_LANG['01_stores_pick_goods'];
		}
		else {
			if ($store_order_info && $store_order_info['pick_code'] == $pick_code) {
				$sql = ' UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET order_status = \'' . OS_SPLITED . '\', pay_status = \'' . PS_PAYED . '\', shipping_status = \'' . SS_RECEIVED . '\' ' . ', shipping_time = \'' . gmtime() . '\', confirm_take_time = \'' . gmtime() . '\' ' . (' WHERE order_id = \'' . $order_id . '\' ');

				if ($GLOBALS['db']->query($sql)) {
					$result['error'] = 1;
					$result['message'] = $_LANG['02_stores_pick_goods'];
				}

				$is_update_sale = is_update_sale($order_id);
				if ($_CFG['sales_volume_time'] == SALES_SHIP && $is_update_sale == 0) {
					get_goods_sale($order_id);
				}

				if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
					change_order_goods_storage($order_id, true, SDT_PAID, 2, 0, $store_id);
				}
			}
			else {
				$result['error'] = 0;
				$result['message'] = $_LANG['03_stores_pick_goods'];
			}
		}
	}
	else {
		$result['error'] = 0;
		$result['message'] = $_LANG['04_stores_pick_goods'];
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'grab_order') {
	store_priv('order_manage');
	$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
	$order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
	$link[] = array('href' => 'order.php?act=list', 'text' => '门店订单列表');
	$store_order_info = get_store_order_info($id);

	if (empty($store_order_info['store_id'])) {
		$has_stock = true;
		$goods_list = get_table_date('order_goods', 'order_id=\'' . $store_order_info['order_id'] . '\'', array('goods_id', 'goods_attr_id', 'goods_number'), 1);

		foreach ($goods_list as $key => $val) {
			if (empty($val['goods_attr_id'])) {
				$goods_number = get_table_date('store_goods', 'store_id=\'' . $store_id . '\' AND goods_id=\'' . $val['goods_id'] . '\'', array('goods_number'), 2);
			}
			else {
				$goods_attr = str_replace(',', '|', $val['goods_attr_id']);
				$goods_number = get_table_date('store_products', 'store_id=\'' . $store_id . '\' AND goods_id=\'' . $val['goods_id'] . '\' AND goods_attr=\'' . $goods_attr . '\'', array('product_number'), 2);
			}

			if ($goods_number < $val['goods_number']) {
				$has_stock = false;
				break;
			}
		}

		if (!$has_stock) {
			make_json_response('', 0, '您的商品库存不足哦，不能抢这个订单', array('url' => 'order.php?act=list'));
		}

		$sql = ' UPDATE ' . $GLOBALS['ecs']->table('store_order') . (' SET store_id = \'' . $store_id . '\' WHERE id = \'' . $id . '\' ');

		if ($GLOBALS['db']->query($sql)) {
			make_json_response('', 1, '碉堡了，您以迅雷不及掩耳的速度抢到这个订单', array('url' => 'order.php?act=list'));
		}
	}
	else {
		make_json_response('', 0, '哎呀手慢了，订单被人抢走了', array('url' => 'order.php?act=list'));
	}
}
else if ($_REQUEST['act'] == 'delivery') {
	store_priv('order_manage');
	$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
	$order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
	$invoice_no = empty($_REQUEST['invoice_no']) ? '' : trim($_REQUEST['invoice_no']);
	define('GMTIME_UTC', gmtime());

	if ($invoice_no) {
		$order_id = intval(trim($order_id));

		if (!empty($order_id)) {
			$order = order_info($order_id);
		}
		else {
			exit('order does not exist');
		}

		if (0 < $order['user_id']) {
			$user = user_info($order['user_id']);

			if (!empty($user)) {
				$order['user_name'] = $user['user_name'];
			}
		}

		$order['order_time'] = local_date($_CFG['time_format'], $order['add_time']);
		$order['invoice_no'] = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $_LANG['ss'][SS_UNSHIPPED] : $order['invoice_no'];
		$order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
		$exist_real_goods = exist_real_goods($order_id);
		$_goods = get_order_goods(array('order_id' => $order['order_id'], 'order_sn' => $order['order_sn']), $store_id);
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
		$sql = 'SELECT stores_user FROM ' . $ecs->table('offline_store') . ' WHERE id = \'store_id\'';
		$stores_user = $delivery['action_user'] = $db->getOne($sql);
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
					if (empty($value['extension_code']) || $value['extension_code'] == 'virtual_card') {
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
							$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . "\r\n                SET send_number = " . $value['goods_number'] . "\r\n                WHERE order_id = '" . $value['order_id'] . "'\r\n                AND goods_id = '" . $pg_value['goods_id'] . '\' ';
							$GLOBALS['db']->query($sql, 'SILENT');
						}
					}
				}
			}
		}
		else {
			make_json_response('', 0, '操作失败', array('url' => 'order.php?act=list'));
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

		if (0 < $delivery_order['user_id']) {
			$user = user_info($delivery_order['user_id']);

			if (!empty($user)) {
				$delivery_order['user_name'] = $user['user_name'];
			}
		}

		$sql = 'SELECT concat(IFNULL(c.region_name, \'\'), \'  \', IFNULL(p.region_name, \'\'), ' . '\'  \', IFNULL(t.region_name, \'\'), \'  \', IFNULL(d.region_name, \'\')) AS region ' . 'FROM ' . $ecs->table('order_info') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS c ON o.country = c.region_id ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS p ON o.province = p.region_id ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS t ON o.city = t.region_id ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS d ON o.district = d.region_id ' . 'WHERE o.order_id = \'' . $delivery_order['order_id'] . '\'';
		$delivery_order['region'] = $db->getOne($sql);
		$order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
		$goods_sql = "SELECT *\r\n                  FROM " . $ecs->table('delivery_goods') . "\r\n                  WHERE delivery_id = '" . $delivery_order['delivery_id'] . '\'';
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

		for ($i = 0; $i < count($delivery_stock_result); $i++) {
			$products = get_warehouse_id_attr_number($delivery_stock_result[$i]['goods_id'], $delivery_stock_result[$i]['goods_attr_id'], $delivery_stock_result[$i]['ru_id'], $delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['model_attr'], $store_id);

			if ($products) {
				$delivery_stock_result[$i]['storage'] = $products['product_number'];
			}

			if (($delivery_stock_result[$i]['storage'] < $delivery_stock_result[$i]['sums'] || $delivery_stock_result[$i]['storage'] <= 0) && ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP || $_CFG['use_storage'] == '0' && $delivery_stock_result[$i]['is_real'] == 0)) {
				make_json_response('', 0, '商品缺货', array('url' => 'order.php?act=list'));
				break;
			}

			if ($delivery_stock_result[$i]['is_real'] == 0) {
				$virtual_goods[] = array('goods_id' => $delivery_stock_result[$i]['goods_id'], 'goods_name' => $delivery_stock_result[$i]['goods_name'], 'num' => $delivery_stock_result[$i]['send_number']);
			}
		}

		if (is_array($virtual_goods) && 0 < count($virtual_goods)) {
			foreach ($virtual_goods as $virtual_value) {
				virtual_card_shipping($virtual_value, $order['order_sn'], $msg, 'split');
			}
		}

		if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP) {
			foreach ($delivery_stock_result as $value) {
				if ($value['is_real'] != 0) {
					if (!empty($value['goods_attr_id'])) {
						$spec = explode(',', $value['goods_attr_id']);

						if (is_spec($spec)) {
							$product_info = get_products_info($value['goods_id'], $spec, $value['warehouse_id'], $value['area_id'], $store_id);
							$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('store_products') . "\r\n                                            SET product_number = product_number - " . $value['sums'] . ("\r\n                                            WHERE store_id = '" . $store_id . '\' AND goods_id =\'') . $value['goods_id'] . '\' AND product_id = ' . $product_info['product_id'];
						}
					}
					else {
						$minus_stock_sql = 'UPDATE ' . $GLOBALS['ecs']->table('store_goods') . "\r\n                                            SET goods_number = goods_number - " . $value['sums'] . "\r\n                                            WHERE goods_id = " . $value['goods_id'] . (' AND store_id = \'' . $store_id . '\'');
					}

					$GLOBALS['db']->query($minus_stock_sql, 'SILENT');
					$logs_other = array('goods_id' => $value['goods_id'], 'order_id' => $value['order_id'], 'use_storage' => $_CFG['stock_dec_time'], 'admin_id' => $_SESSION['store_user_id'], 'number' => '- ' . $value['sums'], 'model_inventory' => $value['model_inventory'], 'model_attr' => $value['model_attr'], 'product_id' => $value['product_id'], 'warehouse_id' => $value['warehouse_id'], 'area_id' => $value['area_id'], 'add_time' => gmtime());
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
			sys_msg('操作失败', 1, $links);
		}

		$order_finish = get_all_delivery_finish($order_id);
		$shipping_status = $order_finish == 1 ? SS_SHIPPED : SS_SHIPPED_PART;
		$arr['shipping_status'] = $shipping_status;
		$arr['shipping_time'] = GMTIME_UTC;
		$arr['invoice_no'] = trim($order['invoice_no'] . '<br>' . $invoice_no, '<br>');
		update_order($order_id, $arr);
		order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $action_note, $stores_user, 1);

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
				$smarty->assign('confirm_url', $ecs->stores_url() . 'user.php?act=order_detail&order_id=' . $order['order_id']);
				$smarty->assign('send_msg_url', $ecs->stores_url() . 'user.php?act=message_list&order_id=' . $order['order_id']);
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
					$shop_name = $GLOBALS['_CFG']['shop_name'];
				}

				$smsParams = array('shop_name' => $shop_name, 'user_name' => $user_info['user_name'], 'consignee' => $order['consignee'], 'order_sn' => $order['order_sn'], 'mobile_phone' => $order['mobile']);

				if ($GLOBALS['_CFG']['sms_type'] == 0) {
					huyi_sms($smsParams, 'sms_order_shipped');
				}
				else if (1 <= $GLOBALS['_CFG']['sms_type']) {
					$result = sms_ali($smsParams, 'sms_order_shipped');

					if ($result) {
						$resp = $GLOBALS['ecs']->ali_yu($result);
					}
					else {
						make_json_response('', 0, '阿里大鱼短信配置异常', array('url' => 'order.php?act=list'));
					}
				}
			}

			$is_update_sale = is_update_sale($order_id);
			if ($_CFG['sales_volume_time'] == SALES_SHIP && $is_update_sale == 0) {
				get_goods_sale($order_id);
			}
		}

		clear_cache_files();
		make_json_response('', 1, '操作成功，发货完成', array('url' => 'order.php?act=list'));
	}
	else {
		make_json_response('', 0, '请输入订单号', array('url' => 'order.php?act=list'));
	}
}
else if ($_REQUEST['act'] == 'operate') {
	$order_id = isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id']) ? trim($_REQUEST['order_id']) : 0;
	$batch = isset($_REQUEST['batch']);

	if (isset($_POST['print'])) {
		if (empty($_POST['order_id'])) {
			sys_msg($_LANG['pls_select_order']);
		}

		$url = 'tp_api.php?act=order_print&order_sn=' . $_POST['order_id'];
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
	else {
		ecs_header("Location: order.php?act=list\n");
	}
}

?>
