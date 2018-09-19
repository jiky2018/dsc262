<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once ROOT_PATH . 'includes/lib_clips.php';
require_once ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/user.php';
require ROOT_PATH . 'includes/cls_json.php';
if (!isset($_REQUEST['cmt']) && !isset($_REQUEST['act'])) {
	ecs_header("Location: ./\n");
	exit();
}

$_REQUEST['cmt'] = isset($_REQUEST['cmt']) ? json_str_iconv($_REQUEST['cmt']) : '';
$json = new JSON();
$result = array('error' => 0, 'message' => '', 'content' => '');
$cmt = new stdClass();
$cmt->id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
$cmt->type = !empty($_GET['type']) ? intval($_GET['type']) : 0;
$cmt->page = isset($_GET['page']) && (0 < intval($_GET['page'])) ? intval($_GET['page']) : 1;

if ($result['error'] == 0) {
	$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('collect_goods') . ' WHERE user_id=\'' . $_SESSION['user_id'] . '\'');

	if (defined('THEME_EXTENSION')) {
		$size = 12;
	}
	else {
		$size = 10;
	}

	$collection_goods = get_collection_goods($_SESSION['user_id'], $record_count, $cmt->page, 'collection_goods_gotoPage', $size);
	$smarty->assign('goods_list', $collection_goods['goods_list']);
	$smarty->assign('pager', $collection_goods['pager']);
	$smarty->assign('count', $collection_goods['record_count']);
	$smarty->assign('size', $collection_goods['size']);
	$smarty->assign('url', $ecs->url());
	$lang_list = array('UTF8' => $_LANG['charset']['utf8'], 'GB2312' => $_LANG['charset']['zh_cn'], 'BIG5' => $_LANG['charset']['zh_tw']);
	$smarty->assign('lang_list', $lang_list);
	$smarty->assign('user_id', $_SESSION['user_id']);
	$smarty->assign('lang', $_LANG);
	$result['content'] = $smarty->fetch('library/collection_goods_list.lbi');
	$result['pages'] = $smarty->fetch('library/pages_ajax.lbi');
}

echo $json->encode($result);

?>
