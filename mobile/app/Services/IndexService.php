<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class IndexService
{
	private $goodsRepository;
	private $shopRepository;
	private $articleRepository;
	private $root_url;

	public function __construct(\App\Repositories\Goods\GoodsRepository $goodsRepository, \App\Repositories\Article\ArticleRepository $articleRepository, \App\Repositories\Shop\ShopRepository $shopRepository, \Illuminate\Http\Request $request)
	{
		$this->goodsRepository = $goodsRepository;
		$this->articleRepository = $articleRepository;
		$this->shopRepository = $shopRepository;
		$this->root_url = dirname(dirname($request->root())) . '/';
	}

	public function bestGoodsList($type = 'best')
	{
		$arr = array('goods_id', 'goods_name', 'shop_price', 'goods_thumb', 'promote_price', 'promote_start_date', 'promote_end_date', 'goods_link', 'goods_number', 'market_price');
		$goodsList = $this->goodsRepository->findByType($type);
		$data = array_map(function($v) use($arr) {
			foreach ($v as $ck => $cv) {
				if (!in_array($ck, $arr)) {
					unset($v[$ck]);
				}
			}

			if ($v['promote_price'] && $v['promote_start_date'] < gmtime() && gmtime() < $v['promote_end_date']) {
				$v['shop_price'] = $v['promote_price'] < $v['shop_price'] ? $v['promote_price'] : $v['shop_price'];
			}

			$v['goods_thumb'] = get_image_path($v['goods_thumb']);
			$v['goods_stock'] = $v['goods_number'];
			$v['market_price_formated'] = price_format($v['market_price'], false);
			$v['shop_price_formated'] = price_format($v['shop_price'], false);
			unset($v['goods_number']);
			return $v;
		}, $goodsList);
		return $data;
	}

	public function getBanners()
	{
		$res = $this->shopRepository->getPositions('weapp', 10);
		$ads = array();

		foreach ($res as $row) {
			if (!empty($row['position_id'])) {
				$src = strpos($row['ad_code'], 'http://') === false && strpos($row['ad_code'], 'https://') === false ? 'data/afficheimg/' . $row['ad_code'] : $row['ad_code'];
				$ads[] = array('pic' => get_image_path($src), 'banner_id' => $row['ad_id'], 'link' => $row['ad_link']);
			}
		}

		return $ads;
	}

	public function getAdsense()
	{
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$number = $shopconfig->getShopConfigByCode('wx_index_show_number');

		if (empty($number)) {
			$number = 10;
		}

		$adsense = $this->shopRepository->getPositions('', $number);
		$ads = array();

		foreach ($adsense as $row) {
			if (!empty($row['position_id'])) {
				$src = strpos($row['ad_code'], 'http://') === false && strpos($row['ad_code'], 'https://') === false ? 'data/afficheimg/' . $row['ad_code'] : $row['ad_code'];
				$ads[] = array('pic' => get_image_path($src), 'adsense_id' => $row['ad_id'], 'link' => $row['ad_link']);
			}
		}

		return $ads;
	}

	public function getAd()
	{
		$ads['ad'] = $this->shopRepository->getAd();
		$ads['store'] = $this->shopRepository->getStore();
		return $ads;
	}
}


?>
