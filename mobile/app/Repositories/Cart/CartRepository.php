<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Cart;

class CartRepository
{
	const FAR_ALL = 0;
	const FAR_CATEGORY = 1;
	const FAT_PRICE = 1;
	const FAT_DISCOUNT = 2;
	const FAR_BRAND = 2;
	const FAR_GOODS = 3;

	private $model;
	private $shopConfigRepository;
	private $userRankRepository;
	private $authService;
	private $goodsRepository;
	private $shopRepository;

	public function __construct(\App\Repositories\ShopConfig\ShopConfigRepository $shopConfigRepository, \App\Repositories\User\UserRankRepository $userRankRepository, \App\Services\AuthService $authService, \App\Repositories\Goods\GoodsRepository $goodsRepository, \App\Repositories\Shop\ShopRepository $shopRepository)
	{
		$this->shopConfigRepository = $shopConfigRepository;
		$this->userRankRepository = $userRankRepository;
		$this->authService = $authService;
		$this->goodsRepository = $goodsRepository;
		$this->shopRepository = $shopRepository;
		$this->model = \App\Models\Cart::where('rec_id', '<>', 0);
	}

	public function field($columns)
	{
		$this->model->select($columns);
		return $this;
	}

	public function find($rec_id)
	{
		$cart = $this->model->where('rec_id', $rec_id)->first();

		if ($cart === null) {
			return array();
		}

		return $cart->toArray();
	}

	public function addGoodsToCart($params)
	{
		$model = new \App\Models\Cart();

		foreach ($params as $k => $v) {
			$model->$k = $v;
		}

		$res = $model->save();

		if ($res) {
			return $model->goods_number;
		}

		return false;
	}

	public function getAllCartGoods()
	{
		$cart = \App\Models\Cart::select('rec_id', 'user_id', 'goods_id', 'goods_name', 'market_price', 'goods_price', 'goods_number', 'goods_attr', 'ru_id')->get()->toArray();
		return $cart;
	}

	public function getCartByGoods($uid, $goodsId, $goodsAttr = '')
	{
		$cart = \App\Models\Cart::where('user_id', $uid)->where('goods_id', $goodsId)->where('goods_attr_id', $goodsAttr)->where('rec_type', 0)->first();

		if ($cart === null) {
			return array();
		}

		return $cart->toArray();
	}

	public function goodsNumInCartByUser($id, $flow_type = 0)
	{
		$cart_list = \App\Models\Cart::where('user_id', $id)->where('rec_type', $flow_type)->sum('goods_number');
		return $cart_list;
	}

	public function getTeamGoodsInCart($id, $flow_type = 0)
	{
		$cart = \App\Models\Cart::select('*')->where('user_id', $id)->where('rec_type', $flow_type)->first();

		if ($cart === null) {
			return array();
		}

		return $cart->toArray();
	}

