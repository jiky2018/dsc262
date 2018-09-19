<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class AliyunClient
{
	public $accessKeyId;
	public $accessKeySecret;
	public $serverUrl = 'http://ecs.aliyuncs.com/';
	public $format = 'json';
	public $connectTimeout = 3000;
	public $readTimeout = 80000;
	/** 是否打开入参check**/
	public $checkRequest = true;
	protected $signatureMethod = 'HMAC-SHA1';
	protected $signatureVersion = '1.0';
	protected $dateTimeFormat = 'Y-m-d\\TH:i:s\\Z';
	protected $sdkVersion = '1.0';

	public function execute($request)
	{
		if ($this->checkRequest) {
			try {
				$request->check();
			}
			catch (Exception $e) {
				$result->code = $e->getCode();
				$result->message = $e->getMessage();
				return $result;
			}
		}

		$apiParams = $request->getApiParas();
		$apiParams['AccessKeyId'] = $this->accessKeyId;
		$apiParams['Format'] = $this->format;
		$apiParams['SignatureMethod'] = $this->signatureMethod;
		$apiParams['SignatureVersion'] = $this->signatureVersion;
		$apiParams['SignatureNonce'] = uniqid();
		date_default_timezone_set('GMT');
		$apiParams['TimeStamp'] = date($this->dateTimeFormat);
		$apiParams['partner_id'] = $this->sdkVersion;
		$apiNameArray = split('\\.', $request->getApiMethodName());
		$apiParams['Action'] = $apiNameArray[3];
		$apiParams['Version'] = $apiNameArray[4];
		$apiParams['Signature'] = $this->computeSignature($apiParams, $this->accessKeySecret);
		$requestUrl = rtrim($this->serverUrl, '/') . '/?';

		foreach ($apiParams as $apiParamKey => $apiParamValue) {
			$requestUrl .= $apiParamKey . '=' . urlencode($apiParamValue) . '&';
		}

		$requestUrl = substr($requestUrl, 0, -1);

		try {
			$resp = $this->curl($requestUrl, NULL);
		}
		catch (Exception $e) {
			$this->logCommunicationError($apiParams['Action'], $requestUrl, 'HTTP_ERROR_' . $e->getCode(), $e->getMessage());

			if ('json' == $this->format) {
				return json_decode($e->getMessage());
			}
			else if ('xml' == $this->format) {
				return @simplexml_load_string($e->getMessage());
			}
		}

		$respWellFormed = false;

		if ('json' == $this->format) {
			$respObject = json_decode($resp);

			if (NULL !== $respObject) {
				$respWellFormed = true;
			}
		}
		else if ('xml' == $this->format) {
			$respObject = @simplexml_load_string($resp);

			if (false !== $respObject) {
				$respWellFormed = true;
			}
		}

		if (false === $respWellFormed) {
			$this->logCommunicationError($apiParams['Action'], $requestUrl, 'HTTP_RESPONSE_NOT_WELL_FORMED', $resp);
			$result->code = 0;
			$result->message = 'HTTP_RESPONSE_NOT_WELL_FORMED';
			return $result;
		}

		if (isset($respObject->code)) {
			$logger = new LtLogger();
			$logger->conf['log_file'] = rtrim(TOP_SDK_WORK_DIR, '\\/') . '/' . 'logs/top_biz_err_' . $this->appkey . '_' . date('Y-m-d') . '.log';
			$logger->log(array(date('Y-m-d H:i:s'), $resp));
		}

		return $respObject;
	}

	public function exec($paramsArray)
	{
		if (!isset($paramsArray['Action'])) {
			trigger_error('No api name passed');
		}

		$inflector = new LtInflector();
		$inflector->conf['separator'] = '.';
		$requestClassName = ucfirst($inflector->camelize(substr($paramsArray['Action'], 7))) . 'Request';

		if (!class_exists($requestClassName)) {
			trigger_error('No such api: ' . $paramsArray['Action']);
		}

		$req = new $requestClassName();

		foreach ($paramsArray as $paraKey => $paraValue) {
			$inflector->conf['separator'] = '_';
			$setterMethodName = $inflector->camelize($paraKey);
			$inflector->conf['separator'] = '.';
			$setterMethodName = 'set' . $inflector->camelize($setterMethodName);

			if (method_exists($req, $setterMethodName)) {
				$req->$setterMethodName($paraValue);
			}
		}

		return $this->execute($req, $session);
	}

	protected function percentEncode($str)
	{
		$res = urlencode($str);
		$res = preg_replace('/\\+/', '%20', $res);
		$res = preg_replace('/\\*/', '%2A', $res);
		$res = preg_replace('/%7E/', '~', $res);
		return $res;
	}

	protected function computeSignature($parameters, $accessKeySecret)
	{
		ksort($parameters);
		$canonicalizedQueryString = '';

		foreach ($parameters as $key => $value) {
			$canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
		}

		$stringToSign = 'GET&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
		$signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
		return $signature;
	}

	public function curl($url, $postFields = NULL)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if ($this->readTimeout) {
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->readTimeout);
		}

		if ($this->connectTimeout) {
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
		}

		if ((5 < strlen($url)) && (strtolower(substr($url, 0, 5)) == 'https')) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}

		if (is_array($postFields) && (0 < count($postFields))) {
			$postBodyString = '';
			$postMultipart = false;

			foreach ($postFields as $k => $v) {
				if ('@' != substr($v, 0, 1)) {
					$postBodyString .= $k . '=' . urlencode($v) . '&';
				}
				else {
					$postMultipart = true;
				}
			}

			unset($k);
			unset($v);
			curl_setopt($ch, CURLOPT_POST, true);

			if ($postMultipart) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
			}
			else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
			}
		}

		$reponse = curl_exec($ch);

		if (curl_errno($ch)) {
			throw new Exception(curl_error($ch), 0);
		}

		curl_close($ch);
		return $reponse;
	}

	protected function logCommunicationError($apiName, $requestUrl, $errorCode, $responseTxt)
	{
		$localIp = (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'CLI');
		$logger = new LtLogger();
		$logger->conf['log_file'] = rtrim(TOP_SDK_WORK_DIR, '\\/') . '/' . 'logs/top_comm_err_' . $this->accessKeyId . '_' . date('Y-m-d') . '.log';
		$logger->conf['separator'] = '^_^';
		$logData = array(date('Y-m-d H:i:s'), $apiName, $this->accessKeyId, $localIp, PHP_OS, $this->sdkVersion, $requestUrl, $errorCode, str_replace("\n", '', $responseTxt));
		$logger->log($logData);
	}
}


?>
