<?php
//zend by 商创网络 Q Q:123456    禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_area.php';
assign_template();
$position = assign_ur_here(0, $_LANG['tag_cloud']);
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here', $position['ur_here']);
$smarty->assign('categories', get_categories_tree());
$smarty->assign('helps', get_shop_help());
$vote = get_vote();

if (!empty($vote)) {
	$smarty->assign('vote_id', $vote['id']);
	$smarty->assign('vote', $vote['content']);
}

assign_dynamic('tag_cloud');
$tags = get_tags();

if (!empty($tags)) {
	include_once ROOT_PATH . 'includes/lib_clips.php';
	color_tag($tags);
}

$smarty->assign('tags', $tags);
$smarty->display('tag_cloud.dwt');

?>
