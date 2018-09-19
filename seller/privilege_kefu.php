<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function services_list()
{
	$filter = array();
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$sql = 'SELECT COUNT(s.id) FROM ' . $GLOBALS['ecs']->table('im_service') . ' s' . ' INNER JOIN ' . $GLOBALS['ecs']->table('admin_user') . ' a ON a.user_id = s.user_id  AND a.ru_id = ' . $_SESSION['admin_ru_id'] . ' WHERE s.status = 1';
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$list = array();
	$sql = 'SELECT s.* FROM ' . $GLOBALS['ecs']->table('im_service') . ' s' . ' INNER JOIN ' . $GLOBALS['ecs']->table('admin_user') . ' a ON a.user_id = s.user_id  AND a.ru_id = ' . $_SESSION['admin_ru_id'] . ' WHERE s.status = 1';
	$sql .= ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$rows['chat_status'] = $rows['chat_status'] == 0 ? '未登录' : '登录中';
		$rows['avatar'] = '../data/images_user/' . (empty($rows['avatar']) ? '/no_picture.jpg' : $rows['avatar']);
		$list[] = $rows;
	}

	return array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function admin_list()
{
	$sql = 'SELECT ru_id FROM ' . $GLOBALS['ecs']->table('admin_user') . ' WHERE user_id = ' . $_SESSION['seller_id'];
	$ruId = $GLOBALS['db']->getOne($sql);
	$sql = 'SELECT a.user_id, a.user_name FROM ' . $GLOBALS['ecs']->table('admin_user') . ' a' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('im_service') . ' s ON a.user_id = s.user_id AND s.status = 1' . ' WHERE a.ru_id = ' . $ruId;
	$list = $GLOBALS['db']->getAll($sql);
	return $list;
}

function dialog_list($id, $val = 0)
{
	$sql = 'SELECT id,  customer_id, goods_id, store_id, start_time, end_time FROM ' . $GLOBALS['ecs']->table('im_dialog') . ' WHERE services_id = ' . $id;

	if ($val === 0) {
		$time = strtotime(date('Y-m-d', time()));
		$sql .= ' AND start_time > ' . $time;
	}
	else if ($val === 1) {
		$time = strtotime('-1 week');
		$sql .= ' AND start_time > ' . $time;
	}
	else if ($val === 2) {
		$time = strtotime('-1 month');
		$sql .= ' AND start_time > ' . $time;
	}

	$sql .= ' ORDER BY start_time DESC';
	$res = $GLOBALS['db']->getAll($sql);
	$temp = array();

	foreach ($res as $k => $v) {
		if (in_array($v['customer_id'], $temp)) {
			unset($res[$k]);
			continue;
		}

		$temp[] = $v['customer_id'];
	}

	foreach ($res as $k => $v) {
		if (0 < $v['goods_id']) {
			$sql = 'SELECT goods_name, goods_thumb FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id = ' . $v['goods_id'];
			$goods = $GLOBALS['db']->getRow($sql);
			$res[$k]['goods_name'] = $goods['goods_name'];
			$res[$k]['goods_thumb'] = format_goods_pic($goods['goods_thumb']);
		}

		$sql = 'SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = ' . $v['customer_id'];
		$res[$k]['user_name'] = $GLOBALS['db']->getOne($sql);
		$res[$k]['start_time'] = date('Y-m-d H:i:s', $v['start_time']);
		$res[$k]['end_time'] = date('Y-m-d H:i:s', $v['end_time']);
	}

	return $res;
}

function message_list($customer_id, $service_id, $page = 0, $keyword = '', $date = '')
{
	$size = 10;
	$start = ($page - 1) * $size;
	$start = (intval($start) < 0 ? 0 : (int) $start);
	$sql = 'SELECT message, user_type, FROM_UNIXTIME(add_time) AS add_time FROM ' . $GLOBALS['ecs']->table('im_message') . ' WHERE ((from_user_id = ' . $customer_id . ' AND to_user_id = ' . $service_id . ') OR (from_user_id = ' . $service_id . ' AND to_user_id = ' . $customer_id . ')';
	$sqlCount = 'SELECT count(id) FROM ' . $GLOBALS['ecs']->table('im_message') . ' WHERE ((from_user_id = ' . $customer_id . ' AND to_user_id = ' . $service_id . ') OR (from_user_id = ' . $service_id . ' AND to_user_id = ' . $customer_id . ')';

	if (!empty($keyword)) {
		$sql .= ') AND (message like \'%' . $keyword . '%\') ORDER BY add_time';
		$sqlCount .= ') AND (message like \'%' . $keyword . '%\') ORDER BY add_time';
	}
	else if (!empty($date)) {
		$sql .= ') AND UNIX_TIMESTAMP(FROM_UNIXTIME(add_time, \'%Y-%m-%d\')) = ' . $date;
		$sqlCount .= ') AND UNIX_TIMESTAMP(FROM_UNIXTIME(add_time, \'%Y-%m-%d\')) = ' . $date;
	}
	else if (!empty($page)) {
		$sql .= ') ORDER BY add_time';
		$sqlCount .= ')';
	}
	else {
		$sql .= ') ORDER BY add_time';
		$sqlCount .= ')';
	}

	$sql .= ' limit ' . $start . ', ' . $size;
	$res = $GLOBALS['db']->getAll($sql);
	$count = $GLOBALS['db']->getOne($sqlCount);

	foreach ($res as $k => $v) {
		$res[$k]['message'] = htmlspecialchars_decode($v['message']);
	}

	return array('list' => $res, 'count' => ceil($count / $size));
}

