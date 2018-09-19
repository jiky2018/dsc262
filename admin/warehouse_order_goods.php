<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function order_goods_warehouse_list($order_id, $warehouse_id)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'og.rec_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$ex_where = ' WHERE 1 ';
		$sql = 'SELECT rw.region_name, g.goods_name, og.goods_attr, og.attr_number, og.province_id, og.city_id, og.district_id, oi.add_time ' . ' FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . ' left join ' . $GLOBALS['ecs']->table('goods') . ' as g on og.goods_id = g.goods_id' . ' left join ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw on og.warehouse_id = rw.region_id' . ' left join ' . $GLOBALS['ecs']->table('order_info') . ' as oi on og.order_id = oi.order_id' . $ex_where . ' AND og.order_id = \'' . $order_id . '\' AND og.warehouse_id = \'' . $warehouse_id . '\' AND oi.user_id = \'' . $user_id . '\'';
		$filter['record_count'] = count($GLOBALS['db']->getAll($sql));
		$filter = page_and_size($filter);
		$sql = 'SELECT rw.region_name, g.goods_name, og.rec_id, og.goods_id, og.goods_attr, og.attr_number, og.size_attr, og.province_id, og.city_id, og.district_id, oi.user_id, oi.add_time ' . ' FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . ' left join ' . $GLOBALS['ecs']->table('goods') . ' as g on og.goods_id = g.goods_id' . ' left join ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw on og.warehouse_id = rw.region_id' . ' left join ' . $GLOBALS['ecs']->table('order_info') . ' as oi on og.order_id = oi.order_id' . $ex_where . ' AND og.order_id = \'' . $order_id . '\' AND og.warehouse_id = \'' . $warehouse_id . '\'' . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$order_goods_list = $GLOBALS['db']->getAll($sql);
	$count = count($order_goods_list);

	for ($i = 0; $i < $count; $i++) {
		$order_goods_list[$i]['add_time'] = local_date($GLOBALS['_CFG']['date_format'], $order_goods_list[$i]['add_time']);
		$size_attr = get_explode_arr($order_goods_list[$i]['size_attr'], ',');
		$sizeAttr = get_arr_two($size_attr);
		$attr = get_size_list_order_goods($sizeAttr, $order_goods_list[$i]['goods_id'], 1);
		$order_goods_list[$i]['attr_value'] = $attr['attr'];
		$user_address = get_user_address_order($order_goods_list[$i]['user_id'], $order_goods_list[$i]['province_id'], $order_goods_list[$i]['city_id'], $order_goods_list[$i]['district_id']);
		$order_goods_list[$i]['address'] = $user_address['address'];
		$order_goods_list[$i]['mobile'] = $user_address['mobile'];
		$order_goods_list[$i]['r1_name'] = get_region_name_order($order_goods_list[$i]['province_id']);
		$order_goods_list[$i]['r2_name'] = get_region_name_order($order_goods_list[$i]['city_id']);
		$order_goods_list[$i]['r3_name'] = get_region_name_order($order_goods_list[$i]['district_id']);
	}

	$arr = array('order_goods_list' => $order_goods_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_region_name_order($region_id)
{
	if (0 < $region_id) {
		$sql = 'select region_name from ' . $GLOBALS['ecs']->table('region') . ' where region_id = \'' . $region_id . '\'';
		return $GLOBALS['db']->getOne($sql);
	}
}

function get_user_address_order($user_id, $province_id, $city_id, $district_id)
{
	$sql = 'select address, mobile from ' . $GLOBALS['ecs']->table('user_address') . ' where user_id = \'' . $user_id . '\' and province = \'' . $province_id . '\' and city = \'' . $city_id . '\' and district = \'' . $district_id . '\'';
	return $GLOBALS['db']->getRow($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'list') {
	admin_priv('warehouse_manage');
	$order_id = (isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0);
	$warehouse_id = (isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0);
	$smarty->assign('ur_here', '配送区域');
	$order_goods_list = order_goods_warehouse_list($order_id, $warehouse_id);
	$_SESSION['warehouse_order_goods_warehouseId'] = $warehouse_id;
	$_SESSION['warehouse_order_goods_orderId'] = $order_id;
	$smarty->assign('order_goods_list', $order_goods_list['order_goods_list']);
	$smarty->assign('filter', $order_goods_list['filter']);
	$smarty->assign('record_count', $order_goods_list['record_count']);
	$smarty->assign('page_count', $order_goods_list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->display('order_goods_warehouse_list.htm');
}
else if ($_REQUEST['act'] == 'query') {
	$warehouse_id = $_SESSION['warehouse_order_goods_warehouseId'];
	$order_id = $_SESSION['warehouse_order_goods_orderId'];
	$order_goods_list = order_warehouse_list($order_id, $warehouse_id);
	$smarty->assign('order_goods_list', $order_goods_list['order_goods_list']);
	$smarty->assign('filter', $order_goods_list['filter']);
	$smarty->assign('record_count', $order_goods_list['record_count']);
	$smarty->assign('page_count', $order_goods_list['page_count']);
	$sort_flag = sort_flag($warehouse_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('order_goods_warehouse_list.htm'), '', array('filter' => $order_goods_list['filter'], 'page_count' => $order_goods_list['page_count']));
}

?>
