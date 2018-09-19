<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class cappay
{
	public function __construct()
	{
	}

	public function get_code($order, $payment)
	{
		$v_rcvname = trim($payment['cappay_account']);
		$m_orderid = $order['log_id'];
		$v_amount = $order['order_amount'];
		$v_moneytype = trim($payment['cappay_currency']);
		$v_url = return_url(basename(__FILE__, '.php'));
		$m_ocomment = '欢迎使用首信易支付';
		$v_ymd = local_date('Ymd', gmtime());
		$MD5Key = $payment['cappay_key'];
		$v_oid = $v_ymd . '-' . $v_rcvname . '-' . $m_orderid;
		$sourcedata = $v_moneytype . $v_ymd . $v_amount . $v_rcvname . $v_oid . $v_rcvname . $v_url;
		$result = $this->hmac_md5($MD5Key, $sourcedata);
		$def_url = '<form method=post action="http://pay.beijing.com.cn/prs/user_payment.checkit" target="_blank">';
		$def_url .= '<input type= \'hidden\' name = \'v_mid\'     value= \'' . $v_rcvname . '\'>';
		$def_url .= '<input type= \'hidden\' name = \'v_oid\'     value= \'' . $v_oid . '\'>';
		$def_url .= '<input type= \'hidden\' name = \'v_rcvname\' value= \'' . $v_rcvname . '\'>';
		$def_url .= '<input type= \'hidden\' name = \'v_rcvaddr\' value= \'' . $v_rcvname . '\'>';
		$def_url .= '<input type= \'hidden\' name = \'v_rcvtel\'  value= \'' . $v_rcvname . '\'>';
		$def_url .= '<input type= \'hidden\' name = \'v_rcvpost\'  value= \'' . $v_rcvname . '\'>';
		$def_url .= '<input type= \'hidden\' name = \'v_amount\'   value= \'' . $v_amount . '\'>';
		$def_url .= '<input type= \'hidden\' name = \'v_ymd\'      value= \'' . $v_ymd . '\'>';
		$def_url .= '<input type= \'hidden\' name = \'v_orderstatus\' value =\'0\'>';
		$def_url .= '<input type= \'hidden\' name = \'v_ordername\'   value =\'' . $v_rcvname . '\'>';
		$def_url .= '<input type= \'hidden\' name = \'v_moneytype\'   value =\'' . $v_moneytype . '\'>';
		$def_url .= '<input type= \'hidden\' name=\'v_url\' value=\'' . $v_url . '\'>';
		$def_url .= '<input type=\'hidden\' name=\'v_md5info\' value=' . $result . '>';
		$def_url .= '<input type=\'submit\' value=\'' . $GLOBALS['_LANG']['cappay_button'] . '\'>';
		$def_url .= '</form>';
		return $def_url;
	}

	public function respond()
	{
		$payment = get_payment(basename(__FILE__, '.php'));
		$v_tempdate = explode('-', $_REQUEST['v_oid']);
		$md5info_paramet = $_REQUEST['v_oid'] . $_REQUEST['v_pstatus'] . $_REQUEST['v_pstring'] . $_REQUEST['v_pmode'];
		$md5info_tem = $this->hmac_md5($payment['cappay_key'], $md5info_paramet);
		$md5money_paramet = $_REQUEST['v_amount'] . $_REQUEST['v_moneytype'];
		$md5money_tem = $this->hmac_md5($payment['cappay_key'], $md5money_paramet);
		if (($md5info_tem == $_REQUEST['v_md5info']) && ($md5money_tem == $_REQUEST['v_md5money'])) {
			order_paid($v_tempdate[2]);
			return true;
		}
		else {
			return false;
		}
	}

	public function hmac_md5($key, $data)
	{
		if (extension_loaded('mhash')) {
			return bin2hex(mhash(MHASH_MD5, $data, $key));
		}

		$b = 64;

		if ($b < strlen($key)) {
			$key = pack('H*', md5($key));
		}

		$key = str_pad($key, $b, chr(0));
		$ipad = str_pad('', $b, chr(54));
		$opad = str_pad('', $b, chr(92));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;
		return md5($k_opad . pack('H*', md5($k_ipad . $data)));
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/cappay.php';

if (file_exists($payment_lang)) {
	global $_LANG;
	include_once $payment_lang;
}

if (isset($set_modules) && ($set_modules == true)) {
	$i = (isset($modules) ? count($modules) : 0);
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['desc'] = 'cappay_desc';
	$modules[$i]['is_cod'] = '0';
	$modules[$i]['is_online'] = '1';
	$modules[$i]['author'] = 'ECMOBAN TEAM';
	$modules[$i]['website'] = 'http://www.beijing.com.cn';
	$modules[$i]['version'] = 'V4.3';
	$modules[$i]['config'] = array(
	array('name' => 'cappay_account', 'type' => 'text', 'value' => ''),
	array('name' => 'cappay_key', 'type' => 'text', 'value' => ''),
	array('name' => 'cappay_currency', 'type' => 'select', 'value' => 'USD')
	);
	return NULL;
}

?>
