<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_admin_userlist($ru_id)
{
	$list = array();
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['role_id'] = empty($_REQUEST['role_id']) ? 0 : intval($_REQUEST['role_id']);
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

		if (0 < $filter['role_id']) {
			$where .= ' AND au.role_id = \'' . $filter['role_id'] . '\' ';
		}

		$admin_id = get_admin_id();
		$action_list = get_table_date('admin_user', 'user_id=\'' . $admin_id . '\'', array('action_list'), 2);

		if ($action_list != 'all') {
			$admin_ids = array();

			while ($admin_id) {
				$sql = ' SELECT user_id FROM ' . $GLOBALS['ecs']->table('admin_user') . ' WHERE parent_id ' . db_create_in($admin_id);
				$admin_id = $GLOBALS['db']->getCol($sql);
				$admin_ids = array_merge($admin_ids, $admin_id);
			}

			if ($admin_ids) {
				$where .= ' AND user_id ' . db_create_in($admin_ids);
			}
			else {
				$where .= ' AND user_id IS NULL ';
			}
		}

		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_where = '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($ru_id == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$where .= ' AND au.ru_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = au.ru_id ' . $store_where . ') > 0 ');
				}
			}
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('admin_user') . ' AS au ' . (' WHERE 1 AND ru_id = 0 ' . $where);
		$record_count = $GLOBALS['db']->getOne($sql);
		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT user_id, user_name, au.ru_id, email, add_time, last_login, agency_id, parent_id ' . 'FROM ' . $GLOBALS['ecs']->table('admin_user') . ' AS au ' . 'WHERE 1 AND ru_id = 0 ' . $where . ' ORDER BY user_id DESC ' . 'LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
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

		if ($val['agency_id']) {
			$list[$key]['agency'] = get_agency_user($val['agency_id']);
		}

		$list[$key]['parent_name'] = get_table_date('admin_user', 'user_id=\'' . $val['parent_id'] . '\'', array('user_name'), 2);
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

function get_role_list()
{
	$list = array();
	$sql = 'SELECT role_id, role_name, action_list ' . 'FROM ' . $GLOBALS['ecs']->table('role');
	$list = $GLOBALS['db']->getAll($sql);
	return $list;
}

