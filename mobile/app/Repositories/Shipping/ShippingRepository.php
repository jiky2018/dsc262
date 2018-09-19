<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Shipping;

class ShippingRepository
{
	private $goodsRepository;
	private $addressRepository;

	public function __construct(\App\Repositories\Goods\GoodsRepository $goodsRepository, \App\Repositories\User\AddressRepository $addressRepository)
	{
		$this->goodsRepository = $goodsRepository;
		$this->addressRepository = $addressRepository;
	}

	public function shippingList()
	{
		$shippingList = \App\Models\Shipping::select('*')->get()->toArray();
		return $shippingList;
	}

	public function find($id)
	{
		$shipping = \App\Models\Shipping::select('*')->where('shipping_id', $id)->where('enabled', 1)->first();

		if ($shipping === null) {
			return array();
		}

		return $shipping->toArray();
	}

	public function total_shipping_fee($address, $products, $shipping_id, $ruId = 0)
	{
		$weight = 0;
		$amount = 0;
		$number = 0;

		foreach ($products as $key => $value) {
			$pro[$key]['goods_id'] = $value['goods']['goods_id'];
			$pro[$key]['goods_number'] = $value['goods']['goods_number'];
			$pro[$key]['is_shipping'] = $value['goods']['is_shipping'];
		}

		$IsShippingFree = true;

		if (isset($pro)) {
			foreach ($pro as $product) {
				$goods_weight = \App\Models\Goods::where(array('goods_id' => $product['goods_id']))->pluck('goods_weight')->toArray();
				$goods_weight = $goods_weight[0];
				$goods_weight = 0 < count($goods_weight) ? $goods_weight[0] : 0;

				if ($goods_weight) {
					$weight += $goods_weight * $product['goods_number'];
				}

				$amount += $this->goodsRepository->getFinalPrice($product['goods_id'], $product['goods_number']);
				$number += $product['goods_number'];

				if (!intval($product['is_shipping'])) {
					$IsShippingFree = false;
				}
			}
		}

		if ($IsShippingFree) {
			return 0;
		}

		$result = \App\Models\ShippingArea::select('shipping_area.*')->with(array('shipping' => function($query) {
			$query->select('shipping_id', 'shipping_name', 'insure', 'shipping_code');
		}))->where('ru_id', $ruId)->where('shipping_id', $shipping_id)->first();

		if ($result === null) {
			$result = array();
		}
		else {
			$result = $result->toArray();
		}

		if (!empty($result['configure'])) {
			$configure = $this->getConfigure($result['configure']);
			$fee = $this->calculate($configure, $result['shipping']['shipping_code'], $weight, $amount, $number);
			return price_format($fee, false);
		}

		return false;
	}

	private function calculate($configure, $shipping_code, $goods_weight, $goods_amount, $goods_number)
	{
		$fee = 0;
		if (0 < $configure['free_money'] && $configure['free_money'] <= $goods_amount) {
			return $fee;
		}

		switch ($shipping_code) {
		case 'city_express':
		case 'flat':
			$fee = isset($configure['base_fee']) ? $configure['base_fee'] : 0;
			break;

		case 'ems':
			$fee = isset($configure['base_fee']) ? $configure['base_fee'] : 0;
			$configure['fee_compute_mode'] = !empty($configure['fee_compute_mode']) ? $configure['fee_compute_mode'] : 'by_weight';

			if ($configure['fee_compute_mode'] == 'by_number') {
				$fee = $goods_number * $configure['item_fee'];
			}
			else if (0.5 < $goods_weight) {
				$fee += ceil(($goods_weight - 0.5) / 0.5) * $configure['step_fee'];
			}

			break;

		case 'post_express':
			$fee = isset($configure['base_fee']) ? $configure['base_fee'] : 0;
			$configure['fee_compute_mode'] = !empty($configure['fee_compute_mode']) ? $configure['fee_compute_mode'] : 'by_weight';

			if ($configure['fee_compute_mode'] == 'by_number') {
				$fee = $goods_number * $configure['item_fee'];
			}
			else if (5 < $goods_weight) {
				$fee += 8 * $configure['step_fee'];
				$fee += ceil(($goods_weight - 5) / 0.5) * $configure['step_fee1'];
			}
			else if (1 < $goods_weight) {
				$fee += ceil(($goods_weight - 1) / 0.5) * $configure['step_fee'];
			}

			break;

		case 'post_mail':
			$fee = $configure['base_fee'] + $configure['pack_fee'];
			$configure['fee_compute_mode'] = !empty($configure['fee_compute_mode']) ? $configure['fee_compute_mode'] : 'by_weight';

			if ($configure['fee_compute_mode'] == 'by_number') {
				$fee = $goods_number * ($configure['item_fee'] + $configure['pack_fee']);
			}
			else if (5 < $goods_weight) {
				$fee += 4 * $configure['step_fee'];
				$fee += ceil($goods_weight - 5) * $configure['step_fee1'];
			}
			else if (1 < $goods_weight) {
				$fee += ceil($goods_weight - 1) * $configure['step_fee'];
			}

			break;

		case 'presswork':
			$fee = $goods_weight * 4 + 3.3999999999999999;

			if (0.10000000000000001 < $goods_weight) {
				$fee += ceil(($goods_weight - 0.10000000000000001) / 0.10000000000000001) * 0.40000000000000002;
			}

			break;

		case 'sf_express':
		case 'sto_express':
		case 'yto':
			if (0 < $configure['free_money'] && $configure['free_money'] <= $goods_amount) {
				return 0;
			}
			else {
				$fee = isset($configure['base_fee']) ? $configure['base_fee'] : 0;
				$configure['fee_compute_mode'] = !empty($configure['fee_compute_mode']) ? $configure['fee_compute_mode'] : 'by_weight';

				if ($configure['fee_compute_mode'] == 'by_number') {
					$fee = $goods_number * $configure['item_fee'];
				}
				else if (1 < $goods_weight) {
					$fee += ceil($goods_weight - 1) * $configure['step_fee'];
				}
			}

			break;

		case 'zto':
			$fee = isset($configure['base_fee']) ? $configure['base_fee'] : 0;
			$configure['fee_compute_mode'] = !empty($configure['fee_compute_mode']) ? $configure['fee_compute_mode'] : 'by_weight';

			if ($configure['fee_compute_mode'] == 'by_number') {
				$fee = $goods_number * $configure['item_fee'];
			}
			else if (1 < $goods_weight) {
				$fee += ceil($goods_weight - 1) * $configure['step_fee'];
			}

			break;

		default:
			$fee = 0;
			break;
		}

		$fee = floatval($fee);
		return $fee;
	}

	private function getConfigure($configure)
	{
		$data = array();
		$configure = unserialize($configure);

		foreach ($configure as $key => $val) {
			$data[$val['name']] = $val['value'];
		}

		return $data;
	}

	public function getSellerShippingType($ru_id)
	{
		$res = \App\Models\SellerShopinfo::select('shipping.shipping_id', 'shipping.shipping_name', 'shipping.shipping_code')->join('shipping', 'shipping.shipping_id', '=', 'seller_shopinfo.shipping_id')->where('ru_id', $ru_id)->first();

		if ($res) {
			return $res->toArray();
		}

		return array();
	}
}


?>
