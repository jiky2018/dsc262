<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class sf_express
{
	/**
     * 配置信息参数
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
			@$fee = $this->configure['base_fee'];
			$this->configure['fee_compute_mode'] = !empty($this->configure['fee_compute_mode']) ? $this->configure['fee_compute_mode'] : 'by_weight';

			if ($this->configure['fee_compute_mode'] == 'by_number') {
				$fee = $goods_number * $this->configure['item_fee'];
			}
			else if (1 < $goods_weight) {
				$fee += ceil($goods_weight - 1) * $this->configure['step_fee'];
			}

			return $fee;
		}
	}

	public function query($invoice_sn)
	{
		$form_str = '<a href="http://www.sf-express.com/tabid/68/Default.aspx" target="_blank">' . $invoice_sn . '</a>';
		return $form_str;
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$shipping_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/shipping/sf_express.php';

if (file_exists($shipping_lang)) {
	global $_LANG;
	include_once $shipping_lang;
}

if (isset($set_modules) && $set_modules == true) {
	include_once ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/' . ADMIN_PATH . '/shipping.php';
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['desc'] = 'sf_express_desc';
	$modules[$i]['cod'] = false;
	$modules[$i]['author'] = 'ECMOBAN TEAM';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	$modules[$i]['configure'] = array(
	array('name' => 'item_fee', 'value' => 20),
	array('name' => 'base_fee', 'value' => 15),
	array('name' => 'step_fee', 'value' => 2)
	);
	$modules[$i]['print_model'] = 2;
	$modules[$i]['print_bg'] = '/images/receipt/dly_sf_express.jpg';
	$modules[$i]['config_lable'] = 't_shop_name,' . $_LANG['lable_box']['shop_name'] . ',150,29,112,137,b_shop_name||,||t_shop_address,' . $_LANG['lable_box']['shop_address'] . ',268,55,105,168,b_shop_address||,||t_shop_tel,' . $_LANG['lable_box']['shop_tel'] . ',55,25,177,224,b_shop_tel||,||t_customer_name,' . $_LANG['lable_box']['customer_name'] . ',78,23,299,265,b_customer_name||,||t_customer_address,' . $_LANG['lable_box']['customer_address'] . ',271,94,104,293,b_customer_address||,||';
	$modules[$i]['kdniao_print'] = true;
	$modules[$i]['kdniao_account'] = 0;
	$modules[$i]['kdniao_code'] = 'SF';
	$modules[$i]['kdniao_width'] = 100;
	$modules[$i]['kdniao_height'] = 150;
	return NULL;
}

?>
