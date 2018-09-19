<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers\Wx;

class TeamController extends \App\Api\Controllers\Controller
{
	/** @var IndexService  */
	private $teamService;
	private $authService;
	private $goodsService;

	public function __construct(\App\Services\TeamService $teamService, \App\Services\AuthService $authService, \App\Services\GoodsService $goodsService)
	{
		$this->teamService = $teamService;
		$this->authService = $authService;
		$this->goodsService = $goodsService;
	}

	public function index()
	{
		$banner = $this->teamService->getAdsense('1008');
		$data['banner'] = $banner;
		$banner_bottom = $this->teamService->getAdsense('1009');
		$data['banner_bottom'] = $banner_bottom;
		$ad_hot_left = $this->teamService->getAdsense('1010');
		$data['ad_hot_left'] = $ad_hot_left;
		$ad_hot_right = $this->teamService->getAdsense('1011');
		$data['ad_hot_right'] = $ad_hot_right;
		$ad_hot_bottom = $this->teamService->getAdsense('1012');
		$data['ad_hot_bottom'] = $ad_hot_bottom;
		$ad_best_left = $this->teamService->getAdsense('1013');
		$data['ad_best_left'] = $ad_best_left;
		$ad_best_right = $this->teamService->getAdsense('1014');
		$data['ad_best_right'] = $ad_best_right;
		$team_categories = $this->teamService->teamCategories();
		$data['team_categories'] = $team_categories;
		return $this->apiReturn($data);
	}

	public function teamList(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('page' => 'required|integer', 'size' => 'required|integer', 'tc_id' => 'required|integer'));
		$list = $this->teamService->teamGoodsList($request->get('page'), $request->get('size'), $request->get('tc_id'));
		return $this->apiReturn($list);
	}

	public function virtualOrder(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array());
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			$uid = 0;
		}

		$data = $this->teamService->virtualOrder($uid);
		return $this->apiReturn($data);
	}

	public function categoriesIndex(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('tc_id' => 'required|integer'));
		$banner = $this->teamService->categoriesAdsense($request->get('tc_id'), 'banner');
		$data['banner'] = $banner;
		$ads_left = $this->teamService->categoriesAdsense($request->get('tc_id'), 'left');
		$data['ads_left'] = $ads_left;
		$ads_right = $this->teamService->categoriesAdsense($request->get('tc_id'), 'right');
		$data['ads_right'] = $ads_right;
		$team_categories = $this->teamService->teamCategories();
		$data['team_categories'] = $team_categories;
		$team_categories_child = $this->teamService->teamCategoriesChild($request->get('tc_id'));
		$data['team_categories_child'] = $team_categories_child['list'];
		$data['title'] = $team_categories_child['title'];
		return $this->apiReturn($data);
	}

	public function categoryList(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('page' => 'required|integer', 'size' => 'required|integer', 'tc_id' => 'required|integer', 'keyword' => 'required|string', 'sort_key' => 'required|integer', 'sort_value' => 'required|string'));
		$list = $this->teamService->categoryGoodsList($request->get('tc_id'), $request->get('page'), $request->get('size'), $request->get('keyword'), $request->get('sort_key'), $request->get('sort_value'));
		return $this->apiReturn($list);
	}

	public function teamRanking(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('page' => 'required|integer', 'size' => 'required|integer', 'type' => 'required|integer'));
		$list = $this->teamService->teamRankingList($request->get('page'), $request->get('size'), $request->get('type'));
		return $this->apiReturn($list);
	}

	public function goodsDetail(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('goods_id' => 'required|integer', 'team_id' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			$uid = 0;
		}

		$list = $this->teamService->goodsDetail($request->get('goods_id'), $uid, $request->get('team_id'));
		return $this->apiReturn($list, $list['error']);
	}

	public function teamProperty(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('goods_id' => 'required|integer', 'num' => 'required|integer', 'warehouse_id' => 'required|integer', 'area_id' => 'required|integer'));
		$price = $this->teamService->goodsPropertiesPrice($request->get('goods_id'), $request->get('attr_id'), $request->get('num'), $request->get('warehouse_id'), $request->get('area_id'));
		return $this->apiReturn($price);
	}

	public function teamBuy(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('goods_id' => 'required|integer', 't_id' => 'required|integer', 'num' => 'required|integer', 'team_id' => 'required|integer'));
		$res = $this->authService->authorization();
		if (isset($res['error']) && 0 < $res['error']) {
			return $this->apiReturn($res, 1);
		}

		$args = array_merge($request->all(), array('uid' => $res));
		$result = $this->teamService->addGoodsToCart($args);
		return $this->apiReturn($result);
	}

	public function teamWait(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('team_id' => 'required|int', 'user_id' => 'required|int'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$list = $this->teamService->teamWait($uid, $request->get('team_id'), $request->get('user_id'));
		return $this->apiReturn($list);
	}

	public function teamIsBest(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('page' => 'required|integer', 'size' => 'required|integer', 'type' => 'required|integer'));
		$list = $this->teamService->teamRankingList($request->get('page'), $request->get('size'), $request->get('type'));
		return $this->apiReturn($list);
	}

	public function teamUser(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('team_id' => 'required|int', 'page' => 'required|integer', 'size' => 'required|integer'));
		$list = $this->teamService->teamUser($request->get('team_id'), $request->get('page'), $request->get('size'));
		return $this->apiReturn($list);
	}

	public function teamUserOrder(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('type' => 'required|int', 'page' => 'required|integer', 'size' => 'required|integer'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			$uid = 0;
		}

		$list = $this->teamService->teamUserOrder($uid, $request->get('type'), $request->get('page'), $request->get('size'));
		return $this->apiReturn($list);
	}
}

?>
