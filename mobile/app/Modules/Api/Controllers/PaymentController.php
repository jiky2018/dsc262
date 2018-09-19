<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Modules\Api\Controllers;

class PaymentController extends \App\Api\Controllers\Controller
{
	private $paymentService;
	private $authService;

	public function __construct(\App\Services\PaymentService $paymentService, \App\Services\AuthService $authService)
	{
		$this->paymentService = $paymentService;
		$this->authService = $authService;
	}

	public function pay(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'open_id' => 'required|string', 'code' => 'string'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$args = $request->all();
		$args['uid'] = $uid;
		$res = $this->paymentService->payment($args);
		return $this->apiReturn($res);
	}

	public function notify(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'code' => 'string'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$args = $request->all();
		$args['uid'] = $uid;
		$res = $this->paymentService->notify($args);

		if (0 < $res['code']) {
			return $this->apiReturn($res['msg'], 1);
		}

		return $this->apiReturn($res);
	}
}

?>
