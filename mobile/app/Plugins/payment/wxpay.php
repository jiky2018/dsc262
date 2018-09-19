<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class wxpay
{
	private $parameters;
	private $payment;

	public function get_code($order, $payment)
	{
		include_once BASE_PATH . 'Helpers/payment_helper.php';
		$this->payment = $payment;
		$options = array('appid' => $this->payment['wxpay_appid'], 'mch_id' => $this->payment['wxpay_mchid'], 'key' => $this->payment['wxpay_key']);
		$weObj = new \App\Extensions\Wechat($options);
		$order_amount = $order['order_amount'] * 100;

		if (!is_wechat_browser()) {
			$scene_info = json_encode(array(
	'h5_info' => array('type' => 'Wap', 'wap_url' => __URL__, 'wap_name' => C('shop.shop_name'))
	));
			$this->setParameter('body', $order['order_sn']);
			$this->setParameter('out_trade_no', $order['order_sn'] . $order_amount . 'A' . $order['log_id']);
			$this->setParameter('total_fee', $order_amount);
			$this->setParameter('spbill_create_ip', $this->get_client_ip());
			$this->setParameter('notify_url', notify_url(basename(__FILE__, '.php')));
			$this->setParameter('trade_type', 'MWEB');
			$this->setParameter('scene_info', $scene_info);
			$respond = $weObj->PayUnifiedOrder($this->parameters);

			if (isset($respond['mweb_url'])) {
				if ($respond['result_code'] == 'SUCCESS') {
					$redirect_url = __URL__ . '/respond.php?code=wxpay&type=wxh5&log_id=' . $order['log_id'];
				}

				$button = '<a class="box-flex btn-submit" type="button" onclick="window.open(\'' . $respond['mweb_url'] . '&redirect_url=' . urlencode($redirect_url) . '\')">微信支付</a>';
			}
			else {
				$button = '';
				return false;
			}
		}
		else {
			$openid = '';
			if (isset($_SESSION['openid']) && !empty($_SESSION['openid'])) {
				$openid = $_SESSION['openid'];
			}
			else {
				if (isset($_SESSION['openid_base']) && !empty($_SESSION['openid_base'])) {
					$openid = $_SESSION['openid_base'];
				}
				else {
					return false;
				}
			}

			$this->setParameter('openid', $openid);
			$this->setParameter('body', $order['order_sn']);
			$this->setParameter('out_trade_no', $order['order_sn'] . $order_amount . 'A' . $order['log_id']);
			$this->setParameter('total_fee', $order_amount);
			$this->setParameter('spbill_create_ip', $this->get_client_ip());
			$this->setParameter('notify_url', notify_url(basename(__FILE__, '.php')));
			$this->setParameter('trade_type', 'JSAPI');
			$respond = $weObj->PayUnifiedOrder($this->parameters, true);
			$jsApiParameters = json_encode($respond);
			$js = "<script language=\"javascript\">\r\n                function jsApiCall(){WeixinJSBridge.invoke(\"getBrandWCPayRequest\"," . $jsApiParameters . ',function(res){if(res.err_msg == "get_brand_wcpay_request:ok"){location.href="' . return_url(basename(__FILE__, '.php')) . '&status=1&log_id=' . $order['log_id'] . '"}else{location.href="' . return_url(basename(__FILE__, '.php')) . '&status=0&log_id=' . $order['log_id'] . "\"}})};function callpay(){if (typeof WeixinJSBridge == \"undefined\"){if( document.addEventListener ){document.addEventListener(\"WeixinJSBridgeReady\", jsApiCall, false);}else if (document.attachEvent){document.attachEvent(\"WeixinJSBridgeReady\", jsApiCall);document.attachEvent(\"onWeixinJSBridgeReady\", jsApiCall);}}else{jsApiCall();}}\r\n                </script>";
			$button = '<a class="box-flex btn-submit" type="button" onclick="callpay();">微信支付</a>' . $js;
		}

		return $button;
	}

	public function callback($data)
	{
		if (isset($_GET) && $_GET['status'] == 1) {
			$order = array();
			$order['log_id'] = intval($_GET['log_id']);
			include_once BASE_PATH . 'Helpers/payment_helper.php';
			$payment = get_payment(basename(__FILE__, '.php'));
			return $this->queryOrder($order, $payment);
		}
		else {
			return false;
		}
	}

	public function notify($data)
	{
		include_once BASE_PATH . 'Helpers/payment_helper.php';
		$_POST['postStr'] = file_get_contents('php://input');

		if (!empty($_POST['postStr'])) {
			$payment = get_payment($data['code']);
			$postdata = json_decode(json_encode(simplexml_load_string($_POST['postStr'], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
			$wxsign = $postdata['sign'];
			unset($postdata['sign']);

			foreach ($postdata as $k => $v) {
				$Parameters[$k] = $v;
			}

			ksort($Parameters);
			$buff = '';

			foreach ($Parameters as $k => $v) {
				$buff .= $k . '=' . $v . '&';
			}

			$String = '';

			if (0 < strlen($buff)) {
				$String = substr($buff, 0, strlen($buff) - 1);
			}

			$String = $String . '&key=' . $payment['wxpay_key'];
			$String = md5($String);
			$sign = strtoupper($String);

			if ($wxsign == $sign) {
				if ($postdata['result_code'] == 'SUCCESS') {
					$out_trade_no = explode('A', $postdata['out_trade_no']);
					$order_sn = $out_trade_no[1];
					dao('pay_log')->data(array('openid' => $postdata['openid'], 'transid' => $postdata['transaction_id']))->where(array('log_id' => $order_sn))->save();
					order_paid($order_sn, 2);
				}

				$returndata['return_code'] = 'SUCCESS';
			}
			else {
				$returndata['return_code'] = 'FAIL';
				$returndata['return_msg'] = '签名失败';
			}
		}
		else {
			$returndata['return_code'] = 'FAIL';
			$returndata['return_msg'] = '无数据返回';
		}

		$xml = '<xml>';

		foreach ($returndata as $key => $val) {
			if (is_numeric($val)) {
				$xml .= '<' . $key . '>' . $val . '</' . $key . '>';
			}
			else {
				$xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
			}
		}

		$xml .= '</xml>';
		exit($xml);
	}

	public function trimString($value)
	{
		$ret = NULL;

		if (NULL != $value) {
			$ret = $value;

			if (strlen($ret) == 0) {
				$ret = NULL;
			}
		}

		return $ret;
	}

	public function createNoncestr($length = 32)
	{
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$str = '';

		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}

		return $str;
	}

	public function setParameter($parameter, $parameterValue)
	{
		$this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}

	public function getSign($Obj)
	{
		foreach ($Obj as $k => $v) {
			$Parameters[$k] = $v;
		}

		ksort($Parameters);
		$buff = '';

		foreach ($Parameters as $k => $v) {
			$buff .= $k . '=' . $v . '&';
		}

		$String = '';

		if (0 < strlen($buff)) {
			$String = substr($buff, 0, strlen($buff) - 1);
		}

		$String = $String . '&key=' . $this->payment['wxpay_key'];
		$String = md5($String);
		$result_ = strtoupper($String);
		return $result_;
	}

	private function get_client_ip()
	{
		if ($_SERVER['REMOTE_ADDR']) {
			$cip = $_SERVER['REMOTE_ADDR'];
		}
		else if (getenv('REMOTE_ADDR')) {
			$cip = getenv('REMOTE_ADDR');
		}
		else if (getenv('HTTP_CLIENT_IP')) {
			$cip = getenv('HTTP_CLIENT_IP');
		}
		else {
			$cip = 'unknown';
		}

		return $cip;
	}

	public function postXmlCurl($xml, $url, $second = 30)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$data = curl_exec($ch);

		if ($data) {
			curl_close($ch);
			return $data;
		}
		else {
			$error = curl_errno($ch);
			echo 'curl出错，错误码:' . $error . '<br>';
			echo '<a href=\'http://curl.haxx.se/libcurl/c/libcurl-errors.html\'>错误原因查询</a></br>';
			curl_close($ch);
			return false;
		}
	}

	public function queryOrder($order, $payment)
	{
		$res = dao('pay_log')->field('transid, is_paid, log_id')->where(array('log_id' => $order['log_id']))->find();
		if ($res['is_paid'] == 0 && !empty($res['transid'])) {
			$options = array('appid' => $payment['wxpay_appid'], 'mch_id' => $payment['wxpay_mchid'], 'key' => $payment['wxpay_key']);
			$weObj = new \App\Extensions\Wechat($options);
			$transaction_id = $res['transid'];
			$this->setParameter('transaction_id', $transaction_id);
			$respond = $weObj->PayQueryOrder($this->parameters);
			if ($respond['result_code'] == 'SUCCESS' && $respond['trade_state'] == 'SUCCESS') {
				order_paid($order['log_id'], 2);
				return true;
			}
			else {
				return false;
			}
		}
		else if ($res['is_paid'] == 1) {
			return true;
		}
		else {
			return false;
		}
	}

	public function payRefund($order, $payment)
	{
		$res = dao('pay_log')->field('transid, is_paid, log_id, order_amount')->where(array('order_id' => $order['order_id']))->find();
		if ($res['is_paid'] == 1 && $order['pay_status'] == 2) {
			$options = array('appid' => $payment['wxpay_appid'], 'mch_id' => $payment['wxpay_mchid'], 'key' => $payment['wxpay_key']);
			$weObj = new \App\Extensions\Wechat($options);
			$sslcert = ROOT_PATH . 'storage/app/certs/wxpay/' . md5($payment['wxpay_appsecret']) . '_apiclient_cert.pem';
			$sslkey = ROOT_PATH . 'storage/app/certs/wxpay/' . md5($payment['wxpay_appsecret']) . '_apiclient_key.pem';
			if (file_exists($sslcert) && file_exists($sslkey)) {
				$order_amount = $res['order_amount'] * 100;
				$order_sn = dao('order_info')->where(array('order_id' => $order['order_id']))->getField('order_sn');
				$out_trade_no = $order_sn . $order_amount . 'A' . $res['log_id'];
				$order_return_info = dao('order_return')->field('return_sn, order_sn, return_status, refound_status')->where(array('order_id' => $order['order_id']))->find();
				$out_refund_no = $order_return_info['return_sn'];
				$total_fee = $order_amount;
				$refund_fee = isset($order['should_return']) ? $order['should_return'] : $order_amount;
				$this->setParameter('out_trade_no', $out_trade_no);
				$this->setParameter('out_refund_no', $out_refund_no);
				$this->setParameter('total_fee', $total_fee);
				$this->setParameter('refund_fee', $refund_fee);
				$this->setParameter('op_user_id', $payment['wxpay_mchid']);
				$respond = $weObj->PayRefund($this->parameters, $sslcert, $sslkey);

				if ($respond['result_code'] == 'SUCCESS') {
					$out_refund_no = $respond['out_refund_no'];
					return $out_refund_no;
				}
				else {
					logResult($respond);
					return false;
				}
			}
		}
	}

	public function payRefundQuery($order, $payment)
	{
		$order_return_info = dao('order_return')->field('return_sn, order_sn, return_status, refound_status')->where(array('order_id' => $order['order_id']))->find();
		if ($order_return_info && $order_return_info['refound_status'] == 1) {
			$options = array('appid' => $payment['wxpay_appid'], 'mch_id' => $payment['wxpay_mchid'], 'key' => $payment['wxpay_key']);
			$weObj = new \App\Extensions\Wechat($options);
			$this->setParameter('out_refund_no', $order_return_info['return_sn']);
			$respond = $weObj->PayRefundQuery($this->parameters);
			if ($respond['result_code'] == 'SUCCESS' && $respond['refund_status'] == 'SUCCESS') {
				$out_refund_no = $respond['out_refund_no'];
				$refund_count = $respond['refund_count'];
				$refund_fee = $respond['refund_fee'];
				return $out_refund_no;
			}
			else {
				logResult($respond);
				return false;
			}
		}
	}

	private function getOpenid()
	{
		if (!isset($_GET['code'])) {
			$redirectUrl = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $_SERVER['QUERY_STRING']);
			$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->payment['wxpay_appid'] . '&redirect_uri=' . $redirectUrl . '&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
			header('Location: ' . $url);
			exit();
		}
		else {
			$code = $_GET['code'];
			$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->payment['wxpay_appid'] . '&secret=' . $this->payment['wxpay_appsecret'] . '&code=' . $code . '&grant_type=authorization_code';
			$result = \App\Extensions\Http::doGet($url);

			if ($result) {
				$json = json_decode($result);
				if (isset($json['errCode']) && $json['errCode']) {
					return false;
				}

				$_SESSION['openid_base'] = $json['openid'];
				return $json['openid'];
			}

			return false;
		}
	}
}

defined('IN_ECTOUCH') || exit('Deny Access');

?>
