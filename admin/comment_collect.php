<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php';
require_once ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_comment.php';

if ($_REQUEST['act'] == 'comment') {
	$goods_id = intval($_REQUEST['goods_id']);
	$sql = 'SELECT * FROM ' . $ecs->table('goods') . ' WHERE goods_id = \'' . $goods_id . '\'';
	$goods = $db->getRow($sql);
	$goods['format_add_time'] = date('Y-m-d', $goods['add_time']);
	$smarty->assign('goods', $goods);
	$smarty->assign('goods_id', $goods_id);
	$smarty->display('comment.htm');
}
else if ($_REQUEST['act'] == 'comment_preview') {
	$taoUrl = (isset($_REQUEST['taoUrl']) ? trim($_REQUEST['taoUrl']) : '');
	$pageNum = (isset($_REQUEST['pageNum']) ? trim($_REQUEST['pageNum']) : '');
	$goods_name = (isset($_REQUEST['goods_name']) ? trim($_REQUEST['goods_name']) : '');
	$goods_id = (isset($_REQUEST['goods_id']) ? trim($_REQUEST['goods_id']) : '');

	if (empty($taoUrl)) {
		$link[] = array('href' => 'goods.php?act=list', 'text' => '商品列表');
		sys_msg('淘宝商品URL不能为空', 1, $link);
	}

	$file_contents = get_file_get_contents($taoUrl, $pageNum);
	$tao_list = get_array_merge($file_contents);
	$comment_list = get_tao_list($tao_list, $goods_name, $goods_id);
	$num = count($comment_list);
	$smarty->assign('comment_list', $comment_list);
	$smarty->assign('num', $num);
	$smarty->assign('goods_id', $goods_id);
	$smarty->display('comment_preview.htm');
}
else if ($_REQUEST['act'] == 'comment_batch_import') {
	$ids = (isset($_POST['checkboxes']) ? $_POST['checkboxes'] : array());
	$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : array());
	$id_value = (isset($_POST['id_value']) ? $_POST['id_value'] : array());
	$goods_id = (isset($_POST['goods_id']) ? $_POST['goods_id'] : 0);
	$usernames = (isset($_POST['usernames']) ? $_POST['usernames'] : array());
	$contents = (isset($_POST['contents']) ? $_POST['contents'] : array());
	$times = (isset($_POST['times']) ? $_POST['times'] : array());
	$array_name = array();
	$sql = 'SELECT distinct user_name FROM ' . $ecs->table('comment') . ' WHERE id_value=' . $_GET['goods_id'];
	$names = $db->getAll($sql);

	for ($i = 0; $i < count($names); $i++) {
		$array_name[] = $names[$i]['user_name'];
	}

	if (0 < count($ids)) {
		$ikey = 0;

		foreach ($ids as $id) {
			$id = $id - 1;
			$user_name = $usernames[$id];

			if (in_array($user_name, $array_name)) {
				continue;
			}

			$comment_type = 0;
			$email = '';
			$comment_rank = 5;
			$ip_address = real_ip();
			$status = 1;
			$parent_id = 0;
			$userId = 0;
			$user_name = $usernames[$id];
			$content = $contents[$id];
			$add_time = local_strtotime($times[$id]);

			if ($content) {
				$sql = 'INSERT INTO ' . $ecs->table('comment') . '(comment_type, id_value, email, user_name, content, comment_rank, add_time, ip_address, status, parent_id, user_id) VALUES ' . '(\'' . $comment_type . '\', \'' . $goods_id . '\', \'' . $email . '\', \'' . $user_name . '\', \'' . $content . '\', \'' . $comment_rank . '\', \'' . $add_time . '\', \'' . $ip_address . '\', \'' . $status . '\', \'' . $parent_id . '\', \'' . $userId . '\')';
				$result = $db->query($sql);
				$ikey++;
			}
		}

		$comments_number = $ikey;
		$sql = 'UPDATE ' . $ecs->table('goods') . ' SET comments_number=comments_number + ' . $comments_number . ' WHERE goods_id=\'' . $goods_id . '\'';
		$db->query($sql);
		$success_failure = '添加成功';
	}
	else {
		$success_failure = '失败';
	}

	$link[] = array('href' => 'goods.php?act=list', 'text' => '商品列表');
	sys_msg($success_failure, 1, $link);
}

?>
