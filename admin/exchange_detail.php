<?php
//QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_shop_exchange_detail($is_pagination = true)
{
	$where = '';
	$adminru = get_admin_ru_id();
	$where .= get_rs_null_where('user_id', $adminru['rs_id']);
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE merchants_audit = 1' . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE merchants_audit = 1' . $where;

	if ($is_pagination) {
		$sql .= ' LIMIT ' . $filter['start'] . ', ' . $filter['page_size'];
	}

	$detail = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($detail as $key => $row) {
		$row['shop_name'] = get_shop_name($row['user_id'], 1);
		$exchange = get_seller_goods_exchange($row['user_id']);
		$row['give_integral'] = $exchange['give_integral'];
		$row['rank_integral'] = $exchange['rank_integral'];
		$arr[] = $row;
	}

	$arr = array('detail' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function give_integral_order_list()
{
	$result = get_filter();

	if ($result === false) {
		$where = ' WHERE 1 ';
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'oi.order_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['user_id'] = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		$where .= ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id) = 0 ';
		$where .= ' AND og.ru_id = \'' . $filter['user_id'] . '\'';
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON oi.order_id = og.order_id ' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT oi.order_id, oi.order_sn, og.goods_name, og.goods_number  FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON oi.order_id = og.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON og.goods_id = g.goods_id ' . (' ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$arr = array();
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$val = $row['order_sn'];
		$sql = 'SELECT SUM(rank_points) AS rank_integral, SUM(pay_points) AS give_integral FROM ' . $GLOBALS['ecs']->table('account_log') . (' WHERE change_desc LIKE \'%' . $val . '%\'');
		$integral = $GLOBALS['db']->getRow($sql);
		$row['rank_integral'] = $integral['rank_integral'];
		$row['give_integral'] = $integral['give_integral'];
		$arr[] = $row;
	}

	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_exchange_list($user_id, $is_pagination = true)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . (' AS msi ON msi.user_id = g.user_id WHERE msi.user_id=\'' . $user_id . '\' AND g.give_integral <> 0 AND g.rank_integral <> 0');
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT msi.user_id, g.goods_id, g.goods_name, g.goods_thumb, g.give_integral, g.rank_integral, model_price, is_promote, promote_price, shop_price, model_price FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . (' AS msi ON msi.user_id = g.user_id WHERE msi.user_id=\'' . $user_id . '\' AND g.give_integral <> 0 AND g.rank_integral <> 0 ORDER BY msi.user_id DESC');

	if ($is_pagination) {
		$sql .= ' LIMIT ' . $filter['start'] . ', ' . $filter['page_size'];
	}

	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		if ($row['model_price'] == 1) {
			$sql = 'SELECT SUM(give_integral) AS give_integral, SUM(rank_integral) AS rank_integral FROM ' . $GLOBALS['ecs']->table('warehouse_goods') . ' WHERE goods_id = \'' . $row['goods_id'] . '\'';
			$warehouse_row = $GLOBALS['db']->getRow($sql);
			$row['give_integral'] = $warehouse_row['give_integral'];
			$row['rank_integral'] = $warehouse_row['rank_integral'];
		}
		else if ($row['model_price'] == 2) {
			$sql = 'SELECT SUM(give_integral) AS give_integral, SUM(rank_integral) AS rank_integral FROM ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' WHERE goods_id = \'' . $row['goods_id'] . '\'';
			$area_row = $GLOBALS['db']->getRow($sql);
			$row['give_integral'] = $area_row['give_integral'];
			$row['rank_integral'] = $area_row['rank_integral'];
		}
		else {
			if ($row['give_integral'] == '-1') {
				$row['give_integral'] = intval($row['is_promote'] ? $row['promote_price'] : $row['shop_price']);
			}

			if ($row['rank_integral'] == '-1') {
				$row['rank_integral'] = intval($row['is_promote'] ? $row['promote_price'] : $row['shop_price']);
			}
		}

		$row['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$arr[] = $row;
	}

	$arr = array('goods' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_seller_goods_exchange($user_id = 0)
{
	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.give_integral, g.rank_integral, g.model_price, g.is_promote, g.promote_price, g.shop_price, g.model_price, og.goods_number FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = og.goods_id ' . (' WHERE g.user_id = \'' . $user_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		if ($row['model_price'] == 1) {
			$sql = 'SELECT SUM(give_integral) AS give_integral, SUM(rank_integral) AS rank_integral FROM ' . $GLOBALS['ecs']->table('warehouse_goods') . ' WHERE goods_id = \'' . $row['goods_id'] . '\'';
			$warehouse_row = $GLOBALS['db']->getRow($sql);
			$row['give_integral'] = $warehouse_row['give_integral'];
			$row['rank_integral'] = $warehouse_row['rank_integral'];
		}
		else if ($row['model_price'] == 2) {
			$sql = 'SELECT SUM(give_integral) AS give_integral, SUM(rank_integral) AS rank_integral FROM ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' WHERE goods_id = \'' . $row['goods_id'] . '\'';
			$area_row = $GLOBALS['db']->getRow($sql);
			$row['give_integral'] = $area_row['give_integral'];
			$row['rank_integral'] = $area_row['rank_integral'];
		}
		else {
			if ($row['give_integral'] == '-1') {
				$row['give_integral'] = intval($row['is_promote'] ? $row['promote_price'] : $row['shop_price']);
			}

			if ($row['rank_integral'] == '-1') {
				$row['rank_integral'] = intval($row['is_promote'] ? $row['promote_price'] : $row['shop_price']);
			}
		}

		$give_integral = $row['give_integral'] * $row['goods_number'];
		$rank_integral = $row['rank_integral'] * $row['goods_number'];
		$arr['give_integral'] += $give_integral;
		$arr['rank_integral'] += $rank_integral;
	}

	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
if (isset($_REQUEST['act']) && ($_REQUEST['act'] == 'detail_query' || $_REQUEST['act'] == 'download')) {
	if ($_REQUEST['act'] == 'download') {
		$file_name = '商家积分明细';
		$exchange_detail = get_shop_exchange_detail(false);
		header('Content-type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $file_name . '.xls');
		echo ecs_iconv(EC_CHARSET, 'GB2312', '商家积分明细') . "\t\n";
		echo ecs_iconv(EC_CHARSET, 'GB2312', '商家名称') . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', '总赠送消费积分') . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', '总赠送等级积分') . "\t\n";

		foreach ($exchange_detail['detail'] as $key => $value) {
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['shop_name']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['give_integral']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['rank_integral']) . '	';
			echo "\n";
		}

		exit();
	}

	$exchange_detail_data = get_shop_exchange_detail();
	$smarty->assign('filter', $exchange_detail_data['filter']);
	$smarty->assign('record_count', $exchange_detail_data['record_count']);
	$smarty->assign('page_count', $exchange_detail_data['page_count']);
	$smarty->assign('detail', $exchange_detail_data['detail']);
	make_json_result($smarty->fetch('exchange_detail_list.dwt'), '', array('filter' => $exchange_detail_data['filter'], 'page_count' => $exchange_detail_data['page_count']));
}

if (isset($_REQUEST['act']) && ($_REQUEST['act'] == 'order_query' || $_REQUEST['act'] == 'order_download')) {
	if ($_REQUEST['act'] == 'order_download') {
		$file_name = '赠送积分订单列表';
		$order_list = give_integral_order_list();
		header('Content-type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $file_name . '.xls');
		echo ecs_iconv(EC_CHARSET, 'GB2312', '赠送积分订单列表') . "\t\n";
		echo ecs_iconv(EC_CHARSET, 'GB2312', '订单号') . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', '商品名称') . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', '商品数量') . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', '总赠送消费积分') . '	';
		echo ecs_iconv(EC_CHARSET, 'GB2312', '总赠送等级积分') . "\t\n";

		foreach ($order_list['item'] as $key => $value) {
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['order_sn']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['goods_name']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['goods_number']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['give_integral']) . '	';
			echo ecs_iconv(EC_CHARSET, 'GB2312', $value['rank_integral']) . '	';
			echo "\n";
		}

		exit();
	}

	$order_list_data = give_integral_order_list();
	$smarty->assign('filter', $order_list_data['filter']);
	$smarty->assign('record_count', $order_list_data['record_count']);
	$smarty->assign('page_count', $order_list_data['page_count']);
	$smarty->assign('order_list', $order_list_data['item']);
	make_json_result($smarty->fetch('give_integral_orders.dwt'), '', array('filter' => $order_list_data['filter'], 'page_count' => $order_list_data['page_count']));
}
else if ($_REQUEST['act'] == 'detail') {
	admin_priv('exchange');
	$exchange_detail = get_shop_exchange_detail(true);
	$smarty->assign('filter', $exchange_detail['filter']);
	$smarty->assign('record_count', $exchange_detail['record_count']);
	$smarty->assign('page_count', $exchange_detail['page_count']);
	$smarty->assign('detail', $exchange_detail['detail']);
	$smarty->assign('menu_select', array('action' => '06_stats', 'current' => 'exchange_count'));
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->display('exchange_detail_list.dwt');
}
else if ($_REQUEST['act'] == 'detail_query') {
	$exchange_detail = get_shop_exchange_detail(true);
	$smarty->assign('filter', $exchange_detail['filter']);
	$smarty->assign('record_count', $exchange_detail['record_count']);
	$smarty->assign('page_count', $exchange_detail['page_count']);
	$smarty->assign('detail', $exchange_detail['detail']);
	$sort_flag = sort_flag($exchange_detail['filter']);
	make_json_result($smarty->fetch('exchange_detail_list.dwt'), '', array('filter' => $exchange_detail['filter'], 'page_count' => $exchange_detail['page_count']));
}
else if ($_REQUEST['act'] == 'exchange_goods') {
	$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
	$goods = get_exchange_list($user_id, $is_pagination = true);
	$smarty->assign('filter', $goods['filter']);
	$smarty->assign('record_count', $goods['record_count']);
	$smarty->assign('page_count', $goods['page_count']);
	$smarty->assign('goods', $goods['goods']);
	$smarty->assign('menu_select', array('action' => '06_stats', 'current' => 'exchange_count_goods'));
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->display('exchange_goods_detail_info.dwt');
}
else if ($_REQUEST['act'] == 'goods_detail_query') {
	$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
	$goods = get_exchange_list($user_id, $is_pagination = true);
	$smarty->assign('filter', $goods['filter']);
	$smarty->assign('record_count', $goods['record_count']);
	$smarty->assign('page_count', $goods['page_count']);
	$smarty->assign('goods', $goods['goods']);
	$sort_flag = sort_flag($goods['filter']);
	make_json_result($smarty->fetch('exchange_goods_detail_info.dwt'), '', array('filter' => $goods['filter'], 'page_count' => $goods['page_count']));
}
else if ($_REQUEST['act'] == 'order_view') {
	$smarty->assign('ur_here', $_LANG['give_integral_order_list']);
	$smarty->assign('action_link', array('href' => 'exchange_detail.php?act=export_orders&', 'text' => $_LANG['export']));
	$order_list = give_integral_order_list();
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$smarty->assign('order_list', $order_list['item']);
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->display('give_integral_orders.dwt');
}
else if ($_REQUEST['act'] == 'order_query') {
	$order_list = give_integral_order_list();
	$smarty->assign('filter', $order_list['filter']);
	$smarty->assign('record_count', $order_list['record_count']);
	$smarty->assign('page_count', $order_list['page_count']);
	$smarty->assign('order_list', $order_list['item']);
	$sort_flag = sort_flag($order_list['filter']);
	make_json_result($smarty->fetch('give_integral_orders.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}

?>
