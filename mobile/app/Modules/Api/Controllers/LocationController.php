<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Modules\Api\Controllers;

class LocationController extends \App\Modules\Api\Foundation\Controller
{
	/**
     * @var LocationRepository
     */
	protected $location;
	/**
     * @var LocationTransformer
     */
	protected $locationTransformer;

	public function __construct(\App\Services\LocationService $locationService, \App\Services\AuthService $authService)
	{
		$this->locationService = $locationService;
		$this->authService = $authService;
	}

	public function Index()
	{
		$region = $this->locationService->index();
		return $region;
	}

	public function Info(Request $request)
	{
		$this->validate($request, array('region_id' => 'required|int', 'region_type' => 'required|int'));
		$region = $this->locationService->info($request->get('region_id'), $request->get('region_type'));
		return $region;
	}

	public function getcity()
	{
		$region = $this->locationService->getcity();
		return $region;
	}

	public function setcity()
	{
		$region = $this->locationService->setcity();
		return $region;
	}

	public function specific(Request $request)
	{
		$this->validate($request, array('address' => 'required|string'));
		$region = $this->locationService->specific($request->get('address'));
		return $region;
	}
}

?>
