<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class post_express
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

	public function calculate($goods_weight, $goods_amount, $goods_number)
	{
		if (0 < $this->configure['free_money'] && $this->configure['free_money'] <= $goods_amount) {
			return 0;
		}
		else {
			$fee = $this->configure['base_fee'];
			$this->configure['fee_compute_mode'] = !empty($this->configure['fee_compute_mode']) ? $this->configure['fee_compute_mode'] : 'by_weight';

			if ($this->configure['fee_compute_mode'] == 'by_number') {
				$fee = $goods_number * $this->configure['item_fee'];
			}
			else if (5 < $goods_weight) {
				$fee += 8 * $this->configure['step_fee'];
				$fee += ceil(($goods_weight - 5) / 0.5) * $this->configure['step_fee1'];
			}
			else if (1 < $goods_weight) {
				$fee += ceil(($goods_weight - 1) / 0.5) * $this->configure['step_fee'];
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

$shipping_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/shipping/post_express.php';

if (file_exists($shipping_lang)) {
	global $_LANG;
	include_once $shipping_lang;
}

if (isset($set_modules) && $set_modules == true) {
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['desc'] = 'post_express_desc';
	$modules[$i]['insure'] = '1%';
	$modules[$i]['cod'] = false;
	$modules[$i]['author'] = 'ECMOBAN TEAM';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	$modules[$i]['configure'] = array(
	array('name' => 'item_fee', 'value' => 5),
	array('name' => 'base_fee', 'value' => 5),
	array('name' => 'step_fee', 'value' => 2),
	array('name' => 'step_fee1', 'value' => 1)
	);
	$modules[$i]['print_model'] = 2;
	$modules[$i]['print_bg'] = '';
	$modules[$i]['config_lable'] = '';
	$modules[$i]['kdniao_print'] = true;
	$modules[$i]['kdniao_account'] = 0;
	$modules[$i]['kdniao_code'] = 'YZPY';
	$modules[$i]['kdniao_width'] = 100;
	$modules[$i]['kdniao_height'] = 180;
	return NULL;
}

?>
