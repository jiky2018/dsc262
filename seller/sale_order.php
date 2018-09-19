<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_sales_order($ru_id, $is_pagination = true)
{
	global $start_date;
	global $end_date;
	$filter['start_date'] = empty($_REQUEST['start_date']) ? $start_date : local_strtotime($_REQUEST['start_date']);
	$filter['end_date'] = empty($_REQUEST['end_date']) ? $end_date : local_strtotime($_REQUEST['end_date']);
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'goods_num' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$where = $where_record = ' WHERE og.order_id = oi.order_id ' . order_query_sql('finished', 'oi.');

	if ($filter['start_date']) {
		$where .= ' AND oi.add_time >= \'' . $filter['start_date'] . '\'';
	}

	if ($filter['end_date']) {
		$where .= ' AND oi.add_time <= \'' . $filter['end_date'] . '\'';
	}

	$leftJoin = '';

	if (0 < $ru_id) {
		$where .= ' AND og.ru_id = \'' . $ru_id . '\'';
	}

	$sql = 'SELECT COUNT(distinct(og.goods_id)) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi,' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT og.goods_id, og.goods_sn, og.goods_name, oi.order_status, ' . 'SUM(og.goods_number) AS goods_num, SUM(og.goods_number * og.goods_price) AS turnover, og.ru_id ' . 'FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og, ' . $GLOBALS['ecs']->table('order_info') . ' AS oi  ' . $leftJoin . $where . ' GROUP BY og.goods_id ' . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'];

	if ($is_pagination) {
		$sql .= ' LIMIT ' . $filter['start'] . ', ' . $filter['page_size'];
	}

	$sales_order_data = $GLOBALS['db']->getAll($sql);

	foreach ($sales_order_data as $key => $item) {
		$sales_order_data[$key]['wvera_price'] = price_format($item['goods_num'] ? $item['turnover'] / $item['goods_num'] : 0);
		$sales_order_data[$key]['short_name'] = sub_str($item['goods_name'], 30, true);
		$sales_order_data[$key]['turnover'] = price_format($item['turnover']);
		$sales_order_data[$key]['taxis'] = $key + 1;
		$sales_order_data[$key]['ru_name'] = get_shop_name($item['ru_id'], 1);
	}

	$arr = array('sales_order_data' => $sales_order_data, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/statistic.php';
$smarty->assign('lang', $_LANG);
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('primary_cat', $_LANG['06_stats']);
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if (isset($_REQUEST['act']) && (($_REQUEST['act'] == 'query') || ($_REQUEST['act'] == 'download'))) {
	check_authz_json('sale_order_stats');

	if (strstr($_REQUEST['start_date'], '-') === false) {
		$_REQUEST['start_date'] = local_date('Y-m-d', $_REQUEST['start_date']);
		$_REQUEST['end_date'] = local_date('Y-m-d', $_REQUEST['end_date']);
	}

	if ($_REQUEST['act'] == 'download') {
		$goods_order_data = get_sales_order($adminru['ru_id'], false);
		$goods_order_data = $goods_order_data['sales_order_data'];
		$filename = $_REQUEST['start_date'] . '_' . $_REQUEST['end_date'] . 'sale_order';
		header('Content-type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename . '.xls');
		$data = $_LANG['sell_stats'] . "\t\n";
		$data .= $_LANG['order_by'] . '	' . $_LANG['goods_name'] . '	' . $_LANG['goods_steps_name'] . '	' . $_LANG['goods_sn'] . '	' . $_LANG['sell_amount'] . '	' . $_LANG['sell_sum'] . '	' . $_LANG['percent_count'] . "\n";

		foreach ($goods_order_data as $k => $row) {
			$order_by = $k + 1;
			$data .= $order_by . '	' . $row['goods_name'] . '	' . $row['ru_name'] . '	' . $row['goods_sn'] . '	' . $row['goods_num'] . '	' . $row['turnover'] . '	' . $row['wvera_price'] . "\n";
		}

		if (EC_CHARSET == 'utf-8') {
			echo ecs_iconv(EC_CHARSET, 'GB2312', $data);
		}
		else {
			echo $data;
		}

		exit();
	}

	$goods_order_data = get_sales_order($adminru['ru_id']);
	$page_count_arr = seller_page($goods_order_data, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('goods_order_data', $goods_order_data['sales_order_data']);
	$smarty->assign('filter', $goods_order_data['filter']);
	$smarty->assign('record_count', $goods_order_data['record_count']);
	$smarty->assign('page_count', $goods_order_data['page_count']);
	$sort_flag = sort_flag($goods_order_data['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('sale_order.dwt'), '', array('filter' => $goods_order_data['filter'], 'page_count' => $goods_order_data['page_count']));
}
else {
	admin_priv('sale_order_stats');
	$smarty->assign('current', 'goods_num');

	if (!isset($_REQUEST['start_date'])) {
		$start_date = local_strtotime('-7 day');
	}

	if (!isset($_REQUEST['end_date'])) {
		$end_date = local_strtotime('today');
	}

	$goods_order_data = get_sales_order($adminru['ru_id']);
	$page_count_arr = seller_page($goods_order_data, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('ur_here', $_LANG['sell_stats']);
	$smarty->assign('goods_order_data', $goods_order_data['sales_order_data']);
	$smarty->assign('filter', $goods_order_data['filter']);
	$smarty->assign('record_count', $goods_order_data['record_count']);
	$smarty->assign('page_count', $goods_order_data['page_count']);
	$smarty->assign('filter', $goods_order_data['filter']);
	$smarty->assign('full_page', 1);
	$smarty->assign('sort_goods_num', '<img src="images/sort_desc.gif">');
	$smarty->assign('start_date', local_date('Y-m-d', $start_date));
	$smarty->assign('end_date', local_date('Y-m-d', $end_date));
	$smarty->assign('action_link', array('text' => $_LANG['download_sale_sort'], 'href' => '#download', 'class' => 'icon-download-alt'));
	assign_query_info();
	$smarty->display('sale_order.dwt');
}

?>
