<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_str_array1($order)
{
	$arr = array();

	foreach ($order as $key => $row) {
		$row = explode('@', $row);
		$arr[$row[0]] = $row[1];
	}

	$arr = json_encode($arr);
	$arr = json_decode($arr);
	return $arr;
}

function get_str_array2($id)
{
	$arr = array();

	foreach ($id as $key => $row) {
		$row = explode('-', $row);
		$arr[$row[0]] = $row[1];
	}

	return $arr;
}

function get_regions_log($type = 0, $parent = 0)
{
	$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE region_type = \'' . $type . '\' AND parent_id = \'' . $parent . '\'');
	return $GLOBALS['db']->GetAll($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/cls_json.php';
require ROOT_PATH . 'includes/lib_order.php';
$user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if (!empty($user_id)) {
	$sess = $user_id;
}
else {
	$sess = real_cart_mac_ip();
}

$json = new JSON();
$result = array('error' => 0, 'message' => '', 'content' => '');
$is_jsonp = isset($_REQUEST['is_jsonp']) && !empty($_REQUEST['is_jsonp']) ? intval($_REQUEST['is_jsonp']) : 0;

if ($_REQUEST['act'] == 'shipping_type') {
	include_once 'includes/lib_order.php';
	$result = array('error' => 0, 'massage' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);
	$ru_id = isset($_POST['ru_id']) ? intval($_POST['ru_id']) : 0;
	$tmp_shipping_id = isset($_POST['shipping_id']) ? intval($_POST['shipping_id']) : 0;
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$shipping = isset($_REQUEST['shipping']) ? $_REQUEST['shipping'] : '';
	$warehouse_id = !empty($_POST['warehouse_id']) ? intval($_POST['warehouse_id']) : 0;
	$area_id = !empty($_POST['area_id']) ? intval($_POST['area_id']) : 0;
	$shipping_type = isset($_POST['type']) ? intval($_POST['type']) : 0;
	$consignee = get_consignee($user_id);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
	if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
		if (empty($cart_goods)) {
			$result['error'] = 1;
			$result['massage'] = $_LANG['no_goods_in_cart'];
		}
		else if (!check_consignee_info($consignee, $flow_type)) {
			$result['error'] = 2;
			$result['massage'] = $_LANG['no_address'];
		}
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();
		$_SESSION['flow_order'] = $order;
		$_SESSION['merchants_shipping'][$ru_id]['shipping_type'] = $shipping_type;
		$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
		$smarty->assign('cart_goods_number', $cart_goods_number);
		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
		$goods_list = cart_by_favourable($cart_goods_list);
		$smarty->assign('goods_list', $goods_list);

		foreach ($cart_goods_list as $key => $val) {
			if (0 < $tmp_shipping_id && $val['ru_id'] == $ru_id) {
				$cart_goods_list[$key]['tmp_shipping_id'] = $tmp_shipping_id;
			}
		}

		$type = array('type' => 0, 'shipping_list' => $shipping);
		$total = order_fee($order, $cart_goods, $consignee, $type, $_SESSION['cart_value'], 0, $cart_goods_list);
		$smarty->assign('total', $total);

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}

		get_goods_flow_type($_SESSION['cart_value']);
		$smarty->assign('warehouse_id', $warehouse_id);
		$smarty->assign('area_id', $area_id);
		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/order_total.lbi');
	}

	$result['ru_id'] = $ru_id;
	$result['shipping_type'] = $shipping_type;
	$result['shipping_id'] = $tmp_shipping_id;
	$shipping_info = get_shipping_code($tmp_shipping_id);
	$result['shipping_code'] = $shipping_info['shipping_code'];
}
else if ($_REQUEST['act'] == 'edit_invoice') {
	$result = array('error' => 0, 'content' => '');
	$json = new JSON();
	$invoice_type = !empty($_POST['invoice_type']) ? intval($_POST['invoice_type']) : 0;
	$from = !empty($_REQUEST['from']) ? $_REQUEST['from'] : '';
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$consignee = get_consignee($user_id);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
	if (empty($cart_goods) && empty($from) || !check_consignee_info($consignee, $flow_type) && empty($from)) {
		$result['error'] = 1;
		$result['content'] = '购物车商品为空或者收货人信息未填写！';
		exit($json->encode($result));
	}
	else {
		$smarty->assign('config', $_CFG);
		if ((!isset($_CFG['can_invoice']) || $_CFG['can_invoice'] == '1') && isset($_CFG['invoice_content']) && trim($_CFG['invoice_content']) != '' && $flow_type != CART_EXCHANGE_GOODS) {
			$inv_content_list = explode("\n", str_replace("\r", '', $_CFG['invoice_content']));
			$smarty->assign('inv_content_list', $inv_content_list);
			$inv_type_list = array();

			foreach ($_CFG['invoice_type']['type'] as $key => $type) {
				if (!empty($type)) {
					$inv_type_list[$type] = $type . ' [' . floatval($_CFG['invoice_type']['rate'][$key]) . '%]';
				}
			}

			$sql = 'SELECT * FROM ' . $ecs->table('order_invoice') . (' WHERE user_id=\'' . $user_id . '\' LIMIT 10');
			$order_invoice = $db->getAll($sql);
			$smarty->assign('order_invoice', $order_invoice);
			$smarty->assign('inv_type_list', $inv_type_list);
			$smarty->assign('country_list', get_regions());
			$smarty->assign('please_select', '请选择');
			$sql = ' SELECT * FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE user_id=\'' . $user_id . '\' LIMIT 1 ');

			if ($vat_info = $db->getRow($sql)) {
				$smarty->assign('vat_info', $vat_info);
				$smarty->assign('audit_status', $vat_info['audit_status']);
			}
		}

		$smarty->assign('invoice_type', $invoice_type);
		$smarty->assign('user_id', $user_id);
		$result['content'] = $smarty->fetch('library/invoice.lbi');
	}
}
else if ($_REQUEST['act'] == 'update_invoicename') {
	$result = array('error' => 0, 'msg' => '', 'content' => '', 'invoice_id' => 0);
	$json = new JSON();
	$inv_payee = !empty($_POST['inv_payee']) ? json_str_iconv(urldecode($_POST['inv_payee'])) : '';
	$inv_payee = !empty($inv_payee) ? addslashes(trim($inv_payee)) : '';
	$invoice_id = !empty($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
	$tax_id = !empty($_POST['tax_id']) ? intval($_POST['tax_id']) : '';
	if (empty($user_id) || empty($inv_payee)) {
		$result['error'] = 1;
		$result['msg'] = $_LANG['Parameter_error'];
	}
	else if (empty($invoice_id)) {
		$sql = 'SELECT invoice_id FROM ' . $GLOBALS['ecs']->table('order_invoice') . (' WHERE inv_payee = \'' . $inv_payee . '\' AND user_id = \'' . $user_id . '\'');

		if (!$GLOBALS['db']->getOne($sql)) {
			$sql = 'INSERT INTO ' . $ecs->table('order_invoice') . (' (`user_id`, `inv_payee`, `tax_id`) VALUES (\'' . $user_id . '\', \'' . $inv_payee . '\', \'' . $tax_id . '\')');
			$db->query($sql);
			$result['invoice_id'] = $db->insert_id();
		}
		else {
			$result['error'] = 1;
			$result['msg'] = '发票抬头已存在！';
		}
	}
	else {
		$sql = 'UPDATE ' . $ecs->table('order_invoice') . (' SET inv_payee = \'' . $inv_payee . '\', tax_id = \'' . $tax_id . '\' WHERE invoice_id=\'' . $invoice_id . '\'');
		$db->query($sql);
		$result['invoice_id'] = $invoice_id;
	}

	$result['tax_id'] = $tax_id;
}
else if ($_REQUEST['act'] == 'del_invoicename') {
	$result = array('error' => '', 'msg' => '', 'content' => '');
	$json = new JSON();
	$invoice_id = !empty($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;

	if (empty($user_id)) {
		$result['error'] = 1;
		$result['msg'] = $_LANG['Parameter_error'];
	}
	else {
		$sql = 'DELETE FROM ' . $ecs->table('order_invoice') . (' WHERE invoice_id=\'' . $invoice_id . '\'');
		$db->query($sql);
	}
}
else if ($_REQUEST['act'] == 'gotoInvoice') {
	$result = array('error' => '', 'content' => '');
	$json = new JSON();
	$invoice_id = !empty($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
	$inv_content = !empty($_POST['inv_content']) ? json_str_iconv(urldecode($_POST['inv_content'])) : '';
	$store_id = !empty($_POST['store_id']) ? intval($_POST['store_id']) : 0;
	$invoice_type = !empty($_POST['invoice_type']) ? intval($_POST['invoice_type']) : 0;
	$tax_id = !empty($_POST['tax_id']) ? json_str_iconv(urldecode($_POST['tax_id'])) : '';
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$inv_payee = !empty($_POST['inv_payee']) ? json_str_iconv(urldecode($_POST['inv_payee'])) : '';
	$inv_payee = !empty($inv_payee) ? addslashes(trim($inv_payee)) : '';
	$warehouse_id = !empty($_POST['warehouse_id']) ? intval($_POST['warehouse_id']) : 0;
	$area_id = !empty($_POST['area_id']) ? intval($_POST['area_id']) : 0;
	$from = !empty($_REQUEST['from']) ? $_REQUEST['from'] : '';

	if (empty($invoice_id)) {
		$sql = 'SELECT invoice_id FROM ' . $GLOBALS['ecs']->table('order_invoice') . (' WHERE inv_payee = \'' . $inv_payee . '\' AND user_id = \'' . $user_id . '\'');

		if (!$GLOBALS['db']->getOne($sql)) {
			$sql = 'INSERT INTO ' . $ecs->table('order_invoice') . (' (`tax_id`) VALUES (\'' . $tax_id . '\')');
			$db->query($sql);
		}
	}
	else {
		$sql = 'UPDATE ' . $ecs->table('order_invoice') . (' SET tax_id=\'' . $tax_id . '\' WHERE invoice_id=\'' . $invoice_id . '\'');
		$db->query($sql);
	}

	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$consignee = get_consignee($user_id);
	$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
	if (empty($cart_goods) && empty($from) || !check_consignee_info($consignee, $flow_type) && empty($from)) {
		$result['error'] = $_LANG['no_goods_in_cart'];
		exit($json->encode($result));
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();

		if ($inv_content) {
			if (0 < $invoice_id) {
				$sql = 'SELECT inv_payee FROM ' . $ecs->table('order_invoice') . (' WHERE invoice_id=\'' . $invoice_id . '\'');
				$inv_payee = $db->getOne($sql);
			}
			else {
				$inv_payee = '个人';
			}

			$order['tax_id'] = $tax_id;
			$order['need_inv'] = 1;
			$order['inv_type'] = $_CFG['invoice_type']['type'][0];
			$order['inv_payee'] = $inv_payee;
			$order['inv_content'] = $inv_content;
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
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
		$smarty->assign('goods_list', $cart_goods_list);
		$smarty->assign('store_id', $store_id);
		$cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);
		$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
		$smarty->assign('total', $total);

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}

		$result['invoice_type'] = '普通发票（纸质）';

		if ($invoice_type) {
			$result['type'] = 1;
			$result['invoice_type'] = '增值税发票';
		}

		$result['inv_payee'] = $order['inv_payee'];
		$result['inv_content'] = $order['inv_content'];
		$result['tax_id'] = $order['tax_id'];
		$smarty->assign('warehouse_id', $warehouse_id);
		$smarty->assign('area_id', $area_id);
		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/order_total.lbi');
	}
}
else if ($_REQUEST['act'] == 'delete_cart_goods') {
	$cart_value = isset($_REQUEST['cart_value']) ? json_str_iconv($_REQUEST['cart_value']) : '';

	if ($cart_value) {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . (' WHERE rec_id IN(' . $cart_value . ')');
		$GLOBALS['db']->query($sql);
	}

	$result['cart_value'] = $cart_value;
}
else if ($_REQUEST['act'] == 'drop_to_collect') {
	if (0 < $user_id) {
		$cart_value = isset($_REQUEST['cart_value']) ? json_str_iconv($_REQUEST['cart_value']) : '';
		$goods_list = $db->getAll('SELECT goods_id, rec_id FROM ' . $ecs->table('cart') . (' WHERE rec_id IN(' . $cart_value . ')'));

		foreach ($goods_list as $row) {
			$count = $db->getOne('SELECT goods_id FROM ' . $ecs->table('collect_goods') . (' WHERE user_id = \'' . $sess . '\' AND goods_id = \'') . $row['goods_id'] . '\'');

			if (empty($count)) {
				$time = gmtime();
				$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('collect_goods') . ' (user_id, goods_id, add_time)' . ('VALUES (\'' . $sess . '\', \'') . $row['goods_id'] . ('\', \'' . $time . '\')');
				$db->query($sql);
			}

			flow_drop_cart_goods($row['rec_id']);
		}
	}
}
else if ($_REQUEST['act'] == 'user_order_gotopage') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	include_once ROOT_PATH . 'includes/lib_transaction.php';
	$id = !empty($_GET['id']) ? json_str_iconv($_GET['id']) : array();
	$page = isset($_GET['page']) && 0 < intval($_GET['page']) ? intval($_GET['page']) : 1;
	$type = 0;

	if ($id) {
		$id = explode('=', $id);
	}

	$where = '';
	$order = '';

	if (1 < count($id)) {
		$user_id = $id[0];
		$id = explode('|', $id[1]);
		$order = get_str_array1($id);
		$where = get_order_search_keyword($order);
		$left_join = '';

		if (defined('THEME_EXTENSION')) {
			if ($order->idTxt == 'signNum') {
				$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = g.goods_id AND c.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\') = 0 AND og.order_id = oi.order_id ');
			}

			$left_join = ' LEFT JOIN ' . $ecs->table('goods') . ' AS g ON g.goods_id = og.goods_id ';
		}

		$record_count = $db->getAll('SELECT oi.order_id FROM ' . $ecs->table('order_info') . ' as oi' . ' left join ' . $ecs->table('order_goods') . ' as og on oi.order_id = og.order_id' . $left_join . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'' . $show_type . '\' ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi.order_id) = 0 ' . $where . ' group by oi.order_id');
		$record_count = count($record_count);
	}
	else {
		$user_id = $id[0];
		$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('order_info') . ' as oi_1' . (' WHERE oi_1.user_id = \'' . $user_id . '\' and oi_1.is_delete = \'' . $type . '\' ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi_1.order_id) = 0 ');
	}

	$order->action = 'order_list';
	$orders = get_user_orders($user_id, $record_count, $page, $type, $where, $order);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('orders', $orders);
	$smarty->assign('action', $order->action);
	$smarty->assign('open_delivery_time', $GLOBALS['_CFG']['open_delivery_time']);
	$result['content'] = $smarty->fetch('library/user_order_list.lbi');
}
else if ($_REQUEST['act'] == 'user_auction_order_gotopage') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	include_once ROOT_PATH . 'includes/lib_transaction.php';
	$id = !empty($_GET['id']) ? json_str_iconv($_GET['id']) : array();
	$page = isset($_GET['page']) && 0 < intval($_GET['page']) ? intval($_GET['page']) : 1;
	$type = 0;

	if ($id) {
		$id = explode('=', $id);
	}

	$where = '';
	$order = '';

	if (1 < count($id)) {
		$user_id = $id[0];
		$id = explode('|', $id[1]);
		$order = get_str_array1($id);
		$where = get_order_search_keyword($order);
		$left_join = '';

		if (defined('THEME_EXTENSION')) {
			if ($order->idTxt == 'signNum') {
				$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = g.goods_id AND c.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\') = 0 AND og.order_id = oi.order_id ');
			}

			$left_join = ' LEFT JOIN ' . $ecs->table('goods') . ' AS g ON g.goods_id = og.goods_id ';
		}

		$record_count = $db->getAll('SELECT oi.order_id FROM ' . $ecs->table('order_info') . ' as oi' . ' left join ' . $ecs->table('order_goods') . ' as og on oi.order_id = og.order_id' . $left_join . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'' . $show_type . '\' AND oi.extension_code = \'auction\' ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi.order_id) = 0 ' . $where . ' group by oi.order_id');
		$record_count = count($record_count);
	}
	else {
		$user_id = $id[0];
		$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('order_info') . ' as oi_1' . (' WHERE oi_1.user_id = \'' . $user_id . '\' and oi_1.is_delete = \'' . $type . '\' AND oi.extension_code = \'auction\' ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi_1.order_id) = 0 ');
	}

	$order->action = 'auction';
	$orders = get_user_orders($user_id, $record_count, $page, $type, $where, $order);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('orders', $orders);
	$smarty->assign('action', $order->action);
	$smarty->assign('open_delivery_time', $GLOBALS['_CFG']['open_delivery_time']);
	$result['content'] = $smarty->fetch('library/user_order_list.lbi');
}
else if ($_REQUEST['act'] == 'user_auction_gotopage') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	include_once ROOT_PATH . 'includes/lib_transaction.php';
	$id = !empty($_GET['id']) ? json_str_iconv($_GET['id']) : array();
	$page = isset($_GET['page']) && 0 < intval($_GET['page']) ? intval($_GET['page']) : 1;
	$type = 0;

	if ($id) {
		$id = explode('=', $id);
	}

	$where = '';
	$auction = '';
	$user_id = $id[0];
	$id = explode('|', $id[1]);
	$auction = get_str_array1($id);
	$where = get_auction_search_keyword($auction);
	$all_auction = get_all_auction($user_id, $where);
	$order->action = 'auction_list';
	$auction_list = get_auction_list($user_id, $all_auction, $page, $where, $auction);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('auction_list', $auction_list);
	$smarty->assign('action', $order->action);
	$result['content'] = $smarty->fetch('library/user_auction_list.lbi');
}
else if ($_REQUEST['act'] == 'user_snatch_gotopage') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	include_once ROOT_PATH . 'includes/lib_transaction.php';
	$id = !empty($_GET['id']) ? json_str_iconv($_GET['id']) : array();
	$page = isset($_GET['page']) && 0 < intval($_GET['page']) ? intval($_GET['page']) : 1;
	$type = 0;

	if ($id) {
		$id = explode('=', $id);
	}

	$where = '';
	$snatch = '';
	$user_id = $id[0];
	$id = explode('|', $id[1]);
	$snatch = get_str_array1($id);
	$where = get_snatch_search_keyword($snatch);
	$all_snatch = get_all_snatch($user_id, $where);
	$order->action = 'snatch_list';
	$snatch_list = get_snatch_list($user_id, $all_snatch, $page, $where, $snatch);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('snatch_list', $snatch_list);
	$smarty->assign('action', $order->action);
	$result['content'] = $smarty->fetch('library/user_snatch_list.lbi');
}
else if ($_REQUEST['act'] == 'user_inv_gotopage') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	include_once ROOT_PATH . 'includes/lib_transaction.php';
	$id = !empty($_GET['id']) ? json_str_iconv($_GET['id']) : array();
	$page = isset($_GET['page']) && 0 < intval($_GET['page']) ? intval($_GET['page']) : 1;
	$type = 0;

	if ($id) {
		$id = explode('=', $id);
	}

	$where = '';
	$order = '';

	if (1 < count($id)) {
		$user_id = $id[0];
		$id = explode('|', $id[1]);
		$order = get_str_array1($id);
		$where = get_order_search_keyword($order);
		$left_join = '';

		if (defined('THEME_EXTENSION')) {
			if ($order->idTxt == 'signNum') {
				$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = g.goods_id AND c.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\') = 0 AND og.order_id = oi.order_id ');
			}

			$left_join = ' LEFT JOIN ' . $ecs->table('goods') . ' AS g ON g.goods_id = og.goods_id ';
		}

		$record_count = $db->getAll('SELECT oi.order_id FROM ' . $ecs->table('order_info') . ' as oi' . ' left join ' . $ecs->table('order_goods') . ' as og on oi.order_id = og.order_id' . $left_join . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'' . $show_type . '\' ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi.order_id) = 0 ' . $where . ' group by oi.order_id');
		$record_count = count($record_count);
	}
	else {
		$user_id = $id[0];
		$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('order_info') . ' as oi_1' . (' WHERE oi_1.user_id = \'' . $user_id . '\' and oi_1.is_delete = \'' . $type . '\' ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi_1.order_id) = 0 ');
	}

	$order->action = 'invoice';
	$invoice_list = invoice_list($user_id, $record_count, $page);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('invoice_list', $invoice_list);
	$smarty->assign('action', $order->action);
	$smarty->assign('open_delivery_time', $GLOBALS['_CFG']['open_delivery_time']);
	$result['content'] = $smarty->fetch('library/user_inv_list.lbi');
}
else if ($_REQUEST['act'] == 'store_shop_gotoPage') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/search.php';
	$smarty->assign('lang', $_LANG);
	$id = !empty($_GET['id']) ? json_str_iconv($_GET['id']) : array();
	$page = isset($_GET['page']) && 0 < intval($_GET['page']) ? intval($_GET['page']) : 1;
	$type = isset($_GET['type']) && 0 < intval($_GET['type']) ? intval($_GET['type']) : 0;
	$libType = isset($_GET['libType']) && 0 < intval($_GET['libType']) ? intval($_GET['libType']) : 0;

	if ($libType == 1) {
		$size = 10;
	}
	else {
		$size = 16;
	}

	$sort = 'shop_id';
	$order = 'DESC';
	$keywords = '';
	$region_id = '';
	$area_id = '';
	$store_province = '';
	$store_city = '';
	$store_district = '';

	if ($id) {
		$id = explode('|', $id);
		$id = get_str_array2($id);

		if ($id) {
			$id = get_request_filter($id, 2);
		}

		$sort = isset($id['sort']) && !empty($id['sort']) ? addslashes_deep($id['sort']) : 'shop_id';
		$order = isset($id['order']) && !empty($id['order']) ? addslashes_deep($id['order']) : 'DESC';
		$keywords = isset($id['keywords']) && !empty($id['keywords']) ? addslashes_deep($id['keywords']) : '';
		$region_id = isset($id['region_id']) && !empty($id['region_id']) ? intval($id['region_id']) : '';
		$area_id = isset($id['area_id']) && !empty($id['area_id']) ? intval($id['area_id']) : '';
		$store_province = isset($id['store_province']) && !empty($id['store_province']) ? intval($id['store_province']) : '';
		$store_city = isset($id['store_city']) && !empty($id['store_city']) ? intval($id['store_city']) : '';
		$store_district = isset($id['store_district']) && !empty($id['store_district']) ? intval($id['store_district']) : '';
		$store_user = isset($id['store_user']) && !empty($id['store_user']) ? addslashes_deep($id['store_user']) : '';
		$count = get_store_shop_count($keywords, $sort, $store_province, $store_city, $store_district, $store_user, $libType);
		$store_shop_list = get_store_shop_list($libType, $keywords, $count, $size, $page, $sort, $order, $region_id, $area_id, $store_province, $store_city, $store_district, $store_user);
		$shop_list = $store_shop_list['shop_list'];
		$smarty->assign('store_shop_list', $shop_list);
		$smarty->assign('pager', $store_shop_list['pager']);
		$smarty->assign('count', $count);
	}
	else {
		$smarty->assign('store_shop_list', array());
		$smarty->assign('pager', '');
		$smarty->assign('count', 0);
	}

	$smarty->assign('size', $size);
	$smarty->assign('user_id', $user_id);

	if ($libType == 1) {
		$result['content'] = $smarty->fetch('library/search_store_shop_list.lbi');
	}
	else {
		$result['content'] = $smarty->fetch('library/store_shop_list.lbi');
		$result['pages'] = $smarty->fetch('library/pages_ajax.lbi');
	}
}
else if ($_REQUEST['act'] == 'wholesale_order_gotopage') {
	include_once ROOT_PATH . 'includes/lib_wholesale.php';
	include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/wholesale.php';
	$id = !empty($_GET['id']) ? json_str_iconv($_GET['id']) : array();
	$page = isset($_GET['page']) && 0 < intval($_GET['page']) ? intval($_GET['page']) : 1;
	$type = 0;

	if ($id) {
		$id = explode('=', $id);
	}

	$user_id = $id[0];
	$order->action = 'wholesale_buy';
	$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('wholesale_order_info') . ' as oi_1' . (' WHERE oi_1.user_id = \'' . $user_id . '\' and oi_1.is_delete = 0 ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('wholesale_order_info') . ' as oi_2 where oi_2.main_order_id = oi_1.order_id) = 0 ');
	$wholesale_orders = get_wholesale_orders($user_id, $record_count, $page);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('orders', $wholesale_orders);
	$smarty->assign('action', $order->action);
	$result['content'] = $smarty->fetch('library/user_wholesale_order_list.lbi');
}
else if ($_REQUEST['act'] == 'getCategoryCallback') {
	$cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$cat_topic_file = 'category_topic' . $cat_id;
	$category_topic = read_static_cache($cat_topic_file);

	if ($category_topic === false) {
		$category_topic = get_category_topic($cat_id);

		if ($category_topic) {
			write_static_cache($cat_topic_file, $category_topic);
		}
	}

	$smarty->assign('category_topic', $category_topic);
	$cat_file = 'category_tree_child' . $cat_id;
	$child_tree = read_static_cache($cat_file);

	if ($child_tree === false) {
		$child_tree = cat_list($cat_id, 1);
		write_static_cache($cat_file, $child_tree);
	}

	$has_child = 0;

	if (!empty($child_tree)) {
		foreach ($child_tree as $k => $v) {
			if (isset($v['child_tree']) && !empty($v['child_tree'])) {
				$has_child = 1;
				break;
			}
		}
	}

	$result['has_child'] = $has_child;
	$smarty->assign('has_child', $has_child);
	$smarty->assign('child_tree', $child_tree);
	$brands_file = 'category_tree_brands' . $cat_id;
	$brands_ad = read_static_cache($brands_file);

	if ($brands_ad === false) {
		$brands_ad = get_category_brands_ad($cat_id);
		write_static_cache($brands_file, $brands_ad);
	}

	$smarty->assign('brands_ad', $brands_ad);
	$result['cat_id'] = $cat_id;
	$result['cat_content'] = $smarty->fetch('library/index_cat_tree.lbi');
}
else if ($_REQUEST['act'] == 'goods_stock_exhausted') {
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$rec_number = isset($_REQUEST['rec_number']) ? htmlspecialchars($_REQUEST['rec_number']) : '';
	$warehouse_id = isset($_REQUEST['warehouse_id']) && !empty($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$store_id = isset($_REQUEST['store_id']) && !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
	$store_seller = isset($_REQUEST['store_seller']) && !empty($_REQUEST['store_seller']) ? $_REQUEST['store_seller'] : '';

	if (!empty($rec_number)) {
		$cart_value = get_sc_str_replace($_SESSION['cart_value'], $rec_number, 1);
		$cart_goods_list = cart_goods($flow_type, $rec_number, 1, $warehouse_id, $area_id);
		$cart_goods_list_new = cart_by_favourable($cart_goods_list);
		$GLOBALS['smarty']->assign('goods_list', $cart_goods_list_new);
		$GLOBALS['smarty']->assign('cart_value', $cart_value);
		$GLOBALS['smarty']->assign('store_seller', $store_seller);
		$GLOBALS['smarty']->assign('store_id', $store_id);
		$result['error'] = 1;
		$result['cart_value'] = $cart_value;
		$result['content'] = $GLOBALS['smarty']->fetch('library/goods_stock_exhausted.lbi');
	}
}
else if ($_REQUEST['act'] == 'shipping_prompt') {
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$shipping_prompt = isset($_REQUEST['shipping_prompt']) ? addslashes($_REQUEST['shipping_prompt']) : '';
	$warehouse_id = !empty($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
	$store_seller = !empty($_REQUEST['store_seller']) ? $_REQUEST['store_seller'] : '';

	if ($shipping_prompt) {
		$cart_value = get_sc_str_replace($_SESSION['cart_value'], $shipping_prompt, 1);
		$cart_goods_list = cart_goods($flow_type, $shipping_prompt, 1, $warehouse_id, $area_id);
		$cart_goods_list_new = cart_by_favourable($cart_goods_list);
		$GLOBALS['smarty']->assign('goods_list', $cart_goods_list_new);
		$GLOBALS['smarty']->assign('cart_value', $cart_value);
		$GLOBALS['smarty']->assign('store_seller', $store_seller);
		$GLOBALS['smarty']->assign('store_id', $store_id);
		$result['error'] = 1;
		$result['cart_value'] = $cart_value;
		$result['content'] = $GLOBALS['smarty']->fetch('library/goods_shipping_prompt.lbi');
	}
}
else if ($_REQUEST['act'] == 'ajax_get_spec') {
	$result = array('error' => 0, 'message' => '', 'attr_val' => '');
	$rec_id = isset($_REQUEST['rec_id']) ? intval($_REQUEST['rec_id']) : 0;
	$g_id = isset($_REQUEST['g_id']) ? intval($_REQUEST['g_id']) : 0;
	$g_number = isset($_REQUEST['g_number']) ? intval($_REQUEST['g_number']) : 0;
	$sql = 'select warehouse_id, area_id, area_city from ' . $ecs->table('order_goods') . (' where rec_id = \'' . $rec_id . '\'');
	$order_goods = $db->getRow($sql);
	if ($rec_id == 0 || $g_id == 0) {
		$result['err_msg'] = $_lang['Can_get_attr'];
		$result['err_no'] = 1;
	}
	else {
		$sql = 'select goods_attr_id from ' . $ecs->table('order_goods') . (' where rec_id = \'' . $rec_id . '\'');
		$goods_attr_id = $db->getOne($sql);
		$goods_attr = array();

		if (!empty($goods_attr_id)) {
			$goods_attr = explode(',', $goods_attr_id);
		}

		$properties = get_goods_properties($g_id, $order_goods['warehouse_id'], $order_goods['area_id'], $order_goods['area_city']);
		$spec = $properties['spe'];

		if (!empty($spec)) {
			foreach ($spec as $key => $value) {
				if ($value['values']) {
					$result['spec'] .= '<div class="catt"><span class="type_item">' . $value['name'] . '：</span>';
					$result['spec'] .= '<input type="hidden"  value="" id="attr_' . $key . '" name="attr_val[]"/>';
					$result['spec'] .= '<span class="type_con">';

					foreach ($value['values'] as $k => $v) {
						$arr_class = get_user_attr_checked($goods_attr, $v['id']);

						if ($arr_class['class'] == 'cattsel') {
							$result['attr_val'] .= $key . '_' . $arr_class['attr_val'] . ',';
						}

						if ($value['is_checked'] == 1) {
							$padding = '';

							if (!empty($v['img_flie'])) {
								$img_flie = '<img src="' . $v['img_flie'] . '" width="25" height="25">' . $v['label'];
							}
							else {
								$img_flie = $v['label'];
								$padding = 'style="padding:3px 7px !important;"';
							}

							$result['spec'] .= '<a ' . $padding . ' class="' . $arr_class['class'] . '" title="' . $v['label'] . '[' . $v['format_price'] . ']" onclick="setChange(' . $v['id'] . ' , this , ' . $key . ')" >' . $img_flie . '<i></i></a>';
						}
						else {
							$result['spec'] .= '<a class="' . $arr_class['class'] . '" title="' . $v['label'] . '[' . $v['format_price'] . ']" onclick="setChange(' . $v['id'] . ',this , ' . $key . ')" >' . $v['label'] . '<i></i></a>';
						}
					}
				}

				$result['spec'] .= '</span>';
				$result['spec'] .= '</div>';
			}
		}

		$result['spec'] .= '<div id="back_div">';
		$result['spec'] .= '<div class="type_item">' . $_LANG['exchange_number'] . '</div>';
		$result['spec'] .= '<div class="type_con"><a onclick="buyNumber.minus(this, 2)" href="javascript:;" id="decrease" class="plus_minus">-</a>';
		$result['spec'] .= '<input class="return_num" type="text" id="back_num" value="1" defaultnumber="1" name="attr_num" ' . ' onblur=check_attr_num(this.id,' . $g_number . ',' . $rec_id . ') />';
		$result['spec'] .= '</div><a onclick="buyNumber.plus(this, 2)" href="javascript:;" id="increase" class="plus_minus">+</a>';
		$result['spec'] .= '</div>';
		$result['rec_id'] = $rec_id;

		if (!empty($result['attr_val'])) {
			$result['attr_val'] = substr($result['attr_val'], 0, -1);
		}
	}
}
else if ($_REQUEST['act'] == 'goods_delivery_area') {
	include_once 'includes/lib_transaction.php';
	$_POST['area'] = strip_tags(urldecode($_POST['area']));
	$_POST['area'] = json_str_iconv($_POST['area']);

	if (empty($_POST['area'])) {
		$result['error'] = 1;
		exit($json->encode($result));
	}

	$area = $json->decode($_POST['area']);
	$province_id = !empty($area->province_id) ? intval($area->province_id) : 0;
	$city_id = !empty($area->city_id) ? intval($area->city_id) : 0;
	$district_id = !empty($area->district_id) ? intval($area->district_id) : 0;
	$street_id = !empty($area->street_id) ? intval($area->street_id) : 0;
	$street_list = !empty($area->street_list) ? intval($area->street_list) : 0;
	$goods_id = !empty($area->goods_id) ? intval($area->goods_id) : 0;
	$user_id = !empty($area->user_id) ? intval($area->user_id) : 0;
	$region_id = !empty($area->region_id) ? intval($area->region_id) : 0;
	$area_id = !empty($area->area_id) ? intval($area->area_id) : 0;
	$merchant_id = !empty($area->merchant_id) ? intval($area->merchant_id) : 0;
	$warehouse_type = !empty($area->warehouse_type) ? dsc_addslashes($area->warehouse_type) : '';
	$province_list = get_warehouse_province();
	$city_list = get_region_city_county($province_id);
	$district_list = get_region_city_county($city_id);
	$warehouse_list = get_warehouse_list_goods();
	$warehouse_name = get_warehouse_name_id($region_id);

	foreach ($province_list as $k => $v) {
		$province_list[$k]['choosable'] = true;
	}

	foreach ($city_list as $k => $v) {
		$city_list[$k]['choosable'] = true;
	}

	foreach ($district_list as $k => $v) {
		$district_list[$k]['choosable'] = true;
	}

	$GLOBALS['smarty']->assign('province_list', $province_list);
	$GLOBALS['smarty']->assign('city_list', $city_list);
	$GLOBALS['smarty']->assign('district_list', $district_list);
	$GLOBALS['smarty']->assign('goods_id', $goods_id);
	$GLOBALS['smarty']->assign('warehouse_list', $warehouse_list);
	$GLOBALS['smarty']->assign('warehouse_name', $warehouse_name);
	$GLOBALS['smarty']->assign('region_id', $region_id);
	$GLOBALS['smarty']->assign('area_id', $area_id);
	$GLOBALS['smarty']->assign('user_id', $user_id);
	$GLOBALS['smarty']->assign('area_id', $area_id);
	$GLOBALS['smarty']->assign('merchant_id', $merchant_id);
	$GLOBALS['smarty']->assign('warehouse_type', $warehouse_type);
	$consignee_list = get_new_consignee_list($user_id);
	$GLOBALS['smarty']->assign('consignee_list', $consignee_list);
	$address_id = $db->getOne('SELECT address_id FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_id . '\''));
	$GLOBALS['smarty']->assign('address_id', $address_id);
	$province_row = get_region_info($province_id);
	$city_row = get_region_info($city_id);
	$district_row = get_region_info($district_id);
	$GLOBALS['smarty']->assign('province_row', $province_row);
	$GLOBALS['smarty']->assign('city_row', $city_row);
	$GLOBALS['smarty']->assign('district_row', $district_row);
	$GLOBALS['smarty']->assign('show_warehouse', $GLOBALS['_CFG']['show_warehouse']);
	$result['goods_id'] = $goods_id;
	$result['area'] = array('region_id' => $area->region_id, 'area_id' => $area->area_id, 'province_id' => $area->province_id, 'city_id' => $area->city_id, 'district_id' => $area->district_id, 'street_id' => $area->street_id, 'street_list' => $area->street_list);

	if (defined('THEME_EXTENSION')) {
		$is_theme = 1;
	}
	else {
		$is_theme = 0;
	}

	$result['is_theme'] = $is_theme;
	$result['content'] = $GLOBALS['smarty']->fetch('library/goods_delivery_area.lbi');
	$result['warehouse_content'] = $GLOBALS['smarty']->fetch('library/goods_warehouse.lbi');
}
else if ($_REQUEST['act'] == 'user_area_shipping') {
	$_POST['area'] = strip_tags(urldecode($_POST['area']));
	$_POST['area'] = json_str_iconv($_POST['area']);

	if (empty($_POST['area'])) {
		$result['error'] = 1;
		exit($json->encode($result));
	}

	$area = $json->decode($_POST['area']);
	$goods_id = $area->goods_id;
	$province_id = $area->province_id;
	$city_id = $area->city_id;
	$district_id = $area->district_id;
	$street_id = $area->street_id;
	$street_list = $area->street_list;
	$region_id = $area->region_id;
	$area_id = $area->area_id;
	$region = array(1, $province_id, $city_id, $district_id, $street_id, $street_list);
	$shippingFee = goodsShippingFee($goods_id, $region_id, $area_id, $region);
	$smarty->assign('shippingFee', $shippingFee);
	$sql = 'SELECT is_shipping FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\'');
	$is_shipping = $GLOBALS['db']->getOne($sql, true);
	$smarty->assign('is_shipping', $is_shipping);
	$result['content'] = $GLOBALS['smarty']->fetch('library/user_area_shipping.lbi');
}
else if ($_REQUEST['act'] == 'get_store_list') {
	$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$cart_value = !empty($_REQUEST['cart_value']) ? addslashes_deep($_REQUEST['cart_value']) : '';
	$province = isset($_REQUEST['province']) ? intval($_REQUEST['province']) : 0;
	$city = isset($_REQUEST['city']) ? intval($_REQUEST['city']) : 0;
	$district = isset($_REQUEST['district']) ? intval($_REQUEST['district']) : 0;
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
	$spec_arr = isset($_REQUEST['spec_arr']) ? addslashes_deep($_REQUEST['spec_arr']) : '';
	$where = '1';

	if (0 < $goods_id) {
		$where = 's.goods_id = \'' . $goods_id . '\'';
	}
	else if ($cart_value) {
		$sql = 'SELECT goods_id FROM' . $ecs->table('cart') . (' WHERE rec_id in (' . $cart_value . ')');
		$goods_id = arr_foreach($db->getAll($sql));
		$where = 's.goods_id ' . db_create_in($goods_id);
	}

	if (0 < $province) {
		$where .= ' AND o.province = ' . $province;
	}

	if (0 < $city) {
		$where .= ' AND o.city = ' . $city;
	}

	if (0 < $district) {
		$where .= ' AND o.district = ' . $district;
	}

	$sql = 'SELECT o.id,o.stores_name,s.goods_id,o.stores_address,o.stores_traffic_line,o.ru_id ,p.region_name as province ,s.goods_number ,o.stores_address, o.stores_tel, o.stores_opening_hours, ' . 'c.region_name as city ,d.region_name as district FROM ' . $ecs->table('offline_store') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('store_goods') . ' AS s ON o.id = s.store_id ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS p ON p.region_id = o.province ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS c ON c.region_id = o.city ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS d ON d.region_id = o.district ' . ('WHERE ' . $where . '  AND o.is_confirm=1 GROUP BY o.id');
	$seller_store = $db->getAll($sql);
	$is_spec = explode(',', $spec_arr);
	$html = '';
	$result['error'] = 0;

	if (!empty($seller_store)) {
		foreach ($seller_store as $k => $v) {
			if (is_spec($is_spec) == true) {
				$products = get_warehouse_id_attr_number($v['goods_id'], $spec_arr, $v['ru_id'], 0, 0, '', $v['id']);
				$v['goods_number'] = $products['product_number'];
			}

			if (0 < $v['goods_number'] || $cart_value) {
				if ($type == 'flow') {
					if (defined('THEME_EXTENSION')) {
						$html .= '<div class="option" data-value="' . $v['id'] . '" onClick="edit_offline_store(this);">' . $v['stores_name'] . '</div>';
					}
					else {
						$html .= '<option value="' . $v['id'] . '">' . $v['stores_name'] . '</option>';
					}
				}
				else {
					$addtocart = 'addToCart(' . $goods_id . ',0,0,\'\',\'\',' . $v['id'] . ')';
					$html .= '<li><div class="td s_title"><i></i>' . $v['stores_name'] . '</div><div class="td s_address">' . $_LANG['address'] . '[' . $v['province'] . '&nbsp;' . $v['city'] . '&nbsp;' . $v['district'] . ']&nbsp;' . $v['stores_address'] . '</div><div class="td handle"><a  href="javascript:bool=2;' . $addtocart . '" >' . $_LANG['Since_lift_new'] . '</a></div></li>';
				}
			}
		}

		$result['error'] = 1;
	}

	if ($type == 'flow') {
		if (defined('THEME_EXTENSION')) {
			$result['content'] = '<div class="option" data-value="" onClick="edit_offline_store(this);">' . $_LANG['Please_store'] . '</div>' . $html . '</div>';
		}
		else {
			$result['content'] = '<select onchange="edit_offline_store(this)"><option value="">' . $_LANG['Please_store'] . '</option>' . $html . '</select>';
		}
	}
	else if ($type == 'store_select_shop') {
		$smarty->assign('area_position_list', $seller_store);
		$result['content'] = $GLOBALS['smarty']->fetch('library/store_select_shop.lbi');
	}
	else {
		$result['content'] = $html;
	}
}
else if ($_REQUEST['act'] == 'all_stores_list') {
	$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$spec_arr = isset($_REQUEST['spec_arr']) ? $_REQUEST['spec_arr'] : '';

	if (0 < $goods_id) {
		$where = 's.goods_id = \'' . $goods_id . '\'';
	}

	$sql = 'SELECT o.id,o.stores_name,s.goods_id,o.stores_address,o.stores_traffic_line,o.ru_id ,p.region_name as province ,s.goods_number ,' . 'c.region_name as city ,d.region_name as district FROM ' . $ecs->table('offline_store') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('store_goods') . ' AS s ON o.id = s.store_id ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS p ON p.region_id = o.province ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS c ON c.region_id = o.city ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS d ON d.region_id = o.district ' . ('WHERE o.is_confirm=1 AND s.goods_id =\'' . $goods_id . '\'  GROUP BY o.id');
	$seller_store = $db->getAll($sql);
	$is_spec = explode(',', $spec_arr);
	$html = '';
	$result['error'] = 0;

	if (!empty($seller_store)) {
		foreach ($seller_store as $k => $v) {
			if (is_spec($is_spec) == true) {
				$products = get_warehouse_id_attr_number($v['goods_id'], $spec_arr, $v['ru_id'], 0, 0, '', $v['id']);
				$v['goods_number'] = $products['product_number'];
			}

			if (0 < $v['goods_number'] || $cart_value) {
				if ($type == 'flow') {
					$html .= '<option value="' . $v['id'] . '">' . $v['stores_name'] . '</option>';
				}
				else {
					$addtocart = 'addToCart(' . $goods_id . ',0,0,\'\',\'\',' . $v['id'] . ')';
					$html .= '<li><div class="td s_title"><i></i>' . $v['stores_name'] . '</div><div class="td s_address">' . $_LANG['address'] . '[' . $v['province'] . '&nbsp;' . $v['city'] . '&nbsp;' . $v['district'] . ']&nbsp;' . $v['stores_address'] . '</div><div class="td handle"><a  href="javascript:bool=2;' . $addtocart . '" >' . $_LANG['Since_lift_new'] . '</a></div></li>';
				}
			}
		}

		$result['error'] = 1;
	}

	if ($type == 'flow') {
		$result['content'] = '<select onchange="edit_offline_store(this)"><option value="">' . $_LANG['Please_store'] . '</option>' . $html . '</select>';
	}
	else {
		$result['content'] = $html;
	}
}
else if ($_REQUEST['act'] == 'getInfo') {
	$json = new JSON();
	$result = array('error' => 0, 'message' => '');
	$attr_id = !empty($_REQUEST['attr_id']) ? intval($_REQUEST['attr_id']) : 0;
	$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$sql = 'SELECT attr_gallery_flie FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_attr_id = \'' . $attr_id . '\' and goods_id = \'' . $goods_id . '\'');
	$row = $db->getRow($sql);
	$result['t_img'] = !empty($row['attr_gallery_flie']) ? get_image_path(0, $row['attr_gallery_flie']) : '';
}
else if ($_REQUEST['act'] == 'price_notice') {
	$result = array('msg' => '', 'status' => '');
	$goods_id = isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$email = isset($_REQUEST['email']) ? trim($_REQUEST['email']) : '';
	$cellphone = isset($_REQUEST['cellphone']) ? trim($_REQUEST['cellphone']) : '';
	$hopeDiscount = isset($_REQUEST['hopeDiscount']) ? trim($_REQUEST['hopeDiscount']) : 0;
	$add_time = gmtime();
	if ($user_id && $email) {
		$sql = 'SELECT count(*) FROM ' . $ecs->table('sale_notice') . (' WHERE goods_id = \'' . $goods_id . '\' AND user_id = \'' . $user_id . '\'');
		$one = $db->getOne($sql);

		if ($one) {
			$sql = 'UPDATE ' . $ecs->table('sale_notice') . (' SET cellphone=\'' . $cellphone . '\',email=\'' . $email . '\',hopeDiscount=\'' . $hopeDiscount . '\',add_time=\'' . $add_time . '\' WHERE goods_id=\'' . $goods_id . '\' AND user_id=\'' . $user_id . '\'');
			$db->query($sql);
			$result['msg'] = $_LANG['update_Success'];
		}
		else {
			$sql = 'INSERT INTO ' . $ecs->table('sale_notice') . ' (user_id,goods_id,cellphone,email,hopeDiscount,add_time)' . (' VALUES (\'' . $user_id . '\',\'' . $goods_id . '\',\'' . $cellphone . '\',\'' . $email . '\',\'' . $hopeDiscount . '\',\'' . $add_time . '\')');
			$db->query($sql);
			$result['msg'] = $_LANG['Submit_Success'];
		}

		$result['status'] = 0;
	}
	else {
		$result['msg'] = $_LANG['Submit_fail'];
		$result['status'] = 1;
	}
}
else if ($_REQUEST['act'] == 'getguessYouLike') {
	$result = array('error' => 0, 'content' => '', 'message' => '');
	$goods_ids = isset($_REQUEST['goods_ids']) && !empty($_REQUEST['goods_ids']) ? dsc_addslashes(trim($_REQUEST['goods_ids']), 0) : '';
	$warehouse_id = isset($_REQUEST['warehouse_id']) && !empty($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$area_city = isset($_REQUEST['area_city']) && !empty($_REQUEST['area_city']) ? intval($_REQUEST['area_city']) : 0;
	$type = isset($_REQUEST['type']) && !empty($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
	$goods_list = array();

	if ($goods_ids) {
		$goods_list = get_floor_ajax_goods(0, 0, $warehouse_id, $area_id, $area_city, $goods_ids);
	}

	$smarty->assign('goods_list', $goods_list);
	$smarty->assign('type', $type);
	$result['content'] = $GLOBALS['smarty']->fetch('library/guessYouLike_list.lbi');
}
else if ($_REQUEST['act'] == 'checked_article_cat') {
	$result = array('error' => 0, 'content' => '', 'message' => '');
	$cat_ids = !empty($_REQUEST['cat_ids']) ? trim($_REQUEST['cat_ids']) : '';
	$index_article_arr = array();

	if (!empty($cat_ids)) {
		include_once ROOT_PATH . 'includes/lib_main.php';
		include_once ROOT_PATH . 'includes/lib_article.php';
		$index_article_arr = array();
		$index_article_cat_arr = explode(',', $cat_ids);

		foreach ($index_article_cat_arr as $key => $val) {
			$index_article_arr[] = assign_articles($val, 3);
		}
	}

	$smarty->assign('index_article_cat', $index_article_arr);
	$result['content'] = $GLOBALS['smarty']->fetch('library/index_article_cat.lbi');
}

if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'guess_goods') {
	$result = array('err_msg' => '', 'result' => '');
	$warehouse_id = isset($_REQUEST['warehouse_id']) && !empty($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	if (3 < $page) {
		$page = 1;
	}

	$need_cache = $GLOBALS['smarty']->caching;
	$need_compile = $GLOBALS['smarty']->force_compile;
	$GLOBALS['smarty']->caching = false;
	$GLOBALS['smarty']->force_compile = true;
	$guess_goods = get_guess_goods($user_id, 1, $page, 7, $warehouse_id, $area_id);
	$smarty->assign('guess_goods', $guess_goods);
	$smarty->assign('pager', $pager);
	$result['page'] = $page;
	$result['result'] = $GLOBALS['smarty']->fetch('library/guess_goods_love.lbi');
	$GLOBALS['smarty']->caching = $need_cache;
	$GLOBALS['smarty']->force_compile = $need_compile;
}

if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'in_stock') {
	$res = array('err_msg' => '', 'result' => '', 'qty' => 1);
	clear_cache_files();
	$goods_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
	$province = empty($_REQUEST['province']) ? 1 : intval($_REQUEST['province']);
	$city = empty($_REQUEST['city']) ? 52 : intval($_REQUEST['city']);
	$district = empty($_REQUEST['district']) ? 500 : intval($_REQUEST['district']);
	$d_null = empty($_REQUEST['d_null']) ? 0 : intval($_REQUEST['d_null']);
	$user_id = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
	$user_address = get_user_address_region($user_id);
	$user_address = explode(',', $user_address['region_address']);
	setcookie('province', $province, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('city', $city, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('district', $district, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$regionId = 0;
	setcookie('regionId', $regionId, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('type_province', 0, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('type_city', 0, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('type_district', 0, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$res['d_null'] = $d_null;

	if ($d_null == 0) {
		if (in_array($district, $user_address)) {
			$res['isRegion'] = 1;
		}
		else {
			$res['message'] = $_LANG['Distribution_message'];
			$res['isRegion'] = 88;
		}
	}
	else {
		setcookie('district', '', gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}

	$res['goods_id'] = $goods_id;
	$flow_warehouse = get_warehouse_goods_region($province);
	setcookie('flow_region', $flow_warehouse['region_id'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	exit($json->encode($res));
}
else if ($_REQUEST['act'] == 'cat_store_list') {
	$merchant_id = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
	$cat_list = cat_list(0, 1, 0, 'merchants_category', array(), 0, $merchant_id);
	$smarty->assign('cat_store_list', $cat_list);
	$result['content'] = $smarty->fetch('library/cat_store_list.lbi');
}
else if ($_REQUEST['act'] == 'merchants_article') {
	$result = array('error' => 0, 'content' => '', 'message' => '');
	$json = new JSON();
	$title = isset($_REQUEST['title']) ? trim($_REQUEST['title']) : '';
	$sql = 'SELECT content FROM' . $ecs->table('article') . ' WHERE title = \'' . $title . '\'';
	$article = $db->getOne($sql);

	if ($article) {
		$result['error'] = 1;
		$smarty->assign('article', $article);
		$smarty->assign('act', $_REQUEST['act']);
		$smarty->assign('title', $title);
		$result['content'] = $smarty->fetch('library/dialog.lbi');
	}
	else {
		$result['error'] = 0;
		$result['message'] = $_LANG['merchants_article'];
	}
}
else if ($_REQUEST['act'] == 'getUserInfo') {
	$seckillid = '';
	$_REQUEST['seckillid'] = !empty($_REQUEST['seckillid']) ? stripslashes($_REQUEST['seckillid']) : '';
	$temp = !empty($_REQUEST['temp']) ? trim($_REQUEST['temp']) : '';
	$_REQUEST['seckillid'] = json_str_iconv($_REQUEST['seckillid']);

	if (!empty($_REQUEST['seckillid'])) {
		$seckillid = json_decode($_REQUEST['seckillid'], true);
	}

	$brand_id = !empty($_REQUEST['brand_id']) ? trim($_REQUEST['brand_id']) : '';
	$smarty->assign('user_id', $user_id);
	$smarty->assign('info', get_user_default($user_id));
	$smarty->assign('site_domain', $_CFG['site_domain']);
	$smarty->assign('temp', $temp);

	if ($temp == 'backup_festival_1') {
		$arr['num'] = 29;
	}
	else {
		$arr['num'] = 17;
	}

	$result['brand_list'] = insert_recommend_brands($arr, $brand_id);
	$result['seckill_goods'] = insert_index_seckill_goods($seckillid, $temp);
	$result['content'] = $smarty->fetch('library/user_info.lbi');
}
else if ($_REQUEST['act'] == 'flow_shipping') {
	get_request_filter();
	$rec = !empty($_REQUEST['rec_id']) ? addslashes($_REQUEST['rec_id']) : '';
	$shipping_list = !empty($_REQUEST['shipping_list']) ? addslashes($_REQUEST['shipping_list']) : '';
	$shipping_list = !empty($shipping_list) ? explode(',', $shipping_list) : array();
	$cart_info = array();

	if ($rec) {
		$cart_info = explode(',', $rec);
	}

	$region = array();

	if ($_SESSION['flow_consignee']) {
		$region = array($_SESSION['flow_consignee']['country'], $_SESSION['flow_consignee']['province'], $_SESSION['flow_consignee']['city'], $_SESSION['flow_consignee']['district'], $_SESSION['flow_consignee']['street']);
	}

	$region_id = isset($_COOKIE['area_region']) ? intval($_COOKIE['area_region']) : 0;
	$rec_id = '';
	$arr = array();
	$seller = array();
	$cart_value = '';

	if ($cart_info) {
		foreach ($cart_info as $key => $row) {
			$list = explode('|', $row);
			$arr[$list[0]][$key] = $list[1];
		}

		$shipping_id = 0;

		foreach ($arr as $key => $row) {
			foreach ($shipping_list as $skey => $srow) {
				$srow = explode('-', $srow);

				if ($srow[0] == $key) {
					$shipping_id = $srow[1];
				}
			}

			foreach ($row as $rckey => $rcrow) {
				$list = explode('_', $rcrow);
				$cart_value .= $list[0] . ',';
				if ($list && $list[3]) {
					$trow = get_goods_transport($list[3]);

					if ($list[2] == 2) {
						$shipping_code = get_table_date('shipping', 'shipping_id=\'' . $shipping_id . '\'', array('shipping_code'), 2);
						$seller[$key][$list[1]][$rckey]['seller_id'] = $key;
						$seller[$key][$list[1]][$rckey]['rec_id'] = $list[0];
						$seller[$key][$list[1]][$rckey]['goods_id'] = $list[1];

						if ($shipping_code == 'cac') {
							$district = !empty($_SESSION['flow_consignee']['district']) ? intval($_SESSION['flow_consignee']['district']) : 0;
							$where = 'ar.region_id = \'' . $district . '\'';
							$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('shipping_point') . ' AS sp ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('area_region') . ' AS ar ON ar.shipping_area_id = sp.shipping_area_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('shipping_area') . ' AS sa ON sa.shipping_area_id = sp.shipping_area_id ' . ('WHERE ' . $where . ' LIMIT 1');
							$shipping_count = $db->getOne($sql);
						}
						else {
							$where = ' AND s.shipping_id = \'' . $shipping_id . '\'';

							if ($trow['freight_type'] == 1) {
								$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s, ' . $GLOBALS['ecs']->table('goods_transport_tpl') . ' AS gtt ' . ' WHERE gtt.shipping_id = s.shipping_id' . $where . (' AND s.enabled = 1 AND gtt.user_id = \'' . $key . '\' AND gtt.tid = \'') . $list[3] . '\'' . ' AND (FIND_IN_SET(\'' . $region[1] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[2] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[3] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[4] . '\', gtt.region_id))';
								$shipping_count = $GLOBALS['db']->getOne($sql, true);
							}
							else {
								$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_transport_extend') . ' AS gted ON gted.tid = \'' . $list[3] . ('\' AND gted.ru_id = \'' . $key . '\'') . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_transport_express') . (' AS gte ON gted.tid = gte.tid AND gte.ru_id = \'' . $key . '\'') . ' WHERE FIND_IN_SET(s.shipping_id, gte.shipping_id) ' . $where . ' AND ((FIND_IN_SET(\'' . $region[1] . '\', gted.top_area_id)) OR (FIND_IN_SET(\'' . $region[2] . '\', gted.area_id) OR FIND_IN_SET(\'' . $region[3] . '\', gted.area_id) OR FIND_IN_SET(\'' . $region[4] . '\', gted.area_id)))' . ' GROUP BY s.shipping_id';
								$shipping_count = $GLOBALS['db']->getAll($sql);

								if ($shipping_count) {
									$shipping_count = count($shipping_count);
								}
								else {
									$shipping_count = 0;
								}
							}
						}

						if ($shipping_count) {
							$seller[$key][$list[1]][$rckey]['is_shipping'] = 1;
						}
						else {
							$seller[$key][$list[1]][$rckey]['is_shipping'] = 0;
							$rec_id .= $list[0] . ',';
						}
					}
				}
			}
		}
	}

	if ($rec_id) {
		$rec_id = get_del_str_comma($rec_id);
		$cart_value = get_del_str_comma($cart_value);
		$cart_value = get_sc_str_replace($cart_value, $rec_id, 1);
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$warehouse_id = !empty($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
		$area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
		$store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
		$store_seller = !empty($_REQUEST['store_seller']) ? $_REQUEST['store_seller'] : '';
		$cart_goods_list = cart_goods($flow_type, $rec_id, 1, $warehouse_id, $area_id);
		$cart_goods_list_new = cart_by_favourable($cart_goods_list);
		$GLOBALS['smarty']->assign('goods_list', $cart_goods_list_new);
		$GLOBALS['smarty']->assign('cart_value', $cart_value);
		$GLOBALS['smarty']->assign('store_seller', $store_seller);
		$GLOBALS['smarty']->assign('store_id', $store_id);
		$result['error'] = 1;
		$result['cart_value'] = $cart_value;
		$result['content'] = $GLOBALS['smarty']->fetch('library/goods_shipping_prompt.lbi');
	}
}
else if ($_REQUEST['act'] == 'sdt_place') {
	$cart_value = isset($_REQUEST['cart_value']) && !empty($_REQUEST['cart_value']) ? dsc_addslashes($_REQUEST['cart_value'], 0) : 0;
	$warehouse_id = isset($_REQUEST['warehouse_id']) && !empty($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$store_id = isset($_REQUEST['store_id']) && !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
	$sql = 'SELECT GROUP_CONCAT(goods_id) AS goods_id FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE rec_id ' . db_create_in($cart_value) . ' AND is_real = 1';
	$cart = $GLOBALS['db']->getRow($sql);
	$goods_list = array();
	if ($cart && $cart['goods_id']) {
		$sql = 'SELECT goods_number, model_inventory FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id ' . db_create_in($cart['goods_id']);
		$goods_list = $GLOBALS['db']->getAll($sql);
	}

	if ($goods_list) {
		get_print_r($goods_list);
	}

	$result['cart_value'] = $cart_value;
	$result['warehouse_id'] = $warehouse_id;
	$result['area_id'] = $area_id;
	$result['store_id'] = $store_id;
}
else if ($_REQUEST['act'] == 'get_login_dialog') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	$back_act = !empty($_REQUEST['back_act']) ? trim($_REQUEST['back_act']) : '';
	$dsc_token = get_dsc_token();
	$smarty->assign('dsc_token', $dsc_token);
	$captcha = intval($_CFG['captcha']);
	if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
		$GLOBALS['smarty']->assign('enabled_captcha', 1);
		$GLOBALS['smarty']->assign('rand', mt_rand());
	}

	$website_dir = ROOT_PATH . 'includes/website/config/';
	$website_list = get_dir_file_list($website_dir, 1, '_');

	for ($i = 0; $i < count($website_list); $i++) {
		if ($website_list[$i]['file'] == 'index.htm' || $website_list[$i]['file'] == 'index.html') {
			unset($website_list[$i]);
		}
	}

	$count = !empty($website_list) ? count($website_list) : 0;

	if (file_exists(ROOT_PATH . 'wechat_oauth.php')) {
		$website_list[$count]['web_type'] = 'weixin';
	}

	$smarty->assign('website_list', $website_list);
	$smarty->assign('site_domain', $_CFG['site_domain']);
	$smarty->assign('back_act', $back_act);
	$smarty->assign('user_lang', $_LANG);
	$smarty->assign('is_jsonp', $is_jsonp);
	$result['content'] = $GLOBALS['smarty']->fetch('library/login_dialog_body.lbi');
}
else if ($_REQUEST['act'] == 'del_hometemplates') {
	$code = isset($_REQUEST['suffix']) ? addslashes(trim($_REQUEST['suffix'])) : '';
	dsc_unlink(ROOT_PATH . 'data/sc_file/hometemplates/' . $code . '.php');
}
else if ($_REQUEST['act'] == 'del_topictemplates') {
	$seller_id = isset($_REQUEST['seller_id']) ? addslashes(trim($_REQUEST['seller_id'])) : '';
	$code = isset($_REQUEST['suffix']) ? addslashes(trim($_REQUEST['suffix'])) : '';
	dsc_unlink(ROOT_PATH . 'data/sc_file/topic/topic_' . $seller_id . '/' . $code . '.php');
}
else if ($_REQUEST['act'] == 'del_sellertemplates') {
	$seller_id = isset($_REQUEST['seller_id']) ? addslashes(trim($_REQUEST['seller_id'])) : '';
	$code = isset($_REQUEST['suffix']) ? addslashes(trim($_REQUEST['suffix'])) : '';
	dsc_unlink(ROOT_PATH . 'data/sc_file/sellertemplates/seller_tem_' . $seller_id . '/' . $code . '.php');
}
else if ($_REQUEST['act'] == 'goods_collection') {
	$goods_id = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$result['collect_count'] = get_collect_goods_user_count($goods_id);
	$result['is_collect'] = get_collect_user_goods($goods_id);
}
else if ($_REQUEST['act'] == 'goods_collect_store') {
	$seller_id = isset($_REQUEST['seller_id']) && !empty($_REQUEST['seller_id']) ? intval($_REQUEST['seller_id']) : 0;

	if ($seller_id) {
		$sql = 'SELECT rec_id FROM ' . $ecs->table('collect_store') . (' WHERE user_id = \'' . $user_id . '\' AND ru_id = \'' . $seller_id . '\'');
		$rec_id = $db->getOne($sql, true);

		if (0 < $rec_id) {
			$result['error'] = 1;
		}
		else {
			$result['error'] = 0;
		}
	}
}
else if ($_REQUEST['act'] == 'ajax_send_mail') {
	$send_time = empty($_REQUEST['send_time']) ? '' : trim($_REQUEST['send_time']);
	$order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
	$order_info = get_main_order_info($order_id, 1);
	$ru_id = explode(',', $order_info['all_ruId']['ru_id']);
	$order = order_info($order_id);
	$sql = ' SELECT goods_name, goods_sn FROM ' . $ecs->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' ');
	$goods_list = $db->getAll($sql);
	$sellerId = $ru_id[0];
	$shop_name = get_shop_name($sellerId, 1);

	if ($sellerId == 0) {
		$service_email = $_CFG['service_email'];
	}
	else {
		$sql = 'SELECT mobile, seller_email FROM ' . $ecs->table('seller_shopinfo') . (' WHERE ru_id = \'' . $sellerId . '\'');
		$seller_shopinfo = $db->getOne($sql, true);
		$service_email = isset($seller_shopinfo['seller_email']) && !empty($seller_shopinfo['seller_email']) ? $seller_shopinfo['seller_email'] : '';
	}

	$sql = ' select * from ' . $GLOBALS['ecs']->table('crons') . ' where cron_code=\'auto_sms\' and enable=1 LIMIT 1';
	$auto_sms = $GLOBALS['db']->getRow($sql);

	if (!empty($auto_sms)) {
		$sql = ' INSERT INTO ' . $GLOBALS['ecs']->table('auto_sms') . ' (item_id,item_type,user_id,ru_id,order_id,add_time) ' . ' VALUES ' . '(NULL,2,\'' . $order['user_id'] . '\',\'' . $sellerId . '\',\'' . $order['order_id'] . '\',\'' . gmtime() . '\')';
		$GLOBALS['db']->query($sql);
	}
	else {
		$tpl = get_mail_template('remind_of_new_order');
		$smarty->assign('order', $order);
		$smarty->assign('goods_list', $goods_list);
		$smarty->assign('shop_name', $shop_name);
		$smarty->assign('send_date', local_date($_CFG['time_format'], gmtime()));
		$content = $smarty->fetch('str:' . $tpl['template_content']);
		send_mail($_CFG['shop_name'], $service_email, $tpl['template_subject'], $content, $tpl['is_html']);
	}
}
else if ($_REQUEST['act'] == 'ajax_captcha') {
	$result = true;
	$captcha_str = isset($_REQUEST['captcha']) ? trim($_REQUEST['captcha']) : '';
	$seKey = !empty($_GET['seKey']) ? $_GET['seKey'] : '';
	$verify = new Verify();
	$captcha_code = $verify->check($captcha_str, $seKey, '', 'ajax');

	if (!$captcha_code) {
		$result = false;
	}
}
else if ($_REQUEST['act'] == 'see_more_goods') {
	$goods_id = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$seller_id = isset($_REQUEST['seller_id']) && !empty($_REQUEST['seller_id']) ? intval($_REQUEST['seller_id']) : 0;
	$cat_id = isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$warehouse_id = isset($_REQUEST['warehouse_id']) && !empty($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$see_more_goods = read_static_cache('see_more_goods_' . $cat_id . '_' . $warehouse_id . '_' . $area_id, '/temp/static_caches/');

	if ($see_more_goods === false) {
		$top_cat = get_topparent_cat($cat_id);
		$see_more_goods = get_filter_goods_list(array('cat_ids' => $top_cat['cat_id']), 5, 1, 'click_count', 'DESC', $warehouse_id, $area_id, 'goods');
		write_static_cache('see_more_goods_' . $cat_id . '_' . $warehouse_id . '_' . $area_id, $see_more_goods, '/temp/static_caches/');
	}

	$smarty->assign('see_more_goods', $see_more_goods);
	$result['content'] = $GLOBALS['smarty']->fetch('library/see_more_goods.lbi');
}
else if ($_REQUEST['act'] == 'see_more_presale') {
	$goods_id = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$cat_id = isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$look_top = get_top_presale_goods($goods_id, $cat_id);
	$smarty->assign('look_top', $look_top);
	$result['content'] = $GLOBALS['smarty']->fetch('library/see_more_presale.lbi');
}
else if ($_REQUEST['act'] == 'guess_goods_love') {
	$warehouse_id = isset($_REQUEST['warehouse_id']) && !empty($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$guess_goods = get_guess_goods($user_id, 1, 1, 7, $warehouse_id, $area_id);
	$smarty->assign('guess_goods', $guess_goods);
	$result['content'] = $GLOBALS['smarty']->fetch('library/guess_goods_love.lbi');
}
else if ($_REQUEST['act'] == 'ajax_Homeindex_Bonusadv') {
	require ROOT_PATH . 'includes/lib_visual.php';
	$suffix = !empty($_REQUEST['suffix']) ? trim($_REQUEST['suffix']) : '';
	$type = !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : '';
	$bonusadv = getleft_attr('bonusadv', 0, $suffix, $GLOBALS['_CFG']['template']);

	if ($bonusadv['img_file']) {
		$bonusadv['img_file'] = get_image_path(0, $bonusadv['img_file']);

		if (strpos($bonusadv['img_file'], $_COOKIE['index_img_file']) !== false) {
			if ($_COOKIE['bonusadv'] == 1) {
				$bonusadv['img_file'] = '';
			}
			else if ($bonusadv['img_file']) {
				setcookie('bonusadv', 1, gmtime() + 3600 * 10, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
				setcookie('index_img_file', $bonusadv['img_file'], gmtime() + 3600 * 10, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
			}
		}
		else {
			setcookie('bonusadv', 1, gmtime() + 3600 * 10, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
			setcookie('index_img_file', $bonusadv['img_file'], gmtime() + 3600 * 10, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		}
	}

	if ($bonusadv['img_file']) {
		$ad_child = array();
		$bonusadv['ad_link'] = $bonusadv['fileurl'];
		$bonusadv['ad_code'] = $bonusadv['img_file'];
		$ad_child[] = $bonusadv;
		$smarty->assign('ad_child', $ad_child);
		$result['content'] = $smarty->fetch('library/bonushome_ad.lbi');
	}
	else {
		$result['error'] = 1;
	}
}
else if ($_REQUEST['act'] == 'checked_home_rank') {
	$goodsids = isset($_REQUEST['goodsids']) && !empty($_REQUEST['goodsids']) ? trim($_REQUEST['goodsids']) : '';
	$activitytype = isset($_REQUEST['activitytype']) && !empty($_REQUEST['activitytype']) ? trim($_REQUEST['activitytype']) : 'snatch';
	$warehouse_id = isset($_REQUEST['warehouse_id']) && !empty($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
	$area_city = isset($_REQUEST['area_city']) && !empty($_REQUEST['area_city']) ? intval($_REQUEST['area_city']) : 0;
	$goods_list = array();
	$time = gmtime();
	if ($activitytype == 'is_new' || $activitytype == 'is_best' || $activitytype == 'is_hot') {
		$goods_list = $goods_list = get_floor_ajax_goods(0, 6, $warehouse_id, $area_id, $area_city, $goodsids, 0, $activitytype);
	}
	else {
		$where = ' WHERE 1 ';
		$leftjoin = '';
		$search = '';

		if (!empty($goodsids)) {
			$where .= ' AND g.goods_id' . db_create_in($goodsids);
		}

		$limit = ' LIMIT 6';

		if ($activitytype == 'exchange') {
			$search = ', ga.exchange_integral';
			$leftjoin = ' LEFT JOIN ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS ga ON ga.goods_id=g.goods_id ';
			$where .= ' AND ga.review_status = 3 AND ga.is_exchange = 1 AND g.is_delete = 0';
		}
		else if ($activitytype == 'presale') {
			$search = ',ga.act_id, ga.act_name, ga.end_time, ga.start_time ';
			$leftjoin = ' LEFT JOIN ' . $GLOBALS['ecs']->table('presale_activity') . ' AS ga ON ga.goods_id=g.goods_id ';
			$where .= ' AND ga.review_status = 3 AND ga.start_time <= \'' . $time . '\' AND ga.end_time >= \'' . $time . '\' AND g.goods_id <> \'\' AND ga.is_finished =0';
		}
		else {
			if ($activitytype == 'snatch') {
				$act_type = GAT_SNATCH;
			}
			else if ($activitytype == 'auction') {
				$act_type = GAT_AUCTION;
			}
			else if ($activitytype == 'group_buy') {
				$act_type = GAT_GROUP_BUY;
			}

			$search = ',ga.act_id, ga.act_name, ga.end_time, ga.start_time, ga.ext_info';
			$leftjoin = ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ON ga.goods_id=g.goods_id ';
			$where .= ' AND ga.review_status = 3 AND ga.start_time <= \'' . $time . '\' AND ga.end_time >= \'' . $time . '\' AND g.goods_id <> \'\' AND ga.is_finished =0 AND ga.act_type=' . $act_type;
		}

		$sql = 'SELECT g.promote_start_date, g.promote_end_date, g.promote_price, g.goods_name, g.goods_id, g.goods_thumb, g.shop_price, g.market_price, g.original_img ' . $search . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftjoin . $where . $limit;
		$goods_list = $db->getAll($sql);

		if (!empty($goods_list)) {
			foreach ($goods_list as $key => $val) {
				if (0 < $val['promote_price'] && $val['promote_start_date'] <= $time && $time <= $val['promote_end_date']) {
					$goods_list[$key]['promote_price'] = price_format(bargain_price($val['promote_price'], $val['promote_start_date'], $val['promote_end_date']));
				}
				else {
					$goods_list[$key]['promote_price'] = '';
				}

				$goods_list[$key]['market_price'] = price_format($val['market_price']);
				$goods_list[$key]['shop_price'] = price_format($val['shop_price']);
				$goods_list[$key]['goods_thumb'] = get_image_path($val['goods_id'], $val['goods_thumb']);
				$goods_list[$key]['original_img'] = get_image_path($val['goods_id'], $val['original_img']);

				if ($activitytype == 'snatch') {
					$goods_list[$key]['url'] = build_uri('snatch', array('sid' => $val['act_id']));
				}
				else if ($activitytype == 'auction') {
					$goods_list[$key]['url'] = build_uri('auction', array('auid' => $val['act_id']));
					$ext_info = unserialize($val['ext_info']);
					$auction = array_merge($val, $ext_info);
					$goods_list[$key]['promote_price'] = price_format($auction['start_price']);
				}
				else if ($activitytype == 'group_buy') {
					$ext_info = unserialize($val['ext_info']);
					$group_buy = array_merge($val, $ext_info);
					$goods_list[$key]['promote_price'] = price_format($group_buy['price_ladder'][0]['price']);
					$goods_list[$key]['url'] = build_uri('group_buy', array('gbid' => $val['act_id']));
				}
				else if ($activitytype == 'exchange') {
					$goods_list[$key]['url'] = build_uri('exchange_goods', array('gid' => $val['goods_id']), $val['goods_name']);
					$goods_list[$key]['exchange_integral'] = '积分：' . $val['exchange_integral'];
				}
				else if ($activitytype == 'presale') {
					$goods_list[$key]['url'] = build_uri('presale', array('act' => 'view', 'presaleid' => $val['act_id']), $val['goods_name']);
				}

				$goods_list[$key]['goods_name'] = !empty($val['act_name']) ? $val['act_name'] : $val['goods_name'];
			}
		}
	}

	$smarty->assign('goods_list', $goods_list);
	$smarty->assign('activitytype', $activitytype);
	$smarty->assign('type', 'home_rank');
	$result['content'] = $GLOBALS['smarty']->fetch('library/guessYouLike_list.lbi');
}
else if ($_REQUEST['act'] == 'check_kefu') {
	$result['content'] = is_dir(__DIR__ . '/kefu') ? 1 : 0;
}
else if ($_REQUEST['act'] == 'edit_wholesale_invoice') {
	require_once 'includes/lib_wholesale.php';
	require_once 'includes/lib_suppliers.php';
	$result = array('error' => 0, 'content' => '');
	$json = new JSON();
	$invoice_type = !empty($_POST['invoice_type']) ? intval($_POST['invoice_type']) : 0;
	$from = !empty($_REQUEST['from']) ? $_REQUEST['from'] : '';
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$consignee = get_consignee($user_id);
	$cart_goods = wholesale_cart_goods(0, $_SESSION['cart_value']);
	if (empty($cart_goods) && empty($from) || !check_consignee_info($consignee, $flow_type) && empty($from)) {
		$result['error'] = 1;
		$result['content'] = '购物车商品为空或者收货人信息未填写！';
		exit($json->encode($result));
	}
	else {
		$smarty->assign('config', $_CFG);
		if ((!isset($_CFG['can_invoice']) || $_CFG['can_invoice'] == '1') && isset($_CFG['invoice_content']) && trim($_CFG['invoice_content']) != '' && $flow_type != CART_EXCHANGE_GOODS) {
			$inv_content_list = explode("\n", str_replace("\r", '', $_CFG['invoice_content']));
			$smarty->assign('inv_content_list', $inv_content_list);
			$inv_type_list = array();

			foreach ($_CFG['invoice_type']['type'] as $key => $type) {
				if (!empty($type)) {
					$inv_type_list[$type] = $type . ' [' . floatval($_CFG['invoice_type']['rate'][$key]) . '%]';
				}
			}

			$sql = 'SELECT * FROM ' . $ecs->table('order_invoice') . (' WHERE user_id=\'' . $user_id . '\' LIMIT 10');
			$order_invoice = $db->getAll($sql);
			$smarty->assign('order_invoice', $order_invoice);
			$smarty->assign('inv_type_list', $inv_type_list);
			$smarty->assign('country_list', get_regions());
			$smarty->assign('please_select', '请选择');
			$sql = ' SELECT * FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE user_id=\'' . $user_id . '\' LIMIT 1 ');

			if ($vat_info = $db->getRow($sql)) {
				$smarty->assign('vat_info', $vat_info);
				$smarty->assign('audit_status', $vat_info['audit_status']);
			}
		}

		$smarty->assign('invoice_type', $invoice_type);
		$smarty->assign('user_id', $user_id);
		$result['content'] = $smarty->fetch('library/invoice.lbi');
	}
}
else if ($_REQUEST['act'] == 'wholesale_gotoInvoice') {
	require_once 'includes/lib_wholesale.php';
	require_once 'includes/lib_suppliers.php';
	$result = array('error' => '', 'content' => '');
	$json = new JSON();
	$invoice_id = !empty($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
	$inv_content = !empty($_POST['inv_content']) ? json_str_iconv(urldecode($_POST['inv_content'])) : '';
	$store_id = !empty($_POST['store_id']) ? intval($_POST['store_id']) : 0;
	$invoice_type = !empty($_POST['invoice_type']) ? intval($_POST['invoice_type']) : 0;
	$tax_id = !empty($_POST['tax_id']) ? json_str_iconv(urldecode($_POST['tax_id'])) : '';
	$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
	$tmp_shipping_id_arr = $json->decode($_POST['shipping_id']);
	$inv_payee = !empty($_POST['inv_payee']) ? json_str_iconv(urldecode($_POST['inv_payee'])) : '';
	$inv_payee = !empty($inv_payee) ? addslashes(trim($inv_payee)) : '';
	$warehouse_id = !empty($_POST['warehouse_id']) ? intval($_POST['warehouse_id']) : 0;
	$area_id = !empty($_POST['area_id']) ? intval($_POST['area_id']) : 0;
	$from = !empty($_REQUEST['from']) ? $_REQUEST['from'] : '';

	if (empty($invoice_id)) {
		$sql = 'SELECT invoice_id FROM ' . $GLOBALS['ecs']->table('order_invoice') . (' WHERE inv_payee = \'' . $inv_payee . '\' AND user_id = \'' . $user_id . '\'');

		if (!$GLOBALS['db']->getOne($sql)) {
			$sql = 'INSERT INTO ' . $ecs->table('order_invoice') . (' (`tax_id`) VALUES (\'' . $tax_id . '\')');
			$db->query($sql);
		}
	}
	else {
		$sql = 'UPDATE ' . $ecs->table('order_invoice') . (' SET tax_id=\'' . $tax_id . '\' WHERE invoice_id=\'' . $invoice_id . '\'');
		$db->query($sql);
	}

	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$consignee = get_consignee($user_id);
	$cart_goods = wholesale_cart_goods(0, $_SESSION['cart_value']);
	$smarty->assign('goods_list', $cart_goods);
	if (empty($cart_goods) && empty($from) || !check_consignee_info($consignee, $flow_type) && empty($from)) {
		$result['error'] = $_LANG['no_goods_in_cart'];
		exit($json->encode($result));
	}
	else {
		$smarty->assign('config', $_CFG);
		$order = flow_order_info();

		if ($inv_content) {
			if (0 < $invoice_id) {
				$sql = 'SELECT inv_payee FROM ' . $ecs->table('order_invoice') . (' WHERE invoice_id=\'' . $invoice_id . '\'');
				$inv_payee = $db->getOne($sql);
			}
			else {
				$inv_payee = '个人';
			}

			$order['tax_id'] = $tax_id;
			$order['need_inv'] = 1;
			$order['inv_type'] = $_CFG['invoice_type']['type'][0];
			$order['inv_payee'] = $inv_payee;
			$order['inv_content'] = $inv_content;
		}
		else {
			$order['need_inv'] = 0;
			$order['inv_type'] = '';
			$order['inv_payee'] = '';
			$order['inv_content'] = '';
			$order['tax_id'] = '';
		}

		$consignee['province_name'] = get_goods_region_name($consignee['province']);
		$consignee['city_name'] = get_goods_region_name($consignee['city']);
		$consignee['district_name'] = get_goods_region_name($consignee['district']);
		$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'];
		$smarty->assign('consignee', $consignee);
		$smarty->assign('store_id', $store_id);
		$total = wholesale_cart_info(0, $_SESSION['cart_value']);
		$smarty->assign('total', $total);

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$smarty->assign('is_group_buy', 1);
		}

		$result['invoice_type'] = '普通发票（纸质）';

		if ($invoice_type) {
			$result['type'] = 1;
			$result['invoice_type'] = '增值税发票';
		}

		$result['inv_payee'] = $order['inv_payee'];
		$result['inv_content'] = $order['inv_content'];
		$result['tax_id'] = $order['tax_id'];
		$smarty->assign('warehouse_id', $warehouse_id);
		$smarty->assign('area_id', $area_id);
		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$account_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie('done_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$smarty->assign('sc_guid', $sc_guid);
		$smarty->assign('sc_rand', $sc_rand);
		$result['content'] = $smarty->fetch('library/wholesale_order_total.lbi');
	}
}
else if ($_REQUEST['act'] == 'wholesale_order_gotopage') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	include_once ROOT_PATH . 'includes/lib_transaction.php';
	include_once ROOT_PATH . 'includes/lib_wholesale.php';
	$id = !empty($_GET['id']) ? json_str_iconv($_GET['id']) : array();
	$page = isset($_GET['page']) && 0 < intval($_GET['page']) ? intval($_GET['page']) : 1;
	$type = 0;

	if ($id) {
		$id = explode('=', $id);
	}

	$where = '';
	$order = '';

	if (1 < count($id)) {
		$user_id = $id[0];
		$id = explode('|', $id[1]);
		$order = get_str_array1($id);
		$where = get_order_search_keyword($order);
		$left_join = '';
		$record_count = $db->getAll('SELECT oi.order_id FROM ' . $ecs->table('wholesale_order_info') . ' as oi' . ' left join ' . $ecs->table('order_goods') . ' as og on oi.order_id = og.order_id' . $left_join . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'' . $show_type . '\' ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('wholesale_order_info') . ' as oi_2 where oi_2.main_order_id = oi.order_id) = 0 ' . $where . ' group by oi.order_id');
		$record_count = count($record_count);
	}
	else {
		$user_id = $id[0];
		$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('wholesale_order_info') . ' as oi_1' . (' WHERE oi_1.user_id = \'' . $user_id . '\' and oi_1.is_delete = \'' . $type . '\' ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('wholesale_order_info') . ' as oi_2 where oi_2.main_order_id = oi_1.order_id) = 0 ');
	}

	$order->action = 'order_list';
	$orders = get_wholesale_orders($user_id, $record_count, $page, $where, $order);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('orders', $orders);
	$smarty->assign('action', $order->action);
	$smarty->assign('open_delivery_time', $GLOBALS['_CFG']['open_delivery_time']);
	$result['content'] = $smarty->fetch('library/user_wholesale_order_list.lbi');
}

if ($is_jsonp) {
	echo $_GET['jsoncallback'] . '(' . $json->encode($result) . ')';
}
else {
	echo $json->encode($result);
}

?>
