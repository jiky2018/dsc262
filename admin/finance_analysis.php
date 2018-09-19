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

if ($_REQUEST['act'] == 'settlement_stats') {
	$smarty->assign('total_stats', settlement_total_stats());
	$order_list = merchants_commission_list();
	$smarty->assign('full_page', 1);
	$smarty->assign('order_list', $order_list['result']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	assign_query_info();
	$smarty->display('settlement_stats.dwt');
}

if ($_REQUEST['act'] == 'settlement_stats_query') {
	$order_list = merchants_commission_list();
	$smarty->assign('order_list', $order_list['result']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('settlement_stats.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}

if ($_REQUEST['act'] == 'settlement_total_stats') {
	$total_stats = settlement_total_stats();
	make_json_result('', '', $total_stats);
}
else if ($_REQUEST['act'] == 'balance_stats') {
	$smarty->assign('total_stats', balance_total_stats());
	$order_list = balance_stats();
	$smarty->assign('full_page', 1);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	assign_query_info();
	$smarty->display('balance_stats.dwt');
}
else if ($_REQUEST['act'] == 'balance_stats_query') {
	$order_list = balance_stats();
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('balance_stats.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}

if ($_REQUEST['act'] == 'balance_total_stats') {
	$total_stats = balance_total_stats();
	make_json_result('', '', $total_stats);
}
else if ($_REQUEST['act'] == 'download') {
	$_GET['uselastfilter'] = 1;
	$order_list = member_account_stats();
	$tdata = $order_list['orders'];
	$thead = array($_LANG['record_id'], $_LANG['user_name'], $_LANG['finance_analysis'][5], $_LANG['finance_analysis'][6], $_LANG['finance_analysis'][7], $_LANG['finance_analysis'][8], $_LANG['finance_analysis'][9]);
	$tbody = array('user_id', 'user_name', 'recharge_money', 'consumption_money', 'cash_money', 'return_money', 'user_money');
	$config = array('filename' => $_LANG['balance_stats'], 'thead' => $thead, 'tbody' => $tbody, 'tdata' => $tdata);
	list_download($config);
}
else if ($_REQUEST['act'] == 'download_settlement') {
	$_GET['uselastfilter'] = 1;
	$order_list = merchants_commission_list();
	$tdata = $order_list['result'];
	$thead = array($_LANG['record_id'], $_LANG['steps_shop_name'], $_LANG['finance_analysis'][1], $_LANG['finance_analysis'][2], $_LANG['finance_analysis'][3], $_LANG['finance_analysis'][4]);
	$tbody = array('user_id', 'store_name', 'valid_total', 'refund_total', 'platform_commission', 'is_settlement');
	$config = array('filename' => $_LANG['settlement_stats'], 'thead' => $thead, 'tbody' => $tbody, 'tdata' => $tdata);
	list_download($config);
}

?>
