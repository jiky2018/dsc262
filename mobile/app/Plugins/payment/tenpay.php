<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class tenpay
{
	public function get_code($order, $payment)
	{
		include_once BASE_PATH . 'Helpers/payment_helper.php';
		$gateway = 'https://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_init.cgi';
		$payway = 'https://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_gate.cgi';
		$data = array('ver' => '2.0', 'charset' => 1, 'bank_type' => 0, 'desc' => $order['order_sn'], 'purchaser_id' => '', 'bargainor_id' => $payment['tenpay_account'], 'sp_billno' => $order['order_sn'] . 'O' . $order['log_id'], 'total_fee' => $order['order_amount'] * 100, 'fee_type' => 1, 'notify_url' => notify_url(basename(__FILE__, '.php')), 'callback_url' => return_url(basename(__FILE__, '.php')));
		ksort($data);
		reset($data);
		$sign = '';

		foreach ($data as $key => $vo) {
			if ($vo !== '') {
				$sign .= $key . '=' . $vo . '&';
			}
		}

		$sign .= 'key=' . $payment['tenpay_key'];
		$data['sign'] = strtoupper(md5($sign));
		$result = \App\Extensions\Http::doPost($gateway, $data);
		$xml = (array) simplexml_load_string($result);

		if (isset($xml['err_info'])) {
			return '<a class="box-flex" style="color:red">错误信息：' . $xml['err_info'] . '</a>';
		}

		$button = '<a type="button" class="box-flex btn-submit" onclick="window.open(\'' . $gateway . '?token_id=' . $xml['token_id'] . '\')">财付通支付</a>';
		return $button;
	}

	public function callback($data)
	{
		include_once BASE_PATH . 'Helpers/payment_helper.php';

		if (!empty($_GET)) {
			$payment = get_payment($data['code']);
			$record_data = I($_GET);
			ksort($record_data);
			reset($record_data);
			$sign = '';

			foreach ($record_data as $key => $vo) {
				if (($vo !== '') && ($key != 'sign')) {
					$sign .= $key . '=' . $vo . '&';
				}
			}

			$sign .= 'key=' . $payment['tenpay_key'];
			$sign = strtoupper(md5($sign));

			if ($sign != $record_data['sign']) {
				return false;
			}

			$sp_billno = explode('O', $record_data['sp_billno']);
			$log_id = $sp_billno[1];

			if ($record_data['pay_result'] == 0) {
				order_paid($log_id, 2);
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	public function notify($data)
	{
		include_once BASE_PATH . 'Helpers/payment_helper.php';

		if (!empty($_GET)) {
			$payment = get_payment($data['code']);
			$record_data = in($_GET);
			ksort($record_data);
			reset($record_data);
			$sign = '';

			foreach ($record_data as $key => $vo) {
				if (($vo !== '') && ($key != 'sign')) {
					$sign .= $key . '=' . $vo . '&';
				}
			}

			$sign .= 'key=' . $payment['tenpay_key'];
			$sign = strtoupper(md5($sign));

			if ($sign != $record_data['sign']) {
				exit('fail');
			}

			$pay_result = $record_data['pay_result'];
			$sp_billno = explode('O', $record_data['sp_billno']);
			$log_id = $sp_billno[1];

			if ($pay_result == 0) {
				order_paid($log_id, 2);
				exit('success');
			}
			else {
				exit('fail');
			}
		}
		else {
			exit('fail');
		}
	}

	public function query($order, $payment)
	{
	}
}

defined('IN_ECTOUCH') || exit('Deny Access');

?>
