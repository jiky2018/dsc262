<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . '/includes/lib_area.php';
$display_mode = (empty($_GET['display_mode']) ? 'javascript' : $_GET['display_mode']);

if ($display_mode == 'javascript') {
	$charset_array = array('UTF8', 'GBK', 'gbk', 'utf8', 'GB2312', 'gb2312');

	if (!in_array($charset, $charset_array)) {
		$charset = 'UTF8';
	}

	header('content-type: application/x-javascript; charset=' . ($charset == 'UTF8' ? 'utf-8' : $charset));
}

$cache_id = sprintf('%X', crc32($_SERVER['QUERY_STRING']));
$goodsid = intval($_GET['gid']);
$userid = intval($_GET['u']);
$type = intval($_GET['type']);
$tpl = ROOT_PATH . DATA_DIR . '/affiliate.html';

if (!$smarty->is_cached($tpl, $cache_id)) {
	$time = gmtime();
	$goods_url = $ecs->url() . 'goods.php?u=' . $userid . '&id=';
	$goods = get_goods_info($goodsid);
	$goods['goods_thumb'] = (strpos($goods['goods_thumb'], 'http://') === false) && (strpos($goods['goods_thumb'], 'https://') === false) ? $ecs->url() . $goods['goods_thumb'] : $goods['goods_thumb'];
	$goods['goods_img'] = (strpos($goods['goods_img'], 'http://') === false) && (strpos($goods['goods_img'], 'https://') === false) ? $ecs->url() . $goods['goods_img'] : $goods['goods_img'];
	$goods['shop_price'] = price_format($goods['shop_price']);
	$smarty->assign('goods', $goods);
	$smarty->assign('userid', $userid);
	$smarty->assign('type', $type);
	$smarty->assign('url', $ecs->url());
	$smarty->assign('goods_url', $goods_url);
}

$output = $smarty->fetch($tpl, $cache_id);
$output = str_replace("\r", '', $output);
$output = str_replace("\n", '', $output);

if ($display_mode == 'javascript') {
	echo 'document.write(\'' . $output . '\');';
}
else if ($display_mode == 'iframe') {
	echo $output;
}

?>
