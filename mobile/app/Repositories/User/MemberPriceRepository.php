<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\User;

class MemberPriceRepository
{
	public function getMemberPriceByUid($rank, $goods_id)
	{
		$price = \App\Models\MemberPrice::where('user_rank', $rank)->where('goods_id', $goods_id)->pluck('user_price')->toArray();

		if (!empty($price)) {
			$price = $price[0];
		}

		return $price;
	}
}


?>
