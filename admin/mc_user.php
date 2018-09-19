<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function mc_reg_user($str = '', $password = 'admin123')
{
	if (!$str) {
		return false;
	}

	$str = get_preg_replace($str);
	$str_arr = explode(',', $str);
	$password = md5($password);

	for ($i = 0; $i < count($str_arr); $i++) {
		if (!empty($str_arr[$i])) {
			$str_arr[$i] = explode('|', $str_arr[$i]);
			$other = array('user_name' => str_iconv($str_arr[$i][0]), 'password' => $password, 'email' => $str_arr[$i][1], 'msn' => $str_arr[$i][2], 'qq' => $str_arr[$i][3], 'office_phone' => $str_arr[$i][4], 'home_phone' => $str_arr[$i][5], 'mobile_phone' => $str_arr[$i][6], 'reg_time' => gmtime());
			$sql = 'select user_id from ' . $GLOBALS['ecs']->table('users') . ' where user_name = \'' . $other['user_name'] . '\'';
			$user_id = $GLOBALS['db']->getOne($sql);

			if ($user_id < 1) {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'), $other, 'INSERT');
			}
		}
	}
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require 'mc_function.php';
admin_priv('users_manage');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if ($_REQUEST['act'] == 'mc_add') {
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'mc_user.php');
	$password = $_REQUEST['password'];
	$confirm_password = $_REQUEST['confirm_password'];
	if (!$password || $password != $confirm_password) {
		sys_msg('两次密码输入不一致,请检查;', 0, $link);
	}

	if (!$_FILES['upfile']) {
		sys_msg('没有上传文件;', 0, $link);
	}

	$path = '../mc_upfile/' . date('Ym') . '/';
	$file_chk = uploadfile('upfile', $path, 'mc_user.php', 1024000, 'txt');

	if ($file_chk) {
		$filename = $path . $file_chk[0];
		$str = mc_read_txt($filename);

		if ($str) {
			mc_reg_user($str, $password);
		}
		else {
			sys_msg('读取文件出错;', 0, $link);
		}

		sys_msg('恭喜，批量注册用户成功！;', 0, $link);
	}
	else {
		sys_msg('文件未上传成功;', 0, $link);
	}
}
else {
	$smarty->assign('action_link', array('text' => $_LANG['03_users_list'], 'href' => 'users.php?act=list'));
	$smarty->assign('ur_here', $_LANG['batch_add_user']);
	$smarty->display('mc_user.dwt');
}

?>
