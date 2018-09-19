<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_visual.php';
admin_priv('article_manage');

if ($_REQUEST['act'] == 'visual') {
	$des = ROOT_PATH . 'data/cms_Templates/' . $GLOBALS['_CFG']['template'];
	$is_temp = 0;

	if (file_exists($des . '/' . $code . '/temp/pc_page.php')) {
		$filename = $des . '/temp/pc_page.php';
		$is_temp = 1;
	}
	else {
		$filename = $des . '/pc_page.php';
	}

	$news = get_html_file($filename);
	$smarty->assign('pc_page', $news);
	$smarty->assign('is_temp', $is_temp);
	$smarty->display('news.dwt');
}
else if ($_REQUEST['act'] == 'restore') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'content' => '');
	$des = ROOT_PATH . 'data/cms_Templates/' . $GLOBALS['_CFG']['template'];
	del_DirAndFile($des);
	exit(json_encode($result));
}

?>
