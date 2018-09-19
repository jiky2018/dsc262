<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_area.php';
get_request_filter();
$order_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$consignee = !empty($_REQUEST['con']) ? rawurldecode(trim($_REQUEST['con'])) : '';
$sql = 'SELECT * FROM ' . $ecs->table('order_info') . (' WHERE order_id = \'' . $order_id . '\'');
$order = $db->getRow($sql);

if (empty($order)) {
	$msg = $_LANG['order_not_exists'];
}
else if ($order['shipping_status'] == SS_RECEIVED) {
	$msg = $_LANG['order_already_received'];
}
else if ($order['shipping_status'] != SS_SHIPPED) {
	$msg = $_LANG['order_invalid'];
}
else if ($order['consignee'] != $consignee) {
	$msg = $_LANG['order_invalid'];
}
else {
	$sql = 'UPDATE ' . $ecs->table('order_info') . ' SET shipping_status = \'' . SS_RECEIVED . ('\' WHERE order_id = \'' . $order_id . '\'');
	$db->query($sql);
	order_action($order['order_sn'], $order['order_status'], SS_RECEIVED, $order['pay_status'], '', $_LANG['buyer']);
	$msg = $_LANG['act_ok'];
}

assign_template();
$position = assign_ur_here();
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here', $position['ur_here']);
$smarty->assign('helps', get_shop_help());
assign_dynamic('receive');
$smarty->assign('msg', $msg);
$smarty->display('receive.dwt');

?>
