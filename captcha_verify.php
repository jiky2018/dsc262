<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
define('INIT_NO_SMARTY', true);
require dirname(__FILE__) . '/includes/init.php';
$captcha_width = $_CFG['captcha_width'];
$captcha_height = $_CFG['captcha_height'];
$captcha_font_size = $_CFG['captcha_font_size'];
$captcha_length = $_CFG['captcha_length'];

if (isset($_REQUEST['width'])) {
	$captcha_width = $_REQUEST['width'];
}

if (isset($_REQUEST['height'])) {
	$captcha_height = $_REQUEST['height'];
}

if (isset($_REQUEST['font_size'])) {
	$captcha_font_size = $_REQUEST['font_size'];
}

if (isset($_REQUEST['length'])) {
	$captcha_length = $_REQUEST['length'];
}

$code_config = array('imageW' => $captcha_width, 'imageH' => $captcha_height, 'fontSize' => $captcha_font_size, 'length' => $captcha_length, 'useNoise' => false);

if (isset($_REQUEST['captcha'])) {
	if ($_REQUEST['captcha'] == 'is_common') {
		$code_config['seKey'] = 'captcha_common';
	}
	else if ($_REQUEST['captcha'] == 'is_login') {
		$code_config['seKey'] = 'captcha_login';
	}
	else if ($_REQUEST['captcha'] == 'is_register_email') {
		$code_config['seKey'] = 'register_email';
	}
	else if ($_REQUEST['captcha'] == 'is_register_phone') {
		$code_config['seKey'] = 'mobile_phone';
	}
	else if ($_REQUEST['captcha'] == 'is_discuss') {
		$code_config['seKey'] = 'captcha_discuss';
	}
	else if ($_REQUEST['captcha'] == 'is_user_comment') {
		$code_config['seKey'] = 'user_comment';
	}
	else if ($_REQUEST['captcha'] == 'is_get_password') {
		$code_config['seKey'] = 'get_password';
	}
	else if ($_REQUEST['captcha'] == 'is_get_phone_password') {
		$code_config['seKey'] = 'get_phone_password';
	}
	else if ($_REQUEST['captcha'] == 'get_pwd_question') {
		$code_config['seKey'] = 'psw_question';
	}
	else if ($_REQUEST['captcha'] == 'is_bonus') {
		$code_config['seKey'] = 'bonus';
	}
	else if ($_REQUEST['captcha'] == 'is_value_card') {
		$code_config['seKey'] = 'value_card';
	}
	else if ($_REQUEST['captcha'] == 'is_pay_card') {
		$code_config['seKey'] = 'pay_card';
	}
	else if ($_REQUEST['captcha'] == 'admin_login') {
		$code_config['seKey'] = 'admin_login';
	}
	else if ($_REQUEST['captcha'] == 'change_password_s') {
		$code_config['seKey'] = 'change_password_s';
	}
	else if ($_REQUEST['captcha'] == 'change_password_f') {
		$code_config['seKey'] = 'change_password_f';
	}
}

$identify = isset($_REQUEST['identify']) ? intval($_REQUEST['identify']) : '';
$img = new Verify($code_config);
$img->entry($identify);

?>
