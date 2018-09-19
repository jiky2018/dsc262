<?php
//zend by 商创网络 Q Q:123456    禁止倒卖 一经发现停止任何服务
function get_message_list()
{
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'sent_time' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$filter['msg_type'] = empty($_REQUEST['msg_type']) ? 0 : intval($_REQUEST['msg_type']);

	switch ($filter['msg_type']) {
	case 1:
		$where = ' a.receiver_id=\'' . $_SESSION['seller_id'] . '\'';
		break;

	case 2:
		$where = ' a.sender_id=\'' . $_SESSION['seller_id'] . '\' AND a.deleted=\'0\'';
		break;

	case 3:
		$where = ' a.readed=\'0\' AND a.receiver_id=\'' . $_SESSION['seller_id'] . '\' AND a.deleted=\'0\'';
		break;

	case 4:
		$where = ' a.readed=\'1\' AND a.receiver_id=\'' . $_SESSION['seller_id'] . '\' AND a.deleted=\'0\'';
		break;

	default:
		$where = ' (a.receiver_id=\'' . $_SESSION['seller_id'] . '\' OR a.sender_id=\'' . $_SESSION['seller_id'] . '\') AND a.deleted=\'0\'';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('admin_message') . ' AS a WHERE 1 AND ' . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT a.message_id,a.sender_id,a.receiver_id,a.sent_time,a.read_time,a.deleted,a.title,a.message,b.user_name' . ' FROM ' . $GLOBALS['ecs']->table('admin_message') . ' AS a,' . $GLOBALS['ecs']->table('admin_user') . ' AS b ' . (' WHERE a.sender_id=b.user_id AND ' . $where . ' ') . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$row[$key]['sent_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['sent_time']);
		$row[$key]['read_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['read_time']);
	}

	$arr = array('item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$_REQUEST['act'] = trim($_REQUEST['act']);

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}

$adminru = get_admin_ru_id();

if ($_REQUEST['act'] == 'list') {
	admin_priv('admin_message');
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['msg_list']);
	$smarty->assign('action_link', array('text' => $_LANG['send_msg'], 'href' => 'message.php?act=send', 'class' => 'icon-plus'));
	$list = get_message_list();
	$smarty->assign('message_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('message_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$list = get_message_list();
	$smarty->assign('message_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('message_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'send') {
	admin_priv('admin_message');
	$smarty->assign('primary_cat', $_LANG['10_priv_admin']);
	$smarty->assign('menu_select', array('action' => '10_priv_admin', 'current' => 'admin_message'));
	$admin_list = $db->getAll('SELECT user_id, user_name FROM ' . $ecs->table('admin_user') . ' WHERE parent_id > 0 AND ru_id = \'' . $adminru['ru_id'] . '\' OR action_list = \'all\'');
	$smarty->assign('ur_here', $_LANG['send_msg']);
	$smarty->assign('action_link', array('text' => $_LANG['msg_list'], 'href' => 'message.php?act=list', 'class' => 'icon-reply'));
	$smarty->assign('action', 'add');
	$smarty->assign('form_act', 'insert');
	$smarty->assign('admin_list', $admin_list);
	assign_query_info();
	$smarty->display('message_info.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	admin_priv('admin_message');
	$rec_arr = $_POST['receiver_id'];

	if ($rec_arr[0] == 0) {
		$result = $db->query('SELECT user_id FROM ' . $ecs->table('admin_user') . 'WHERE user_id !=' . $_SESSION['seller_id']);

		while ($rows = $db->FetchRow($result)) {
			$sql = 'INSERT INTO ' . $ecs->table('admin_message') . ' (sender_id, receiver_id, sent_time, ' . 'read_time, readed, deleted, title, message) ' . 'VALUES (\'' . $_SESSION['seller_id'] . '\', \'' . $rows['user_id'] . '\', \'' . gmtime() . '\', ' . ('0, \'0\', \'0\', \'' . $_POST['title'] . '\', \'' . $_POST['message'] . '\')');
			$db->query($sql);
		}

		$link[0]['text'] = $_LANG['back_list'];
		$link[0]['href'] = 'message.php?act=list';
		$link[1]['text'] = $_LANG['continue_send_msg'];
		$link[1]['href'] = 'message.php?act=send';
		sys_msg($_LANG['send_msg'] . '&nbsp;' . $_LANG['action_succeed'], 0, $link);
		admin_log(admin_log($_LANG['send_msg']), 'add', 'admin_message');
	}
	else {
		foreach ($rec_arr as $key => $id) {
			$sql = 'INSERT INTO ' . $ecs->table('admin_message') . ' (sender_id, receiver_id, ' . 'sent_time, read_time, readed, deleted, title, message) ' . 'VALUES (\'' . $_SESSION['seller_id'] . ('\', \'' . $id . '\', \'') . gmtime() . '\', ' . ('\'0\', \'0\', \'0\', \'' . $_POST['title'] . '\', \'' . $_POST['message'] . '\')');
			$db->query($sql);
		}

		admin_log(addslashes($_LANG['send_msg']), 'add', 'admin_message');
		$link[0]['text'] = $_LANG['back_list'];
		$link[0]['href'] = 'message.php?act=list';
		$link[1]['text'] = $_LANG['continue_send_msg'];
		$link[1]['href'] = 'message.php?act=send';
		sys_msg($_LANG['send_msg'] . '&nbsp;' . $_LANG['action_succeed'], 0, $link);
	}
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('admin_message');
	$id = intval($_REQUEST['id']);
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '09_topic'));
	$admin_list = $db->getAll('SELECT user_id, user_name FROM ' . $ecs->table('admin_user'));
	$sql = 'SELECT message_id, receiver_id, title, message' . 'FROM ' . $ecs->table('admin_message') . (' WHERE message_id=\'' . $id . '\'');
	$msg_arr = $db->getRow($sql);
	$smarty->assign('ur_here', $_LANG['edit_msg']);
	$smarty->assign('action_link', array('href' => 'message.php?act=list', 'text' => $_LANG['msg_list']));
	$smarty->assign('form_act', 'update');
	$smarty->assign('admin_list', $admin_list);
	$smarty->assign('msg_arr', $msg_arr);
	assign_query_info();
	$smarty->display('message_info.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	admin_priv('admin_message');
	$msg_arr = array();
	$msg_arr = $db->getRow('SELECT * FROM ' . $ecs->table('admin_message') . (' WHERE message_id=\'' . $_POST['id'] . '\''));
	$sql = 'UPDATE ' . $ecs->table('admin_message') . ' SET ' . ('title = \'' . $_POST['title'] . '\',') . ('message = \'' . $_POST['message'] . '\'') . ('WHERE sender_id = \'' . $msg_arr['sender_id'] . '\' AND sent_time=\'' . $msg_arr['send_time'] . '\'');
	$db->query($sql);
	$link[0]['text'] = $_LANG['back_list'];
	$link[0]['href'] = 'message.php?act=list';
	sys_msg($_LANG['edit_msg'] . ' ' . $_LANG['action_succeed'], 0, $link);
	admin_log(addslashes($_LANG['edit_msg']), 'edit', 'admin_message');
}
else if ($_REQUEST['act'] == 'view') {
	admin_priv('admin_message');
	$msg_id = intval($_REQUEST['id']);
	$smarty->assign('primary_cat', $_LANG['10_priv_admin']);
	$smarty->assign('menu_select', array('action' => '10_priv_admin', 'current' => 'admin_message'));
	$msg_arr = array();
	$sql = 'SELECT a.*, b.user_name ' . 'FROM ' . $ecs->table('admin_message') . ' AS a ' . 'LEFT JOIN ' . $ecs->table('admin_user') . ' AS b ON b.user_id = a.sender_id ' . ('WHERE a.message_id = \'' . $msg_id . '\'');
	$msg_arr = $db->getRow($sql);
	$msg_arr['title'] = nl2br(htmlspecialchars($msg_arr['title']));
	$msg_arr['message'] = nl2br(htmlspecialchars($msg_arr['message']));

	if ($msg_arr['readed'] == 0) {
		$msg_arr['read_time'] = gmtime();
		$sql = 'UPDATE ' . $ecs->table('admin_message') . ' SET ' . 'read_time = \'' . $msg_arr['read_time'] . '\', ' . 'readed = \'1\' ' . ('WHERE message_id = \'' . $msg_id . '\'');
		$db->query($sql);
	}

	$smarty->assign('ur_here', $_LANG['view_msg']);
	$smarty->assign('action_link', array('href' => 'message.php?act=list', 'text' => $_LANG['msg_list']));
	$smarty->assign('admin_user', $_SESSION['admin_name']);
	$smarty->assign('msg_arr', $msg_arr);
	assign_query_info();
	$smarty->display('message_view.dwt');
}
else if ($_REQUEST['act'] == 'reply') {
	admin_priv('admin_message');
	$smarty->assign('primary_cat', $_LANG['10_priv_admin']);
	$smarty->assign('menu_select', array('action' => '10_priv_admin', 'current' => 'admin_message'));
	$msg_id = intval($_REQUEST['id']);
	$msg_val = array();
	$sql = 'SELECT a.*, b.user_name ' . 'FROM ' . $ecs->table('admin_message') . ' AS a ' . 'LEFT JOIN ' . $ecs->table('admin_user') . ' AS b ON b.user_id = a.sender_id ' . ('WHERE a.message_id = \'' . $msg_id . '\'');
	$msg_val = $db->getRow($sql);
	$smarty->assign('ur_here', $_LANG['reply_msg']);
	$smarty->assign('action_link', array('href' => 'message.php?act=list', 'text' => $_LANG['msg_list']));
	$smarty->assign('action', 'reply');
	$smarty->assign('form_act', 're_msg');
	$smarty->assign('msg_val', $msg_val);
	assign_query_info();
	$smarty->display('message_info.dwt');
}
else if ($_REQUEST['act'] == 're_msg') {
	admin_priv('admin_message');
	$sql = 'INSERT INTO ' . $ecs->table('admin_message') . ' (sender_id, receiver_id, sent_time, ' . 'read_time, readed, deleted, title, message) ' . 'VALUES (\'' . $_SESSION['seller_id'] . ('\', \'' . $_POST['receiver_id'] . '\', \'') . gmtime() . '\', ' . ('0, \'0\', \'0\', \'' . $_POST['title'] . '\', \'' . $_POST['message'] . '\')');
	$db->query($sql);
	$link[0]['text'] = $_LANG['back_list'];
	$link[0]['href'] = 'message.php?act=list';
	sys_msg($_LANG['send_msg'] . ' ' . $_LANG['action_succeed'], 0, $link);
	admin_log(addslashes($_LANG['send_msg']), 'add', 'admin_message');
}
else if ($_REQUEST['act'] == 'drop_msg') {
	admin_priv('admin_message');

	if (isset($_POST['checkboxes'])) {
		$count = 0;

		foreach ($_POST['checkboxes'] as $key => $id) {
			$sql = 'UPDATE ' . $ecs->table('admin_message') . ' SET ' . 'deleted = \'1\'' . ('WHERE message_id = \'' . $id . '\' AND (sender_id=\'' . $_SESSION['seller_id'] . '\' OR receiver_id=\'' . $_SESSION['seller_id'] . '\')');
			$db->query($sql);
			$count++;
		}

		admin_log('', 'remove', 'admin_message');
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'message.php?act=list');
		sys_msg(sprintf($_LANG['batch_drop_success'], $count), 0, $link);
	}
	else {
		sys_msg($_LANG['no_select_msg'], 1);
	}
}
else if ($_REQUEST['act'] == 'remove') {
	admin_priv('admin_message');
	$id = intval($_GET['id']);
	$sql = 'UPDATE ' . $ecs->table('admin_message') . ' SET deleted=1 ' . (' WHERE message_id=' . $id . ' AND (sender_id=\'' . $_SESSION['seller_id'] . '\' OR receiver_id=\'' . $_SESSION['seller_id'] . '\')');
	$db->query($sql);
	$url = 'message.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

?>
