<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers\Wx;

class BargainController extends \App\Api\Controllers\Controller
{
	/** @var IndexService  */
	private $bargainService;
	private $authService;

	public function __construct(\App\Services\BargainService $bargainService, \App\Services\AuthService $authService)
	{
		$this->bargainService = $bargainService;
		$this->authService = $authService;
	}

	public function index()
	{
		$banner = $this->bargainService->getAdsense('1020');
		$data['banner'] = $banner;
		return $this->apiReturn($data);
	}

	public function bargainList(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('page' => 'required|int'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			$uid = 0;
		}

		$list = $this->bargainService->bargainGoodsList($request->get('page'), $request->get('per_page'), $uid);
		return $this->apiReturn($list);
	}

	public function goodsDetail(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'bs_id' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			$uid = 0;
		}

		$list = $this->bargainService->goodsDetail($request->get('id'), $uid, $request->get('bs_id'));
		return $this->apiReturn($list, $list['error']);
	}

	public function property(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'num' => 'required|integer', 'warehouse_id' => 'required|integer', 'area_id' => 'required|integer'));
		$price = $this->bargainService->goodsPropertiesPrice($request->get('id'), $request->get('attr_id'), $request->get('num'), $request->get('warehouse_id'), $request->get('area_id'));
		return $this->apiReturn($price);
	}

	public function addBargain(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$result = $this->bargainService->addBargain($request->get('id'), $request->get('attr_id'), $uid);
		return $this->apiReturn($result);
	}

	public function goBargain(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'bs_id' => 'required|integer', 'form_id' => 'required|string'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$result = $this->bargainService->goBargain($request->get('id'), $request->get('bs_id'), $uid, $request->get('form_id'));
		return $this->apiReturn($result);
	}

	public function Bargainbuy(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'bs_id' => 'required|integer', 'num' => 'required|integer', 'goods_id' => 'required|integer'));
		$res = $this->authService->authorization();
		if (isset($res['error']) && 0 < $res['error']) {
			return $this->apiReturn($res, 1);
		}

		$args = array_merge($request->all(), array('uid' => $res));
		$result = $this->bargainService->addGoodsToCart($args);
		return $this->apiReturn($result);
	}

	public function myBargain(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('page' => 'required|int'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$list = $this->bargainService->myBargain($uid, $request->get('page'), $request->get('per_page'));
		return $this->apiReturn($list);
	}
}

?>
