<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class TeamService
{
	private $teamRepository;
	private $shopRepository;
	private $goodsRepository;
	private $goodsAttrRepository;
	private $cartRepository;
	private $StoreRepository;
	private $userRepository;
	private $WxappConfigRepository;
	private $collectGoodsRepository;
	private $shopConfigRepository;
	private $shopService;
	private $root_url;

	public function __construct(\App\Repositories\Team\TeamRepository $teamRepository, \App\Repositories\Shop\ShopRepository $shopRepository, \App\Repositories\Goods\GoodsRepository $goodsRepository, \App\Repositories\Goods\GoodsAttrRepository $goodsAttrRepository, \App\Repositories\Cart\CartRepository $cartRepository, \App\Repositories\Store\StoreRepository $StoreRepository, \App\Repositories\User\UserRepository $userRepository, \App\Repositories\Wechat\WxappConfigRepository $WxappConfigRepository, \App\Repositories\Goods\CollectGoodsRepository $collectGoodsRepository, \App\Repositories\ShopConfig\ShopConfigRepository $shopConfigRepository, ShopService $shopService, \Illuminate\Http\Request $request)
	{
		$this->teamRepository = $teamRepository;
		$this->shopRepository = $shopRepository;
		$this->goodsRepository = $goodsRepository;
		$this->goodsAttrRepository = $goodsAttrRepository;
		$this->cartRepository = $cartRepository;
		$this->StoreRepository = $StoreRepository;
		$this->userRepository = $userRepository;
		$this->WxappConfigRepository = $WxappConfigRepository;
		$this->collectGoodsRepository = $collectGoodsRepository;
		$this->shopConfigRepository = $shopConfigRepository;
		$this->shopService = $shopService;
		$this->root_url = dirname(dirname($request->root())) . '/';
	}

	public function getAdsense($position_id = 0)
	{
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$number = $shopconfig->getShopConfigByCode('wx_index_show_number');

		if (empty($number)) {
			$number = 10;
		}

		$adsense = $this->teamRepository->teamPositions($position_id, $number);
		$ads = array();

		foreach ($adsense as $row) {
			if (!empty($row['position_id'])) {
				$src = strpos($row['ad_code'], 'http://') === false && strpos($row['ad_code'], 'https://') === false ? 'data/afficheimg/' . $row['ad_code'] : $row['ad_code'];
				$ads[] = array('pic' => get_image_path($src), 'adsense_id' => $row['ad_id'], 'link' => $row['ad_link']);
			}
		}

		return $ads;
	}

	public function categoriesAdsense($tc_id = 0, $type = 'banner')
	{
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$number = $shopconfig->getShopConfigByCode('wx_index_show_number');

		if (empty($number)) {
			$number = 10;
		}

		$adsense = $this->teamRepository->categoriesAdsense($tc_id, $type, $number);
		$ads = array();

		foreach ($adsense as $row) {
			if (!empty($row['position_id'])) {
				$src = strpos($row['ad_code'], 'http://') === false && strpos($row['ad_code'], 'https://') === false ? 'data/afficheimg/' . $row['ad_code'] : $row['ad_code'];
				$ads[] = array('pic' => get_image_path($src), 'adsense_id' => $row['ad_id'], 'link' => $row['ad_link']);
			}
		}

		return $ads;
	}

	public function teamCategories()
	{
		$arr = array('tc_id', 'name');
		$team_categories_list = $this->teamRepository->teamCategoriesList();
		$list = array();

		foreach ($team_categories_list as $key => $val) {
			$list[$key]['tc_id'] = $val['id'];
			$list[$key]['name'] = $val['name'];
		}

		return $list;
	}

	public function teamCategoriesChild($tc_id = 0)
	{
		$arr = array('tc_id', 'name', 'tc_img');
		$team_categories_child = $this->teamRepository->teamCategoriesChild($tc_id);
		$list = array();

		foreach ($team_categories_child as $key => $val) {
			$list[$key]['tc_id'] = $val['id'];
			$list[$key]['name'] = $val['name'];
			$list[$key]['tc_img'] = get_image_path($val['tc_img']);
		}

		$team_categories_info = $this->teamRepository->teamCategoriesInfo($tc_id);
		$data['list'] = $list;
		$data['title'] = $team_categories_info['name'];
		return $data;
	}

	public function teamGoodsList($page = 1, $size = 10, $tc_id = 0)
	{
		$page = empty($page) ? 1 : $page;
		$arr = array('id', 'goods_id', 'goods_name', 'shop_price', 'goods_thumb', 'team_price', 'team_num', 'limit_num');
		$type = array();

		if (0 < $tc_id) {
			$team_categories_child = $this->teamRepository->teamCategoriesChild($tc_id);

			if (!empty($team_categories_child)) {
				foreach ($team_categories_child as $key) {
					$one_id[] = $key['id'];
				}

				$type = $one_id;
			}
		}

		$goodsList = $this->teamRepository->teamGoodsList($page, $size, $type);
		$list = array();

		foreach ($goodsList as $key => $val) {
			$list[$key]['id'] = $val['id'];
			$list[$key]['goods_id'] = $val['goods_id'];
			$list[$key]['goods_name'] = $val['goods_name'];
			$list[$key]['shop_price'] = price_format($val['shop_price']);
			$list[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
			$list[$key]['team_price'] = price_format($val['team_price']);
			$list[$key]['team_num'] = $val['team_num'];
			$list[$key]['limit_num'] = $val['limit_num'];
		}

		return $list;
	}

	public function virtualOrder($user_id = 0)
	{
		$arr = array('error', 'user_name', 'user_picture', 'seconds');

		if ($this->shopConfigRepository->getShopConfigByCode('virtual_order') == 1) {
			$user = $this->teamRepository->randUserInfo($user_id);

			if ($user) {
				$list = array();

				foreach ($user as $key => $val) {
					$list[$key] = get_object_vars($val);
					$user_info = $this->userRepository->userInfo($list[$key]['user_id']);
					$list[$key]['user_name'] = !empty($user_info['nick_name']) ? $user_info['nick_name'] : $user_info['user_name'];
					$list[$key]['user_picture'] = get_image_path($user_info['user_picture']);
					$list[$key]['seconds'] = rand(1, 8) . '秒前';
				}
			}
			else {
				$list['error'] = 1;
			}
		}
		else {
			$list['error'] = 1;
		}

		return $list;
	}

	public function categoryGoodsList($tc_id = 0, $page = 1, $size = 10, $keyword = '', $sortKey = 0, $sortVal = '')
	{
		$page = empty($page) ? 1 : $page;
		$arr = array('id', 'goods_id', 'goods_name', 'shop_price', 'goods_thumb', 'team_price', 'team_num', 'limit_num', 'goods_number', 'sales_volume');
		$goodsList = $this->teamRepository->categoryGoodsList($tc_id, $page, $size, $keyword, $sortKey, $sortVal);
		$list = array();

		foreach ($goodsList as $key => $val) {
			$list[$key]['id'] = $val['id'];
			$list[$key]['goods_id'] = $val['goods_id'];
			$list[$key]['goods_name'] = $val['goods_name'];
			$list[$key]['shop_price'] = price_format($val['shop_price']);
			$list[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
			$list[$key]['team_price'] = price_format($val['team_price']);
			$list[$key]['team_num'] = $val['team_num'];
			$list[$key]['limit_num'] = $val['limit_num'];
			$list[$key]['goods_number'] = $val['goods_number'];
			$list[$key]['sales_volume'] = $val['sales_volume'];
		}

		return $list;
	}

	public function teamRankingList($page = 1, $size = 10, $type = 0)
	{
		$page = empty($page) ? 1 : $page;
		$arr = array('id', 'goods_id', 'goods_name', 'shop_price', 'goods_thumb', 'team_price', 'team_num', 'limit_num');
		$goodsList = $this->teamRepository->teamRankingList($page, $size, $type);
		$list = array();

		foreach ($goodsList as $key => $val) {
			$list[$key]['key'] = $key + 1;
			$list[$key]['id'] = $val['id'];
			$list[$key]['goods_id'] = $val['goods_id'];
			$list[$key]['goods_name'] = $val['goods_name'];
			$list[$key]['shop_price'] = price_format($val['shop_price']);
			$list[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
			$list[$key]['team_price'] = price_format($val['team_price']);
			$list[$key]['team_num'] = $val['team_num'];
			$list[$key]['limit_num'] = $val['limit_num'];
			$list[$key]['type'] = $type;
		}

		return $list;
	}

	public function goodsDetail($goods_id = 0, $uid, $team_id = 0)
	{
		$result = array('error' => 0, 'user_id' => 0, 'goods_img' => '', 'goods_info' => '', 'team_log' => '', 'new_goods' => '', 'goods_properties' => '');
		$result['user_id'] = $uid;
		$time = local_gettime();
		$rootPath = app('request')->root();
		$rootPath = dirname(dirname($rootPath)) . '/';
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$timeFormat = $shopconfig->getShopConfigByCode('time_format');
		$collect = $this->collectGoodsRepository->findOne($goods_id, $uid);
		$goodsInfo = $this->teamRepository->goodsInfo($goods_id);
		$goodsInfo['team_id'] = 0;

		if ($team_id) {
			$team_info = $this->teamRepository->teamIsFailure($team_id);
			if ($team_info['is_team'] != 1 || $team_info['status'] == 1) {
				return array('error' => 1, 'msg' => '该拼团活动已结束，去查看新的活动吧');
			}

			$goodsInfo['team_id'] = $team_id;
		}

		if ($goodsInfo['is_on_sale'] == 0) {
			return array('error' => 1, 'msg' => '商品已下架');
		}

		if (empty($goodsInfo)) {
			return array('error' => 1, 'msg' => '该拼团活动已结束，去查看新的活动吧');
		}

		$goodsInfo['goods_thumb'] = get_image_path($goodsInfo['goods_thumb']);
		$goodsInfo['team_price'] = price_format($goodsInfo['team_price'], true);
		$goodsInfo['shop_price'] = price_format($goodsInfo['shop_price'], true);
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

		$goodsInfo['is_collect'] = empty($collect) ? 0 : 1;
		$result['goods_info'] = $goodsInfo;
		$ruId = $goodsInfo['user_id'];
		unset($result['goods_info']['user_id']);

		if (0 < $ruId) {
			$result['shop_name'] = $this->shopService->getShopName($ruId);
			$result['coll_num'] = $this->StoreRepository->collnum($ruId);
			$detail = $this->StoreRepository->detail($ruId);
			$result['detail'] = $detail[0];
			$result['detail']['sellershopinfo']['logo_thumb'] = get_image_path(str_replace('../', '', $detail[0]['sellershopinfo']['logo_thumb']));
		}

		$team_log = $this->teamRepository->teamGoodsLog($goods_id);

		if ($team_log) {
			foreach ($team_log as $key => $val) {
				$validity_time = $val['start_time'] + $val['validity_time'] * 3600 + 8 * 3600;
				$team_log[$key]['end_time'] = $validity_time;
				$team_num = $this->teamRepository->surplusNum($val['team_id']);
				$team_log[$key]['surplus'] = $val['team_num'] - $team_num;
				$user_info = $this->userRepository->userInfo($val['team_parent_id']);
				$team_log[$key]['user_name'] = !empty($user_info['nick_name']) ? $user_info['nick_name'] : $user_info['user_name'];
				$team_log[$key]['user_picture'] = get_image_path($user_info['user_picture']);
				$team_log[$key]['is_team'] = 0;
				$team_join = $this->teamRepository->teamJoin($uid, $val['team_id']);

				if (0 < $team_join) {
					$team_log[$key]['is_team'] = 1;
				}

				if ($validity_time <= $time) {
					unset($team_log[$key]);
				}
			}

			$result['team_log'] = $team_log;
		}

		$new_goods = $this->teamRepository->teamNewGoods('is_new', $goodsInfo['user_id']);

		foreach ($new_goods as $key => $val) {
			$new_goods[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
			$new_goods[$key]['shop_price'] = price_format($val['shop_price'], true);
			$new_goods[$key]['team_price'] = price_format($val['team_price'], true);
		}

		$result['new_goods'] = $new_goods;
		$goodsGallery = $this->goodsRepository->goodsGallery($goods_id);

		foreach ($goodsGallery as $k => $v) {
			$goodsGallery[$k] = get_image_path($v['img_url']);
		}

		$result['goods_img'] = $goodsGallery;
		$result['goods_properties'] = $this->goodsRepository->goodsProperties($goods_id);
		return $result;
	}

	public function goodsPropertiesPrice($goods_id, $attr_id, $num = 1, $warehouse_id = 0, $area_id = 0, $store_id = 0)
	{
		$result = array('stock' => '', 'market_price' => '', 'qty' => '', 'spec_price' => '', 'goods_price' => '', 'attr_img' => '');
		$goodsInfo = $this->teamRepository->goodsInfo($goods_id);
		$result['stock'] = $this->goodsRepository->goodsAttrNumber($goodsInfo['goods_id'], $attr_id, $warehouse_id, $area_id, $store_id);
		$result['market_price'] = $this->goodsRepository->goodsMarketPrice($goodsInfo['goods_id'], $attr_id, $warehouse_id, $area_id);
		$result['market_price_formated'] = price_format($result['market_price'], true);
		$result['qty'] = $num;
		$result['spec_price'] = $this->goodsRepository->goodsPropertyPrice($goodsInfo['goods_id'], $attr_id, $warehouse_id, $area_id);
		$result['spec_price_formated'] = price_format($result['spec_price'], true);
		$result['goods_price'] = $this->teamRepository->getFinalPrice($goodsInfo['goods_id'], $num, true, $attr_id, $warehouse_id, $area_id);
		$result['goods_price_formated'] = price_format($result['goods_price'], true);
		$attr_img = $this->goodsRepository->getAttrImgFlie($goodsInfo['goods_id'], $attr_id);

		if (!empty($attr_img['attr_img_flie'])) {
			$result['attr_img'] = get_image_path($attr_img['attr_img_flie']);
		}

		return $result;
	}

	public function addGoodsToCart($params)
	{
		$result = array('code' => 0, 'flow_type' => 0, 't_id' => 0, 'team_id' => 0);
		$goods = $this->teamRepository->goodsInfo($params['goods_id']);

		if ($goods['is_on_sale'] != 1) {
			return '商品已下架';
		}

		$goodsAttr = empty($params['attr_id']) ? '' : json_decode($params['attr_id'], 1);
		$goodsAttrId = implode(',', $goodsAttr);
		$product = $this->goodsRepository->getProductByGoods($params['goods_id'], implode('|', $goodsAttr));

		if (empty($product)) {
			$product['id'] = 0;
		}

		$attrName = $this->goodsAttrRepository->getAttrNameById($goodsAttr);
		$attrNameStr = '';

		foreach ($attrName as $v) {
			$attrNameStr .= $v['attr_name'] . ':' . $v['attr_value'] . " \n";
		}

		$goodsPrice = $this->teamRepository->getFinalPrice($params['goods_id'], $params['num'], true, $goodsAttr);
		$attr_number = $this->goodsRepository->goodsAttrNumber($params['goods_id'], $goodsAttr);

		if ($attr_number < $params['num']) {
			return '当前库存不足';
		}

		if ($goods['astrict_num'] < $params['num']) {
			return '已超过拼团限购数量';
		}

		$this->cartRepository->clearCart(CART_TEAM_GOODS, $params['uid']);
		$arguments = array('goods_id' => $goods['goods_id'], 'user_id' => $params['uid'], 'goods_sn' => $goods['goods_sn'], 'product_id' => empty($product['id']) ? '' : $product['id'], 'group_id' => '', 'goods_name' => $goods['goods_name'], 'market_price' => $goods['market_price'], 'goods_price' => $goodsPrice, 'goods_number' => $params['num'], 'goods_attr' => $attrNameStr, 'is_real' => $goods['is_real'], 'extension_code' => empty($params['extension_code']) ? '' : $params['extension_code'], 'parent_id' => 0, 'rec_type' => CART_TEAM_GOODS, 'is_gift' => 0, 'is_shipping' => $goods['is_shipping'], 'can_handsel' => '', 'model_attr' => $goods['model_attr'], 'goods_attr_id' => $goodsAttrId, 'ru_id' => $goods['user_id'], 'shopping_fee' => '', 'warehouse_id' => '', 'area_id' => '', 'add_time' => gmtime(), 'stages_qishu' => '', 'store_id' => '', 'freight' => '', 'tid' => '', 'shipping_fee' => '', 'store_mobile' => '', 'take_time' => '', 'is_checked' => '1');
		$goodsNumber = $this->cartRepository->addGoodsToCart($arguments);

		if ($goodsNumber) {
			$result['flow_type'] = CART_TEAM_GOODS;
			$result['t_id'] = $params['t_id'];

			if (0 < $params['team_id']) {
				$result['team_id'] = $params['team_id'];
			}
		}

		return $result;
	}

	public function teamWait($uid = 0, $team_id = 0, $user_id)
	{
		$result = array('error' => 0, 'team_info' => '', 'teamUser' => '');
		$team_info = $this->teamRepository->teamInfo($team_id);
		$user_info = $this->userRepository->userInfo($team_info['team_parent_id']);
		$team_info['user_name'] = !empty($user_info['nick_name']) ? $user_info['nick_name'] : $user_info['user_name'];
		$team_info['user_picture'] = get_image_path($user_info['user_picture']);
		$team_info['goods_thumb'] = get_image_path($team_info['goods_thumb']);
		$team_info['team_price'] = price_format($team_info['team_price']);
		$end_time = $team_info['start_time'] + $team_info['validity_time'] * 3600;
		$team_info['end_time'] = $end_time + 8 * 3600;
		$team_num = $this->teamRepository->surplusNum($team_info['team_id']);
		$team_info['surplus'] = $team_info['team_num'] - $team_num;
		$team_info['bar'] = round($team_num * 100 / $team_info['team_num'], 0);
		if ($team_info['status'] != 1 && gmtime() < $end_time && $team_info['is_team'] == 1) {
			$team_info['status'] = 0;
		}
		else {
			if ($team_info['status'] != 1 && $end_time < gmtime() || $team_info['is_team'] != 1) {
				$team_info['status'] = 2;
			}
			else if ($team_info['status'] = 1) {
				$team_info['status'] = 1;
			}
		}

		$team_join = $this->teamRepository->teamJoin($uid, $team_id);

		if (0 < $team_join) {
			$team_info['team_join'] = 1;
		}

		$result['team_info'] = $team_info;
		$teamUser = $this->teamRepository->teamUserList($team_id, 1, 5);

		foreach ($teamUser as $key => $val) {
			$user_info = $this->userRepository->userInfo($val['user_id']);
			$teamUser[$key]['user_name'] = !empty($user_info['nick_name']) ? $user_info['nick_name'] : $user_info['user_name'];
			$teamUser[$key]['user_picture'] = get_image_path($user_info['user_picture']);
		}

		$result['teamUser'] = $teamUser;
		return $result;
	}

	public function teamUser($team_id = 0, $page = 1, $size = 10)
	{
		$page = empty($page) ? 1 : $page;
		$teamUser = $this->teamRepository->teamUserList($team_id, $page, $size);
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$timeFormat = $shopconfig->getShopConfigByCode('time_format');

		foreach ($teamUser as $key => $val) {
			$teamUser[$key]['add_time'] = local_date($timeFormat, $val['add_time']);
			$user_info = $this->userRepository->userInfo($val['user_id']);
			$teamUser[$key]['user_name'] = !empty($user_info['nick_name']) ? $user_info['nick_name'] : $user_info['user_name'];
			$teamUser[$key]['user_picture'] = get_image_path($user_info['user_picture']);
		}

		return $teamUser;
	}

	public function teamUserOrder($user_id, $type = 0, $page = 1, $size = 10)
	{
		$page = empty($page) ? 1 : $page;
		$arr = array('id', 'goods_id', 'goods_name', 'shop_price', 'goods_thumb', 'team_price', 'team_num', 'limit_num');
		$team_order = $this->teamRepository->teamUserOrder($user_id, $type, $page, $size);
		$list = array();

		foreach ($team_order as $key => $val) {
			$list[$key] = get_object_vars($val);
			$list[$key]['id'] = $list[$key]['id'];
			$list[$key]['team_id'] = $list[$key]['team_id'];
			$list[$key]['goods_id'] = $list[$key]['goods_id'];
			$list[$key]['order_id'] = $list[$key]['order_id'];
			$list[$key]['team_id'] = $list[$key]['team_id'];
			$list[$key]['user_id'] = $list[$key]['user_id'];
			$list[$key]['goods_name'] = $list[$key]['goods_name'];
			$list[$key]['shop_price'] = price_format($list[$key]['shop_price']);
			$list[$key]['goods_thumb'] = get_image_path($list[$key]['goods_thumb']);
			$list[$key]['team_price'] = price_format($list[$key]['team_price']);
			$list[$key]['team_num'] = $list[$key]['team_num'];
			$team_num = $this->teamRepository->surplusNum($list[$key]['team_id']);
			$list[$key]['limit_num'] = $team_num;
			$list[$key]['type'] = $type;
		}

		return $list;
	}
}


?>
