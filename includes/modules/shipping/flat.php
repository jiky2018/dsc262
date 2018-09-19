<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class flat
{
	/**
     * 配置信息
     */
	public $configure;

	public function __construct($cfg = array())
	{
		foreach ($cfg as $key => $val) {
			$this->configure[$val['name']] = $val['value'];
		}
	}

	public function calculate($goods_weight, $goods_amount)
	{
		if (0 < $this->configure['free_money'] && $this->configure['free_money'] <= $goods_amount) {
			return 0;
		}
		else {
			return isset($this->configure['base_fee']) ? $this->configure['base_fee'] : 0;
		}
	}

	public function query($invoice_sn)
	{
		return $invoice_sn;
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$shipping_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/shipping/flat.php';

if (file_exists($shipping_lang)) {
	global $_LANG;
	include_once $shipping_lang;
}

if (isset($set_modules) && $set_modules == true) {
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['desc'] = 'flat_desc';
	$modules[$i]['cod'] = false;
	$modules[$i]['author'] = 'ECMOBAN TEAM';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	$modules[$i]['configure'] = array(
	array('name' => 'base_fee', 'value' => 10)
	);
	$modules[$i]['print_model'] = 2;
	$modules[$i]['print_bg'] = '';
	$modules[$i]['config_lable'] = '';
	$modules[$i]['kdniao_print'] = false;
	return NULL;
}

?>
