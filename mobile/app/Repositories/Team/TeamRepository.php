<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Team;

class TeamRepository
{
	protected $goods;
	private $field;
	private $authService;
	private $goodsAttrRepository;
	private $shopConfigRepository;
	private $goodsRepository;
	private $userRepository;

	public function __construct(\App\Services\AuthService $authService, \App\Repositories\Goods\GoodsAttrRepository $goodsAttrRepository, \App\Repositories\ShopConfig\ShopConfigRepository $shopConfigRepository, \App\Repositories\Goods\GoodsRepository $goodsRepository, \App\Repositories\User\UserRepository $userRepository)
	{
		$this->setField();
		$this->authService = $authService;
		$this->goodsAttrRepository = $goodsAttrRepository;
		$this->shopConfigRepository = $shopConfigRepository;
		$this->goodsRepository = $goodsRepository;
		$this->userRepository = $userRepository;
	}

	public function setField()
	{
		$this->field = array('category' => 'cat_id');
	}

	public function getField($field)
	{
		return $this->field[$field];
	}

	public function teamPositions($position_id = 0, $num = 3)
	{
		$time = gmtime();
		$res = \App\Models\TouchAd::select('ad_id', 'touch_ad.position_id', 'media_type', 'ad_link', 'ad_code', 'ad_name')->with(array('position'))->join('touch_ad_position', 'touch_ad_position.position_id', '=', 'touch_ad.position_id')->where('start_time', '<=', $time)->where('end_time', '>=', $time)->where('touch_ad_position.position_id', $position_id)->where('enabled', 1)->limit($num)->get()->toArray();
		$res = array_map(function($v) {
			if (!empty($v['position'])) {
				$temp = array_merge($v, $v['position']);
				unset($temp['position']);
				return $temp;
			}
		}, $res);
		return $res;
	}

	public function categoriesAdsense($tc_id = 0, $type = 'banner', $num = 3)
	{
		$time = gmtime();
		$res = \App\Models\TouchAd::select('ad_id', 'touch_ad.position_id', 'media_type', 'ad_link', 'ad_code', 'ad_name')->with(array('position'))->join('touch_ad_position', 'touch_ad_position.position_id', '=', 'touch_ad.position_id')->where('start_time', '<=', $time)->where('end_time', '>=', $time)->where('touch_ad_position.tc_id', $tc_id)->where('touch_ad_position.tc_type', $type)->where('touch_ad_position.ad_type', 'wxapp')->where('enabled', 1)->limit($num)->get()->toArray();
		$res = array_map(function($v) {
			if (!empty($v['position'])) {
				$temp = array_merge($v, $v['position']);
				unset($temp['position']);
				return $temp;
			}
		}, $res);
		return $res;
	}

	public function teamCategoriesList()
	{
		return \App\Models\TeamCategory::select('*')->where('parent_id', 0)->where('status', 1)->orderby('id', 'asc')->get()->toArray();
	}

	public function teamCategoriesChild($tc_id = 0)
	{
		return \App\Models\TeamCategory::select('*')->where('parent_id', $tc_id)->where('status', 1)->orderby('id', 'asc')->get()->toArray();
	}

	public function teamCategoriesInfo($tc_id = 0)
	{
		return \App\Models\TeamCategory::select('*')->where('id', $tc_id)->where('status', 1)->first()->toArray();
	}

	public function teamGoodsList($page = 1, $size = 10, $type = array())
	{
		$goods = \App\Models\TeamGoods::from('team_goods as tg')->select('g.goods_id', 'g.goods_name', 'g.shop_price', 'g.goods_number', 'g.sales_volume', 'g.goods_thumb', 'tg.id', 'tg.team_price', 'tg.team_num', 'tg.limit_num')->leftjoin('goods as g', 'g.goods_id', '=', 'tg.goods_id');

		if (!empty($type)) {
			$goods->wherein('tg.tc_id', $type);
		}

		$begin = ($page - 1) * $size;
		$list = $goods->where('tg.is_team', 1)->where('tg.is_audit', 2)->where('g.is_on_sale', 1)->where('g.is_alone_sale', 1)->where('g.is_delete', 0)->where('g.review_status', '>', 2)->offset($begin)->orderby('tg.id', 'desc')->limit($size)->get()->toArray();

		if ($list === null) {
			return array();
		}

		return $list;
	}

