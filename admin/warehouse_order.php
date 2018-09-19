<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function order_warehouse_list($order_id)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'rw.region_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$ex_where = ' WHERE 1 ';
		$sql = 'SELECT  og.warehouse_id, rw.region_name ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi' . ' left join ' . $GLOBALS['ecs']->table('order_goods') . ' as og on oi.order_id = og.order_id' . ' left join ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw on og. warehouse_id  = rw.region_id' . $ex_where . ' AND oi.order_id = \'' . $order_id . '\' group by og. warehouse_id';
		$filter['record_count'] = count($GLOBALS['db']->getAll($sql));
		$filter = page_and_size($filter);
		$sql = 'SELECT  og.order_id, u.user_id, u.user_name, og.warehouse_id, rw.region_name, sum(og.attr_number) as attr_number' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi' . ' left join ' . $GLOBALS['ecs']->table('order_goods') . ' as og on oi.order_id = og.order_id' . ' left join ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw on og. warehouse_id  = rw.region_id' . ' left join ' . $GLOBALS['ecs']->table('users') . ' as u on oi.user_id  = u.user_id' . $ex_where . ' AND oi.order_id = \'' . $order_id . '\' group by og. warehouse_id' . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$warehouse_list = $GLOBALS['db']->getAll($sql);
	$count = count($warehouse_list);

	for ($i = 0; $i < $count; $i++) {
		$warehouse_list[$i]['add_time'] = local_date($GLOBALS['_CFG']['date_format'], $warehouse_list[$i]['add_time']);
	}

	$arr = array('warehouse_list' => $warehouse_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'list') {
	admin_priv('warehouse_manage');
	$order_id = (isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0);
	$smarty->assign('ur_here', '仓库管理');
	$warehouse_list = order_warehouse_list($order_id);
	$_SESSION['warehouse_order_id'] = $order_id;
	$smarty->assign('warehouse_list', $warehouse_list['warehouse_list']);
	$smarty->assign('filter', $warehouse_list['filter']);
	$smarty->assign('record_count', $warehouse_list['record_count']);
	$smarty->assign('page_count', $warehouse_list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->display('order_warehouse_list.htm');
}
else if ($_REQUEST['act'] == 'query') {
	$warehouse_list = order_warehouse_list($_SESSION['warehouse_order_id']);
	$smarty->assign('warehouse_list', $warehouse_list['warehouse_list']);
	$smarty->assign('filter', $warehouse_list['filter']);
	$smarty->assign('record_count', $warehouse_list['record_count']);
	$smarty->assign('page_count', $warehouse_list['page_count']);
	$sort_flag = sort_flag($warehouse_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('order_warehouse_list.htm'), '', array('filter' => $warehouse_list['filter'], 'page_count' => $warehouse_list['page_count']));
}

?>
