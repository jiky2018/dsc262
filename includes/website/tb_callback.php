<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function curl($url, $postFields = NULL)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FAILONERROR, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if (is_array($postFields) && (0 < count($postFields))) {
		$postBodyString = '';

		foreach ($postFields as $k => $v) {
			$postBodyString .= $k . '=' . urlencode($v) . '&';
		}

		unset($k);
		unset($v);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
	}

	$reponse = curl_exec($ch);

	if (curl_errno($ch)) {
		throw new Exception(curl_error($ch), 0);
	}
	else {
		$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if (200 !== $httpStatusCode) {
			throw new Exception($reponse, $httpStatusCode);
		}
	}

	curl_close($ch);
	return $reponse;
}

header('Content-type:text/html; charset=UTF-8;');
define('IN_ECS', true);
require_once 'config/taobao_config.php';
session_start();
if (!isset($_GET['state']) || empty($_GET['state']) || !isset($_GET['code']) || empty($_GET['code'])) {
	echo '<span style=\'font-size:12px;line-height:24px;\'>请求非法或超时!&nbsp;&nbsp;<a href=\'/index.php\'>返回首页</a></span>';
	exit();
}
else {
	if ($_GET['state'] != $_SESSION['tb_state']) {
		echo '<span style=\'font-size:12px;line-height:24px;\'>请求非法或超时!&nbsp;&nbsp;<a href=\'/index.php\'>返回首页</a></span>';
		exit();
	}

	$code = $_GET['code'];
	$redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$postfields = array('grant_type' => 'authorization_code', 'client_id' => APP_KEY, 'client_secret' => APP_SECRET, 'code' => $code, 'redirect_uri' => $redirect_url);
	$url = 'https://oauth.taobao.com/token';
	$token = json_decode(curl($url, $postfields));
	$access_token = $token->access_token;
	$_SESSION['tb_access_token'] = $access_token;
	$user_info['user_id'] = $token->taobao_user_id;
	$user_info['name'] = urldecode($token->taobao_user_nick);
	$_SESSION['user_info'] = $user_info;
	$go_url = '../../user.php?act=other_login&type=tb';
	header('location:' . $go_url);
}

?>
