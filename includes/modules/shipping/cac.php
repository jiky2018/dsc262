<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class cac
{
	/**
     * 配置信息
     */
	public $configure;

	public function __construct($cfg = array())
	{
	}

	public function calculate($goods_weight, $goods_amount)
	{
		return 0;
	}

	public function query($invoice_sn)
	{
		return $invoice_sn;
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$shipping_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/shipping/cac.php';

if (file_exists($shipping_lang)) {
	global $_LANG;
	include_once $shipping_lang;
}

if (isset($set_modules) && $set_modules == true) {
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['code'] = 'cac';
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['desc'] = 'cac_desc';
	$modules[$i]['insure'] = false;
	$modules[$i]['cod'] = false;
	$modules[$i]['author'] = 'ECMOBAN TEAM';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	$modules[$i]['configure'] = array();
	$modules[$i]['print_model'] = 2;
	$modules[$i]['print_bg'] = '';
	$modules[$i]['config_lable'] = '';
	$modules[$i]['kdniao_print'] = false;
	return NULL;
}

?>
