<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$cron_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/cron/ipdel.php';

if (file_exists($cron_lang)) {
	global $_LANG;
	include_once $cron_lang;
}

if (isset($set_modules) && ($set_modules == true)) {
	$i = (isset($modules) ? count($modules) : 0);
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['desc'] = 'ipdel_desc';
	$modules[$i]['author'] = 'ECSHOP TEAM';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['config'] = array(
	array('name' => 'ipdel_day', 'type' => 'select', 'value' => '30')
	);
	return NULL;
}

empty($cron['ipdel_day']) && $cron['ipdel_day'] = 7;
$deltime = gmtime() - ($cron['ipdel_day'] * 3600 * 24);
$sql = 'DELETE FROM ' . $ecs->table('stats') . 'WHERE  access_time < \'' . $deltime . '\'';
$db->query($sql);

?>
