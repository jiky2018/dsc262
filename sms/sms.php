<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function random($length = 6, $numeric = 0)
{
	PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);

	if ($numeric) {
		$hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
	}
	else {
		$hash = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max = strlen($chars) - 1;

		for ($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}

	return $hash;
}

function write_file($file_name, $content)
{
	mkdirs(date('Ymd'));
	$filename = date('Ymd') . '/' . $file_name . '.log';
	$Ts = fopen($filename, 'a+');
	fputs($Ts, "\r\n" . $content);
	fclose($Ts);
}

function mkdirs($dir, $mode = 511)
{
	if (is_dir($dir) || @mkdir($dir, $mode)) {
		return true;
	}

	if (!mkdirs(dirname($dir), $mode)) {
		return false;
	}

	return @mkdir($dir, $mode);
}

function read_file($file_name)
{
	$content = '';
	$filename = date('Ymd') . '/' . $file_name . '.log';

	if (function_exists('file_get_contents')) {
		@$content = file_get_contents($filename);
	}
	else if (@$fp = fopen($filename, 'r')) {
		@$content = fread($fp, filesize($filename));
		@fclose($fp);
	}

	$content = explode("\r\n", $content);
	return end($content);
}

function get_send_sms_keyval($key, $val)
{
	if (empty($key) || empty($val)) {
		$is_null = 1;
	}
	else {
		$is_null = 0;
	}

	return $is_null;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/../includes/init.php';
$mobile = $_POST['mobile'] + 0;
$mobile_code = $_POST['mobile_code'] + 0;
$security_code = $_POST['seccode'] + 0;
$username = !empty($_POST['username']) ? trim($_POST['username']) : '';
$sms_value = isset($_POST['sms_value']) ? trim($_POST['sms_value']) : '';
$_GET['flag'] = !empty($_GET['flag']) ? htmlspecialchars($_GET['flag']) : '';

if ($_GET['act'] == 'check') {
	if ($mobile != $_SESSION['sms_mobile'] || $mobile_code != $_SESSION['sms_mobile_code']) {
		exit(json_encode(array('msg' => '手机验证码输入错误。')));
	}
	else {
		exit(json_encode(array('code' => '2')));
	}
}

if ($_GET['act'] == 'send') {
	if ($GLOBALS['_CFG']['sms_type'] == 1) {
		$is_null = get_send_sms_keyval($GLOBALS['_CFG']['ali_appkey'], $GLOBALS['_CFG']['ali_secretkey']);
	}
	else if ($GLOBALS['_CFG']['sms_type'] == 2) {
		$is_null = get_send_sms_keyval($GLOBALS['_CFG']['access_key_id'], $GLOBALS['_CFG']['access_key_secret']);
	}
	else if ($GLOBALS['_CFG']['sms_type'] == 3) {
		$is_null = get_send_sms_keyval($GLOBALS['_CFG']['dsc_appkey'], $GLOBALS['_CFG']['dsc_appsecret']);
	}
	else {
		$is_null = get_send_sms_keyval($GLOBALS['_CFG']['sms_ecmoban_user'], $GLOBALS['_CFG']['sms_ecmoban_password']);
	}

	if ($is_null) {
		exit(json_encode(array('msg' => '发送失败，请检查短信配置')));
	}

	if (empty($mobile)) {
		exit(json_encode(array('msg' => '手机号码不能为空')));
	}

	$preg = '/^1[0-9]{10}$/';

	if (!preg_match($preg, $mobile)) {
		exit(json_encode(array('msg' => '手机号码不正确，请重新输入')));
	}

	if ($_GET['flag'] == 'register' && intval($_CFG['captcha']) & CAPTCHA_REGISTER && 0 < gd_version() || isset($_POST['captcha'])) {
		$captcha = isset($_POST['captcha']) && !empty($_POST['captcha']) ? trim($_POST['captcha']) : '';
		$seKey = isset($_POST['sekey']) && !empty($_POST['sekey']) ? trim($_POST['sekey']) : 'mobile_phone';

		if (empty($captcha)) {
			exit(json_encode(array('msg' => '验证码不能为空')));
		}

		$verify = new Verify();
		$captcha_code = $verify->check($captcha, $seKey, '', 'ajax');

		if (!$captcha_code) {
			exit(json_encode(array('msg' => '验证码有误')));
		}
	}
	else if ($_SESSION['sms_security_code'] != $security_code) {
		exit(json_encode(array('msg' => 'you are lost.')));
	}

	if ($_SESSION['sms_mobile']) {
		if (gmtime() - 60 < local_strtotime(read_file($mobile))) {
			exit(json_encode(array('msg' => '获取验证码太过频繁，一分钟之内只能获取一次。')));
		}
	}

	$where = '';
	if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
		$where = ' AND user_id <> \'' . $_SESSION['user_id'] . '\'';
	}

	$sql = 'SELECT user_id, user_name FROM ' . $ecs->table('users') . (' WHERE mobile_phone = \'' . $mobile . '\'') . $where;
	$row = $db->getRow($sql);
	if ($_GET['flag'] == 'register' || $_GET['flag'] == 'change_mobile') {
		if (!empty($row['user_id'])) {
			exit(json_encode(array('msg' => '手机已存在,请重新输入')));
		}
	}
	else if ($_GET['flag'] == 'forget') {
		if (empty($row['user_id'])) {
			exit(json_encode(array('msg' => "手机号码不存在\n无法通过该号码找回密码")));
		}
	}

	$mobile_code = random(6, 1);

	if ($GLOBALS['_CFG']['sms_type'] == 0) {
		$message = '您的验证码是：' . $mobile_code . '，请不要把验证码泄露给其他人，如非本人操作，可不用理会';
	}
	else {
		$message = array('mobile_code' => $mobile_code, 'user_name' => $username, 'sms_value' => $sms_value);
	}

	include ROOT_PATH . 'includes/cls_sms.php';
	$sms = new sms();
	$sms_error = '';
	$send_result = $sms->send($mobile, $message, '', 1, '', '', $sms_error, $mobile_code);
	write_file($mobile, date('Y-m-d H:i:s'));
	if (isset($send_result) && $send_result) {
		$_SESSION['sms_mobile'] = $mobile;
		$_SESSION['sms_mobile_code'] = $mobile_code;
		$sms_security_code = rand(1000, 9999);
		$_SESSION['sms_security_code'] = $sms_security_code;
		exit(json_encode(array('code' => 2, 'flag' => $_GET['flag'], 'sms_security_code' => $sms_security_code)));
	}
	else {
		if (empty($username)) {
			$error = 1;
			$sms_error = '请填写用户名';
		}

		exit(json_encode(array('msg' => $sms_error, 'error' => $error)));
	}
}

?>
