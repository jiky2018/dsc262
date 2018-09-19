<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class StoreService
{
	private $storeRepository;
	private $collectStoreRepository;

	public function __construct(\App\Repositories\Store\StoreRepository $storeRepository, \App\Repositories\Store\CollectStoreRepository $collectStoreRepository)
	{
		$this->storeRepository = $storeRepository;
		$this->collectStoreRepository = $collectStoreRepository;
	}

	public function storeList()
	{
		$list = $this->storeRepository->all();
		return $list;
	}

	public function detail($id, $page, $per_page = 10, $cate_key, $sort, $order = 'ASC', $cat_id = 0, $uid)
	{
		$rootPath = app('request')->root();
		$rootPath = dirname(dirname($rootPath)) . '/';
		$detail = $this->storeRepository->detail($id);
		$detail[0]['sellershopinfo']['logo_thumb'] = get_image_path(str_replace('../', '', $detail[0]['sellershopinfo']['logo_thumb']));
		$goods = $this->storeRepository->goods($id, $page, $per_page, $cate_key, $sort, $order, $cat_id);
		$category = $this->storeRepository->store_category($id);

		foreach ($goods as $key => $value) {
			$goods[$key]['goods_name'] = $value['goods_name'];
			$goods[$key]['goods_thumb'] = get_image_path($value['goods_thumb']);
			$goods[$key]['shop_price'] = price_format($value['shop_price'], true);
			$goods[$key]['yuan_shop'] = $value['shop_price'];
			$goods[$key]['cat_id'] = $value['cat_id'];
			$goods[$key]['market_price'] = price_format($value['market_price'], true);
			$goods[$key]['yuan_market'] = $value['market_price'];
			$goods[$key]['goods_number'] = $value['goods_number'];
		}

		$collnum = $this->storeRepository->collnum($id);
		$collect = $this->storeRepository->collect($id, $uid);
		$list['detail'] = $detail[0];
		$list['goods'] = $goods;
		$list['category'] = $category;
		$list['collnum'] = $collnum;
		$list['collect'] = $collect;
		$list['root_path'] = $rootPath;
		return $list;
	}

	public function attention($id, $uid)
	{
		$collectStore = $this->collectStoreRepository->findOne($id, $uid);

		if (empty($collectStore)) {
			$result = $this->collectStoreRepository->addCollectStore($id, $uid);
			$result = array('collect' => 'true', 'collnum' => $this->storeRepository->collnum($id));
		}
		else {
			$result = $this->collectStoreRepository->deleteCollectStore($id, $uid);
			$result = array('collect' => '0', 'collnum' => $this->storeRepository->collnum($id));
		}

		return $result;
	}
}


?>
