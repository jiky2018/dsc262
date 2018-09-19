<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers\Wx;

class FlowController extends \App\Api\Controllers\Controller
{
	private $flowService;
	private $authService;

	public function __construct(\App\Services\FlowService $flowService, \App\Services\AuthService $authService)
	{
		$this->flowService = $flowService;
		$this->authService = $authService;
	}

	public function index(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('flow_type' => 'required|integer', 'bs_id' => 'required|integer', 't_id' => 'required|integer', 'team_id' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$flowInfo = $this->flowService->flowInfo($uid, $request->get('flow_type'), $request->get('bs_id'), $request->get('t_id'), $request->get('team_id'));
		return $this->apiReturn($flowInfo);
	}

	public function changecou(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('uc_id' => 'required|integer', 'flow_type' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$res = $this->flowService->changeCou($request->get('uc_id'), $uid, $request->get('flow_type'));
		return $this->apiReturn($res);
	}

	public function down(\Illuminate\Http\Request $request)
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

	public function shipping(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'ru_id' => 'required|integer', 'address' => 'required|integer', 'flow_type' => 'required|integer'));
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

	public function detail(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer'));
		$args['main_order_id'] = $request->get('id');
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$args['uid'] = $uid;
		$order = $this->flowService->orderDetail($args);
		return $this->apiReturn($order);
	}
}

?>
