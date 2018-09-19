<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Bargain;

class BargainRepository
{
	protected $goods;
	private $field;
	private $goodsAttrRepository;
	private $shopConfigRepository;
	private $goodsRepository;
	private $userRepository;

	public function __construct(\App\Repositories\Goods\GoodsAttrRepository $goodsAttrRepository, \App\Repositories\ShopConfig\ShopConfigRepository $shopConfigRepository, \App\Repositories\Goods\GoodsRepository $goodsRepository, \App\Repositories\User\UserRepository $userRepository)
	{
		$this->setField();
		$this->goodsAttrRepository = $goodsAttrRepository;
		$this->shopConfigRepository = $shopConfigRepository;
		$this->goodsRepository = $goodsRepository;
		$this->userRepository = $userRepository;
	}

	public function create(array $data)
	{
	}

	public function get($id)
	{
	}

	public function update(array $data)
	{
	}

	public function delete($id)
	{
	}

	public function search(array $data)
	{
	}

	public function sku($id)
	{
	}

	public function skuAdd()
	{
	}

	public function setField()
	{
		$this->field = array('category' => 'cat_id');
	}

	public function getField($field)
	{
		return $this->field[$field];
	}

	public function find($bargain_id)
	{
		return \App\Models\BargainGoods::select('*')->where('bargain_id', $bargain_id)->first()->toArray();
	}

	public function bargainLog($bs_id = 0)
	{
		return \App\Models\BargainStatisticsLog::select('*')->where('id', $bs_id)->first()->toArray();
	}

	public function isAddBargain($bargain_id = 0, $user_id)
	{
		$tatistics_og = \App\Models\BargainStatisticsLog::select('*')->where('bargain_id', $bargain_id)->where('user_id', $user_id)->where('status', 0)->first();

		if ($tatistics_og === null) {
			return array();
		}

		return $tatistics_og->toArray();
	}

	public function isBargainJoin($bs_id = 0, $user_id)
	{
		$bargain_info = \App\Models\BargainStatistics::select('*')->where('bs_id', $bs_id)->where('user_id', $user_id)->first();

		if ($bargain_info === null) {
			return array();
		}

		return $bargain_info->toArray();
	}

	public function bargainLogNumber($bs_id = 0, $user_id = 0)
	{
		return \App\Models\BargainStatistics::select('*')->where('bs_id', $bs_id)->where('user_id', $user_id)->count();
	}

	public function bargainPositions($tc_type = 'weapp', $num = 3)
	{
		$time = gmtime();
		$res = \App\Models\TouchAd::select('ad_id', 'touch_ad.position_id', 'media_type', 'ad_link', 'ad_code', 'ad_name')->with(array('position'))->join('touch_ad_position', 'touch_ad_position.position_id', '=', 'touch_ad.position_id')->where('start_time', '<=', $time)->where('end_time', '>=', $time)->where('touch_ad_position.position_id', $tc_type)->where('enabled', 1)->limit($num)->get()->toArray();
		$res = array_map(function($v) {
			if (!empty($v['position'])) {
				$temp = array_merge($v, $v['position']);
				unset($temp['position']);
				return $temp;
			}
		}, $res);
		return $res;
	}

