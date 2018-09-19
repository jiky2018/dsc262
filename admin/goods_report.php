<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_goods_report()
{
	$result = get_filter();

	if ($result === false) {
		$where = ' WHERE 1 ';
		$filter = array();
		$filter['handle_type'] = !empty($_REQUEST['handle_type']) ? $_REQUEST['handle_type'] : '-1';
		$filter['keywords'] = !empty($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '';

		if ($filter['keywords']) {
			$where .= ' AND (u.user_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' OR u.nick_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' OR g.goods_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\')';
		}

		if ($filter['handle_type'] != '-1') {
			if ($filter['handle_type'] == 6) {
				$where .= ' AND g.report_state = 0';
			}
			else {
				$where .= ' AND g.report_state > 0';
			}
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_report') . 'AS g LEFT JOIN ' . $GLOBALS['ecs']->table('users') . 'AS u ON u.user_id = g.user_id' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT g.report_id,g.goods_image,g.goods_name,g.goods_id,g.title_id,g.type_id,g.add_time,g.report_state' . ',g.handle_type,g.admin_id,g.user_id,u.user_name,handle_time FROM' . $GLOBALS['ecs']->table('goods_report') . ' AS g ' . 'LEFT JOIN' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id = g.user_id' . ' ' . $where . ' ORDER BY add_time DESC ';
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
		$rows['goods_image'] = get_image_path($rows['goods_id'], $rows['goods_image']);
		$rows['admin_name'] = $GLOBALS['db']->getOne('SELECT user_name FROM' . $GLOBALS['ecs']->table('admin_user') . ' WHERE user_id = \'' . $rows['admin_id'] . '\' LIMIT 1');

		if (0 < $rows['title_id']) {
			$sql_title = 'SELECT title_name FROM ' . $GLOBALS['ecs']->table('goods_report_title') . 'WHERE title_id = \'' . $rows['title_id'] . '\'';
			$rows['title_name'] = $GLOBALS['db']->getOne($sql_title);
		}

		if (0 < $rows['type_id']) {
			$sql_type = 'SELECT type_name FROM ' . $GLOBALS['ecs']->table('goods_report_type') . 'WHERE type_id = \'' . $rows['type_id'] . '\'';
			$rows['type_name'] = $GLOBALS['db']->getOne($sql_type);
		}

		if (0 < $rows['add_time']) {
			$rows['add_time'] = local_date('Y-m-d H:i:s', $rows['add_time']);
		}

		if (0 < $rows['handle_time']) {
			$rows['handle_time'] = local_date('Y-m-d H:i:s', $rows['handle_time']);
		}

		$rows['url'] = build_uri('goods', array('gid' => $rows['goods_id']), $rows['goods_name']);
		$sql = 'SELECT user_id FROM' . $GLOBALS['ecs']->table('goods') . 'WHERE goods_id = \'' . $rows['goods_id'] . '\' LIMIT 1';
		$rows['shop_name'] = get_shop_name($GLOBALS['db']->getOne($sql), 1);
		$sql = 'SELECT img_file ,img_id FROM ' . $GLOBALS['ecs']->table('goods_report_img') . ' WHERE report_id = \'' . $rows['report_id'] . '\' ORDER BY  img_id DESC';
		$img_list = $GLOBALS['db']->getAll($sql);

		if (!empty($img_list)) {
			foreach ($img_list as $k => $v) {
				$img_list[$k]['img_file'] = get_image_path($v['img_id'], $v['img_file']);
			}
		}

		$rows['img_list'] = $img_list;
		$arr[] = $rows;
	}

	return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_goods_report_type_list()
{
	$result = get_filter();

	if ($result === false) {
		$where = ' WHERE 1 ';
		$filter = array();
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_report_type') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT type_id , type_name , type_desc ,is_show FROM' . $GLOBALS['ecs']->table('goods_report_type') . ' ' . $where . ' ORDER BY type_id DESC LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$list = $GLOBALS['db']->getAll($sql);
	return array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_goods_report_title_list()
{
	$result = get_filter();

	if ($result === false) {
		$where = ' WHERE 1 ';
		$filter = array();
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_report_title') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT title_id , type_id , title_name ,is_show FROM' . $GLOBALS['ecs']->table('goods_report_title') . ' ' . $where . ' ORDER BY type_id DESC LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$list = $GLOBALS['db']->getAll($sql);

	if ($list) {
		foreach ($list as $k => $v) {
			if (0 < $v['type_id']) {
				$sql = 'SELECT type_name FROM ' . $GLOBALS['ecs']->table('goods_report_type') . 'WHERE type_id = \'' . $v['type_id'] . '\'';
				$list[$k]['type_name'] = $GLOBALS['db']->getOne($sql);
			}
		}
	}

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

$exc = new exchange($ecs->table('goods_report'), $db, 'report_id', 'user_id');
$exc_type = new exchange($ecs->table('goods_report_type'), $db, 'type_id', 'type_name');
$exc_title = new exchange($ecs->table('goods_report_title'), $db, 'title_id', 'title_name');

if ($_REQUEST['act'] == 'list') {
	admin_priv('goods_report');
	$smarty->assign('ur_here', $_LANG['goods_report_list']);
	$smarty->assign('action_link', array('text' => $_LANG['goods_report_list'], 'href' => 'goods_report.php?act=list'));
	$smarty->assign('action_link1', array('text' => $_LANG['goods_report_type'], 'href' => 'goods_report.php?act=type'));
	$smarty->assign('action_link2', array('text' => $_LANG['goods_report_title'], 'href' => 'goods_report.php?act=title'));
	$smarty->assign('action_link3', array('text' => $_LANG['report_conf'], 'href' => 'goods_report.php?act=report_conf'));
	$goods_report = get_goods_report();
	$smarty->assign('goods_report', $goods_report['list']);
	$smarty->assign('filter', $goods_report['filter']);
	$smarty->assign('record_count', $goods_report['record_count']);
	$smarty->assign('page_count', $goods_report['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('act_type', $_REQUEST['act']);
	assign_query_info();
	$smarty->display('goods_report_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	check_authz_json('goods_report');
	$goods_report = get_goods_report();
	$smarty->assign('goods_report', $goods_report['list']);
	$smarty->assign('filter', $goods_report['filter']);
	$smarty->assign('record_count', $goods_report['record_count']);
	$smarty->assign('page_count', $goods_report['page_count']);
	make_json_result($smarty->fetch('goods_report_list.dwt'), '', array('filter' => $goods_report['filter'], 'page_count' => $goods_report['page_count']));
}
else if ($_REQUEST['act'] == 'check_state') {
	admin_priv('goods_report');
	$smarty->assign('ur_here', $_LANG['handle_report']);
	$smarty->assign('action_link', array('text' => $_LANG['goods_report_list'], 'href' => 'goods_report.php?act=list'));
	$report_id = (!empty($_REQUEST['report_id']) ? intval($_REQUEST['report_id']) : 0);
	$sql = 'SELECT report_id,goods_image,goods_name,goods_id,title_id,type_id,add_time,report_state,handle_type,admin_id,user_id,inform_content,handle_message FROM' . $GLOBALS['ecs']->table('goods_report') . 'WHERE report_id = \'' . $report_id . '\' LIMIT 1';
	$rows = $db->getRow($sql);

	if (!empty($rows)) {
		$rows['goods_image'] = get_image_path($rows['goods_id'], $rows['goods_image']);
		$rows['admin_name'] = $GLOBALS['db']->getOne('SELECT user_name FROM' . $GLOBALS['ecs']->table('admin_user') . ' WHERE user_id = \'' . $rows['admin_id'] . '\' LIMIT 1');

		if (0 < $rows['title_id']) {
			$sql_title = 'SELECT title_name FROM ' . $GLOBALS['ecs']->table('goods_report_title') . 'WHERE title_id = \'' . $rows['title_id'] . '\'';
			$rows['title_name'] = $GLOBALS['db']->getOne($sql_title);
		}

		if (0 < $rows['type_id']) {
			$sql_type = 'SELECT type_name FROM ' . $GLOBALS['ecs']->table('goods_report_type') . 'WHERE type_id = \'' . $rows['type_id'] . '\'';
			$rows['type_name'] = $GLOBALS['db']->getOne($sql_type);
		}

		if (0 < $rows['add_time']) {
			$rows['add_time'] = local_date('Y-m-d H:i:s', $rows['add_time']);
		}

		$rows['url'] = build_uri('goods', array('gid' => $rows['goods_id']), $rows['goods_name']);
		$sql = 'SELECT user_id FROM' . $GLOBALS['ecs']->table('goods') . 'WHERE goods_id = \'' . $rows['goods_id'] . '\' LIMIT 1';
		$rows['shop_name'] = get_shop_name($GLOBALS['db']->getOne($sql), 1);
		$rows['user_name'] = $GLOBALS['db']->getOne('SELECT user_name FROM' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $rows['user_id'] . '\' LIMIT 1');
		$sql = 'SELECT img_file ,img_id FROM ' . $GLOBALS['ecs']->table('goods_report_img') . ' WHERE report_id = \'' . $rows['report_id'] . '\' ORDER BY  img_id DESC';
		$img_list = $GLOBALS['db']->getAll($sql);

		if (!empty($img_list)) {
			foreach ($img_list as $k => $v) {
				$img_list[$k]['img_file'] = get_image_path($v['img_id'], $v['img_file']);
			}
		}

		$rows['img_list'] = $img_list;
	}

	$smarty->assign('handle_type', $_LANG['handle_type_desc']);
	$smarty->assign('goods_report', $rows);
	$smarty->display('goods_report_info.dwt');
}
else if ($_REQUEST['act'] == 'submit_handle') {
	admin_priv('goods_report');
	$report_id = (!empty($_REQUEST['report_id']) ? intval($_REQUEST['report_id']) : 0);
	$handle_type = (!empty($_REQUEST['handle_type']) ? intval($_REQUEST['handle_type']) : 0);
	$handle_message = (!empty($_REQUEST['handle_message']) ? trim($_REQUEST['handle_message']) : '');
	$sql = 'SELECT report_state,user_id,goods_id,title_id,type_id  FROM' . $ecs->table('goods_report') . ' WHERE report_id = \'' . $report_id . '\'';
	$goods_report_info = $db->getRow($sql);

	if ($goods_report_info['report_state'] == 0) {
		$time = gmtime();
		$sql = 'UPDATE' . $ecs->table('goods_report') . 'SET report_state = 1 ,handle_type = \'' . $handle_type . '\' , handle_message = \'' . $handle_message . '\',handle_time = \'' . $time . '\' , admin_id = \'' . $_SESSION['admin_id'] . '\' WHERE report_id = \'' . $report_id . '\' ';
		$db->query($sql);

		if ($handle_type == 2) {
			if ($_CFG['report_handle'] == 1) {
				$report_handle_time = (0 < $_CFG['report_handle_time'] ? $_CFG['report_handle_time'] : 30);
				$report_time = (time() - date('Z')) + ($report_handle_time * 86400);
				$sql = 'UPDATE' . $ecs->table('users') . 'SET report_time = \'' . $report_time . '\' WHERE user_id = \'' . $goods_report_info['user_id'] . '\'';
				$db->query($sql);
				$sql = 'UPDATE' . $ecs->table('goods_report') . 'SET report_state = 1 ,handle_type = 1 , handle_message = \'' . $_LANG['handle_message_def'] . '\' ,handle_time = \'' . $time . '\' , admin_id = \'' . $_SESSION['admin_id'] . '\' WHERE user_id =  \'' . $goods_report_info['user_id'] . '\' AND report_state = 0';
				$db->query($sql);
			}
		}
		else if ($handle_type == 3) {
			$title_name = '违规';

			if (0 < $goods_report_info['title_id']) {
				$sql_title = 'SELECT title_name FROM ' . $GLOBALS['ecs']->table('goods_report_title') . 'WHERE title_id = \'' . $goods_report_info['title_id'] . '\'';
				$title_name = $GLOBALS['db']->getOne($sql_title);
			}

			$handle_message_goods = sprintf($_LANG['handle_message_goods'], $title_name);
			$sql = 'UPDATE' . $ecs->table('goods') . ' SET is_on_sale = 0 , review_status = 2 ,review_content = \'' . $handle_message_goods . '\' WHERE goods_id = \'' . $goods_report_info['goods_id'] . '\'';
			$db->query($sql);
		}

		$link[0]['text'] = $_LANG['back_list'];
		$link[0]['href'] = 'goods_report.php?act=list';
		sys_msg($_LANG['edit_succeed'], 0, $link);
	}
	else {
		$link[0]['text'] = $_LANG['back_list'];
		$link[0]['href'] = 'goods_report.php?act=list';
		sys_msg($_LANG['handle_report_repeat'], 0, $link);
	}
}
else if ($_REQUEST['act'] == 'report_conf') {
	admin_priv('goods_report');
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/shop_config.php';
	$smarty->assign('ur_here', $_LANG['report_conf']);
	$smarty->assign('action_link', array('text' => $_LANG['goods_report_list'], 'href' => 'goods_report.php?act=list'));
	$smarty->assign('action_link1', array('text' => $_LANG['goods_report_type'], 'href' => 'goods_report.php?act=type'));
	$smarty->assign('action_link2', array('text' => $_LANG['goods_report_title'], 'href' => 'goods_report.php?act=title'));
	$smarty->assign('action_link3', array('text' => $_LANG['report_conf'], 'href' => 'goods_report.php?act=report_conf'));
	$report_conf = get_up_settings('report_conf');
	$smarty->assign('report_conf', $report_conf);
	$smarty->assign('act_type', $_REQUEST['act']);
	assign_query_info();
	$smarty->display('goods_report_conf.dwt');
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('goods_report');
	$id = intval($_GET['id']);
	$sql = 'SELECT img_file FROM' . $ecs->table('goods_report_img') . ' WHERE report_id = \'' . $id . '\'';
	$img_list = $db->getAll($sql);

	if (!empty($img_list)) {
		foreach ($img_list as $key => $val) {
			get_oss_del_file(array($val['img_file']));
			@unlink(ROOT_PATH . $val['img_file']);
		}
	}

	$sql = 'DELETE FROM' . $ecs->table('goods_report_img') . ' WHERE report_id = \'' . $id . '\'';
	$db->query($sql);
	$exc->drop($id);
	$url = 'goods_report.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'type') {
	admin_priv('goods_report');
	$smarty->assign('ur_here', $_LANG['goods_report_type']);
	$smarty->assign('action_link', array('text' => $_LANG['goods_report_list'], 'href' => 'goods_report.php?act=list'));
	$smarty->assign('action_link1', array('text' => $_LANG['goods_report_type'], 'href' => 'goods_report.php?act=type'));
	$smarty->assign('action_link2', array('text' => $_LANG['goods_report_title'], 'href' => 'goods_report.php?act=title'));
	$smarty->assign('action_link3', array('text' => $_LANG['type_add'], 'href' => 'goods_report.php?act=type_add'));
	$smarty->assign('action_link4', array('text' => $_LANG['report_conf'], 'href' => 'goods_report.php?act=report_conf'));
	$type_info = get_goods_report_type_list();
	$smarty->assign('type_info', $type_info['list']);
	$smarty->assign('filter', $type_info['filter']);
	$smarty->assign('record_count', $type_info['record_count']);
	$smarty->assign('page_count', $type_info['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('act_type', $_REQUEST['act']);
	assign_query_info();
	$smarty->display('goods_report_type.dwt');
}
else if ($_REQUEST['act'] == 'type_query') {
	check_authz_json('goods_report');
	$type_info = get_goods_report_type_list();
	$smarty->assign('type_info', $type_info['list']);
	$smarty->assign('filter', $type_info['filter']);
	$smarty->assign('record_count', $type_info['record_count']);
	$smarty->assign('page_count', $type_info['page_count']);
	make_json_result($smarty->fetch('goods_report_type.dwt'), '', array('filter' => $type_info['filter'], 'page_count' => $type_info['page_count']));
}
else if ($_REQUEST['act'] == 'toggle_show') {
	check_authz_json('complaint');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$exc_type->edit('is_show = \'' . $val . '\'', $id);
	clear_cache_files();
	make_json_result($val);
}
else if ($_REQUEST['act'] == 'toggle_show_title') {
	check_authz_json('complaint');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$exc_title->edit('is_show = \'' . $val . '\'', $id);
	clear_cache_files();
	make_json_result($val);
}
else {
	if (($_REQUEST['act'] == 'type_add') || ($_REQUEST['act'] == 'type_edit')) {
		admin_priv('goods_report');
		$smarty->assign('ur_here', $_LANG['goods_report_type']);
		$smarty->assign('action_link', array('text' => $_LANG['goods_report_type'], 'href' => 'goods_report.php?act=type'));
		$type_id = (!empty($_REQUEST['type_id']) ? intval($_REQUEST['type_id']) : 0);

		if ($_REQUEST['act'] == 'type_add') {
			$form_action = 'type_insert';
		}
		else {
			$form_action = 'type_update';
			$sql = 'SELECT type_id , type_name , type_desc,is_show FROM' . $ecs->table('goods_report_type') . 'WHERE type_id = \'' . $type_id . '\'';
			$report_type_info = $db->getRow($sql);
			$smarty->assign('report_type_info', $report_type_info);
		}

		$smarty->assign('form_action', $form_action);
		$smarty->display('goods_report_type_info.dwt');
	}
	else {
		if (($_REQUEST['act'] == 'type_insert') || ($_REQUEST['act'] == 'type_update')) {
			admin_priv('goods_report');
			$type_name = (!empty($_REQUEST['type_name']) ? trim($_REQUEST['type_name']) : '');
			$type_id = (!empty($_REQUEST['type_id']) ? intval($_REQUEST['type_id']) : 0);
			$is_show = (!empty($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0);
			$type_desc = (!empty($_REQUEST['type_desc']) ? trim($_REQUEST['type_desc']) : '');

			if (empty($type_name)) {
				sys_msg($_LANG['type_name_null'], 1);
			}

			if (empty($type_desc)) {
				sys_msg($_LANG['type_desc_null'], 1);
			}

			if ($_REQUEST['act'] == 'type_insert') {
				$is_only = $exc_type->is_only('type_name', $type_name, 0);

				if (!$is_only) {
					sys_msg(sprintf($_LANG['title_exist'], stripslashes($type_name)), 1);
				}

				$sql = 'INSERT INTO' . $ecs->table('goods_report_type') . '(`type_name`,`type_desc`,`is_show`) VALUES (\'' . $type_name . '\',\'' . $type_desc . '\',\'' . $is_show . '\')';
				$db->query($sql);
				$link[0]['text'] = $_LANG['continue_add'];
				$link[0]['href'] = 'goods_report.php?act=type_add';
				$link[1]['text'] = $_LANG['back_list'];
				$link[1]['href'] = 'goods_report.php?act=type';
				sys_msg($_LANG['add_succeed'], 0, $link);
			}
			else {
				$is_only = $exc_type->is_only('type_name', $type_name, 0, 'type_id != \'' . $type_id . '\'');

				if (!$is_only) {
					sys_msg(sprintf($_LANG['title_exist'], stripslashes($type_name)), 1);
				}

				$sql = 'UPDATE' . $ecs->table('goods_report_type') . ' SET type_name = \'' . $type_name . '\',type_desc=\'' . $type_desc . '\',is_show=\'' . $is_show . '\' WHERE type_id = \'' . $type_id . '\'';
				$db->query($sql);
				$link[0]['text'] = $_LANG['back_list'];
				$link[0]['href'] = 'goods_report.php?act=type';
				sys_msg($_LANG['edit_succeed'], 0, $link);
			}
		}
		else if ($_REQUEST['act'] == 'remove_type') {
			check_authz_json('goods_report');
			$id = intval($_GET['id']);
			$exc_type->drop($id);
			$url = 'goods_report.php?act=type_query&' . str_replace('act=remove_type', '', $_SERVER['QUERY_STRING']);
			ecs_header('Location: ' . $url . "\n");
			exit();
		}
		else if ($_REQUEST['act'] == 'title') {
			admin_priv('goods_report');
			$smarty->assign('ur_here', $_LANG['goods_report_title']);
			$smarty->assign('action_link', array('text' => $_LANG['goods_report_list'], 'href' => 'goods_report.php?act=list'));
			$smarty->assign('action_link1', array('text' => $_LANG['goods_report_type'], 'href' => 'goods_report.php?act=type'));
			$smarty->assign('action_link2', array('text' => $_LANG['goods_report_title'], 'href' => 'goods_report.php?act=title'));
			$smarty->assign('action_link3', array('text' => $_LANG['title_add'], 'href' => 'goods_report.php?act=title_add'));
			$smarty->assign('action_link4', array('text' => $_LANG['report_conf'], 'href' => 'goods_report.php?act=report_conf'));
			$title = get_goods_report_title_list();
			$smarty->assign('title_info', $title['list']);
			$smarty->assign('filter', $title['filter']);
			$smarty->assign('record_count', $title['record_count']);
			$smarty->assign('page_count', $title['page_count']);
			$smarty->assign('full_page', 1);
			$smarty->assign('act_type', $_REQUEST['act']);
			assign_query_info();
			$smarty->display('goods_report_title.dwt');
		}
		else if ($_REQUEST['act'] == 'title_query') {
			check_authz_json('goods_report');
			$title = get_goods_report_title_list();
			$smarty->assign('title_info', $title['list']);
			$smarty->assign('filter', $title['filter']);
			$smarty->assign('record_count', $title['record_count']);
			$smarty->assign('page_count', $title['page_count']);
			make_json_result($smarty->fetch('goods_report_title.dwt'), '', array('filter' => $title['filter'], 'page_count' => $title['page_count']));
		}
		else {
			if (($_REQUEST['act'] == 'title_add') || ($_REQUEST['act'] == 'title_edit')) {
				admin_priv('goods_report');
				$smarty->assign('ur_here', $_LANG['goods_report_title']);
				$smarty->assign('action_link', array('text' => $_LANG['goods_report_title'], 'href' => 'goods_report.php?act=title'));
				$title_id = (!empty($_REQUEST['title_id']) ? intval($_REQUEST['title_id']) : 0);
				$goods_report_type = get_goods_report_type();

				if ($_REQUEST['act'] == 'title_add') {
					$form_action = 'title_insert';
				}
				else {
					$form_action = 'title_update';
					$sql = 'SELECT title_id , type_id , title_name,is_show FROM' . $ecs->table('goods_report_title') . 'WHERE title_id = \'' . $title_id . '\'';
					$report_title_info = $db->getRow($sql);
					$smarty->assign('report_title_info', $report_title_info);
				}

				$smarty->assign('goods_report_type', $goods_report_type);
				$smarty->assign('form_action', $form_action);
				$smarty->display('goods_report_title_info.dwt');
			}
			else {
				if (($_REQUEST['act'] == 'title_insert') || ($_REQUEST['act'] == 'title_update')) {
					admin_priv('goods_report');
					$title_name = (!empty($_REQUEST['title_name']) ? trim($_REQUEST['title_name']) : '');
					$type_id = (!empty($_REQUEST['type_id']) ? intval($_REQUEST['type_id']) : 0);
					$title_id = (!empty($_REQUEST['title_id']) ? intval($_REQUEST['title_id']) : 0);
					$is_show = (!empty($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0);

					if (empty($title_name)) {
						sys_msg($_LANG['title_name_null'], 1);
					}

					if ($_REQUEST['act'] == 'title_insert') {
						$is_only = $exc_title->is_only('title_name', $title_name, 0);

						if (!$is_only) {
							sys_msg(sprintf($_LANG['exist_title'], stripslashes($title_name)), 1);
						}

						$sql = 'INSERT INTO' . $ecs->table('goods_report_title') . '(`type_id`,`title_name`,`is_show`) VALUES (\'' . $type_id . '\',\'' . $title_name . '\',\'' . $is_show . '\')';
						$db->query($sql);
						$link[0]['text'] = $_LANG['continue_add'];
						$link[0]['href'] = 'goods_report.php?act=title_add';
						$link[1]['text'] = $_LANG['back_list'];
						$link[1]['href'] = 'goods_report.php?act=title';
						sys_msg($_LANG['add_succeed'], 0, $link);
					}
					else {
						$is_only = $exc_title->is_only('title_name', $title_name, 0, 'title_id != \'' . $title_id . '\'');

						if (!$is_only) {
							sys_msg(sprintf($_LANG['exist_title'], stripslashes($title_name)), 1);
						}

						$sql = 'UPDATE' . $ecs->table('goods_report_title') . ' SET title_name = \'' . $title_name . '\',type_id=\'' . $type_id . '\',is_show=\'' . $is_show . '\' WHERE title_id = \'' . $title_id . '\'';
						$db->query($sql);
						$link[0]['text'] = $_LANG['back_list'];
						$link[0]['href'] = 'goods_report.php?act=title';
						sys_msg($_LANG['edit_succeed'], 0, $link);
					}
				}
				else if ($_REQUEST['act'] == 'remove_title') {
					check_authz_json('goods_report');
					$id = intval($_GET['id']);
					$exc_title->drop($id);
					$url = 'goods_report.php?act=title_query&' . str_replace('act=remove_title', '', $_SERVER['QUERY_STRING']);
					ecs_header('Location: ' . $url . "\n");
					exit();
				}
			}
		}
	}
}

?>
