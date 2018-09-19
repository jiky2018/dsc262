<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_admin_userlist($ru_id)
{
	$list = array();
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['parent_id'] = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
		$filter['ru_id'] = $ru_id;
		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);
		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$where = '';

		if ($filter['keywords']) {
			$where .= ' AND (user_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\')';
		}

		if ($filter['parent_id']) {
			$where .= ' AND parent_id = \'' . $_SESSION['seller_id'] . '\'';
		}
		else {
			$where .= ' AND parent_id > 0';
		}

		if (0 < $filter['ru_id']) {
			$where .= ' AND ru_id = \'' . $filter['ru_id'] . '\' ';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('admin_user') . ' AS au ' . (' WHERE 1 AND ru_id > 0 ' . $where);
		$record_count = $GLOBALS['db']->getOne($sql);
		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT user_id, user_name, au.ru_id, email, add_time, last_login ' . 'FROM ' . $GLOBALS['ecs']->table('admin_user') . ' AS au ' . 'WHERE 1  AND ru_id > 0 ' . $where . ' ORDER BY user_id DESC ' . 'LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$list = $GLOBALS['db']->getAll($sql);

	foreach ($list as $key => $val) {
		$list[$key]['ru_name'] = get_shop_name($val['ru_id'], 1);
		$list[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['add_time']);
		$list[$key]['last_login'] = local_date($GLOBALS['_CFG']['time_format'], $val['last_login']);
	}

	$arr = array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function clear_cart()
{
	$sql = 'SELECT DISTINCT session_id ' . 'FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c, ' . $GLOBALS['ecs']->table('sessions') . ' AS s ' . 'WHERE c.session_id = s.sesskey ';
	$valid_sess = $GLOBALS['db']->getCol($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE session_id NOT ' . db_create_in($valid_sess);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart_combo') . ' WHERE session_id NOT ' . db_create_in($valid_sess);
	$GLOBALS['db']->query($sql);
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
	$smarty->assign('ur_here', '管理员列表');
	$smarty->assign('primary_cat', $_LANG['10_priv_admin']);

	if (0 < $adminru['ru_id']) {
		$smarty->assign('action_link', array('href' => 'privilege_seller.php?act=add', 'text' => $_LANG['admin_add'], 'class' => 'icon-plus'));
	}

	$smarty->assign('ru_id', $adminru['ru_id']);
	$smarty->assign('full_page', 1);
	$admin_list = get_admin_userlist($adminru['ru_id']);
	$page_count_arr = seller_page($admin_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('admin_list', $admin_list['list']);
	$smarty->assign('filter', $admin_list['filter']);
	$smarty->assign('record_count', $admin_list['record_count']);
	$smarty->assign('page_count', $admin_list['page_count']);
	assign_query_info();
	$smarty->assign('current', 'privilege_seller');
	$smarty->display('privilege_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$admin_list = get_admin_userlist($adminru['ru_id']);
	$page_count_arr = seller_page($admin_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('admin_list', $admin_list['list']);
	$smarty->assign('filter', $admin_list['filter']);
	$smarty->assign('record_count', $admin_list['record_count']);
	$smarty->assign('page_count', $admin_list['page_count']);
	$smarty->assign('current', 'privilege_seller');
	make_json_result($smarty->fetch('privilege_list.dwt'), '', array('filter' => $admin_list['filter'], 'page_count' => $admin_list['page_count']));
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('seller_manage');
	$smarty->assign('primary_cat', $_LANG['10_priv_admin']);
	$smarty->assign('ur_here', '添加管理员');
	$smarty->assign('menu_select', array('action' => '10_priv_admin', 'current' => '02_admin_seller'));
	$smarty->assign('action_link', array('href' => 'privilege_seller.php?act=list', 'text' => $_LANG['02_admin_seller'], 'class' => 'icon-reply'));
	$smarty->assign('form_act', 'insert');
	$smarty->assign('action', 'add');
	assign_query_info();
	$smarty->assign('current', 'privilege_seller');
	$smarty->display('privilege_info.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	admin_priv('seller_manage');

	if (!empty($_POST['user_name'])) {
		$is_only = $exc->is_only('user_name', stripslashes($_POST['user_name']));

		if (!$is_only) {
			sys_msg(sprintf($_LANG['user_name_exist'], stripslashes($_POST['user_name'])), 1);
		}
	}

	if (!empty($_POST['email'])) {
		$is_only = $exc->is_only('email', stripslashes($_POST['email']));

		if (!$is_only) {
			sys_msg(sprintf($_LANG['email_exist'], stripslashes($_POST['email'])), 1);
		}
	}

	$add_time = gmtime();
	$password = md5($_POST['password']);
	$parent_id = '';
	$action_list = '';
	$ru_id = '';

	if (0 < $_SESSION['seller_id']) {
		$res = $db->getRow(' SELECT ru_id,action_list FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'');
		$action_list = $res['action_list'];
		$ru_id = $res['ru_id'];
	}

	$sql = 'SELECT nav_list FROM ' . $ecs->table('admin_user') . ' WHERE action_list = \'all\'';
	$row = $db->getRow($sql);
	$sql = 'INSERT INTO ' . $ecs->table('admin_user') . ' (user_name, email, password, add_time, nav_list,action_list,ru_id,parent_id) ' . 'VALUES (\'' . trim($_POST['user_name']) . '\', \'' . trim($_POST['email']) . ('\', \'' . $password . '\', \'' . $add_time . '\', \'' . $row['nav_list'] . '\',\'' . $action_list . '\',\'' . $ru_id . '\',\'') . $_SESSION['seller_id'] . '\')';
	$db->query($sql);
	$new_id = $db->Insert_ID();
	$link[0]['text'] = $_LANG['go_allot_priv'];
	$link[0]['href'] = 'privilege_seller.php?act=allot&id=' . $new_id . '&user=' . $_POST['user_name'] . '';
	$link[1]['text'] = $_LANG['continue_add'];
	$link[1]['href'] = 'privilege_seller.php?act=add';
	sys_msg($_LANG['add'] . '&nbsp;' . $_POST['user_name'] . '&nbsp;' . $_LANG['action_succeed'], 0, $link);
	admin_log($_POST['user_name'], 'add', 'privilege');
}
else if ($_REQUEST['act'] == 'edit') {
	$smarty->assign('primary_cat', $_LANG['10_priv_admin']);
	$smarty->assign('menu_select', array('action' => '10_priv_admin', 'current' => '02_admin_seller'));

	if ($_SESSION['seller_name'] == 'demo') {
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'privilege_seller.php?act=list');
		sys_msg($_LANG['edit_admininfo_cannot'], 0, $link);
	}

	$_REQUEST['id'] = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if ($_SESSION['seller_id'] != $_REQUEST['id']) {
		admin_priv('seller_manage');
	}

	$sql = 'SELECT user_id, user_name, email, password, agency_id, role_id FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_REQUEST['id'] . '\'';
	$user_info = $db->getRow($sql);

	if (0 < $user_info['agency_id']) {
		$sql = 'SELECT agency_name FROM ' . $ecs->table('agency') . (' WHERE agency_id = \'' . $user_info['agency_id'] . '\'');
		$user_info['agency_name'] = $db->getOne($sql);
	}

	$smarty->assign('ur_here', $_LANG['admin_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['02_admin_seller'], 'href' => 'privilege_seller.php?act=list', 'class' => 'icon-reply'));
	$smarty->assign('user', $user_info);
	$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_GET['id'] . '\''));
	$smarty->assign('form_act', 'update');
	$smarty->assign('action', 'edit');
	assign_query_info();
	$smarty->display('privilege_info.dwt');
}
else {
	if ($_REQUEST['act'] == 'update' || $_REQUEST['act'] == 'update_self') {
		$admin_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$admin_name = !empty($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
		$admin_email = !empty($_REQUEST['email']) ? trim($_REQUEST['email']) : '';
		$ec_salt = rand(1, 9999);
		$password = !empty($_POST['new_password']) ? ', password = \'' . md5(md5($_POST['new_password']) . $ec_salt) . '\'' : '';

		if ($_REQUEST['act'] == 'update') {
			if ($_SESSION['seller_id'] != $_REQUEST['id']) {
				admin_priv('seller_manage');
			}

			$g_link = 'privilege_seller.php?act=list';
			$nav_list = '';
		}
		else {
			$nav_list = !empty($_POST['nav_list']) ? ', nav_list = \'' . @join(',', $_POST['nav_list']) . '\'' : '';
			$admin_id = $_SESSION['seller_id'];
			$g_link = 'privilege_seller.php?act=modif';
		}

		if (!empty($admin_name)) {
			$is_only = $exc->num('user_name', $admin_name, $admin_id);

			if ($is_only == 1) {
				sys_msg(sprintf($_LANG['user_name_exist'], stripslashes($admin_name)), 1);
			}
		}

		if (!empty($admin_email)) {
			$is_only = $exc->num('email', $admin_email, $admin_id);

			if ($is_only == 1) {
				sys_msg(sprintf($_LANG['email_exist'], stripslashes($admin_email)), 1);
			}
		}

		$pwd_modified = false;

		if (!empty($_POST['new_password'])) {
			if ($_POST['new_password'] != $_POST['pwd_confirm']) {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
				sys_msg($_LANG['js_languages']['password_error'], 0, $link);
			}
			else {
				$pwd_modified = true;
			}
		}

		if ($pwd_modified) {
			$sql = 'UPDATE ' . $ecs->table('admin_user') . ' SET ' . ('user_name = \'' . $admin_name . '\', ') . ('email = \'' . $admin_email . '\', ') . ('ec_salt = \'' . $ec_salt . '\' ') . $password . $nav_list . ('WHERE user_id = \'' . $admin_id . '\'');
		}
		else {
			$sql = 'UPDATE ' . $ecs->table('admin_user') . ' SET ' . ('user_name = \'' . $admin_name . '\', ') . ('email = \'' . $admin_email . '\' ') . $nav_list . ('WHERE user_id = \'' . $admin_id . '\'');
		}

		$db->query($sql);
		admin_log($_POST['user_name'], 'edit', 'privilege');
		if ($pwd_modified && $_REQUEST['act'] == 'update_self') {
			$sess->delete_spec_admin_session($_SESSION['seller_id']);
			$msg = $_LANG['edit_password_succeed'];
		}
		else {
			$msg = $_LANG['edit_profile_succeed'];
		}

		$link[] = array('text' => strpos($g_link, 'list') ? $_LANG['back_admin_list'] : $_LANG['modif_info'], 'href' => $g_link);
		sys_msg($msg . '<script>parent.document.getElementById(\'header-frame\').contentWindow.document.location.reload();</script>', 0, $link);
	}
	else if ($_REQUEST['act'] == 'allot') {
		admin_priv('seller_allot');
		include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/priv_action.php';
		$user_id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
		$smarty->assign('primary_cat', $_LANG['10_priv_admin']);
		$smarty->assign('menu_select', array('action' => '10_priv_admin', 'current' => '02_admin_seller'));
		$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_GET['id'] . '\''));

		if ($priv_str == 'all') {
			$link[] = array('text' => $_LANG['back_admin_list'], 'href' => 'privilege_seller.php?act=list');
			sys_msg($_LANG['edit_admininfo_cannot'], 0, $link);
		}

		$user_parent = $db->getRow('SELECT action_list, parent_id FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $user_id . '\''));
		$parent_priv = $db->getRow('SELECT action_list FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $user_parent['parent_id'] . '\'');
		$parent_priv = explode(',', $parent_priv['action_list']);
		$priv_str = $user_parent['action_list'];
		$sql_query = 'SELECT action_id, parent_id, action_code,relevance FROM ' . $ecs->table('admin_action') . ' WHERE parent_id = 0';
		$res = $db->query($sql_query);

		while ($rows = $db->FetchRow($res)) {
			if (!file_exists(MOBILE_WECHAT) && $rows['action_code'] == 'wechat') {
				continue;
			}

			if (!file_exists(MOBILE_DRP) && $rows['action_code'] == 'drp') {
				continue;
			}

			if (!wxapp_enabled() && $rows['action_code'] == 'wxapp') {
				continue;
			}

			if (!file_exists(MOBILE_TEAM) && $rows['action_code'] == 'team') {
				continue;
			}

			if (!file_exists(MOBILE_BARGAIN) && $rows['action_code'] == 'bargain_manage') {
				continue;
			}

			$priv_arr[$rows['action_id']] = $rows;
		}

		if ($priv_arr) {
			$sql = 'SELECT action_id, parent_id, action_code,relevance FROM ' . $ecs->table('admin_action') . ' WHERE parent_id ' . db_create_in(array_keys($priv_arr)) . ' AND action_code ' . db_create_in(array_values($parent_priv));
			$result = $db->query($sql);

			while ($priv = $db->FetchRow($result)) {
				$priv_arr[$priv['parent_id']]['priv'][$priv['action_code']] = $priv;
			}

			foreach ($priv_arr as $action_id => $action_group) {
				if ($action_group['priv']) {
					$priv = @array_keys($action_group['priv']);
					$priv_arr[$action_id]['priv_list'] = join(',', $priv);

					foreach ($action_group['priv'] as $key => $val) {
						$priv_arr[$action_id]['priv'][$key]['cando'] = strpos($priv_str, $val['action_code']) !== false || $priv_str == 'all' ? 1 : 0;
					}
				}
			}
		}

		$smarty->assign('lang', $_LANG);
		$smarty->assign('ur_here', $_LANG['allot_priv'] . ' [ ' . $_GET['user'] . ' ] ');
		$smarty->assign('action_link', array('href' => 'privilege_seller.php?act=list', 'text' => $_LANG['02_admin_seller'], 'class' => 'icon-reply'));
		$smarty->assign('priv_arr', $priv_arr);
		$smarty->assign('form_act', 'update_allot');
		$smarty->assign('user_id', $_GET['id']);
		assign_query_info();
		$smarty->assign('current', 'privilege_seller');
		$smarty->display('privilege_allot.dwt');
	}
	else if ($_REQUEST['act'] == 'update_allot') {
		admin_priv('seller_allot');
		$admin_name = $db->getOne('SELECT user_name FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_POST['id'] . '\''));
		$act_list = @join(',', $_POST['action_code']);
		$sql = 'UPDATE ' . $ecs->table('admin_user') . (' SET action_list = \'' . $act_list . '\', role_id = \'\' ') . ('WHERE user_id = \'' . $_POST['id'] . '\'');
		$db->query($sql);

		if ($_SESSION['admin_id'] == $_POST['id']) {
			$_SESSION['action_list'] = $act_list;
		}

		admin_log(addslashes($admin_name), 'edit', 'privilege');
		$link[] = array('text' => $_LANG['back_admin_list'], 'href' => 'privilege_seller.php?act=list');
		sys_msg($_LANG['edit'] . '&nbsp;' . $admin_name . '&nbsp;' . $_LANG['action_succeed'], 0, $link);
	}
	else if ($_REQUEST['act'] == 'remove') {
		check_authz_json('seller_drop');
		$id = intval($_GET['id']);
		$admin_name = $db->getOne('SELECT user_name FROM ' . $ecs->table('admin_user') . (' WHERE user_id=\'' . $id . '\''));

		if ($admin_name == 'demo') {
			make_json_error($_LANG['edit_remove_cannot']);
		}

		if ($id == 1) {
			make_json_error($_LANG['remove_cannot']);
		}

		if ($id == $_SESSION['seller_id']) {
			make_json_error($_LANG['remove_self_cannot']);
		}

		if ($exc->drop($id)) {
			$sess->delete_spec_admin_session($id);
			admin_log(addslashes($admin_name), 'remove', 'privilege');
			clear_cache_files();
		}

		$url = 'privilege_seller.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
}

?>
