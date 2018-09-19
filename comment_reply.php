<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/cls_json.php';
if (!isset($_REQUEST['cmt']) && !isset($_REQUEST['act'])) {
	ecs_header("Location: ./\n");
	exit();
}

$json = new JSON();
$result = array('error' => 0, 'message' => '', 'content' => '');
$cmt = new stdClass();
$cmt->id = !empty($_GET['id']) ? json_str_iconv($_GET['id']) : 0;
$cmt->type = !empty($_GET['type']) ? intval($_GET['type']) : 0;
$cmt->page = isset($_GET['page']) && (0 < intval($_GET['page'])) ? intval($_GET['page']) : 1;
$cmt->libType = isset($_GET['libType']) && (0 < intval($_GET['libType'])) ? intval($_GET['libType']) : 0;

if ($result['error'] == 0) {
	$id = explode('|', $cmt->id);
	$goods_id = $id[0];
	$comment_id = $id[1];

	if ($cmt->libType == 1) {
		$comment_reply = get_reply_list($goods_id, $comment_id, $cmt->type, $cmt->page, $cmt->libType, 10);
	}
	else {
		$comment_reply = get_reply_list($goods_id, $comment_id, $cmt->type, $cmt->page, $cmt->libType);
	}

	$smarty->assign('comment_type', $cmt->type);
	$smarty->assign('goods_id', $goods_id);
	$smarty->assign('comment_id', $comment_id);
	$smarty->assign('reply_list', $comment_reply['reply_list']);
	$smarty->assign('reply_pager', $comment_reply['reply_pager']);
	$smarty->assign('reply_count', $comment_reply['reply_count']);
	$smarty->assign('reply_size', $comment_reply['reply_size']);
	$result['comment_id'] = $comment_id;

	if ($cmt->libType == 1) {
		$result['content'] = $smarty->fetch('library/comment_repay.lbi');
	}
	else {
		$result['content'] = $smarty->fetch('library/comment_reply.lbi');
	}
}

echo $json->encode($result);

?>
