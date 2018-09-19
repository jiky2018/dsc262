<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Goods;

class GoodsAttrRepository
{
	private $shopConfigRepository;

	public function __construct(\App\Repositories\ShopConfig\ShopConfigRepository $shopConfigRepository)
	{
		$this->shopConfigRepository = $shopConfigRepository;
	}

	public function goodsAttr($goods_id)
	{
		$res = \App\Models\GoodsAttr::from('goods_attr as g')->select('*')->join('attribute as a', 'a.attr_id', '=', 'g.attr_id')->where('g.goods_id', $goods_id)->orderby('a.sort_order')->orderby('g.attr_sort', 'ASC')->get();

		if ($res == null) {
			return array();
		}

		return $res->toArray();
	}

	public function attrGroup($goods_id)
	{
		$model = \App\Models\GoodsAttr::from('goods_type as gt')->select('attr_group')->join('goods as g', 'gt.cat_id', '=', 'g.goods_type')->where('g.goods_id', $goods_id)->first();

		if ($model == null) {
			return array();
		}

		return $model->attr_group;
	}

	public function getAttrNameById($attrId)
	{
		$goodsAttr = \App\Models\GoodsAttr::select('attribute.attr_name', 'goods_attr.attr_value');

		if (is_array($attrId)) {
			$goodsAttr = $goodsAttr->wherein('goods_attr_id', $attrId)->leftjoin('attribute', 'attribute.attr_id', '=', 'goods_attr.attr_id')->get();
		}
		else if (is_int($attrId)) {
			$goodsAttr = $goodsAttr->where('goods_attr_id', $attrId)->leftjoin('attribute', 'attribute.attr_id', '=', 'goods_attr.attr_id')->first();
		}

		if ($goodsAttr == null) {
			return array();
		}

		return $goodsAttr->toArray();
	}
}


?>
