<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class onlinepay
{
	public function __construct()
	{
		$this->onlinepay();
	}

	public function onlinepay()
	{
	}

	public function get_code()
	{
		return '';
	}

	public function response()
	{
		return NULL;
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/onlinepay.php';

if (file_exists($payment_lang)) {
	global $_LANG;
	include_once $payment_lang;
}

if (isset($set_modules) && $set_modules == true) {
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['desc'] = 'onlinepay_desc';
	$modules[$i]['is_cod'] = '0';
	$modules[$i]['is_online'] = '0';
	$modules[$i]['author'] = '模板堂';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['config'] = array();
	return NULL;
}

?>
