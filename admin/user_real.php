<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function users_real_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['review_status'] = !isset($_REQUEST['review_status']) ? -1 : intval($_REQUEST['review_status']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
			$filter['review_status'] = json_str_iconv($filter['review_status']);
		}

		$filter['user_type'] = isset($_REQUEST['user_type']) ? intval($_REQUEST['user_type']) : 0;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'real_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$ex_where = ' WHERE 1 ';

		if ($filter['keywords']) {
			$ex_where .= ' AND u.user_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\'';
		}

		if ($filter['review_status'] != -1) {
			$ex_where .= ' AND ur.review_status = \'' . $filter['review_status'] . '\'';
		}

		$ex_where .= ' AND ur.user_type = \'' . $filter['user_type'] . '\'';
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('users_real') . 'AS ur ' . ' JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON ur.user_id = u.user_id ' . $ex_where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT ur.*,u.user_name ' . ' FROM ' . $GLOBALS['ecs']->table('users_real') . 'as ur ' . ' JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON ur.user_id = u.user_id ' . $ex_where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$users_real_list = $GLOBALS['db']->getAll($sql);

	for ($i = 0; $i < count($users_real_list); $i++) {
		if ($users_real_list[$i]['user_type']) {
			$users_real_list[$i]['user_name'] = get_shop_name($users_real_list[$i]['user_id'], 1);
		}
	}

	$arr = array('users_real_list' => $users_real_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'list') {
	admin_priv('users_real_manage');
	$users_real_list = users_real_list();
	$smarty->assign('ur_here', $_LANG['16_users_real']);
	$smarty->assign('users_real_list', $users_real_list['users_real_list']);
	$smarty->assign('filter', $users_real_list['filter']);
	$smarty->assign('record_count', $users_real_list['record_count']);
	$smarty->assign('page_count', $users_real_list['page_count']);
	$smarty->assign('full_page', 1);
	$user_type = empty($_REQUEST['user_type']) ? 0 : intval($_REQUEST['user_type']);
	$smarty->assign('user_type', $user_type);

	if ($user_type == 1) {
		$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => '16_seller_users_real'));
	}
	else {
		$smarty->assign('menu_select', array('action' => '08_members', 'current' => '16_users_real'));
	}

	assign_query_info();
	$smarty->display('users_real_list.dwt');
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('users_real_manage');
	$real_id = empty($_REQUEST['real_id']) ? 0 : trim($_REQUEST['real_id']);
	$user_type = empty($_REQUEST['user_type']) ? 0 : trim($_REQUEST['user_type']);
	$sql = 'SELECT ur.*, u.user_name, u.user_id FROM ' . $ecs->table('users_real') . ' AS ur ' . ' JOIN ' . $ecs->table('users') . ' AS u ON ur.user_id = u.user_id ' . (' WHERE ur.real_id = \'' . $real_id . '\'');
	$user_real_info = $db->getRow($sql);

	if ($user_real_info) {
		if ($user_real_info['front_of_id_card']) {
			$user_real_info['front_of_id_card'] = get_image_path(0, $user_real_info['front_of_id_card']);
		}

		if ($user_real_info['reverse_of_id_card']) {
			$user_real_info['reverse_of_id_card'] = get_image_path(0, $user_real_info['reverse_of_id_card']);
		}
	}

	$smarty->assign('ur_here', $_LANG['users_real_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['16_users_real'], 'href' => 'user_real.php?act=list&' . list_link_postfix()));
	$smarty->assign('user_type', $user_type);
	$smarty->assign('user_real_info', $user_real_info);
	$smarty->display('users_real_info.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	admin_priv('users_real_manage');
	$user_id = empty($_POST['user_id']) ? 0 : trim($_POST['user_id']);
	$real_name = empty($_POST['real_name']) ? '' : trim($_POST['real_name']);
	$self_num = empty($_POST['self_num']) ? '' : trim($_POST['self_num']);
	$bank_name = empty($_POST['bank_name']) ? '' : trim($_POST['bank_name']);
	$bank_card = empty($_POST['bank_card']) ? '' : trim($_POST['bank_card']);
	$review_status = empty($_POST['review_status']) ? '' : trim($_POST['review_status']);
	$review_content = empty($_POST['review_content']) ? '' : trim($_POST['review_content']);
	$user_type = empty($_POST['user_type']) ? 0 : intval($_POST['user_type']);
	$post_user_real = array('user_id' => $user_id, 'bank_name' => $bank_name, 'real_name' => $real_name, 'self_num' => $self_num, 'review_status' => $review_status, 'review_content' => $review_content, 'bank_card' => $bank_card);
	$type = '';

	if ($user_type) {
		$type = '&user_type=' . $user_type;
	}

	if (0 < $user_id) {
		$sql = 'SELECT real_id FROM ' . $ecs->table('users_real') . (' WHERE user_id = \'' . $user_id . '\' AND user_type = \'' . $user_type . '\'');
		$real_id = $db->getOne($sql);

		if ($real_id) {
			if ($db->autoExecute($ecs->table('users_real'), $post_user_real, 'UPDATE', 'real_id = \'' . $real_id . '\'')) {
				$links[] = array('text' => $_LANG['16_users_real'], 'href' => 'user_real.php?act=list' . $type);
				sys_msg('会员实名更新成功！', 0, $links);
			}
		}
		else {
			$post_user_real['add_time'] = gmtime();

			if ($db->autoExecute($ecs->table('users_real'), $post_user_real, 'INSERT')) {
				$links[] = array('text' => $_LANG['go_back'], 'href' => 'user_real.php?act=list' . $type);
				sys_msg('会员实名设置成功！', 0, $links);
			}
		}
	}
}
else if ($_REQUEST['act'] == 'query') {
	check_authz_json('users_real_manage');
	$users_real_list = users_real_list();
	$smarty->assign('users_real_list', $users_real_list['users_real_list']);
	$smarty->assign('filter', $users_real_list['filter']);
	$smarty->assign('record_count', $users_real_list['record_count']);
	$smarty->assign('page_count', $users_real_list['page_count']);
	$sort_flag = sort_flag($users_real_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('users_real_list.dwt'), '', array('filter' => $users_real_list['filter'], 'page_count' => $users_real_list['page_count']));
}
else if ($_REQUEST['act'] == 'batch') {
	admin_priv('users_real_manage');
	$user_type = empty($_REQUEST['user_type']) ? 0 : intval($_REQUEST['user_type']);
	$type = '';

	if ($user_type) {
		$type = '&user_type=' . $user_type;
	}

	if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
		sys_msg('没有选择任何数据', 1);
	}

	$real_id_arr = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;

	if (isset($_POST['type'])) {
		if ($_POST['type'] == 'batch_remove') {
			$sql = 'DELETE FROM ' . $ecs->table('users_real') . ' WHERE real_id ' . db_create_in($real_id_arr);

			if ($db->query($sql)) {
				$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'user_real.php?act=list' . $type);
				sys_msg('删除实名信息成功', 0, $lnk);
			}

			admin_log('', 'batch_trash', 'users_real');
		}
		else if ($_POST['type'] == 'review_to') {
			$time = gmtime();
			$review_status = $_POST['review_status'];
			$review_content = !empty($_POST['review_content']) ? trim($_POST['review_content']) : '';
			$sql = 'UPDATE ' . $ecs->table('users_real') . (' SET review_status = \'' . $review_status . '\', review_content = \'' . $review_content . '\', review_time = \'' . $time . '\' ') . ' WHERE real_id ' . db_create_in($real_id_arr);

			if ($db->query($sql)) {
				$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'user_real.php?act=list' . $type);
				sys_msg('实名信息审核状态设置成功', 0, $lnk);
			}
		}
	}
}
else if ($_REQUEST['act'] == 'remove') {
	admin_priv('users_real_manage');
	$real_id = !empty($_GET[real_id]) ? intval($_GET[real_id]) : 0;
	$user_type = empty($_REQUEST['user_type']) ? 0 : intval($_REQUEST['user_type']);
	$type = '';

	if ($user_type) {
		$type = '&user_type=' . $user_type;
	}

	if (0 < $real_id) {
		$sql = 'DELETE FROM ' . $ecs->table('users_real') . (' WHERE real_id = \'' . $real_id . '\'');

		if ($db->query($sql)) {
			admin_log(addslashes($real_id), 'remove', 'users_real');
			$link[] = array('text' => $_LANG['16_users_real'], 'href' => 'user_real.php?act=list' . $type);
			sys_msg('删除实名用户成功', 0, $link);
		}
	}
}

?>
