<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class GoodsService
{
	private $goodsRepository;
	private $goodsAttrRepository;
	private $collectGoodsRepository;
	private $CouponsRepository;
	private $shopService;
	private $cartRepository;
	private $StoreRepository;
	private $userRepository;
	private $WxappConfigRepository;

	public function __construct(\App\Repositories\Goods\GoodsRepository $goodsRepository, \App\Repositories\Goods\GoodsAttrRepository $goodsAttrRepository, \App\Repositories\Goods\CollectGoodsRepository $collectGoodsRepository, \App\Repositories\Coupons\CouponsRepository $couponsRepository, ShopService $shopService, \App\Repositories\Cart\CartRepository $cartRepository, \App\Repositories\User\UserRepository $userRepository, \App\Repositories\Store\StoreRepository $StoreRepository, \App\Repositories\Wechat\WxappConfigRepository $WxappConfigRepository)
	{
		$this->goodsRepository = $goodsRepository;
		$this->goodsAttrRepository = $goodsAttrRepository;
		$this->collectGoodsRepository = $collectGoodsRepository;
		$this->couponsRepository = $couponsRepository;
		$this->shopService = $shopService;
		$this->cartRepository = $cartRepository;
		$this->StoreRepository = $StoreRepository;
		$this->userRepository = $userRepository;
		$this->WxappConfigRepository = $WxappConfigRepository;
	}

	public function getGoodsList($categoryId = 0, $keywords = '', $page = 1, $size = 10, $sortKey = '', $sortVal = '', $warehouse_id = 0, $area_id = 0, $proprietary = 2, $price_min = 0, $price_max = 0, $brand = '', $province_id = 0, $city_id = 0, $county_id = 0, $fil_key)
	{
		$page = empty($page) ? 1 : $page;
		$cat = '';
		$field = array('goods_id', 'goods_name', 'shop_price', 'goods_thumb', 'goods_number', 'market_price', 'sales_volume');

		if ($categoryId) {
			$cat = $this->goodsRepository->allcat($categoryId);
		}

		if ($cat) {
			foreach ($cat as $k => $val) {
				$res[$k] = isset($val['cat_id']) ? $val['cat_id'] : $val;
			}

			array_unshift($res, $categoryId);
			$categoryId = $res;
		}

		$list = $this->goodsRepository->findBy('category', $categoryId, $page, $size, $field, $keywords, $sortKey, $sortVal, $proprietary, $price_min, $price_max, $brand, $province_id, $city_id, $county_id, $fil_key);

		foreach ($list as $k => $v) {
			$list[$k]['goods_thumb'] = get_image_path($v['goods_thumb']);
			$list[$k]['brand_name'] = $this->goodsRepository->getBrandNameByGoodsId($v['goods_id']);
			$list[$k]['market_price_formated'] = price_format($v['market_price'], false);
			$list[$k]['shop_price'] = $this->goodsRepository->getGoodsOneAttrPrice($v['goods_id']);
			$list[$k]['shop_price_formated'] = price_format($v['shop_price'], false);
		}

		return $list;
	}

	public function getGoodsFilter($id)
	{
		return NULL;
	}

	public function getGoodsFilterCondition($cat_id = 0)
	{
		$list = $this->goodsRepository->FilterCondition($cat_id);
		return $list;
	}

	public function goodsDetail($id, $uid)
	{
		$result = array('error' => 0, 'goods_img' => '', 'goods_info' => '', 'goods_comment' => '', 'goods_properties' => '');
		$rootPath = app('request')->root();
		$rootPath = dirname(dirname($rootPath)) . '/';
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$timeFormat = $shopconfig->getShopConfigByCode('time_format');
		$collect = $this->collectGoodsRepository->findOne($id, $uid);
		$goodsComment = $this->goodsRepository->goodsComment($id);

		foreach ($goodsComment as $k => $v) {
			$goodsComment[$k]['add_time'] = local_date('Y-m-d', $v['add_time']);
			$goodsComment[$k]['user_name'] = $this->goodsRepository->getGoodsCommentUser($v['user_id']);
		}

		$result['goods_comment'] = $goodsComment;
		$result['total_comment_number'] = count($result['goods_comment']);
		$goodsInfo = $this->goodsRepository->goodsInfo($id);

		if ($goodsInfo['is_on_sale'] == 0) {
			return array('error' => 1, 'msg' => '商品已下架');
		}

		$goodsInfo['goods_thumb'] = get_image_path($goodsInfo['goods_thumb']);
		$goodsInfo['goods_video'] = $goodsInfo['goods_video'] ? get_image_path($goodsInfo['goods_video']) : '';
		$goodsInfo['goods_price_formated'] = price_format($goodsInfo['goods_price'], true);
		$goodsInfo['market_price_formated'] = price_format($goodsInfo['market_price'], true);

		if (!empty($goodsInfo['desc_mobile'])) {
			$goodsInfo['desc_mobile'] = preg_replace('/height\\="[0-9]+?"/', '', $goodsInfo['desc_mobile']);
			$goodsInfo['desc_mobile'] = preg_replace('/width\\="[0-9]+?"/', '', $goodsInfo['desc_mobile']);
			$goodsInfo['desc_mobile'] = preg_replace('/style=.+?[*|"]/i', '', $goodsInfo['desc_mobile']);
			$goodsInfo['goods_desc'] = preg_replace('/<div[^>]*(tools)[^>]*>(.*?)<\\/div>(.*?)<\\/div>/is', '', $goodsInfo['desc_mobile']);
		}
		else if (!empty($goodsInfo['goods_desc'])) {
			$open_oss = $shopconfig->getShopConfigByCode('open_oss');

			if ($open_oss == 1) {
				$bucket_info = get_bucket_info();
				$bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
				$goodsInfo['goods_desc'] = str_replace(array('src="/images/upload', 'src="images/upload'), 'src="' . $bucket_info['endpoint'] . 'images/upload', $goodsInfo['goods_desc']);
			}
			else {
				$goodsInfo['goods_desc'] = str_replace(array('src="/images/upload', 'src="images/upload'), 'src="' . $rootPath . '/images/upload', $goodsInfo['goods_desc']);
			}
		}
		else {
			$goodsInfo['goods_desc'] = 'xxx';
		}

		$result['goods_info'] = array_merge($goodsInfo, array('is_collect' => empty($collect) ? 0 : 1));
		$ruId = $goodsInfo['user_id'];
		unset($result['goods_info']['user_id']);

		if (0 < $ruId) {
			$result['shop_name'] = $this->shopService->getShopName($ruId);
			$result['coll_num'] = $this->StoreRepository->collnum($ruId);
			$detail = $this->StoreRepository->detail($ruId);
			$result['detail'] = $detail[0];
			$result['detail']['sellershopinfo']['logo_thumb'] = get_image_path(str_replace('../', '', $detail[0]['sellershopinfo']['logo_thumb']));
		}

		$coupont = $this->couponsRepository->goodsCoupont($id, $goodsInfo['user_id'], $uid);
		$result['coupont'] = $coupont;
		$goodsGallery = $this->goodsRepository->goodsGallery($id);

		foreach ($goodsGallery as $k => $v) {
			$goodsGallery[$k] = get_image_path($v['img_url']);
		}

		$result['goods_img'] = $goodsGallery;
		$result['goods_properties'] = $this->goodsRepository->goodsProperties($id);
		$result['recommend'] = $this->goodsRepository->findByType('best');

		foreach ($result['recommend'] as $key => $value) {
			$result['recommend'][$key]['goods_thumb'] = get_image_path($value['goods_thumb']);
		}

		$result['cart_number'] = $this->cartRepository->goodsNumInCartByUser($uid);
		$result['root_path'] = $rootPath;
		return $result;
	}

	public function goodsPropertiesPrice($goods_id, $attr_id, $num = 1, $warehouse_id = 0, $area_id = 0, $store_id = 0)
	{
		$result = array('stock' => '', 'market_price' => '', 'qty' => '', 'spec_price' => '', 'goods_price' => '', 'attr_img' => '');
		$result['stock'] = $this->goodsRepository->goodsAttrNumber($goods_id, $attr_id, $warehouse_id, $area_id, $store_id);
		$result['market_price'] = $this->goodsRepository->goodsMarketPrice($goods_id, $attr_id, $warehouse_id, $area_id);
		$result['market_price_formated'] = price_format($result['market_price'], true);
		$result['qty'] = $num;
		$result['spec_price'] = $this->goodsRepository->goodsPropertyPrice($goods_id, $attr_id, $warehouse_id, $area_id);
		$result['spec_price_formated'] = price_format($result['spec_price'], true);
		$result['goods_price'] = $this->goodsRepository->getFinalPrice($goods_id, $num, true, $attr_id, $warehouse_id, $area_id);
		$result['goods_price_formated'] = price_format($result['goods_price'], true);
		$attr_img = $this->goodsRepository->getAttrImgFlie($goods_id, $attr_id);

		if (!empty($attr_img)) {
			$result['attr_img'] = get_image_path($attr_img['attr_img_flie']);
		}

		return $result;
	}

	public function goodsShare($id, $uid, $path = '', $width = 430, $type = 'goods')
	{
		$goodsInfo = $this->goodsRepository->goodsInfo($id);
		$ruId = $goodsInfo['user_id'];
		$detail = $this->StoreRepository->detail($ruId);
		$app_name = $this->WxappConfigRepository->getWxappConfig();
		$shop_name = empty($detail) ? $app_name[0]['wx_appname'] : $detail[0]['rz_shopName'];
		$result = $this->get_wxcode($path, $width);
		$rootPath = dirname(base_path());
		$imgDir = $rootPath . '/data/gallery_album/ewm/';

		if (!is_dir($imgDir)) {
			mkdir($imgDir);
		}

		$qrcode = $imgDir . $type . '_' . $uid . '_' . $id . '.png';
		file_put_contents($qrcode, $result);
		$rootPath = app('request')->root();
		$rootPath = dirname(dirname($rootPath)) . '/';
		$image_name = $rootPath . 'data/gallery_album/ewm/' . basename($qrcode);
		$userInfo = $this->userRepository->userInfo($uid);
		$user = array('name' => $userInfo['nick_name'], 'id' => $userInfo['id'], 'pic' => get_image_path($userInfo['user_picture']), 'shop_name' => $shop_name, 'image_name' => $image_name);
		$goods_cont = array('id' => $goodsInfo['goods_id'], 'name' => $goodsInfo['goods_name'], 'pic' => get_image_path($goodsInfo['goods_thumb']));
		$share['user'] = $user;
		$share['goods_cont'] = $goods_cont;
		return $share;
	}

	private function get_wxcode($path, $width)
	{
		$config = array('appid' => $this->WxappConfigRepository->getWxappConfigByCode('wx_appid'), 'secret' => $this->WxappConfigRepository->getWxappConfigByCode('wx_appsecret'));
		$wxapp = new \App\Extensions\Wxapp($config);
		$result = $wxapp->getWaCode($path, $width, false);

		if (empty($result)) {
			return false;
		}

		return $result;
	}

	public function getCoupon($cou_id, $uid)
	{
		$ticket = 1;
		$time = gmtime();
		$result = $this->couponsRepository->getCoutype($cou_id);
		$type = $result['cou_type'];
		$cou_rank = $result['cou_ok_user'];
		$ranks = explode(',', $cou_rank);
		$result = $this->couponsRepository->getCoups($cou_id, $uid, $ticket);
		return $result;
	}

	public function history($goods_list, $page = 1, $size = 10)
	{
		if (empty($goods_list)) {
			return NULL;
		}

		$goods_list = explode(',', $goods_list);
		$list = $this->goodsRepository->goodsHistory($goods_list, $page, $size);
		return $list;
	}

	public function goodsSave($list, $goods_id)
	{
		$goods_list = explode(',', $list);
		array_unshift($goods_list, $goods_id);
		$goods_list = array_unique($goods_list);
		$goods_list = implode(',', $goods_list);
		return $goods_list;
	}
}


?>
