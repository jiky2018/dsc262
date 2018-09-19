<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function exp_post_http()
{
	if (isset($_SERVER['HTTPS'])) {
		return isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off' ? 'https://' : 'http://';
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

$getcom = $_GET['com'];
$getNu = $_GET['nu'];
include_once 'kuaidi100_config.php';
if (isset($postcom) && isset($getNu)) {
	$post_http = exp_post_http();
	$url = $post_http . 'www.kuaidi100.com/applyurl?key=' . $kuaidi100key . '&com=' . $postcom . '&nu=' . $getNu;
	$powered = '查询服务由：<a href="' . $post_http . 'www.kuaidi100.com" target="_blank" style="color:blue">快递100</a> 网站提供';

	if (function_exists('curl_init') == 1) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);
		$get_content = curl_exec($curl);
		curl_close($curl);
	}
	else {
		include 'snoopy.php';
		$snoopy = new snoopy();
		$snoopy->fetch($url);
		$get_content = $snoopy->results;
	}

	if ($post_http == 'https://' && $get_content) {
		$get_content = str_replace('http://', $post_http, $get_content);
	}

	echo '<iframe src="' . $get_content . '" width="534" height="340" frameborder="no" border="0" marginwidth="0" marginheight="0" scrolling="no" allowtransparency="yes"><br/>' . $powered;
}
else {
	echo '查询失败，请重试';
}

exit();
echo "\r";

?>
