<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Order;

class OrderRepository
{
	private $cartRepository;
	private $bonusTypeRepository;
	private $shippingRepository;

	public function __construct(\App\Repositories\Cart\CartRepository $cartRepository, \App\Repositories\Bonus\BonusTypeRepository $bonusTypeRepository, \App\Repositories\Shipping\ShippingRepository $shippingRepository)
	{
		$this->cartRepository = $cartRepository;
		$this->bonusTypeRepository = $bonusTypeRepository;
		$this->shippingRepository = $shippingRepository;
	}

	public function orderNum($id, $status = NULL)
	{
		$model = \App\Models\OrderInfo::select('*')->where('user_id', $id)->where('order_status', '<>', OS_CANCELED)->where('extension_code', '')->where('main_order_id', '<>', 0);

		if ($status === null) {
			$orderNum = $model->count();
			return $orderNum;
		}

		if ($status === STATUS_CREATED) {
			$model->wherein('pay_status', array(PS_UNPAYED));
		}

		if (!empty($status)) {
			switch ($status) {
			case STATUS_PAID:
				$model->wherein('pay_status', array(PS_PAYED));
				break;

			case STATUS_DELIVERING:
				$model->wherein('shipping_status', array(SS_SHIPPED, SS_SHIPPED_PART, OS_SHIPPED_PART));
				break;

			case STATUS_DELIVERIED:
				$model->wherein('shipping_status', array(SS_RECEIVED));
				break;
			}
		}

		$orderNum = $model->count();
		return $orderNum;
	}

	public function getReceived($id)
	{
		$sql = ' select og.order_id, og.goods_id, og.goods_name, og.goods_attr, og.goods_price, g.goods_thumb, g.user_id, og.rec_id from dsc_order_info oi ';
		$sql .= ' left join dsc_order_goods og on oi.order_id=og.order_id ';
		$sql .= ' left join dsc_goods g on og.goods_id=g.goods_id ';
		$sql .= ' where oi.user_id=' . $id . ' AND order_status <> 2 AND shipping_status = 2 AND not exists (select 1 from `dsc_comment` where dsc_comment.rec_id = og.rec_id) ';
		$list = \Illuminate\Support\Facades\DB::select($sql);
		return $list;
	}

	public function orderAppraiseDetail($id, $orderId, $goodsId)
	{
		$model = \App\Models\OrderInfo::select('order_id')->with(array('goods' => function($query) use($goodsId) {
			$query->leftjoin('goods', 'goods.goods_id', '=', 'order_goods.goods_id')->leftjoin('comment', 'comment.rec_id', '=', 'order_goods.rec_id')->where('goods.goods_id', $goodsId)->select('order_goods.order_id', 'order_goods.goods_id', 'order_goods.goods_name', 'order_goods.goods_attr', 'order_goods.goods_price', 'goods.goods_thumb', 'order_goods.goods_price', 'comment.rec_id');
		}))->where('user_id', $id)->where('order_id', $orderId)->where('order_status', '<>', OS_CANCELED);
		$model->wherein('shipping_status', array(SS_RECEIVED));
		$list = $model->first();

		if ($list == null) {
			return array();
		}

		return $list->toArray();
	}

	public function orderDetail($uid, $orderId)
	{
		$order = \App\Models\OrderInfo::select('*')->where('user_id', $uid)->where('order_id', $orderId)->first();

		if ($order == null) {
			return array();
		}

		return $order;
	}

	public function orderMainDetail($uid, $orderId)
	{
		$order = \App\Models\OrderInfo::select('order_sn')->where('user_id', $uid)->where('main_order_id', $orderId)->get()->toArray();
		return $order;
	}

	public function find($orderId)
	{
		$order = \App\Models\OrderInfo::where('order_id', $orderId)->first();

		if ($order == null) {
			return array();
		}

		return $order;
	}

	public function orderCancel($uid, $orderId)
	{
		$order = \App\Models\OrderInfo::where('user_id', $uid)->where('order_id', $orderId)->first();
		$order->order_status = 2;
		return $order->save();
	}

