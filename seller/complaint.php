<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_complaint_list()
{
	$result = get_filter();

	if ($result === false) {
		$adminru = get_admin_ru_id();
		$where = ' WHERE 1 ';
		$filter = array();
		$filter['handle_type'] = !empty($_REQUEST['handle_type']) ? $_REQUEST['handle_type'] : '-1';
		$filter['keywords'] = !empty($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '';

		if ($filter['keywords']) {
			$where .= ' AND (user_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' OR order_sn LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\')';
		}

		if ($filter['handle_type'] != '-1') {
			$where .= ' AND complaint_state = \'' . $filter['handle_type'] . '\'';
		}

		$where .= ' AND ru_id = \'' . $adminru['ru_id'] . '\' AND complaint_active = 1';
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('complaint') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT complaint_id,order_id,order_sn,user_id,user_name,ru_id,shop_name,title_id,complaint_content,add_time,complaint_handle_time,' . 'admin_id,appeal_messg,appeal_time,end_handle_time,end_admin_id,complaint_state,complaint_active FROM' . $GLOBALS['ecs']->table('complaint') . ' ' . $where . ' ORDER BY add_time DESC ';
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$arr = array();
	$k = 0;

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		if (0 < $rows['title_id']) {
			$sql_title = 'SELECT title_name FROM ' . $GLOBALS['ecs']->table('complain_title') . 'WHERE title_id = \'' . $rows['title_id'] . '\'';
			$rows['title_name'] = $GLOBALS['db']->getOne($sql_title);
		}

		$sql = 'SELECT img_file ,img_id FROM ' . $GLOBALS['ecs']->table('complaint_img') . ' WHERE complaint_id = \'' . $rows['complaint_id'] . '\' ORDER BY  img_id DESC';
		$img_list = $GLOBALS['db']->getAll($sql);

		if (!empty($img_list)) {
			foreach ($img_list as $k => $v) {
				$img_list[$k]['img_file'] = get_image_path($v['img_id'], $v['img_file']);
			}
		}

		$rows['img_list'] = $img_list;
		$rows['has_talk'] = 0;

		if (1 < $rows['complaint_state']) {
			$sql = 'SELECT view_state FROM' . $GLOBALS['ecs']->table('complaint_talk') . 'WHERE complaint_id=\'' . $rows['complaint_id'] . '\' ORDER BY talk_time DESC';
			$talk_list = $GLOBALS['db']->getAll($sql);

			if ($talk_list) {
				foreach ($talk_list as $k => $v) {
					if ($v['view_state']) {
						$view_state = explode(',', $v['view_state']);

						if (!in_array('seller', $view_state)) {
							$rows['has_talk'] = 1;
							break;
						}
					}
				}
			}
		}

		$arr[] = $rows;
	}

	return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$adminru = get_admin_ru_id();
$exc = new exchange($ecs->table('complaint'), $db, 'complaint_id', 'title_id');
$smarty->assign('menu_select', array('action' => '04_order', 'current' => '11_complaint'));
$smarty->assign('primary_cat', $_LANG['04_order']);

if ($_REQUEST['act'] == 'list') {
	admin_priv('complaint');
	$smarty->assign('ur_here', $_LANG['11_complaint']);
	$complaint_list = get_complaint_list();
	$smarty->assign('complaint_list', $complaint_list['list']);
	$smarty->assign('filter', $complaint_list['filter']);
	$smarty->assign('record_count', $complaint_list['record_count']);
	$smarty->assign('page_count', $complaint_list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('act_type', $_REQUEST['act']);
	assign_query_info();
	$smarty->display('complaint.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	check_authz_json('complaint');
	$complaint_list = get_complaint_list();
	$smarty->assign('complaint_list', $complaint_list['list']);
	$smarty->assign('filter', $complaint_list['filter']);
	$smarty->assign('record_count', $complaint_list['record_count']);
	$smarty->assign('page_count', $complaint_list['page_count']);
	make_json_result($smarty->fetch('complaint.dwt'), '', array('filter' => $complaint_list['filter'], 'page_count' => $complaint_list['page_count']));
}
else if ($_REQUEST['act'] == 'view') {
	admin_priv('complaint');
	require_once ROOT_PATH . 'includes/lib_order.php';
	$complaint_id = (!empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0);
	$smarty->assign('ur_here', $_LANG['complaint_view']);
	$smarty->assign('action_link', array('text' => $_LANG['11_complaint'], 'href' => 'complaint.php?act=list'));
	$complaint_info = get_complaint_info($complaint_id);
	$talk_list = checkTalkView($complaint_id, 'seller');
	$order_info = order_info($complaint_info['order_id']);
	$order_info['order_goods'] = get_order_goods_toInfo($order_info['order_id']);
	$order_info['status'] = $_LANG['os'][$order_info['order_status']] . ',' . $_LANG['ps'][$order_info['pay_status']] . ',' . $_LANG['ss'][$order_info['shipping_status']];
	$smarty->assign('talk_list', $talk_list);
	$smarty->assign('complaint_info', $complaint_info);
	$smarty->assign('order_info', $order_info);
	$smarty->display('complaint_view.dwt');
}
else if ($_REQUEST['act'] == 'upload_img') {
	check_authz_json('complaint');
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('content' => '', 'sgs' => '');
	$complaint_id = (!empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0);
	$order_id = (!empty($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0);
	$img_file = (isset($_FILES['file']) ? $_FILES['file'] : array());
	include_once ROOT_PATH . '/includes/cls_image.php';
	$image = new cls_image($_CFG['bgcolor']);
	$img_file = $image->upload_image($img_file, 'appeal_img/' . date('Ym'));

	if ($img_file === false) {
		$result['error'] = 1;
		$result['msg'] = $image->error_msg();
		exit($json->encode($result));
	}

	get_oss_add_file(array($img_file));
	$report = array('order_id' => $order_id, 'ru_id' => $adminru['ru_id'], 'img_file' => $img_file, 'complaint_id' => $complaint_id);
	$sql = 'SELECT count(*) FROM ' . $ecs->table('appeal_img') . ' WHERE complaint_id = \'' . $complaint_id . '\' AND order_id = \'' . $order_id . '\'';
	$img_count = $db->getOne($sql);
	if (($img_count < 5) && $img_file) {
		$db->autoExecute($ecs->table('appeal_img'), $report, 'INSERT');
		$result['img_id'] = $db->insert_id();
		$result['img_file'] = get_image_path($result['img_id'], $img_file);
	}
	else {
		$result['error'] = 1;
		$result['message'] = $_LANG['complaint_img_number'];
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'del_img') {
	check_authz_json('complaint');
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'message' => '');
	$complaint_id = (!empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0);
	$img_id = (!empty($_REQUEST['img_id']) ? intval($_REQUEST['img_id']) : 0);

	if (0 < $img_id) {
		$sql = 'SELECT img_file FROM' . $ecs->table('appeal_img') . ' WHERE img_id = \'' . $img_id . '\' AND complaint_id = \'' . $complaint_id . '\' LIMIT 1';
		$img_file = $db->getOne($sql);

		if ($img_file) {
			get_oss_del_file(array($img_file));
			@unlink(ROOT_PATH . $img_file);
		}

		$sql = 'DELETE FROM ' . $ecs->table('appeal_img') . ' WHERE img_id = \'' . $img_id . '\'';
		$db->query($sql);
	}
	else {
		$result['error'] = '1';
		$result['message'] = $_LANG['unknown_error'];
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('complaint');
	$id = intval($_GET['id']);
	del_complaint_img($id);
	del_complaint_img($id, 'appeal_img');
	del_complaint_talk($id);
	$exc->drop($id);
	$url = 'complaint.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'appeal_submit') {
	admin_priv('complaint');
	$complaint_id = (!empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0);
	$appeal_messg = (!empty($_REQUEST['appeal_messg']) ? trim($_REQUEST['appeal_messg']) : '');
	$state_type = (!empty($_REQUEST['state_type']) ? intval($_REQUEST['state_type']) : 0);
	$time = gmtime();
	$set = '';

	if ($state_type == 0) {
		$set = ',appeal_messg = \'' . $appeal_messg . '\',appeal_time = \'' . $time . '\'';
	}

	$sql = ' UPDATE' . $ecs->table('complaint') . ' SET complaint_state = complaint_state+1 ' . $set . ' WHERE complaint_id = \'' . $complaint_id . '\'';
	$db->query($sql);
	$link[0]['text'] = $_LANG['back_info'];
	$link[0]['href'] = 'complaint.php?act=view&complaint_id=' . $complaint_id;
	sys_msg($_LANG['handle_success'], 0, $link);
}
else if ($_REQUEST['act'] == 'talk_release') {
	check_authz_json('complaint');
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'message' => '');
	$complaint_id = (!empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0);
	$talk_content = (!empty($_REQUEST['talk_content']) ? trim($_REQUEST['talk_content']) : '');
	$type = (!empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 0);

	if ($type == 0) {
		$complaint_talk = array('complaint_id' => $complaint_id, 'talk_member_id' => $adminru['ru_id'], 'talk_member_name' => $_SESSION['seller_name'], 'talk_member_type' => 2, 'talk_content' => $talk_content, 'talk_time' => gmtime(), 'view_state' => 'seller');
		$db->autoExecute($ecs->table('complaint_talk'), $complaint_talk, 'INSERT');
	}

	$talk_list = checkTalkView($complaint_id, 'seller');
	$smarty->assign('talk_list', $talk_list);
	$result['content'] = $smarty->fetch('library/talk_list.lbi');
	exit($json->encode($result));
}

?>
