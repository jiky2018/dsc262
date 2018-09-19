<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function get_user_orderinfo($is_pagination = true)
{
	global $db;
	global $ecs;
	global $start_date;
	global $end_date;
	$adminru = get_admin_ru_id();
	$ruCat = '';
	$leftJoin = '';
	$on = '';

	if (0 < $adminru['ru_id']) {
		$ruCat = ' and g.user_id = \'' . $adminru['ru_id'] . '\'';
		$leftJoin = ',' . $GLOBALS['ecs']->table('order_goods') . ' as og' . ',' . $GLOBALS['ecs']->table('goods') . ' as g ';
		$on = ' and og.goods_id = g.goods_id and o.order_id = og.goods_id ';
	}

	$filter['start_date'] = empty($_REQUEST['start_date']) ? $start_date : local_strtotime($_REQUEST['start_date']);
	$filter['end_date'] = empty($_REQUEST['end_date']) ? $end_date : local_strtotime($_REQUEST['end_date']);
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'order_num' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$where = 'WHERE u.user_id = o.user_id ' . 'AND u.user_id > 0 ' . order_query_sql('finished', 'o.');

	if ($filter['start_date']) {
		$where .= ' AND o.add_time >= \'' . $filter['start_date'] . '\'';
	}

	if ($filter['end_date']) {
		$where .= ' AND o.add_time <= \'' . $filter['end_date'] . '\'';
	}

	$where .= $ruCat;
	$sql = 'SELECT count(distinct(u.user_id)) FROM ' . $ecs->table('users') . ' AS u, ' . $ecs->table('order_info') . ' AS o ' . $leftJoin . $where . $on;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$total_fee = ' SUM(' . order_amount_field() . ') AS turnover ';
	$sql = 'SELECT u.user_id, u.user_name, COUNT(*) AS order_num, ' . $total_fee . 'FROM ' . $ecs->table('users') . ' AS u, ' . $ecs->table('order_info') . ' AS o ' . $leftJoin . $where . $on . ' GROUP BY u.user_id' . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'];

	if ($is_pagination) {
		$sql .= ' LIMIT ' . $filter['start'] . ', ' . $filter['page_size'];
	}

	$user_orderinfo = array();
	$res = $db->query($sql);

	while ($items = $db->fetchRow($res)) {
		$items['turnover'] = $items['turnover'];
		$user_orderinfo[] = $items;
	}

	$arr = array('user_orderinfo' => $user_orderinfo, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/statistic.php';
$smarty->assign('lang', $_LANG);
if (isset($_REQUEST['act']) && ($_REQUEST['act'] == 'query' || $_REQUEST['act'] == 'download')) {
	check_authz_json('client_flow_stats');

	if (strstr($_REQUEST['start_date'], '-') === false) {
		$_REQUEST['start_date'] = local_date('Y-m-d', $_REQUEST['start_date']);
		$_REQUEST['end_date'] = local_date('Y-m-d', $_REQUEST['end_date']);
	}

	if ($_REQUEST['act'] == 'download') {
		$user_orderinfo = get_user_orderinfo(false);
		$filename = $_REQUEST['start_date'] . '_' . $_REQUEST['end_date'] . 'users_order';
		header('Content-type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename . '.xls');
		$data = $_LANG['visit_buy'] . "\t\n";
		$data .= $_LANG['order_by'] . '	' . $_LANG['member_name'] . '	' . $_LANG['order_amount'] . '	' . $_LANG['buy_sum'] . "\t\n";

		foreach ($user_orderinfo['user_orderinfo'] as $k => $row) {
			$order_by = $k + 1;
			$data .= $order_by . '	' . $row['user_name'] . '	' . $row['order_num'] . '	' . $row['turnover'] . "\n";
		}

		echo ecs_iconv(EC_CHARSET, 'GB2312', $data);
		exit();
	}

	$user_orderinfo = get_user_orderinfo();
	$smarty->assign('filter', $user_orderinfo['filter']);
	$smarty->assign('record_count', $user_orderinfo['record_count']);
	$smarty->assign('page_count', $user_orderinfo['page_count']);
	$smarty->assign('user_orderinfo', $user_orderinfo['user_orderinfo']);
	$sort_flag = sort_flag($user_orderinfo['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('users_order.dwt'), '', array('filter' => $user_orderinfo['filter'], 'page_count' => $user_orderinfo['page_count']));
}
else {
	admin_priv('client_flow_stats');

	if (!isset($_REQUEST['start_date'])) {
		$start_date = local_strtotime('-7 days');
	}

	if (!isset($_REQUEST['end_date'])) {
		$end_date = local_strtotime('today');
	}

	$user_orderinfo = get_user_orderinfo();
	$smarty->assign('ur_here', $_LANG['report_users']);
	$smarty->assign('action_link', array('text' => $_LANG['download_amount_sort'], 'href' => '#download'));
	$smarty->assign('filter', $user_orderinfo['filter']);
	$smarty->assign('record_count', $user_orderinfo['record_count']);
	$smarty->assign('page_count', $user_orderinfo['page_count']);
	$smarty->assign('user_orderinfo', $user_orderinfo['user_orderinfo']);
	$smarty->assign('full_page', 1);
	$smarty->assign('start_date', local_date('Y-m-d H:i:s', $start_date));
	$smarty->assign('end_date', local_date('Y-m-d H:i:s', $end_date));
	$smarty->assign('sort_order_num', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->display('users_order.dwt');
}

?>