	public function orderConfirm($uid, $orderId)
	{
		$order = \App\Models\OrderInfo::where('user_id', $uid)->where('order_id', $orderId)->first();
		$order->order_status = 1;
		$order->shipping_status = SS_RECEIVED;
		$order->confirm_take_time = gmtime();
		return $order->save();
	}

	public function orderPay($uid, array $orderId)
	{
		$array = array('order_status' => OS_CONFIRMED, 'pay_status' => PS_PAYED, 'pay_time' => gmtime(), 'money_paid' => \Illuminate\Support\Facades\DB::Raw('order_amount'), 'order_amount' => 0);
		return \App\Models\OrderInfo::where('user_id', $uid)->wherein('order_id', $orderId)->update($array);
	}

	public function getOrderGoods($orderId)
	{
		$goods = \App\Models\OrderGoods::where('order_id', $orderId)->select('goods.goods_thumb', 'order_goods.goods_price', 'order_goods.goods_number', 'order_goods.goods_id', 'order_goods.goods_name', 'order_goods.goods_sn', 'order_goods.ru_id')->join('goods', 'goods.goods_id', '=', 'order_goods.goods_id')->get();

		if ($goods == null) {
			return array();
		}

		return $goods->toArray();
	}

	public function getChildOrder($orderId)
	{
		return \App\Models\OrderInfo::where('main_order_id', $orderId)->select('order_id')->get()->toArray();
	}

	public function getOrderByUserId($id, $status = 0, $type = '', $page = 0, $size = 10)
	{
		$model = \App\Models\OrderInfo::select('*')->where('user_id', $id)->where('order_status', '<>', OS_CANCELED);

		if (!empty($status)) {
			switch ($status) {
			case STATUS_PAID:
				$model->wherein('pay_status', array(PS_UNPAYED));
				break;

			case STATUS_DELIVERING:
				$model->wherein('shipping_status', array(SS_SHIPPED, SS_SHIPPED_PART, OS_SHIPPED_PART));
				break;
			}
		}

		if (empty($type)) {
			$model->where('extension_code', '');
			$model->where('main_order_id', '<>', 0);
		}

		if (!empty($type)) {
			switch ($type) {
			case 'bargain':
				$model->where('extension_code', 'bargain_buy');
				break;

			case 'team':
				$model->where('extension_code', 'team_buy');
				break;
			}
		}

		$order = $model->select(array('order_id', 'order_sn', 'order_status', 'shipping_name', 'shipping_id', 'pay_status', 'goods_amount', 'order_amount', 'add_time', 'shipping_status', 'shipping_status', 'money_paid', 'shipping_fee', 'extension_code', 'invoice_no'))->with(array('goods' => function($query) {
			$query->leftjoin('goods', 'goods.goods_id', '=', 'order_goods.goods_id')->select('order_goods.order_id', 'order_goods.goods_number', 'order_goods.goods_id', 'order_goods.goods_name', 'order_goods.goods_attr', 'order_goods.goods_price', 'goods.goods_thumb', 'goods.user_id');
		}))->orderBy('add_time', 'DESC')->offset(($page - 1) * $size)->limit($size)->get()->toArray();
		return $order;
	}

	public function insertGetId($order)
	{
		$orderModel = new \App\Models\OrderInfo();

		foreach ($order as $k => $v) {
			$orderModel->$k = $v;
		}

		$res = $orderModel->save();

		if ($res) {
			return $orderModel->order_id;
		}

		return false;
	}

	public function changeOrderGoodsStorage($order_id, $is_dec = true, $storage = 0)
	{
		switch ($storage) {
		case 0:
			$res = \App\Models\OrderGoods::where('order_id', $order_id)->where('is_real', 1)->groupBy('goods_id')->groupBy('product_id')->select(array('sum(send_number) as num', 'goods_id,max(extension_code) as extension_code', 'product_id'))->get()->toArray();
			break;

		case 1:
			$res = \App\Models\OrderGoods::where(array('order_id' => $order_id))->where(array('is_real' => 1))->groupBy('goods_id')->groupBy('product_id')->selectRaw('sum(goods_number) as num, goods_id,max(extension_code) as extension_code, product_id')->get()->toArray();
			break;
		}

		foreach ($res as $key => $row) {
			if ($row['extension_code'] != 'package_buy') {
				if ($is_dec) {
					$this->change_goods_storage($row['goods_id'], $row['product_id'], 0 - $row['num']);
				}
				else {
					$this->change_goods_storage($row['goods_id'], $row['product_id'], $row['num']);
				}
			}
		}
	}