	public function findByType($user_id = 0, $type = '', $page = 1, $size = 10)
	{
		$time = gmtime();
		$goods = \App\Models\BargainGoods::from('bargain_goods as bg')->select('bg.id', 'bg.start_time', 'bg.goods_price', 'bg.end_time', 'bg.target_price', 'bg.total_num', 'g.goods_id', 'g.goods_name', 'g.shop_price', 'g.market_price', 'g.goods_thumb', 'g.goods_img')->leftjoin('goods as g', 'g.goods_id', '=', 'bg.goods_id');

		if ($type == 'is_hot') {
			$goods->where('bg.is_hot', 1);
		}

		$begin = ($page - 1) * $size;
		$list = $goods->where('bg.status', 0)->where('bg.is_audit', 2)->where('bg.is_delete', 0)->where('bg.start_time', '<=', $time)->where('bg.end_time', '>=', $time)->where('g.is_on_sale', 1)->where('g.is_alone_sale', 1)->where('g.is_delete', 0)->where('g.review_status', '>', 2)->offset($begin)->orderby('bg.id', 'desc')->limit($size)->get()->toArray();

		if ($list === null) {
			return array();
		}

		foreach ($list as $key => $val) {
			$list[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
			$list[$key]['shop_price'] = price_format($val['shop_price'], false);
			$list[$key]['target_price'] = price_format($val['target_price'], false);
			$target_price = $this->getBargainTargetPrice($val['id']);

			if ($target_price) {
				$list[$key]['target_price'] = price_format($target_price, false);
			}

			$add_bargain = $this->isAddBargain($val['id'], $user_id);
			$list[$key]['bs_id'] = empty($add_bargain['id']) ? 0 : $add_bargain['id'];
		}

		return $list;
	}

	public function goodsInfo($bargain_id = 0)
	{
		$res = \App\Models\BargainGoods::from('bargain_goods as bg')->select('bg.id', 'bg.goods_id', 'bg.goods_price', 'bg.start_time', 'bg.end_time', 'bg.target_price', 'bg.min_price', 'bg.max_price', 'bg.total_num', 'bg.status', 'bg.bargain_desc', 'g.user_id', 'g.goods_sn', 'g.goods_name', 'g.is_real', 'g.is_shipping', 'g.is_on_sale', 'g.shop_price', 'g.market_price', 'g.goods_thumb', 'g.goods_img', 'g.goods_number', 'g.goods_desc', 'g.desc_mobile', 'g.goods_type', 'g.goods_brief', 'g.model_attr', 'g.review_status')->leftjoin('goods as g', 'g.goods_id', '=', 'bg.goods_id')->where('bg.id', $bargain_id)->first();

		if ($res === null) {
			return array();
		}

		return $res->toArray();
	}

	public function getBargainStatistics($bs_id = 0)
	{
		$list = \App\Models\BargainStatistics::select('user_id', 'add_time', 'subtract_price')->where('bs_id', $bs_id)->orderby('add_time', 'desc')->get();

		if ($list === null) {
			return array();
		}

		$list = $list->toArray();
		$timeFormat = $this->shopConfigRepository->getShopConfigByCode('time_format');

		foreach ($list as $key => $val) {
			$list[$key]['subtract_price'] = price_format($val['subtract_price'], false);
			$list[$key]['add_time'] = local_date($timeFormat, $val['add_time']);
			$user_info = $this->userRepository->userInfo($val['user_id']);
			$list[$key]['user_name'] = !empty($user_info['nick_name']) ? $user_info['nick_name'] : $user_info['user_name'];
			$list[$key]['user_picture'] = get_image_path($user_info['user_picture']);
		}

		return $list;
	}

	public function getBargainRanking($bargain_id = 0)
	{
		$sql = 'SELECT bsl.user_id , IFNULL((select sum(subtract_price) from dsc_bargain_statistics where bs_id = bsl.id),0) as money from dsc_bargain_statistics_log bsl ';
		$sql .= ' left join dsc_bargain_statistics bs on bsl.id = bs.bs_id ';
		$sql .= ' where bsl.bargain_id=' . $bargain_id . ' GROUP BY bsl.id order by money desc ';
		$list = \Illuminate\Support\Facades\DB::select($sql);

		if (empty($list)) {
			return array();
		}

		foreach ($list as $key => $val) {
			$total[$key] = get_object_vars($val);
			$total[$key]['rank'] = $key + 1;
			$total[$key]['money'] = price_format($total[$key]['money'], false);
			$user_info = $this->userRepository->userInfo($total[$key]['user_id']);
			$total[$key]['user_name'] = !empty($user_info['nick_name']) ? $user_info['nick_name'] : $user_info['user_name'];
			$total[$key]['user_picture'] = get_image_path($user_info['user_picture']);
		}

		return $total;
	}

	public function getBargainTargetPrice($bargain_id = 0)
	{
		$target_price = \App\Models\ActivityGoodsAttr::where('bargain_id', $bargain_id)->min('target_price');

		if ($target_price === null) {
			return array();
		}

		return $target_price;
	}

	public function subtractPriceSum($bs_id = 0)
	{
		$subtract_price = \App\Models\BargainStatistics::where('bs_id', $bs_id)->sum('subtract_price');

		if ($subtract_price === null) {
			return 0;
		}

		return $subtract_price;
	}

	public function getFinalPrice($goods_id, $goods_num = '1', $is_spec_price = false, $property = array(), $warehouse_id = 0, $area_id = 0)
	{
		$final_price = 0;
		$spec_price = 0;

		if ($is_spec_price) {
			if (!empty($property)) {
				$spec_price = $this->goodsRepository->goodsPropertyPrice($goods_id, $property, $warehouse_id, $area_id);
			}
		}

		$goods = \App\Models\Goods::from('goods as g')->select('g.shop_price', 'g.promote_price', 'g.promote_start_date', 'g.promote_end_date', 'mp.user_price')->leftjoin('member_price as mp', 'mp.goods_id', '=', 'g.goods_id')->where('g.goods_id', $goods_id)->where('g.is_delete', 0)->first()->toArray();

		if ($is_spec_price) {
			if ($this->shopConfigRepository->getShopConfigByCode('add_shop_price') == 1) {
				$final_price = $goods['shop_price'];
				$final_price += $spec_price;
			}
		}

		if ($this->shopConfigRepository->getShopConfigByCode('add_shop_price') == 0) {
			$final_price = $spec_price;
		}

		return $final_price;
	}

	public function bargainTargetPrice($bargain_id = 0, $goods_id = 0, $attr_id = array(), $warehouse_id = 0, $area_id = 0, $model_attr = 0)
	{
		if (empty($attr_id)) {
			$attr_id = 0;
		}
		else {
			if (is_string($attr_id)) {
				$attr_arr = explode(',', $attr_id);
			}
			else {
				$attr_arr = $attr_id;
			}

			foreach ($attr_arr as $key => $val) {
				$attr_type = $this->goodsRepository->getGoodsAttrId($val);
				if (($attr_type == 0 || $attr_type == 2) && $attr_arr[$key]) {
					unset($attr_arr[$key]);
				}
			}

			$attr_id = implode('|', $attr_arr);
		}

		if ($this->shopConfigRepository->getShopConfigByCode('goods_attr_price') == 1) {
			if ($model_attr == 1) {
				$product_price = \App\Models\ProductsWarehouse::select('product_id', 'product_price', 'product_promote_price', 'product_market_price')->where('goods_id', $goods_id)->where('goods_attr', $attr_id)->where('warehouse_id', $warehouse_id)->first()->toArray();
			}
			else if ($model_attr == 2) {
				$product_price = \App\Models\ProductsArea::select('product_id', 'product_price', 'product_promote_price', 'product_market_price')->where('goods_id', $goods_id)->where('goods_attr', $attr_id)->where('area_id', $area_id)->first()->toArray();
			}
			else {
				$product_price = \App\Models\Products::select('product_id', 'product_price', 'product_promote_price', 'product_market_price')->where('goods_id', $goods_id)->where('goods_attr', $attr_id)->first()->toArray();
			}

			if ($product_price['product_id']) {
				$res = \App\Models\ActivityGoodsAttr::select('target_price')->where('bargain_id', $bargain_id)->where('goods_id', $goods_id)->where('product_id', $product_price['product_id'])->first()->toArray();
			}

			if ($res === null) {
				return array();
			}

			return $res['target_price'];
		}
	}

	public function addBargain($params)
	{
		$add = \App\Models\BargainStatisticsLog::insertGetId($params);

		if ($add) {
			$result['msg'] = '参与成功！感谢您的参与，祝您购物愉快';
			$result['bs_id'] = $add;
			$result['error'] = 2;
			return $result;
		}
	}

	public function updateBargain($bargain_id = 0, $total_num = 0)
	{
		$total_num = $total_num + 1;
		\App\Models\BargainGoods::where('id', $bargain_id)->update(array('total_num' => $total_num));
	}

	public function addBargainStatistics($params)
	{
		return $add = \App\Models\BargainStatistics::insertGetId($params);
	}

	public function updateBargainStatistics($bs_id = 0, $count_num = 0, $final_price = 0)
	{
		\App\Models\BargainStatisticsLog::where('id', $bs_id)->update(array('count_num' => $count_num, 'final_price' => $final_price));
	}

	public function updateStatus($bs_id = 0)
	{
		\App\Models\BargainStatisticsLog::where('id', $bs_id)->update(array('status' => 1));
	}

	public function myBargain($user_id = 0, $page = 1, $size = 10)
	{
		$begin = ($page - 1) * $size;
		$goods = \App\Models\BargainStatisticsLog::from('bargain_statistics_log as bsl')->select('bg.id', 'bg.target_price', 'bg.total_num', 'g.goods_id', 'g.goods_name', 'g.shop_price', 'g.goods_thumb', 'g.goods_img')->leftjoin('bargain_goods as bg', 'bsl.bargain_id', '=', 'bg.id')->leftjoin('goods as g', 'bg.goods_id', '=', 'g.goods_id')->offset($begin)->where('bsl.user_id', $user_id)->orderby('bsl.add_time', 'desc')->limit($size)->get()->toArray();
		return $goods;
	}

	public function copyArrayColumn($input, $columnKey, $indexKey = NULL)
	{
		$columnKeyIsNumber = is_numeric($columnKey) ? true : false;
		$indexKeyIsNull = is_null($indexKey) ? true : false;
		$indexKeyIsNumber = is_numeric($indexKey) ? true : false;
		$result = array();

		foreach ((array) $input as $key => $row) {
			if ($columnKeyIsNumber) {
				$tmp = array_slice($row, $columnKey, 1);
				$tmp = is_array($tmp) && !empty($tmp) ? current($tmp) : null;
			}
			else {
				$tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
			}

			if (!$indexKeyIsNull) {
				if ($indexKeyIsNumber) {
					$key = array_slice($row, $indexKey, 1);
					$key = is_array($key) && !empty($key) ? current($key) : null;
					$key = is_null($key) ? 0 : $key;
				}
				else {
					$key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
				}
			}

			$result[$key] = $tmp;
		}

		return $result;
	}
}


?>
