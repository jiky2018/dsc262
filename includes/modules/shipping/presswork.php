<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class presswork
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
			$fee = $goods_weight * 4 + 3.3999999999999999;

			if (0.10000000000000001 < $goods_weight) {
				$fee += ceil(($goods_weight - 0.10000000000000001) / 0.10000000000000001) * 0.40000000000000002;
			}

			return $fee;
		}
	}

	public function query($invoice_sn)
	{
		return $invoice_sn;
	}

	public function calculate_insure($total_price, $insure_rate)
	{
		$total_price = ceil($total_price);
		$price = $total_price * $insure_rate;

		if ($price < 1) {
			$price = 1;
		}

		return ceil($price);
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$shipping_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/shipping/presswork.php';

if (file_exists($shipping_lang)) {
	global $_LANG;
	include_once $shipping_lang;
}

if (isset($set_modules) && $set_modules == true) {
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['desc'] = 'presswork_desc';
	$modules[$i]['insure'] = '1%';
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