	public function randUserInfo($user_id = 0)
	{
		$prefix = \Illuminate\Support\Facades\Config::get('database.connections.mysql.prefix');
		$sql = 'SELECT user_name,user_id FROM ' . $prefix . 'users WHERE user_id >= ((SELECT MAX(user_id) FROM ' . $prefix . 'users)-(SELECT MIN(user_id) FROM ' . $prefix . 'users)) * RAND() + (SELECT MIN(user_id) FROM ' . $prefix . 'users) and nick_name !=\'\' LIMIT 30 ';
		$list = \Illuminate\Support\Facades\DB::select($sql);

		if ($list == null) {
			return array();
		}

		return $list;
	}

	public function categoryGoodsList($tc_id = 0, $page = 1, $size = 10, $keywords = '', $sortKey = 0, $sortVal = '')
	{
		$goods = \App\Models\TeamGoods::from('team_goods as tg')->select('g.goods_id', 'g.goods_name', 'g.shop_price', 'g.goods_number', 'g.sales_volume', 'g.goods_thumb', 'tg.id', 'tg.team_price', 'tg.team_num', 'tg.limit_num')->leftjoin('goods as g', 'g.goods_id', '=', 'tg.goods_id');

		if (!empty($keywords)) {
			$goods->where('goods_name', 'like', '%' . $keywords . '%');
		}

		$sort = array('ASC', 'DESC');

		switch ($sortKey) {
		case '0':
			$goods->orderby('g.goods_id', 'ASC');
			break;

		case '1':
			$goods->orderby('g.last_update', in_array($sortVal, $sort) ? $sortVal : 'ASC');
			break;

		case '2':
			$goods->orderby('g.sales_volume', in_array($sortVal, $sort) ? $sortVal : 'ASC');
			break;

		case '3':
			$goods->orderby('tg.team_price', in_array($sortVal, $sort) ? $sortVal : 'ASC');
			break;
		}

		$begin = ($page - 1) * $size;
		$list = $goods->where('tg.tc_id', $tc_id)->where('tg.is_team', 1)->where('tg.is_audit', 2)->where('g.is_on_sale', 1)->where('g.is_delete', 0)->offset($begin)->limit($size)->get()->toArray();

		if ($list === null) {
			return array();
		}

		return $list;
	}

	public function teamRankingList($page = 1, $size = 10, $type = 0)
	{
		$goods = \App\Models\TeamGoods::from('team_goods as tg')->select('g.goods_id', 'g.goods_name', 'g.shop_price', 'g.goods_number', 'g.sales_volume', 'g.goods_thumb', 'tg.id', 'tg.team_price', 'tg.team_num', 'tg.limit_num')->leftjoin('goods as g', 'g.goods_id', '=', 'tg.goods_id');

		switch ($type) {
		case '0':
			$goods->orderby('tg.limit_num', 'DESC');
			break;

		case '1':
			$goods->orderby('g.add_time', 'DESC');
			break;

		case '2':
			$goods->where('g.is_hot', 1);
			break;

		case '3':
			$goods->where('g.is_best', 1);
			break;
		}

		$begin = ($page - 1) * $size;
		$list = $goods->where('tg.is_team', 1)->where('tg.is_audit', 2)->where('g.is_on_sale', 1)->where('g.is_delete', 0)->offset($begin)->limit($size)->get()->toArray();

		if ($list === null) {
			return array();
		}

		return $list;
	}

	public function goodsInfo($goods_id = 0)
	{
		$res = \App\Models\TeamGoods::from('team_goods as tg')->select('tg.id', 'tg.goods_id', 'tg.team_price', 'tg.team_num', 'tg.limit_num', 'tg.astrict_num', 'tg.is_audit', 'tg.is_team', 'tg.team_desc', 'g.user_id', 'g.goods_sn', 'g.goods_name', 'g.is_real', 'g.is_shipping', 'g.is_on_sale', 'g.shop_price', 'g.market_price', 'g.goods_thumb', 'g.goods_img', 'g.goods_number', 'g.sales_volume', 'g.goods_desc', 'g.desc_mobile', 'g.goods_type', 'g.goods_brief', 'g.model_attr', 'g.review_status')->leftjoin('goods as g', 'g.goods_id', '=', 'tg.goods_id')->where('tg.goods_id', $goods_id)->where('tg.is_team', 1)->first();

		if ($res === null) {
			return array();
		}

		return $res->toArray();
	}

