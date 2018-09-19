<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_visual.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . '/includes/lib_area.php';
$area_info = get_area_info($province_id);
$area_id = $area_info['region_id'];
$where = 'regionId = \'' . $province_id . '\'';
$date = array('parent_id');
$region_id = get_table_date('region_warehouse', $where, $date, 2);
$topic_id = (empty($_REQUEST['topic_id']) ? 0 : intval($_REQUEST['topic_id']));
$preview = (!empty($_REQUEST['preview']) ? $_REQUEST['preview'] : 0);
$where = '';

if ($preview != 1) {
	$where = 'AND  ' . gmtime() . ' >= start_time AND ' . gmtime() . '<= end_time AND review_status = 3';
}

$sql = 'SELECT topic_id, user_id FROM ' . $ecs->table('topic') . 'WHERE topic_id = \'' . $topic_id . '\'' . $where;
$topic = $db->getRow($sql);

if (empty($topic)) {
	ecs_header("Location: ./\n");
	exit();
}

get_down_topictemplates($topic['topic_id'], $topic['user_id']);
$pc_page['tem'] = 'topic_' . $topic_id;
$filename = ROOT_PATH . 'data/topic' . '/topic_' . $topic['user_id'] . '/' . $pc_page['tem'];

if ($preview == 1) {
	$preview_dir = ROOT_PATH . 'data/topic' . '/topic_' . $topic['user_id'] . '/' . $pc_page['tem'] . '/temp';

	if (is_dir($preview_dir)) {
		$filename = $preview_dir;
	}
}

$pc_page['out'] = get_html_file($filename . '/pc_html.php');
$nav_page = get_html_file($filename . '/nav_html.php');
$pc_page['out'] = str_replace('../data/gallery_album/', 'data/gallery_album/', $pc_page['out'], $i);
$pc_page['out'] = str_replace('../data/seller_templates/', 'data/seller_templates/', $pc_page['out'], $i);
$pc_page['out'] = str_replace('../data/topic/', 'data/topic/', $pc_page['out'], $i);

if ($GLOBALS['_CFG']['open_oss'] == 1) {
	$bucket_info = get_bucket_info();
	$endpoint = $bucket_info['endpoint'];
}
else {
	$endpoint = (!empty($GLOBALS['_CFG']['site_domain']) ? $GLOBALS['_CFG']['site_domain'] : '');
}

if ($pc_page['out'] && $endpoint) {
	$desc_preg = get_goods_desc_images_preg($endpoint, $pc_page['out']);
	$pc_page['out'] = $desc_preg['goods_desc'];
}

$sql = 'SELECT * FROM ' . $ecs->table('topic') . ' WHERE topic_id = \'' . $topic_id . '\'';
$topic = $db->getRow($sql);
assign_template();
$position = assign_ur_here(0, $topic['title']);
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here', $position['ur_here']);
$smarty->assign('helps', get_shop_help());
$smarty->assign('show_marketprice', $_CFG['show_marketprice']);
$smarty->assign('sort_goods_arr', $sort_goods_arr);
$smarty->assign('topic', $topic);
$smarty->assign('keywords', $topic['keywords']);
$smarty->assign('description', $topic['description']);
$smarty->assign('site_domain', $_CFG['site_domain']);
$categories_pro = get_category_tree_leve_one();
$smarty->assign('categories_pro', $categories_pro);
$smarty->assign('pc_page', $pc_page);
$smarty->assign('warehouse_id', $region_id);
$smarty->assign('area_id', $area_id);
$smarty->assign('nav_page', $nav_page);
$smarty->display('topic.dwt');

?>
