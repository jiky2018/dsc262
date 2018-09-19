<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class SpiUtils
{
	static private $top_sign_list = 'HTTP_TOP_SIGN_LIST';
	static private $timestamp = 'timestamp';
	static private $header_real_ip = array('X_Real_IP', 'X_Forwarded_For', 'Proxy_Client_IP', 'WL_Proxy_Client_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR');

	static public function checkSign4FormRequest($secret)
	{
		return self::checkSign(NULL, NULL, $secret);
	}

	static public function checkSign4TextRequest($body, $secret)
	{
		return self::checkSign(NULL, $body, $secret);
	}

	static public function checkSign4FileRequest($form, $secret)
	{
		return self::checkSign($form, NULL, $secret);
	}

	static private function checkSign($form, $body, $secret)
	{
		$params = array();
		$headerMap = self::getHeaderMap();

		foreach ($headerMap as $k => $v) {
			$params[$k] = $v;
		}

		$queryMap = self::getQueryMap();

		foreach ($queryMap as $k => $v) {
			$params[$k] = $v;
		}

		if (($form == NULL) && ($body == NULL)) {
			$formMap = self::getFormMap();

			foreach ($formMap as $k => $v) {
				$params[$k] = $v;
			}
		}
		else if ($form != NULL) {
			foreach ($form as $k => $v) {
				$params[$k] = $v;
			}
		}

		if ($body == NULL) {
			$body = file_get_contents('php://input');
		}

		$remoteSign = $queryMap['sign'];
		$localSign = self::sign($params, $body, $secret);

		if (strcmp($remoteSign, $localSign) == 0) {
			return true;
		}
		else {
			$paramStr = self::getParamStrFromMap($params);
			self::logCommunicationError($remoteSign, $localSign, $paramStr, $body);
			return false;
		}
	}

	static private function getHeaderMap()
	{
		$headerMap = array();
		$signList = $_SERVER['HTTP_TOP_SIGN_LIST'];
		$signList = trim($signList);

		if (0 < strlen($signList)) {
			$params = split(',', $signList);

			foreach ($_SERVER as $k => $v) {
				if (substr($k, 0, 5) == 'HTTP_') {
					foreach ($params as $kk) {
						$upperkey = strtoupper($kk);

						if (self::endWith($k, $upperkey)) {
							$headerMap[$kk] = $v;
						}
					}
				}
			}
		}

		return $headerMap;
	}

	static private function getQueryMap()
	{
		$queryStr = $_SERVER['QUERY_STRING'];
		$resultArray = array();

		foreach (explode('&', $queryStr) as $pair) {
			list($key, $value) = explode('=', $pair);

			if (strpos($key, '.') !== false) {
				list($subKey, $subVal) = explode('.', $key);

				if (preg_match('/(?P<name>\\w+)\\[(?P<index>\\w+)\\]/', $subKey, $matches)) {
					$resultArray[$matches['name']][$matches['index']][$subVal] = $value;
				}
				else {
					$resultArray[$subKey][$subVal] = urldecode($value);
				}
			}
			else {
				$resultArray[$key] = urldecode($value);
			}
		}

		return $resultArray;
	}

	static private function checkRemoteIp()
	{
		$remoteIp = $_SERVER['REMOTE_ADDR'];

		foreach ($header_real_ip as $k) {
			$realIp = $_SERVER[$k];
			$realIp = trim($realIp);
			if ((0 < strlen($realIp)) && strcasecmp('unknown', $realIp)) {
				$remoteIp = $realIp;
				break;
			}
		}

		return self::startsWith($remoteIp, '140.205.144.') || $this->startsWith($remoteIp, '40.205.145.');
	}

	static private function getFormMap()
	{
		$resultArray = array();

		foreach ($_POST as $key => $v) {
			$resultArray[$k] = $v;
		}

		return $resultArray;
	}

	static private function startsWith($haystack, $needle)
	{
		return ($needle === '') || (strpos($haystack, $needle) === 0);
	}

	static private function endWith($haystack, $needle)
	{
		$length = strlen($needle);

		if ($length == 0) {
			return true;
		}

		return substr($haystack, 0 - $length) === $needle;
	}

	static private function checkTimestamp()
	{
		$ts = $_POST['timestamp'];

		if ($ts) {
			$clientTimestamp = strtotime($ts);
			$current = $_SERVER['REQUEST_TIME'];
			return ($current - $clientTimestamp) <= 5 * 60 * 1000;
		}
		else {
			return false;
		}
	}

	static private function getParamStrFromMap($params)
	{
		ksort($params);
		$stringToBeSigned = '';

		foreach ($params as $k => $v) {
			if (strcmp('sign', $k) != 0) {
				$stringToBeSigned .= $k . $v;
			}
		}

		unset($k);
		unset($v);
		return $stringToBeSigned;
	}

	static private function sign($params, $body, $secret)
	{
		ksort($params);
		$stringToBeSigned = $secret;
		$stringToBeSigned .= self::getParamStrFromMap($params);

		if ($body) {
			$stringToBeSigned .= $body;
		}

		$stringToBeSigned .= $secret;
		return strtoupper(md5($stringToBeSigned));
	}

	static protected function logCommunicationError($remoteSign, $localSign, $paramStr, $body)
	{
		$localIp = (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'CLI');
		$logger = new TopLogger();
		$logger->conf['log_file'] = rtrim(TOP_SDK_WORK_DIR, '\\/') . '/' . 'logs/top_comm_err_' . date('Y-m-d') . '.log';
		$logger->conf['separator'] = '^_^';
		$logData = array('checkTopSign error', 'remoteSign=' . $remoteSign, 'localSign=' . $localSign, 'paramStr=' . $paramStr, 'body=' . $body);
		$logger->log($logData);
	}

	static private function clear_blank($str, $glue = '')
	{
		$replace = array(' ', "\r", "\n", '	');
		return str_replace($replace, $glue, $str);
	}
}


?>
