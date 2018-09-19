<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Api\Controllers\Wx;

class RegionController extends \App\Api\Controllers\Controller
{
	private $authService;
	private $regionService;

	public function __construct(\App\Services\AuthService $authService, \App\Services\RegionService $regionService)
	{
		$this->authService = $authService;
		$this->regionService = $regionService;
	}

	public function regionList(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer'));
		$args = $request->all();
		$list = $this->regionService->regionList($args);
		return $this->apiReturn($list);
	}
}

?>
