<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Notifications;

class DrpAccountChecked
{
	public function setVia($via)
	{
		if (!is_array($via)) {
			$this->via = array($via);
		}

		return $this;
	}
}


?>
