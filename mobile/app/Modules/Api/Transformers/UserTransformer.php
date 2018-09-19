<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Api\Transformers;

class UserTransformer
{
	public function transform(\App\Models\Users $user)
	{
		return array('id' => $user->user_id, 'name' => $user->user_name);
	}
}


?>