	public function teamNewGoods($type = 'is_new', $user_id = 0, $size = 10)
	{
		$goods = \App\Models\TeamGoods::from('team_goods as tg')->select('g.goods_id', 'g.goods_name', 'g.shop_price', 'g.goods_number', 'g.sales_volume', 'g.goods_thumb', 'tg.id', 'tg.team_price', 'tg.team_num', 'tg.limit_num')->leftjoin('goods as g', 'g.goods_id', '=', 'tg.goods_id');

		if ($type == 'is_new') {
			$goods->where('g.is_new', 1);
		}

		$list = $goods->where('tg.is_team', 1)->where('tg.is_audit', 2)->where('g.is_on_sale', 1)->where('g.is_delete', 0)->where('g.user_id', $user_id)->orderby('tg.id', 'desc')->limit($size)->get()->toArray();

		if ($list === null) {
			return array();
		}

		return $list;
	}

	public function teamIsFailure($team_id = 0)
	{
		$list = \App\Models\TeamLog::from('team_log as tl')->select('tl.team_id', 'tl.goods_id', 'tl.start_time', 'tl.status', 'tg.id', 'tg.validity_time', 'tg.team_price', 'tg.team_num', 'tg.limit_num', 'tg.astrict_num', 'tg.is_team', 'g.goods_name')->leftjoin('team_goods as tg', 'tl.t_id', '=', 'tg.id')->leftjoin('goods as g', 'tg.goods_id', '=', 'g.goods_id')->where('tl.team_id', $team_id)->first();

		if ($list === null) {
			return array();
		}

		return $list->toArray();
	}

	public function teamOrderInfo($team_id = 0)
	{
		$list = \App\Models\OrderInfo::select('order_sn', 'user_id')->where('team_id', $team_id)->where('extension_code', 'team_buy')->where('pay_status', 2)->get()->toArray();

		if ($list === null) {
			return array();
		}

		return $list;
	}

	public function teamGoodsLog($goods_id = 0, $size = 6)
	{
		$list = \App\Models\TeamLog::from('team_log as tl')->select('tl.team_id', 'tl.goods_id', 'tl.start_time', 'o.team_parent_id', 'tg.validity_time', 'tg.team_num')->leftjoin('order_info as o', 'tl.team_id', '=', 'o.team_id')->leftjoin('team_goods as tg', 'tl.t_id', '=', 'tg.id')->where('tl.goods_id', $goods_id)->where('tl.status', '<', 1)->where('tl.is_show', 1)->where('o.extension_code', 'team_buy')->where('o.team_parent_id', '>', 0)->where('o.pay_status', 2)->where('tg.is_team', 1)->orderby('o.add_time', 'desc')->limit($size)->get()->toArray();

		if ($list === null) {
			return array();
		}

		return $list;
	}

	public function surplusNum($team_id = 0)
	{
		return \App\Models\OrderInfo::select('*')->where(array(
	array('team_id', '=', $team_id),
	array('extension_code', '=', 'team_buy')
	))->orWhere(array(
	array('pay_status', '=', 2),
	array('order_status', '=', 4)
	))->count();
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

		$goods = $this->goodsInfo($goods_id);

		if ($is_spec_price) {
			if ($this->shopConfigRepository->getShopConfigByCode('add_shop_price') == 1) {
				$final_price = $goods['team_price'];
				$final_price += $spec_price;
			}
		}

		if ($this->shopConfigRepository->getShopConfigByCode('add_shop_price') == 0) {
			$final_price = $goods['team_price'];
		}

		return $final_price;
	}

