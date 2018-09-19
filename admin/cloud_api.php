<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_REQUEST['act'])) {
	exit('Error');
}
else if ($_REQUEST['act'] == 'config') {
	admin_priv('shop_config');
	$smarty->assign('ur_here', $_LANG['cloud_api']);
	$smarty->assign('form_act', 'cloud_update');
	$api_config = array();
	$api_config['client_id'] = get_table_date('shop_config', 'code=\'cloud_client_id\'', array('value'), 2);
	$api_config['appkey'] = get_table_date('shop_config', 'code=\'cloud_appkey\'', array('value'), 2);
	$api_config['cloud_dsc_appkey'] = get_table_date('shop_config', 'code=\'cloud_dsc_appkey\'', array('value'), 2);
	$smarty->assign('api_config', $api_config);
	assign_query_info();
	$smarty->display('cloud_api.dwt');
}
else if ($_REQUEST['act'] == 'cloud_update') {
	admin_priv('shop_config');
	$client_id = empty($_REQUEST['client_id']) ? '' : trim($_REQUEST['client_id']);
	$appkey = empty($_REQUEST['appkey']) ? '' : trim($_REQUEST['appkey']);
	$cloud_dsc_appkey = empty($_REQUEST['cloud_dsc_appkey']) ? '' : trim($_REQUEST['cloud_dsc_appkey']);
	$sql = ' UPDATE ' . $GLOBALS['ecs']->table('shop_config') . (' SET value = \'' . $client_id . '\' WHERE code = \'cloud_client_id\' ');
	$GLOBALS['db']->query($sql);
	$sql = ' UPDATE ' . $GLOBALS['ecs']->table('shop_config') . (' SET value = \'' . $appkey . '\' WHERE code = \'cloud_appkey\' ');
	$GLOBALS['db']->query($sql);
	$sql = ' UPDATE ' . $GLOBALS['ecs']->table('shop_config') . (' SET value = \'' . $cloud_dsc_appkey . '\' WHERE code = \'cloud_dsc_appkey\' ');
	$GLOBALS['db']->query($sql);
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'cloud_api.php?act=config');
	sys_msg($_LANG['save_success'], 0, $link);
}

?>
