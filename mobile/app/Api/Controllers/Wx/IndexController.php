<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers\Wx;

class IndexController extends \App\Api\Controllers\Controller
{
	/** @var IndexService  */
	private $indexService;

	public function __construct(\App\Services\IndexService $indexService)
	{
		$this->indexService = $indexService;
	}

	/*public function index()
	{
		$banners = $this->indexService->getBanners();
		$data['banner'] = $banners;
		$adsense = $this->indexService->getAdsense();
		$ad = $this->indexService->getAd();
		$data['ad'] = $ad;
		$data['adsense'] = $adsense;
		$goodsList = $this->indexService->bestGoodsList('best');
		$data['goods_list'] = $goodsList;
		$goodsList_new = $this->indexService->bestGoodsList('new');
		$data['goods_list_new'] = $goodsList_new;
		return $this->apiReturn($data);
	}*/
	
	public function index(\Illuminate\Http\Request $request)
	{
		$page = $request->get('page') ?  $request->get('page') : 1;
		
		$banners = $this->indexService->getBanners2(1015,3);
		$data['banner'] = $banners;
		$banners = $this->indexService->getBanners2(1016,1);
		$data['bestbanner'] = $banners;
		$banners = $this->indexService->getBanners2(1017,1);
		$data['newbanner'] = $banners;
		$adsense = $this->indexService->getAdsense();
		$data['adsense'] = $adsense;
		$goodsList = $this->indexService->bestGoodsList('best');
		$data['goods_list'] = $goodsList;
		$goodsList_new = $this->indexService->bestGoodsList('new');
		$data['goods_list_new'] = $goodsList_new;
		$goodsList_hot = $this->indexService->bestGoodsList('hot',10,$page);
		$data['goods_list_hot'] = $goodsList_hot;
		return $this->apiReturn($data);
	}
}

?>
