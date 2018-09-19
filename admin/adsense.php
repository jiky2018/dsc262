<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/ads.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if (($_REQUEST['act'] == 'list') || ($_REQUEST['act'] == 'download')) {
	admin_priv('ad_manage');
	$ads_stats = array();
	$sql = 'SELECT a.ad_id, a.ad_name, b.* ' . 'FROM ' . $ecs->table('ad') . ' AS a, ' . $ecs->table('adsense') . ' AS b ' . 'WHERE b.from_ad = a.ad_id ORDER by a.ad_name DESC';
	$res = $db->query($sql);

	while ($rows = $db->fetchRow($res)) {
		$rows['referer'] = addslashes($rows['referer']);
		$sql2 = 'SELECT COUNT(order_id) FROM ' . $ecs->table('order_info') . ' WHERE from_ad=\'' . $rows['ad_id'] . '\' AND referer=\'' . $rows['referer'] . '\'';
		$rows['order_num'] = $db->getOne($sql2);
		$sql3 = 'SELECT COUNT(order_id) FROM ' . $ecs->table('order_info') . ' WHERE from_ad    = \'' . $rows['ad_id'] . '\'' . ' AND referer = \'' . $rows['referer'] . '\' ' . order_query_sql('finished');
		$rows['order_confirm'] = $db->getOne($sql3);
		$ads_stats[] = $rows;
	}

	$smarty->assign('ads_stats', $ads_stats);
	$goods_stats = array();
	$goods_sql = 'SELECT from_ad, referer, clicks FROM ' . $ecs->table('adsense') . ' WHERE from_ad = \'-1\' ORDER by referer DESC';
	$goods_res = $db->query($goods_sql);

	while ($rows2 = $db->fetchRow($goods_res)) {
		$rows2['referer'] = addslashes($rows2['referer']);
		$rows2['order_num'] = $db->getOne('SELECT COUNT(order_id) FROM ' . $ecs->table('order_info') . ' WHERE referer=\'' . $rows2['referer'] . '\'');
		$sql = 'SELECT COUNT(order_id) FROM ' . $ecs->table('order_info') . ' WHERE referer=\'' . $rows2['referer'] . '\'' . order_query_sql('finished');
		$rows2['order_confirm'] = $db->getOne($sql);
		$rows2['ad_name'] = $_LANG['adsense_js_goods'];
		$goods_stats[] = $rows2;
	}

	if ($_REQUEST['act'] == 'download') {
		header('Content-type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename=ad_statistics.xls');
		$data = $_LANG['adsense_name'] . '	' . $_LANG['cleck_referer'] . '	' . $_LANG['click_count'] . '	' . $_LANG['confirm_order'] . '	' . $_LANG['gen_order_amount'] . "\n";
		$res = array_merge($goods_stats, $ads_stats);

		foreach ($res as $row) {
			$data .= $row['ad_name'] . '	' . $row['referer'] . '	' . $row['clicks'] . '	' . $row['order_confirm'] . '	' . $row['order_num'] . "\n";
		}

		echo ecs_iconv(EC_CHARSET, 'GB2312', $data);
		exit();
	}

	$smarty->assign('goods_stats', $goods_stats);
	$smarty->assign('action_link', array('href' => 'ads.php?act=list', 'text' => $_LANG['ad_list']));
	$smarty->assign('action_link2', array('href' => 'adsense.php?act=download', 'text' => $_LANG['download_ad_statistics']));
	$smarty->assign('ur_here', $_LANG['adsense_js_stats']);
	$smarty->assign('lang', $_LANG);
	assign_query_info();
	$smarty->display('adsense.htm');
}

?>
