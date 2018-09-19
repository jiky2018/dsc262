<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
admin_priv('sms_setting');

if ($_REQUEST['act'] == 'step_up') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/shop_config.php';
	$smarty->assign('ur_here', $_LANG['01_sms_setting']);
	$smarty->assign('menu_select', array('action' => '24_sms', 'current' => '01_sms_setting'));
	$group_list = get_up_settings('sms');
	$smarty->assign('group_list', $group_list);
	assign_query_info();
	$smarty->display('sms_step_up.dwt');
}

?>
