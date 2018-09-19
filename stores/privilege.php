<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
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

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'login';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$smarty->assign('seller', 1);
$php_self = get_php_self(1);
$smarty->assign('php_self', $php_self);

if ($_REQUEST['act'] == 'logout') {
	setcookie('ECSCP[stores_id]', '', 1);
	setcookie('ECSCP[store_user_id]', '', 1);
	setcookie('ECSCP[seller_pass]', '', 1);
	$sess->destroy_session();
	unset($_SESSION['admin_ru_id']);
	$_REQUEST['act'] = 'login';
}

if ($_REQUEST['act'] == 'login') {
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	$dsc_token = get_dsc_token();
	$smarty->assign('dsc_token', $dsc_token);
	$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'stores_login_logo\'';
	$stores_login_logo = strstr($GLOBALS['db']->getOne($sql), 'images');
	$smarty->assign('stores_login_logo', $stores_login_logo);
	if ((intval($_CFG['captcha']) & CAPTCHA_ADMIN) && (0 < gd_version())) {
		$smarty->assign('gd_version', gd_version());
		$smarty->assign('random', mt_rand());
	}

	$smarty->display('login.dwt');
}
else if ($_REQUEST['act'] == 'signin') {
	$_POST = get_request_filter($_POST, 1);
	$_POST['username'] = INPUT_I('post.stores_user', '');
	$_POST['password'] = INPUT_I('post.stores_pwd', '');
	$_POST['username'] = !empty($_POST['username']) ? str_replace(array('=', ' '), '', $_POST['username']) : '';
	$_POST['username'] = !empty($_POST['username']) ? $_POST['username'] : dsc_addslashes($_POST['username']);
	if ((intval($_CFG['captcha']) & CAPTCHA_ADMIN) && (0 < gd_version())) {
		require ROOT_PATH . '/includes/cls_captcha_verify.php';
		$captcha = (isset($_POST['captcha']) ? trim($_POST['captcha']) : '');
		$verify = new Verify();
		$captcha_code = $verify->check($captcha, 'admin_login');

		if (!$captcha_code) {
			make_json_response('', 0, $_LANG['captcha_error']);
		}
	}

	$sql = 'SELECT `ec_salt` FROM ' . $ecs->table('store_user') . 'WHERE stores_user = \'' . $_POST['username'] . '\'';
	$ec_salt = $db->getOne($sql);

	if (!empty($ec_salt)) {
		$sql = 'SELECT id,ru_id, stores_user, stores_pwd ,ec_salt,store_id ' . ' FROM ' . $ecs->table('store_user') . ' WHERE stores_user = \'' . $_POST['username'] . '\' AND stores_pwd = \'' . md5(md5($_POST['password']) . $ec_salt) . '\'';
	}
	else {
		$sql = 'SELECT id,ru_id, stores_user, stores_pwd ,ec_salt,store_id' . ' FROM ' . $ecs->table('store_user') . ' WHERE stores_user = \'' . $_POST['username'] . '\' AND stores_pwd = \'' . md5($_POST['password']) . '\'';
	}

	$row = $db->getRow($sql);

	if ($row) {
		set_admin_session($row['id'], $row['stores_user'], $row['store_id']);

		if (empty($row['ec_salt'])) {
			$ec_salt = rand(1, 9999);
			$new_possword = md5(md5($_POST['password']) . $ec_salt);
			$db->query('UPDATE ' . $ecs->table('store_user') . ' SET ec_salt=\'' . $ec_salt . '\', stores_pwd=\'' . $new_possword . '\'' . ' WHERE id=\'' . $_SESSION['store_user_id'] . '\'');
		}

		clear_cart();
		make_json_response('', 1, '登陆成功', array('url' => 'index.php'));
		exit();
	}
	else {
		make_json_response('', 0, $_LANG['login_faild']);
	}
}

?>
