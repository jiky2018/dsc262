<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Store;

class StoreRepository
{
	public function all($uid)
	{
		$store = \App\Models\MerchantsShopInformation::select('shop_id', 'user_id', 'rz_shopName', 'sort_order')->with(array('sellershopinfo' => function($query) {
			$query->select('logo_thumb', 'ru_id');
		}))->where('shop_close', 1)->where('is_street', 1)->orderBy('sort_order', 'ASC')->get()->toArray();

		foreach ($store as $key => $val) {
			$store[$key]['collect'] = $this->collect($val['user_id'], $uid);
			$store[$key]['sellershopinfo']['logo_thumb'] = get_image_path(str_replace('../', '', $val['sellershopinfo']['logo_thumb']));
			$store[$key]['goods'] = \App\Models\Goods::select('goods_id', 'goods_sn', 'brand_id', 'goods_name', 'goods_thumb', 'shop_price', 'promote_price', 'cat_id', 'market_price', 'goods_number')->where('user_id', $val['user_id'])->where('is_on_sale', '1')->where('is_alone_sale', '1')->limit(4)->orderBy('sort_order', 'ASC')->get()->toArray();

			foreach ($store[$key]['goods'] as $k => $v) {
				$store[$key]['goods'][$k]['goods_thumb'] = get_image_path($v['goods_thumb']);
			}
		}

		return $store;
	}

	public function detail($id)
	{
		$detail = \App\Models\MerchantsShopInformation::select('*')->with(array('sellershopinfo' => function($query) {
			$query->select('*');
		}))->where('user_id', $id)->get()->toArray();
		return $detail;
	}

	public function goods($id, $page, $per_page, $cate_key, $sort, $order, $cat_id)
	{
		if ($page == 1) {
			$current = 0;
		}
		else {
			$current = ($page - 1) * $per_page;
		}

		if (0 < $cat_id) {
			$goods = \App\Models\Goods::select('goods_id', 'goods_sn', 'brand_id', 'goods_name', 'goods_thumb', 'shop_price', 'promote_price', 'cat_id', 'market_price', 'goods_number')->where('user_id', $id)->where('is_on_sale', '1')->where('is_alone_sale', '1')->where($cate_key, '1')->where('user_cat', $cat_id)->offset($current)->limit($per_page)->orderBy($sort, $order)->get()->toArray();
		}
		else {
			$goods = \App\Models\Goods::select('goods_id', 'goods_sn', 'brand_id', 'goods_name', 'goods_thumb', 'shop_price', 'promote_price', 'cat_id', 'market_price', 'goods_number')->where('user_id', $id)->where('is_on_sale', '1')->where('is_alone_sale', '1')->where($cate_key, '1')->offset($current)->limit($per_page)->orderBy($sort, $order)->get()->toArray();
		}

		return $goods;
	}

	public function store_category($id)
	{
		$res = \App\Models\MerchantsCategory::select('cat_id', 'cat_name')->where('user_id', $id)->where('is_show', '1')->where('parent_id', '0')->get()->toArray();
		$arr = array();

		foreach ($res as $key => $row) {
			$arr[$key] = $row;
			$arr[$key]['opennew'] = 0;
			$arr[$key]['child'] = $this->store_category_child($row['cat_id'], $id);
		}

		$arr = array_merge($arr);
		return $arr;
	}

	public function store_category_child($parent_id, $ru_id)
	{
		$res = \App\Models\MerchantsCategory::select('cat_id', 'cat_name')->where('parent_id', $parent_id)->where('user_id', $ru_id)->get()->toArray();
		$arr = array();

		foreach ($res as $key => $row) {
			$arr[$key]['cat_id'] = $row['cat_id'];
			$arr[$key]['cat_name'] = $row['cat_name'];
			$arr[$key]['url'] = url('merchants_store', array('cid' => $row['cat_id'], 'urid' => $ru_id), $row['cat_name']);
			$arr[$key]['child'] = $this->store_category_child($row['cat_id'], $row['cat_id']);
		}

		return $arr;
	}

	public function collect($id, $uid)
	{
		$coll = array();
		$coll['ect'] = \App\Models\CollectStore::where('ru_id', $id)->where('user_id', $uid)->count();
		$coll['num'] = \App\Models\CollectStore::where('ru_id', $id)->count();
		return $coll;
	}

	public function collnum($id)
	{
		return \App\Models\CollectStore::where('ru_id', $id)->count();
	}

	public function delete($id)
	{
	}

	public function find($id, $columns = array('*'))
	{
	}

	public function findBy($field, $value, $columns = array('*'))
	{
	}
}


?>
