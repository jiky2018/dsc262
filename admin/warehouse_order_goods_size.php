<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_size_attr_goods($rec_id)
{
	$sql = 'select size_attr from ' . $GLOBALS['ecs']->table('order_goods') . ' where rec_id = \'' . $rec_id . '\'';
	return $GLOBALS['db']->getOne($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'list') {
	admin_priv('warehouse_manage');
	$rec_id = (isset($_REQUEST['rec_id']) ? intval($_REQUEST['rec_id']) : 0);
	$goods_id = (isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0);
	$smarty->assign('ur_here', '商品属性类型');
	$size_attr = get_size_attr_goods($rec_id);
	$size_attr = get_explode_arr($size_attr, ',');
	$sizeAttr = get_arr_two($size_attr);
	$size_list = get_size_list_order_goods($sizeAttr, $goods_id, 2);
	$_SESSION['rec_id'] = $rec_id;
	$smarty->assign('size_list', $size_list);
	$smarty->assign('full_page', 1);
	$smarty->assign('goods_id', $goods_id);
	$smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->display('order_goods_size_warehouse_list.htm');
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
	make_json_result($smarty->fetch('order_goods_size_warehouse_list.htm'), '', array('filter' => $order_goods_list['filter'], 'page_count' => $order_goods_list['page_count']));
}

?>
