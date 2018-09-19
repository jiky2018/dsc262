<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class alipay
{
	public function get_code($order, $payment)
	{
		if (!defined('CHARSET')) {
			$charset = 'utf-8';
		}
		else {
			$charset = CHARSET;
		}

		include_once BASE_PATH . 'Helpers/payment_helper.php';
		$gateway = 'http://wappaygw.alipay.com/service/rest.htm?';
		$req_data = '<direct_trade_create_req>' . '<subject>' . $order['order_sn'] . '</subject>' . '<out_trade_no>' . $order['order_sn'] . 'O' . $order['log_id'] . '</out_trade_no>' . '<total_fee>' . $order['order_amount'] . '</total_fee>' . '<seller_account_name>' . $payment['alipay_account'] . '</seller_account_name>' . '<call_back_url>' . return_url(basename(__FILE__, '.php')) . '</call_back_url>' . '<notify_url>' . notify_url(basename(__FILE__, '.php')) . '</notify_url>' . '<out_user>' . $order['consignee'] . '</out_user>' . '<merchant_url>' . __URL__ . '</merchant_url>' . '<pay_expire>3600</pay_expire>' . '</direct_trade_create_req>';
		$parameter = array('service' => 'alipay.wap.trade.create.direct', 'format' => 'xml', 'v' => '2.0', 'partner' => $payment['alipay_partner'], 'req_id' => $order['order_sn'] . $order['log_id'], 'sec_id' => 'MD5', 'req_data' => $req_data, '_input_charset' => $charset);
		ksort($parameter);
		reset($parameter);
		$param = '';
		$sign = '';

		foreach ($parameter as $key => $val) {
			$param .= $key . '=' . urlencode($val) . '&';
			$sign .= $key . '=' . $val . '&';
		}

		$param = substr($param, 0, -1);
		$sign = substr($sign, 0, -1) . $payment['alipay_key'];
		$result = \App\Extensions\Http::doPost($gateway, $param . '&sign=' . md5($sign));

		if (!$result) {
			$result = file_get_contents($gateway . $param . '&sign=' . md5($sign));
		}

		$result = urldecode($result);
		$result_array = explode('&', $result);
		$new_result_array = $temp_item = array();

		if (is_array($result_array)) {
			foreach ($result_array as $vo) {
				$temp_item = explode('=', $vo, 2);
				$new_result_array[$temp_item[0]] = $temp_item[1];
			}
		}

		$xml = simplexml_load_string($new_result_array['res_data']);
		$request_token = (array) $xml->request_token;
		$parameter = array('service' => 'alipay.wap.auth.authAndExecute', 'format' => 'xml', 'v' => $new_result_array['v'], 'partner' => $new_result_array['partner'], 'sec_id' => $new_result_array['sec_id'], 'req_data' => '<auth_and_execute_req><request_token>' . $request_token[0] . '</request_token></auth_and_execute_req>', 'request_token' => $request_token[0], 'app_pay' => 'Y', '_input_charset' => $charset);
		ksort($parameter);
		reset($parameter);
		$param = '';
		$sign = '';

		foreach ($parameter as $key => $val) {
			$param .= $key . '=' . urlencode($val) . '&';
			$sign .= $key . '=' . $val . '&';
		}

		$param = substr($param, 0, -1);
		$sign = substr($sign, 0, -1) . $payment['alipay_key'];
		$button = '<a  type="button" class="box-flex btn-submit min-two-btn" onclick="javascript:_AP.pay(\'' . $gateway . $param . '&sign=' . md5($sign) . '\')">支付宝支付</a>';
		return $button;
	}

	public function callback($data)
	{
		if (!empty($_GET)) {
			include_once BASE_PATH . 'Helpers/payment_helper.php';
			$out_trade_no = explode('O', $_GET['out_trade_no']);
			$log_id = $out_trade_no[1];
			$payment = get_payment($data['code']);
			ksort($_GET);
			reset($_GET);
			$sign = '';

			foreach ($_GET as $key => $val) {
				if (($key != 'sign') && ($key != 'sign_type') && ($key != 'code')) {
					$sign .= $key . '=' . $val . '&';
				}
			}

			$sign = substr($sign, 0, -1) . $payment['alipay_key'];

			if (md5($sign) != $_GET['sign']) {
				return false;
			}

			if ($_GET['result'] == 'success') {
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
		if (!empty($_POST)) {
			include_once BASE_PATH . 'Helpers/payment_helper.php';
			$payment = get_payment($data['code']);
			$parameter['service'] = $_POST['service'];
			$parameter['v'] = $_POST['v'];
			$parameter['sec_id'] = $_POST['sec_id'];
			$parameter['notify_data'] = $_POST['notify_data'];
			$sign = '';

			foreach ($parameter as $key => $val) {
				$sign .= $key . '=' . $val . '&';
			}

			$sign = substr($sign, 0, -1) . $payment['alipay_key'];

			if (md5($sign) != $_POST['sign']) {
				exit('fail');
			}

			$data = (array) simplexml_load_string($parameter['notify_data']);
			$trade_status = $data['trade_status'];
			$out_trade_no = explode('O', $data['out_trade_no']);
			$log_id = $out_trade_no[1];
			if (($trade_status == 'TRADE_FINISHED') || ($trade_status == 'TRADE_SUCCESS')) {
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
