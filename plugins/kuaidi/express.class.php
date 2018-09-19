<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class Express
{
	private $expressname = array();

	public function __construct()
	{
		$this->expressname = $this->expressname();
	}

	private function getcontent($url)
	{
		if (function_exists('file_get_contents')) {
			$file_contents = file_get_contents($url);
		}
		else {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$file_contents = curl_exec($ch);
			curl_close($ch);
		}

		return $file_contents;
	}

	private function expressname()
	{
		$site_dir = $this->exp_http() . 'www.kuaidi100.com/';
		$result = $this->getcontent($site_dir);
		preg_match_all('/data\\-code\\="(?P<name>\\w+)"\\>\\<span\\>(?P<title>.*)\\<\\/span>/iU', $result, $data);
		$name = array();

		foreach ($data['title'] as $k => $v) {
			$name[$v] = $data['name'][$k];
		}

		return $name;
	}

	private function json_array($json)
	{
		if ($json) {
			foreach ((array) $json as $k => $v) {
				$data[$k] = !is_string($v) ? $this->json_array($v) : $v;
			}

			return $data;
		}
	}

	public function getorder($name, $order)
	{
		$keywords = '';
		$keywords .= $this->expressname[$name];
		$site_dir = $this->exp_http() . 'www.kuaidi100.com/query?type=' . $keywords . '&postid=' . $order;
		$result = $this->getcontent($site_dir);
		$result = json_decode($result);
		$data = $this->json_array($result);
		return $data;
	}

	private function exp_http()
	{
		if (isset($_SERVER['HTTPS'])) {
			return isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off') ? 'https://' : 'http://';
		}
		else {
			if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && !empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
				$proto_http = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);

				if (strpos($proto_http, 'https') !== false) {
					$proto_http = 'https://';
				}
				else {
					$proto_http = 'http://';
				}

				return $proto_http;
			}
			else {
				return 'http://';
			}
		}
	}
}


?>
