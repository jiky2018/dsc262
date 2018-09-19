<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Modules\Api\Controllers;

class FlowController extends \App\Modules\Api\Foundation\Controller
{
	private $flowService;
	private $authService;

	public function __construct(\App\Services\FlowService $flowService, \App\Services\AuthService $authService)
	{
		$this->flowService = $flowService;
		$this->authService = $authService;
	}

	public function index(Request $request)
	{
		$this->validate($request, array());
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$flowInfo = $this->flowService->flowInfo($uid);
		return $this->apiReturn($flowInfo);
	}

	public function down(Request $request)
	{
		$this->validate($request, array('consignee' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$args = $request->all();
		$args['uid'] = $uid;
		app('config')->set('uid', $uid);
		$res = $this->flowService->submitOrder($args);

		if ($res['error'] == 1) {
			return $this->apiReturn($res['msg'], 1);
		}

		return $this->apiReturn($res);
	}

	public function shipping(Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'ru_id' => 'required|integer', 'address' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$args = $request->all();
		$args['uid'] = $uid;
		$res = $this->flowService->shippingFee($args);

		if ($res['error'] == 0) {
			unset($res['error']);
			unset($res['message']);
			return $this->apiReturn($res);
		}
		else {
			return $this->apiReturn($res['message'], 1);
		}
	}
}

?>
