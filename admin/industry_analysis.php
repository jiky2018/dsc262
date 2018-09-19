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

if ($_REQUEST['act'] == 'list') {
	$order_list = industry_analysis();
	$smarty->assign('full_page', 1);
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['04_industry_analysis']);
	$smarty->display('industry_analysis.dwt');
}

if ($_REQUEST['act'] == 'query') {
	$order_list = industry_analysis();
	$smarty->assign('order_list', $order_list['orders']);
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$sort_flag = sort_flag($order_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('industry_analysis.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}
else if ($_REQUEST['act'] == 'get_chart_data') {
	$search_data = array();
	$search_data['type'] = empty($_REQUEST['type']) ? '' : trim($_REQUEST['type']);
	$chart_data = get_statistical_industry_analysis($search_data);
	make_json_result($chart_data);
}
else if ($_REQUEST['act'] == 'download') {
	$_GET['uselastfilter'] = 1;
	$order_list = industry_analysis();
	$tdata = $order_list['orders'];
	$thead = array('商品分类', '销售额（元）', '有效销售额（元）', '总下单量', '有效下单量', '商品总数', '有销量商品数', '无销量商品数', '下单会员数');
	$tbody = array('cat_name', 'goods_amount', 'valid_goods_amount', 'order_num', 'valid_num', 'goods_num', 'order_goods_num', 'no_order_goods_num', 'user_num');
	$config = array('filename' => $_LANG['04_industry_analysis'], 'thead' => $thead, 'tbody' => $tbody, 'tdata' => $tdata);
	list_download($config);
}

?>