	public function change_goods_storage($good_id, $product_id, $number = 0)
	{
		if ($number == 0) {
			return true;
		}

		if (empty($good_id) || empty($number)) {
			return false;
		}

		$number = 0 < $number ? '+ ' . $number : $number;
		$products_query = true;

		if (!empty($product_id)) {
			$products_query = \App\Models\Products::where('goods_id', $good_id)->where('product_id', $product_id)->first();
			$products_query->product_number += $number;
			$products_query->save();
		}

		$query = \App\Models\Goods::where('goods_id', $good_id)->first();
		$query->goods_number += $number;
		$query->save();
		if ($query && $products_query) {
			return true;
		}
		else {
			return false;
		}
	}

	public function order_fee($order, $goods, $consignee, $cart_good_id = 0, $shipping, $consignee_id)
	{
		if (!isset($order['extension_code'])) {
			$order['extension_code'] = '';
		}

		$total = array('real_goods_count' => 0, 'gift_amount' => 0, 'goods_price' => 0, 'market_price' => 0, 'discount' => 0, 'pack_fee' => 0, 'card_fee' => 0, 'shipping_fee' => 0, 'shipping_insure' => 0, 'integral_money' => 0, 'bonus' => 0, 'surplus' => 0, 'cod_fee' => 0, 'pay_fee' => 0, 'tax' => 0);
		$weight = 0;
		$newGoodsList = array();

		foreach ($goods as $val) {
			foreach ($val['goods'] as $v) {
				if (!empty($v['is_real'])) {
					$total['real_goods_count']++;
				}

				$total['goods_price'] += $v['goods_price'] * $v['goods_number'];
				$total['market_price'] += $v['market_price'] * $v['goods_number'];
				$newGoodsList[] = array('goods' => $v);
			}
		}

		$total['saving'] = $total['market_price'] - $total['goods_price'];
		$total['save_rate'] = $total['market_price'] ? round($total['saving'] * 100 / $total['market_price']) . '%' : 0;
		$total['goods_price_formated'] = price_format($total['goods_price'], false);
		$total['market_price_formated'] = price_format($total['market_price'], false);
		$total['saving_formated'] = price_format($total['saving'], false);
		$total['discount'] = $this->cartRepository->computeDiscountCheck($goods);

		if ($total['goods_price'] < $total['discount']) {
			$total['discount'] = $total['goods_price'];
		}

		$total['discount_formated'] = price_format($total['discount'], false);
		if (!empty($order['need_inv']) && $order['inv_type'] != '') {
			$rate = 0;

			foreach ($GLOBALS['_CFG']['invoice_type']['type'] as $key => $type) {
				if ($type == $order['inv_type']) {
					$rate = floatval($GLOBALS['_CFG']['invoice_type']['rate'][$key]) / 100;
					break;
				}
			}

			if (0 < $rate) {
				$total['tax'] = $rate * $total['goods_price'];
			}
		}

		$total['tax_formated'] = price_format($total['tax'], false);

		if (!empty($order['bonus_id'])) {
			$bonus = $this->bonusTypeRepository->bonusInfo($order['bonus_id']);
			$total['bonus'] = $bonus['type_money'];
		}

		$total['bonus_formated'] = price_format($total['bonus'], false);

		if (!empty($order['bonus_kill'])) {
			$total['bonus_kill'] = $order['bonus_kill'];
			$total['bonus_kill_formated'] = price_format($total['bonus_kill'], false);
		}

		$shipping_cod_fee = null;
		$shippingArr = explode(',', $order['shipping_id']);
		if (0 < count($shippingArr) && 0 < $total['real_goods_count']) {
			$region['country'] = $consignee['country'];
			$region['province'] = $consignee['province'];
			$region['city'] = $consignee['city'];
			$region['district'] = $consignee['district'];
			$shippingFee = 0;

			foreach ($shippingArr as $k => $v) {
				$temp = explode('|', $v);
				$cart_value = '';
				$newGoodsListShip = array();

				foreach ($newGoodsList as $newK => $newV) {
					$cart_value = ', ' . $newV['goods']['rec_id'];
					$newGoodsListShip[$newK] = $newV['goods'];
				}

				$cart_value = substr($cart_value, 1);
				$shipRes = app('App\\Services\\flowService')->getRuShippngInfo($newGoodsListShip, $cart_value, $temp[0]);
				$shippingList = $shipRes['shipping_list'];
				$shipFee = 0;

				foreach ($shippingList as $shipK => $shipV) {
					if ($shipV['shipping_id'] == $temp[1]) {
						$shipFee = $shipV['shipping_fee'];
					}
				}

				$newShipFee = strip_tags(preg_replace('/([\\x80-\\xff]*|[a-zA-Z])/i', '', $shipFee));

				if (0 < floatval($newShipFee)) {
					$shippingFee += $newShipFee;
					$total['shipping_fee_list'][$temp[0]] = $newShipFee;
				}
			}

			$total['shipping_fee'] = $shippingFee;
		}

		$total['shipping_fee_formated'] = price_format($total['shipping_fee'], false);
		$bonus_amount = $this->cartRepository->computeDiscountCheck($goods);
		$max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : $total['goods_price'] - $bonus_amount;
		if ($order['extension_code'] == 'group_buy' && 0 < $group_buy['deposit']) {
			$total['amount'] = $total['goods_price'];
		}
		else {
			$total['amount'] = $total['goods_price'] - $total['discount'] + $total['tax'] + $total['pack_fee'] + $total['card_fee'] + $total['shipping_fee'] + $total['shipping_insure'] + $total['cod_fee'];
			$use_bonus = min($total['bonus'], $max_amount);

			if (isset($total['bonus_kill'])) {
				$use_bonus_kill = min($total['bonus_kill'], $max_amount);
				$total['amount'] -= $price = number_format($total['bonus_kill'], 2, '.', '');
			}

			$total['bonus'] = $use_bonus;
			$total['bonus_formated'] = price_format($total['bonus'], false);
			$total['amount'] -= $use_bonus;
			$max_amount -= $use_bonus;
		}

		$order['integral'] = 0 < $order['integral'] ? $order['integral'] : 0;
		if (0 < $total['amount'] && 0 < $max_amount && 0 < $order['integral']) {
			$integral_money = self::value_of_integral($order['integral']);
			$use_integral = min($total['amount'], $max_amount, $integral_money);
			$total['amount'] -= $use_integral;
			$total['integral_money'] = $use_integral;
			$order['integral'] = self::integral_of_value($use_integral);
		}
		else {
			$total['integral_money'] = 0;
			$order['integral'] = 0;
		}

		$total['integral'] = $order['integral'];
		$total['integral_formated'] = price_format($total['integral_money'], false);
		$se_flow_type = isset($_SESSION['flow_type']) ? $_SESSION['flow_type'] : '';

		if ($order['extension_code'] == 'group_buy') {
			$total['will_get_integral'] = $group_buy['gift_integral'];
		}
		else if ($order['extension_code'] == 'exchange_goods') {
			$total['will_get_integral'] = 0;
		}
		else {
			$total['will_get_integral'] = $this->cartRepository->getGiveIntegral();
		}

		$total['will_get_bonus'] = 0;
		$total['formated_goods_price'] = price_format($total['goods_price'], false);
		$total['formated_market_price'] = price_format($total['market_price'], false);
		$total['formated_saving'] = price_format($total['saving'], false);
		return $total;
	}

	public function shippingName($com_id)
	{
		return \App\Models\Shipping::where('shipping_id', $com_id)->value('shipping_code');
	}
}


?>