	public function teamInfo($team_id = 0)
	{
		$info = \App\Models\TeamLog::from('team_log as tl')->select('tl.team_id', 'tl.start_time', 'tl.status', 'o.user_id', 'o.team_parent_id', 'g.goods_id', 'g.goods_thumb', 'g.goods_img', 'g.goods_name', 'g.goods_brief', 'tg.validity_time', 'tg.team_num', 'tg.team_price', 'tg.is_team')->leftjoin('order_info as o', 'tl.team_id', '=', 'o.team_id')->leftjoin('goods as g', 'tl.goods_id', '=', 'g.goods_id')->leftjoin('team_goods as tg', 'tl.t_id', '=', 'tg.id')->where('tl.team_id', $team_id)->where('o.extension_code', 'team_buy')->where('o.team_parent_id', '>', 0)->first()->toArray();
		return $info;
	}

	public function orderInfo($team_id = 0, $user_id = 0)
	{
		return \App\Models\OrderInfo::select('order_id', 'order_status', 'pay_status')->where('team_id', $team_id)->where('user_id', $user_id)->first()->toArray();
	}

	public function teamUserList($team_id = 0, $page = 1, $size = 5)
	{
		$begin = ($page - 1) * $size;
		$list = \App\Models\OrderInfo::from('order_info as o')->select('o.add_time', 'o.team_id', 'o.user_id', 'o.team_parent_id', 'o.team_user_id')->leftjoin('users as u', 'o.user_id', '=', 'u.user_id')->where(array(
	array('o.team_id', '=', $team_id),
	array('o.extension_code', '=', 'team_buy')
	))->orWhere(array(
	array('o.pay_status', '=', 2),
	array('o.order_status', '=', 4)
	))->orderby('o.add_time', 'asc')->offset($begin)->limit($size)->get()->toArray();
		return $list;
	}

	public function teamJoin($user_id, $team_id = 0)
	{
		return \App\Models\OrderInfo::select('*')->where('team_id', $team_id)->where('user_id', $user_id)->where('extension_code', 'team_buy')->count();
	}

	public function teamUserOrder($user_id, $type = 0, $page = 1, $size = 10)
	{
		$start = ($page - 1) * $size;

		switch ($type) {
		case '0':
			$where = ' and t.status < 1 and \'' . gmtime() . '\'< (t.start_time+(tg.validity_time*3600)) and o.order_status != 2 and tg.is_team = 1 ';
			break;

		case '1':
			$where = ' and t.status = 1 ';
			break;

		case '2':
			$where = ' and t.status < 1 and (\'' . gmtime() . '\' > (t.start_time+(tg.validity_time*3600)) || tg.is_team != 1)';
			break;
		}

		$prefix = \Illuminate\Support\Facades\Config::get('database.connections.mysql.prefix');
		$sql = 'select o.order_id,o.user_id,o.order_status,o.pay_status,t.goods_id,t.team_id,t.start_time,t.status,g.goods_name,g.goods_thumb,g.shop_price,tg.validity_time,tg.id,tg.team_num,tg.team_price,tg.limit_num from ' . $prefix . 'order_info as o left join ' . $prefix . 'team_log as t on o.team_id = t.team_id left join ' . $prefix . 'team_goods as tg on t.t_id = tg.id left join ' . $prefix . 'goods as g on g.goods_id = tg.goods_id' . (' where o.user_id = ' . $user_id . ' and o.extension_code =\'team_buy\'  and t.is_show = 1 ' . $where . '  ORDER BY o.add_time DESC limit ' . $start . ',' . $size);
		$list = \Illuminate\Support\Facades\DB::select($sql);
		return $list;
	}

	public function addTeamLog($params)
	{
		$add = \App\Models\TeamLog::insertGetId($params);

		if ($add) {
			return $add;
		}
	}

	public function updateTeamLogStatua($team_id)
	{
		\App\Models\TeamLog::where('team_id', $team_id)->update(array('status' => 1));
	}

	public function updateTeamLimitNum($id = 0, $goods_id = 0, $limit_num = 0)
	{
		\App\Models\TeamGoods::where('id', $id)->where('goods_id', $goods_id)->update(array('limit_num' => $limit_num));
	}
}


?>
