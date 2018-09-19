<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function get_complaint_list()
{
	$result = get_filter();

	if ($result === false) {
		$where = ' WHERE 1 ';
		$filter = array();
		$filter['handle_type'] = !empty($_REQUEST['handle_type']) ? $_REQUEST['handle_type'] : '-1';
		$filter['keywords'] = !empty($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '';
		$filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
		$adminru = get_admin_ru_id();

		if (0 < $adminru['rs_id']) {
			$filter['rs_id'] = $adminru['rs_id'];
		}

		if ($filter['keywords']) {
			$where .= ' AND (user_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' OR order_sn LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\')';
		}

		if ($filter['handle_type'] != '-1') {
			$handle_type = $filter['handle_type'];

			if ($filter['handle_type'] == 5) {
				$handle_type = 0;
			}

			$where .= ' AND complaint_state = \'' . $handle_type . '\'';
		}

		$where .= get_rs_null_where('ru_id', $filter['rs_id']);
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('complaint') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT complaint_id,order_id,order_sn,user_id,user_name,ru_id,shop_name,title_id,complaint_content,add_time,complaint_handle_time,' . 'admin_id,appeal_messg,appeal_time,end_handle_time,end_admin_id,complaint_state,complaint_active FROM' . $GLOBALS['ecs']->table('complaint') . (' ' . $where . ' ORDER BY add_time DESC ');
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
		$rows['admin_name'] = $GLOBALS['db']->getOne('SELECT user_name FROM' . $GLOBALS['ecs']->table('admin_user') . ' WHERE user_id = \'' . $rows['admin_id'] . '\' LIMIT 1');

		if (0 < $rows['title_id']) {
			$sql_title = 'SELECT title_name FROM ' . $GLOBALS['ecs']->table('complain_title') . 'WHERE title_id = \'' . $rows['title_id'] . '\'';
			$rows['title_name'] = $GLOBALS['db']->getOne($sql_title);
		}

		if (0 < $rows['add_time']) {
			$rows['add_time'] = local_date('Y-m-d H:i:s', $rows['add_time']);
		}

		$sql = 'SELECT img_file ,img_id FROM ' . $GLOBALS['ecs']->table('complaint_img') . ' WHERE complaint_id = \'' . $rows['complaint_id'] . '\' ORDER BY  img_id DESC';
		$img_list = $GLOBALS['db']->getAll($sql);

		if (!empty($img_list)) {
			foreach ($img_list as $k => $v) {
				$img_list[$k]['img_file'] = get_image_path($v['img_id'], $v['img_file']);
			}
		}

		$rows['img_list'] = $img_list;
		$sql = 'SELECT img_file ,img_id FROM ' . $GLOBALS['ecs']->table('appeal_img') . ' WHERE complaint_id = \'' . $rows['complaint_id'] . '\' ORDER BY  img_id DESC';
		$appeal_img = $GLOBALS['db']->getAll($sql);

		if (!empty($appeal_img)) {
			foreach ($appeal_img as $k => $v) {
				$appeal_img[$k]['img_file'] = get_image_path($v['img_id'], $v['img_file']);
			}
		}

		$rows['appeal_img'] = $appeal_img;
		$rows['has_talk'] = 0;

		if (1 < $rows['complaint_state']) {
			$sql = 'SELECT view_state FROM' . $GLOBALS['ecs']->table('complaint_talk') . 'WHERE complaint_id=\'' . $rows['complaint_id'] . '\' ORDER BY talk_time DESC';
			$talk_list = $GLOBALS['db']->getAll($sql);

			if ($talk_list) {
				foreach ($talk_list as $k => $v) {
					if ($v['view_state']) {
						$view_state = explode(',', $v['view_state']);

						if (!in_array('admin', $view_state)) {
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

function get_complaint_title_list()
{
	$result = get_filter();

	if ($result === false) {
		$where = ' WHERE 1 ';
		$filter = array();
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('complain_title') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT title_id , title_name , title_desc,is_show FROM' . $GLOBALS['ecs']->table('complain_title') . (' ' . $where . ' ORDER BY title_id DESC LIMIT ') . $filter['start'] . ',' . $filter['page_size'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$list = $GLOBALS['db']->getAll($sql);
	return array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
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
$exc_title = new exchange($ecs->table('complain_title'), $db, 'title_id', 'title_name');
$exc = new exchange($ecs->table('complaint'), $db, 'complaint_id', 'title_id');

if ($_REQUEST['act'] == 'list') {
	admin_priv('complaint');
	$smarty->assign('ur_here', $_LANG['13_complaint']);
	$smarty->assign('action_link', array('text' => $_LANG['13_complaint'], 'href' => 'complaint.php?act=list'));
	$smarty->assign('action_link1', array('text' => $_LANG['complain_title'], 'href' => 'complaint.php?act=title'));
	$smarty->assign('action_link2', array('text' => $_LANG['report_conf'], 'href' => 'complaint.php?act=complaint_conf'));
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
	$complaint_id = !empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0;
	$smarty->assign('ur_here', $_LANG['complaint_view']);
	$smarty->assign('action_link', array('text' => $_LANG['13_complaint'], 'href' => 'complaint.php?act=list'));
	$complaint_info = get_complaint_info($complaint_id);
	$order_info = order_info($complaint_info['order_id']);
	$order_info['order_goods'] = get_order_goods_toInfo($order_info['order_id']);
	$order_info['status'] = $_LANG['os'][$order_info['order_status']] . ',' . $_LANG['ps'][$order_info['pay_status']] . ',' . $_LANG['ss'][$order_info['shipping_status']];
	$talk_list = checkTalkView($complaint_id);
	$smarty->assign('talk_list', $talk_list);
	$smarty->assign('complaint_info', $complaint_info);
	$smarty->assign('order_info', $order_info);
	$smarty->display('complaint_view.dwt');
}
else if ($_REQUEST['act'] == 'handle') {
	admin_priv('complaint');
	$complaint_id = !empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0;
	$complaint_state = !empty($_REQUEST['complaint_state']) ? intval($_REQUEST['complaint_state']) : 0;
	$end_handle_messg = !empty($_REQUEST['end_handle_messg']) ? trim($_REQUEST['end_handle_messg']) : '';
	$ru_id = $db->getOne('SELECT ru_id FROM' . $ecs->table('complaint') . ('WHERE complaint_id = \'' . $complaint_id . '\''));
	$time = gmtime();

	if (isset($_POST['abopt_comp'])) {
		if ($complaint_state == 0 && $ru_id == 0) {
			$sql = 'SELECT order_id FROM' . $ecs->table('complaint') . ('WHERE complaint_id = \'' . $complaint_id . '\'');
			$order_id = $db->getOne($sql);
			$sql = 'UPDATE' . $ecs->table('order_info') . ('SET is_frozen = 1 WHERE order_id = \'' . $order_id . '\'');
			$db->query($sql);
			$complaint_state = 2;
		}
		else {
			$complaint_state = $complaint_state + 1;
		}

		$sql = 'UPDATE' . $ecs->table('complaint') . ('SET complaint_state = \'' . $complaint_state . '\' ,complaint_handle_time = \'' . $time . '\' ,complaint_active=1,admin_id = \'') . $_SESSION['admin_id'] . ('\' WHERE complaint_id = \'' . $complaint_id . '\'');
	}
	else if (isset($_POST['close_comp'])) {
		$sql = 'UPDATE' . $ecs->table('complaint') . ('SET complaint_state = 4 ,end_handle_time = \'' . $time . '\',end_admin_id = \'') . $_SESSION['admin_id'] . ('\',end_handle_messg = \'' . $end_handle_messg . '\' ' . $set . '  WHERE complaint_id = \'' . $complaint_id . '\'');
	}

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
	$talk_id = !empty($_REQUEST['talk_id']) ? intval($_REQUEST['talk_id']) : 0;
	$complaint_id = !empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0;
	$talk_content = !empty($_REQUEST['talk_content']) ? trim($_REQUEST['talk_content']) : '';
	$type = !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;

	if ($type == 0) {
		$complaint_talk = array('complaint_id' => $complaint_id, 'talk_member_id' => $adminru['ru_id'], 'talk_member_name' => $_SESSION['admin_name'], 'talk_member_type' => 3, 'talk_content' => $talk_content, 'talk_time' => gmtime(), 'view_state' => 'admin');
		$db->autoExecute($ecs->table('complaint_talk'), $complaint_talk, 'INSERT');
	}
	else {
		if ($type == 2 || $type == 3) {
			$talk_state = 2;

			if ($type == 3) {
				$talk_state = 1;
			}

			$complaint_talk = array('talk_state' => $talk_state, 'admin_id' => $_SESSION['admin_id']);
			$db->autoExecute($ecs->table('complaint_talk'), $complaint_talk, 'UPDATE', 'complaint_id=\'' . $complaint_id . '\' AND talk_id =\'' . $talk_id . '\'');
		}
	}

	$talk_list = checkTalkView($complaint_id);
	$smarty->assign('talk_list', $talk_list);
	$result['content'] = $smarty->fetch('library/talk_list.lbi');
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
else if ($_REQUEST['act'] == 'title') {
	admin_priv('complaint');
	$smarty->assign('ur_here', $_LANG['complain_title']);
	$smarty->assign('action_link', array('text' => $_LANG['13_complaint'], 'href' => 'complaint.php?act=list'));
	$smarty->assign('action_link1', array('text' => $_LANG['complain_title'], 'href' => 'complaint.php?act=title'));
	$smarty->assign('action_link2', array('text' => $_LANG['report_conf'], 'href' => 'complaint.php?act=complaint_conf'));
	$smarty->assign('action_link3', array('text' => $_LANG['title_add'], 'href' => 'complaint.php?act=add'));
	$title = get_complaint_title_list();
	$smarty->assign('title_info', $title['list']);
	$smarty->assign('filter', $title['filter']);
	$smarty->assign('record_count', $title['record_count']);
	$smarty->assign('page_count', $title['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('act_type', $_REQUEST['act']);
	assign_query_info();
	$smarty->display('complaint_title.dwt');
}
else if ($_REQUEST['act'] == 'title_query') {
	check_authz_json('complaint');
	$title = get_complaint_title_list();
	$smarty->assign('title_info', $title['list']);
	$smarty->assign('filter', $title['filter']);
	$smarty->assign('record_count', $title['record_count']);
	$smarty->assign('page_count', $title['page_count']);
	make_json_result($smarty->fetch('complaint_title.dwt'), '', array('filter' => $title['filter'], 'page_count' => $title['page_count']));
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		admin_priv('complaint');
		$smarty->assign('ur_here', $_LANG['title_add']);
		$smarty->assign('action_link', array('text' => $_LANG['complain_title'], 'href' => 'complaint.php?act=title'));
		$title_id = !empty($_REQUEST['title_id']) ? intval($_REQUEST['title_id']) : 0;

		if ($_REQUEST['act'] == 'add') {
			$form_action = 'insert';
		}
		else {
			$form_action = 'update';
			$sql = 'SELECT title_id , title_name , title_desc ,is_show FROM' . $ecs->table('complain_title') . ('WHERE title_id = \'' . $title_id . '\'');
			$complaint_title_info = $db->getRow($sql);
			$smarty->assign('complaint_title_info', $complaint_title_info);
		}

		$smarty->assign('form_action', $form_action);
		$smarty->display('complaint_title_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
			admin_priv('complaint');
			$title_name = !empty($_REQUEST['title_name']) ? trim($_REQUEST['title_name']) : '';
			$title_id = !empty($_REQUEST['title_id']) ? intval($_REQUEST['title_id']) : 0;
			$title_desc = !empty($_REQUEST['title_desc']) ? trim($_REQUEST['title_desc']) : '';
			$is_show = !empty($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;

			if (empty($title_name)) {
				sys_msg($_LANG['title_name_null'], 1);
			}

			if (empty($title_desc)) {
				sys_msg($_LANG['title_desc_null'], 1);
			}

			if ($_REQUEST['act'] == 'insert') {
				$is_only = $exc_title->is_only('title_name', $title_name, 0);

				if (!$is_only) {
					sys_msg(sprintf($_LANG['title_exist'], stripslashes($title_name)), 1);
				}

				$sql = 'INSERT INTO' . $ecs->table('complain_title') . ('(`title_name`,`title_desc`,`is_show`) VALUES (\'' . $title_name . '\',\'' . $title_desc . '\',\'' . $is_show . '\')');
				$db->query($sql);
				$link[0]['text'] = $_LANG['continue_add'];
				$link[0]['href'] = 'complaint.php?act=add';
				$link[1]['text'] = $_LANG['back_list'];
				$link[1]['href'] = 'complaint.php?act=title';
				sys_msg($_LANG['add_succeed'], 0, $link);
			}
			else {
				$is_only = $exc_title->is_only('title_name', $title_name, 0, 'title_id != \'' . $title_id . '\'');

				if (!$is_only) {
					sys_msg(sprintf($_LANG['title_exist'], stripslashes($title_name)), 1);
				}

				$sql = 'UPDATE' . $ecs->table('complain_title') . (' SET title_name = \'' . $title_name . '\',title_desc=\'' . $title_desc . '\',is_show = \'' . $is_show . '\' WHERE title_id = \'' . $title_id . '\'');
				$db->query($sql);
				$link[0]['text'] = $_LANG['back_list'];
				$link[0]['href'] = 'complaint.php?act=title';
				sys_msg($_LANG['edit_succeed'], 0, $link);
			}
		}
		else if ($_REQUEST['act'] == 'remove_title') {
			check_authz_json('complaint');
			$id = intval($_GET['id']);
			$exc_title->drop($id);
			$url = 'complaint.php?act=title_query&' . str_replace('act=remove_title', '', $_SERVER['QUERY_STRING']);
			ecs_header('Location: ' . $url . "\n");
			exit();
		}
		else if ($_REQUEST['act'] == 'complaint_conf') {
			if (0 < $adminru['rs_id']) {
				$url = 'complaint.php?act=list';
				ecs_header('Location: ' . $url . "\n");
			}

			admin_priv('complaint');
			require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/shop_config.php';
			$smarty->assign('ur_here', $_LANG['report_conf']);
			$smarty->assign('action_link', array('text' => $_LANG['13_complaint'], 'href' => 'complaint.php?act=list'));
			$smarty->assign('action_link1', array('text' => $_LANG['complain_title'], 'href' => 'complaint.php?act=title'));
			$smarty->assign('action_link2', array('text' => $_LANG['report_conf'], 'href' => 'complaint.php?act=complaint_conf'));
			$complaint_conf = get_up_settings('complaint_conf');
			$smarty->assign('report_conf', $complaint_conf);
			$smarty->assign('act_type', $_REQUEST['act']);
			$smarty->assign('conf_type', 'complaint_conf');
			assign_query_info();
			$smarty->display('goods_report_conf.dwt');
		}
		else if ($_REQUEST['act'] == 'toggle_show') {
			check_authz_json('complaint');
			$id = intval($_POST['id']);
			$val = intval($_POST['val']);
			$exc_title->edit('is_show = \'' . $val . '\'', $id);
			clear_cache_files();
			make_json_result($val);
		}
	}
}

?>
