<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
if (!defined('USE_DEBUGLIB')) {
	define('USE_DEBUGLIB', true);
}

if (USE_DEBUGLIB) {
	$MICROTIME_START = microtime();
	@$GLOBALS_initial_count = count($GLOBALS);
	class Print_a_class
	{	}
}

?>
