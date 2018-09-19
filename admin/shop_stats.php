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
$smarty->assign('main_category', cat_list());
$smarty->assign('store_type', $_LANG['store_type']);
$smarty->assign('area_list', get_areaRegion_list());

if ($_REQUEST['act'] == 'new') {
	$smarty->assign('ur_here', $_LANG['newadd_shop']);
	$smarty->display('new_shop_stats.dwt');
}
else if ($_REQUEST['act'] == 'get_chart_data') {
	$search_data = array();
	$search_data['start_date'] = $start_date;
	$search_data['end_date'] = $end_date;
	$search_data['shop_categoryMain'] = empty($_REQUEST['shop_categoryMain']) ? 0 : intval($_REQUEST['shop_categoryMain']);
	$search_data['shopNameSuffix'] = empty($_REQUEST['shopNameSuffix']) ? '' : trim($_REQUEST['shopNameSuffix']);
	$chart_data = get_statistical_new_shop($search_data);
	make_json_result($chart_data);
}
else if ($_REQUEST['act'] == 'shop_sale_stats') {
	$smarty->assign('total_stats', shop_total_stats());
	$order_list = shop_sale_stats();
	$smarty->assign('full_page', 1);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['shop_sale_stats']);
	$smarty->display('shop_sale_stats.dwt');
}

if ($_REQUEST['act'] == 'shop_sale_stats_query') {
	$order_list = shop_sale_stats();
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('shop_sale_stats.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}

if ($_REQUEST['act'] == 'get_total_stats') {
	$total_stats = shop_total_stats();
	make_json_result('', '', $total_stats);
}
else if ($_REQUEST['act'] == 'shop_area') {
	$order_list = shop_area_stats();
	$smarty->assign('full_page', 1);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['shop_area_distribution']);
	$smarty->display('shop_area_distribution.dwt');
}
else if ($_REQUEST['act'] == 'shop_area_query') {
	$order_list = shop_area_stats();
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('shop_area_distribution.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}
else if ($_REQUEST['act'] == 'get_area_chart_data') {
	$search_data = array();
	$search_data['start_date'] = empty($_REQUEST['start_date']) ? '' : (0 < strpos($_REQUEST['start_date'], '-') ? local_strtotime($_REQUEST['start_date']) : $_REQUEST['start_date']);
	$search_data['end_date'] = empty($_REQUEST['end_date']) ? '' : (0 < strpos($_REQUEST['end_date'], '-') ? local_strtotime($_REQUEST['end_date']) : $_REQUEST['end_date']);
	$search_data['area'] = empty($_REQUEST['area']) ? 0 : intval($_REQUEST['area']);
	$search_data['shop_categoryMain'] = empty($_REQUEST['shop_categoryMain']) ? 0 : intval($_REQUEST['shop_categoryMain']);
	$search_data['shopNameSuffix'] = empty($_REQUEST['shopNameSuffix']) ? '' : trim($_REQUEST['shopNameSuffix']);
	$chart_data = get_statistical_shop_area($search_data);
	make_json_result($chart_data);
}
else if ($_REQUEST['act'] == 'download') {
	$_GET['uselastfilter'] = 1;
	$order_list = shop_sale_stats();
	$tdata = $order_list['orders'];
	$thead = array($_LANG['record_id'], $_LANG['steps_shop_name'], $_LANG['sale_stats'][0], $_LANG['sale_stats'][1], $_LANG['sale_stats'][2], $_LANG['sale_stats'][3], $_LANG['sale_stats'][4], $_LANG['sale_stats'][5], $_LANG['sale_stats'][6]);
	$tbody = array('ru_id', 'user_name', 'total_user_num', 'total_order_num', 'total_fee', 'total_valid_num', 'valid_fee', 'total_return_num', 'return_amount');
	$config = array('filename' => $_LANG['shop_sale_stats'], 'thead' => $thead, 'tbody' => $tbody, 'tdata' => $tdata);
	list_download($config);
}
else if ($_REQUEST['act'] == 'download_area') {
	$_GET['uselastfilter'] = 1;
	$order_list = shop_area_stats();
	$tdata = $order_list['orders'];
	$thead = array($_LANG['province_alt'], $_LANG['city'], $_LANG['area_alt'], $_LANG['shop_number']);
	$tbody = array('province_name', 'city_name', 'district_name', 'store_num');
	$config = array('filename' => $_LANG['shop_area_distribution'], 'thead' => $thead, 'tbody' => $tbody, 'tdata' => $tdata);
	list_download($config);
}

?>
