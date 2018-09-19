<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class aliyunxin
{
	private $regionId = 'cn-hangzhou';
	private $getAccessKeyId = '';
	private $getAccessSecret = '';
	private $getAcceptFormat = 'JSON';
	private $SignatureMethod = 'HMAC-SHA1';
	private $dateTimeFormat = 'Y-m-d\\TH:i:s\\Z';
	private $action = 'SendSms';
	private $protocolType = 'http';
	private $domain = 'dysmsapi.aliyuncs.com';
	private $getMethod = 'GET';
	private $version = '2017-05-25';

	public function __construct($getAccessKeyId = '', $accessKeySecret = '')
	{
		$this->getAccessKeyId = $getAccessKeyId;
		$this->accessKeySecret = $accessKeySecret;
	}

	public function composeUrl($apiParams = array())
	{
		foreach ($apiParams as $key => $value) {
			$apiParams[$key] = $this->prepareValue($value);
		}

		$apiParams['RegionId'] = $this->regionId;
		$apiParams['AccessKeyId'] = $this->getAccessKeyId;
		$apiParams['Format'] = $this->getAcceptFormat;
		$apiParams['SignatureMethod'] = $this->SignatureMethod;
		$apiParams['SignatureVersion'] = $this->getSignatureVersion();
		$apiParams['SignatureNonce'] = uniqid();
		date_default_timezone_set('GMT');
		$apiParams['Timestamp'] = date($this->dateTimeFormat);
		$apiParams['Action'] = $this->action;
		$apiParams['Version'] = $this->version;
		$apiParams['Signature'] = $this->computeSignature($apiParams, $this->accessKeySecret);
		$requestUrl = $this->protocolType . '://' . $this->domain . '/?';

		foreach ($apiParams as $apiParamKey => $apiParamValue) {
			$requestUrl .= $apiParamKey . '=' . urlencode($apiParamValue) . '&';
		}

		return substr($requestUrl, 0, -1);
	}

	public function send($url)
	{
		$http = new Http();
		$resp = $http->doGet($url);
		$resp = json_decode($resp);

		if ($resp->Code == 'OK') {
			return true;
		}
		else {
			$this->errorInfo = $this->errorMsg($resp->Message);
			return $this->errorInfo;
		}
	}

	private function computeSignature($parameters, $accessKeySecret)
	{
		ksort($parameters);
		$canonicalizedQueryString = '';

		foreach ($parameters as $key => $value) {
			$canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
		}

		$stringToSign = $this->getMethod . '&%2F&' . $this->percentEncode(substr($canonicalizedQueryString, 1));
		$signature = $this->signString($stringToSign, $this->accessKeySecret . '&');
		return $signature;
	}

	protected function percentEncode($str)
	{
		$res = urlencode($str);
		$res = preg_replace('/\\+/', '%20', $res);
		$res = preg_replace('/\\*/', '%2A', $res);
		$res = preg_replace('/%7E/', '~', $res);
		return $res;
	}

	private function prepareValue($value)
	{
		if (is_bool($value)) {
			if ($value) {
				return 'true';
			}
			else {
				return 'false';
			}
		}
		else {
			return $value;
		}
	}

	public function signString($source, $accessSecret)
	{
		return base64_encode(hash_hmac('sha1', $source, $accessSecret, true));
	}

	public function getSignatureVersion()
	{
		return '1.0';
	}

	private function errorMsg($key)
	{
		$message = array('isp.RAM_PERMISSION_DENY' => 'RAM权限DENY', 'isv.OUT_OF_SERVICE' => '业务停机', 'isv.PRODUCT_UN_SUBSCRIPT' => '未开通云通信产品的阿里云客户', 'isv.PRODUCT_UNSUBSCRIBE' => '产品未开通', 'isv.ACCOUNT_NOT_EXISTS' => '账户不存在', 'isv.ACCOUNT_ABNORMAL' => '账户异常', 'isv.SMS_TEMPLATE_ILLEGAL' => '短信模板不合法', 'isv.SMS_SIGNATURE_ILLEGAL' => '短信签名不合法', 'isv.INVALID_PARAMETERS' => '参数异常', 'isp.SYSTEM_ERROR' => '系统错误', 'isv.MOBILE_NUMBER_ILLEGAL' => '非法手机号', 'isv.MOBILE_COUNT_OVER_LIMIT' => '手机号码数量超过限制', 'isv.TEMPLATE_MISSING_PARAMETERS' => '模板缺少变量', 'isv.BUSINESS_LIMIT_CONTROL' => '业务限流', 'isv.INVALID_JSON_PARAM' => 'JSON参数不合法，只接受字符串值', 'isv.BLACK_KEY_CONTROL_LIMIT' => '黑名单管控', 'isv.PARAM_LENGTH_LIMIT' => '参数超出长度限制', 'isv.PARAM_NOT_SUPPORT_URL' => '不支持URL', 'isv.AMOUNT_NOT_ENOUGH' => '账户余额不足');
		return $message[$key];
	}

	private function logResult($word = '')
	{
		$word = (is_array($word) ? var_export($word, true) : $word);
		$fp = fopen(ROOT_PATH . 'sms/aliyunxin_log.txt', 'a');
		flock($fp, LOCK_EX);
		fwrite($fp, '执行日期：' . date('Y-m-d H:i:s', time()) . "\n" . $word . "\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
