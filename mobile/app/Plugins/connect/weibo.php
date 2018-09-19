<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class weibo
{
	public $api_url = 'https://api.weibo.com/2/';
	public $format = 'json';

	public function __construct($config, $access_token = NULL)
	{
		$this->client_id = $config['app_key'];
		$this->client_secret = $config['app_secret'];
		$this->access_token = $access_token;
	}

	public function redirect($callback_url)
	{
		return $this->login_url($callback_url, $this->scope);
	}

	public function callback($callback_url, $code)
	{
		$result = $this->access_token($callback_url, $code);
		if (isset($result['access_token']) && $result['access_token'] != '') {
			$this->access_token = $result['access_token'];
			$openid = $this->get_openid();
			$userinfo = $this->get_user_info($openid);

			if ($userinfo['gender'] == 'f') {
				$userinfo['gender'] = 1;
			}
			else if ($userinfo['gender'] == 'm') {
				$userinfo['gender'] = 2;
			}
			else {
				$userinfo['gender'] = 0;
			}

			$_SESSION['nickname'] = $this->get_user_name($userinfo);
			$_SESSION['headimgurl'] = $userinfo['profile_image_url'];
			$data = array('unionid' => $openid, 'nickname' => $this->get_user_name($userinfo), 'sex' => $userinfo['gender'], 'headimgurl' => $userinfo['profile_image_url']);
			return $data;
		}
		else {
			return false;
		}
	}

	public function login_url($callback_url)
	{
		$params = array('response_type' => 'code', 'client_id' => $this->client_id, 'redirect_uri' => $callback_url);
		return 'https://api.weibo.com/oauth2/authorize?' . http_build_query($params, '', '&');
	}

	public function access_token($callback_url, $code)
	{
		$params = array('grant_type' => 'authorization_code', 'code' => $code, 'client_id' => $this->client_id, 'client_secret' => $this->client_secret, 'redirect_uri' => $callback_url);
		$url = 'https://api.weibo.com/oauth2/access_token';
		return $this->http($url, http_build_query($params, '', '&'), 'POST');
	}

	public function get_openid()
	{
		$params = array();
		$result = $this->api('account/get_uid', $params);
		return $result['uid'];
	}

	public function get_user_info($uid)
	{
		$params = array('uid' => $uid);
		return $this->api('users/show', $params);
	}

	public function get_user_name($userinfo)
	{
		if ($userinfo['screen_name'] != '') {
			return $userinfo['screen_name'];
		}
		else {
			return $userinfo['name'];
		}
	}

	public function update($img_c, $pic = '')
	{
		$params = array('status' => $img_c);
		if ($pic != '' && is_array($pic)) {
			$url = 'statuses/upload';
			$params['pic'] = $pic;
		}
		else {
			$url = 'statuses/update';
		}

		return $this->api($url, $params, 'POST');
	}

	public function user_timeline($uid, $count = 10, $page = 1)
	{
		$params = array('uid' => $uid, 'page' => $page, 'count' => $count);
		return $this->api('statuses/user_timeline', $params);
	}

	public function api($url, $params = array(), $method = 'GET')
	{
		$url = $this->api_url . $url . '.' . $this->format;
		$params['access_token'] = $this->access_token;

		if ($method == 'GET') {
			$query = http_build_query($params, '', '&');
			$result = $this->http($url . '?' . $query);
		}
		else if (isset($params['pic'])) {
			uksort($params, 'strcmp');
			$str_b = uniqid('------------------');
			$str_m = '--' . $str_b;
			$str_e = $str_m . '--';
			$body = '';

			foreach ($params as $k => $v) {
				if ($k == 'pic') {
					if (is_array($v)) {
						$img_c = $v[2];
						$img_n = $v[1];
					}
					else if ($v[0] == '@') {
						$url = ltrim($v, '@');
						$img_c = file_get_contents($url);
						$url_a = explode('?', basename($url));
						$img_n = $url_a[0];
					}

					$body .= $str_m . "\r\n";
					$body .= 'Content-Disposition: form-data; name="' . $k . '"; filename="' . $img_n . '"' . "\r\n";
					$body .= "Content-Type: image/unknown\r\n\r\n";
					$body .= $img_c . "\r\n";
				}
				else {
					$body .= $str_m . "\r\n";
					$body .= 'Content-Disposition: form-data; name="' . $k . '"' . "\r\n\r\n";
					$body .= $v . "\r\n";
				}
			}

			$body .= $str_e;
			$headers[] = 'Content-Type: multipart/form-data; boundary=' . $str_b;
			$result = $this->http($url, $body, 'POST', $headers);
		}
		else {
			$query = http_build_query($params, '', '&');
			$result = $this->http($url, $query, 'POST');
		}

		return $result;
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
		$json_r = array();

		if ($response != '') {
			$json_r = json_decode($response, true);
		}

		return $json_r;
	}
}

defined('IN_ECTOUCH') || exit('Deny Access');
$payment_lang = LANG_PATH . C('shop.lang') . '/connect/' . basename(__FILE__);

if (file_exists($payment_lang)) {
	include_once $payment_lang;
	L($_LANG);
}

if (isset($set_modules) && $set_modules == true) {
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['name'] = 'Weibo';
	$modules[$i]['type'] = 'weibo';
	$modules[$i]['className'] = 'weibo';
	$modules[$i]['author'] = 'ECTouch';
	$modules[$i]['qq'] = '800007167';
	$modules[$i]['email'] = 'support@ecmoban.com';
	$modules[$i]['website'] = 'http://open.weibo.com';
	$modules[$i]['version'] = '1.0';
	$modules[$i]['date'] = '2014-10-03';
	$modules[$i]['config'] = array(
	array('type' => 'text', 'name' => 'app_key', 'value' => ''),
	array('type' => 'text', 'name' => 'app_secret', 'value' => '')
	);
	return NULL;
}

?>
