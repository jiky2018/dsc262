<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class zto
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
		$str = '<form style="margin:0px" methods="post" ' . 'action="http://www.zto.cn/bill.asp" name="queryForm_' . $invoice_sn . '" target="_blank">' . '<input type="hidden" name="ID" value="' . str_replace('<br>', "\n", $invoice_sn) . '" />' . '<a href="javascript:document.forms[\'queryForm_' . $invoice_sn . '\'].submit();">' . $invoice_sn . '</a>' . '<input type="hidden" name="imageField.x" value="26" />' . '<input type="hidden" name="imageField.x" value="43" />' . '</form>';
		return $str;
	}

	public function calculate_insure($goods_amount, $insure)
	{
		if (10000 < $goods_amount) {
			$goods_amount = 10000;
		}

		$fee = $goods_amount * $insure;

		if ($fee < 100) {
			$fee = 100;
		}

		return $fee;
	}
}

$shipping_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/shipping/zto.php';

if (file_exists($shipping_lang)) {
	global $_LANG;
	include_once $shipping_lang;
}

if (isset($set_modules) && $set_modules == true) {
	include_once ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/' . ADMIN_PATH . '/shipping.php';
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['code'] = 'zto';
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['desc'] = 'zto_desc';
	$modules[$i]['insure'] = '2%';
	$modules[$i]['cod'] = false;
	$modules[$i]['author'] = '蓝色黯然';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	$modules[$i]['configure'] = array(
	array('name' => 'item_fee', 'value' => 15),
	array('name' => 'base_fee', 'value' => 10),
	array('name' => 'step_fee', 'value' => 5)
	);
	$modules[$i]['print_model'] = 2;
	$modules[$i]['print_bg'] = '/images/receipt/dly_zto.jpg';
	$modules[$i]['config_lable'] = 't_shop_province,' . $_LANG['lable_box']['shop_province'] . ',116,30,296.55,117.2,b_shop_province||,||t_customer_province,' . $_LANG['lable_box']['customer_province'] . ',114,32,649.95,114.3,b_customer_province||,||t_shop_address,' . $_LANG['lable_box']['shop_address'] . ',260,57,151.75,152.05,b_shop_address||,||t_shop_name,' . $_LANG['lable_box']['shop_name'] . ',259,28,152.65,212.4,b_shop_name||,||t_shop_tel,' . $_LANG['lable_box']['shop_tel'] . ',131,37,138.65,246.5,b_shop_tel||,||t_customer_post,' . $_LANG['lable_box']['customer_post'] . ',104,39,659.2,242.2,b_customer_post||,||t_customer_tel,' . $_LANG['lable_box']['customer_tel'] . ',158,22,461.9,241.9,b_customer_tel||,||t_customer_mobel,' . $_LANG['lable_box']['customer_mobel'] . ',159,21,463.25,265.4,b_customer_mobel||,||t_customer_name,' . $_LANG['lable_box']['customer_name'] . ',109,32,498.9,115.8,b_customer_name||,||t_customer_address,' . $_LANG['lable_box']['customer_address'] . ',264,58,499.6,150.1,b_customer_address||,||t_months,' . $_LANG['lable_box']['months'] . ',35,23,135.85,392.8,b_months||,||t_day,' . $_LANG['lable_box']['day'] . ',24,23,180.1,392.8,b_day||,||';
	$modules[$i]['kdniao_print'] = true;
	$modules[$i]['kdniao_account'] = 2;
	$modules[$i]['kdniao_code'] = 'ZTO';
	$modules[$i]['kdniao_width'] = 100;
	$modules[$i]['kdniao_height'] = 180;
	return NULL;
}

?>
