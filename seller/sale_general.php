<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_data_list($type = 0)
{
	$leftJoin = '';
	$adminru = get_admin_ru_id();

	if ($type != 0) {
		$result = get_filter();

		if ($result === false) {
			$filter['keyword'] = !isset($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
			if (!empty($_GET['is_ajax']) && ($_GET['is_ajax'] == 1)) {
				$_REQUEST['keyword'] = json_str_iconv($_REQUEST['keyword']);
			}

			$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'goods_number' : trim($_REQUEST['sort_by']);
			$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
			$filter['time_type'] = isset($_REQUEST['time_type']) ? intval($_REQUEST['time_type']) : 1;
			$filter['date_start_time'] = !empty($_REQUEST['date_start_time']) ? trim($_REQUEST['date_start_time']) : '';
			$filter['date_end_time'] = !empty($_REQUEST['date_end_time']) ? trim($_REQUEST['date_end_time']) : '';
			$filter['cat_name'] = !empty($_REQUEST['cat_name']) ? trim($_REQUEST['cat_name']) : '';
			$filter['order_status'] = isset($_REQUEST['order_status']) ? $_REQUEST['order_status'] : -1;
			$filter['shipping_status'] = isset($_REQUEST['shipping_status']) ? $_REQUEST['shipping_status'] : -1;
			$goods_where = 1;

			if (!empty($filter['cat_name'])) {
				$sql = 'SELECT cat_id FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE cat_name = \'' . $filter['cat_name'] . '\'';
				$cat_id = $GLOBALS['db']->getOne($sql);
				$goods_where .= ' AND g.cat_id = \'' . $cat_id . '\'';
			}

			$where_order = 1;
			if (($filter['date_start_time'] == '') && ($filter['date_end_time'] == '')) {
				$start_time = local_mktime(0, 0, 0, local_date('m'), 1, local_date('Y'));
				$end_time = (local_mktime(0, 0, 0, local_date('m'), local_date('t'), local_date('Y')) + (24 * 60 * 60)) - 1;
			}
			else {
				$start_time = local_strtotime($filter['date_start_time']);
				$end_time = local_strtotime($filter['date_end_time']);
			}

			if (!empty($filter['cat_name'])) {
				$where_order .= ' AND (SELECT g.cat_id FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g WHERE g.goods_id = og.goods_id LIMIT 1) = \'' . $cat_id . '\'';
			}

			if ($filter['time_type'] == 1) {
				$where_order .= ' AND o.add_time >= \'' . $start_time . '\' AND o.add_time <= \'' . $end_time . '\'';
			}
			else {
				$where_order .= ' AND o.shipping_time >= \'' . $start_time . '\' AND o.shipping_time <= \'' . $end_time . '\'';
			}

			if (-1 < $filter['order_status']) {
				$order_status = $filter['order_status'];
				$where_order .= ' AND o.order_status IN(' . $order_status . ')';
			}

			if (-1 < $filter['shipping_status']) {
				$shipping_status = $filter['shipping_status'];
				$where_order .= ' AND o.shipping_status IN(' . $shipping_status . ')';
			}

			if (0 < $adminru['ru_id']) {
				$goods_where .= ' AND g.user_id = \'' . $adminru['ru_id'] . '\'';
				$where_order .= ' AND og.ru_id = \'' . $adminru['ru_id'] . '\'';
			}

			$where_order .= ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g WHERE ' . $goods_where . ' AND (SELECT og.goods_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS o ON og.order_id = o.order_id' . ' WHERE ' . $where_order . ' AND og.goods_id = g.goods_id LIMIT 1) > 0';
			$filter['record_count'] = $GLOBALS['db']->getOne($sql);
			$filter = page_and_size($filter);
			$sql = 'SELECT og.goods_id, og.order_id, og.goods_id, og.goods_name, og.ru_id, og.goods_sn, og.goods_price, o.add_time, o.shipping_time, ' . 'SUM(og.goods_price * og.goods_number) AS total_fee, SUM(og.goods_number) AS goods_number, GROUP_CONCAT(o.order_id) AS order_id ' . ' FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . ' ON o.order_id = og.order_id ' . ' WHERE ' . $where_order . ' GROUP BY og.goods_id' . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ' . ' LIMIT ' . (($filter['page'] - 1) * $filter['page_size']) . ',' . $filter['page_size'];
			set_filter($filter, $sql);
		}
		else {
			$sql = $result['sql'];
			$filter = $result['filter'];
		}
	}

	$data_list = $GLOBALS['db']->getAll($sql);

	if ($type != 0) {
		for ($i = 0; $i < count($data_list); $i++) {
			$data_list[$i]['order_id'] = explode(',', $data_list[$i]['order_id']);
			$data_list[$i]['order_id'] = array_unique($data_list[$i]['order_id']);
			$data_list[$i]['shop_name'] = get_shop_name($data_list[$i]['ru_id'], 1);
			$data_list[$i]['cat_name'] = $GLOBALS['db']->getOne('SELECT c.cat_name FROM ' . $GLOBALS['ecs']->table('category') . ' AS c, ' . $GLOBALS['ecs']->table('goods') . ' AS g' . ' WHERE c.cat_id = g.cat_id AND g.goods_id = \'' . $data_list[$i]['goods_id'] . '\' ');

			if ($filter['time_type'] == 1) {
				$data_list[$i]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $data_list[$i]['add_time']);
			}
			else {
				$data_list[$i]['shipping_time'] = local_date($GLOBALS['_CFG']['time_format'], $data_list[$i]['shipping_time']);
			}
		}

		if ($filter['sort_by'] == 'goods_number') {
			$data_list = get_array_sort($data_list, 'goods_number', 'DESC');
		}

		$arr = array('data_list' => $data_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
		return $arr;
	}
}

function get_status_list($type = 'all')
{
	global $_LANG;
	$list = array();
	if (($type == 'all') || ($type == 'order')) {
		$pre = ($type == 'all' ? 'os_' : '');

		foreach ($_LANG['os'] as $key => $value) {
			$list[$pre . $key] = $value;
		}
	}

	if (($type == 'all') || ($type == 'shipping')) {
		$pre = ($type == 'all' ? 'ss_' : '');

		foreach ($_LANG['ss'] as $key => $value) {
			$list[$pre . $key] = $value;
		}
	}

	if (($type == 'all') || ($type == 'payment')) {
		$pre = ($type == 'all' ? 'ps_' : '');

		foreach ($_LANG['ps'] as $key => $value) {
			$list[$pre . $key] = $value;
		}
	}

	return $list;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/statistic.php';
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('lang', $_LANG);
admin_priv('sale_order_stats');
if (empty($_REQUEST['act']) || !in_array($_REQUEST['act'], array('list', 'download', 'query'))) {
	$_REQUEST['act'] = 'list';
}

$smarty->assign('primary_cat', $_LANG['06_stats']);

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('current', 'sale_general_list');
	$start_time = local_mktime(0, 0, 0, local_date('m'), 1, local_date('Y'));
	$end_time = (local_mktime(0, 0, 0, local_date('m'), local_date('t'), local_date('Y')) + (24 * 60 * 60)) - 1;
	$start_time = local_date($GLOBALS['_CFG']['time_format'], $start_time);
	$end_time = local_date($GLOBALS['_CFG']['time_format'], $end_time);
	$smarty->assign('start_time', $start_time);
	$smarty->assign('end_time', $end_time);
	$smarty->assign('os_list', get_status_list('order'));
	$smarty->assign('ss_list', get_status_list('shipping'));
	$data = get_data_list(1);
	$page_count_arr = seller_page($data, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('data_list', $data['data_list']);
	$smarty->assign('filter', $data['filter']);
	$smarty->assign('record_count', $data['record_count']);
	$smarty->assign('page_count', $data['page_count']);
	$smarty->assign('date_start_time', $data['start_time']);
	$smarty->assign('date_end_time', $data['end_time']);
	$smarty->assign('full_page', 1);
	$smarty->assign('sort_order_time', '<img src="images/sort_desc.gif">');
	$smarty->assign('action_link', array('text' => $_LANG['down_sales_stats'], 'href' => 'sale_general.php?act=download&start_time=' . $start_time . '&end_time=' . $end_time, 'class' => 'icon-download-alt'));
	$smarty->assign('ur_here', $_LANG['report_sell']);
	assign_query_info();
	$smarty->display('sale_general.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$data = get_data_list(1);
	$page_count_arr = seller_page($data, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('data_list', $data['data_list']);
	$smarty->assign('filter', $data['filter']);
	$smarty->assign('record_count', $data['record_count']);
	$smarty->assign('page_count', $data['page_count']);
	$sort_flag = sort_flag($data['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('sale_general.dwt'), '', array('filter' => $data['filter'], 'page_count' => $data['page_count']));
}
else if ($_REQUEST['act'] == 'download') {
	$data = get_data_list(1);
	$data_list = $data['data_list'];
	$filename = str_replace(' ', '-', local_date($GLOBALS['_CFG']['time_format'], gmtime())) . '_' . rand(0, 1000);
	header('Content-type: application/vnd.ms-excel; charset=utf-8');
	header('Content-Disposition: attachment; filename=' . $filename . '.xls');
	echo ecs_iconv(EC_CHARSET, 'GB2312', $filename . $_LANG['sales_statistics']) . "\t\n";
	echo ecs_iconv(EC_CHARSET, 'GB2312', '商家名称') . '	';
	echo ecs_iconv(EC_CHARSET, 'GB2312', '商品名称') . '	';
	echo ecs_iconv(EC_CHARSET, 'GB2312', '货号') . '	';
	echo ecs_iconv(EC_CHARSET, 'GB2312', '分类') . '	';
	echo ecs_iconv(EC_CHARSET, 'GB2312', '数量') . '	';
	echo ecs_iconv(EC_CHARSET, 'GB2312', '单价') . '	';
	echo ecs_iconv(EC_CHARSET, 'GB2312', '总金额') . '	';
	echo ecs_iconv(EC_CHARSET, 'GB2312', '售出日期') . "\t\n";

	foreach ($data_list as $data) {
		echo ecs_iconv(EC_CHARSET, 'GB2312', $data['shop_name']) . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $data['goods_name']) . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $data['goods_sn']) . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $data['cat_name']) . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $data['goods_number']) . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $data['goods_price']) . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $data['total_fee']) . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', $data['add_time']) . '	';
		echo "\n";
	}
}

?>
