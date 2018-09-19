<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init_api.php';
require dirname(__FILE__) . '/plugins/dscapi/autoload.php';
$base = new \app\func\base();
$base->get_request_filter();
$method = isset($_REQUEST['method']) && !empty($_REQUEST['method']) ? strtolower(addslashes($_REQUEST['method'])) : '';
$app_key = isset($_REQUEST['app_key']) && !empty($_REQUEST['app_key']) ? $base->dsc_addslashes($_REQUEST['app_key']) : '';
$format = isset($_REQUEST['format']) && !empty($_REQUEST['format']) ? strtolower($_REQUEST['format']) : 'json';
$interface_type = isset($_REQUEST['interface_type']) && !empty($_REQUEST['interface_type']) ? strtolower($_REQUEST['interface_type']) : 0;
$data = isset($_REQUEST['data']) && !empty($_REQUEST['data']) ? addslashes_deep($_REQUEST['data']) : '*';
$page_size = isset($_REQUEST['page_size']) && !empty($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 15;
$page = isset($_REQUEST['page']) && !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$sort_by = isset($_REQUEST['sort_by']) ? $base->get_addslashes($_REQUEST['sort_by']) : '';
$sort_order = isset($_REQUEST['sort_order']) ? $base->get_addslashes($_REQUEST['sort_order']) : 'ASC';

if ($interface_type == 1) {
	$raw_post_data = file_get_contents('php://input', 'r');
	$raw_post_data = json_decode($raw_post_data, true);
	$data = $raw_post_data['data'];
	$data = base64_decode($data);
}

$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('open_api') . (' WHERE app_key = \'' . $app_key . '\' AND is_open = 1');
$open_api = $GLOBALS['db']->getRow($sql);

if ($app_key) {
	if (!$open_api) {
		exit('暂无该接口权限');
	}
	else {
		$action_code = isset($open_api['action_code']) && !empty($open_api['action_code']) ? explode(',', $open_api['action_code']) : array();

		if (empty($action_code)) {
			exit('暂无该接口权限');
		}
		else if (!in_array($method, $action_code)) {
			exit('暂无该接口权限');
		}
	}
}
else {
	exit('密钥不能为空');
}

if ($format == 'json' && $data) {
	if ($interface_type == 0) {
		$data = stripslashes($data);
		$data = stripslashes($data);
	}

	$data = json_decode($data, true);
}
else {
	$data = htmlspecialchars_decode($data);
	$data = json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
}

$interface = array('goods', 'product', 'order', 'user', 'region', 'warehouse', 'attribute', 'category', 'brand');
$interface = $base->get_interface_file(dirname(__FILE__), $interface);

foreach ($interface as $key => $row) {
	require $row;
}

if (in_array($method, $goods_action)) {
	$file_type = 'goods';
}
else if (in_array($method, $product_action)) {
	$file_type = 'product';
}
else if (in_array($method, $order_action)) {
	$file_type = 'order';
}
else if (in_array($method, $user_action)) {
	$file_type = 'user';
}
else if (in_array($method, $region_action)) {
	$file_type = 'region';
}
else if (in_array($method, $warehouse_action)) {
	$file_type = 'warehouse';
}
else if (in_array($method, $attribute_action)) {
	$file_type = 'attribute';
}
else if (in_array($method, $category_action)) {
	$file_type = 'category';
}
else if (in_array($method, $brand_action)) {
	$file_type = 'brand';
}
else {
	exit('非法入口');
}

require dirname(__FILE__) . '/plugins/dscapi/view/' . $file_type . '.php';

?>
