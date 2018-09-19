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
$cmt->id = !empty($_GET['id']) ? json_str_iconv($_GET['id']) : 0;
$cmt->type = !empty($_GET['type']) ? intval($_GET['type']) : 0;
$cmt->page = isset($_GET['page']) && (0 < intval($_GET['page'])) ? intval($_GET['page']) : 1;

if ($result['error'] == 0) {
	$id = explode('|', $cmt->id);
	$goods_id = $id[0];
	$dis_type = $id[1];
	$revType = $id[2];
	$sort = $id[3];

	if ($revType) {
		$size = 10;
	}
	else {
		$size = 40;
	}

	if (!$sort) {
		$sort = 'add_time';
	}

	$discuss_list = get_discuss_all_list($goods_id, $dis_type, $cmt->page, $size, $revType, $sort);
	$smarty->assign('discuss_list', $discuss_list);

	if ($revType) {
		if ($dis_type == 4) {
			$all_count = get_commentImg_count($goods_id);
		}
		else {
			$all_count = get_discuss_type_count($goods_id, $revType);
		}

		$smarty->assign('all_count', $all_count);
		$smarty->assign('goods_id', $goods_id);
		$result['content'] = $smarty->fetch('library/comments_discuss_list1.lbi');
	}
	else {
		$result['content'] = $smarty->fetch('library/comments_discuss_list2.lbi');
	}
}

echo $json->encode($result);

?>