function statistics_reception($now = false)
{
	$sql = 'SELECT count(id) FROM ' . $GLOBALS['ecs']->table('im_dialog');
	$nowTime = strtotime(date('Y-m-d', time()));

	if ($now) {
		$sql .= ' WHERE start_time > ' . $nowTime;
		$sql .= ' AND store_id = ' . $_SESSION['admin_ru_id'];
	}
	else {
		$sql .= ' WHERE store_id = ' . $_SESSION['admin_ru_id'];
	}

	$times = $GLOBALS['db']->getOne($sql);
	return $times;
}

function statistics_reception_customer($now = false)
{
	$sql = 'SELECT COUNT(DISTINCT customer_id) FROM ' . $GLOBALS['ecs']->table('im_dialog');
	$nowTime = strtotime(date('Y-m-d', time()));

	if ($now) {
		$sql .= ' WHERE start_time > ' . $nowTime;
		$sql .= ' AND store_id = ' . $_SESSION['admin_ru_id'];
	}
	else {
		$sql .= ' WHERE store_id = ' . $_SESSION['admin_ru_id'];
	}

	$times = $GLOBALS['db']->getOne($sql);
	return $times;
}

function service_info($id)
{
	$sql = 'SELECT id, nick_name, post_desc FROM ' . $GLOBALS['ecs']->table('im_service') . '  WHERE status = 1 AND user_id = ' . $id;
	$res = $GLOBALS['db']->getRow($sql);
	return $res;
}

function dialog($id)
{
	$sql = 'SELECT u.user_name, s.nick_name, FROM_UNIXTIME(start_time) AS start_time FROM ' . $GLOBALS['ecs']->table('im_dialog') . ' d' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('im_service') . ' s ON d.services_id = s.id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' u ON d.customer_id = u.user_id' . ' WHERE d.id = ' . $id;
	$res = $GLOBALS['db']->getRow($sql);
	return $res;
}

