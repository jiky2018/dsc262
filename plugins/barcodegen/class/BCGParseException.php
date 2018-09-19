<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class BCGParseException extends Exception
{
	protected $barcode;

	public function __construct($barcode, $message)
	{
		$this->barcode = $barcode;
		parent::__construct($message, 10000);
	}
}

?>
