<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'setup') {
	admin_priv('gen_goods_script');
	$lang_list = array('UTF8' => $_LANG['charset']['utf8'], 'GB2312' => $_LANG['charset']['zh_cn'], 'BIG5' => $_LANG['charset']['zh_tw']);
	$ur_here = $_LANG['16_goods_script'];
	$smarty->assign('ur_here', $ur_here);
	$smarty->assign('brand_list', get_brand_list());
	$smarty->assign('intro_list', $_LANG['intro']);
	$smarty->assign('url', $ecs->url());
	$smarty->assign('lang_list', $lang_list);
	set_default_filter();
	assign_query_info();
	$smarty->display('gen_goods_script.dwt');
}

?>
