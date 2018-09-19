<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class ecs_login
{
	public $access_token = '';
	public $get_openid_url = 'https://graph.qq.com/oauth2.0/me?';

	public function __construct($access_token)
	{
		$this->access_token = $access_token;
	}

	public function get_unionid()
	{
		$params = array('access_token' => $this->access_token, 'unionid' => 1);
		$url = $this->get_openid_url . http_build_query($params, '', '&');
		$result_str = $this->http($url);
		$json_r = array();

		if ($result_str) {
			preg_match('/callback\\(\\s+(.*?)\\s+\\)/i', $result_str, $result_a);
			$json_r = json_decode($result_a[1], true);
			if (!$json_r || !empty($json_r['error'])) {
				$errCode = $json_r['error'];
				$errMsg = $json_r['error_description'];
				return false;
			}

			return $json_r['unionid'];
		}

		return false;
	}

	private function http($url, $postfields = '', $method = 'GET', $headers = array())
	{
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ci, CURLOPT_TIMEOUT, 30);

		if ($method == 'POST') {
			curl_setopt($ci, CURLOPT_POST, true);

			if ($postfields != '') {
				curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
			}
		}

		$headers[] = 'User-Agent: ECTouch.cn';
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		curl_close($ci);
		return $response;
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
