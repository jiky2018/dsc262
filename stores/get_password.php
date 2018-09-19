<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_SERVER['REQUEST_METHOD'])) {
	$_SERVER['REQUEST_METHOD'] = 'GET';
}
else {
	$_SERVER['REQUEST_METHOD'] = trim($_SERVER['REQUEST_METHOD']);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (!empty($_GET['act']) && ($_GET['act'] == 'reset_pwd')) {
		$code = (!empty($_GET['code']) ? trim($_GET['code']) : '');
		$adminid = (!empty($_GET['uid']) ? intval($_GET['uid']) : 0);
		if (($adminid == 0) || empty($code)) {
			ecs_header("Location: privilege.php?act=login\n");
			exit();
		}

		$sql = 'SELECT stores_pwd FROM ' . $ecs->table('store_user') . ' WHERE id = \'' . $adminid . '\'';
		$password = $db->getOne($sql);

		if (md5($adminid . $password) != $code) {
			$link[0]['text'] = $_LANG['back'];
			$link[0]['href'] = 'privilege.php?act=login';
			sys_msg($_LANG['code_param_error'], 0, $link);
		}
		else {
			$smarty->assign('adminid', $adminid);
			$smarty->assign('code', $code);
			$smarty->assign('form_act', 'reset_pwd');
		}
	}
	else {
		if (!empty($_GET['act']) && ($_GET['act'] == 'forget_pwd')) {
			$smarty->assign('form_act', 'forget_pwd');
		}
	}

	$smarty->assign('ur_here', $_LANG['get_newpassword']);
	assign_query_info();
	$smarty->display('get_pwd.dwt');
}
else {
	if (!empty($_POST['action']) && ($_POST['action'] == 'get_pwd')) {
		$admin_username = (!empty($_POST['user_name']) ? trim($_POST['user_name']) : '');
		$admin_email = (!empty($_POST['email']) ? trim($_POST['email']) : '');
		if (empty($admin_username) || empty($admin_email)) {
			ecs_header("Location: privilege.php?act=login\n");
			exit();
		}

		$sql = 'SELECT id, stores_pwd FROM ' . $ecs->table('store_user') . ' WHERE stores_user = \'' . $admin_username . '\' AND email = \'' . $admin_email . '\'';
		$admin_info = $db->getRow($sql);

		if (!empty($admin_info)) {
			$admin_id = $admin_info['id'];
			$code = md5($admin_id . $admin_info['stores_pwd']);
			$template = get_mail_template('send_password');
			$reset_email = $ecs->stores_url() . STORES_PATH . '/get_password.php?act=reset_pwd&uid=' . $admin_id . '&code=' . $code;
			$smarty->assign('user_name', $admin_username);
			$smarty->assign('reset_email', $reset_email);
			$smarty->assign('shop_name', $_CFG['shop_name']);
			$smarty->assign('send_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
			$smarty->assign('sent_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
			$content = $smarty->fetch('str:' . $template['template_content']);

			if (send_mail($admin_username, $admin_email, $template['template_subject'], $content, $template['is_html'])) {
				$link[0]['text'] = $_LANG['back'];
				$link[0]['href'] = 'privilege.php?act=login';
				sys_msg($_LANG['send_success'] . $admin_email, 0, $link);
			}
			else {
				sys_msg($_LANG['send_mail_error'], 1);
			}
		}
		else {
			sys_msg($_LANG['email_username_error'], 1);
		}
	}
	else {
		if (!empty($_POST['action']) && ($_POST['action'] == 'reset_pwd')) {
			$new_password = (isset($_POST['password']) ? trim($_POST['password']) : '');
			$adminid = (isset($_POST['adminid']) ? intval($_POST['adminid']) : 0);
			$code = (isset($_POST['code']) ? trim($_POST['code']) : '');
			if (empty($new_password) || empty($code) || ($adminid == 0)) {
				ecs_header("Location: privilege.php?act=login\n");
				exit();
			}

			$sql = 'SELECT stores_pwd FROM ' . $ecs->table('store_user') . ' WHERE id = \'' . $adminid . '\'';
			$password = $db->getOne($sql);

			if (md5($adminid . $password) != $code) {
				$link[0]['text'] = $_LANG['back'];
				$link[0]['href'] = 'privilege.php?act=login';
				sys_msg($_LANG['code_param_error'], 0, $link);
			}

			$ec_salt = rand(1, 9999);
			$sql = 'UPDATE ' . $ecs->table('store_user') . 'SET stores_pwd = \'' . md5(md5($new_password) . $ec_salt) . '\',`ec_salt`=\'' . $ec_salt . '\' ' . 'WHERE id = \'' . $adminid . '\'';
			$result = $db->query($sql);

			if ($result) {
				$link[0]['text'] = $_LANG['login_now'];
				$link[0]['href'] = 'privilege.php?act=login';
				sys_msg($_LANG['update_pwd_success'], 0, $link);
			}
			else {
				sys_msg($_LANG['update_pwd_failed'], 1);
			}
		}
	}
}

?>
