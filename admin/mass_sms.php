<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function mass_sms_list()
{
	$filter = array();
	$filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
	if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
		$filter['keyword'] = json_str_iconv($filter['keyword']);
	}

	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$where = 'WHERE 1 ';

	if (!empty($filter['keyword'])) {
		$where .= ' AND (temp_content LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\')';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('mass_sms_template') . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$arr = array();
	$sql = 'SELECT * ' . 'FROM ' . $GLOBALS['ecs']->table('mass_sms_template') . $where . 'ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$idx = 0;

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$rows['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);
		$rows['wait_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('mass_sms_log') . ' WHERE 1 AND template_id = \'' . $rows['id'] . '\' AND send_status = 0 ');
		$rows['success_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('mass_sms_log') . ' WHERE 1 AND template_id = \'' . $rows['id'] . '\' AND send_status = 1 ');
		$rows['failure_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('mass_sms_log') . ' WHERE 1 AND template_id = \'' . $rows['id'] . '\' AND send_status = 2 ');
		$arr[$idx] = $rows;
		$idx++;
	}

	return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function mass_sms_log()
{
	$filter = array();
	$filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
	if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
		$filter['keyword'] = json_str_iconv($filter['keyword']);
	}

	$filter['template_id'] = empty($_REQUEST['template_id']) ? 0 : trim($_REQUEST['template_id']);
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$where = 'WHERE 1 ';

	if (!empty($filter['keyword'])) {
		$where .= ' AND (temp_content LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\') ';
	}

	if (!empty($filter['template_id'])) {
		$where .= ' AND template_id = \'' . $filter['template_id'] . '\' ';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('mass_sms_log') . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$arr = array();
	$sql = 'SELECT * ' . 'FROM ' . $GLOBALS['ecs']->table('mass_sms_log') . $where . 'ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$idx = 0;

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$rows['last_send'] = local_date($GLOBALS['_CFG']['time_format'], $rows['last_send']);
		$user_info = get_table_date('users', 'user_id=\'' . $rows['user_id'] . '\'', array('user_name', 'mobile_phone'));
		$rows['user_name'] = $user_info['user_name'];
		$rows['mobile_phone'] = $user_info['mobile_phone'];
		$arr[$idx] = $rows;
		$idx++;
	}

	return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once ROOT_PATH . 'includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('mass_sms_template'), $db, 'id', 'temp_id');
$exc_log = new exchange($ecs->table('mass_sms_log'), $db, 'id', 'template_id');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$adminru = get_admin_ru_id();
$ruCat = '';

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('mass_sms');
	$smarty->assign('ur_here', $_LANG['17_mass_sms']);
	$smarty->assign('action_link', array('text' => $_LANG['template_add'], 'href' => 'mass_sms.php?act=add'));
	$smarty->assign('full_page', 1);
	$smarty->assign('sms_type', $_CFG['sms_type']);
	$list = mass_sms_list();
	$smarty->assign('mass_sms', $list['list']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('mass_sms_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	check_authz_json('mass_sms');
	$smarty->assign('sms_type', $_CFG['sms_type']);
	$list = mass_sms_list();
	$smarty->assign('mass_sms', $list['list']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('mass_sms_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else {
	if (($_REQUEST['act'] == 'add') || ($_REQUEST['act'] == 'edit')) {
		admin_priv('mass_sms');

		if ($_REQUEST['act'] == 'add') {
			$smarty->assign('ur_here', $_LANG['template_add']);
			$smarty->assign('form_act', 'insert');
			$smarty->assign('action', 'add');
		}
		else {
			$smarty->assign('ur_here', $_LANG['template_edit']);
			$smarty->assign('form_act', 'update');
			$smarty->assign('action', 'add');
			$id = (empty($_GET['id']) ? 0 : intval($_GET['id']));
			$note = get_table_date('mass_sms_template', 'id=\'' . $id . '\'', array('*'));
			$smarty->assign('note', $note);
		}

		$smarty->assign('action_link', array('href' => 'mass_sms.php?act=list'));
		$smarty->assign('sms_type', $_CFG['sms_type']);
		$smarty->assign('ranklist', get_rank_list());
		assign_query_info();
		$smarty->display('mass_sms_info.dwt');
	}
	else {
		if (($_REQUEST['act'] == 'insert') || ($_REQUEST['act'] == 'update')) {
			admin_priv('mass_sms');
			$id = (empty($_POST['id']) ? 0 : intval($_POST['id']));
			$signature = (empty($_POST['signature']) ? 0 : intval($_POST['signature']));
			$other['temp_id'] = empty($_POST['temp_id']) ? '' : trim($_POST['temp_id']);
			$other['temp_content'] = empty($_POST['temp_content']) ? '' : trim($_POST['temp_content']);
			$other['content'] = empty($_POST['content']) ? '' : trim($_POST['content']);
			$other['set_sign'] = empty($_POST['set_sign']) ? '' : trim($_POST['set_sign']);
			$other['add_time'] = gmtime();

			if ($id) {
				$db->autoExecute($ecs->table('mass_sms_template'), $other, 'UPDATE', 'id = \'' . $id . '\'');
				$href = 'mass_sms.php?act=edit&id=' . $id;
				$lang_name = $_LANG['edit_success'];
			}
			else {
				$db->autoExecute($ecs->table('mass_sms_template'), $other, 'INSERT');
				$href = 'mass_sms.php?act=list';
				$lang_name = $_LANG['add_success'];
				$id = $db->insert_id();
			}

			$user_list = array();
			$type = (empty($_POST['type']) ? 0 : intval($_POST['type']));

			if ($type == 0) {
				$user_list = $_POST['user'];
			}
			else if ($type == 1) {
				$rank_id = (empty($_POST['rank_id']) ? 0 : intval($_POST['rank_id']));

				if (0 < $rank_id) {
					$sql = 'SELECT min_points, max_points, special_rank FROM ' . $ecs->table('user_rank') . ' WHERE rank_id = \'' . $rank_id . '\'';
					$row = $db->getRow($sql);

					if ($row['special_rank']) {
						$sql = 'SELECT user_id FROM ' . $ecs->table('users') . ' WHERE user_rank = \'' . $rank_id . '\'';
					}
					else {
						$sql = 'SELECT user_id FROM ' . $ecs->table('users') . ' WHERE rank_points >= ' . intval($row['min_points']) . ' AND rank_points < ' . intval($row['max_points']);
					}

					$user_list = $db->getCol($sql);
				}
			}
			else if ($type == 2) {
				$sql = ' SELECT user_id FROM ' . $GLOBALS['ecs']->table('users');
				$user_list = $GLOBALS['db']->getCol($sql);
			}

			if ($user_list) {
				foreach ($user_list as $key => $val) {
					$data = array();
					$data['template_id'] = $id;
					$data['user_id'] = $val;
					$data['send_status'] = 0;
					$data['last_send'] = 0;
					$db->autoExecute($ecs->table('mass_sms_log'), $data, 'INSERT');
				}
			}

			$save_count = count($user_list);
			$lang_name .= sprintf($_LANG['save_count'], $save_count);

			if (isset($_POST['send'])) {
			}

			$link[] = array('text' => $_LANG['go_back'], 'href' => $href);
			sys_msg(sprintf($lang_name, htmlspecialchars(stripslashes($other['temp_id']))), 0, $link);
		}
		else if ($_REQUEST['act'] == 'remove') {
			admin_priv('mass_sms');
			$id = (empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']));
			$sql = 'SELECT temp_id FROM ' . $GLOBALS['ecs']->table('mass_sms_template') . ' WHERE id = \'' . $id . '\'';
			$temp_id = $GLOBALS['db']->getOne($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('mass_sms_template') . ' WHERE id = \'' . $id . '\'';
			$GLOBALS['db']->query($sql);
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'mass_sms.php?act=list');
			sys_msg(sprintf($_LANG['remove_success'], $temp_id), 0, $link);
		}
	}
}

if ($_REQUEST['act'] == 'search_users') {
	check_authz_json('mass_sms');
	$keywords = json_str_iconv(trim($_GET['keywords']));
	$sql = 'SELECT user_id, user_name FROM ' . $ecs->table('users') . ' WHERE user_name LIKE \'%' . mysql_like_quote($keywords) . '%\' OR user_id LIKE \'%' . mysql_like_quote($keywords) . '%\'';
	$row = $db->getAll($sql);
	make_json_result($row);
}

if ($_REQUEST['act'] == 'log_list') {
	admin_priv('mass_sms');
	$smarty->assign('ur_here', $_LANG['log_list']);
	$smarty->assign('full_page', 1);
	$list = mass_sms_log();
	$smarty->assign('log', $list['list']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('mass_sms_log.dwt');
}
else if ($_REQUEST['act'] == 'log_query') {
	check_authz_json('mass_sms');
	$list = mass_sms_log();
	$smarty->assign('log', $list['list']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('mass_sms_log.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'remove_log') {
	check_authz_json('mass_sms');
	$id = intval($_GET['id']);
	$exc_log->drop($id);
	$url = 'mass_sms.php?act=log_query&' . str_replace('act=remove_log', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'send') {
	admin_priv('mass_sms');
	$id = (empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']));
	$log_info = get_table_date('mass_sms_log', 'id=\'' . $id . '\'', array('*'));
	$user_info = get_table_date('users', 'user_id=\'' . $log_info['user_id'] . '\'', array('user_name', 'mobile_phone'));
	$template_info = get_table_date('mass_sms_template', 'id=\'' . $log_info['template_id'] . '\'', array('*'));
	$links[] = array('text' => $_LANG['back_list'], 'href' => 'mass_sms.php?act=log_list&template_id=' . $log_info['template_id']);

	if ($user_info['mobile_phone'] != '') {
		$shop_name = get_shop_name($adminru['ru_id'], 1);
		$smsParams = array('shop_name' => $shop_name, 'shopname' => $shop_name, 'user_name' => $user_info['user_name'], 'username' => $user_info['user_name'], 'content' => $template_info['content'], 'mobile_phone' => $user_info['mobile_phone'], 'mobilephone' => $user_info['mobile_phone']);

		if ($GLOBALS['_CFG']['sms_type'] == 0) {
			$send_status = huyi_sms($smsParams, '', $template_info['temp_content']);
		}
		else if (1 <= $GLOBALS['_CFG']['sms_type']) {
			$result = sms_ali($smsParams, '', $template_info);

			if ($result) {
				$resp = $GLOBALS['ecs']->ali_yu($result);

				if ($GLOBALS['_CFG']['sms_type'] == 1) {
					if (isset($resp->result) && ($resp->result->error == 0)) {
						$send_status = true;
					}
				}
				else if ($GLOBALS['_CFG']['sms_type'] == 2) {
					$send_status = $resp;
				}
			}
		}
	}

	if ($send_status) {
		$res_no = 0;
		$res_msg = $_LANG['send_success'];
		$data = array('send_status' => 1, 'last_send' => gmtime());
	}
	else {
		$res_no = 1;
		$res_msg = $_LANG['send_failure'];
		$data = array('send_status' => 2, 'last_send' => gmtime());
	}

	$db->autoExecute($ecs->table('mass_sms_log'), $data, 'UPDATE', 'id = \'' . $id . '\'');
	sys_msg($res_msg, $res_no, $links);
}
else if ($_REQUEST['act'] == 'batch_log') {
	admin_priv('mass_sms');
	$template_id = (empty($_REQUEST['template_id']) ? 0 : intval($_REQUEST['template_id']));
	$smarty->assign('template_id', $template_id);
	$links[] = array('text' => $_LANG['back_list'], 'href' => 'mass_sms.php?act=log_list&template_id=' . $template_id);

	if (isset($_POST['checkboxes'])) {
		if (isset($_POST['send'])) {
			$smarty->assign('ur_here', $_LANG['batch_send']);
			$record_count = count($_POST['checkboxes']);
			$smarty->assign('record_count', $record_count);
			$smarty->assign('page', 1);
			$smarty->assign('log_list', implode(',', $_POST['checkboxes']));
			assign_query_info();
			$smarty->display('mass_sms_batch_send.dwt');
		}

		if (isset($_POST['drop'])) {
			$del_count = 0;

			foreach ($_POST['checkboxes'] as $key => $id) {
				$exc_log->drop($id);
				$del_count++;
			}

			sys_msg(sprintf($_LANG['batch_drop_success'], $del_count), 0, $links);
		}
	}
	else {
		sys_msg($_LANG['no_select_record'], 0, $links);
	}
}
else if ($_REQUEST['act'] == 'send_all') {
	admin_priv('mass_sms');
	$template_id = (empty($_REQUEST['template_id']) ? 0 : intval($_REQUEST['template_id']));
	$smarty->assign('template_id', $template_id);
	$links[] = array('text' => $_LANG['back_list'], 'href' => 'mass_sms.php?act=log_list&template_id=' . $template_id);
	$smarty->assign('ur_here', $_LANG['send_all']);
	$sql = ' SELECT id FROM ' . $GLOBALS['ecs']->table('mass_sms_log') . ' WHERE template_id = \'' . $template_id . '\' AND send_status <> 1 ';
	$id_list = $GLOBALS['db']->getCol($sql);
	$record_count = count($id_list);
	$smarty->assign('record_count', $record_count);
	$smarty->assign('page', 1);
	$smarty->assign('log_list', implode(',', $id_list));
	assign_query_info();
	$smarty->display('mass_sms_batch_send.dwt');
}
else if ($_REQUEST['act'] == 'batch_send') {
	check_authz_json('mass_sms');
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$list = (!empty($_REQUEST['list']) ? trim($_REQUEST['list']) : '');

	if ($list) {
		$sql = ' SELECT id FROM ' . $GLOBALS['ecs']->table('mass_sms_log') . ' WHERE id IN (' . $list . ') AND send_status <> 1 ';
		$list_array = $GLOBALS['db']->getCol($sql);
		$list_count = count($list_array);

		if (!empty($list_array)) {
			$first = array_shift($list_array);
			$log_info = get_table_date('mass_sms_log', 'id=\'' . $first . '\'', array('*'));
			$user_info = get_table_date('users', 'user_id=\'' . $log_info['user_id'] . '\'', array('user_name', 'mobile_phone'));
			$template_info = get_table_date('mass_sms_template', 'id=\'' . $log_info['template_id'] . '\'', array('*'));
			$data = array();
			$data['last_send'] = gmtime();

			if (empty($user_info['mobile_phone'])) {
				$data['send_status'] = 2;
			}
			else {
				$shop_name = get_shop_name($adminru['ru_id'], 1);
				$smsParams = array('shop_name' => $shop_name, 'shopname' => $shop_name, 'user_name' => $user_info['user_name'], 'username' => $user_info['user_name'], 'content' => $template_info['content'], 'mobile_phone' => $user_info['mobile_phone'], 'mobilephone' => $user_info['mobile_phone']);

				if ($GLOBALS['_CFG']['sms_type'] == 0) {
					$send_status = huyi_sms($smsParams, '', $template_info['temp_content']);
				}
				else if (1 <= $GLOBALS['_CFG']['sms_type']) {
					$result = sms_ali($smsParams, '', $template_info);

					if ($result) {
						$resp = $GLOBALS['ecs']->ali_yu($result);

						if ($GLOBALS['_CFG']['sms_type'] == 1) {
							if (isset($resp->result) && ($resp->result->error == 0)) {
								$send_status = true;
							}
						}
						else if ($GLOBALS['_CFG']['sms_type'] == 2) {
							$send_status = $resp;
						}
					}
				}

				if ($send_status) {
					$data['send_status'] = 1;
				}
				else {
					$data['send_status'] = 2;
				}
			}

			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('mass_sms_log'), $data, 'UPDATE', 'id = \'' . $first . '\'');
			$result['data'] = array_merge($log_info, $user_info, $data);
			$result['data']['last_send'] = local_date($GLOBALS['_CFG']['time_format'], $result['data']['last_send']);
			$result['data']['send_status'] = $GLOBALS['_LANG']['send_status'][$result['data']['send_status']];
		}
	}

	if (isset($list_count) && !empty($list_count)) {
		$result['list'] = implode(',', $list_array);
		$result['is_stop'] = 1;
	}
	else {
		$result['is_stop'] = 0;
	}

	exit($json->encode($result));
}

?>
