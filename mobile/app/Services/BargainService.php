<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class BargainService
{
	private $bargainRepository;
	private $shopRepository;
	private $goodsRepository;
	private $goodsAttrRepository;
	private $cartRepository;
	private $StoreRepository;
	private $userRepository;
	private $WxappConfigRepository;
	private $root_url;
	private $authService;

	public function __construct(\App\Repositories\Bargain\BargainRepository $bargainRepository, \App\Repositories\Shop\ShopRepository $shopRepository, \App\Repositories\Goods\GoodsRepository $goodsRepository, \App\Repositories\Goods\GoodsAttrRepository $goodsAttrRepository, \App\Repositories\Cart\CartRepository $cartRepository, \App\Repositories\Store\StoreRepository $StoreRepository, \App\Repositories\User\UserRepository $userRepository, \App\Repositories\Wechat\WxappConfigRepository $WxappConfigRepository, AuthService $authService, \Illuminate\Http\Request $request)
	{
		$this->bargainRepository = $bargainRepository;
		$this->shopRepository = $shopRepository;
		$this->goodsRepository = $goodsRepository;
		$this->goodsAttrRepository = $goodsAttrRepository;
		$this->cartRepository = $cartRepository;
		$this->StoreRepository = $StoreRepository;
		$this->userRepository = $userRepository;
		$this->WxappConfigRepository = $WxappConfigRepository;
		$this->authService = $authService;
		$this->root_url = dirname(dirname($request->root())) . '/';
	}

	public function bargainGoodsList($page = 1, $size = 10, $user_id = 0)
	{
		$page = empty($page) ? 1 : $page;
		$arr = array('id', 'goods_id', 'goods_name', 'shop_price', 'goods_thumb', 'total_num', 'target_price');
		$goodsList = $this->bargainRepository->findByType($user_id, '', $page, $size);
		return $goodsList;
	}

	public function getAdsense($position_id = 0)
	{
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$number = $shopconfig->getShopConfigByCode('wx_index_show_number');

		if (empty($number)) {
			$number = 10;
		}

		$adsense = $this->bargainRepository->bargainPositions($position_id, $number);
		$ads = array();

		foreach ($adsense as $row) {
			if (!empty($row['position_id'])) {
				$src = strpos($row['ad_code'], 'http://') === false && strpos($row['ad_code'], 'https://') === false ? 'data/afficheimg/' . $row['ad_code'] : $row['ad_code'];
				$ads[] = array('pic' => get_image_path($src), 'adsense_id' => $row['ad_id'], 'link' => $row['ad_link']);
			}
		}

		return $ads;
	}

	public function goodsDetail($id = 0, $user_id = 0, $bs_id = 0)
	{
		$result = array('error' => 0, 'goods_img' => '', 'goods_info' => '', 'bargain_info' => '', 'bargain_list' => '', 'bargain_ranking' => '', 'bargain_hot' => '', 'goods_properties' => '');
		$rootPath = app('request')->root();
		$rootPath = dirname(dirname($rootPath)) . '/';
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$timeFormat = $shopconfig->getShopConfigByCode('time_format');
		$time = gmtime();
		$goodsInfo = $this->bargainRepository->goodsInfo($id);
		$goodsInfo['bs_id'] = 0;
		$goodsInfo['add_bargain'] = 0;
		$goodsInfo['bargain_join'] = 0;
		$goodsInfo['bargain_bar'] = 0;
		$goodsInfo['final_price'] = '';
		$goodsInfo['bargain_end'] = '';

		if ($goodsInfo['is_on_sale'] == 0) {
			return array('error' => 1, 'msg' => '商品已下架');
		}

		if ($goodsInfo['status'] == 1 || $goodsInfo['end_time'] < $time) {
			$goodsInfo['bargain_end'] = 1;
		}

		if ($bs_id) {
			$goodsInfo['bs_id'] = empty($bs_id) ? 0 : $bs_id;
			$bs_id = $goodsInfo['bs_id'];
		}

		$add_bargain = $this->bargainRepository->isAddBargain($id, $user_id);

		if ($add_bargain) {
			$goodsInfo['bs_id'] = empty($add_bargain['id']) ? 0 : $add_bargain['id'];
			$bs_id = $goodsInfo['bs_id'];
			$goodsInfo['add_bargain'] = 1;
		}

		if (!empty($bs_id)) {
			$bargain_info = $this->bargainRepository->isBargainJoin($bs_id, $user_id);

			if ($bargain_info) {
				$goodsInfo['bargain_join'] = 1;
				$user_info = $this->userRepository->userInfo($bargain_info['user_id']);
				$bargain_info['user_name'] = !empty($user_info['nick_name']) ? $user_info['nick_name'] : $user_info['user_name'];
				$bargain_info['user_picture'] = get_image_path($user_info['user_picture']);
				$bargain_ranking = $this->bargainRepository->getBargainRanking($id);
				$bargain_info['ranking_num'] = count($bargain_ranking);
				$rank = $this->bargainRepository->copyArrayColumn($bargain_ranking, 'user_id');
				$rank = array_search($user_id, $rank);
				$bargain_info['rank'] = $rank + 1;
				$bargain_log = $this->bargainRepository->bargainLog($bs_id);
				$bargain_info['final_price'] = $bargain_log['final_price'];
				$result['bargain_info'] = $bargain_info;
			}

			$bargain_list = $this->bargainRepository->getBargainStatistics($bs_id);
			$bargain_num = count($bargain_list);
			$goodsInfo['bargain_num'] = $bargain_num;
			$result['bargain_list'] = $bargain_list;
			$bargain_log = $this->bargainRepository->bargainLog($bs_id);
			$goodsInfo['final_price'] = $bargain_log['final_price'];

			if ($bargain_log['goods_attr_id']) {
				$spec = explode(',', $bargain_log['goods_attr_id']);
				$goodsInfo['shop_price'] = $this->bargainRepository->getFinalPrice($goodsInfo['goods_id'], '', true, $spec, '', '');
				$goodsInfo['target_price'] = $this->bargainRepository->bargainTargetPrice($id, $goodsInfo['goods_id'], $spec, 0, 0, $goodsInfo['model_attr']);
				$attrName = $this->goodsAttrRepository->getAttrNameById($spec);
				$attrNameStr = '';

				foreach ($attrName as $v) {
					$attrNameStr .= $v['attr_name'] . ':' . $v['attr_value'] . " \n";
				}

				$goodsInfo['attr_name'] = $attrNameStr;
			}

			$surplus = $goodsInfo['shop_price'] - $goodsInfo['target_price'];
			$subtract = $this->bargainRepository->subtractPriceSum($bs_id);
			$bargain_bar = round($subtract * 100 / $surplus, 0);
			$goodsInfo['bargain_bar'] = $bargain_bar;
		}

		$bargain_ranking = $this->bargainRepository->getBargainRanking($id);
		$goodsInfo['ranking_num'] = count($bargain_ranking);
		$rank = $this->bargainRepository->copyArrayColumn($bargain_ranking, 'user_id');
		$rank = array_search($user_id, $rank);
		$goodsInfo['rank'] = $rank + 1;
		$result['bargain_ranking'] = $bargain_ranking;
		$result['bargain_hot'] = $this->bargainRepository->findByType($user_id, 'is_hot');

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

		$goodsInfo['goods_thumb'] = get_image_path($goodsInfo['goods_thumb']);
		$goodsInfo['shop_price'] = price_format($goodsInfo['shop_price'], true);
		$goodsInfo['target_price'] = price_format($goodsInfo['target_price'], true);
		$goodsInfo['market_price_formated'] = price_format($goodsInfo['market_price'], true);
		$goodsInfo['end_time'] = $goodsInfo['end_time'] + 8 * 3600;
		$result['goods_info'] = $goodsInfo;
		$goodsGallery = $this->goodsRepository->goodsGallery($goodsInfo['goods_id']);

		foreach ($goodsGallery as $k => $v) {
			$goodsGallery[$k] = get_image_path($v['img_url']);
		}

		$result['goods_img'] = $goodsGallery;
		$result['goods_properties'] = $this->goodsRepository->goodsProperties($goodsInfo['goods_id']);
		$result['root_path'] = $rootPath;
		return $result;
	}

	public function goodsPropertiesPrice($bargain_id, $attr_id, $num = 1, $warehouse_id = 0, $area_id = 0, $store_id = 0)
	{
		$result = array('stock' => '', 'market_price' => '', 'qty' => '', 'spec_price' => '', 'goods_price' => '', 'target_price' => '', 'attr_img' => '');
		$goodsInfo = $this->bargainRepository->goodsInfo($bargain_id);
		$result['target_price'] = $goodsInfo['target_price'];
		$result['stock'] = $this->goodsRepository->goodsAttrNumber($goodsInfo['goods_id'], $attr_id, $warehouse_id, $area_id, $store_id);
		$result['market_price'] = $this->goodsRepository->goodsMarketPrice($goodsInfo['goods_id'], $attr_id, $warehouse_id, $area_id);
		$result['market_price_formated'] = price_format($result['market_price'], true);
		$result['qty'] = $num;
		$result['spec_price'] = $this->goodsRepository->goodsPropertyPrice($goodsInfo['goods_id'], $attr_id, $warehouse_id, $area_id);
		$result['spec_price_formated'] = price_format($result['spec_price'], true);
		$result['goods_price'] = $goodsInfo['goods_price'];

		if (!empty($attr_id)) {
			$result['target_price'] = $this->bargainRepository->bargainTargetPrice($bargain_id, $goodsInfo['goods_id'], $attr_id, $warehouse_id, $area_id, $goodsInfo['model_attr']);
			$result['goods_price'] = $this->bargainRepository->getFinalPrice($goodsInfo['goods_id'], $num, true, $attr_id, $warehouse_id, $area_id);
		}

		$result['goods_price_formated'] = price_format($result['goods_price'], true);
		$attr_img = $this->goodsRepository->getAttrImgFlie($goodsInfo['goods_id'], $attr_id);

		if (!empty($attr_img)) {
			$result['attr_img'] = get_image_path($attr_img['attr_img_flie']);
		}

		return $result;
	}

	public function addBargain($bargain_id, $attr_id, $user_id = 0, $warehouse_id = 0, $area_id = 0)
	{
		$goodsInfo = $this->bargainRepository->goodsInfo($bargain_id);
		$attr_id = empty($attr_id) ? '' : json_decode($attr_id, 1);
		$goodsAttrId = implode(',', $attr_id);

		if (!empty($attr_id)) {
			$final_price = $this->bargainRepository->getFinalPrice($goodsInfo['goods_id'], '', true, $attr_id, $warehouse_id, $area_id);
		}
		else {
			$final_price = $goodsInfo['shop_price'];
		}

		$arguments = array('bargain_id' => $bargain_id, 'goods_attr_id' => $goodsAttrId, 'user_id' => $user_id, 'final_price' => $final_price, 'add_time' => gmtime());
		$result = $this->bargainRepository->addBargain($arguments);
		$attrName = $this->goodsAttrRepository->getAttrNameById($attr_id);
		$attrNameStr = '';

		foreach ($attrName as $v) {
			$attrNameStr .= $v['attr_name'] . ':' . $v['attr_value'] . " \n";
		}

		$result['attr_name'] = $attrNameStr;
		$result['num'] = 1;
		$result['add_bargain'] = 1;
		$this->bargainRepository->updateBargain($bargain_id, $goodsInfo['total_num']);
		return $result;
	}

	public function goBargain($bargain_id = 0, $bs_id = 0, $user_id = 0, $form_id = '')
	{
		$result = array('error' => '', 'message' => '');
		$bargain = $this->bargainRepository->goodsInfo($bargain_id);
		$bs_log = $this->bargainRepository->bargainLog($bs_id);

		if ($bs_log['goods_attr_id']) {
			$spec = explode(',', $bs_log['goods_attr_id']);
			$bargain['target_price'] = $this->bargainRepository->bargainTargetPrice($bargain_id, $bargain['goods_id'], $spec, 0, 0, $bargain['model_attr']);
		}

		$number = $this->bargainRepository->bargainLogNumber($bs_id, $user_id);

		if (0 < $number) {
			$result = array('error' => 1, 'message' => '您已参与砍价！');
			return $result;
		}

		if ($bargain['target_price'] == $bs_log['final_price']) {
			$result = array('error' => 1, 'message' => '已砍至最低价格！');
			return $result;
		}
		else {
			$subtract_price = rand($bargain['min_price'], $bargain['max_price']);
			$subtract = $bs_log['final_price'] - $subtract_price;

			if ($subtract < $bargain['target_price']) {
				$subtract_price = $bs_log['final_price'] - $bargain['target_price'];
			}
		}

		$arguments = array('bs_id' => $bs_id, 'user_id' => $user_id, 'subtract_price' => $subtract_price, 'add_time' => gmtime());
		$add = $this->bargainRepository->addBargainStatistics($arguments);

		if ($add) {
			$count_num = $bs_log['count_num'] + 1;
			$final_price = $bs_log['final_price'] - $subtract_price;
			$this->bargainRepository->updateBargainStatistics($bs_id, $count_num, $final_price);
			$bargain_ranking = $this->bargainRepository->getBargainRanking($bargain_id);
			$rank = $this->bargainRepository->copyArrayColumn($bargain_ranking, 'user_id');
			$rank = array_search($bs_log['user_id'], $rank);
			$rank = $rank + 1;
			$user_info = $this->userRepository->userInfo($user_id);
			$user_name = !empty($user_info['nick_name']) ? $user_info['nick_name'] : $user_info['user_name'];
			$user_picture = get_image_path($user_info['user_picture']);
			$add_bargain = 0;
			$add_bargain_info = $this->bargainRepository->isAddBargain($bargain_id, $user_id);

			if ($add_bargain_info) {
				$add_bargain = 1;
			}

			$result = array('error' => 2, 'subtract_price' => $subtract_price, 'final_price' => $final_price, 'rank' => $rank, 'user_name' => $user_name, 'user_picture' => $user_picture, 'add_bargain' => $add_bargain, 'bs_id' => $bs_id, 'bargain_join' => 1, 'message' => '砍价成功');
			$pushData = array(
				'keyword1' => array('value' => $bargain['goods_name'], 'color' => '#000000'),
				'keyword2' => array('value' => price_format($bargain['target_price'], true), 'color' => '#000000'),
				'keyword3' => array('value' => price_format($subtract_price, true), 'color' => '#000000')
				);
			$url = 'pages/bargain/goods?objectId=' . $bargain_id . '&bs_id=' . $bs_id;
			$this->authService->wxappPushTemplate('AT1173', $pushData, $url, $user_id, $form_id);
		}
		else {
			$result = array('error' => 1, 'message' => '砍价失败');
		}

		return $result;
	}

	public function addGoodsToCart($params)
	{
		$result = array('code' => 0, 'flow_type' => 0, 'bs_id' => 0);
		$goods = $this->goodsRepository->find($params['goods_id']);

		if ($goods['is_on_sale'] != 1) {
			return '商品已下架';
		}

		$bs_log = $this->bargainRepository->bargainLog($params['bs_id']);
		$goodsAttrId = $bs_log['goods_attr_id'];
		$goodsAttr = explode(',', $goodsAttrId);
		$product = $this->goodsRepository->getProductByGoods($params['goods_id'], implode('|', $goodsAttr));

		if (empty($product)) {
			$product['id'] = 0;
		}

		$attrName = $this->goodsAttrRepository->getAttrNameById($goodsAttr);
		$attrNameStr = '';

		foreach ($attrName as $v) {
			$attrNameStr .= $v['attr_name'] . ':' . $v['attr_value'] . " \n";
		}

		$attr_number = $this->goodsRepository->goodsAttrNumber($params['goods_id'], $goodsAttr);

		if ($attr_number < $params['num']) {
			return '当前库存不足';
		}

		$this->cartRepository->clearCart(CART_BARGAIN_GOODS, $params['uid']);
		$goodsPrice = $bs_log['final_price'];
		$arguments = array('goods_id' => $goods['goods_id'], 'user_id' => $params['uid'], 'goods_sn' => $goods['goods_sn'], 'product_id' => empty($product['id']) ? '' : $product['id'], 'group_id' => '', 'goods_name' => $goods['goods_name'], 'market_price' => $goods['market_price'], 'goods_price' => $goodsPrice, 'goods_number' => $params['num'], 'goods_attr' => $attrNameStr, 'is_real' => $goods['is_real'], 'extension_code' => empty($params['extension_code']) ? '' : $params['extension_code'], 'parent_id' => 0, 'rec_type' => CART_BARGAIN_GOODS, 'is_gift' => 0, 'is_shipping' => $goods['is_shipping'], 'can_handsel' => '', 'model_attr' => $goods['model_attr'], 'goods_attr_id' => $goodsAttrId, 'ru_id' => $goods['user_id'], 'shopping_fee' => '', 'warehouse_id' => '', 'area_id' => '', 'add_time' => gmtime(), 'stages_qishu' => '', 'store_id' => '', 'freight' => '', 'tid' => '', 'shipping_fee' => '', 'store_mobile' => '', 'take_time' => '', 'is_checked' => '1');
		$goodsNumber = $this->cartRepository->addGoodsToCart($arguments);

		if ($goodsNumber) {
			$result['flow_type'] = CART_BARGAIN_GOODS;
			$result['bs_id'] = $params['bs_id'];
		}

		return $result;
	}

	public function myBargain($user_id = 0, $page = 1, $size = 10)
	{
		$page = empty($page) ? 1 : $page;
		$field = array('id', 'goods_id', 'goods_name', 'shop_price', 'goods_thumb', 'target_price');
		$list = $this->bargainRepository->myBargain($user_id, $page, $size);

		foreach ($list as $key => $v) {
			$list[$key]['goods_thumb'] = get_image_path($v['goods_thumb']);
			$list[$key]['shop_price'] = price_format($v['shop_price'], false);
			$list[$key]['target_price'] = price_format($v['target_price'], false);
			$target_price = $this->bargainRepository->getBargainTargetPrice($v['id']);

			if ($target_price) {
				$list[$key]['target_price'] = price_format($target_price, false);
			}
		}

		return $list;
	}
}


?>
