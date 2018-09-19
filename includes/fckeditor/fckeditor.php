<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!function_exists('version_compare') || version_compare(phpversion(), '5', '<')) {
	include_once 'fckeditor_php4.php';
}
else {
	include_once 'fckeditor_php5.php';
}

?>
