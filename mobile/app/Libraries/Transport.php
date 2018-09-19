<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Libraries;

class Transport
{
	/**
     * 脚本执行时间。－1表示采用PHP的默认值。
     *
     * @access  private
     * @var     integer $time_limit
     */
	private $time_limit = -1;
	/**
     * 在多少秒之内，如果连接不可用，脚本就停止连接。－1表示采用PHP的默认值。
     *
     * @access  private
     * @var     integer $connect_timeout
     */
	private $connect_timeout = -1;
	/**
     * 连接后，限定多少秒超时。－1表示采用PHP的默认值。此项仅当采用CURL库时启用。
     *
     * @access  private
     * @var     integer $stream_timeout
     */
	private $stream_timeout = -1;
	/**
     * 是否使用CURL库来连接。false表示采用fsockopen进行连接。
     *
     * @access  private
     * @var     boolean $use_curl
     */
	private $use_curl = false;

	public function __construct($time_limit = -1, $connect_timeout = -1, $stream_timeout = -1, $use_curl = false)
	{
		$this->time_limit = $time_limit;
		$this->connect_timeout = $connect_timeout;
		$this->stream_timeout = $stream_timeout;
		$this->use_curl = $use_curl;
	}

	public function request($url, $params = '', $method = 'POST', $my_header = '')
	{
		$fsock_exists = function_exists('fsockopen');
		$curl_exists = function_exists('curl_init');
		if (!$fsock_exists && !$curl_exists) {
			exit('No method available!');
		}

		if (!$url) {
			exit('Invalid url!');
		}

		if (-1 < $this->time_limit) {
			set_time_limit($this->time_limit);
		}

		$method = ($method === 'GET' ? $method : 'POST');
		$response = '';
		$temp_str = '';
		if ($params && is_array($params)) {
			foreach ($params as $key => $value) {
				$temp_str .= '&' . $key . '=' . $value;
			}

			$params = preg_replace('/^&/', '', $temp_str);
		}

		if ($fsock_exists && !$this->use_curl) {
			$response = $this->use_socket($url, $params, $method, $my_header);
		}
		else if ($curl_exists) {
			$response = $this->use_curl($url, $params, $method, $my_header);
		}

		if (!$response) {
			return false;
		}

		return $response;
	}

	private function use_socket($url, $params, $method, $my_header)
	{
		$query = '';
		$auth = '';
		$content_type = '';
		$content_length = '';
		$request_body = '';
		$request = '';
		$http_response = '';
		$temp_str = '';
		$error = '';
		$errstr = '';
		$crlf = $this->generate_crlf();

		if ($method === 'GET') {
			$query = ($params ? '?' . $params : '');
		}
		else {
			$request_body = $params;
			$content_type = 'Content-Type: application/x-www-form-urlencoded' . $crlf;
			$content_length = 'Content-Length: ' . strlen($request_body) . $crlf . $crlf;
		}

		$url_parts = $this->parse_raw_url($url);
		$path = $url_parts['path'] . $query;

		if (!empty($url_parts['user'])) {
			$auth = 'Authorization: Basic ' . base64_encode($url_parts['user'] . ':' . $url_parts['pass']) . $crlf;
		}

		if ($my_header && is_array($my_header)) {
			foreach ($my_header as $key => $value) {
				$temp_str .= $key . ': ' . $value . $crlf;
			}

			$my_header = $temp_str;
		}

		$request = $method . ' ' . $path . ' HTTP/1.0' . $crlf . 'Host: ' . $url_parts['host'] . $crlf . $auth . $my_header . $content_type . $content_length . $request_body;

		if (-1 < $this->connect_timeout) {
			$fp = @fsockopen($url_parts['host'], $url_parts['port'], $error, $errstr, $this->connect_timeout);
		}
		else {
			$fp = @fsockopen($url_parts['host'], $url_parts['port'], $error, $errstr);
		}

		if (!$fp) {
			return false;
		}

		if (!@fwrite($fp, $request)) {
			return false;
		}

		while (!feof($fp)) {
			$http_response .= fgets($fp);
		}

		if (!$http_response) {
			return false;
		}

		$separator = '/\\r\\n\\r\\n|\\n\\n|\\r\\r/';
		list($http_header, $http_body) = preg_split($separator, $http_response, 2);
		$http_response = array('header' => $http_header, 'body' => $http_body);
		@fclose($fp);
		return $http_response;
	}

	private function use_curl($url, $params, $method, $my_header)
	{
		$curl_session = curl_init();
		curl_setopt($curl_session, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl_session, CURLOPT_HEADER, true);
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_session, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		$url_parts = $this->parse_raw_url($url);

		if (!empty($url_parts['user'])) {
			$auth = $url_parts['user'] . ':' . $url_parts['pass'];
			curl_setopt($curl_session, CURLOPT_USERPWD, $auth);
			curl_setopt($curl_session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}

		$header = array();
		$header[] = 'Host: ' . $url_parts['host'];
		if ($my_header && is_array($my_header)) {
			foreach ($my_header as $key => $value) {
				$header[] = $key . ': ' . $value;
			}
		}

		if ($method === 'GET') {
			curl_setopt($curl_session, CURLOPT_HTTPGET, true);
			$url .= ($params ? '?' . $params : '');
		}
		else {
			curl_setopt($curl_session, CURLOPT_POST, true);
			$header[] = 'Content-Type: application/x-www-form-urlencoded';
			$header[] = 'Content-Length: ' . strlen($params);
			curl_setopt($curl_session, CURLOPT_POSTFIELDS, $params);
		}

		curl_setopt($curl_session, CURLOPT_URL, $url);
		curl_setopt($curl_session, CURLOPT_HTTPHEADER, $header);

		if (-1 < $this->connect_timeout) {
			curl_setopt($curl_session, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
		}

		if (-1 < $this->stream_timeout) {
			curl_setopt($curl_session, CURLOPT_TIMEOUT, $this->stream_timeout);
		}

		$http_response = curl_exec($curl_session);

		if (curl_errno($curl_session) != 0) {
			return false;
		}

		$separator = '/\\r\\n\\r\\n|\\n\\n|\\r\\r/';
		list($http_header, $http_body) = preg_split($separator, $http_response, 2);
		$http_response = array('header' => $http_header, 'body' => $http_body);
		curl_close($curl_session);
		return $http_response;
	}

	private function parse_raw_url($raw_url)
	{
		$retval = array();
		$raw_url = (string) $raw_url;

		if (strpos($raw_url, '://') === false) {
			$raw_url = 'http://' . $raw_url;
		}

		$retval = parse_url($raw_url);

		if (!isset($retval['path'])) {
			$retval['path'] = '/';
		}

		if (!isset($retval['port'])) {
			$retval['port'] = '80';
		}

		return $retval;
	}

	private function generate_crlf()
	{
		$crlf = '';

		if (strtoupper(substr(PHP_OS, 0, 3) === 'WIN')) {
			$crlf = "\r\n";
		}
		else if (strtoupper(substr(PHP_OS, 0, 3) === 'MAC')) {
			$crlf = "\r";
		}
		else {
			$crlf = "\n";
		}

		return $crlf;
	}
}


?>
