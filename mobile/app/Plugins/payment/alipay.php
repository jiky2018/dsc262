<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class alipay
{
	public function get_code($order, $payment)
	{
		$payData = array('body' => $order['order_sn'], 'subject' => $order['order_sn'], 'order_no' => make_trade_no($order['log_id'], $order['order_amount']), 'timeout_express' => time() + 3600 * 24, 'amount' => $order['order_amount'], 'return_param' => (string) $order['log_id'], 'client_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1', 'goods_type' => 1, 'store_id' => '');

		try {
			$payUrl = \Payment\Client\Charge::run(\Payment\Config::ALI_CHANNEL_WAP, $this->getConfig(), $payData);
		}
		catch (\Payment\Common\PayException $e) {
			exit($e->getMessage());
		}

		return '<a  type="button" class="box-flex btn-submit min-two-btn" onclick="javascript:_AP.pay(\'' . $payUrl . '\')">支付宝支付</a>';
	}

	public function callback($data)
	{
		if (!empty($_GET)) {
			try {
				$log_id = parse_trade_no($_GET['out_trade_no']);
				$sql = 'SELECT oi.order_sn, pl.log_id, pl.order_amount from ' . $GLOBALS['ecs']->table('pay_log') . ' as pl LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' as oi ON pl.order_id = oi.order_id WHERE pl.log_id = \'' . $log_id . '\'';
				$order = $GLOBALS['db']->getRow($sql);
				return $this->query($order);
			}
			catch (\Payment\Common\PayException $e) {
				logResult($e->getMessage());
				return false;
			}
		}
		else {
			return false;
		}
	}

	public function notify($data)
	{
		if (!empty($_POST)) {
			try {
				$callback = new OrderPaidNotify();
				$ret = \Payment\Client\Notify::run(\Payment\Config::ALI_CHARGE, $this->getConfig(), $callback);
				exit($ret);
			}
			catch (\Payment\Common\PayException $e) {
				logResult($e->getMessage());
				exit('fail');
			}
		}
		else {
			exit('fail');
		}
	}

	public function query($order)
	{
		$data = array('out_trade_no' => make_trade_no($order['log_id'], $order['order_amount']));

		try {
			$ret = \Payment\Client\Query::run(\Payment\Config::ALI_CHARGE, $this->getConfig(), $data);

			if ($ret['response']['trade_state'] === \Payment\Config::TRADE_STATUS_SUCC) {
				order_paid($order['log_id'], 2);
				return true;
			}
		}
		catch (\Payment\Common\PayException $e) {
			logResult($e->getMessage());
		}

		return false;
	}

	private function getConfig()
	{
		include_once BASE_PATH . 'Helpers/payment_helper.php';
		$payment = get_payment(basename(__FILE__, '.php'));
		return array('use_sandbox' => (bool) $payment['use_sandbox'], 'partner' => $payment['alipay_partner'], 'app_id' => $payment['app_id'], 'sign_type' => $payment['sign_type'], 'ali_public_key' => $payment['ali_public_key'], 'rsa_private_key' => $payment['rsa_private_key'], 'notify_url' => notify_url(basename(__FILE__, '.php')), 'return_url' => return_url(basename(__FILE__, '.php')), 'return_raw' => false);
	}
}

defined('IN_ECTOUCH') || exit('Deny Access');
class OrderPaidNotify implements \Payment\Notify\PayNotifyInterface
{
	public function notifyProcess(array $data)
	{
		$log_id = $data['return_param'];
		order_paid($log_id, 2);
		return true;
	}
}

?>
