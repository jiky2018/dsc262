<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers\Wx;

class GoodsController extends \App\Api\Controllers\Controller
{
	private $goodsService;
	private $authService;

	public function __construct(\App\Services\GoodsService $goodsService, \App\Services\AuthService $authService)
	{
		$this->goodsService = $goodsService;
		$this->authService = $authService;
	}

	public function goodsList(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('page' => 'required|int', 'warehouse_id' => 'required|integer', 'area_id' => 'required|integer'));
		$list = $this->goodsService->getGoodsList($request->get('id'), $request->get('keyword'), $request->get('page'), $request->get('per_page'), $request->get('sort_key'), $request->get('sort_value'), $request->get('warehouse_id'), $request->get('area_id'), $request->get('proprietary'), $request->get('price_min'), $request->get('price_max'), $request->get('brand'), $request->get('province_id'), $request->get('city_id'), $request->get('county_id'), $request->get('fil_key'));
		return $this->apiReturn($list);
	}

	public function goodsFilterCondition(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('cat_id' => 'required|integer'));
		$list = $this->goodsService->getGoodsFilterCondition($request->get('cat_id'));
		return $this->apiReturn($list);
	}

	public function goodsDetail(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			$uid = 0;
		}

		$list = $this->goodsService->goodsDetail($request->get('id'), $uid);
		return $this->apiReturn($list, $list['error']);
	}

	public function property(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'num' => 'required|integer', 'warehouse_id' => 'required|integer', 'area_id' => 'required|integer'));
		$price = $this->goodsService->goodsPropertiesPrice($request->get('id'), $request->get('attr_id'), $request->get('num'), $request->get('warehouse_id'), $request->get('area_id'));
		return $this->apiReturn($price);
	}

	public function Share(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'path' => 'required|string'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$share = $this->goodsService->goodsShare($request->get('id'), $uid, $request->get('path'));
		return $this->apiReturn($share);
	}

	public function Coupons(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('cou_id' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$result = $this->goodsService->getCoupon($request->get('cou_id'), $uid);
		return $this->apiReturn($result);
	}

	public function history(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array());
		$result = $this->goodsService->history($request->get('list'), $request->get('page'), $request->get('size'));
		return $this->apiReturn($result);
	}

	public function goodssave(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array());
		$result = $this->goodsService->goodsSave($request->get('list'), $request->get('goodsId'));
		return $this->apiReturn($result);
	}
}

?>
