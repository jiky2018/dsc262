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
$smarty->assign('area_list', get_areaRegion_list());

if ($_REQUEST['act'] == 'new') {
	$smarty->assign('ur_here', $_LANG['newadd_user']);
	$smarty->display('new_user_stats.dwt');
}
else if ($_REQUEST['act'] == 'get_chart_data') {
	$search_data = array();
	$search_data['start_date'] = $start_date;
	$search_data['end_date'] = $end_date;
	$chart_data = get_statistical_new_user($search_data);
	make_json_result($chart_data);
}
else if ($_REQUEST['act'] == 'user_analysis') {
	$order_list = user_sale_stats();
	$smarty->assign('full_page', 1);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['user_analysis']);
	$smarty->display('user_analysis.dwt');
}
else if ($_REQUEST['act'] == 'user_analysis_query') {
	$order_list = user_sale_stats();
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('user_analysis.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}
else if ($_REQUEST['act'] == 'user_area_analysis') {
	$order_list = user_area_stats();
	$smarty->assign('full_page', 1);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['user_area_analysis']);
	$smarty->display('user_area_analysis.dwt');
}
else if ($_REQUEST['act'] == 'user_area_analysis_query') {
	$order_list = user_area_stats();
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('user_area_analysis.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}
else if ($_REQUEST['act'] == 'user_rank_analysis') {
	$user_rank = get_statistical_user_rank();
	$smarty->assign('user_rank', $user_rank['source']);
	$smarty->assign('json_data', json_encode($user_rank));
	$smarty->assign('ur_here', $_LANG['user_rank_analysis']);
	$smarty->display('user_rank_analysis.dwt');
}
else if ($_REQUEST['act'] == 'user_consumption_rank') {
	$order_list = user_sale_stats();
	$smarty->assign('full_page', 1);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['user_consumption_rank']);
	$smarty->display('user_consumption_rank.dwt');
}
else if ($_REQUEST['act'] == 'user_consumption_rank_query') {
	$order_list = user_sale_stats();
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('user_consumption_rank.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}
else if ($_REQUEST['act'] == 'download_area') {
	$_GET['uselastfilter'] = 1;
	$order_list = user_area_stats();
	$tdata = $order_list['orders'];
	$thead = array($_LANG['province_alt'], $_LANG['city'], $_LANG['area_alt'], $_LANG['user_sale_stats'][0], '下单总金额', $_LANG['user_sale_stats'][1]);
	$tbody = array('province_name', 'city_name', 'district_name', 'user_num', 'total_fee', 'total_num');
	$config = array('filename' => $_LANG['user_area_analysis'], 'thead' => $thead, 'tbody' => $tbody, 'tdata' => $tdata);
	list_download($config);
}
else if ($_REQUEST['act'] == 'download_rank') {
	$_GET['uselastfilter'] = 1;
	$order_list = user_sale_stats();
	$tdata = $order_list['orders'];
	$thead = array($_LANG['record_id'], $_LANG['user_name'], $_LANG['user_sale_stats'][2], $_LANG['user_sale_stats'][3], $_LANG['user_sale_stats'][4], $_LANG['user_sale_stats'][5], $_LANG['user_sale_stats'][6], $_LANG['user_sale_stats'][7]);
	$tbody = array('user_id', 'user_name', 'total_num', 'total_fee', 'valid_num', 'valid_fee', 'return_num', 'return_fee');
	$config = array('filename' => $_LANG['user_consumption_rank'], 'thead' => $thead, 'tbody' => $tbody, 'tdata' => $tdata);
	list_download($config);
}

?>