function format_goods_pic($path)
{
	$path = '../' . $path;
	return $path;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('admin_user'), $db, 'user_id', 'user_name');
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'privilege');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('seller', 0);
$php_self = get_php_self(1);
$smarty->assign('php_self', $php_self);

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', '客服列表');
	$smarty->assign('primary_cat', $_LANG['10_priv_admin']);

	if (0 < $adminru['ru_id']) {
		$smarty->assign('action_link', array('href' => 'privilege_kefu.php?act=add', 'text' => $_LANG['add_kefu'], 'class' => 'icon-plus'));
	}

	$smarty->assign('ru_id', $adminru['ru_id']);
	$smarty->assign('full_page', 1);
	$services = services_list();
	$smarty->assign('services_list', $services['list']);
	$smarty->assign('filter', $services['filter']);
	$smarty->assign('record_count', $services['record_count']);
	$smarty->assign('page_count', $services['page_count']);
	$times['times'] = statistics_reception();
	$times['today_times'] = statistics_reception(1);
	$times['people'] = statistics_reception_customer();
	$times['today_people'] = statistics_reception_customer(1);
	$smarty->assign('times', $times);
	assign_query_info();
	$smarty->assign('current', 'privilege_kefu');
	$smarty->display('privilege_kefu_list.dwt');
}
else if ($_REQUEST['act'] == 'remove') {
	$id = (empty($_GET['id']) ? 0 : intval($_GET['id']));
	$isAjax = (empty($_GET['is_ajax']) ? 0 : intval($_GET['is_ajax']));

	if (!$isAjax) {
		make_json_error('invalid method');
	}

	if (!$id) {
		make_json_error('invalid params');
	}

	$sql = 'SELECT user_name, chat_status FROM  ' . $ecs->table('im_service') . ' WHERE id = ' . $id . ' AND status = 1';
	$res = $db->getRow($sql);

	if (!$res) {
		make_json_error('客服不存在');
	}

	if ($res['chat_status'] == 1) {
		make_json_error('客服正在登录中，不能删除');
	}

	$sql = 'UPDATE' . $ecs->table('im_service') . ' SET status = 0  WHERE id = ' . $id;
	$res = $db->query($sql);

	if (!$res) {
		make_json_error('客服不存在');
	}

	$services = services_list();
	$smarty->assign('services_list', $services['list']);
	$smarty->assign('services_list', $services['list']);
	$smarty->assign('filter', $services['filter']);
	$smarty->assign('record_count', $services['record_count']);
	$smarty->assign('page_count', $services['page_count']);
	make_json_result($smarty->fetch('privilege_kefu_list.dwt'), '删除成功');
}
else if ($_REQUEST['act'] == 'trash') {
	$ids = (array) $_POST['checkboxes'];
	$ids = array_filter($ids, function($v) {
		return (int) $v;
	});
	$isAjax = (empty($_GET['is_ajax']) ? 0 : intval($_GET['is_ajax']));
	$ids = implode(',', $ids);
	$sql = 'UPDATE' . $ecs->table('im_service') . ' SET status = 0  WHERE id in (' . $ids . ')';
	$res = $db->query($sql);
	$url = 'privilege_kefu.php?act=list';
	ecs_header('Location: ' . $url . "\n");
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('seller_manage');
	$smarty->assign('primary_cat', $_LANG['10_priv_admin']);
	$smarty->assign('ur_here', '添加管理员');
	$smarty->assign('ur_here', '客服管理');
	$admins = admin_list();
	$smarty->assign('admin_list', $admins);
	$smarty->assign('form_act', 'insert');
	$smarty->assign('action', 'add');
	$smarty->assign('menu_select', array('action' => '10_priv_admin', 'current' => 'kefu_list'));
	assign_query_info();
	$smarty->display('privilege_kefu_info.dwt');
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('seller_manage');
	$smarty->assign('primary_cat', $_LANG['10_priv_admin']);
	$smarty->assign('ur_here', '添加管理员');
	$smarty->assign('ur_here', '客服管理');
	$admins = admin_list();
	$smarty->assign('admin_list', $admins);
	$smarty->assign('form_act', 'update');
	$smarty->assign('action', 'edit');
	$id = $_SESSION['seller_id'];
	$services = service_info($id);
	$smarty->assign('services', $services);
	$smarty->assign('menu_select', array('action' => '10_priv_admin', 'current' => 'kefu_list'));
	assign_query_info();
	$smarty->display('privilege_kefu_info.dwt');
}
else {
	if (($_REQUEST['act'] == 'insert') || ($_REQUEST['act'] == 'update')) {
		$services_name = (empty($_POST['nick_name']) ? 0 : strip_tags($_POST['nick_name']));
		$services_desc = (empty($_POST['kefu_desc']) ? 0 : strip_tags($_POST['kefu_desc']));
		$services = (empty($_POST['services']) ? 0 : intval($_POST['services']));

		if (empty($services_name)) {
			sys_msg('请填写昵称', 1);
		}
		else if (empty($services_desc)) {
			sys_msg('请填写描述', 1);
		}
		else if (empty($services)) {
			sys_msg('请选择管理员', 1);
		}

		$sql = 'SELECT user_name FROM  ' . $ecs->table('admin_user') . ' WHERE user_id=' . $services;
		$userName = $db->getOne($sql);

		if (!$userName) {
			sys_msg('没有该管理员', 1);
		}

		if ($_REQUEST['act'] == 'insert') {
			$sql = 'SELECT user_name, status FROM  ' . $ecs->table('im_service') . ' WHERE user_id=' . $services;
			$res = $db->getRow($sql);

			if ($res['status'] == 1) {
				sys_msg('该管理员已是客服', 1);
			}
			else {
				if (($res['status'] === 0) || ($res['status'] === '0')) {
					$sql = 'UPDATE' . $ecs->table('im_service') . ' SET nick_name=\'' . $services_name . '\', post_desc=\'' . $services_desc . '\', status=1  WHERE user_id=' . $services;
					$res = $db->query($sql);
				}
				else {
					$sql = 'INSERT INTO ' . $ecs->table('im_service') . '(user_id, user_name, nick_name, post_desc, chat_status, status) ' . 'VALUES (' . $services . ', \'' . $userName . '\', \'' . $services_name . '\',  ' . '\'' . $services_desc . '\', \'0\', \'1\')';
					$res = $db->query($sql);
				}
			}

			if (!$res) {
				sys_msg('添加客服失败', 1);
			}
		}
		else if ($_REQUEST['act'] == 'update') {
			$id = (empty($_POST['id']) ? 0 : intval($_POST['id']));
			$sql = 'UPDATE' . $ecs->table('im_service') . ' SET nick_name=\'' . $services_name . '\', post_desc=\'' . $services_desc . '\', status=1  WHERE id=' . $id;
			$res = $db->query($sql);

			if (!$res) {
				sys_msg('更新客服失败', 1);
			}
		}

		admin_log('', 'service', $_REQUEST['act']);
		$url = 'privilege_kefu.php?act=list';
		ecs_header('Location: ' . $url . "\n");
	}
	else if ($_REQUEST['act'] == 'dialog_list') {
		admin_priv('seller_manage');
		$id = (empty($_GET['id']) ? 0 : intval($_GET['id']));
		$list = dialog_list($id, 0);
		$smarty->assign('id', $id);
		$smarty->assign('dialog_list', $list);
		$smarty->assign('full_page', 1);
		$smarty->assign('ur_here', '会话记录');
		$smarty->assign('menu_select', array('action' => '10_priv_admin', 'current' => 'kefu_list'));
		$smarty->assign('action_link', array('href' => 'privilege_kefu.php?act=list', 'text' => $_LANG['kefu_list'], 'class' => 'icon-reply'));
		assign_query_info();
		$smarty->assign('current', 'privilege_seller');
		$smarty->display('privilege_dialog_list.dwt');
	}
	else if ($_REQUEST['act'] == 'dialog_list_ajax') {
		$id = (empty($_POST['id']) ? 0 : intval($_POST['id']));
		$val = (empty($_POST['val']) ? 0 : intval($_POST['val']));
		$list = dialog_list($id, $val);
		$smarty->assign('dialog_list', $list);
		make_json_result($smarty->fetch('privilege_dialog_list.dwt'));
	}
	else if ($_REQUEST['act'] == 'message_list_ajax') {
		$id = (empty($_POST['id']) ? 0 : intval($_POST['id']));
		$customer_id = (empty($_POST['customer_id']) ? 0 : intval($_POST['customer_id']));
		$service_id = (empty($_POST['service_id']) ? 0 : intval($_POST['service_id']));
		$page = (empty($_POST['page']) ? 0 : intval($_POST['page']));
		$keyword = (empty($_POST['keyword']) ? 0 : strip_tags(trim($_POST['keyword'])));
		$dialog = dialog($id);
		$message = message_list($customer_id, $service_id, $page, $keyword);
		$list = $message['list'];
		$count = $message['count'];
		$smarty->assign('message_page', 1);
		$smarty->assign('dialog', $dialog);
		$smarty->assign('message_list', $list);
		make_json_result($smarty->fetch('privilege_dialog_list.dwt'), $count);
	}
	else if ($_REQUEST['act'] == 'generage_word') {
		$id = (empty($_GET['id']) ? 0 : intval($_GET['id']));
		$customer_id = (empty($_GET['customer_id']) ? 0 : intval($_GET['customer_id']));
		$service_id = (empty($_GET['service_id']) ? 0 : intval($_GET['service_id']));
		$message = message_list($customer_id, $service_id);
		$list = $message['list'];
		$dialog = dialog($id);
		require dirname(__FILE__) . '/../mobile/vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
		$excel = new PHPExcel();
		$letter = array('A', 'B', 'C', 'D', 'E', 'F', 'F', 'G');

		foreach ($list as $k => $v) {
			$excel->getActiveSheet()->setCellValue($letter[0] . $k, strip_tags($v['message']));
			$excel->getActiveSheet()->setCellValue($letter[1] . $k, $v['add_time']);

			if ($v['user_type'] == 1) {
				$excel->getActiveSheet()->setCellValue($letter[2] . $k, $dialog['user_name']);
			}
			else if ($v['user_type'] == 2) {
				$excel->getActiveSheet()->setCellValue($letter[2] . $k, $dialog['nick_name']);
			}
		}

		$write = new PHPExcel_Writer_Excel5($excel);
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control:must-revalidate, post-check=0, pre-check=0');
		header('Content-Type:application/force-download');
		header('Content-Type:application/vnd.ms-execl');
		header('Content-Type:application/octet-stream');
		header('Content-Type:application/download');
		header('Content-Disposition:attachment;filename="' . $dialog[user_name] . '.xls"');
		header('Content-Transfer-Encoding:binary');
		$write->save('php://output');
	}
	else if ($_REQUEST['act'] == 'get_message_by_date') {
		$customer_id = (empty($_POST['customer_id']) ? 0 : intval($_POST['customer_id']));
		$service_id = (empty($_POST['service_id']) ? 0 : intval($_POST['service_id']));
		$page = (empty($_POST['page']) ? 0 : intval($_POST['page']));
		$start_time = (empty($_POST['start_time']) ? 0 : strip_tags(trim($_POST['start_time'])));
		$message = message_list($customer_id, $service_id, $page, '', strtotime($start_time));
		$list = $message['list'];
		$count = $message['count'];
		$smarty->assign('message_page', 1);
		$smarty->assign('message_list', $list);
		make_json_result($smarty->fetch('privilege_dialog_list.dwt'), $count);
	}
}

?>
