<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Flow\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	protected $sess_id = '';
	protected $a_sess = '';
	protected $b_sess = '';
	protected $c_sess = '';
	protected $sess_ip = '';
	protected $region_id = 0;
	protected $area_id = 0;

	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/flow.php');
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		$files = array('order', 'clips', 'transaction');
		$this->load_helper($files);
		$this->check_login();

		if (!empty($_SESSION['user_id'])) {
			$this->sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->a_sess = ' a.user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->b_sess = ' b.user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->sess_ip = '';
		}
		else {
			$this->sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->a_sess = ' a.session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->b_sess = ' b.session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->sess_ip = real_cart_mac_ip();
		}

		$area_info = get_area_info($this->province_id);
		$this->area_id = $area_info['region_id'];
		$where = 'regionId = \'' . $this->province_id . '\'';
		$date = array('parent_id');
		$this->region_id = get_table_date('region_warehouse', $where, $date, 2);
		if (isset($_COOKIE['region_id']) && !empty($_COOKIE['region_id'])) {
			$this->region_id = $_COOKIE['region_id'];
		}
	}

	public function actionIndex()
	{
		unset($_SESSION['flow_order']['bonus_id']);
		unset($_SESSION['flow_order']['cou_id']);
		unset($_SESSION['flow_order']['uc_id']);
		unset($_SESSION['flow_order']['vc_id']);
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
		$_SESSION['shipping_type'] = 0;
		$_SESSION['shipping_type_ru_id'] = array();
		$_SESSION['flow_consignee']['point_id'] = array();
		$direct_shopping = isset($_REQUEST['direct_shopping']) ? intval($_REQUEST['direct_shopping']) : $_SESSION['direct_shopping'];
		$cart_value = isset($_REQUEST['cart_value']) ? htmlspecialchars($_REQUEST['cart_value']) : '';
		$store_seller = isset($_REQUEST['store_seller']) ? addslashes($_REQUEST['store_seller']) : '';
		$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;

		if (empty($cart_value)) {
			$cart_value = get_cart_value($flow_type, $store_id);
		}
		else if (count(explode(',', $cart_value)) == 1) {
			$cart_value = intval($cart_value);
		}

		$_SESSION['cart_value'] = $cart_value;

		if (0 < $store_id) {
			$_SESSION['store_id'] = $store_id;
		}
		else {
			unset($_SESSION['store_id']);
		}

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$this->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$this->assign('is_exchange_goods', 1);
		}
		else if ($flow_type == CART_PRESALE_GOODS) {
			$this->assign('is_presale_goods', 1);
		}
		else {
			$_SESSION['flow_order']['extension_code'] = '';
		}

		$sql = 'SELECT COUNT(*) FROM {pre}cart  WHERE ' . $this->sess_id . ('AND parent_id = 0 AND is_gift = 0 AND rec_type = \'' . $flow_type . '\'');

		if ($this->db->getOne($sql) == 0) {
			show_message(L('no_goods_in_cart'), '', url('/'), 'warning');
		}

		if (empty($direct_shopping) && $_SESSION['user_id'] == 0) {
			ecs_header('Location: ' . url('user/login/index'));
			exit();
		}

		$consignee = get_consignee($_SESSION['user_id']);
		if (!check_consignee_info($consignee, $flow_type) && $store_id <= 0) {
			ecs_header('Location: ' . url('address_list'));
			exit();
		}

		$user_address = get_order_user_address_list($_SESSION['user_id']);
		if ($direct_shopping != 1 && !empty($_SESSION['user_id'])) {
			$_SESSION['browse_trace'] = url('cart/index/index');
		}
		else {
			$_SESSION['browse_trace'] = url('flow/index/index');
		}

		if (count($user_address) <= 0 && $direct_shopping != 1 && $store_id <= 0) {
			ecs_header('Location: ' . url('address_list'));
			exit();
		}

		if ($consignee) {
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$street = get_region_name($consignee['street']);
			$consignee['street_name'] = $street['region_name'];
			$consignee['region'] = $consignee['province_name'] . '&nbsp;' . $consignee['city_name'] . '&nbsp;' . $consignee['district_name'] . '&nbsp;' . $consignee['street_name'];
		}

		$default_id = $this->db->getOne('SELECT address_id FROM {pre}users WHERE user_id=\'' . $_SESSION['user_id'] . '\'');

		if ($consignee['address_id'] == $default_id) {
			$this->assign('is_default', '1');
		}

		$_SESSION['flow_consignee'] = $consignee;
		$this->assign('consignee', $consignee);
		$storeinfo = getStore($store_id);

		if (!empty($storeinfo)) {
			$isStoreOrder = 1;
		}
		else {
			$isStoreOrder = 0;
		}

		$cart_goods_list = cart_goods($flow_type, $cart_value, 1, $this->region_id, $this->area_id, $consignee, $store_id);

		if (empty($cart_goods_list)) {
			$this->redirect('/');
		}

		$store_goods_id = '';

		if ($cart_goods_list) {
			foreach ($cart_goods_list as $key => $val) {
				$amount = 0;
				$goods_price_amount = 0;
				$amount += $val['shipping']['shipping_fee'];

				foreach ($val['goods_list'] as $v) {
					$amount += $v['subtotal'];
					$goods_price_amount += $v['subtotal'];

					if ($v['store_id'] == 0) {
						$isStoreOrder = 0;
					}

					$store_goods_id = $v['goods_id'];
				}

				$cart_goods_list[$key]['amount'] = $amount ? price_format($amount, false) : 0;
				$cart_goods_list[$key]['goods_price_amount'] = $goods_price_amount ? price_format($goods_price_amount, false) : 0;
			}
		}

		if (empty($consignee) && !$isStoreOrder) {
			ecs_header('Location: ' . url('address_list'));
			exit();
		}

		$cac_name = dao('shipping')->where(array('shipping_code' => 'cac'))->getField('shipping_name');
		$cac_name = isset($cac_name) ? $cac_name : '门店自提';
		$this->assign('cac_name', $cac_name);
		$this->assign('store_goods_id', $store_goods_id);
		$this->assign('isStoreOrder', $isStoreOrder);
		$this->assign('store', $storeinfo);

		if (0 < $store_id) {
			$sql = 'SELECT store_mobile,take_time FROM {pre}cart WHERE rec_id in (' . $cart_value . ') AND store_id = \'' . $store_id . '\' LIMIT 1';
			$store_cart = $this->db->getRow($sql);

			if (!$store_cart['store_mobile']) {
				$store_cart['store_mobile'] = dao('users')->where(array('user_id' => $_SESSION['user_id']))->getField('mobile_phone');
			}

			$this->assign('store_cart', $store_cart);
		}

		$cart_goods_list_new = cart_by_favourable($cart_goods_list);
		$this->assign('goods_list', $cart_goods_list_new);
		if ($flow_type != CART_GENERAL_GOODS || C('shop.one_step_buy') == '1') {
			$this->assign('allow_edit_cart', 0);
		}
		else {
			$this->assign('allow_edit_cart', 1);
		}

		$this->assign('config', C('shop'));
		$order = flow_order_info();
		$sql = 'SELECT pay_id from {pre}payment where pay_code=\'onlinepay\'';
		$order['pay_id'] = $this->db->getOne($sql);
		$user_id = $_SESSION['user_id'];

		if (0 < $user_id) {
			$user_info = user_info($user_id);

			if (0 < $user_info['user_money']) {
				$this->assign('user_money', $user_info['user_money']);
			}

			$this->assign('user_money', $user_info['user_money']);
		}

		if ($flow_type == CART_GENERAL_GOODS || $flow_type == CART_ONESTEP_GOODS) {
			$discount = compute_discount(3, $cart_value, 0, 0, $flow_type);
			$this->assign('discount', $discount['discount']);
			$favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
			$this->assign('your_discount', sprintf(L('your_discount'), $favour_name, price_format($discount['discount'])));
		}

		$cart_goods = get_new_group_cart_goods($cart_goods_list);
		$total = order_fee($order, $cart_goods, $consignee, 0, $cart_value, 0, $cart_goods_list, 0, 0, $store_id, $store_seller);
		$this->assign('total', $total);
		$this->assign('shopping_money', sprintf(L('shopping_money'), $total['formated_goods_price']));
		$this->assign('market_price_desc', sprintf(L('than_market_price'), $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));
		$days = array();
		$shipping_date_list = dao('shipping_date')->select();
		$shipping_date = array();

		for ($i = 0; $i <= 6; $i++) {
			$year = date('Y-m-d', strtotime(' +' . $i . 'day'));
			$date = date('m月d日', strtotime(' +' . $i . 'day'));
			$shipping_date[$i]['id'] = $i;
			$shipping_date[$i]['name'] = $date . '【周' . transition_date($year) . '】';

			if ($shipping_date_list) {
				foreach ($shipping_date_list as $key => $val) {
					$strtime = strtotime($year . ' ' . $val['end_date']);
					if ($val['select_day'] <= $i && gmtime() + 8 * 3600 <= $strtime) {
						$shipping_date[$i]['child'][$key]['id'] = $val['shipping_date_id'];
						$shipping_date[$i]['child'][$key]['name'] = $val['start_date'] . '-' . $val['end_date'];
					}
				}
			}
		}

		$this->assign('shipping_date', json_encode($shipping_date));
		$district = $_SESSION['flow_consignee']['district'];
		$city = $_SESSION['flow_consignee']['city'];
		$sql = 'SELECT * FROM ' . $this->ecs->table('region') . (' WHERE parent_id = \'' . $city . '\'');
		$district_list = $this->db->getAll($sql);
		$picksite_list = get_self_point($district);
		$this->assign('picksite_list', $picksite_list);
		$this->assign('district_list', $district_list);
		$this->assign('district', $district);
		$this->assign('city', $city);

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
						$this->assign('gb_deposit', $group_buy['deposit']);
					}
				}

				if ($cod) {
					$shipping_area_info = shipping_area_info($order['shipping_id'], $region);
					$cod_fee = $shipping_area_info['pay_fee'];
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

				if ($payment['is_online'] == 1) {
					unset($payment_list[$key]);
				}

				if ($flow_type == CART_EXCHANGE_GOODS || $total['real_goods_count'] == 0) {
					if ($payment['pay_code'] == 'cod') {
						unset($payment_list[$key]);
					}
				}

				if ($payment['is_cod'] == '1') {
					$payment_list[$key]['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment['format_pay_fee'] . '</span>';
				}

				if ($payment['pay_code'] == 'yeepayszx' && 300 < $total['amount']) {
					unset($payment_list[$key]);
				}

				if ($payment['pay_code'] == 'balance') {
					unset($payment_list[$key]);
				}

				if (!file_exists(ADDONS_PATH . 'payment/' . $payment['pay_code'] . '.php')) {
					unset($payment_list[$key]);
				}

				if ($payment['pay_code'] == 'wxpay') {
					if (!is_dir(APP_WECHAT_PATH)) {
						unset($payment_list[$key]);
					}

					if (is_wechat_browser() == false && is_wxh5() == 0) {
						unset($payment_list[$key]);
					}
				}
			}
		}

		$this->assign('payment_list', $payment_list);

		if ($order['pay_id']) {
			$payment_selected = payment_info($order['pay_id']);

			if (file_exists(ADDONS_PATH . 'payment/' . $payment_selected['pay_code'] . '.php')) {
				$payment_selected['format_pay_fee'] = strpos($payment_selected['pay_fee'], '%') !== false ? $payment_selected['pay_fee'] : price_format($payment_selected['pay_fee'], false);
				$this->assign('payment_selected', $payment_selected);
			}
		}

		if (0 < $total['real_goods_count']) {
			$use_package = C('shop.use_package');
			if (!isset($use_package) || $use_package == '1') {
				$pack_list = pack_list();
				$this->assign('pack_list', $pack_list);
			}

			$pack_info = $order['pack_id'] ? pack_info($order['pack_id']) : array();
			$pack_info['format_pack_fee'] = price_format($pack_info['pack_fee'], false);
			$pack_info['format_free_money'] = price_format($pack_info['free_money'], false);
			$this->assign('pack_info', $pack_info);
			$use_card = C('shop.use_card');
			if (!isset($use_card) || $use_card == '1') {
				$this->assign('card_list', card_list());
			}
		}

		$user_info = user_info($_SESSION['user_id']);
		$use_surplus = C('shop.use_surplus');
		if ((!isset($use_surplus) || $use_surplus == '1') && 0 < $_SESSION['user_id'] && 0 < $user_info['user_money']) {
			$this->assign('allow_use_surplus', 1);
			$this->assign('your_surplus', $user_info['user_money']);
		}

		$use_integral = C('shop.use_integral');
		if ((!isset($use_integral) || $use_integral == '1') && 0 < $_SESSION['user_id'] && 0 < $user_info['pay_points'] && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS)) {
			$order_max_integral = flow_available_points($cart_value);
			$this->assign('allow_use_integral', 1);
			$this->assign('order_max_integral', $order_max_integral);
			$this->assign('your_integral', $user_info['pay_points']);
			$integral_scale = C('shop.integral_scale');
			$integral_scale = $integral_scale ? $integral_scale / 100 : 0;
			$integral_money = price_format($order_max_integral * $integral_scale);
			$this->assign('integral_money', $integral_money);
			$this->assign('integral_money_format', price_format($integral_money, false));
		}

		$users_paypwd = dao('users_paypwd')->field('paypwd_id, pay_online, user_surplus, user_point')->where(array('user_id' => $_SESSION['user_id']))->find();
		if (!empty($users_paypwd) || $users_paypwd['use_surplus'] == 1 && 0 < $user_info['pay_points']) {
			$this->assign('allow_users_paypwd', 1);
		}

		$cart_ru_id = '';

		if ($cart_value) {
			$cart_ru_id = get_cart_seller($cart_value);
		}

		$use_bonus = C('shop.use_bonus');
		$this->assign('total_goods_price', $total['goods_price']);
		if ((!isset($use_bonus) || $use_bonus == '1') && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS)) {
			$user_bonus_count = user_bonus($_SESSION['user_id'], $total['goods_price'], $cart_value, 0);
			$this->assign('cart_value', $cart_value);

			if ($order['bonus_id']) {
				$order_bonus = bonus_info($order['bonus_id']);
				$order_bonus['type_money_format'] = price_format($order_bonus['type_money'], false);
				$this->assign('order_bonus', $order_bonus);
			}
			else {
				$order['bonus_id'] = 0;
			}

			$this->assign('allow_use_bonus', 1);
			$this->assign('user_bonus_count', $user_bonus_count);
		}

		$use_value_card = C('shop.use_value_card');
		if ((isset($use_value_card) || $use_value_card == '1') && $flow_type != CART_EXCHANGE_GOODS) {
			$value_card_info = value_card_info($order['vc_id']);
			$value_card_info['type_money_format'] = price_format($value_card_info['card_money'], false);
			$this->assign('value_card_money_format', $value_card_info['type_money_format']);
			$value_card = get_user_value_card($_SESSION['user_id'], $cart_goods, $cart_value);

			if (!empty($value_card)) {
				foreach ($value_card as $key => $val) {
					$value_card[$key]['card_money_formated'] = price_format($val['card_money'], false);
				}

				$this->assign('value_card_list', $value_card);
			}

			if ($value_card && isset($value_card['is_value_cart'])) {
				$value_card = array();
				$this->assign('is_value_cart', 0);
			}
			else {
				$this->assign('is_value_cart', 1);
			}

			$this->assign('allow_use_value_card', 1);
		}
		else {
			$this->assign('allow_use_value_card', 0);
		}

		$use_coupons = C('shop.use_coupons');
		if ((!isset($use_coupons) || $use_coupons == '1') && ($flow_type == CART_GENERAL_GOODS || $flow_type == CART_ONESTEP_GOODS)) {
			$user_coupons_list = get_user_coupons_list($_SESSION['user_id'], true, $total['goods_price'], $cart_goods, true, $cart_ru_id);
			$user_coupons_count = count($user_coupons_list);
			$this->assign('user_coupons', $user_coupons_count);

			if ($order['cou_id']) {
				$order_coupont = get_coupons($order['cou_id'], array('cou_id, cou_money'));
				$order_coupont['type_cou_money'] = price_format($order_coupont['cou_money'], false);
				$this->assign('order_coupont', $order_coupont);
			}
		}

		$use_how_oos = C('shop.use_how_oos');
		if (!isset($use_how_oos) || $use_how_oos == '1') {
			$oos = L('oos');
			if (is_array($oos) && !empty($oos)) {
				$this->assign('how_oos_list', L('oos'));
			}
		}

		$can_invoice = C('shop.can_invoice');
		if ((!isset($can_invoice) || $can_invoice == '1') && isset($GLOBALS['_CFG']['invoice_content']) && trim($GLOBALS['_CFG']['invoice_content']) != '' && $flow_type != CART_EXCHANGE_GOODS) {
			$inv_content_list = explode("\n", str_replace("\r", '', $GLOBALS['_CFG']['invoice_content']));
			$this->assign('inv_content_list', $inv_content_list);
			$inv_type_list = array();
			$invoice_type = C('shop.invoice_type');

			if (is_array($invoice_type)) {
				foreach ($invoice_type['type'] as $key => $type) {
					if (!empty($type)) {
						$inv_type_list[$type] = $type . ' [' . floatval($GLOBALS['_CFG']['invoice_type']['rate'][$key]) . '%]';
					}
				}
			}

			$this->assign('inv_type_list', $inv_type_list);
			$invoice_type = C('shop.invoice_type');
			$order['need_inv'] = 1;
			$order['inv_type'] = $invoice_type['type'][0];
			$order['inv_payee'] = '个人';
			$order['inv_content'] = $inv_content_list[0];
			$sql = 'SELECT * FROM {pre}order_invoice WHERE user_id = \'' . $_SESSION[user_id] . '\' AND tax_id > 0';
			$invoice_list_company = $this->db->getAll($sql);
			$this->assign('invoice_list_company', $invoice_list_company);
		}

		$invoice_list = dao('users_vat_invoices_info')->where(array('user_id' => $_SESSION[user_id], 'audit_status' => 1))->find();
		$invoice_list = !empty($invoice_list) ? $invoice_list['id'] : 0;
		$this->assign('users_vat_invoices_id', $invoice_list);
		$_SESSION['flow_order'] = $order;
		$this->assign('order', $order);
		if (!empty($cart_goods) && empty($store_id)) {
			$store_id = '';

			foreach ($cart_goods as $val) {
				$store_id .= $val['store_id'] . ',';
			}

			$store_id = substr($store_id, 0, -1);
		}

		$this->assign('store_id', $store_id);
		$this->assign('user_info', $user_info);
		$this->assign('page_title', '订单确认');
		$this->display();
	}

	public function actionGetUserBonus()
	{
		$result = array('error' => 0);
		$total_goods_price = I('get.total_goods_price');
		$cart_value = I('get.cart_value');
		$page = 1;
		$size = 100;
		$user_bonus = user_bonus($_SESSION['user_id'], $total_goods_price, $cart_value, $size, ($page - 1) * $size);
		$count = $user_bonus['conut'];
		$user_bonus = $user_bonus['list'];

		if (!empty($user_bonus)) {
			foreach ($user_bonus as $key => $val) {
				$user_bonus[$key]['type_money'] = round($val['type_money']);
				$user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);
				$user_bonus[$key]['use_start_date'] = local_date('Y-m-d', $val['use_start_date']);
				$user_bonus[$key]['use_end_date'] = local_date('Y-m-d', $val['use_end_date']);

				if ($val['usebonus_type'] == 1) {
					$user_bonus[$key]['shop_name'] = '全场通用';
				}
				else if ($val['user_id'] == 0) {
					$user_bonus[$key]['shop_name'] = '';
				}
				else {
					$user_bonus[$key]['shop_name'] = get_shop_name($val['user_id'], 1);
				}
			}

			$this->assign('bonus_num', count($user_bonus));
			$result['bonus_list'] = $user_bonus;
			$result['totalPage'] = 1;
		}

		exit(json_encode($result));
	}

	public function actionGetUserCouon()
	{
		$result = array('error' => 0);
		$total_goods_price = I('get.total_goods_price');
		$cart_value = I('get.cart_value');
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
		$cart_goods = cart_goods($flow_type, $cart_value);
		$cart_ru_id = '';

		if ($cart_value) {
			$cart_ru_id = get_cart_seller($cart_value);
		}

		$consignee = get_consignee($_SESSION['user_id']);
		$lang = L('lang_goods_coupons');
		$user_coupons = get_user_coupons_list($_SESSION['user_id'], true, $total_goods_price, $cart_goods, true, $cart_ru_id);

		if (!empty($user_coupons)) {
			foreach ($user_coupons as $k => $v) {
				$user_coupons[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
				$user_coupons[$k]['cou_type_name'] = $v['cou_type'] == 1 ? L('vouchers_login') : ($v['cou_type'] == 2 ? L('vouchers_shoping') : ($v['cou_type'] == 3 ? L('vouchers_all') : ($v['cou_type'] == 4 ? L('vouchers_user') : ($v['cou_type'] == 5 ? L('vouchers_shipping') : L('unknown')))));

				if ($v['spec_cat']) {
					$user_coupons[$k]['cou_goods_name'] = $lang['is_cate'];
				}
				else if ($v['cou_goods']) {
					$user_coupons[$k]['cou_goods_name'] = $lang['is_goods'];
				}
				else {
					$user_coupons[$k]['cou_goods_name'] = $lang['is_all'];
				}
			}
		}

		$disabled_coupons_list = get_user_coupons_list($_SESSION['user_id'], true, '', false, true, $cart_ru_id, 'cart');

		if (!empty($disabled_coupons_list)) {
			foreach ($disabled_coupons_list as $k => $v) {
				$disabled_coupons_list[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
				$disabled_coupons_list[$k]['cou_type_name'] = $v['cou_type'] == 1 ? L('vouchers_login') : ($v['cou_type'] == 2 ? L('vouchers_shoping') : ($v['cou_type'] == 3 ? L('vouchers_all') : ($v['cou_type'] == 4 ? L('vouchers_user') : ($v['cou_type'] == 5 ? L('vouchers_shipping') : L('unknown')))));

				if ($v['spec_cat']) {
					$disabled_coupons_list[$k]['cou_goods_name'] = $lang['is_cate'];
				}
				else if ($v['cou_goods']) {
					$disabled_coupons_list[$k]['cou_goods_name'] = $lang['is_goods'];
				}
				else {
					$disabled_coupons_list[$k]['cou_goods_name'] = $lang['is_all'];
				}

				$not_freightfree = 0;
				$not_in_categary = 0;
				$not_in_goods = 0;

				if ($v['cou_type'] == 5) {
					$cou_region = get_coupons_region($v['cou_id']);
					$cou_region = !empty($cou_region) ? explode(',', $cou_region) : array();
					if ($cou_region && in_array($consignee['province'], $cou_region)) {
						$not_freightfree = 1;
					}
				}

				foreach ($cart_goods as $g) {
					$res[$g['ru_id']]['order_total'] += $g['goods_price'] * $g['goods_number'];
					$res[$g['ru_id']]['seller_id'] = $g['ru_id'];
					$res[$g['ru_id']]['goods_id'] .= $g['goods_id'] . ',';
					$res[$g['ru_id']]['cat_id'] .= $g['cat_id'] . ',';
				}

				foreach ($res as $key => $row) {
					$row['goods_id'] = get_del_str_comma($row['goods_id']);
					$row['cat_id'] = get_del_str_comma($row['cat_id']);
					$goods_ids = array();
					if (isset($row['goods_id']) && $row['goods_id'] && !is_array($row['goods_id'])) {
						$goods_ids = explode(',', $row['goods_id']);
						$goods_ids = array_unique($goods_ids);
					}

					$goods_cats = array();
					if (isset($row['cat_id']) && $row['cat_id'] && !is_array($row['cat_id'])) {
						$goods_cats = explode(',', $row['cat_id']);
						$goods_cats = array_unique($goods_cats);
					}
				}

				if ($v['spec_cat']) {
					$spec_cat = get_cou_children($v['spec_cat']);
					$spec_cat = explode(',', $spec_cat);

					foreach ($goods_cats as $m => $n) {
						if (!in_array($n, $spec_cat)) {
							$not_in_categary = 1;
						}
					}
				}
				else if ($v['cou_goods']) {
					$cou_goods = explode(',', $v['cou_goods']);

					foreach ($goods_ids as $m => $n) {
						if (!in_array($n, $cou_goods)) {
							$not_in_goods = 1;
						}
					}
				}

				$disabled_coupons_list[$k]['disable_cause'] = '';

				if ($not_freightfree == 1) {
					$disabled_coupons_list[$k]['disable_cause'] .= '【不在免邮地区】';
				}

				if ($not_in_categary == 1) {
					$disabled_coupons_list[$k]['disable_cause'] .= '【不在指定分类】';
				}

				if ($not_in_goods == 1) {
					$disabled_coupons_list[$k]['disable_cause'] .= '【不在指定商品】';
				}

				if (!empty($user_coupons)) {
					foreach ($user_coupons as $uk => $ur) {
						if ($v['cou_id'] == $ur['cou_id']) {
							unset($disabled_coupons_list[$k]);
							continue;
						}
					}
				}
			}
		}

		$result['disabled_coupons_list'] = $disabled_coupons_list;
		$result['user_coupons'] = $user_coupons;
		$result['totalPage'] = 1;
		exit(json_encode($result));
	}

	public function actionGetValueCard()
	{
		$result = array('error' => 0);
		$total_goods_price = I('get.total_goods_price');
		$cart_value = I('get.cart_value');
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
		$cart_goods = cart_goods($flow_type, $cart_value);
		$value_card = get_user_value_card($_SESSION['user_id'], $cart_goods, $cart_value);

		if (!empty($value_card)) {
			foreach ($value_card as $key => $val) {
				$value_card[$key]['card_money_formated'] = price_format($val['card_money'], false);
			}

			$result['value_card'] = $value_card;
			$result['totalPage'] = 1;
		}

		exit(json_encode($result));
	}

	public function actionDone()
	{
		$order_hash = md5(serialize($_POST));

		if (S('order_hash_' . $_SESSION['user_id']) === $order_hash) {
			$this->redirect('user/order/index');
		}
		else {
			S('order_hash_' . $_SESSION['user_id'], $order_hash, 10);
		}

		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
		$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
		$store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
		$sql = 'SELECT COUNT(*) FROM {pre}cart WHERE ' . $this->sess_id . ('AND parent_id = 0 AND is_gift = 0 AND rec_type = \'' . $flow_type . '\' AND rec_id ') . db_create_in($_SESSION['cart_value']) . ' ';

		if ($this->db->getOne($sql) == 0) {
			show_message(L('no_goods_in_cart'), '', url('cart/index/index'), 'warning');
		}

		if (C('shop.use_storage') == '1' && C('shop.stock_dec_time') == SDT_PLACE) {
			$cart_goods_stock = get_cart_goods($_SESSION['cart_value']);
			$_cart_goods_stock = array();

			if (!empty($cart_goods_stock['goods_list'])) {
				foreach ($cart_goods_stock['goods_list'] as $value) {
					foreach ($value['goods_list'] as $value2) {
						$_cart_goods_stock[$value2['rec_id']] = $value2['goods_number'];
					}
				}

				flow_cart_stock($_cart_goods_stock, $store_id);
				unset($cart_goods_stock);
				unset($_cart_goods_stock);
			}
		}

		if (empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0) {
			ecs_header('Location: ' . url('user/login/index'));
			exit();
		}

		$consignee = get_consignee($_SESSION['user_id']);
		if (!check_consignee_info($consignee, $flow_type) && $store_id <= 0) {
			ecs_header('Location: ' . url('address_list'));
			exit();
		}

		$where_flow = '';
		$_POST['how_oos'] = isset($_POST['how_oos']) ? intval($_POST['how_oos']) : 0;
		$_POST['card_message'] = isset($_POST['card_message']) ? compile_str($_POST['card_message']) : '';
		$_POST['inv_type'] = !empty($_POST['inv_type']) ? compile_str($_POST['inv_type']) : '';
		$_POST['inv_payee'] = isset($_POST['inv_payee']) ? compile_str($_POST['inv_payee']) : '';
		$_POST['inv_content'] = isset($_POST['inv_content']) ? compile_str($_POST['inv_content']) : '';
		$msg = I('post.postscript', '', array('htmlspecialchars', 'trim'));
		$ru_id_arr = I('post.ru_id');
		$shipping_arr = I('post.shipping');
		$postscript = '';

		if (1 < count($msg)) {
			$postscript = array();

			foreach ($msg as $k => $v) {
				$postscript[$ru_id_arr[$k]] = $v;
			}
		}
		else {
			$postscript = isset($msg[0]) ? $msg[0] : '';
		}

		$lang_oos = L('oos');
		$how_oos = $lang_oos[$_POST['how_oos']];
		$shipping_type = I('post.shipping_type');
		$shipping = get_order_post_shipping($_POST['shipping'], $_POST['shipping_code'], $_POST['shipping_type'], $_POST['ru_id']);
		$point = I('post.point_id', 0);

		if (is_array($point)) {
			$point = array_filter($point);
		}

		$point_id = 0;
		$shipping_dateStr = '';
		if (is_array($point) && !empty($point)) {
			foreach ($point as $key => $val) {
				if ($shipping_type[$key] == 1) {
					$point_id .= $key . '|' . $val . ',';
				}
			}

			if (is_array(I('post.shipping_dateStr'))) {
				$shipping_dateStr = '';

				foreach (I('post.shipping_dateStr') as $key => $val) {
					if ($shipping_type[$key] == 1) {
						$shipping_dateStr .= $key . '|' . $val . ',';
					}
				}

				if ($point_id && $shipping_dateStr) {
					$point_id = substr($point_id, 0, -1);
					$shipping_dateStr = substr($shipping_dateStr, 0, -1);
				}
			}
		}

		if (count($_POST['shipping']) == 1) {
			$shipping['shipping_id'] = $shipping_arr[0];

			if (is_array($point)) {
				foreach ($point as $key => $val) {
					if ($shipping_type[$key] == 1) {
						$point_id = $val;
					}
				}
			}
			else {
				$point_id = $point;
			}

			if (is_array(I('post.shipping_dateStr'))) {
				foreach (I('post.shipping_dateStr') as $key => $val) {
					if ($shipping_type[$key] == 1) {
						$shipping_dateStr = $val;
					}
				}
			}
			else {
				$shipping_dateStr = I('post.shipping_dateStr');
			}
		}

		$order = array('shipping_id' => empty($shipping['shipping_id']) ? 0 : $shipping['shipping_id'], 'shipping_type' => empty($shipping['shipping_type']) ? 0 : $shipping['shipping_type'], 'shipping_code' => empty($shipping['shipping_code']) ? 0 : $shipping['shipping_code'], 'pay_id' => intval($_POST['payment']), 'pack_id' => isset($_POST['pack']) ? intval($_POST['pack']) : 0, 'card_id' => isset($_POST['card']) ? intval($_POST['card']) : 0, 'card_message' => trim($_POST['card_message']), 'surplus' => isset($_POST['surplus']) ? floatval($_POST['surplus']) : 0, 'integral' => isset($_POST['integral']) ? intval($_POST['integral']) : 0, 'is_surplus' => isset($_POST['is_surplus']) ? intval($_POST['is_surplus']) : 0, 'bonus_id' => I('bonus', 0, 'intval'), 'vc_id' => I('vc_id', 0, 'intval'), 'need_inv' => empty($_POST['need_inv']) ? 0 : 1, 'tax_id' => I('tax_id', 0, 'intval'), 'invoice_id' => I('invoice_id', 0, 'intval'), 'invoice' => I('invoice', 1, 'intval'), 'inv_type' => I('inv_type', 1, 'intval'), 'invoice_type' => I('inv_type', 1, 'intval'), 'inv_payee' => trim($_POST['inv_payee']), 'inv_content' => trim($_POST['inv_content']), 'vat_id' => I('vat_id', 0, 'intval'), 'postscript' => is_array($postscript) ? '' : $postscript, 'how_oos' => isset($how_oos) ? addslashes($how_oos) : '', 'need_insure' => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0, 'user_id' => $_SESSION['user_id'], 'add_time' => gmtime(), 'order_status' => OS_UNCONFIRMED, 'shipping_status' => SS_UNSHIPPED, 'pay_status' => PS_UNPAYED, 'agency_id' => get_agency_by_regions(array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'])), 'point_id' => $point_id, 'shipping_dateStr' => $shipping_dateStr, 'uc_id' => I('uc_id', 0, 'intval'), 'mobile' => isset($_POST['store_mobile']) && !empty($_POST['store_mobile']) ? addslashes(trim($_POST['store_mobile'])) : '');
		if (isset($_SESSION['flow_type']) && $flow_type != CART_GENERAL_GOODS && $flow_type != CART_ONESTEP_GOODS) {
			$order['extension_code'] = $_SESSION['extension_code'];
			$order['extension_id'] = $_SESSION['extension_id'];
		}
		else {
			$order['extension_code'] = '';
			$order['extension_id'] = 0;
		}

		$user_id = $_SESSION['user_id'];

		if (0 < $user_id) {
			$user_info = user_info($user_id);
			$order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);

			if ($order['surplus'] < 0) {
				$order['surplus'] = 0;
			}

			$flow_points = flow_available_points($_SESSION['cart_value']);
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
		else if (isset($_POST['bonus_sn'])) {
			$bonus_sn = trim($_POST['bonus_sn']);
			$bonus = bonus_info(0, $bonus_sn);
			$now = gmtime();
			if (empty($bonus) || 0 < $bonus['user_id'] || 0 < $bonus['order_id'] || cart_amount(true, $flow_type) < $bonus['min_goods_amount'] || $bonus['use_end_date'] < $now) {
			}
			else {
				if (0 < $user_id) {
					$sql = 'UPDATE {pre}user_bonus  SET user_id = \'' . $user_id . '\' WHERE bonus_id = \'' . $bonus['bonus_id'] . '\' LIMIT 1';
					$this->db->query($sql);
				}

				$order['bonus_id'] = $bonus['bonus_id'];
				$order['bonus_sn'] = $bonus_sn;
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
					$sql = ' UPDATE {pre}value_card SET user_id = \'' . $user_id . '\', bind_time = \'' . gmtime() . '\'' . $end_time . (' WHERE vid = \'' . $value_card['vid'] . '\' ');
					$this->db->query($sql);
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

		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id, '', $store_id);

		if (empty($cart_goods_list)) {
			show_message(L('no_goods_in_cart'), L('back_home'), './', 'war ning');
		}

		if (($flow_type == CART_GENERAL_GOODS || $flow_type == CART_ONESTEP_GOODS) && cart_amount(true, CART_GENERAL_GOODS) < C('shop.min_goods_amount')) {
			show_message(sprintf(L('goods_amount_not_enough'), price_format(C('shop.min_goods_amount'), false)));
		}

		foreach ($consignee as $key => $value) {
			if (!is_array($value)) {
				if ($key != 'shipping_dateStr') {
					$order[$key] = addslashes($value);
				}
				else {
					$order[$key] = addslashes($order['shipping_dateStr']);
				}
			}
		}

		$cart_goods = get_new_group_cart_goods($cart_goods_list);

		foreach ($cart_goods as $val) {
			if ($val['is_real']) {
				$is_real_good = 1;
			}
		}

		if (isset($is_real_good)) {
			if (empty($order['shipping_id']) && empty($point_id) && empty($store_id) || empty($order['pay_id'])) {
				show_message('请选择配送方式或者支付方式');
			}
		}

		$pay_paypwd = I('pay_paypwd', '', array('htmlspecialchars', 'trim'));

		if (!empty($pay_paypwd)) {
			$res = dao('users_paypwd')->field('pay_password, ec_salt')->where(array('user_id' => $_SESSION['user_id']))->find();
			$new_password = md5(md5($pay_paypwd) . $res['ec_salt']);

			if ($new_password != $res['pay_password']) {
				show_message('支付密码输入不正确！');
			}
		}

		$post_ru_id = empty($_POST['ru_id']) ? array() : $_POST['ru_id'];

		foreach ($cart_goods_list as $key => $val) {
			foreach ($post_ru_id as $kk => $vv) {
				if ($val['ru_id'] == $vv) {
					$cart_goods_list[$key]['tmp_shipping_id'] = $_POST['shipping'][$kk];
					continue;
				}
			}
		}

		$pay_type = 0;
		$total = order_fee($order, $cart_goods, $consignee, 1, $_SESSION['cart_value'], $pay_type, $cart_goods_list, 0, 0, $store_id);
		$order['bonus'] = $total['bonus'];
		$order['coupons'] = $total['coupons'];
		$order['use_value_card'] = $total['use_value_card'];
		$order['goods_amount'] = $total['goods_price'];
		$order['cost_amount'] = $total['cost_price'] ? $total['cost_price'] : 0;
		$order['discount'] = $total['discount'] ? $total['discount'] : 0;
		$order['surplus'] = $total['surplus'];
		$order['tax'] = $total['tax'];
		$discount_amout = compute_discount_amount($_SESSION['cart_value'], $flow_type);
		$temp_amout = $order['goods_amount'] - $discount_amout;

		if ($temp_amout <= 0) {
			$order['bonus_id'] = 0;
		}

		if (!empty($order['shipping_id'])) {
			if (count($_POST['shipping']) == 1) {
				$shipping = shipping_info($order['shipping_id']);
			}

			$order['shipping_name'] = addslashes($shipping['shipping_name']);
			$order['shipping_code'] = addslashes($shipping['shipping_code']);
		}

		$order['shipping_fee'] = $total['shipping_fee'];
		$order['insure_fee'] = $total['shipping_insure'];

		if (0 < $order['pay_id']) {
			$payment = payment_info($order['pay_id']);
			$order['pay_name'] = addslashes($payment['pay_name']);
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
			$where_flow = '&direct_shopping=' . $_SESSION['direct_shopping'];
		}

		if ($order['is_surplus'] == 1 && 0 < $order['order_amount']) {
			if (0 < $order['surplus']) {
				$order['order_amount'] = $order['order_amount'] + $order['surplus'];
				$order['surplus'] = 0;
			}

			if ($user_info['user_money'] + $user_info['credit_line'] < $order['order_amount']) {
				$order['surplus'] = $user_info['user_money'];
				$order['order_amount'] = $order['order_amount'] - $user_info['user_money'];
			}
			else if ($_SESSION['flow_type'] == CART_PRESALE_GOODS) {
				$order['surplus'] = $order['order_amount'];
				$order['pay_status'] = PS_PAYED_PART;
				$order['order_status'] = OS_CONFIRMED;
				$order['order_amount'] = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['tax'] - $order['discount'] - $order['surplus'];
			}
			else {
				$order['surplus'] = $order['order_amount'];
				$order['order_amount'] = 0;
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
		$integral_scale = C('shop.integral_scale');

		if ($order['extension_code'] == 'exchange_goods') {
			$order['integral_money'] = value_of_integral($total['exchange_integral']);
			$order['integral'] = $total['exchange_integral'];
			$order['goods_amount'] = value_of_integral($total['exchange_integral']);
		}

		$order['from_ad'] = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';
		$order['referer'] = !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : addslashes(L('self_site'));
		$affiliate = unserialize(C('shop.affiliate'));
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
		$user = get_user_info($order['user_id']);
		$order['email'] = $user['email'];
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

		$error_no = 0;

		do {
			$order['order_sn'] = get_order_sn();
			$new_order = $this->db->filter_field('order_info', $order);
			$new_order_id = $this->db->table('order_info')->data($new_order)->add();
			$error_no = $GLOBALS['db']->errno();
			if (0 < $error_no && $error_no != 1062) {
				exit($GLOBALS['db']->errno());
			}
		} while ($error_no == 1062);

		$order['order_id'] = $new_order_id;
		if (is_dir(APP_DRP_PATH) && $order['extension_code'] != 'bargain_buy') {
			$drp_affiliate = get_drp_affiliate_config();
			if (isset($drp_affiliate['on']) && $drp_affiliate['on'] == 1) {
				$sql = 'SELECT u.drp_parent_id FROM {pre}users as u' . ' LEFT JOIN  {pre}drp_shop as ds ON u.drp_parent_id = ds.user_id' . ' WHERE u.user_id = ' . $_SESSION['user_id'] . ' AND ds.audit = 1 AND ds.status = 1';
				$parent_id = $GLOBALS['db']->getOne($sql);

				if ($parent_id) {
					$is_distribution = 1;
				}
				else {
					$is_distribution = 0;
				}
			}

			$goodsIn = '';
			$cartValue = isset($_SESSION['cart_value']) ? $_SESSION['cart_value'] : '';

			if (!empty($cartValue)) {
				$goodsIn = ' and ca.rec_id in(' . $cartValue . ')';
			}

			$sql = 'INSERT INTO ' . $this->ecs->table('order_goods') . '( ' . 'user_id, order_id, goods_id, goods_name, goods_sn, product_id,is_reality,is_return,is_fast, goods_number, market_price, goods_price, commission_rate, ' . 'goods_attr, is_real, extension_code, parent_id, is_gift,freight, tid,shipping_fee, model_attr, goods_attr_id, ru_id, shopping_fee, warehouse_id, area_id, is_distribution, drp_money) ' . (' SELECT \'' . $user_id . '\', \'' . $new_order_id . '\', ca.goods_id, ca.goods_name, ca.goods_sn, ca.product_id,(SELECT is_reality FROM ') . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id),(SELECT is_return FROM ' . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id),(SELECT is_fast FROM ' . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id), ca.goods_number, ca.market_price, ca.goods_price, ca.commission_rate, ca.goods_attr, ' . 'ca.is_real, ca.extension_code, ca.parent_id, ca.is_gift,ca.freight, ca.tid,ca.shipping_fee,ca.model_attr, ca.goods_attr_id, ca.ru_id, ca.shopping_fee, ca.warehouse_id,ca.area_id,' . ('g.is_distribution*\'' . $is_distribution . '\' as is_distribution, ') . ('g.dis_commission*g.is_distribution*ca.goods_price*ca.goods_number/100*\'' . $is_distribution . '\' as drp_money') . ' FROM ' . $this->ecs->table('cart') . ' ca' . ' LEFT JOIN  {pre}goods as g ON ca.goods_id=g.goods_id' . ' WHERE ca.' . $this->sess_id . (' AND is_checked=1  AND ca.rec_type = \'' . $flow_type . '\'') . $goodsIn;
			$this->db->query($sql);
			$drp_money = dao('order_goods')->where(array('order_id' => $order['order_id']))->sum('drp_money');
			$goods = dao('order_goods')->field('goods_name')->where(array('order_id' => $order['order_id']))->find();

			if (0 < $parent_id) {
				$pushData = array(
					'keyword1' => array('value' => $goods['goods_name'], 'color' => '#173177'),
					'keyword2' => array('value' => $drp_money, 'color' => '#173177'),
					'keyword3' => array('value' => date('Y-m-d', gmtime()), 'color' => '#173177'),
					'keyword4' => array('value' => '下单成功', 'color' => '#173177'),
					'remark'   => array('value' => '您可以进入微店中了解更多佣金详情。', 'color' => '#173177')
					);
				$url = __HOST__ . url('drp/user/order');
				push_template('OPENTM206328970', $pushData, $url, $parent_id);
			}
		}
		else {
			$goodsIn = '';
			$cartValue = isset($_SESSION['cart_value']) ? $_SESSION['cart_value'] : '';

			if (!empty($cartValue)) {
				$goodsIn = ' and rec_id in(' . $cartValue . ')';
			}

			$sql = 'INSERT INTO ' . $this->ecs->table('order_goods') . '( ' . 'user_id, order_id, goods_id, goods_name, goods_sn, product_id,is_reality,is_return,is_fast, goods_number, market_price, commission_rate, ' . 'goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, model_attr, goods_attr_id, ru_id, shopping_fee, warehouse_id, area_id, freight, tid, shipping_fee) ' . (' SELECT \'' . $user_id . '\', \'' . $new_order_id . '\', ca.goods_id, ca.goods_name, ca.goods_sn, ca.product_id,(SELECT is_reality FROM ') . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id),(SELECT is_return FROM ' . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id),(SELECT is_fast FROM ' . $GLOBALS['ecs']->table('goods_extend') . ' AS ge WHERE ge.goods_id = ca.goods_id), ca.goods_number, ca.market_price, ca.commission_rate, ' . 'ca.goods_price, ca.goods_attr, ca.is_real, ca.extension_code, ca.parent_id, ca.is_gift, ca.model_attr, ca.goods_attr_id, ca.ru_id, ca.shopping_fee, ca.warehouse_id, ca.area_id, ca.freight, ca.tid, ca.shipping_fee' . ' FROM ' . $this->ecs->table('cart') . ' AS ca' . ' WHERE ' . $this->sess_id . (' AND rec_type = \'' . $flow_type . '\'') . $goodsIn;
			$this->db->query($sql);
		}

		$sql = 'SELECT rec_id,model_attr,product_id FROM ' . $this->ecs->table('order_goods') . (' WHERE order_id = \'' . $new_order_id . '\'');
		$goods_product = $this->db->query($sql);

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

			$sql = 'SELECT product_sn FROM ' . $this->ecs->table($table) . ' WHERE product_id = \'' . $gval['product_id'] . '\'';
			$product_sn = $this->db->getOne($sql);
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . (' SET product_sn = \'' . $product_sn . '\' WHERE rec_id = ') . $gval['rec_id'];
			$this->db->query($sql);
		}

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

		$good_ru_id = !empty($_REQUEST['ru_id']) ? $_REQUEST['ru_id'] : array();
		if (0 < $store_id && $good_ru_id) {
			foreach ($good_ru_id as $v) {
				$pick_code = substr($order['order_sn'], -3) . rand(0, 9) . rand(0, 9) . rand(0, 9);

				if ($stores_sms != 1) {
					$pick_code = '';
				}

				$take_time = I('take_time');
				$store_order = array('order_id' => $new_order_id, 'store_id' => $store_id, 'ru_id' => $v, 'pick_code' => $pick_code, 'take_time' => $take_time);
				dao('store_order')->data($store_order)->add();
			}

			$stores_info = getStore($store_id);
			$this->assign('store', $stores_info);
			$this->assign('pick_code', $pick_code);
		}

		if (0 < $order['uc_id']) {
			$this->use_coupons($order['uc_id'], $order['order_id']);
		}

		if ($order['extension_code'] == 'auction') {
			$is_finished = 2;
			$sql = 'SELECT ext_info FROM {pre}goods_activity WHERE act_id = \'' . $order['extension_id'] . '\' LIMIT 1';
			$activity_ext_info = $this->db->getOne($sql);

			if ($activity_ext_info) {
				$activity_ext_info = unserialize($activity_ext_info);

				if (0 < $activity_ext_info['deposit']) {
					$is_finished = 1;
				}
			}

			$sql = 'UPDATE {pre}goods_activity SET is_finished=\'' . $is_finished . '\' WHERE act_id = \'' . $order['extension_id'] . '\'';
			$this->db->query($sql);
		}

		if ($order['extension_code'] == 'bargain_buy') {
			$sql = 'UPDATE {pre}bargain_statistics_log SET status=\'1\' WHERE id=' . $_SESSION['bs_id'];
			$this->db->query($sql);
		}

		if (0 < $order['vc_id']) {
			$this->use_value_card($order['vc_id'], $new_order_id, $order['use_value_card']);
		}

		if (0 < $order['user_id'] && (0 < $order['surplus'] || 0 < $order['integral'])) {
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

			log_account_change($order['user_id'], $order_surplus, 0, 0, $order_integral, sprintf(L('pay_order'), $order['order_sn']));
			create_snapshot($new_order_id);
		}

		$order['presaletime'] = 0;

		if ($order['extension_code'] == 'presale') {
			$presale = dao('presale_activity')->field('pay_start_time, pay_end_time')->where(array('act_id' => $order['extension_id']))->find();
			if (gmtime() < $presale['pay_end_time'] && $presale['pay_start_time'] < gmtime()) {
				$order['presaletime'] = 1;
			}
			else {
				$order['presaletime'] = 2;
			}
		}

		if (0 < $order['bonus_id'] && 0 < $temp_amout) {
			use_bonus($order['bonus_id'], $new_order_id);
		}

		if (C('shop.use_storage') == '1' && C('shop.stock_dec_time') == SDT_PLACE) {
			change_order_goods_storage($order['order_id'], true, SDT_PLACE);
		}

		if (0 < $new_order_id && C('shop.use_storage') == '1' && C('shop.stock_dec_time') == SDT_PAID && $order['order_amount'] <= 0) {
			change_order_goods_storage($order['order_id'], true, SDT_PAID, C('shop.stock_dec_time'), 0, $store_id);
		}

		$is_update_sale = is_update_sale($order['order_id']);
		if (C('shop.sales_volume_time') == SALES_PAY && $is_update_sale == 0) {
			get_goods_sale($order['order_id']);
		}

		if (count($cart_goods) <= 1) {
			if (1 <= $cart_goods[0]['ru_id']) {
				$sql = 'SELECT seller_email FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = \'' . $cart_goods[0]['ru_id'] . '\'';
				$service_email = $GLOBALS['db']->getOne($sql);
			}
			else {
				$service_email = C('shop.service_email');
			}
		}
		else {
			$service_email = C('shop.service_email');
		}

		$msg = $order['pay_status'] == PS_UNPAYED ? L('order_placed_sms') : L('order_placed_sms') . '[' . L('sms_paid') . ']';
		$order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);

		if ($order['order_amount'] <= 0) {
			$sql = 'SELECT goods_id, goods_name, goods_number AS num FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE is_real = 0 AND extension_code = \'virtual_card\'' . ' AND ' . $this->sess_id . (' AND rec_type = \'' . $flow_type . '\'');
			$res = $GLOBALS['db']->getAll($sql);
			$virtual_goods = array();

			foreach ($res as $row) {
				$virtual_goods['virtual_card'][] = array('goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']);
			}

			cloud_confirmorder($new_order_id);
			if ($virtual_goods && $flow_type != CART_GROUP_BUY_GOODS) {
				$error_msg = '';

				if (virtual_goods_ship($virtual_goods, $error_msg, $order['order_sn'], true)) {
					$sql = 'SELECT COUNT(*)' . ' FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order['order_id'] . '\' ') . ' AND is_real = 1';

					if ($GLOBALS['db']->getOne($sql) <= 0) {
						update_order($order['order_id'], array('order_status' => 5, 'shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));

						if (0 < $order['user_id']) {
							$user = user_info($order['user_id']);
							$integral = integral_to_give($order);
							log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf(L('order_gift_integral'), $order['order_sn']));
							send_order_bonus($order['order_id']);
						}
					}
				}
			}
		}

		if ($payment['pay_code'] == 'cod') {
			$order['pay_code'] = $payment['pay_code'];
		}

		clear_cart($flow_type, $_SESSION['cart_value']);
		clear_all_files();

		if (!empty($order['shipping_name'])) {
			$order['shipping_name'] = trim(stripcslashes($order['shipping_name']));
		}

		$this->assign('order', $order);
		$this->assign('total', $total);
		$this->assign('goods_list', $cart_goods);
		$this->assign('order_submit_back', sprintf(L('order_submit_back'), L('back_home'), L('goto_user_center')));
		unset($_SESSION['flow_consignee']);
		unset($_SESSION['cart_value']);
		unset($_SESSION['flow_order']);
		unset($_SESSION['direct_shopping']);
		unset($_SESSION['store_id']);
		unset($_SESSION['extension_code']);
		unset($_SESSION['bs_id']);
		$order_id = $order['order_id'];
		$row = get_main_order_info($order_id);
		$order_info = get_main_order_info($order_id, 1);
		$ru_id = explode(',', $order_info['all_ruId']['ru_id']);
		$ru_number = count($ru_id);

		if (1 < $ru_number) {
			get_insert_order_goods_single($order_info, $row, $order_id, $ru_number);
		}

		$sql = 'select count(order_id) from ' . $this->ecs->table('order_info') . ' where main_order_id = ' . $order['order_id'];
		$child_order = $this->db->getOne($sql);

		if (1 < $child_order) {
			$child_order_info = get_child_order_info($order['order_id']);
			$this->assign('child_order_info', $child_order_info);
		}

		$this->assign('pay_type', $pay_type);
		$this->assign('child_order', $child_order);
		if ($stores_sms == 1 && 0 < $store_id) {
			if ($order['mobile']) {
				$user_mobile_phone = $order['mobile'];
			}
			else {
				$users = dao('users')->field('mobile_phone, user_name')->where(array('user_id' => $_SESSION['user_id']))->find();
				$user_mobile_phone = $users['mobile_phone'];
				$user_name = $users['user_name'];
			}

			$store_address = get_area_region_info($stores_info) . $stores_info['stores_address'];
			$user_name = isset($_SESSION['user_name']) && !empty($_SESSION['user_name']) ? $_SESSION['user_name'] : $user_name;
			$store_smsParams = array('user_name' => $user_name, 'username' => $user_name, 'order_sn' => $order['order_sn'], 'ordersn' => $order['order_sn'], 'code' => $pick_code, 'store_address' => $store_address, 'storeaddress' => $store_address, 'mobile_phone' => $user_mobile_phone, 'mobilephone' => $user_mobile_phone);
			send_sms($user_mobile_phone, 'store_order_code', $store_smsParams);
		}

		if (count($ru_id) == 1) {
			$sellerId = $ru_id[0];

			if ($sellerId == 0) {
				$sms_shop_mobile = C('shop.sms_shop_mobile');
			}
			else {
				$sql = 'SELECT mobile FROM ' . $this->ecs->table('seller_shopinfo') . (' WHERE ru_id = \'' . $sellerId . '\'');
				$sms_shop_mobile = $this->db->getOne($sql);
			}

			if (C('shop.sms_order_placed') == '1' && $sms_shop_mobile != '') {
				$msg = array('consignee' => $order['consignee'], 'order_mobile' => $order['mobile'], 'ordermobile' => $order['mobile']);
				send_sms($sms_shop_mobile, 'sms_order_placed', $msg);
			}

			if (C('shop.send_service_email') && $service_email != '') {
				$tpl = get_mail_template('remind_of_new_order');
				$this->assign('order', $order);
				$this->assign('goods_list', $cart_goods);
				$this->assign('shop_name', C('shop.shop_name'));
				$send_date = local_date(C('shop.time_format'), gmtime());
				$this->assign('send_date', $send_date);
				$content = $this->fetch('', $tpl['template_content']);
				send_mail(C('shop.shop_name'), $service_email, $tpl['template_subject'], $content, $tpl['is_html']);
			}
		}

		if (is_dir(APP_WECHAT_PATH)) {
			$pushData = array(
				'orderID'         => array('value' => $order['order_sn'], 'color' => '#173177'),
				'orderMoneySum'   => array('value' => $order['order_amount'], 'color' => '#173177'),
				'backupFieldName' => array('value' => '', 'color' => '#173177'),
				'remark'          => array('value' => '感谢您的光临', 'color' => '#173177')
				);
			$url = __HOST__ . url('user/order/detail', array('order_id' => $order_id));
			push_template('TM00016', $pushData, $url);
		}

		$payment = payment_info($order['pay_id']);
		$order['pay_code'] = $payment['pay_code'];
		if (0 < $order['order_amount'] && $payment['pay_code'] == 'onlinepay' && $payment['pay_code'] != 'cod') {
			ecs_header('Location: ' . url('onlinepay/index/index', array('order_sn' => $order['order_sn'])) . "\n");
		}

		S('order_hash_' . $_SESSION['user_id'], null);
		$this->assign('page_title', L('order_success'));
		$this->display();
	}

	public function actionShippingfee()
	{
		if (IS_AJAX) {
			$result = array('error' => 0, 'massage' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);
			$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
			$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
			$shipping_type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
			$tmp_shipping_id = isset($_POST['shipping_id']) ? intval($_POST['shipping_id']) : 0;
			$order['shipping_type'] = isset($_REQUEST['shipping_type']) ? intval($_REQUEST['shipping_type']) : 0;
			$order['shipping_code'] = isset($_REQUEST['shipping_code']) ? intval($_REQUEST['shipping_code']) : 0;
			$ru_id = isset($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;
			$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
			$store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
			$consignee = get_consignee($_SESSION['user_id']);
			$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id, '', $store_id);
			if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type)) {
				if (empty($cart_goods_list)) {
					$result['error'] = 1;
				}
				else if (!check_consignee_info($consignee, $flow_type)) {
					$result['error'] = 2;
				}
			}
			else {
				$this->assign('config', C('shop'));
				$order = flow_order_info();
				$order['shipping_id'] = $tmp_shipping_id;
				$_SESSION['flow_order'] = $order;

				if ($shipping_type == 1) {
					if (is_array($_SESSION['shipping_type_ru_id'])) {
						$_SESSION['shipping_type_ru_id'][$ru_id] = $ru_id;
					}
				}
				else if (isset($_SESSION['shipping_type_ru_id'][$ru_id])) {
					unset($_SESSION['shipping_type_ru_id'][$ru_id]);
				}

				$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
				$tmp_shipping_id_arr = json_decode($_POST['shipping_id']);
				$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
				$this->assign('cart_goods_number', $cart_goods_number);
				$consignee['province_name'] = get_goods_region_name($consignee['province']);
				$consignee['city_name'] = get_goods_region_name($consignee['city']);
				$consignee['district_name'] = get_goods_region_name($consignee['district']);
				$consignee['street'] = get_goods_region_name($consignee['street']);
				$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
				$this->assign('consignee', $consignee);
				$this->assign('goods_list', cart_by_favourable($cart_goods_list));

				foreach ($cart_goods_list as $key => $val) {
					foreach ($tmp_shipping_id_arr as $k => $v) {
						if (0 < $v[1] && $val['ru_id'] == $v[0]) {
							$cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
						}
					}
				}

				if (!empty($_SESSION['flow_order']['cou_id'])) {
					$cou_id = $_SESSION['flow_order']['cou_id'];
					$coupons_info = get_coupons($cou_id, array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
					$not_freightfree = 0;
					if (!empty($coupons_info) && $cart_goods_list) {
						if ($coupons_info['cou_type'] == 5) {
							$goods_amount = 0;

							foreach ($cart_goods_list as $key => $row) {
								if ($row['ru_id'] == $coupons_info['ru_id']) {
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

					$result['cou_type'] = $coupons_info['cou_type'];
					$result['not_freightfree'] = $not_freightfree;
				}

				$cart_goods = get_new_group_cart_goods($cart_goods_list);
				$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
				$this->assign('order', $order);
				$this->assign('total', $total);

				if ($flow_type == CART_GROUP_BUY_GOODS) {
					$this->assign('is_group_buy', 1);
				}

				$result['amount'] = $total['amount_formated'];
				$result['content'] = $this->fetch('order_total');
			}

			exit(json_encode($result));
		}
	}

	public function actionSelectPayment()
	{
		if (IS_AJAX) {
			$result = array('error' => 0, 'massage' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);
			$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
			$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
			$tmp_shipping_id_arr = I('shipping_id');
			$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
			$store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
			$consignee = get_consignee($_SESSION['user_id']);
			$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id, '', $store_id);
			if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0) {
				if (empty($cart_goods_list)) {
					$result['error'] = 1;
				}
				else if (!check_consignee_info($consignee, $flow_type)) {
					$result['error'] = 2;
				}
			}
			else {
				$this->assign('config', C('shop'));
				$order = flow_order_info();
				$order['pay_id'] = intval($_REQUEST['payment']);
				$payment_info = payment_info($order['pay_id']);
				$result['pay_code'] = $payment_info['pay_code'];
				$result['pay_name'] = $payment_info['pay_name'];
				$result['pay_fee'] = $payment_info['pay_fee'];
				$result['format_pay_fee'] = strpos($payment_info['pay_fee'], '%') !== false ? $payment_info['pay_fee'] : price_format($payment_info['pay_fee'], false);
				$result['pay_id'] = $payment_info['pay_id'];
				$_SESSION['flow_order'] = $order;
				$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
				$this->assign('cart_goods_number', $cart_goods_number);
				$consignee['province_name'] = get_goods_region_name($consignee['province']);
				$consignee['city_name'] = get_goods_region_name($consignee['city']);
				$consignee['district_name'] = get_goods_region_name($consignee['district']);
				$consignee['street'] = get_goods_region_name($consignee['street']);
				$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
				$this->assign('consignee', $consignee);

				foreach ($cart_goods_list as $key => $val) {
					foreach ($tmp_shipping_id_arr as $k => $v) {
						if (0 < $v[1] && $val['ru_id'] == $v[0]) {
							$cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
						}
					}
				}

				if (!empty($_SESSION['flow_order']['cou_id'])) {
					$cou_id = $_SESSION['flow_order']['cou_id'];
					$coupons_info = get_coupons($cou_id, array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
					$not_freightfree = 0;
					if (!empty($coupons_info) && $cart_goods_list) {
						if ($coupons_info['cou_type'] == 5) {
							$goods_amount = 0;

							foreach ($cart_goods_list as $key => $row) {
								if ($row['ru_id'] == $coupons_info['ru_id']) {
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

					$result['cou_type'] = $coupons_info['cou_type'];
					$result['not_freightfree'] = $not_freightfree;
				}

				$cart_goods = get_new_group_cart_goods($cart_goods_list);
				$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
				$this->assign('order', $order);
				$this->assign('total', $total);

				if ($flow_type == CART_GROUP_BUY_GOODS) {
					$this->assign('is_group_buy', 1);
				}

				$result['amount'] = $total['amount_formated'];
				$result['content'] = $this->fetch('order_total');
			}

			exit(json_encode($result));
		}
	}

	public function actionSelectPack()
	{
		if (IS_AJAX) {
			$result = array('error' => '', 'content' => '', 'need_insure' => 0);
			$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
			$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
			$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
			$store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
			$consignee = get_consignee($_SESSION['user_id']);
			$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id, '', $store_id);
			if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0) {
				$result['error'] = L('no_goods_in_cart');
			}
			else {
				$order = flow_order_info();
				$order['pack_id'] = intval($_REQUEST['pack']);
				$_SESSION['flow_order'] = $order;
				$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
				$this->assign('cart_goods_number', $cart_goods_number);
				$consignee['province_name'] = get_goods_region_name($consignee['province']);
				$consignee['city_name'] = get_goods_region_name($consignee['city']);
				$consignee['district_name'] = get_goods_region_name($consignee['district']);
				$consignee['street'] = get_goods_region_name($consignee['street']);
				$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
				$this->assign('consignee', $consignee);
				$this->assign('goods_list', $cart_goods_list);
				$cart_goods = get_new_group_cart_goods($cart_goods_list);
				$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
				$this->assign('total', $total);
				$this->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
				$this->assign('total_bonus', price_format(get_total_bonus(), false));

				if ($flow_type == CART_GROUP_BUY_GOODS) {
					$this->assign('is_group_buy', 1);
				}

				$result['pack_id'] = $order['pack_id'];
				$result['amount'] = $total['amount_formated'];
				$result['content'] = $this->fetch('order_total');
			}

			exit(json_encode($result));
		}
	}

	public function actionChangeBonus()
	{
		$result = array('error' => '', 'content' => '');
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
		$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
		$tmp_shipping_id_arr = json_decode($_POST['shipping_id']);
		$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
		$store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id, '', $store_id);
		if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			$this->assign('config', C('shop'));
			$order = flow_order_info();
			$bonus = bonus_info(intval($_GET['bonus']));
			if (!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id'] || $_GET['bonus'] == 0) {
				$order['bonus_id'] = intval($_GET['bonus']);
			}
			else {
				$order['bonus_id'] = 0;
				$result['error'] = L('invalid_bonus');
			}

			$_SESSION['flow_order'] = $order;
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['street'] = get_goods_region_name($consignee['street']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
			$this->assign('consignee', $consignee);

			foreach ($cart_goods_list as $key => $val) {
				foreach ($tmp_shipping_id_arr as $k => $v) {
					if (0 < $v[1] && $val['ru_id'] == $v[0]) {
						$cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
					}
				}
			}

			$cart_goods = get_new_group_cart_goods($cart_goods_list);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
			$this->assign('total', $total);
			$this->assign('order', $order);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			$result['bonus_id'] = $order['bonus_id'];
			$result['amount'] = $total['amount_formated'];
			$result['content'] = $this->fetch('order_total');
		}

		exit(json_encode($result));
	}

	public function actionChangeCoupont()
	{
		$result = array('error' => '', 'content' => '');
		$cou_id = I('cou_id', 0, 'intval');
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
		$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
		$store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id, '', $store_id);
		if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			$this->assign('config', C());
			$order = flow_order_info();
			$coupons_info = get_coupons($cou_id, array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
			$coupons_info['cou_money'] = !empty($coupons_info['uc_money']) ? $coupons_info['uc_money'] : $coupons_info['cou_money'];
			if (!empty($coupons_info) && $coupons_info['user_id'] == $_SESSION['user_id']) {
				$order['cou_id'] = $order['uc_id'] = $cou_id;
			}
			else {
				$order['cou_id'] = $order['uc_id'] = 0;
			}

			$_SESSION['flow_order'] = $order;
			$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
			$tmp_shipping_id_arr = json_decode($_POST['shipping_id']);
			$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
			$this->assign('cart_goods_number', $cart_goods_number);
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['street'] = get_goods_region_name($consignee['street']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
			$this->assign('consignee', $consignee);
			$this->assign('goods_list', $cart_goods_list);

			if ($tmp_shipping_id_arr) {
				foreach ($cart_goods_list as $key => $val) {
					foreach ($tmp_shipping_id_arr as $k => $v) {
						if (0 < $v[1] && $val['ru_id'] == $v[0]) {
							$cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
						}
					}
				}
			}

			$not_freightfree = 0;
			if (!empty($coupons_info) && $cart_goods_list) {
				if ($coupons_info['cou_type'] == 5) {
					$goods_amount = 0;

					foreach ($cart_goods_list as $key => $row) {
						if ($row['ru_id'] == $coupons_info['ru_id']) {
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

			$result['cou_type'] = $coupons_info['cou_type'];
			$result['not_freightfree'] = $not_freightfree;
			$cart_goods = get_new_group_cart_goods($cart_goods_list);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
			$this->assign('order', $order);
			$this->assign('total', $total);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}
			else if ($flow_type == CART_EXCHANGE_GOODS) {
				$this->assign('is_exchange_goods', 1);
			}

			$result['cou_id'] = $order['cou_id'];
			$result['amount'] = $total['amount_formated'];
			$result['content'] = $this->fetch('order_total');
		}

		exit(json_encode($result));
	}

	public function actionChangeValueCart()
	{
		$result = array('error' => '', 'content' => '');
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
		$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
		$store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id, '', $store_id);
		if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			$this->assign('config', C());
			$order = flow_order_info();
			$vcid = I('vcid');
			$value_card = value_card_info($vcid);
			if (!empty($value_card) && $value_card['user_id'] == $_SESSION['user_id'] || $_GET['bonus'] == 0) {
				$order['vc_id'] = $vcid;
			}
			else {
				$order['vc_id'] = 0;
			}

			$_SESSION['flow_order'] = $order;
			$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
			$tmp_shipping_id_arr = json_decode($_POST['shipping_id']);
			$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
			$this->assign('cart_goods_number', $cart_goods_number);
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['street'] = get_goods_region_name($consignee['street']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
			$this->assign('consignee', $consignee);
			$this->assign('goods_list', $cart_goods_list);

			if ($tmp_shipping_id_arr) {
				foreach ($cart_goods_list as $key => $val) {
					foreach ($tmp_shipping_id_arr as $k => $v) {
						if (0 < $v[1] && $val['ru_id'] == $v[0]) {
							$cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
						}
					}
				}
			}

			$cart_goods = get_new_group_cart_goods($cart_goods_list);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
			$this->assign('order', $order);
			$this->assign('total', $total);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}
			else if ($flow_type == CART_EXCHANGE_GOODS) {
				$this->assign('is_exchange_goods', 1);
			}

			$result['vc_id'] = $order['vc_id'];
			$result['amount'] = $total['amount_formated'];
			$result['content'] = $this->fetch('order_total');
		}

		exit(json_encode($result));
	}

	public function actionChangeSurplus()
	{
		$surplus = I('surplus', 0, 'intval');
		$result = array('error' => '', 'content' => '');
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
		$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
		$store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id, '', $store_id);
		if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			$this->assign('config', C());
			$order = flow_order_info();
			$order['is_surplus'] = $surplus;
			$_SESSION['flow_order'] = $order;
			$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
			$tmp_shipping_id_arr = json_decode($_POST['shipping_id']);
			$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
			$this->assign('cart_goods_number', $cart_goods_number);
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['street'] = get_goods_region_name($consignee['street']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
			$this->assign('consignee', $consignee);
			$this->assign('goods_list', $cart_goods_list);

			if ($tmp_shipping_id_arr) {
				foreach ($cart_goods_list as $key => $val) {
					foreach ($tmp_shipping_id_arr as $k => $v) {
						if (0 < $v[1] && $val['ru_id'] == $v[0]) {
							$cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
						}
					}
				}
			}

			$cart_goods = get_new_group_cart_goods($cart_goods_list);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
			$this->assign('order', $order);
			$this->assign('total', $total);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}
			else if ($flow_type == CART_EXCHANGE_GOODS) {
				$this->assign('is_exchange_goods', 1);
			}

			$result['amount'] = $total['amount_formated'];
			$result['content'] = $this->fetch('order_total');
		}

		exit(json_encode($result));
	}

	public function actionChangeIntegral()
	{
		$is_integral = input('points', 0, 'intval');
		$tmp_shipping_id_arr = I('shipping_id');
		$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
		$store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id, $consignee, $store_id);
		if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			if (0 < $is_integral) {
				$user_info = user_info($_SESSION['user_id']);
				$flow_points = flow_available_points($_SESSION['cart_value']);
				$user_points = $user_info['pay_points'];
				if ($user_points == 0 || $user_points < $flow_points) {
					$integral = 0;
					$result['error'] = L('integral_not_enough');
				}
				else {
					$integral = $flow_points;
				}
			}
			else {
				$integral = 0;
			}

			$this->assign('config', C('shop'));
			$order = flow_order_info();
			$order['integral'] = $integral;
			$_SESSION['flow_order'] = $order;
			$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
			$this->assign('cart_goods_number', $cart_goods_number);
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['street'] = get_goods_region_name($consignee['street']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];

			if ($tmp_shipping_id_arr) {
				foreach ($cart_goods_list as $key => $val) {
					foreach ($tmp_shipping_id_arr as $k => $v) {
						if (0 < $v[1] && $val['ru_id'] == $v[0]) {
							$cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
						}
					}
				}
			}

			if (!empty($_SESSION['flow_order']['cou_id'])) {
				$cou_id = $_SESSION['flow_order']['cou_id'];
				$coupons_info = get_coupons($cou_id, array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
				$not_freightfree = 0;
				if (!empty($coupons_info) && $cart_goods_list) {
					if ($coupons_info['cou_type'] == 5) {
						$goods_amount = 0;

						foreach ($cart_goods_list as $key => $row) {
							if ($row['ru_id'] == $coupons_info['ru_id']) {
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

				$result['cou_type'] = $coupons_info['cou_type'];
				$result['not_freightfree'] = $not_freightfree;
			}

			$cart_goods = get_new_group_cart_goods($cart_goods_list);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
			$this->assign('order', $order);
			$this->assign('total', $total);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			$result['integral'] = $order['integral'];
			$result['amount'] = $total['amount_formated'];
			$result['content'] = $this->fetch('order_total');
		}

		exit(json_encode($result));
	}

	public function actionSelectPicksite()
	{
		$result = array('error' => 0, 'err_msg' => '', 'content' => '');
		$ru_id = I('request.ru_id', 0, 'intval');

		if (isset($_REQUEST['picksite_id'])) {
			$picksite_id = I('request.picksite_id', 0, 'intval');

			if (is_array($_SESSION['flow_consignee']['point_id'])) {
				$_SESSION['flow_consignee']['point_id'][$ru_id] = $picksite_id;
			}
		}
		else {
			if (isset($_REQUEST['shipping_date']) && isset($_REQUEST['time_range'])) {
				$shipping_date = I('request.shipping_date');
				$time_range = I('request.time_range');
				$_SESSION['flow_consignee']['shipping_dateStr'] = $shipping_date . $time_range;
			}
		}

		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id, '', $store_id);
		if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type)) {
			if (empty($cart_goods_list)) {
				$result['error'] = 1;
				$result['err_msg'] = L('no_goods_in_cart');
			}
			else if (!check_consignee_info($consignee, $flow_type)) {
				$result['error'] = 2;
				$result['err_msg'] = L('au_buy_after_login');
			}
		}

		exit(json_encode($result));
	}

	public function actionChangeNeedinv()
	{
		$result = array('error' => '', 'content' => '', 'amount' => '');
		$_GET['inv_type'] = !empty($_GET['inv_type']) ? json_str_iconv(urldecode($_GET['inv_type'])) : 0;
		$_GET['inv_payee'] = !empty($_GET['inv_payee']) ? json_str_iconv(urldecode($_GET['inv_payee'])) : 0;
		$_GET['inv_content'] = !empty($_GET['inv_content']) ? json_str_iconv(urldecode($_GET['inv_content'])) : '';
		$_GET['tax_id'] = !empty($_GET['tax_id']) ? json_str_iconv(urldecode($_GET['tax_id'])) : '';
		$_GET['invoice_id'] = !empty($_GET['invoice_id']) ? json_str_iconv(urldecode($_GET['invoice_id'])) : 0;
		$_GET['invoice'] = !empty($_GET['invoice']) ? json_str_iconv(urldecode($_GET['invoice'])) : '';
		$_GET['vat_id'] = !empty($_GET['vat_id']) ? json_str_iconv(urldecode($_GET['vat_id'])) : 0;
		$_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
		$tmp_shipping_id_arr = json_decode($_POST['shipping_id']);
		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$flow_type = $_SESSION['flow_type'] == CART_ONESTEP_GOODS ? CART_ONESTEP_GOODS : $flow_type;
		$store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
		$store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id, '', $store_id);
		if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			$this->assign('config', C('shop'));
			$order = flow_order_info();
			if (isset($_GET['need_inv']) && intval($_GET['need_inv']) == 1) {
				$order['need_inv'] = 1;
				$order['inv_type'] = trim(stripslashes($_GET['inv_type']));
				$order['inv_payee'] = trim(stripslashes($_GET['inv_payee']));
				$order['inv_content'] = trim(stripslashes($_GET['inv_content']));
				$order['tax_id'] = trim(stripslashes($_GET['tax_id']));
				$order['invoice_id'] = trim(stripslashes($_GET['invoice_id']));
				$order['invoice'] = trim(stripslashes($_GET['invoice']));
				$order['vat_id'] = trim(stripslashes($_GET['vat_id']));
				$order['invoice_type'] = trim(stripslashes($_GET['inv_type']));
			}
			else {
				$order['need_inv'] = 0;
				$order['inv_type'] = '';
				$order['inv_payee'] = '';
				$order['inv_content'] = '';
				$order['tax_id'] = '';
				$order['invoice_id'] = 0;
				$order['invoice'] = '';
				$order['vat_id'] = 0;
				$order['invoice_type'] = 0;
			}

			$_SESSION['flow_order'] = $order;
			$invoice_id = trim(stripslashes($_GET['invoice']));
			$sql = 'SELECT invoice_id FROM {pre}order_invoice WHERE inv_payee = \'' . $order['inv_payee'] . '\' AND user_id = \'' . $_SESSION['user_id'] . '\'';
			if (empty($invoice_id) && empty($order['tax_id'])) {
				if (!$GLOBALS['db']->getOne($sql)) {
					$sql = 'INSERT INTO {pre}order_invoice (inv_payee,user_id) VALUES (\'' . $order['inv_payee'] . '\',' . $_SESSION['user_id'] . ')';
					$this->db->query($sql);
				}
			}
			else {
				if (empty($invoice_id) && !empty($order['tax_id'])) {
					if (!$GLOBALS['db']->getOne($sql)) {
						$sql = 'INSERT INTO {pre}order_invoice (inv_payee,tax_id,user_id) VALUES (\'' . $order['inv_payee'] . '\',' . $order['tax_id'] . ',' . $_SESSION['user_id'] . ')';
						$this->db->query($sql);
					}
				}
				else {
					$sql = 'UPDATE {pre}order_invoice SET tax_id=\'' . $order['tax_id'] . '\' WHERE invoice_id=\'' . $invoice_id . '\'and user_id=' . $_SESSION['user_id'];
					$this->db->query($sql);
				}
			}

			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['street'] = get_goods_region_name($consignee['street']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
			$this->assign('consignee', $consignee);

			foreach ($cart_goods_list as $key => $val) {
				foreach ($tmp_shipping_id_arr as $k => $v) {
					if (0 < $v[1] && $val['ru_id'] == $v[0]) {
						$cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
					}
				}
			}

			if (!empty($_SESSION['flow_order']['cou_id'])) {
				$cou_id = $_SESSION['flow_order']['cou_id'];
				$coupons_info = get_coupons($cou_id, array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
				$not_freightfree = 0;
				if (!empty($coupons_info) && $cart_goods_list) {
					if ($coupons_info['cou_type'] == 5) {
						$goods_amount = 0;

						foreach ($cart_goods_list as $key => $row) {
							if ($row['ru_id'] == $coupons_info['ru_id']) {
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

				$result['cou_type'] = $coupons_info['cou_type'];
				$result['not_freightfree'] = $not_freightfree;
			}

			$cart_goods = get_new_group_cart_goods($cart_goods_list);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
			$this->assign('total', $total);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			$result['amount'] = $total['amount_formated'];
			$result['content'] = $this->fetch('order_total');
		}

		exit(json_encode($result));
	}

	public function actionAddressList()
	{
		if (IS_AJAX) {
			$id = I('address_id');
			drop_consignee($id);
			unset($_SESSION['flow_consignee']);
			exit();
		}

		$user_id = $_SESSION['user_id'];

		if (0 < $_SESSION['user_id']) {
			$consignee_list = get_consignee_list($_SESSION['user_id']);
		}
		else if (isset($_SESSION['flow_consignee'])) {
			$consignee_list = array($_SESSION['flow_consignee']);
		}
		else {
			$consignee_list[] = array('country' => C('shop.shop_country'));
		}

		$this->assign('name_of_region', array(C('shop.name_of_region_1'), C('shop.name_of_region_2'), C('shop.name_of_region_3'), C('shop.name_of_region_4')));

		if ($consignee_list) {
			foreach ($consignee_list as $k => $v) {
				$address = '';

				if ($v['province']) {
					$res = get_region_name($v['province']);
					$address .= $res['region_name'];
				}

				if ($v['city']) {
					$ress = get_region_name($v['city']);
					$address .= $ress['region_name'];
				}

				if ($v['district']) {
					$resss = get_region_name($v['district']);
					$address .= $resss['region_name'];
				}

				if ($v['street']) {
					$resss = get_region_name($v['street']);
					$address .= $resss['region_name'];
				}

				$consignee_list[$k]['address'] = $address . ' ' . $v['address'];
				$consignee_list[$k]['url'] = url('user/edit_address', array('id' => $v['address_id']));
			}
		}
		else {
			$this->redirect('add_address');
		}

		$default_id = $this->db->getOne('SELECT address_id FROM {pre}users WHERE user_id=\'' . $user_id . '\'');
		$address_id = $_SESSION['flow_consignee']['address_id'];
		$this->assign('defulte_id', $default_id);
		$this->assign('address_id', $address_id);
		$this->assign('consignee_list', $consignee_list);
		$this->assign('page_title', L('receiving_address'));
		$this->display();
	}

	public function actionAddAddress()
	{
		if (IS_POST) {
			$consignee = array('address_id' => I('address_id'), 'consignee' => I('consignee'), 'country' => 1, 'province' => I('province_region_id'), 'city' => I('city_region_id'), 'district' => I('district_region_id'), 'street' => I('town_region_id'), 'email' => I('email'), 'address' => I('address'), 'zipcode' => I('zipcode'), 'tel' => I('tel'), 'mobile' => I('mobile'), 'sign_building' => I('sign_building'), 'best_time' => I('best_time'), 'user_id' => $_SESSION['user_id']);

			if (empty($consignee['consignee'])) {
				exit(json_encode(array('status' => 'n', 'info' => L('msg_receiving_notnull'))));
			}

			if (is_mobile($consignee['mobile']) == false) {
				exit(json_encode(array('status' => 'n', 'info' => L('msg_mobile_format_error'))));
			}

			if (empty($consignee['province'])) {
				exit(json_encode(array('status' => 'n', 'info' => L('请选择地区'))));
			}

			if (empty($consignee['address'])) {
				exit(json_encode(array('status' => 'n', 'info' => L('msg_address_notnull'))));
			}

			$limit_address = $this->db->getOne('select count(address_id) from {pre}user_address where user_id = \'' . $consignee['user_id'] . '\'');

			if (10 < $limit_address) {
				exit(json_encode(array('status' => 'n', 'info' => sprintf(L('msg_save_address'), 10))));
			}

			if (0 < $_SESSION['user_id']) {
				save_consignee($consignee, false);
			}

			$_SESSION['flow_consignee'] = stripslashes_deep($consignee);
			$back_act = url('address_list');
			if (isset($_SESSION['flow_consignee']) && empty($consignee['address_id'])) {
				exit(json_encode(array('status' => 'y', 'info' => L('success_address'), 'url' => $back_act)));
			}
			else {
				if (isset($_SESSION['flow_consignee']) && !empty($consignee['address_id'])) {
					exit(json_encode(array('status' => 'y', 'info' => L('edit_address'), 'url' => $back_act)));
				}
				else {
					exit(json_encode(array('status' => 'n', 'info' => L('error_address'))));
				}
			}
		}

		$this->assign('user_id', $_SESSION['user_id']);
		$this->assign('country_list', get_regions());
		$this->assign('shop_country', C('shop.shop_country'));
		$this->assign('shop_province_list', get_regions(1, C('shop.shop_country')));
		$this->assign('address_id', I('address_id'));
		$province_list = get_regions(1, C('shop.shop_country'));
		$this->assign('province_list', $province_list);
		$city_list = get_region_city_county($this->province_id);

		if ($city_list) {
			foreach ($city_list as $k => $v) {
				$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
			}
		}

		$this->assign('city_list', $city_list);
		$district_list = get_region_city_county($this->city_id);
		$this->assign('district_list', $district_list);
		$address_id = I('request.address_id', 0, 'intval');

		if ($address_id) {
			$consignee_list = $this->db->getRow('SELECT * FROM {pre}user_address WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND address_id = \'' . $address_id . '\' ');

			if (empty($consignee_list)) {
				exit(json_encode(array('status' => 'n', 'info' => L('no_address'))));
			}

			$province = get_region_name($consignee_list['province']);
			$city = get_region_name($consignee_list['city']);
			$district = get_region_name($consignee_list['district']);
			$town = get_region_name($consignee_list['street']);
			$consignee_list['province'] = $province['region_name'];
			$consignee_list['city'] = $city['region_name'];
			$consignee_list['district'] = $district['region_name'];
			$consignee_list['town'] = $town['region_name'];
			$consignee_list['province_id'] = $province['region_id'];
			$consignee_list['city_id'] = $city['region_id'];
			$consignee_list['district_id'] = $district['region_id'];
			$consignee_list['town_region_id'] = $town['region_id'];
			$city_list = get_region_city_county($province['region_id']);

			if ($city_list) {
				foreach ($city_list as $k => $v) {
					$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
				}
			}

			$this->assign('city_list', $city_list);
			$this->assign('consignee_list', $consignee_list);
			$this->assign('page_title', '修改收货地址');
			$this->display();
		}
		else {
			$this->assign('page_title', '添加收货地址');
			$this->display();
		}
	}

	public function actionEditAddress()
	{
		if (IS_POST) {
			$consignee = array('address_id' => I('address_id'), 'consignee' => I('consignee'), 'country' => 1, 'province' => I('province_region_id'), 'city' => I('city_region_id'), 'district' => I('district_region_id'), 'email' => I('email'), 'address' => I('address'), 'zipcode' => I('zipcode'), 'tel' => I('tel'), 'mobile' => I('mobile'), 'sign_building' => I('sign_building'), 'best_time' => I('best_time'), 'user_id' => $_SESSION['user_id']);

			if (empty($consignee['consignee'])) {
				show_message(L('msg_receiving_notnull'));
			}

			if (empty($consignee['mobile'])) {
				show_message(L('msg_contact_way_notnull'));
			}

			if (is_mobile($consignee['mobile']) == false) {
				show_message(L('msg_mobile_format_error'));
			}

			if (empty($consignee['address'])) {
				show_message(L('msg_address_notnull'));
			}

			$limit_address = $this->db->getOne('select count(address_id) from {pre}user_address where user_id = \'' . $consignee['user_id'] . '\'');

			if (10 < $limit_address) {
				show_message(L('msg_save_address'));
			}

			if (0 < $_SESSION['user_id']) {
				save_consignee($consignee, true);
			}

			$_SESSION['flow_consignee'] = stripslashes_deep($consignee);
			ecs_header('Location: ' . url('flow/index/index') . "\n");
			exit();
		}

		$this->assign('user_id', $_SESSION['user_id']);
		$this->assign('country_list', get_regions());
		$this->assign('shop_country', C('shop.shop_country'));
		$this->assign('shop_province_list', get_regions(1, C('shop.shop_country')));
		$this->assign('address_id', I('address_id'));
		$province_list = get_regions(1, C('shop.shop_country'));
		$this->assign('province_list', $province_list);
		$city_list = get_region_city_county($this->province_id);

		if ($city_list) {
			foreach ($city_list as $k => $v) {
				$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
			}
		}

		$address_id = I('request.address_id', 0, 'intval');

		if ($address_id) {
			$consignee_list = $this->db->getRow('SELECT * FROM {pre}user_address WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND address_id=\'' . $address_id . '\' ');

			if (empty($consignee_list)) {
				show_message(L('not_exist_address'));
			}

			$c = get_region_name($consignee_list['province']);
			$cc = get_region_name($consignee_list['city']);
			$ccc = get_region_name($consignee_list['district']);
			$consignee_list['province'] = $c['region_name'];
			$consignee_list['city'] = $cc['region_name'];
			$consignee_list['district'] = $ccc['region_name'];
			$consignee_list['province_id'] = $c['region_id'];
			$consignee_list['city_id'] = $cc['region_id'];
			$consignee_list['district_id'] = $ccc['region_id'];
			$city_list = get_region_city_county($c['region_id']);

			if ($city_list) {
				foreach ($city_list as $k => $v) {
					$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
				}
			}

			$this->assign('consignee_list', $consignee_list);
		}

		$this->assign('city_list', $city_list);
		$district_list = get_region_city_county($this->city_id);
		$this->assign('district_list', $district_list);
		$this->assign('page_title', L('edit_address'));
		$this->display();
	}

	public function actionShowRegionName()
	{
		if (IS_AJAX) {
			$data['province'] = get_region_name(I('province'));
			$data['city'] = get_region_name(I('city'));
			$data['district'] = get_region_name(I('district'));
			exit(json_encode($data));
		}
	}

	public function actionSetAddress()
	{
		if (IS_AJAX) {
			$user_id = session('user_id');
			$default_id = dao('users')->where(array('user_id' => $user_id))->getField('address_id');
			$address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;
			$sql = 'SELECT * FROM {pre}user_address WHERE address_id = \'' . $address_id . '\' AND user_id = \'' . $user_id . '\'';
			$address = $this->db->getRow($sql);

			if (!empty($address)) {
				if (empty($default_id)) {
					dao('users')->data(array('address_id' => $address_id))->where(array('user_id' => $user_id))->save();
				}

				$_SESSION['flow_consignee'] = $address;
				echo json_encode(array('url' => url('flow/index/index'), 'status' => 1));
			}
			else {
				echo json_encode(array('status' => 0));
			}
		}
	}

	public function actionAddPackageToCart()
	{
		if (IS_AJAX) {
			$result = array('error' => 0, 'message' => '', 'content' => '', 'package_id' => '');

			if (empty($_POST['package_info'])) {
				$result['error'] = 1;
				exit(json_encode($result));
			}

			$_POST['package_info'] = stripslashes($_POST['package_info']);
			$package = json_decode($_POST['package_info']);
			if (!is_numeric($package->number) || intval($package->number) <= 0) {
				$result['error'] = 1;
				$result['message'] = L('invalid_number');
			}
			else if (add_package_to_cart($package->package_id, $package->number, $package->warehouse_id, $package->area_id)) {
				if (2 < C('shop.cart_confirm')) {
					$result['message'] = '';
				}
				else {
					$result['message'] = C('shop.cart_confirm') == 1 ? L('addto_cart_success_1') : L('addto_cart_success_2');
				}

				$result['content'] = insert_cart_info();
				$result['one_step_buy'] = C('shop.one_step_buy');
			}
			else {
				$result['message'] = $GLOBALS['err']->last_message();
				$result['error'] = $GLOBALS['err']->error_no;
				$result['package_id'] = stripslashes($package->package_id);
			}

			$confirm_type = isset($package->confirm_type) ? $package->confirm_type : 0;

			if (0 < $confirm_type) {
				$result['confirm_type'] = $confirm_type;
			}
			else {
				$cart_confirm = C('shop.cart_confirm');
				$result['confirm_type'] = !empty($cart_confirm) ? $cart_confirm : 2;
			}

			exit(json_encode($result));
		}
	}

	public function showCoupons($attr)
	{
		$user_id = $_SESSION['user_id'];
		$arr = array();
		$sql = ' select * from  ' . $GLOBALS['ecs']->table('coupons_user') . ' cu  left join ' . $GLOBALS['ecs']->table('coupons') . (' cs  on cu.cou_id =  cs.cou_id   where cu.is_use = 0  and  cu.user_id=\'' . $user_id . '\' ');
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $i) {
			$goodsid = $i['cou_goods'];

			if (empty($goodsid)) {
				$arr[] = $i['cou_id'];
			}
			else {
				$gs = explode(',', $goodsid);

				foreach ($gs as $k) {
					foreach ($attr as $j) {
						if ($j['goods_id'] == $k) {
							$arr[] = $i['cou_id'];
						}
					}
				}
			}
		}

		return array_unique($arr);
	}

	public function check_login()
	{
		$without = array('AddPackageToCart');
		if (!$_SESSION['user_id'] && !in_array(ACTION_NAME, $without)) {
			$back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];

			if (IS_AJAX) {
				$this->ajaxReturn(array('error' => 1, 'message' => L('yet_login'), 'url' => url('user/login/index', array('back_act' => urlencode($back_act)))));
			}

			$this->redirect('user/login/index', array('back_act' => urlencode($back_act)));
		}
	}

	public function use_coupons($cou_id, $order_id)
	{
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('coupons_user') . (' SET order_id = \'' . $order_id . '\', is_use_time = \'') . gmtime() . '\', is_use =1 ' . ('WHERE uc_id = \'' . $cou_id . '\'');
		return $GLOBALS['db']->query($sql);
	}

	public function use_value_card($vc_id, $order_id, $use_val)
	{
		$sql = ' SELECT card_money FROM ' . $GLOBALS['ecs']->table('value_card') . (' WHERE vid = \'' . $vc_id . '\' ');
		$card_money = $GLOBALS['db']->getOne($sql);
		$card_money -= $use_val;

		if ($card_money < 0) {
			return false;
		}

		$sql = ' UPDATE ' . $GLOBALS['ecs']->table('value_card') . (' SET card_money = \'' . $card_money . '\' ') . (' WHERE vid = \'' . $vc_id . '\' ');

		if (!$GLOBALS['db']->query($sql)) {
			return false;
		}

		$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('value_card_record') . ' (vc_id, order_id, use_val, record_time) ' . ('VALUES(\'' . $vc_id . '\', \'' . $order_id . '\', \'' . $use_val . '\', \'') . gmtime() . '\')';

		if (!$GLOBALS['db']->query($sql)) {
			return false;
		}

		return true;
	}
}

?>
