<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Bonus;

class BonusTypeRepository
{
	public function bonusInfo($bonus_id, $bonus_sn = '')
	{
		return self::join('user_bonus', 'bonus_type.type_id', '=', 'user_bonus.bonus_type_id')->where('bonus_id', $bonus_id)->first();
	}
}


?>
