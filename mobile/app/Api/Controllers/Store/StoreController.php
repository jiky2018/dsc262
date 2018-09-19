<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers\Store;

class StoreController extends \App\Api\Controllers\Controller
{
	protected $store;

	public function __construct(\App\Services\StoreService $storeService)
	{
		$this->store = $storeService;
	}

	public function index()
	{
		return $this->store->all();
	}

	public function detail(Request $request)
	{
		$this->validate($request, array('id' => 'required|int'));
		return $this->store->detail($request->get('id'));
	}
}

?>
