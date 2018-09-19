<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class FlowService
{
	private $cartRepository;
	private $CouponsRepository;
	private $addressRepository;
	private $invoiceRepository;
	private $paymentRepository;
	private $shippingRepository;
	private $shopConfigRepository;
	private $goodsRepository;
	private $productRepository;
	private $orderRepository;
	private $orderGoodsRepository;
	private $orderInvoiceRepository;
	private $accountRepository;
	private $payLogRepository;
	private $regionRepository;
	private $shopRepository;
	private $bargainRepository;
	private $teamRepository;
	private $userId;
	private $defaultAddress;

	public function __construct(\App\Repositories\Cart\CartRepository $cartRepository, \App\Repositories\Coupons\CouponsRepository $couponsRepository, \App\Repositories\User\AddressRepository $addressRepository, \App\Repositories\User\InvoiceRepository $invoiceRepository, \App\Repositories\Payment\PaymentRepository $paymentRepository, \App\Repositories\Shipping\ShippingRepository $shippingRepository, \App\Repositories\ShopConfig\ShopConfigRepository $shopConfigRepository, \App\Repositories\Goods\GoodsRepository $goodsRepository, \App\Repositories\Order\OrderInvoiceRepository $orderInvoiceRepository, \App\Repositories\Product\ProductRepository $productRepository, \App\Repositories\Order\OrderRepository $orderRepository, \App\Repositories\Order\OrderGoodsRepository $orderGoodsRepository, \App\Repositories\User\AccountRepository $accountRepository, \App\Repositories\Payment\PayLogRepository $payLogRepository, \App\Repositories\Region\RegionRepository $regionRepository, \App\Repositories\Shop\ShopRepository $shopRepository, \App\Repositories\Bargain\BargainRepository $bargainRepository, \App\Repositories\Team\TeamRepository $teamRepository)
	{
		$this->cartRepository = $cartRepository;
		$this->couponsRepository = $couponsRepository;
		$this->addressRepository = $addressRepository;
		$this->invoiceRepository = $invoiceRepository;
		$this->paymentRepository = $paymentRepository;
		$this->shippingRepository = $shippingRepository;
		$this->shopConfigRepository = $shopConfigRepository;
		$this->goodsRepository = $goodsRepository;
		$this->productRepository = $productRepository;
		$this->orderRepository = $orderRepository;
		$this->orderGoodsRepository = $orderGoodsRepository;
		$this->orderInvoiceRepository = $orderInvoiceRepository;
		$this->accountRepository = $accountRepository;
		$this->payLogRepository = $payLogRepository;
		$this->regionRepository = $regionRepository;
		$this->shopRepository = $shopRepository;
		$this->bargainRepository = $bargainRepository;
		$this->teamRepository = $teamRepository;
	}

	public function flowInfo($userId, $flow_type = 0, $bs_id = 0, $t_id = 0, $team_id = 0)
	{
		$result = array();
		$arrru = array();
		$cou = array();
		$this->userId = $userId;
		$flow_type = isset($flow_type) ? intval($flow_type) : CART_GENERAL_GOODS;
		$this->defaultAddress = $defaultAddress = $this->addressRepository->getDefaultByUserId($userId);
		$result['cart_goods_list'] = $this->arrangeCartGoods($userId, $flow_type);

		foreach ($result['cart_goods_list']['list'] as $key => $val) {
			$cou[$key] = $this->couponsRepository->UserCoupons($userId, true, $result['cart_goods_list']['order_total'], $val, true);
		}

		$cou_num = count($cou);

		if (0 < $cou_num) {
			for ($i = 0; $i < $cou_num; $i++) {
				$arrru = array_merge($arrru, $cou[$i]);
			}

			$result['coupons_list'] = $arrru;
		}

		$result['flow_type'] = $flow_type;

		if ($bs_id) {
			$result['bs_id'] = $bs_id;
		}

		if ($t_id) {
			$result['t_id'] = $t_id;
			$result['team_id'] = $team_id;
		}

		if ($this->shopConfigRepository->getShopConfigByCode('can_invoice') == '1') {
			$result['invoice_content'] = explode("\n", str_replace("\r", '', $this->shopConfigRepository->getShopConfigByCode('invoice_content')));

			if (!$this->invoiceRepository->find($userId)) {
				$result['vat_invoice'] = '';
			}
			else {
				$result['vat_invoice'] = $this->invoiceRepository->find($userId);
			}

			$result['can_invoice'] = 1;
		}
		else {
			$result['can_invoice'] = 0;
		}

		if (empty($defaultAddress['province']) || empty($defaultAddress['city'])) {
			$result['default_address'] = '';
		}
		else {
			$result['default_address'] = array('country' => $this->regionRepository->getRegionName($defaultAddress['country']), 'province' => $this->regionRepository->getRegionName($defaultAddress['province']), 'city' => $this->regionRepository->getRegionName($defaultAddress['city']), 'district' => $this->regionRepository->getRegionName($defaultAddress['district']), 'address' => $defaultAddress['address'], 'address_id' => $defaultAddress['address_id'], 'consignee' => $defaultAddress['consignee'], 'mobile' => $defaultAddress['mobile'], 'user_id' => $defaultAddress['user_id']);
		}

		return $result;
	}

	public function changeCou($uc_id, $userId, $flow_type = 0)
	{
		$this->defaultAddress = $defaultAddress = $this->addressRepository->getDefaultByUserId($userId);
		$cart_goods_list = $this->arrangeCartGoods($userId, $flow_type);

		if (empty($cart_goods_list)) {
			$result['error'] = '购物车中无商品';
		}
		else {
			$coupons_info = $this->couponsRepository->getcoupons($userId, $uc_id, array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
			$consignee['province_name'] = $this->regionRepository->getRegionName($this->defaultAddress['province']);
			$consignee['city_name'] = $this->regionRepository->getRegionName($this->defaultAddress['city']);
			$consignee['district_name'] = $this->regionRepository->getRegionName($this->defaultAddress['district']);
			$consignee['street'] = $this->regionRepository->getRegionName($this->defaultAddress['street']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $this->defaultAddress['address'] . $this->defaultAddress['street'];
			$not_freightfree = 0;
			if (!empty($coupons_info) && $cart_goods_list) {
				if ($coupons_info['cou_type'] == 5) {
					$region = $this->couponsRepository->getcouponsregion($coupons_info['cou_id']);
					$cou_region = $region[0];
					$cou_region = !empty($cou_region) ? explode(',', $cou_region['region_list']) : array();
					if ($cou_region && in_array($this->defaultAddress['province'], $cou_region)) {
						$not_freightfree = 1;
					}
				}
				else if ($coupons_info['cou_money'] < $cart_goods_list['order_total']) {
					$result['cou_money'] = $coupons_info['cou_money'];
					$result['order_total'] = $cart_goods_list['order_total'] - $coupons_info['cou_money'];
					$result['order_total_formated'] = price_format($result['order_total']);
				}
			}

			$result['cou_type'] = $coupons_info['cou_type'];
			$result['not_freightfree'] = $not_freightfree;
			$result['cou_id'] = $uc_id;
		}

		return $result;
	}

	private function arrangeCartGoods($userId, $flow_type)
	{
		$cartGoodsList = $this->cartRepository->getGoodsInCartByUser($userId, $flow_type);
		$list = array();
		$totalAmount = $cartGoodsList['total']['goods_price'];

		foreach ($cartGoodsList['goods_list'] as $k => $v) {
			if (!isset($total[$v['ru_id']])) {
				$total[$v['ru_id']] = 0;
			}

			$totalPrice = empty($total[$v['ru_id']]['price']) ? 0 : $total[$v['ru_id']]['price'];
			$totalNumber = empty($total[$v['ru_id']]['number']) ? 0 : $total[$v['ru_id']]['number'];
			$cart_value = '';

			foreach ($v['goods'] as $key => $value) {
				$totalPrice += $value['goods_price'] * $value['goods_number'];
				$totalNumber += $value['goods_number'];
				$cart_value = $cart_value . ',' . $value['rec_id'];
				$list[$v['ru_id']]['shop_list'][$key] = array('rec_id' => $value['rec_id'], 'user_id' => $v['user_id'], 'cat_id' => $value['cat_id'], 'goods_id' => $value['goods_id'], 'goods_name' => $value['goods_name'], 'ru_id' => $v['ru_id'], 'shop_name' => $v['shop_name'], 'market_price' => strip_tags($value['market_price']), 'market_price_formated' => price_format($value['market_price'], false), 'goods_price' => strip_tags($value['goods_price']), 'goods_price_formated' => price_format($value['goods_price'], false), 'goods_number' => $value['goods_number'], 'goods_thumb' => get_image_path($value['goods_thumb']), 'goods_attr' => $value['goods_attr']);
			}

			$cart_value = substr($cart_value, 1);
			$shippingList = $this->getRuShippngInfo($v['goods'], $cart_value, $v['ru_id']);
			$list[$v['ru_id']]['shop_info'] = array();

			foreach ($shippingList['shipping_list'] as $key => $value) {
				$list[$v['ru_id']]['shop_info'][] = array('shipping_id' => $value['shipping_id'], 'shipping_name' => $value['shipping_name'], 'ru_id' => $v['ru_id']);
			}

			$list[$v['ru_id']]['total'] = array('price' => $totalPrice, 'price_formated' => price_format($totalPrice, false), 'number' => $totalNumber);
		}

		unset($cartGoodsList);
		$totalAmount = strip_tags(preg_replace('/([\\x80-\\xff]*|[a-zA-Z])/i', '', $totalAmount));
		sort($list);
		return array('list' => $list, 'order_total' => $totalAmount, 'order_total_formated' => price_format($totalAmount, false));
	}

	public function submitOrder($args)
	{
		$userId = $args['uid'];
		app('config')->set('uid', $userId);
		$time = gmtime();
		$flow_type = isset($args['flow_type']) ? intval($args['flow_type']) : CART_GENERAL_GOODS;
		$goodsNum = $this->cartRepository->goodsNumInCartByUser($userId, $flow_type);

		if (empty($goodsNum)) {
			return array('error' => 1, 'msg' => '购物车没有商品');
		}

		if ($this->shopConfigRepository->getShopConfigByCode('use_storage') == 1 && $this->shopConfigRepository->getShopConfigByCode('stock_dec_time') == 1) {
			$cart_goods = $this->cartRepository->getGoodsInCartByUser($userId, $flow_type);
			$_cart_goods_stock = array();

			foreach ($cart_goods['goods_list'] as $value) {
				foreach ($value['goods'] as $goodsValue) {
					$_cart_goods_stock[$goodsValue['rec_id']] = $goodsValue['goods_number'];
				}
			}

			if (!$this->flow_cart_stock($_cart_goods_stock)) {
				return array('error' => 1, 'msg' => '库存不足');
			}

			unset($cart_goods_stock);
			unset($_cart_goods_stock);
		}

		$consignee = $args['consignee'];
		$consignee_info = $this->addressRepository->find($consignee);

		if (empty($consignee_info)) {
			return array('error' => 1, 'msg' => 'not find consignee');
		}

		$shipping = $this->generateShipping($args['shipping']);
		$order = array('shipping_id' => empty($shipping['shipping_id']) ? 0 : $shipping['shipping_id'], 'pay_id' => intval(0), 'surplus' => isset($args['surplus']) ? floatval($args['surplus']) : 0, 'integral' => isset($score) ? intval($score) : 0, 'tax_id' => empty($args['postdata']['tax_id']) ? 0 : $args['postdata']['tax_id'], 'inv_payee' => trim($args['postdata']['inv_payee']), 'inv_content' => !trim($args['postdata']['inv_content']) ? 0 : trim($args['postdata']['inv_content']), 'vat_id' => empty($args['postdata']['vat_id']) ? 0 : $args['postdata']['vat_id'], 'invoice_type' => empty($args['postdata']['invoice_type']) ? 0 : $args['postdata']['invoice_type'], 'froms' => '小程序', 'referer' => 'wxapp', 'postscript' => @trim($args['postscript']), 'how_oos' => '', 'user_id' => $userId, 'add_time' => $time, 'order_status' => OS_UNCONFIRMED, 'shipping_status' => SS_UNSHIPPED, 'pay_status' => PS_UNPAYED, 'agency_id' => 0);
		$order['extension_code'] = '';
		$order['extension_id'] = 0;

		if ($flow_type == CART_BARGAIN_GOODS) {
			$order['extension_code'] = 'bargain_buy';
		}

		if ($flow_type == CART_TEAM_GOODS) {
			$order['extension_code'] = 'team_buy';
		}

		if (!isset($cart_goods)) {
			$cart_goods = $this->cartRepository->getGoodsInCartByUser($userId, $flow_type);
		}

		$cartGoods = $cart_goods['goods_list'];
		$cart_good_ids = array();

		foreach ($cartGoods as $k => $v) {
			foreach ($v['goods'] as $goodsValue) {
				array_push($cart_good_ids, $goodsValue['rec_id']);
			}
		}

		if (empty($cart_goods)) {
			return array('error' => 1, 'msg' => '购物车没有商品');
		}

		$order['consignee'] = $consignee_info->consignee;
		$order['country'] = $consignee_info->country;
		$order['province'] = $consignee_info->province;
		$order['city'] = $consignee_info->city;
		$order['mobile'] = $consignee_info->mobile;
		$order['tel'] = $consignee_info->tel;
		$order['zipcode'] = $consignee_info->zipcode;
		$order['district'] = $consignee_info->district;
		$order['address'] = $consignee_info->address;

		foreach ($cartGoods as $val) {
			foreach ($val['goods'] as $v) {
				if ($v['is_real']) {
					$is_real_good = 1;
				}
			}
		}

		$total = $this->orderRepository->order_fee($order, $cart_goods['goods_list'], $consignee_info, $cart_good_ids, $order['shipping_id'], $consignee);

		if (0 < $args['uc_id']) {
			$coupons = $this->couponsRepository->getcoupons($userId, $args['uc_id'], array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
			$total['amount'] = $total['amount'] - $coupons['cou_money'];
			$total['goods_price'] = $total['goods_price'] - $coupons['cou_money'];

			if ($coupons['cou_type'] == 5) {
				$total['amount'] = $total['amount'] - $total['shipping_fee'];
				$total['goods_price'] = $total['goods_price'] - $total['shipping_fee'];
				$total['shipping_fee'] = 0;
			}
		}

		$order['bonus'] = isset($bonus) ? $bonus['type_money'] : '';
		$order['coupons'] = isset($coupons) ? $coupons['cou_money'] : '';
		$order['goods_amount'] = $total['goods_price'];
		$order['discount'] = $total['discount'];
		$order['surplus'] = $total['surplus'];
		$order['tax'] = $total['tax'];

		if (!empty($order['shipping_id'])) {
			$order['shipping_name'] = addslashes($shipping['shipping_name']);
		}

		$order['shipping_fee'] = $total['shipping_fee'];
		$order['insure_fee'] = 0;

		if (0 < $order['pay_id']) {
			$order['pay_name'] = '微信支付';
		}

		$order['pay_name'] = '微信支付';
		$order['pay_fee'] = $total['pay_fee'];
		$order['cod_fee'] = $total['cod_fee'];
		$order['order_amount'] = number_format($total['amount'], 2, '.', '');

		if ($order['order_amount'] <= 0) {
			$order['order_status'] = OS_CONFIRMED;
			$order['confirm_time'] = $time;
			$order['pay_status'] = PS_PAYED;
			$order['pay_time'] = $time;
			$order['order_amount'] = 0;
		}

		$order['integral_money'] = $total['integral_money'];
		$order['integral'] = $total['integral'];
		$order['parent_id'] = 0;
		$order['order_sn'] = $this->getOrderSn();
		$car_goods = array();

		foreach ($cartGoods as $goods) {
			foreach ($goods['goods'] as $k => $list) {
				$car_goods[] = $list;
			}
		}

		$requ = array();
		$cloud_order_list = array();
		$parentordersn = '';
		$requ = $this->sendCloudOrderGoods($car_goods, $order);

		if (!empty($requ)) {
			if ($requ['code'] == '10000') {
				$parentordersn = $requ['data']['result'];
				$cloud_order_list = $requ['data']['orderDetailList'];
			}
			else {
				return array('error' => 1, 'msg' => $requ['message']);
			}
		}

		$order['team_id'] = 0;
		$order['team_parent_id'] = 0;
		$order['team_user_id'] = 0;

		if ($flow_type == CART_TEAM_GOODS) {
			if (0 < $args['team_id']) {
				$team_info = $this->teamRepository->teamIsFailure($args['team_id']);

				if (0 < $team_info['status']) {
					$team_cart_goods = $this->cartRepository->getTeamGoodsInCart($userId, $flow_type);
					$arguments = array('t_id' => $args['t_id'], 'goods_id' => $team_cart_goods['goods_id'], 'start_time' => gmtime(), 'status' => 0);
					$team_log_id = $this->teamRepository->addTeamLog($arguments);
					$order['team_id'] = $team_log_id;
					$order['team_parent_id'] = $userId;
				}
				else {
					$order['team_id'] = $args['team_id'];
					$order['team_user_id'] = $userId;
				}
			}
			else {
				$team_cart_goods = $this->cartRepository->getTeamGoodsInCart($userId, $flow_type);
				$arguments = array('t_id' => $args['t_id'], 'goods_id' => $team_cart_goods['goods_id'], 'start_time' => gmtime(), 'status' => 0);
				$team_log_id = $this->teamRepository->addTeamLog($arguments);
				$order['team_id'] = $team_log_id;
				$order['team_parent_id'] = $userId;
			}
		}

		unset($order['timestamps']);
		unset($order['perPage']);
		unset($order['incrementing']);
		unset($order['dateFormat']);
		unset($order['morphClass']);
		unset($order['exists']);
		unset($order['wasRecentlyCreated']);
		unset($order['cod_fee']);
		$order['bonus'] = !empty($order['bonus']) ? $order['bonus'] : (!empty($order['bonus_id']) ? $order['bonus_id'] : 0);
		$new_order_id = $this->orderRepository->insertGetId($order);
		$order['order_id'] = $new_order_id;
		$newGoodsList = array();

		foreach ($cartGoods as $v) {
			foreach ($v['goods'] as $gv) {
				$gv['ru_id'] = $v['ru_id'];
				$gv['user_id'] = $v['user_id'];
				$gv['shop_name'] = $v['shop_name'];
				$newGoodsList[] = $gv;
			}
		}

		$this->orderGoodsRepository->insertOrderGoods($newGoodsList, $order['order_id']);

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
				$recid = $this->orderGoodsRepository->orderGoodsRecId($order['order_id'], $v['goodId'], $order['user_id']);
				$cloud_order['rec_id'] = $recid['rec_id'];
				$this->orderGoodsRepository->insertOrderCloud($cloud_order);
			}
		}

		if (0 < $order['user_id'] && 0 < $order['integral']) {
			$this->accountRepository->logAccountChange(0, 0, 0, $order['integral'] * -1, trans('message.score.pay'), $order['order_sn'], $userId);
		}

		if ($args['uc_id']) {
			$coutype = $this->couponsRepository->getupcoutype($args['uc_id'], $time);
		}

		if ($order['extension_code'] == 'bargain_buy') {
			$this->bargainRepository->updateStatus($args['bs_id']);
		}

		if ($this->shopConfigRepository->getShopConfigByCode('use_storage') == '1' && $this->shopConfigRepository->getShopConfigByCode('stock_dec_time') == SDT_PLACE) {
			$this->orderRepository->changeOrderGoodsStorage($order['order_id'], true, SDT_PLACE);
		}

		$this->clear_cart_ids($cart_good_ids, $flow_type);
		$order['log_id'] = $this->payLogRepository->insert_pay_log($new_order_id, $order['order_amount'], 0);
		$user_invoice = $this->orderInvoiceRepository->find($userId);
		$invoice_info = array('tax_id' => $order['tax_id'], 'inv_payee' => $order['inv_payee'], 'user_id' => $userId);

		if (!empty($user_invoice)) {
			$this->orderInvoiceRepository->updateInvoice($user_invoice['invoice_id'], $invoice_info);
		}
		else {
			$this->orderInvoiceRepository->addInvoice($invoice_info);
		}

		$order_id = $order['order_id'];
		$shipping = array('shipping' => $args['shipping'], 'shipping_fee_list' => isset($total['shipping_fee_list']) ? $total['shipping_fee_list'] : '');
		if ($flow_type != CART_BARGAIN_GOODS && $flow_type != CART_TEAM_GOODS) {
			$this->childOrder($cart_goods, $order, $consignee_info, $shipping);
		}

		return $order_id;
	}

	private function sendCloudOrderGoods($cart_goods = array(), $order = array())
	{
		if (!$this->shopConfigRepository->getShopConfigByCode('cloud_dsc_appkey')) {
			return $requ = array();
		}

		$order_request = array();
		$order_detaillist = array();

		foreach ($cart_goods as $cart_goods_key => $cart_goods_val) {
			if (0 < $cart_goods_val['cloud_id']) {
				$arr = array();
				$arr['goodName'] = $cart_goods_val['cloud_goodsname'];
				$arr['goodId'] = $cart_goods_val['cloud_id'];

				if ($cart_goods_val['goods_attr_id']) {
					$goods_attr_id = explode(',', $cart_goods_val['goods_attr_id']);
					$goods = $this->goodsRepository->goodsInfo($cart_goods_val['goods_id']);
					$products_info = $this->goodsRepository->getProductsAttrNumber($cart_goods_val['goods_id'], $goods_attr_id, 0, 0, $goods['model_attr']);
					$arr['inventoryId'] = $products_info['inventoryid'];
					$arr['productId'] = $products_info['cloud_product_id'];
				}

				$arr['quantity'] = $cart_goods_val['goods_number'];
				$arr['deliveryWay'] = '3';
				$order_detaillist[] = $arr;
			}
		}

		$requ = array();

		if (!empty($order_detaillist)) {
			$order_request['orderDetailList'] = $order_detaillist;
			$order_request['address'] = $order['address'];
			$order_request['area'] = get_table_date('region', 'region_id=\'' . $order['district'] . '\'', array('region_name'), 2);
			$order_request['city'] = get_table_date('region', 'region_id=\'' . $order['city'] . '\'', array('region_name'), 2);
			$order_request['province'] = get_table_date('region', 'region_id=\'' . $order['province'] . '\'', array('region_name'), 2);
			$order_request['remark'] = $order['postscript'];
			$order_request['mobile'] = intval($order['mobile']);
			$order_request['payType'] = 99;
			$order_request['linkMan'] = $order['consignee'];
			$order_request['billType'] = !empty($order['invoice_type']) ? 2 : 1;
			$order_request['billHeader'] = $order['inv_payee'];
			$order_request['isBill'] = $order['need_inv'];
			$order_request['taxNumber'] = '';

			if ($order_request['billType'] == 2) {
				$invoices_info = $this->invoiceRepository->find($order['user_id']);
				$order_request['billHeader'] = $invoices_info->company_name;
				$order_request['taxNumber'] = $invoices_info->tax_id;
			}

			$cloud = new Erp\JigonService();
			$requ = $cloud->push($order_request, $order);
			$requ = json_decode($requ, true);
		}

		return $requ;
	}

	private function cloudConfirmOrder($order_id)
	{
		if (0 < $order_id) {
			$cloud_order = $this->orderGoodsRepository->orderCloudInfo($order_id);
			$cloud_orders = array();

			if ($cloud_order) {
				$cloud_orders['orderSn'] = $cloud_order['parentordersn'];
				$cloud_orders['paymentFee'] = floatval($cloud_order['goods_number'] * $cloud_order['goods_price'] * 100);
				$loginfo = $this->payLogRepository->pay_log_info($order_id, PAY_ORDER);
				$cloud_orders['payId'] = $loginfo['log_id'];
				$cloud_orders['payType'] = 99;
				$rootPath = app('request')->root();
				$rootPath = dirname(dirname($rootPath)) . '/';
				$cloud_orders['notifyUrl'] = $rootPath . 'api.php?app_key=' . $this->shopConfigRepository->getShopConfigByCode('cloud_dsc_appkey') . '&method=dsc.order.confirmorder.post&format=json&interface_type=1';
				$cloud = new Erp\JigonService();
				$cloud->confirm($cloud_orders);
			}
		}
	}

	private function generateShipping($arr)
	{
		$return = array();
		$str = array();

		foreach ($arr as $k => $v) {
			$return[] = implode('|', array_values($v));
			$shippingId = $v['shipping_id'];
			$shipping = $this->shippingRepository->find($shippingId);
			$str[] = implode('|', array($v['ru_id'], $shipping['shipping_name']));
		}

		return array('shipping_id' => implode(',', $return), 'shipping_name' => implode(',', $str));
	}

	public function getRuShippngInfo($cart_goods, $cart_value, $ru_id, $userId = 0)
	{
		$cart_value_arr = array();
		$cart_freight = array();
		$freight = '';

		foreach ($cart_goods as $cgk => $cgv) {
			if ($cgv['ru_id'] != $ru_id) {
				unset($cart_goods[$cgk]);
			}
			else {
				$cart_value_list = explode(',', $cart_value);

				if (in_array($cgv['rec_id'], $cart_value_list)) {
					$cart_value_arr[] = $cgv['rec_id'];

					if ($cgv['freight'] == 2) {
						@$cart_freight[$cgv['rec_id']][$cgv['freight']] = $cgv['tid'];
					}

					$freight .= $cgv['freight'] . ',';
				}
			}
		}

		if ($freight) {
			$freight = get_del_str_comma($freight);
		}

		$is_freight = 0;

		if ($freight) {
			$freight = explode(',', $freight);
			$freight = array_unique($freight);

			if (in_array(2, $freight)) {
				$is_freight = 1;
			}
		}

		$cart_value = implode(',', $cart_value_arr);
		$sess_id = ' user_id = \'' . (empty($this->userId) ? app('config')->get('uid') : $this->userId) . '\' ';
		$order['shipping_id'] = 0;
		$seller_shipping = $this->shippingRepository->getSellerShippingType($ru_id);
		$shipping_id = isset($seller_shipping['shipping_id']) ? $seller_shipping['shipping_id'] : 0;

		if (empty($this->defaultAddress)) {
			$uid = app('config')->get('uid');
			$this->defaultAddress = $this->addressRepository->getDefaultByUserId($uid);
		}

		$consignee = $this->defaultAddress;
		$consignee['street'] = isset($consignee['street']) ? $consignee['street'] : 0;
		$region = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'], $consignee['street']);
		$insure_disabled = true;
		$cod_disabled = true;
		$where = '';

		if ($cart_value) {
			$where .= ' AND rec_id IN(' . $cart_value . ')';
		}

		$shipping_count = $this->cartRepository->fee_goods($sess_id, $ru_id, $where);
		$shipping_list = array();
		$shipping_list1 = array();
		$shipping_list2 = array();
		$configure_value = 0;
		$configure_type = 0;
		$prefix = \Illuminate\Support\Facades\Config::get('database.connections.mysql.prefix');

		if ($is_freight) {
			if ($cart_freight) {
				$list1 = array();
				$list2 = array();

				foreach ($cart_freight as $key => $row) {
					if (isset($row[2]) && $row[2]) {
						$transport_list = $this->goodsRepository->getTransport($row[2]);

						foreach ($transport_list as $tkey => $trow) {
							if ($trow['freight_type'] == 1) {
								$sql = 'SELECT s.shipping_id, s.shipping_code, s.shipping_name, shipping_order FROM ' . $prefix . 'shipping AS s ' . (' LEFT JOIN ' . $prefix . 'goods_transport_tpl AS gtt ON s.shipping_id = gtt.shipping_id') . (' WHERE gtt.user_id = \'' . $ru_id . '\' AND s.enabled = 1 AND gtt.tid = \'') . $trow['tid'] . '\'' . ' AND (FIND_IN_SET(\'' . $region[1] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[2] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[3] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[4] . '\', gtt.region_id))' . ' GROUP BY s.shipping_id';
								$shipping_list1 = \Illuminate\Support\Facades\DB::select($sql);
								$list1[] = $shipping_list1;
							}
							else {
								$sql = 'SELECT s.shipping_id, s.shipping_code, s.shipping_name, shipping_order FROM ' . $prefix . 'shipping AS s ' . (' LEFT JOIN ' . $prefix . 'goods_transport_extend AS gted ON gted.tid = \'') . $trow['tid'] . ('\' AND gted.ru_id = \'' . $ru_id . '\'') . (' LEFT JOIN ' . $prefix . 'goods_transport_express AS gte ON gted.tid = gte.tid AND gte.ru_id = \'' . $ru_id . '\'') . ' WHERE FIND_IN_SET(s.shipping_id, gte.shipping_id) ' . ' AND ((FIND_IN_SET(\'' . $region[1] . '\', gted.top_area_id)) OR (FIND_IN_SET(\'' . $region[2] . '\', gted.area_id) OR FIND_IN_SET(\'' . $region[3] . '\', gted.area_id) OR FIND_IN_SET(\'' . $region[4] . '\', gted.area_id)))' . ' GROUP BY s.shipping_id';
								$shipping_list2 = \Illuminate\Support\Facades\DB::select($sql);
								$list2[] = $shipping_list2;
							}
						}
					}
				}

				$shipping_list1 = get_three_to_two_array($list1);
				$shipping_list2 = get_three_to_two_array($list2);
				if ($shipping_list1 && $shipping_list2) {
					$shipping_list = array_merge($shipping_list1, $shipping_list2);
				}
				else if ($shipping_list1) {
					$shipping_list = $shipping_list1;
				}
				else if ($shipping_list2) {
					$shipping_list = $shipping_list2;
				}

				foreach ($shipping_list as $k => $v) {
					$shipping_list[$k] = json_decode(json_encode($v), 1);
				}

				if ($shipping_list) {
					$new_shipping = array();

					foreach ($shipping_list as $key => $val) {
						@$new_shipping[$val['shipping_code']][] = $key;
					}

					foreach ($new_shipping as $key => $val) {
						if (1 < count($val)) {
							for ($i = 1; $i < count($val); $i++) {
								unset($shipping_list[$val[$i]]);
							}
						}
					}

					$shipping_list = get_array_sort($shipping_list, 'shipping_order');
				}
			}

			if ($shipping_list) {
				foreach ($shipping_list as $key => $val) {
					if (substr($val['shipping_code'], 0, 5) != 'ship_') {
						$freightModel = $this->shopConfigRepository->getShopConfigByCode('freight_model');

						if ($freightModel == 0) {
							if ($cart_goods) {
								if (count($cart_goods) == 1) {
									$cart_goods = array_values($cart_goods);
									if (!empty($cart_goods[0]['freight']) && $cart_goods[0]['is_shipping'] == 0) {
										if ($cart_goods[0]['freight'] == 1) {
											$configure_value = $cart_goods[0]['shipping_fee'] * $cart_goods[0]['goods_number'];
										}
										else {
											$trow = $this->goodsRepository->getGoodsTransport($cart_goods[0]['tid']);

											if ($trow['freight_type']) {
												$cart_goods[0]['user_id'] = $cart_goods[0]['ru_id'];
												$transport_tpl = $this->get_goods_transport_tpl($cart_goods[0], $region, $val, $cart_goods[0]['goods_number']);
												$configure_value = isset($transport_tpl['shippingFee']) ? $transport_tpl['shippingFee'] : 0;
											}
											else {
												$transport = array('top_area_id', 'area_id', 'tid', 'ru_id', 'sprice');
												$transport_where = ' AND ru_id = \'' . $cart_goods[0]['ru_id'] . '\' AND tid = \'' . $cart_goods[0]['tid'] . '\'';
												$goods_transport = $this->shopRepository->get_select_find_in_set(2, $consignee['city'], $transport, $transport_where, 'goods_transport_extend', 'area_id');
												$ship_transport = array('tid', 'ru_id', 'shipping_fee');
												$ship_transport_where = ' AND ru_id = \'' . $cart_goods[0]['ru_id'] . '\' AND tid = \'' . $cart_goods[0]['tid'] . '\'';
												$goods_ship_transport = $this->shopRepository->get_select_find_in_set(2, $val['shipping_id'], $ship_transport, $ship_transport_where, 'goods_transport_express', 'shipping_id');
												$goods_transport['sprice'] = isset($goods_transport['sprice']) ? $goods_transport['sprice'] : 0;
												$goods_ship_transport['shipping_fee'] = isset($goods_ship_transport['shipping_fee']) ? $goods_ship_transport['shipping_fee'] : 0;

												if ($trow['type'] == 1) {
													$configure_value = $goods_transport['sprice'] * $cart_goods[0]['goods_number'] + $goods_ship_transport['shipping_fee'] * $cart_goods[0]['goods_number'];
												}
												else {
													$configure_value = $goods_transport['sprice'] + $goods_ship_transport['shipping_fee'];
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

							$shipping_fee = $shipping_count == 0 ? 0 : $configure_value;
							$shipping_list[$key]['free_money'] = price_format(0, false);
						}

						$shipping_list[$key]['shipping_id'] = $val['shipping_id'];
						$shipping_list[$key]['shipping_name'] = $val['shipping_name'];
						$shipping_list[$key]['shipping_code'] = $val['shipping_code'];
						$shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
						$shipping_list[$key]['shipping_fee'] = $shipping_fee;
						if (isset($val['insure']) && $val['insure']) {
							$shipping_list[$key]['insure_formated'] = strpos($val['insure'], '%') === false ? price_format($val['insure'], false) : $val['insure'];
						}

						if ($val['shipping_id'] == $order['shipping_id']) {
							if (isset($val['insure']) && $val['insure']) {
								$insure_disabled = $val['insure'] == 0;
							}

							if (isset($val['support_cod']) && $val['support_cod']) {
								$cod_disabled = $val['support_cod'] == 0;
							}
						}

						$shipping_list[$key]['default'] = 0;

						if ($shipping_id == $val['shipping_id']) {
							$shipping_list[$key]['default'] = 1;
						}

						$shipping_list[$key]['insure_disabled'] = $insure_disabled;
						$shipping_list[$key]['cod_disabled'] = $cod_disabled;
					}

					if (substr($val['shipping_code'], 0, 5) == 'ship_') {
						unset($shipping_list[$key]);
					}
				}

				$shipping_type = array();

				foreach ($shipping_list as $key => $val) {
					@$shipping_type[$val['shipping_code']][] = $key;
				}

				foreach ($shipping_type as $key => $val) {
					if (1 < count($val)) {
						for ($i = 1; $i < count($val); $i++) {
							unset($shipping_list[$val[$i]]);
						}
					}
				}
			}
		}
		else {
			if ($cart_goods) {
				if (count($cart_goods) == 1) {
					$cart_goods = array_values($cart_goods);
					if (!empty($cart_goods[0]['freight']) && $cart_goods[0]['is_shipping'] == 0) {
						$configure_value = $cart_goods[0]['shipping_fee'] * $cart_goods[0]['goods_number'];
					}
					else {
						$configure_type = 1;
					}
				}
				else {
					$sprice = 0;

					foreach ($cart_goods as $key => $row) {
						if ($row['is_shipping'] == 0) {
							$sprice += $row['shipping_fee'] * $row['goods_number'];
						}
					}

					$configure_value = $sprice;
				}
			}

			$shipping_fee = $shipping_count == 0 ? 0 : $configure_value;
			$shipping_list[0]['free_money'] = price_format(0, false);
			$shipping_list[0]['format_shipping_fee'] = price_format($shipping_fee, false);
			$shipping_list[0]['shipping_fee'] = $shipping_fee;
			$shipping_list[0]['shipping_id'] = isset($seller_shipping['shipping_id']) && !empty($seller_shipping['shipping_id']) ? $seller_shipping['shipping_id'] : 0;
			$shipping_list[0]['shipping_name'] = isset($seller_shipping['shipping_name']) && !empty($seller_shipping['shipping_name']) ? $seller_shipping['shipping_name'] : '';
			$shipping_list[0]['shipping_code'] = isset($seller_shipping['shipping_code']) && !empty($seller_shipping['shipping_code']) ? $seller_shipping['shipping_code'] : '';
			$shipping_list[0]['default'] = 1;
		}

		$arr = array('is_freight' => $is_freight, 'shipping_list' => $shipping_list);
		return $arr;
	}

	private function get_goods_transport_tpl($goodsInfo = array(), $region = array(), $shippingInfo = array(), $goods_number = 1)
	{
		$goodsInfo['goods_weight'] = isset($goodsInfo['goods_weight']) ? $goodsInfo['goods_weight'] : $goodsInfo['goodsweight'];
		$goodsInfo['shop_price'] = isset($goodsInfo['shop_price']) ? $goodsInfo['shop_price'] : $goodsInfo['goods_price'];
		$prefix = \Illuminate\Support\Facades\Config::get('database.connections.mysql.prefix');

		if (empty($shippingInfo)) {
			$is_goods = 1;
			$shippingInfo = get_seller_shipping_type($goodsInfo['user_id']);

			if (!$shippingInfo) {
				$tpl_shipping = $this->get_goods_transport_tpl_shipping($goodsInfo['tid'], 0, $region);

				if ($tpl_shipping) {
					$shippingInfo = $tpl_shipping[0];
				}
			}
			else {
				$shippingInfo = $this->get_goods_transport_tpl_shipping($goodsInfo['tid'], $shippingInfo['shipping_id'], $region);
			}
		}
		else {
			$is_goods = 0;
			$shippingInfo = $this->get_goods_transport_tpl_shipping($goodsInfo['tid'], $shippingInfo['shipping_id'], $region);
		}

		$where = '';
		if ($shippingInfo && $shippingInfo['shipping_id']) {
			$where .= ' AND s.shipping_id = \'' . $shippingInfo['shipping_id'] . '\'';
		}
		else {
			$shippingInfo = $this->get_goods_transport_tpl_shipping($goodsInfo['tid'], 0, $region, $is_goods);

			if ($shippingInfo) {
				$shippingInfo = isset($shippingInfo[0]) ? $shippingInfo[0] : array();
			}
		}

		$sql = 'SELECT gtt.*, s.shipping_id, s.shipping_code, s.shipping_name, ' . ('s.shipping_desc, s.insure, s.support_cod, gtt.configure FROM ' . $prefix . 'shipping AS s, ') . ($prefix . 'goods_transport_tpl AS gtt ') . ' WHERE gtt.shipping_id = s.shipping_id ' . $where . ' AND s.enabled = 1 AND gtt.user_id = \'' . $goodsInfo['user_id'] . '\' AND gtt.tid = \'' . $goodsInfo['tid'] . '\'' . ' AND (FIND_IN_SET(\'' . $region[1] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[2] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[3] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[4] . '\', gtt.region_id))' . ' LIMIT 1';
		$val = \Illuminate\Support\Facades\DB::select($sql);

		if (0 < count($val)) {
			$val = $val[0];
		}
		else {
			$val = array();
		}

		$val = get_object_vars($val);
		$is_shipping = 0;

		if ($val) {
			$is_shipping = 1;
		}

		if (!$shippingInfo) {
			$shippingInfo = array('shipping_id' => 0, 'shipping_code' => '', 'configure' => '');
		}

		$shippingFee = 0;

		if ($is_shipping) {
			$goods_weight = $goodsInfo['goods_weight'] * $goods_number;
			$shop_price = $goodsInfo['shop_price'] * $goods_number;
			$shippingFee = shipping_fee($shippingInfo['shipping_code'], $shippingInfo['configure'], $goods_weight, $shop_price, $goods_number);
			$shippingCfg = unserialize_config($shippingInfo['configure']);
			$free_money = price_format($shippingCfg['free_money'], false);
		}

		$arr = array('shippingFee' => $shippingFee, 'shipping_fee_formated' => price_format($shippingFee, false), 'is_shipping' => $is_shipping, 'shipping_id' => $shippingInfo['shipping_id']);
		return $arr;
	}

	private function get_goods_transport_tpl_shipping($tid = 0, $shipping_id = 0, $region = array(), $type = 0, $limit = 0)
	{
		$where = '';

		if ($shipping_id) {
			$where .= ' AND gtt.shipping_id = \'' . $shipping_id . '\'';
		}

		if ($limit) {
			$where .= ' LIMIT ' . $limit;
		}

		$prefix = \Illuminate\Support\Facades\Config::get('database.connections.mysql.prefix');
		$sql = 'SELECT gtt.*, s.shipping_name, s.shipping_code FROM ' . $prefix . 'goods_transport_tpl AS gtt' . (' LEFT JOIN ' . $prefix . 'shipping AS s ON gtt.shipping_id = s.shipping_id') . (' WHERE gtt.tid = \'' . $tid . '\' ' . $where);
		$arr = array();

		if ($type == 1) {
			$res = \Illuminate\Support\Facades\DB::select($sql);

			foreach ($res as $key => $row) {
				$row = get_object_vars($row);
				$region_id = !empty($row['region_id']) ? explode(',', $row['region_id']) : array();

				if ($region) {
					foreach ($region as $rk => $rrow) {
						if ($region_id && in_array($rrow, $region_id)) {
							$arr[] = $row;
						}
						else {
							continue;
						}
					}
				}
			}
		}
		else {
			$res = \Illuminate\Support\Facades\DB::select($sql);

			foreach ($res as $key => $row) {
				$res = get_object_vars($row);
				$region_id = !empty($res['region_id']) ? explode(',', $res['region_id']) : array();

				foreach ($region as $rk => $rrow) {
					if ($region_id && in_array($rrow, $region_id)) {
						return $res;
					}
				}
			}
		}

		return $arr;
	}

	private function childOrder($cartGoods, $order, $consigneeInfo, $shipping)
	{
		$goodsList = $cartGoods['goods_list'];
		$total = $cartGoods['total'];
		$orderGoods = array();
		$ruIds = $this->getRuIds($goodsList);

		if (count($ruIds) <= 0) {
			return NULL;
		}

		$newShippingArr = array();

		foreach ($shipping['shipping'] as $v) {
			$newShippingArr[$v['ru_id']] = $v['shipping_id'];
		}

		$newShippingFeeArr = array();
		if (isset($shipping['shipping_fee_list']) && !empty($shipping['shipping_fee_list'])) {
			foreach ($shipping['shipping_fee_list'] as $k => $v) {
				$newShippingFeeArr[$k] = $v;
			}
		}

		$newShippingName = explode(',', $order['shipping_name']);
		$newShippingNameArr = array();

		foreach ($newShippingName as $v) {
			$temp = explode('|', $v);
			$newShippingNameArr[$temp[0]] = $temp[1];
		}

		foreach ($goodsList as $key => $value) {
			$userId = 0;
			$goodsAmount = 0;
			$orderAmount = 0;
			$newOrder = array();
			$orderGoods = array();

			foreach ($value['goods'] as $v) {
				if ($v['ru_id'] != $value['ru_id']) {
					continue;
				}

				$userId = $value['user_id'];
				$goodsAmount += $v['goods_number'] * $v['goods_price'];
				$orderAmount += $v['goods_number'] * $v['goods_price'] - $order['coupons'];
			}

			$newOrder = array('main_order_id' => $order['order_id'], 'order_sn' => $this->getOrderSn(), 'user_id' => $userId, 'shipping_id' => $newShippingArr[$value['ru_id']], 'shipping_name' => $newShippingNameArr[$value['ru_id']], 'shipping_fee' => empty($newShippingFeeArr[$value['ru_id']]) || !isset($newShippingFeeArr[$value['ru_id']]) ? 0 : $newShippingFeeArr[$value['ru_id']], 'pay_id' => $order['pay_id'], 'pay_name' => '微信支付', 'goods_amount' => $goodsAmount, 'order_amount' => $orderAmount, 'add_time' => gmtime(), 'order_status' => $order['order_status'], 'shipping_status' => $order['shipping_status'], 'pay_status' => $order['pay_status'], 'tax_id' => $order['tax_id'], 'inv_payee' => $order['inv_payee'], 'inv_content' => $order['inv_content'], 'vat_id' => $order['vat_id'], 'invoice_type' => $order['invoice_type'], 'froms' => '微信小程序', 'coupons' => $order['coupons'], 'consignee' => $consigneeInfo->consignee, 'country' => $consigneeInfo->country, 'province' => $consigneeInfo->province, 'city' => $consigneeInfo->city, 'mobile' => $consigneeInfo->mobile, 'tel' => $consigneeInfo->tel, 'zipcode' => $consigneeInfo->zipcode, 'district' => $consigneeInfo->district, 'address' => $consigneeInfo->address, 'extension_code' => $order['extension_code'], 'team_id' => $order['team_id'], 'team_parent_id' => $order['team_parent_id'], 'team_user_id' => $order['team_user_id']);
			$new_order_id = $this->orderRepository->insertGetId($newOrder);

			foreach ($value['goods'] as $v) {
				if ($v['ru_id'] != $value['ru_id']) {
					continue;
				}

				$orderGoods[] = array('order_id' => $new_order_id, 'goods_id' => $v['goods_id'], 'goods_name' => $v['goods_name'], 'goods_sn' => $v['goods_sn'], 'product_id' => $v['product_id'], 'goods_number' => $v['goods_number'], 'market_price' => $v['market_price'], 'goods_price' => $v['goods_price'], 'goods_attr' => $v['goods_attr'], 'is_real' => $v['is_real'], 'extension_code' => $v['extension_code'], 'parent_id' => $v['parent_id'], 'is_gift' => $v['is_gift'], 'model_attr' => $v['model_attr'], 'goods_attr_id' => $v['goods_attr_id'], 'ru_id' => $v['ru_id'], 'shipping_fee' => $v['shipping_fee'], 'warehouse_id' => $v['warehouse_id'], 'area_id' => $v['area_id']);
			}

			$this->orderGoodsRepository->insertOrderGoods($orderGoods);
		}
	}

	private function getRuIds($cartGoods)
	{
		$arr = array();

		foreach ($cartGoods as $v) {
			if (in_array($v['ru_id'], $arr)) {
				continue;
			}

			$arr[] = $v['ru_id'];
		}

		return $arr;
	}

	public function flow_cart_stock($arr)
	{
		foreach ($arr as $key => $val) {
			$val = intval(make_semiangle($val));
			if ($val <= 0 || !is_numeric($key)) {
				continue;
			}

			$goods = $this->cartRepository->field(array('goods_id', 'goods_attr_id', 'extension_code'))->find($key);
			$row = $this->goodsRepository->cartGoods($key);
			$goodsExtendsionCode = empty($goods['extension_code']) ? '' : $goods['extension_code'];
			if (0 < intval($this->shopConfigRepository->getShopConfigByCode('use_storage')) && $goodsExtendsionCode != 'package_buy') {
				if ($row['goods_number'] < $val) {
					return false;
				}

				$row['product_id'] = trim($row['product_id']);

				if (!empty($row['product_id'])) {
					@$product_number = $this->productRepository->findBy(array('goods_id' => $goods['goods_id'], 'product_id' => $row['product_id']))->column('product_number');

					if ($product_number < $val) {
						return false;
					}
				}
			}
		}

		return true;
	}

	public function getOrderSn()
	{
		mt_srand((double) microtime() * 1000000);
		return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	}

	private function clear_cart_ids($arr, $type = CART_GENERAL_GOODS)
	{
		$uid = app('config')->get('uid');
		$this->cartRepository->deleteAll(array(
	array('in', 'rec_id', $arr),
	array('rec_type', $type),
	array('user_id', $uid)
	));
	}

	public function shippingFee($args)
	{
		$result = array('error' => 0, 'message' => '');
		$shippingId = isset($args['id']) ? intval($args['id']) : 0;
		$ruId = isset($args['ru_id']) ? intval($args['ru_id']) : 0;
		$address = isset($args['address']) ? intval($args['address']) : 0;
		$uc_id = isset($args['uc_id']) ? intval($args['uc_id']) : 0;
		$this->userId = $args['uid'];
		$coupons_info = $this->couponsRepository->getcoupons($this->userId, $uc_id, array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
		$cart_goods = $this->cartRepository->getGoodsInCartByUser($args['uid'], $args['flow_type']);
		$cart_goods_list = $cart_goods['product'];

		if (empty($cart_goods_list)) {
			$result['error'] = 1;
			$result['message'] = '购物车没有商品';
			return $result;
		}

		foreach ($cart_goods_list as $key => $val) {
			if (0 < $shippingId && $val['goods']['ru_id'] == $ruId) {
				$cart_goods_list[$key]['goods']['tmp_shipping_id'] = $shippingId;
			}
		}

		$this->defaultAddress = $this->addressRepository->getDefaultByUserId($args['uid']);
		$cart_value = '';

		foreach ($cart_goods_list as $k => $v) {
			$cart_goods_list[$k] = $v['goods'];

			if ($v['goods']['ru_id'] == $ruId) {
				$cart_value = $cart_value . ',' . $v['goods']['rec_id'];
			}
		}

		$cart_value = substr($cart_value, 1);
		$shipFee = $this->getRuShippngInfo($cart_goods_list, $cart_value, $ruId);
		$shipList = $shipFee['shipping_list'];

		foreach ($shipList as $k => $v) {
			if ($v['shipping_id'] == $shippingId) {
				$shipFee = $v['shipping_fee'];
			}
		}

		if ($shipFee !== '0' || $shipFee !== 0) {
			$newShipFee = strip_tags(preg_replace('/([\\x80-\\xff]*|[a-zA-Z])/i', '', $shipFee));
			$result['fee'] = '0';

			if (0 < floatval($newShipFee)) {
				$result['fee'] = $newShipFee;
			}
		}
		else {
			$result['error'] = 1;
			$result['message'] = '该地区不支持配送';
		}

		if (0 < $uc_id) {
			$coupons_info = $this->couponsRepository->getcoupons($this->userId, $uc_id, array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id', 'cu.user_id'));
			$result['cou_money'] = $coupons_info['cou_money'];
			$result['cou_type'] = $coupons_info['cou_type'];
		}

		$result['fee_formated'] = $shipFee;
		return $result;
	}

	public function orderDetail($args)
	{
		$main_order = $this->orderRepository->orderDetail($args['uid'], $args['main_order_id']);

		if (empty($main_order)) {
			return array();
		}

		$son_order = $this->orderRepository->orderMainDetail($args['uid'], $args['main_order_id']);
		$list = array('order_amount' => price_format($main_order['order_amount'], false), 'order_amount_formated' => price_format($main_order['order_amount'], false), 'order_sn' => $son_order,'order_id' => $son_order[0]['order_id']);
		return $list;
	}
}


?>
