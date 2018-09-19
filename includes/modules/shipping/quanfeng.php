<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class quanfeng
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
		$str = '<form style="margin:0px" methods="post" ' . 'action="http://www.qfkd.com.cn/billSearch.aspx" name="queryForm_' . $invoice_sn . '" target="_blank">' . '<input type="hidden" name="billcode" value="' . $invoice_sn . '" />' . '<a href="javascript:document.forms[\'queryForm_' . $invoice_sn . '\'].submit();">' . $invoice_sn . '</a>' . '</form>';
		return $str;
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$shipping_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/shipping/quanfeng.php';

if (file_exists($shipping_lang)) {
	global $_LANG;
	include_once $shipping_lang;
}

if (isset($set_modules) && $set_modules == true) {
	include_once ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/' . ADMIN_PATH . '/shipping.php';
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['code'] = 'quanfeng';
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['desc'] = 'quanfeng_desc';
	$modules[$i]['insure'] = false;
	$modules[$i]['cod'] = false;
	$modules[$i]['author'] = 'DSC TEAM';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	$modules[$i]['configure'] = array(
	array('name' => 'item_fee', 'value' => 10),
	array('name' => 'base_fee', 'value' => 5),
	array('name' => 'step_fee', 'value' => 5)
	);
	$modules[$i]['print_model'] = 2;
	$modules[$i]['print_bg'] = '/images/receipt/dly_quanfeng.jpg';
	$modules[$i]['config_lable'] = 't_shop_province,' . $_LANG['lable_box']['shop_province'] . ',132,24,279.6,105.7,b_shop_province||,||t_shop_name,' . $_LANG['lable_box']['shop_name'] . ',268,29,142.95,133.85,b_shop_name||,||t_shop_address,' . $_LANG['lable_box']['shop_address'] . ',346,40,67.3,199.95,b_shop_address||,||t_shop_city,' . $_LANG['lable_box']['shop_city'] . ',64,35,223.8,163.95,b_shop_city||,||t_shop_district,' . $_LANG['lable_box']['shop_district'] . ',56,35,314.9,164.25,b_shop_district||,||t_pigeon,' . $_LANG['lable_box']['pigeon'] . ',21,21,143.1,263.2,b_pigeon||,||t_customer_name,' . $_LANG['lable_box']['customer_name'] . ',89,25,488.65,121.05,b_customer_name||,||t_customer_tel,' . $_LANG['lable_box']['customer_tel'] . ',136,21,656,110.6,b_customer_tel||,||t_customer_mobel,' . $_LANG['lable_box']['customer_mobel'] . ',137,21,655.6,132.8,b_customer_mobel||,||t_customer_province,' . $_LANG['lable_box']['customer_province'] . ',115,24,480.2,173.5,b_customer_province||,||t_customer_city,' . $_LANG['lable_box']['customer_city'] . ',60,27,609.3,172.5,b_customer_city||,||t_customer_district,' . $_LANG['lable_box']['customer_district'] . ',58,28,696.8,173.25,b_customer_district||,||t_customer_post,' . $_LANG['lable_box']['customer_post'] . ',93,21,701.1,240.25,b_customer_post||,||';
	$modules[$i]['kdniao_print'] = true;
	$modules[$i]['kdniao_account'] = 2;
	$modules[$i]['kdniao_code'] = 'QFKD';
	$modules[$i]['kdniao_width'] = 100;
	$modules[$i]['kdniao_height'] = 180;
	return NULL;
}

?>
