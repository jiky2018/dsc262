<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
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
	$comments = assign_comments_single($cmt->id, $cmt->type, $cmt->page);
	$smarty->assign('comment_type', $cmt->type);
	$smarty->assign('id', $cmt->id);
	$smarty->assign('username', $_SESSION['user_name']);
	$smarty->assign('email', $_SESSION['email']);
	$smarty->assign('comments_single', $comments['comments']);
	$smarty->assign('single_pager', $comments['pager']);
	if ((intval($_CFG['captcha']) & CAPTCHA_COMMENT) && (0 < gd_version())) {
		$smarty->assign('enabled_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	$result['message'] = $_CFG['comment_check'] ? $_LANG['cmt_submit_wait'] : $_LANG['cmt_submit_done'];
	$result['content'] = $smarty->fetch('library/comments_single_list.lbi');
}

echo $json->encode($result);

?>
