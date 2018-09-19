<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class post_mail
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
			$fee = $this->configure['base_fee'] + $this->configure['pack_fee'];
			$this->configure['fee_compute_mode'] = !empty($this->configure['fee_compute_mode']) ? $this->configure['fee_compute_mode'] : 'by_weight';

			if ($this->configure['fee_compute_mode'] == 'by_number') {
				$fee = $goods_number * ($this->configure['item_fee'] + $this->configure['pack_fee']);
			}
			else if (5 < $goods_weight) {
				$fee += 4 * $this->configure['step_fee'];
				$fee += ceil($goods_weight - 5) * $this->configure['step_fee1'];
			}
			else if (1 < $goods_weight) {
				$fee += ceil($goods_weight - 1) * $this->configure['step_fee'];
			}

			return $fee;
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

$shipping_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/shipping/post_mail.php';

if (file_exists($shipping_lang)) {
	global $_LANG;
	include_once $shipping_lang;
}

if (isset($set_modules) && $set_modules == true) {
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['desc'] = 'post_mail_desc';
	$modules[$i]['cod'] = false;
	$modules[$i]['author'] = 'ECMOBAN TEAM';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	$modules[$i]['configure'] = array(
	array('name' => 'item_fee', 'value' => 4),
	array('name' => 'base_fee', 'value' => 3.5),
	array('name' => 'step_fee', 'value' => 2),
	array('name' => 'step_fee1', 'value' => 2.5),
	array('name' => 'pack_fee', 'value' => 0)
	);
	$modules[$i]['print_model'] = 2;
	$modules[$i]['print_bg'] = '';
	$modules[$i]['config_lable'] = '';
	$modules[$i]['kdniao_print'] = false;
	return NULL;
}

?>
