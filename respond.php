<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/lib_payment.php';
require ROOT_PATH . 'includes/lib_order.php';
$pay_code = (!empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '');
if (empty($pay_code) && !empty($_REQUEST['v_pmode']) && !empty($_REQUEST['v_pstring'])) {
	$pay_code = 'cappay';
}

if (empty($pay_code) && ($_REQUEST['ext1'] == 'shenzhou') && ($_REQUEST['ext2'] == 'ecshop')) {
	$pay_code = 'shenzhou';
}

if (empty($pay_code)) {
	$msg = $_LANG['pay_not_exist'];
}
else {
	if (strpos($pay_code, '?') !== false) {
		$arr1 = explode('?', $pay_code);
		$arr2 = explode('=', $arr1[1]);
		$_REQUEST['code'] = $arr1[0];
		$_REQUEST[$arr2[0]] = $arr2[1];
		$_GET['code'] = $arr1[0];
		$_GET[$arr2[0]] = $arr2[1];
		$pay_code = $arr1[0];
	}

	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('payment') . ' WHERE pay_code = \'' . $pay_code . '\' AND enabled = 1';

	if ($db->getOne($sql) == 0) {
		$msg = $_LANG['pay_disabled'];
	}
	else {
		$plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

		if (file_exists($plugin_file)) {
			include_once $plugin_file;
			$payment = new $pay_code();
			$msg = (@$payment->respond() ? $_LANG['pay_success'] : $_LANG['pay_fail']);
		}
		else {
			$msg = $_LANG['pay_not_exist'];
		}
	}
}

assign_template();
$position = assign_ur_here();
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here', $position['ur_here']);
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here', $position['ur_here']);
$smarty->assign('helps', get_shop_help());

if (defined('THEME_EXTENSION')) {
	$categories_pro = get_category_tree_leve_one();
	$smarty->assign('categories_pro', $categories_pro);
}

$smarty->assign('message', $msg);
$smarty->assign('shop_url', $ecs->url());
$smarty->display('respond.dwt');

?>
