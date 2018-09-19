<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . 'includes/lib_statistical.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/statistic.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if (isset($_REQUEST['start_date']) && !empty($_REQUEST['end_date'])) {
	$start_date = local_strtotime($_REQUEST['start_date']);
	$end_date = local_strtotime($_REQUEST['end_date']);

	if ($start_date == $end_date) {
		$end_date = $start_date + 86400;
	}
}
else {
	$today = local_strtotime(local_date('Y-m-d'));
	$start_date = $today - 86400 * 6;
	$end_date = $today + 86400;
}

$smarty->assign('start_date', local_date('Y-m-d H:i:s', $start_date));
$smarty->assign('end_date', local_date('Y-m-d H:i:s', $end_date));

if ($_REQUEST['act'] == 'sales_volume') {
	$no_main_order = no_main_order();
	$sql = ' SELECT ' . statistical_field_order_num() . ' AS total_num FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o WHERE 1 ' . $no_main_order;
	$total_num = $GLOBALS['db']->getOne($sql);
	$smarty->assign('total_num', $total_num);
	$smarty->assign('ur_here', $_LANG['sales_volume']);
	$smarty->display('sales_volume_stats.dwt');
}
else if ($_REQUEST['act'] == 'sales_money') {
	$no_main_order = no_main_order();
	$sql = ' SELECT ' . statistical_field_sale_money() . ' AS total_fee FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o WHERE 1 ' . $no_main_order;
	$total_fee = $GLOBALS['db']->getOne($sql);
	$smarty->assign('total_fee', $total_fee);
	$smarty->assign('ur_here', $_LANG['sales_money']);
	$smarty->display('sales_money_stats.dwt');
}
else if ($_REQUEST['act'] == 'get_chart_data') {
	$search_data = array();
	$search_data['start_date'] = $start_date;
	$search_data['end_date'] = $end_date;
	$search_data['type'] = empty($_REQUEST['type']) ? 'volume' : trim($_REQUEST['type']);
	$chart_data = get_statistical_sale($search_data);
	make_json_result($chart_data);
}
else if ($_REQUEST['act'] == 'order_stats') {
	$smarty->assign('ur_here', $_LANG['order_stats']);
	$smarty->display('sales_order_stats.dwt');
}

?>
