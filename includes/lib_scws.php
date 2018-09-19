<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function scws($text, $top = 5, $return_array = false, $sep = ',')
{
	if (!class_exists('pscws4')) {
		include dirname(__FILE__) . '/pscws4/pscws4.php';
	}

	$cws = new pscws4('utf-8');
	$cws->set_charset('utf-8');
	$cws->set_dict(ROOT_PATH . 'includes/pscws4/etc/dict.utf8.xdb');
	$cws->set_rule(ROOT_PATH . 'includes/pscws4/etc/rules.utf8.ini');
	$cws->set_ignore(true);
	$cws->send_text($text);
	$ret = $cws->get_tops($top, 'r,v,p');
	$result = NULL;

	foreach ($ret as $value) {
		if (false === $return_array) {
			$result .= $sep . $value['word'];
		}
		else {
			$result[] = $value['word'];
		}
	}

	return false === $return_array ? substr($result, 1) : $result;
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
