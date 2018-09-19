<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
require_once '../lib/client.php';
$url = 'http://127.0.0.1:8080/api';
$key = 'xkg3ydnm';
$secret = '56dygmyhrfuhuwrdst3c';
$c = new prism_client($url, $key, $secret);
$c->set_logger(function($message) {
	echo $message;
	flush();
});

?>
