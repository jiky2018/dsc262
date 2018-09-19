<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function createParam($paramArr, $showapi_secret)
{
	$paraStr = '';
	$signStr = '';
	ksort($paramArr);

	foreach ($paramArr as $key => $val) {
		if (($key != '') && ($val != '')) {
			$signStr .= $key . $val;
			$paraStr .= $key . '=' . urlencode($val) . '&';
		}
	}

	$signStr .= $showapi_secret;
	$sign = strtolower(md5($signStr));
	$paraStr .= 'showapi_sign=' . $sign;
	echo '排好序的参数:' . $signStr . "<br>\r\n";
	return $paraStr;
}

header('Content-Type:text/html;charset=UTF-8');
date_default_timezone_set('PRC');
$showapi_appid = 'xxxxxx';
$showapi_secret = 'xxxxxxxxx';
$paramArr = array('showapi_appid' => $showapi_appid, 'code' => '');
$param = createparam($paramArr, $showapi_secret);
$url = 'http://route.showapi.com/66-22?' . $param;
echo '请求的url:' . $url . "<br>\r\n";
$result = file_get_contents($url);
echo "返回的json数据:<br>\r\n";
print($result . '<br>\\r\\n');
$result = json_decode($result);
echo "<br>\r\n取出showapi_res_code的值:<br>\r\n";
print_r($result->showapi_res_code);
echo "<br>\r\n";

?>
