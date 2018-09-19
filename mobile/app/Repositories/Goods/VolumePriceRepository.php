<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Goods;

class VolumePriceRepository
{
	public function allVolumes($goods_id, $price_type)
	{
		$res = \App\Models\VolumePrice::where('goods_id', $goods_id)->where('price_type', $price_type)->orderBy('volume_number')->get()->toArray();
		return $res;
	}
}


?>
