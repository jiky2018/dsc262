<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Notifications;

abstract class Notification
{
	protected $via = array();

	public function send()
	{
		foreach ($this->via as $via) {
		}
	}
}


?>
