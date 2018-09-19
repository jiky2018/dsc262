<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_area.php';
require_once ROOT_PATH . 'includes/lib_order.php';
include_once ROOT_PATH . 'includes/lib_transaction.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/shopping_flow.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';

if (0 < $_SESSION['user_id']) {
	$consignee_list = get_consignee_list($_SESSION['user_id']);
	$choose['country'] = isset($_POST['country']) ? intval($_POST['country']) : $consignee_list[0]['country'];
	$choose['province'] = isset($_POST['province']) ? intval($_POST['province']) : $consignee_list[0]['province'];
	$choose['city'] = isset($_POST['city']) ? intval($_POST['city']) : $consignee_list[0]['city'];
	$choose['district'] = isset($_POST['district']) ? intval($_POST['district']) : (isset($consignee_list[0]['district']) ? $consignee_list[0]['district'] : 0);
}
else {
	$choose['country'] = isset($_POST['country']) ? intval($_POST['country']) : $_CFG['shop_country'];
	$choose['province'] = isset($_POST['province']) ? intval($_POST['province']) : 2;
	$choose['city'] = isset($_POST['city']) ? intval($_POST['city']) : 35;
	$choose['district'] = isset($_POST['district']) ? intval($_POST['district']) : 417;
}

assign_template();
assign_dynamic('myship');
$position = assign_ur_here(0, $_LANG['shopping_myship']);
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here', $position['ur_here']);
$smarty->assign('helps', get_shop_help());
$smarty->assign('lang', $_LANG);
$smarty->assign('choose', $choose);
$province_list[NULL] = get_regions(1, $choose['country']);
$city_list[NULL] = get_regions(2, $choose['province']);
$district_list[NULL] = get_regions(3, $choose['city']);
$smarty->assign('province_list', $province_list);
$smarty->assign('city_list', $city_list);
$smarty->assign('district_list', $district_list);
$smarty->assign('country_list', get_regions());
$region = array($choose['country'], $choose['province'], $choose['city'], $choose['district']);
$shipping_list = available_shipping_list($region);
$cart_weight_price = 0;
$insure_disabled = true;
$cod_disabled = true;

foreach ($shipping_list as $key => $val) {
	$shipping_cfg = unserialize_config($val['configure']);
	$shipping_fee = shipping_fee($val['shipping_code'], unserialize($val['configure']), $cart_weight_price['weight'], $cart_weight_price['amount']);
	$shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
	$shipping_list[$key]['fee'] = $shipping_fee;
	$shipping_list[$key]['free_money'] = price_format($shipping_cfg['free_money'], false);
	$shipping_list[$key]['insure_formated'] = strpos($val['insure'], '%') === false ? price_format($val['insure'], false) : $val['insure'];
}

$smarty->assign('shipping_list', $shipping_list);
$smarty->display('myship.dwt');

?>
