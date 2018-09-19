<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/cls_json.php';
header('Content-type: text/html; charset=' . EC_CHARSET);
$type = (!empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 0);
$parent = (!empty($_REQUEST['parent']) ? intval($_REQUEST['parent']) : 0);
$action = (!empty($_REQUEST['act']) ? trim($_REQUEST['act']) : '');
$arr['regions'] = get_regions($type, $parent);

if ($action == 'consigne') {
	$arr['type'] = $type + 1;
	$smarty->assign('type', $arr['type']);
	$smarty->assign('regions_list', $arr['regions']);
	$arr['content'] = $smarty->fetch('library/dialog.lbi');
}
else {
	$arr['type'] = $type;
	$arr['target'] = !empty($_REQUEST['target']) ? stripslashes(trim($_REQUEST['target'])) : '';
	$arr['target'] = htmlspecialchars($arr['target']);
}

$json = new JSON();
echo $json->encode($arr);

?>
