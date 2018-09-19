<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers\Wx;

class StoreController extends \App\Api\Controllers\Controller
{
	private $storeService;
	private $authService;

	public function __construct(\App\Services\StoreService $storeService, \App\Services\AuthService $authService)
	{
		$this->storeService = $storeService;
		$this->authService = $authService;
	}

	public function index(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array());
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		return $this->storeService->storeList($uid);
	}

	public function detail(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|int', 'page' => 'required|int', 'per_page' => 'required|int', 'cate_key' => 'required|string', 'sort' => 'required|string', 'order' => 'required|string', 'cat_id' => 'required|int'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		return $this->storeService->detail($request->get('id'), $request->get('page'), $request->get('per_page'), $request->get('cate_key'), $request->get('sort'), $request->get('order'), $request->get('cat_id'), $uid);
	}

	public function attention(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|int'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		return $this->storeService->attention($request->get('id'), $uid);
	}
}

?>