	public function getGoodsInCartByUser($id, $flow_type = 0)
	{
		$cart_list = \App\Models\Cart::select('cart.*')->with(array('goods' => function($query) {
			$query->select('goods_id', 'cat_id', 'goods_name', 'goods_thumb', 'freight', 'tid', 'goods_weight', 'shipping_fee', 'cloud_id', 'cloud_goodsname');
		}))->where('user_id', $id)->where('rec_type', $flow_type)->orderby('rec_id', 'desc')->get()->toArray();
		$cart = array();

		foreach ($cart_list as $key => $value) {
			if (isset($value['goods']['goods_id'])) {
				$cart[$key] = $value;
			}
		}

		$total = array('goods_price' => 0, 'market_price' => 0, 'goods_number' => 0);
		$goods_list = array();
		$virtual_goods_count = 0;
		$real_goods_count = 0;

		foreach ($cart as $v) {
			$total['goods_price'] += $v['goods_price'] * $v['goods_number'];
			$total['market_price'] += $v['market_price'] * $v['goods_number'];
			$total['goods_number'] += $v['goods_number'];
			$v['subtotal'] = price_format($v['goods_price'] * $v['goods_number'], false);
			$v['goods_price_format'] = price_format($v['goods_price'], false);
			$v['market_price_format'] = price_format($v['market_price'], false);

			if ($v['is_real']) {
				$real_goods_count++;
			}
			else {
				$virtual_goods_count++;
			}

			$shopInfo = $this->shopRepository->findBY('ru_id', $v['ru_id']);

			if (0 < count($shopInfo)) {
				$shopInfo = $shopInfo[0];

				if ($shopInfo['shopname_audit'] == 1) {
					if (0 < $v['ru_id']) {
						$v['shop_name'] = $shopInfo['brandName'] . $shopInfo['shopNameSuffix'];
					}
					else {
						$v['shop_name'] = $shopInfo['shop_name'];
					}
				}
				else {
					$v['shop_name'] = $shopInfo['rz_shopName'];
				}
			}
			else {
				$v['shop_name'] = '';
			}

			$goods_list[] = $v;
		}

		$tmpArray = array();
		$goodslist = array();

		foreach ($goods_list as $key => $row) {
			$row['goods']['rec_id'] = $row['rec_id'];
			$row['goods']['market_price'] = $row['market_price'];
			$row['goods']['goods_price'] = $row['goods_price'];
			$row['goods']['goods_number'] = $row['goods_number'];
			$row['goods']['goods_attr'] = $row['goods_attr'];
			$row['goods']['is_real'] = $row['is_real'];
			$row['goods']['goods_attr_id'] = $row['goods_attr_id'];
			$row['goods']['is_shipping'] = $row['is_shipping'];
			$row['goods']['ru_id'] = $row['ru_id'];
			$row['goods']['warehouse_id'] = $row['warehouse_id'];
			$row['goods']['stages_qishu'] = $row['stages_qishu'];
			$row['goods']['add_time'] = $row['add_time'];
			$row['goods']['goods_sn'] = $row['goods_sn'];
			$row['goods']['product_id'] = $row['product_id'];
			$row['goods']['extension_code'] = $row['extension_code'];
			$row['goods']['parent_id'] = $row['parent_id'];
			$row['goods']['is_gift'] = $row['is_gift'];
			$row['goods']['model_attr'] = $row['model_attr'];
			$row['goods']['area_id'] = $row['area_id'];
			$a = $row['ru_id'];
			$tmpArray[$a]['shop_name'] = $row['shop_name'];
			$tmpArray[$a]['user_id'] = $row['user_id'];
			$tmpArray[$a]['ru_id'] = $row['ru_id'];
			$tmpArray[$a]['goods'][] = $row['goods'];
			$goodslist[$key]['goods'] = $row['goods'];
		}

		foreach ($tmpArray as $key => $value) {
			$shipping = \App\Models\ShippingArea::select('shipping_area.*')->with(array('shipping' => function($query) {
				$query->select('shipping_id', 'shipping_name', 'insure');
			}))->where('ru_id', $value['ru_id'])->get()->toArray();
			$ship = array();

			foreach ($shipping as $k => $val) {
				if ($val['ru_id'] == $value['ru_id']) {
					$val['shipping']['ru_id'] = $val['ru_id'];
					$val['shipping']['configure'] = $val['configure'];
					$ship[] = $val['shipping'];
				}
			}

			$tmpArray[$key]['shop_info'] = $ship;
		}

		$total['saving'] = $total['market_price'] - $total['goods_price'];
		$total['saving_formated'] = price_format($total['market_price'] - $total['goods_price'], false);

		if (0 < $total['market_price']) {
			$total['save_rate'] = $total['market_price'] ? round(($total['market_price'] - $total['goods_price']) * 100 / $total['market_price']) . '%' : 0;
		}

		$total['goods_price_formated'] = price_format($total['goods_price'], false);
		$total['market_price_formated'] = price_format($total['market_price'], false);
		$total['real_goods_count'] = $real_goods_count;
		$total['virtual_goods_count'] = $virtual_goods_count;
		return array('goods_list' => $tmpArray, 'total' => $total, 'product' => $goodslist);
	}

	public function update($uid, $id, $num, $attr = array())
	{
		$cart = \App\Models\Cart::where('user_id', $uid)->where('rec_id', $id)->first();

		if ($cart === null) {
			return false;
		}

		$cart->goods_number = $num;
		return $cart->save();
	}

	public function deleteOne($id, $uid)
	{
		return \App\Models\Cart::where('rec_id', $id)->where('user_id', $uid)->delete();
	}

	public function deleteAll($arr)
	{
		$cartModel = new \App\Models\Cart();

		foreach ($arr as $k => $v) {
			if (count($v) == 3 && $v[0] == 'in') {
				$cartModel = $cartModel->whereIn($v[1], $v[2]);
			}
			else if (count($v) == 2) {
				$cartModel = $cartModel->where($v[0], $v[1]);
			}
		}

		$cartModel->delete();
	}

