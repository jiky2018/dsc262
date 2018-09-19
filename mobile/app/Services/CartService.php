<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class CartService
{
	private $cartRepository;
	private $goodsRepository;
	private $authService;
	private $goodsAttrRepository;

	public function __construct(\App\Repositories\Cart\CartRepository $cartRepository, \App\Repositories\Goods\GoodsRepository $goodsRepository, AuthService $authService, \App\Repositories\Goods\GoodsAttrRepository $goodsAttrRepository)
	{
		$this->cartRepository = $cartRepository;
		$this->goodsRepository = $goodsRepository;
		$this->authService = $authService;
		$this->goodsAttrRepository = $goodsAttrRepository;
	}

	public function getCart()
	{
		$cart = $this->getCartGoods();
		$result = array();

		foreach ($cart['goods_list'] as $v) {
			foreach ($v['goods'] as $key => $value) {
				if (isset($value['goods_id'])) {
					$result['cart_list'][$v['ru_id']][] = array('rec_id' => $value['rec_id'], 'user_id' => $v['user_id'], 'ru_id' => $value['ru_id'], 'shop_name' => $v['shop_name'], 'goods_id' => $value['goods_id'], 'goods_name' => $value['goods_name'], 'market_price' => $value['market_price'], 'market_price_formated' => price_format($value['market_price'], false), 'goods_price' => $value['goods_price'], 'goods_price_formated' => price_format($value['goods_price'], false), 'goods_number' => $value['goods_number'], 'goods_attr' => $value['goods_attr'], 'goods_attr_id' => $value['goods_attr_id'], 'goods_thumb' => get_image_path($value['goods_thumb']));
				}
			}
		}

		$result['total'] = array_map('strip_tags', $cart['total']);
		$result['best_goods'] = $this->getBestGoods();
		return $result;
	}

	private function getCartGoods()
	{
		$userId = $this->authService->authorization();
		$list = $this->cartRepository->getGoodsInCartByUser($userId);
		return $list;
	}

	private function getBestGoods()
	{
		$list = $this->goodsRepository->findByType('best');
		$bestGoods = array_map(function($v) {
			return array('goods_id' => $v['goods_id'], 'goods_name' => $v['goods_name'], 'market_price' => $v['market_price'], 'market_price_formated' => price_format($v['market_price'], false), 'shop_price' => $v['shop_price'], 'shop_price_formated' => price_format($v['shop_price'], false), 'goods_thumb' => get_image_path($v['goods_thumb']));
		}, $list);
		return $bestGoods;
	}

	public function addGoodsToCart($params)
	{
		$result = array('code' => 0, 'goods_number' => 0, 'total_number' => 0);
		$goods = $this->goodsRepository->find($params['id']);

		if ($goods['is_on_sale'] != 1) {
			return '商品已下架';
		}

		$goodsAttr = empty($params['attr_id']) ? '' : json_decode($params['attr_id'], 1);
		$goodsAttrId = implode(',', $goodsAttr);
		$product = $this->goodsRepository->getProductByGoods($params['id'], implode('|', $goodsAttr));

		if (empty($product)) {
			$product['id'] = 0;
		}

		$attrName = $this->goodsAttrRepository->getAttrNameById($goodsAttr);
		$attrNameStr = '';

		foreach ($attrName as $v) {
			$attrNameStr .= $v['attr_name'] . ':' . $v['attr_value'] . " \n";
		}

		$goodsPrice = $this->goodsRepository->getFinalPrice($params['id'], $params['num'], 1, $goodsAttr);
		$cart = $this->cartRepository->getCartByGoods($params['uid'], $params['id'], $goodsAttrId);
		$cart_num = isset($cart['goods_number']) ? $cart['goods_number'] : 0;

		if ($goods['goods_number'] < $params['num'] + $cart_num) {
			return '库存不足';
		}

		if (!empty($cart)) {
			$goodsNumber = $params['num'] + $cart['goods_number'];
			$res = $this->cartRepository->update($params['uid'], $cart['rec_id'], $goodsNumber);

			if ($res) {
				$number = $this->cartRepository->goodsNumInCartByUser($params['uid']);
				$result['goods_number'] = $goodsNumber;
				$result['total_number'] = $number;
			}
		}
		else {
			$arguments = array('goods_id' => $params['id'], 'user_id' => $params['uid'], 'goods_sn' => $goods['goods_sn'], 'product_id' => empty($product['id']) ? '' : $product['id'], 'group_id' => '', 'goods_name' => $goods['goods_name'], 'market_price' => $goods['market_price'], 'goods_price' => $goodsPrice, 'goods_number' => $params['num'], 'goods_attr' => $attrNameStr, 'is_real' => $goods['is_real'], 'extension_code' => empty($params['extension_code']) ? '' : $params['extension_code'], 'parent_id' => 0, 'rec_type' => 0, 'is_gift' => 0, 'is_shipping' => $goods['is_shipping'], 'can_handsel' => '', 'model_attr' => $goods['model_attr'], 'goods_attr_id' => $goodsAttrId, 'ru_id' => $goods['user_id'], 'shopping_fee' => '', 'warehouse_id' => '', 'area_id' => '', 'add_time' => gmtime(), 'stages_qishu' => '', 'store_id' => '', 'freight' => '', 'tid' => '', 'shipping_fee' => '', 'store_mobile' => '', 'take_time' => '', 'is_checked' => '');
			$goodsNumber = $this->cartRepository->addGoodsToCart($arguments);
			$number = $this->cartRepository->goodsNumInCartByUser($params['uid']);
			$result['goods_number'] = $goodsNumber;
			$result['total_number'] = $number;
		}

		return $result;
	}

	public function updateCartGoods($args)
	{
		$cart = $this->cartRepository->find($args['id']);
		$goods = $this->goodsRepository->find($cart['goods_id']);

		if ($goods['goods_number'] < $args['amount']) {
			return array('code' => 1, 'msg' => '库存不足');
		}

		$res = $this->cartRepository->update($args['uid'], $args['id'], $args['amount']);

		if ($res) {
			return array('code' => 0, 'msg' => '添加成功');
		}

		return array('code' => 1, 'msg' => '添加失败');
	}

	public function deleteCartGoods($args)
	{
		$res = $this->cartRepository->deleteOne($args['id'], $args['uid']);
		$result = array();

		switch ($res) {
		case 0:
			$result['code'] = 1;
			$result['msg'] = '购物车中没有该商品';
			break;

		case 1:
			$result['code'] = 0;
			$result['msg'] = '删除一个商品';
			break;

		default:
			$result['code'] = 1;
			$result['msg'] = '删除失败';
			break;
		}

		return $result;
	}
}


?>
