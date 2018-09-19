<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_sale_list($is_pagination = true)
{
	$filter['start_date'] = empty($_REQUEST['start_date']) ? local_strtotime('-7 days') : local_strtotime($_REQUEST['start_date']);
	$filter['end_date'] = empty($_REQUEST['end_date']) ? local_strtotime('today') : local_strtotime($_REQUEST['end_date']);
	$filter['goods_sn'] = empty($_REQUEST['goods_sn']) ? '' : trim($_REQUEST['goods_sn']);
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'og.goods_number' : trim($_REQUEST['sort_by']);
	$filter['order_status'] = isset($_REQUEST['order_status']) && !($_REQUEST['order_status'] == '') ? explode(',', $_REQUEST['order_status']) : '';
	$filter['shipping_status'] = isset($_REQUEST['shipping_status']) && !($_REQUEST['shipping_status'] == '') ? explode(',', $_REQUEST['shipping_status']) : '';
	$filter['time_type'] = !empty($_REQUEST['time_type']) ? intval($_REQUEST['time_type']) : 0;
	$filter['order_referer'] = empty($_REQUEST['order_referer']) ? '' : trim($_REQUEST['order_referer']);
	$where = ' WHERE 1 ';
	$where .= ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id) = 0 AND oi.order_id = og.order_id ';
	$adminru = get_admin_ru_id();
	$leftJoin = '';

	if (0 < $adminru['ru_id']) {
		$where .= ' and og.ru_id = \'' . $adminru['ru_id'] . '\'';
	}

	if ($filter['goods_sn']) {
		$where .= ' AND og.goods_sn = \'' . $filter['goods_sn'] . '\'';
	}

	if ($filter['time_type'] == 1) {
		$where .= ' AND oi.add_time >= \'' . $filter['start_date'] . '\' AND oi.add_time < \'' . $filter['end_date'] . '\'';
	}
	else {
		$where .= ' AND oi.shipping_time >= \'' . $filter['start_date'] . '\' AND oi.shipping_time <= \'' . $filter['end_date'] . '\'';
	}

	if (!empty($filter['order_status'])) {
		$where .= ' AND oi.order_status ' . db_create_in($filter['order_status']);
	}

	if (!empty($filter['shipping_status'])) {
		$where .= ' AND oi.shipping_status ' . db_create_in($filter['shipping_status']);
	}

	if ($filter['order_referer']) {
		if ($filter['order_referer'] == 'pc') {
			$where .= ' AND oi.referer NOT IN (\'mobile\',\'touch\',\'ecjia-cashdesk\') ';
		}
		else {
			$where .= ' AND oi.referer = \'' . $filter['order_referer'] . '\' ';
		}
	}

	$sql = 'SELECT COUNT(og.goods_id) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi,' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . $leftJoin . $where . $on;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT og.goods_id, og.goods_sn, og.goods_name, og.goods_number AS goods_num, og.ru_id, og.goods_price ' . 'AS sales_price, oi.add_time AS sales_time, oi.order_id, oi.order_sn, (og.goods_number * og.goods_price) AS total_fee ' . 'FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og, ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ' . $leftJoin . $where . $on . (' ORDER BY ' . $filter['sort_by'] . ' DESC');

	if ($is_pagination) {
		$sql .= ' LIMIT ' . $filter['start'] . ', ' . $filter['page_size'];
	}

	$sale_list_data = $GLOBALS['db']->getAll($sql);

	foreach ($sale_list_data as $key => $item) {
		$sale_list_data[$key]['shop_name'] = get_shop_name($sale_list_data[$key]['ru_id'], 1);
		$sale_list_data[$key]['sales_price'] = $sale_list_data[$key]['sales_price'];
		$sale_list_data[$key]['total_fee'] = $sale_list_data[$key]['total_fee'];
		$sale_list_data[$key]['sales_time'] = local_date($GLOBALS['_CFG']['time_format'], $sale_list_data[$key]['sales_time']);
	}

	$arr = array('sale_list_data' => $sale_list_data, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_status_list($type = 'all')
{
	global $_LANG;
	$list = array();
	if ($type == 'all' || $type == 'order') {
		$pre = $type == 'all' ? 'os_' : '';

		foreach ($_LANG['os'] as $key => $value) {
			$list[$pre . $key] = $value;
		}
	}

	if ($type == 'all' || $type == 'shipping') {
		$pre = $type == 'all' ? 'ss_' : '';

		foreach ($_LANG['ss'] as $key => $value) {
			$list[$pre . $key] = $value;
		}
	}

	if ($type == 'all' || $type == 'payment') {
		$pre = $type == 'all' ? 'ps_' : '';

		foreach ($_LANG['ps'] as $key => $value) {
			$list[$pre . $key] = $value;
		}
	}

	return $list;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/statistic.php';
$smarty->assign('lang', $_LANG);
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('primary_cat', $_LANG['06_stats']);
if (isset($_REQUEST['act']) && ($_REQUEST['act'] == 'query' || $_REQUEST['act'] == 'download')) {
	check_authz_json('sale_order_stats');

	if (strstr($_REQUEST['start_date'], '-') === false) {
		$_REQUEST['start_date'] = local_date('Y-m-d H:i:s', $_REQUEST['start_date']);
		$_REQUEST['end_date'] = local_date('Y-m-d H:i:s', $_REQUEST['end_date']);
	}

	if ($_REQUEST['act'] == 'download') {
		$file_name = str_replace(' ', '--', $_REQUEST['start_date'] . '_' . $_REQUEST['end_date'] . '_sale');
		$goods_sales_list = get_sale_list(false);
		header('Content-type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $file_name . '.xls');
		echo ecs_iconv(EC_CHARSET, 'GB2312', $_REQUEST['start_date'] . $_LANG['to'] . $_REQUEST['end_date'] . $_LANG['sales_list']) . "\t\n";
		echo ecs_iconv(EC_CHARSET, 'GB2312', '商家名称') . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', '货号') . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['goods_name']) . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['order_sn']) . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['amount']) . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['sell_price']) . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', '总金额') . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['sell_date']) . "\t\n";

		foreach ($goods_sales_list['sale_list_data'] as $key => $value) {
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['shop_name']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['goods_sn']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['goods_name']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', '[ ' . $value['order_sn'] . ' ]') . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['goods_num']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['sales_price']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['total_fee']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['sales_time']) . '	';
			echo "\n";
		}

		exit();
	}

	$sale_list_data = get_sale_list();
	$page_count_arr = seller_page($sale_list_data, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('goods_sales_list', $sale_list_data['sale_list_data']);
	$smarty->assign('filter', $sale_list_data['filter']);
	$smarty->assign('record_count', $sale_list_data['record_count']);
	$smarty->assign('page_count', $sale_list_data['page_count']);
	make_json_result($smarty->fetch('sale_list.dwt'), '', array('filter' => $sale_list_data['filter'], 'page_count' => $sale_list_data['page_count']));
}
else {
	admin_priv('sale_order_stats');
	$smarty->assign('current', 'sale_list');

	if (!isset($_REQUEST['start_date'])) {
		$start_date = local_strtotime('-7 days');
	}

	if (!isset($_REQUEST['end_date'])) {
		$end_date = local_strtotime('today');
	}

	$sale_list_data = get_sale_list();
	$page_count_arr = seller_page($sale_list_data, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('filter', $sale_list_data['filter']);
	$smarty->assign('record_count', $sale_list_data['record_count']);
	$smarty->assign('page_count', $sale_list_data['page_count']);
	$smarty->assign('goods_sales_list', $sale_list_data['sale_list_data']);
	$smarty->assign('ur_here', $_LANG['sell_stats']);
	$smarty->assign('full_page', 1);
	$smarty->assign('start_date', local_date('Y-m-d H:i:s', $start_date));
	$smarty->assign('end_date', local_date('Y-m-d H:i:s', $end_date));
	$smarty->assign('ur_here', $_LANG['sale_list']);
	$smarty->assign('cfg_lang', $_CFG['lang']);
	$smarty->assign('action_link', array('text' => $_LANG['down_sales'], 'href' => '#download', 'class' => 'icon-download-alt'));
	$smarty->assign('os_list', get_status_list('order'));
	$smarty->assign('ss_list', get_status_list('shipping'));
	assign_query_info();
	$smarty->display('sale_list.dwt');
}

?>
