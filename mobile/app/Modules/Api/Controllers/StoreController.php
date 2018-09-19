<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Modules\Api\Controllers;

class StoreController extends \App\Modules\Api\Foundation\Controller
{
	private $storeService;
	private $authService;

	public function __construct(\App\Services\StoreService $storeService, AuthService $authService)
	{
		$this->storeService = $storeService;
		$this->authService = $authService;
	}

	public function index()
	{
		return $this->storeService->storeList();
	}

	public function detail(Request $request)
	{
		$this->validate($request, array('id' => 'required|int', 'page' => 'required|int', 'per_page' => 'required|int', 'cate_key' => 'required|string', 'sort' => 'required|string', 'order' => 'required|string', 'cat_id' => 'required|int'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		return $this->storeService->detail($request->get('id'), $request->get('page'), $request->get('per_page'), $request->get('cate_key'), $request->get('sort'), $request->get('order'), $request->get('cat_id'), $uid);
	}

	public function attention(Request $request)
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
