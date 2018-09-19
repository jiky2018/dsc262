<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_visual.php';
get_invalid_apply(1);

if ($_REQUEST['act'] == 'list') {
	admin_priv('10_visual_editing');
	$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => 'template_mall'));
	$smarty->assign('ur_here', $_LANG['template_mall']);
	$template_mall_list = template_mall_list();
	$smarty->assign('available_templates', $template_mall_list['list']);
	$smarty->assign('filter', $template_mall_list['filter']);
	$smarty->assign('record_count', $template_mall_list['record_count']);
	$smarty->assign('page_count', $template_mall_list['page_count']);
	$smarty->assign('template_type', 'seller');
	$smarty->assign('full_page', 1);
	$smarty->assign('act_type', $_REQUEST['act']);
	assign_query_info();
	$smarty->display('visualhome_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$template_mall_list = template_mall_list();
	$smarty->assign('available_templates', $template_mall_list['list']);
	$smarty->assign('filter', $template_mall_list['filter']);
	$smarty->assign('record_count', $template_mall_list['record_count']);
	$smarty->assign('page_count', $template_mall_list['page_count']);
	$smarty->assign('template_type', 'seller');
	make_json_result($smarty->fetch('visualhome_list.dwt'), '', array('filter' => $template_mall_list['filter'], 'page_count' => $template_mall_list['page_count']));
}

if ($_REQUEST['act'] == 'template_apply_list') {
	$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => 'template_apply_list'));
	$smarty->assign('ur_here', $_LANG['template_apply_list']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$template_mall_list = get_template_apply_list();
	$smarty->assign('available_templates', $template_mall_list['list']);
	$smarty->assign('filter', $template_mall_list['filter']);
	$smarty->assign('record_count', $template_mall_list['record_count']);
	$smarty->assign('page_count', $template_mall_list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('act_type', $_REQUEST['act']);
	assign_query_info();
	$smarty->display('template_apply_list.dwt');
}
else if ($_REQUEST['act'] == 'apply_query') {
	$template_mall_list = get_template_apply_list();
	$smarty->assign('available_templates', $template_mall_list['list']);
	$smarty->assign('filter', $template_mall_list['filter']);
	$smarty->assign('record_count', $template_mall_list['record_count']);
	$smarty->assign('page_count', $template_mall_list['page_count']);
	make_json_result($smarty->fetch('template_apply_list.dwt'), '', array('filter' => $template_mall_list['filter'], 'page_count' => $template_mall_list['page_count']));
}
else if ($_REQUEST['act'] == 'confirm_operation') {
	$apply_id = (!empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$sql = 'SELECT ru_id,temp_id,temp_code FROM' . $GLOBALS['ecs']->table('seller_template_apply') . 'WHERE apply_id = \'' . $apply_id . '\'';
	$seller_template_apply = $GLOBALS['db']->getRow($sql);
	$new_suffix = get_new_dirName($seller_template_apply['ru_id']);
	Import_temp($seller_template_apply['temp_code'], $new_suffix, $seller_template_apply['ru_id']);
	$sql = 'UPDATE' . $GLOBALS['ecs']->table('template_mall') . 'SET sales_volume = sales_volume+1 WHERE temp_id = \'' . $seller_template_apply['temp_id'] . '\'';
	$GLOBALS['db']->query($sql);
	$sql = ' UPDATE ' . $GLOBALS['ecs']->table('seller_template_apply') . ' SET pay_status = 1 ,pay_time = \'' . gmtime() . '\' , apply_status = 1 WHERE apply_id= \'' . $apply_id . '\'';
	$GLOBALS['db']->query($sql);
	$sql = 'UPDATE ' . $ecs->table('pay_log') . 'SET is_paid = 1 WHERE order_id = \'' . $apply_id . '\' AND order_type = \'' . PAY_APPLYTEMP . '\'';
	$db->query($sql);
	$url = 'template_mall.php?act=apply_query&' . str_replace('act=confirm_operation', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'remove') {
	$apply_id = (!empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$sql = 'DELETE FROM' . $ecs->table('seller_template_apply') . 'WHERE apply_id = \'' . $apply_id . '\' AND pay_status = 0';
	$db->query($sql);
	$url = 'template_mall.php?act=apply_query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

?>
