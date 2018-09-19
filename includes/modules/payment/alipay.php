<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class alipay
{
	public function get_code($order, $payment)
	{
		$payData = array('body' => $order['order_sn'], 'subject' => $order['order_sn'], 'order_no' => $order['order_sn'] . 'O' . $order['log_id'], 'timeout_express' => time() + 3600 * 24, 'amount' => $order['order_amount'], 'return_param' => (string) $order['log_id'], 'client_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1', 'goods_type' => 1, 'store_id' => '');

		try {
			$payUrl = \Payment\Client\Charge::run(\Payment\Config::ALI_CHANNEL_WEB, $this->getConfig(), $payData);
		}
		catch (\Payment\Common\PayException $e) {
			exit($e->getMessage());
		}

		if (isset($order['merge']) && $order['merge'] == 1) {
			return $payUrl;
		}
		else {
			return '<div class="alipay" style="text-align:center"><input type="button" onclick="window.open(\'' . $payUrl . '\')" value="' . $GLOBALS['_LANG']['pay_button'] . '" /></div>';
		}
	}

	public function respond()
	{
		if (!empty($_GET)) {
			try {
				$order = array();
				list($order['order_sn'], $order['log_id']) = explode('O', $_GET['out_trade_no']);
				return $this->query($order);
			}
			catch (\Payment\Common\PayException $e) {
				$this->logResult($e->getMessage());
				return false;
			}
		}
		else {
			return false;
		}
	}

	public function notify()
	{
		unset($_POST['code']);
		$config = $this->getConfig();
		$config['notify_url'] = str_replace('api/notify/api/notify', 'api/notify', $config['notify_url']);
		$config['return_url'] = str_replace('/api/notify', '', $config['return_url']);
		$_POST['fund_bill_list'] = stripslashes($_POST['fund_bill_list']);

		if (!empty($_POST)) {
			try {
				$callback = new OrderPaidNotify();
				$ret = \Payment\Client\Notify::run(\Payment\Config::ALI_CHARGE, $config, $callback);
				exit($ret);
			}
			catch (\Payment\Common\PayException $e) {
				$this->logResult($e->getMessage());
				exit('fail');
			}
		}
		else {
			exit('fail');
		}
	}

	public function query($order)
	{
		$data = array('out_trade_no' => $order['order_sn'] . 'O' . $order['log_id']);

		try {
			$ret = \Payment\Client\Query::run(\Payment\Config::ALI_CHARGE, $this->getConfig(), $data);

			if ($ret['response']['trade_state'] === \Payment\Config::TRADE_STATUS_SUCC) {
				order_paid($order['log_id'], 2, '', $order['order_sn']);
				return true;
			}
		}
		catch (\Payment\Common\PayException $e) {
			$this->logResult($e->getMessage());
		}

		return false;
	}

	private function getConfig()
	{
		if (!function_exists('get_payment')) {
			include_once ROOT_PATH . '/includes/lib_payment.php';
		}

		$payment = get_payment(basename(__FILE__, '.php'));
		return array('use_sandbox' => (bool) $payment['use_sandbox'], 'partner' => $payment['alipay_partner'], 'app_id' => $payment['app_id'], 'sign_type' => $payment['sign_type'], 'ali_public_key' => $payment['ali_public_key'], 'rsa_private_key' => $payment['rsa_private_key'], 'notify_url' => notify_url(basename(__FILE__, '.php')), 'return_url' => return_url(basename(__FILE__, '.php')), 'return_raw' => false);
	}

	private function logResult($word = '')
	{
		$word = is_array($word) ? var_export($word, 1) : $word;
		$fp = fopen(ROOT_PATH . '/data/alipaylog.txt', 'a');
		flock($fp, LOCK_EX);
		fwrite($fp, '执行日期：' . strftime('%Y%m%d%H%M%S', time()) . "\n" . $word . "\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/alipay.php';

if (file_exists($payment_lang)) {
	global $_LANG;
	include_once $payment_lang;
}

if (isset($set_modules) && $set_modules == true) {
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['desc'] = 'alipay_desc';
	$modules[$i]['is_cod'] = '0';
	$modules[$i]['is_online'] = '1';
	$modules[$i]['author'] = 'ECMOBAN TEAM';
	$modules[$i]['website'] = 'http://www.alipay.com';
	$modules[$i]['version'] = '1.0.2';
	$modules[$i]['config'] = array(
	array('name' => 'alipay_account', 'type' => 'text', 'value' => ''),
	array('name' => 'alipay_key', 'type' => 'text', 'value' => ''),
	array('name' => 'alipay_partner', 'type' => 'text', 'value' => ''),
	array('name' => 'alipay_pay_method', 'type' => 'select', 'value' => ''),
	array('name' => 'use_sandbox', 'type' => 'select', 'value' => ''),
	array('name' => 'app_id', 'type' => 'text', 'value' => ''),
	array('name' => 'sign_type', 'type' => 'select', 'value' => ''),
	array('name' => 'ali_public_key', 'type' => 'textarea', 'value' => ''),
	array('name' => 'rsa_private_key', 'type' => 'textarea', 'value' => '')
	);
	return NULL;
}

class OrderPaidNotify implements \Payment\Notify\PayNotifyInterface
{
	public function notifyProcess(array $data)
	{
		$log_id = $data['return_param'];
		order_paid($log_id, 2, '', $data['subject']);
		return true;
	}
}

?>
