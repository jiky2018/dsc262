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

if ($_REQUEST['act'] == 'summary_of_money') {
	$today = local_strtotime(local_date('Y-m-d'));
	$start_date = $today;
	$end_date = $today + 86400;
	$search_data = array();
	$search_data['start_date'] = $start_date;
	$search_data['end_date'] = $end_date;
	$today_sale = get_statistical_today_sale($search_data);
	$smarty->assign('today_sale', $today_sale);
	$smarty->assign('update_time', local_date('Y-m-d H:i:s', gmtime()));
	$smarty->display('summary_of_money.dwt');
}
else if ($_REQUEST['act'] == 'get_chart_data') {
	$today = local_strtotime(local_date('Y-m-d'));
	$start_date = $today;
	$end_date = $today + 86400;
	$search_data = array();
	$search_data['start_date'] = $start_date;
	$search_data['end_date'] = $end_date;
	$chart_data = get_statistical_today_trend($search_data);
	make_json_result($chart_data);
}
else if ($_REQUEST['act'] == 'member_account') {
	$smarty->assign('total_stats', shop_total_stats());
	$order_list = member_account_stats();
	$smarty->assign('full_page', 1);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	assign_query_info();
	$smarty->display('member_account.dwt');
}

if ($_REQUEST['act'] == 'member_account_query') {
	$order_list = member_account_stats();
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('member_account.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}
else if ($_REQUEST['act'] == 'recharge_management') {
	$smarty->display('recharge_management.dwt');
}
else if ($_REQUEST['act'] == 'cash_management') {
	$smarty->display('cash_management.dwt');
}
else if ($_REQUEST['act'] == 'download') {
	$_GET['uselastfilter'] = 1;
	$order_list = member_account_stats();
	$tdata = $order_list['orders'];
	$thead = array($_LANG['record_id'], $_LANG['user_desc'], $_LANG['user_rank'], '可用资金', '冻结资金');
	$tbody = array('user_id', 'user_name', 'rank_name', 'user_money', 'frozen_money');
	$config = array('filename' => $_LANG['02_member_account'], 'thead' => $thead, 'tbody' => $tbody, 'tdata' => $tdata);
	list_download($config);
}

?>
