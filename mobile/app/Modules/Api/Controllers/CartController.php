<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Modules\Api\Controllers;

class CartController extends \App\Modules\Api\Foundation\Controller
{
	private $cartService;
	private $authService;

	public function __construct(\App\Services\CartService $cartService, \App\Services\AuthService $authService)
	{
		$this->cartService = $cartService;
		$this->authService = $authService;
	}

	public function cart(Request $request)
	{
		$this->validate($request, array());
		$cart = $this->cartService->getCart();
		return $this->apiReturn($cart);
	}

	public function addGoodsToCart(Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'num' => 'required|integer'));
		$res = $this->authService->authorization();
		if (isset($res['error']) && 0 < $res['error']) {
			return $this->apiReturn($res, 1);
		}

		$args = array_merge($request->all(), array('uid' => $res));
		$result = $this->cartService->addGoodsToCart($args);
		return $this->apiReturn($result);
	}

	public function updateCartGoods(Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'amount' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$args = $request->all();
		$args['uid'] = $uid;
		return $this->cartService->updateCartGoods($args);
	}

	public function deleteCartGoods(Request $request)
	{
		$this->validate($request, array('id' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$args = $request->all();
		$args['uid'] = $uid;
		$res = $this->cartService->deleteCartGoods($args);
		return $res;
	}
}

?>