	public function clearCart($type, $uid)
	{
		return \App\Models\Cart::where('rec_type', $type)->where('user_id', $uid)->delete();
	}

	public function computeDiscountCheck($order_products)
	{
		$now = local_gettime();
		$user_rank = $this->userRankRepository->getUserRankByUid();
		$user_rank = ',' . $user_rank['rank_id'] . ',';
		$favourable_list = \App\Models\FavourableActivity::where('start_time', '<=', $now)->where('end_time', '>=', $now)->whereraw('CONCAT(\',\', user_rank, \',\') LIKE \'%' . $user_rank . '%\'')->wherein('act_type', array(self::FAT_DISCOUNT, self::FAT_PRICE))->get()->toArray();

		if (!$favourable_list) {
			return 0;
		}

		$goods_list = $order_products;

		foreach ($goods_list as $key => $good) {
			foreach ($good['goods'] as $k => $v) {
				$good_property = array();

				if ($v['goods_attr_id']) {
					$good_property = explode(',', $v['goods_attr_id']);
				}

				$goods_list[$key]['price'] = $this->goodsRepository->getFinalPrice($v['goods_id'], $v['goods_number'], true, $good_property);
				$goods_list[$key]['amount'] = $v['goods_number'];
			}
		}

		if (!$goods_list) {
			return 0;
		}

		$discount = 0;
		$favourable_name = array();

		foreach ($favourable_list as $favourable) {
			$total_amount = 0;

			if ($favourable['act_range'] == self::FAR_ALL) {
				foreach ($goods_list as $goods) {
					$total_amount += $goods['goods'][0]['goods_price'] * $goods['goods'][0]['goods_number'];
				}
			}
			else if ($favourable['act_range'] == self::FAR_CATEGORY) {
			}
			else if ($favourable['act_range'] == self::FAR_BRAND) {
				foreach ($goods_list as $goods) {
					$brand_id = $this->goodsRepository->getBrandIdByGoodsId($goods['goods'][0]['goods_id']);

					if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
						$total_amount += $goods['goods'][0]['goods_price'] * $goods['goods'][0]['goods_number'];
					}
				}
			}
			else if ($favourable['act_range'] == self::FAR_GOODS) {
				foreach ($goods_list as $goods) {
					foreach ($goods['goods'] as $v) {
						if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $v['goods_id'] . ',') !== false) {
							$total_amount += $v['goods_price'] * $v['goods_number'];
						}
					}
				}
			}
			else {
				continue;
			}

			if (0 < $total_amount && $favourable['min_amount'] <= $total_amount && ($total_amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0)) {
				if ($favourable['act_type'] == self::FAT_DISCOUNT) {
					$discount += $total_amount * (1 - $favourable['act_type_ext'] / 100);
				}
				else if ($favourable['act_type'] == self::FAT_PRICE) {
					$discount += $favourable['act_type_ext'];
				}
			}
		}

		return $discount;
	}

	public function getGiveIntegral()
	{
		$uid = $this->authService->authorization();
		$allIntegral = \App\Models\Cart::from('cart as c')->select(array('c.*', 'g.give_integral as give_integral'))->leftjoin('goods as g', 'c.goods_id', '=', 'g.goods_id')->where('c.goods_id', '>', 0)->where('c.parent_id', 0)->where('c.rec_type', 0)->where('c.is_gift', 0)->where('c.user_id', $uid)->get()->toArray();
		$sum = 0;

		foreach ($allIntegral as $key => $value) {
			$giveIntegral = empty($value['give_integral']) ? 0 : $value['give_integral'];

			if (-1 < $giveIntegral) {
				$sum += $giveIntegral * $value['goods_number'];
			}
			else {
				$sum += $value['goods_price'] * $value['goods_number'];
			}
		}

		return $sum;
	}

	public function fee_goods($sess_id, $ru_id, $where)
	{
		$prefix = \Illuminate\Support\Facades\Config::get('database.connections.mysql.prefix');
		$sql = 'SELECT count(*) FROM ' . $prefix . 'cart WHERE ' . $sess_id . ' AND `extension_code` != \'package_buy\' AND `is_shipping` = 0 AND ru_id = \'' . $ru_id . '\'' . $where;
		$shipping_count = \Illuminate\Support\Facades\DB::select($sql);
		$shipping_count = get_object_vars($shipping_count[0]);
		return $shipping_count['count(*)'];
	}
}


?>