function get_agency_user($agency_id = 0)
{
	$sql = 'SELECT agency_id, agency_name FROM ' . $GLOBALS['ecs']->table('agency') . (' WHERE agency_id = \'' . $agency_id . '\'');
	$res = $GLOBALS['db']->getRow($sql);
	return $res;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'login';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$exc = new exchange($ecs->table('admin_user'), $db, 'user_id', 'user_name');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

grade_expire();
$smarty->assign('seller', 1);
$php_self = get_php_self(1);
$smarty->assign('php_self', $php_self);

if ($_REQUEST['act'] == 'logout') {
	setcookie('ECSCP[admin_id]', '', 1);
	setcookie('ECSCP[admin_pass]', '', 1);
	$sess->destroy_session();
	$Loaction = 'privilege.php?act=logout';
	ecs_header('Location: ' . $Loaction . "\n");
	exit();
}

if ($_REQUEST['act'] == 'login') {
	setcookie('dscActionParam', '', gmtime(), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'admin_login_logo\'';
	$admin_login_logo = strstr($GLOBALS['db']->getOne($sql), 'images');
	$smarty->assign('admin_login_logo', $admin_login_logo);
	$dsc_token = get_dsc_token();
	$smarty->assign('dsc_token', $dsc_token);
	if (intval($_CFG['captcha']) & CAPTCHA_ADMIN && 0 < gd_version()) {
		$smarty->assign('gd_version', gd_version());
		$smarty->assign('random', mt_rand());
	}

	$smarty->display('login.dwt');
}
else if ($_REQUEST['act'] == 'signin') {
	setcookie('dscActionParam', 'index|main', gmtime(), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('admin_type', 0, gmtime(), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$_POST = get_request_filter($_POST, 1);
	$_POST['username'] = isset($_POST['username']) ? dsc_addslashes(trim($_POST['username'])) : '';
	$_POST['password'] = isset($_POST['password']) ? trim($_POST['password']) : '';
	$_POST['username'] = !empty($_POST['username']) ? str_replace(array('=', ' '), '', $_POST['username']) : '';
	if (0 < gd_version() && intval($_CFG['captcha']) & CAPTCHA_ADMIN) {
		require ROOT_PATH . '/includes/cls_captcha_verify.php';
		$captcha = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
		$verify = new Verify();

		if ($_REQUEST['type'] == 'captcha') {
			$captcha_code = $verify->check($captcha, 'admin_login', '', 'ajax');

			if (!$captcha_code) {
				exit('false');
			}
			else {
				exit('true');
			}
		}
		else {
			$captcha_code = $verify->check($captcha, 'admin_login');

			if (!$captcha_code) {
				sys_msg($_LANG['captcha_error'], 1);
			}
		}
	}

	if ($_REQUEST['type'] == 'password') {
		$sql = 'SELECT `ec_salt` FROM ' . $ecs->table('admin_user') . 'WHERE user_name = \'' . $_POST['username'] . '\'';
		$ec_salt = $db->getOne($sql);

		if (!empty($ec_salt)) {
			$sql = 'SELECT COUNT(*)' . ' FROM ' . $ecs->table('admin_user') . ' WHERE user_name = \'' . $_POST['username'] . '\' AND password = \'' . md5(md5($_POST['password']) . $ec_salt) . '\' AND ru_id = 0 ';
		}
		else {
			$sql = 'SELECT COUNT(*)' . ' FROM ' . $ecs->table('admin_user') . ' WHERE user_name = \'' . $_POST['username'] . '\' AND password = \'' . md5($_POST['password']) . '\' AND ru_id = 0 ';
		}

		$rs = $db->getOne($sql);

		if ($rs) {
			exit('true');
		}
		else {
			exit('false');
		}
	}

	$sql = 'SELECT `ec_salt` FROM ' . $ecs->table('admin_user') . 'WHERE user_name = \'' . $_POST['username'] . '\'';
	$ec_salt = $db->getOne($sql);

	if (!empty($ec_salt)) {
		$sql = 'SELECT user_id, user_name, password, last_login, action_list, last_login,suppliers_id,ec_salt' . ' FROM ' . $ecs->table('admin_user') . ' WHERE user_name = \'' . $_POST['username'] . '\' AND password = \'' . md5(md5($_POST['password']) . $ec_salt) . '\' AND ru_id = 0 ';
	}
	else {
		$sql = 'SELECT user_id, user_name, password, last_login, action_list, last_login,suppliers_id,ec_salt' . ' FROM ' . $ecs->table('admin_user') . ' WHERE user_name = \'' . $_POST['username'] . '\' AND password = \'' . md5($_POST['password']) . '\' AND ru_id = 0 ';
	}

	$row = $db->getRow($sql);

	if ($row) {
		if (!empty($row['suppliers_id'])) {
			$supplier_is_check = suppliers_list_info(' is_check = 1 AND suppliers_id = ' . $row['suppliers_id']);

			if (empty($supplier_is_check)) {
				sys_msg($_LANG['login_disable'], 1);
			}
		}

		set_admin_session($row['user_id'], $row['user_name'], $row['action_list'], $row['last_login']);
		$_SESSION['suppliers_id'] = $row['suppliers_id'];

		if (empty($row['ec_salt'])) {
			$ec_salt = rand(1, 9999);
			$new_possword = md5(md5($_POST['password']) . $ec_salt);
			$db->query('UPDATE ' . $ecs->table('admin_user') . ' SET ec_salt=\'' . $ec_salt . '\', password=\'' . $new_possword . '\'' . (' WHERE user_id=\'' . $_SESSION['admin_id'] . '\''));
		}

		if ($row['action_list'] == 'all' && empty($row['last_login'])) {
			$_SESSION['shop_guide'] = true;
		}

		$db->query('UPDATE ' . $ecs->table('admin_user') . ' SET last_login=\'' . gmtime() . '\', last_ip=\'' . real_ip() . '\'' . (' WHERE user_id=\'' . $_SESSION['admin_id'] . '\''));

		if (!empty($_POST['remember'])) {
			$time = gmtime() + 3600 * 24 * 7;
			setcookie('ECSCP[admin_id]', $row['user_id'], $time);
			setcookie('ECSCP[admin_pass]', md5($row['password'] . $_CFG['hash_code']), $time);
		}

		admin_log('', '', 'admin_login');
		clear_cart();
		ecs_header("Location: ./index.php\n");
		exit();
	}
	else {
		sys_msg($_LANG['login_faild'], 1);
	}
}
else if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', $_LANG['01_admin_list']);
	$smarty->assign('action_link', array('href' => 'privilege.php?act=add', 'text' => $_LANG['admin_add']));
	$smarty->assign('full_page', 1);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$admin_list = get_admin_userlist($adminru['ru_id']);
	$smarty->assign('admin_list', $admin_list['list']);
	$smarty->assign('filter', $admin_list['filter']);
	$smarty->assign('record_count', $admin_list['record_count']);
	$smarty->assign('page_count', $admin_list['page_count']);
	assign_query_info();
	$smarty->display('privilege_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$admin_list = get_admin_userlist($adminru['ru_id']);
	$smarty->assign('admin_list', $admin_list['list']);
	$smarty->assign('filter', $admin_list['filter']);
	$smarty->assign('record_count', $admin_list['record_count']);
	$smarty->assign('page_count', $admin_list['page_count']);
	make_json_result($smarty->fetch('privilege_list.dwt'), '', array('filter' => $admin_list['filter'], 'page_count' => $admin_list['page_count']));
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('admin_manage');
	$smarty->assign('ur_here', $_LANG['admin_add']);
	$smarty->assign('action_link', array('href' => 'privilege.php?act=list', 'text' => $_LANG['01_admin_list']));
	$smarty->assign('form_act', 'insert');
	$smarty->assign('action', 'add');
	$smarty->assign('select_role', get_role_list());
	$smarty->assign('role_manage', admin_priv('role_manage', '', false));
	assign_query_info();
	$smarty->display('privilege_info.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	admin_priv('admin_manage');
	$_POST['user_name'] = trim($_POST['user_name']);
	if ($_POST['user_name'] == '买家' || $_POST['user_name'] == '卖家') {
		$link[] = array('text' => '无效名称，不可使用', 'href' => 'privilege.php?act=modif');
		sys_msg('添加失败', 0, $link);
	}

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
	$role_id = '';
	$action_list = '';

	if (!empty($_POST['select_role'])) {
		$sql = 'SELECT action_list FROM ' . $ecs->table('role') . ' WHERE role_id = \'' . $_POST['select_role'] . '\'';
		$row = $db->getRow($sql);
		$action_list = $row['action_list'];
		$role_id = $_POST['select_role'];
	}

	$sql = 'SELECT nav_list FROM ' . $ecs->table('admin_user') . ' WHERE action_list = \'all\'';
	$row = $db->getRow($sql);
	$admin_id = get_admin_id();
	$sql = 'INSERT INTO ' . $ecs->table('admin_user') . ' (user_name, email, password, add_time, nav_list, action_list, role_id, parent_id, rs_id) ' . 'VALUES (\'' . trim($_POST['user_name']) . '\', \'' . trim($_POST['email']) . ('\', \'' . $password . '\', \'' . $add_time . '\', \'' . $row['nav_list'] . '\', \'' . $action_list . '\', \'' . $role_id . '\', \'' . $admin_id . '\', \'' . $adminru['rs_id'] . '\')');
	$db->query($sql);
	$new_id = $db->Insert_ID();
	$link[0]['text'] = $_LANG['go_allot_priv'];
	$link[0]['href'] = 'privilege.php?act=allot&id=' . $new_id . '&user=' . $_POST['user_name'] . '';
	$link[1]['text'] = $_LANG['continue_add'];
	$link[1]['href'] = 'privilege.php?act=add';
	sys_msg($_LANG['add'] . '&nbsp;' . $_POST['user_name'] . '&nbsp;' . $_LANG['action_succeed'], 0, $link);
	admin_log($_POST['user_name'], 'add', 'privilege');
}
else if ($_REQUEST['act'] == 'edit') {
	if ($_SESSION['admin_name'] == 'demo') {
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'privilege.php?act=list');
		sys_msg($_LANG['edit_admininfo_cannot'], 0, $link);
	}

	$_REQUEST['id'] = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if ($_SESSION['admin_id'] != $_REQUEST['id']) {
		admin_priv('admin_manage');
	}

	$sql = 'SELECT user_id, user_name, email, password, agency_id, role_id FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_REQUEST['id'] . '\'';
	$user_info = $db->getRow($sql);

	if (0 < $user_info['agency_id']) {
		$sql = 'SELECT agency_name FROM ' . $ecs->table('agency') . (' WHERE agency_id = \'' . $user_info['agency_id'] . '\'');
		$user_info['agency_name'] = $db->getOne($sql);
	}

	$smarty->assign('ur_here', $_LANG['admin_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['01_admin_list'], 'href' => 'privilege.php?act=list'));
	$smarty->assign('user', $user_info);
	$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_GET['id'] . '\''));

	if ($priv_str != 'all') {
		$smarty->assign('select_role', get_role_list());
	}

	$smarty->assign('form_act', 'update');
	$smarty->assign('action', 'edit');
	$smarty->assign('role_manage', admin_priv('role_manage', '', false));
	assign_query_info();
	$smarty->display('privilege_info.dwt');
}
else {
	if ($_REQUEST['act'] == 'update' || $_REQUEST['act'] == 'update_self') {
		$admin_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$admin_name = !empty($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
		$admin_email = !empty($_REQUEST['email']) ? trim($_REQUEST['email']) : '';
		$ec_salt = rand(1, 9999);
		$password = !empty($_POST['new_password']) ? ', password = \'' . md5(md5(trim($_POST['new_password'])) . $ec_salt) . '\'' : '';
		if ($admin_name == '买家' || $admin_name == '卖家') {
			$link[] = array('text' => '无效名称，不可使用', 'href' => 'privilege.php?act=modif');
			sys_msg('编辑失败', 0, $link);
		}

		if ($_REQUEST['act'] == 'update') {
			if ($_SESSION['admin_id'] != $_REQUEST['id']) {
				admin_priv('admin_manage');
			}

			$g_link = 'privilege.php?act=list';
			$nav_list = '';
		}
		else {
			$nav_list = !empty($_POST['nav_list']) ? ', nav_list = \'' . @join(',', $_POST['nav_list']) . '\'' : '';
			$admin_id = $_SESSION['admin_id'];
			$g_link = 'privilege.php?act=modif';
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

		$role_id = '';
		$action_list = '';

		if (!empty($_POST['select_role'])) {
			$sql = 'SELECT action_list FROM ' . $ecs->table('role') . ' WHERE role_id = \'' . $_POST['select_role'] . '\'';
			$row = $db->getRow($sql);
			$action_list = ', action_list = \'' . $row['action_list'] . '\'';
			$role_id = ', role_id = ' . $_POST['select_role'] . ' ';
		}

		$sql = 'SELECT ru_id FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $admin_id . '\' LIMIT 1');
		$ru_id = $db->getOne($sql);
		if ($ru_id && $GLOBALS['_CFG']['sms_seller_signin'] == '1') {
			$shop_name = get_shop_name($ru_id, 1);
			$sql = ' SELECT mobile, seller_email FROM ' . $ecs->table('seller_shopinfo') . (' WHERE ru_id = \'' . $ru_id . '\' LIMIT 1');
			$shopinfo = $db->getRow($sql);

			if (empty($shopinfo['mobile'])) {
				$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'contactPhone');

				if ($field['bool']) {
					$sql = ' SELECT contactPhone AS mobile FROM ' . $ecs->table('merchants_steps_fields') . (' WHERE user_id = \'' . $ru_id . '\' LIMIT 1');
					$stepsinfo = $db->getRow($sql);
					$shopinfo['mobile'] = $stepsinfo['mobile'];
				}
			}

			if (!empty($shopinfo['mobile'])) {
				$smsParams = array('name' => $shop_name ? $shop_name : '', 'seller_name' => $admin_name ? htmlspecialchars($admin_name) : '', 'sellername' => $admin_name ? htmlspecialchars($admin_name) : '', 'seller_password' => $_POST['new_password'] ? htmlspecialchars(trim($_POST['new_password'])) : '', 'sellerpassword' => $_POST['new_password'] ? htmlspecialchars(trim($_POST['new_password'])) : '', 'current_admin_name' => $current_admin_name ? $current_admin_name : '', 'currentadminname' => $current_admin_name ? $current_admin_name : '', 'edit_time' => local_date('Y-m-d H:i:s', gmtime()), 'edittime' => local_date('Y-m-d H:i:s', gmtime()), 'shop_name' => $_CFG['shop_name'], 'shopname' => $_CFG['shop_name'], 'mobile_phone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : '', 'mobilephone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : '');

				if ($GLOBALS['_CFG']['sms_type'] == 0) {
					if ($adminru['ru_id'] == 0 && ($admin_name != '' || $_POST['new_password'] != '')) {
						$send_result = huyi_sms($smsParams, 'sms_seller_signin');
						if (isset($send_result) && $send_result) {
							$err_code = true;
						}
						else {
							$err_code = false;
						}
					}
				}
				else if (1 <= $GLOBALS['_CFG']['sms_type']) {
					$result = sms_ali($smsParams, 'sms_seller_signin');

					if ($result) {
						$resp = $GLOBALS['ecs']->ali_yu($result);
					}
					else {
						sys_msg('阿里大鱼短信配置异常', 1);
					}
				}
			}

			$template = get_mail_template('seller_signin');
			if ($adminru['ru_id'] == 0 && $template['template_content'] != '') {
				$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'contactEmail');

				if ($field['bool']) {
					if (empty($shopinfo['seller_email'])) {
						$sql = ' SELECT contactEmail AS seller_email FROM ' . $ecs->table('merchants_steps_fields') . (' WHERE user_id = \'' . $ru_id . '\' LIMIT 1');
						$stepsinfo = $db->getRow($sql);
						$shopinfo['seller_email'] = $stepsinfo['seller_email'];
					}
				}

				if ($shopinfo['seller_email'] && ($admin_name != '' || $_POST['new_password'] != '')) {
					$smarty->assign('shop_name', $shop_name);
					$smarty->assign('seller_name', $admin_name);
					$smarty->assign('seller_psw', trim($_POST['new_password']));
					$smarty->assign('site_name', $_CFG['shop_name']);
					$smarty->assign('send_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
					$content = $smarty->fetch('str:' . $template['template_content']);
					send_mail($admin_name, $shopinfo['seller_email'], $template['template_subject'], $content, $template['is_html']);
				}
			}
		}

		if ($pwd_modified) {
			$sql = 'UPDATE ' . $ecs->table('admin_user') . ' SET ' . ('user_name = \'' . $admin_name . '\', ') . ('email = \'' . $admin_email . '\', ') . ('ec_salt = \'' . $ec_salt . '\' ') . $action_list . $role_id . $password . $nav_list . ('WHERE user_id = \'' . $admin_id . '\'');
		}
		else {
			$sql = 'UPDATE ' . $ecs->table('admin_user') . ' SET ' . ('user_name = \'' . $admin_name . '\', ') . ('email = \'' . $admin_email . '\' ') . $action_list . $role_id . $nav_list . ('WHERE user_id = \'' . $admin_id . '\'');
		}

		$db->query($sql);
		$current_admin_name = $db->getOne('SELECT user_name FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_SESSION['admin_id'] . '\'');
		admin_log($_POST['user_name'], 'edit', 'privilege');
		if ($pwd_modified && $_REQUEST['act'] == 'update_self') {
			$sess->delete_spec_admin_session($_SESSION['admin_id']);
			$msg = $_LANG['edit_password_succeed'];
		}
		else {
			$msg = $_LANG['edit_profile_succeed'];
		}

		$link[] = array('text' => strpos($g_link, 'list') ? $_LANG['back_admin_list'] : $_LANG['modif_info'], 'href' => $g_link);
		sys_msg($msg . '<script>parent.document.getElementById(\'header-frame\').contentWindow.document.location.reload();</script>', 0, $link);
	}
	else if ($_REQUEST['act'] == 'modif') {
		if ($_SESSION['admin_name'] == 'demo') {
			$link[] = array('text' => $_LANG['back_admin_list'], 'href' => 'privilege.php?act=list');
			sys_msg($_LANG['edit_admininfo_cannot'], 0, $link);
		}

		include_once 'includes/inc_menu.php';
		include_once 'includes/inc_priv.php';
		$sql = 'SELECT code FROM ' . $ecs->table('plugins');
		$rs = $db->query($sql);

		while ($row = $db->FetchRow($rs)) {
			if (file_exists(ROOT_PATH . 'plugins/' . $row['code'] . '/languages/common_' . $_CFG['lang'] . '.php')) {
				include_once ROOT_PATH . 'plugins/' . $row['code'] . '/languages/common_' . $_CFG['lang'] . '.php';
			}

			if (file_exists(ROOT_PATH . 'plugins/' . $row['code'] . '/languages/inc_menu.php')) {
				include_once ROOT_PATH . 'plugins/' . $row['code'] . '/languages/inc_menu.php';
			}
		}

		foreach ($modules as $key => $value) {
			ksort($modules[$key]);
		}

		ksort($modules);

		foreach ($modules as $key => $val) {
			if (is_array($val)) {
				foreach ($val as $k => $v) {
					if (is_array($purview[$k])) {
						$boole = false;

						foreach ($purview[$k] as $action) {
							$boole = $boole || admin_priv($action, '', false);
						}

						if (!$boole) {
							unset($modules[$key][$k]);
						}
					}
					else if (!admin_priv($purview[$k], '', false)) {
						unset($modules[$key][$k]);
					}
				}
			}
		}

		$sql = 'SELECT user_id, user_name, email, nav_list, ru_id ' . 'FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_SESSION['admin_id'] . '\'';
		$user_info = $db->getRow($sql);
		$nav_arr = trim($user_info['nav_list']) == '' ? array() : explode(',', $user_info['nav_list']);
		$nav_lst = array();

		foreach ($nav_arr as $val) {
			$arr = explode('|', $val);
			$nav_lst[$arr[1]] = $arr[0];
		}

		$smarty->assign('lang', $_LANG);
		$smarty->assign('ur_here', $_LANG['modif_info']);

		if ($user_info['ru_id'] == 0) {
			$smarty->assign('action_link', array('text' => $_LANG['01_admin_list'], 'href' => 'privilege.php?act=list'));
		}

		$smarty->assign('user', $user_info);
		$smarty->assign('menus', $modules);
		$smarty->assign('nav_arr', $nav_lst);
		$smarty->assign('form_act', 'update_self');
		$smarty->assign('action', 'modif');
		$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_GET['id'] . '\''));

		if ($priv_str == 'all') {
			$smarty->assign('priv_str', 1);
		}

		assign_query_info();
		$smarty->display('privilege_info.dwt');
	}
	else if ($_REQUEST['act'] == 'allot') {
		include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/priv_action.php';
		admin_priv('allot_priv');

		if ($_SESSION['admin_id'] == $_GET['id']) {
			admin_priv('all');
		}

		$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_GET['id'] . '\''));

		if ($priv_str == 'all') {
			$link[] = array('text' => $_LANG['back_admin_list'], 'href' => 'privilege.php?act=list');
			sys_msg($_LANG['edit_admininfo_cannot'], 0, $link);
		}

		$admin_id = get_admin_id();
		$action_list = get_table_date('admin_user', 'user_id=\'' . $admin_id . '\'', array('action_list'), 2);
		$action_array = explode(',', $action_list);
		$sql_query = 'SELECT action_id, parent_id, action_code, relevance, seller_show FROM ' . $ecs->table('admin_action') . ' WHERE parent_id = 0';
		$res = $db->query($sql_query);

		while ($rows = $db->FetchRow($res)) {
			if (!$GLOBALS['_CFG']['region_store_enabled'] && $rows['action_code'] == 'region_store') {
				continue;
			}

			if (!judge_supplier_enabled() && $rows['seller_show'] == 2) {
				continue;
			}

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
			$sql = 'SELECT action_id, parent_id, action_code,relevance FROM ' . $ecs->table('admin_action') . ' WHERE parent_id ' . db_create_in(array_keys($priv_arr));
			$result = $db->query($sql);

			while ($priv = $db->FetchRow($result)) {
				if (!empty($action_list) && $action_list != 'all' && !in_array($priv['action_code'], $action_array)) {
					continue;
				}

				if (!judge_supplier_enabled() && $priv['action_code'] == 'supplier_apply') {
					continue;
				}

				$priv_arr[$priv['parent_id']]['priv'][$priv['action_code']] = $priv;
			}

			foreach ($priv_arr as $action_id => $action_group) {
				if (isset($action_group['priv']) && $action_group['priv']) {
					$priv = @array_keys($action_group['priv']);
					$priv_arr[$action_id]['priv_list'] = join(',', $priv);

					if (!empty($action_group['priv'])) {
						foreach ($action_group['priv'] as $key => $val) {
							$priv_arr[$action_id]['priv'][$key]['cando'] = strpos($priv_str, $val['action_code']) !== false || $priv_str == 'all' ? 1 : 0;
						}
					}
				}
			}
		}

		$smarty->assign('lang', $_LANG);
		$smarty->assign('ur_here', $_LANG['allot_priv'] . ' [ ' . $_GET['user'] . ' ] ');
		$smarty->assign('action_link', array('href' => 'privilege.php?act=list', 'text' => $_LANG['01_admin_list']));
		$smarty->assign('priv_arr', $priv_arr);
		$smarty->assign('form_act', 'update_allot');
		$smarty->assign('user_id', $_GET['id']);
		assign_query_info();
		$smarty->display('privilege_allot.dwt');
	}
	else if ($_REQUEST['act'] == 'update_allot') {
		admin_priv('admin_manage');
		$admin_name = $db->getOne('SELECT user_name FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_POST['id'] . '\''));
		$act_list = @join(',', $_POST['action_code']);
		$sql = 'UPDATE ' . $ecs->table('admin_user') . (' SET action_list = \'' . $act_list . '\', role_id = \'\' ') . ('WHERE user_id = \'' . $_POST['id'] . '\'');
		$db->query($sql);

		if ($_SESSION['admin_id'] == $_POST['id']) {
			$_SESSION['action_list'] = $act_list;
		}

		admin_log(addslashes($admin_name), 'edit', 'privilege');
		$link[] = array('text' => $_LANG['back_admin_list'], 'href' => 'privilege.php?act=list');
		sys_msg($_LANG['edit'] . '&nbsp;' . $admin_name . '&nbsp;' . $_LANG['action_succeed'], 0, $link);
	}
	else if ($_REQUEST['act'] == 'remove') {
		check_authz_json('admin_drop');
		$id = intval($_GET['id']);
		$admin_name = $db->getOne('SELECT user_name FROM ' . $ecs->table('admin_user') . (' WHERE user_id=\'' . $id . '\''));

		if ($admin_name == 'demo') {
			make_json_error($_LANG['edit_remove_cannot']);
		}

		if ($id == 1) {
			make_json_error($_LANG['remove_cannot']);
		}

		if ($id == $_SESSION['admin_id']) {
			make_json_error($_LANG['remove_self_cannot']);
		}

		if ($exc->drop($id)) {
			$sess->delete_spec_admin_session($id);
			admin_log(addslashes($admin_name), 'remove', 'privilege');
			clear_cache_files();
		}

		$url = 'privilege.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
}

?>
