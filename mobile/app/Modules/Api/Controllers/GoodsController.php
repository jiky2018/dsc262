<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Modules\Api\Controllers;

class GoodsController extends \App\Modules\Api\Foundation\Controller
{
	private $goodsService;
	private $authService;

	public function __construct(\App\Services\GoodsService $goodsService, \App\Services\AuthService $authService)
	{
		$this->goodsService = $goodsService;
		$this->authService = $authService;
	}

	public function goodsList(Request $request)
	{
		$this->validate($request, array('page' => 'required|int', 'warehouse_id' => 'required|integer', 'area_id' => 'required|integer'));
		$list = $this->goodsService->getGoodsList($request->get('id'), $request->get('keyword'), $request->get('page'), $request->get('per_page'), $request->get('sort_key'), $request->get('sort_value'), $request->get('warehouse_id'), $request->get('area_id'));
		return $this->apiReturn($list);
	}

	public function goodsDetail(Request $request)
	{
		$this->validate($request, array('id' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			$uid = 0;
		}

		$list = $this->goodsService->goodsDetail($request->get('id'), $uid);
		return $this->apiReturn($list, $list['error']);
	}

	public function property(Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'num' => 'required|integer', 'warehouse_id' => 'required|integer', 'area_id' => 'required|integer'));
		$price = $this->goodsService->goodsPropertiesPrice($request->get('id'), $request->get('attr_id'), $request->get('num'), $request->get('warehouse_id'), $request->get('area_id'));
		return $this->apiReturn($price);
	}
}

?>
