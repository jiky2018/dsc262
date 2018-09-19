<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class BCGDrawException extends Exception
{
	public function __construct($message)
	{
		parent::__construct($message, 30000);
	}
}

?>
