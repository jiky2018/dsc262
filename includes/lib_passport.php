<?php
//zend by 商创网络 Q Q:123456    禁止倒卖 一经发现停止任何服务
function register($username, $password, $email, $other = array(), $register_mode = 0)
{
	if (!empty($GLOBALS['_CFG']['shop_reg_closed'])) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['shop_register_closed']);
	}

	if (empty($username)) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['username_empty']);
	}
	else if (preg_match('/\'\\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username)) {
		$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['username_invalid'], htmlspecialchars($username)));
	}

	if (!empty($email)) {
		if (!is_email($email)) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_invalid'], htmlspecialchars($email)));
		}
	}

	if ($register_mode == 1) {
		if (empty($other['mobile_phone'])) {
			$GLOBALS['err']->add($GLOBALS['_LANG']['msg_mobile_code_blank']);
		}
		else {
			if ($_CFG['sms_signin'] == 1) {
				if ($other['mobile_phone'] != $_SESSION['sms_mobile'] || $other['mobile_code'] != $_SESSION['sms_mobile_code']) {
					$GLOBALS['err']->add($GLOBALS['_LANG']['msg_mobile_mobile_code']);
				}
			}

			if (!preg_match('/^1[34578]{1}\\d{9}$/', $other['mobile_phone'])) {
				$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['msg_mobile_invalid'], htmlspecialchars($other['mobile_phone'])));
			}
		}
	}

	if (0 < $GLOBALS['err']->error_no) {
		return false;
	}

	if (admin_registered($username)) {
		$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['username_exist'], $username));
		return false;
	}

	if ($register_mode != 1) {
		$other['mobile_phone'] = '';
	}

	$user_registerMode = array('email' => $email, 'mobile_phone' => $other['mobile_phone'], 'is_validated' => $other['is_validated'], 'register_mode' => $register_mode);

	if (!$GLOBALS['user']->add_user($username, $password, $user_registerMode)) {
		if ($GLOBALS['user']->error == ERR_INVALID_USERNAME) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['username_invalid'], $username));
		}
		else if ($GLOBALS['user']->error == ERR_USERNAME_NOT_ALLOW) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['username_not_allow'], $username));
		}
		else if ($GLOBALS['user']->error == ERR_USERNAME_EXISTS) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['username_exist'], $username));
		}
		else if ($GLOBALS['user']->error == ERR_INVALID_EMAIL) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_invalid'], $email));
		}
		else if ($GLOBALS['user']->error == ERR_EMAIL_NOT_ALLOW) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_not_allow'], $email));
		}
		else if ($GLOBALS['user']->error == ERR_EMAIL_EXISTS) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_exist'], $email));
		}
		else {
			$GLOBALS['err']->add('UNKNOWN ERROR!');
		}

		return false;
	}
	else {
		$GLOBALS['user']->set_session($username);
		$GLOBALS['user']->set_cookie($username);

		if (!empty($GLOBALS['_CFG']['register_points'])) {
			log_account_change($_SESSION['user_id'], 0, 0, $GLOBALS['_CFG']['register_points'], $GLOBALS['_CFG']['register_points'], $GLOBALS['_LANG']['register_points']);
		}

		register_coupons($_SESSION['user_id']);
		$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
		if (isset($affiliate['on']) && $affiliate['on'] == 1) {
			$up_uid = get_affiliate();
			empty($affiliate) && ($affiliate = array());
			$affiliate['config']['level_register_all'] = intval($affiliate['config']['level_register_all']);
			$affiliate['config']['level_register_up'] = intval($affiliate['config']['level_register_up']);

			if ($up_uid) {
				if (!empty($affiliate['config']['level_register_all'])) {
					if (!empty($affiliate['config']['level_register_up'])) {
						$rank_points = $GLOBALS['db']->getOne('SELECT rank_points FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $up_uid . '\''));

						if ($rank_points + $affiliate['config']['level_register_all'] <= $affiliate['config']['level_register_up']) {
							log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, sprintf($GLOBALS['_LANG']['register_affiliate'], $_SESSION['user_id'], $username));
						}
					}
					else {
						log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, $GLOBALS['_LANG']['register_affiliate']);
					}
				}

				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET parent_id = ' . $up_uid . ' WHERE user_id = ' . $_SESSION['user_id'];
				$GLOBALS['db']->query($sql);
			}
		}

		$other_key_array = array('msn', 'qq', 'office_phone', 'home_phone');
		$update_data['reg_time'] = local_strtotime(local_date('Y-m-d H:i:s'));

		if ($other) {
			foreach ($other as $key => $val) {
				if (!in_array($key, $other_key_array)) {
					unset($other[$key]);
				}
				else {
					$other[$key] = htmlspecialchars(trim($val));
				}
			}

			$update_data = array_merge($update_data, $other);
		}

		$nick_name = rand(1, 99999999) . '-' . rand(1, 999999);
		$update_data['nick_name'] = $nick_name;
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'), $update_data, 'UPDATE', 'user_id = ' . $_SESSION['user_id']);
		update_user_info();
		recalculate_price();
		return true;
	}
}

function logout()
{
}

function edit_password($user_id, $old_password, $new_password = '', $code = '')
{
	if (empty($user_id)) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['not_login']);
	}

	if ($GLOBALS['user']->edit_password($user_id, $old_password, $new_password, $code)) {
		return true;
	}
	else {
		$GLOBALS['err']->add($GLOBALS['_LANG']['edit_password_failure']);
		return false;
	}
}

function check_userinfo($user_name, $email)
{
	if (empty($user_name) || empty($email)) {
		ecs_header("Location: user.php?act=get_password\n");
		exit();
	}

	$user_info = $GLOBALS['user']->check_pwd_info($user_name, $email);

	if (!empty($user_info)) {
		return $user_info;
	}
	else {
		return false;
	}
}

function send_pwd_email($uid, $user_name, $email, $code)
{
	if (empty($uid) || empty($user_name) || empty($email) || empty($code)) {
		ecs_header("Location: user.php?act=get_password\n");
		exit();
	}

	$template = get_mail_template('send_password');
	$reset_email = $GLOBALS['ecs']->url() . 'user.php?act=get_password&uid=' . $uid . '&code=' . $code;
	$GLOBALS['smarty']->assign('user_name', $user_name);
	$GLOBALS['smarty']->assign('reset_email', $reset_email);
	$GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
	$GLOBALS['smarty']->assign('send_date', date('Y-m-d'));
	$GLOBALS['smarty']->assign('sent_date', date('Y-m-d'));
	$content = $GLOBALS['smarty']->fetch('str:' . $template['template_content']);

	if (send_mail($user_name, $email, $template['template_subject'], $content, $template['is_html'])) {
		return true;
	}
	else {
		return false;
	}
}

function send_regiter_hash($user_id)
{
	$template = get_mail_template('register_validate');
	$hash = register_hash('encode', $user_id);
	$validate_email = $GLOBALS['ecs']->url() . 'user.php?act=validate_email&hash=' . $hash;
	$sql = 'SELECT user_name, email FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
	$row = $GLOBALS['db']->getRow($sql);
	$GLOBALS['smarty']->assign('user_name', $row['user_name']);
	$GLOBALS['smarty']->assign('validate_email', $validate_email);
	$GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
	$GLOBALS['smarty']->assign('send_date', date($GLOBALS['_CFG']['date_format']));
	$content = $GLOBALS['smarty']->fetch('str:' . $template['template_content']);

	if (send_mail($row['user_name'], $row['email'], $template['template_subject'], $content, $template['is_html'])) {
		return true;
	}
	else {
		return false;
	}
}

function send_account_safe_hash($user_id, $type, $validated = 0)
{
	$template = get_mail_template('register_validate');
	$hash = register_hash('encode', $user_id);
	$validate_email = $GLOBALS['ecs']->url() . 'user.php?act=account_safe&type=validated_email&hash=' . $hash;
	$sql = 'SELECT user_name, email FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
	$row = $GLOBALS['db']->getRow($sql);
	$email = $row['email'];

	switch ($type) {
	case 'change_pwd':
		$validate_email = $GLOBALS['ecs']->url() . 'user.php?act=account_safe&type=validated_email&mail_type=change_pwd&hash=' . $hash;
		break;

	case 'change_mail':
		$validate_email = $GLOBALS['ecs']->url() . 'user.php?act=account_safe&type=validated_email&mail_type=change_mail&hash=' . $hash;
		break;

	case 'change_mobile':
		$validate_email = $GLOBALS['ecs']->url() . 'user.php?act=account_safe&type=validated_email&mail_type=change_mobile&hash=' . $hash;
		break;

	case 'change_paypwd':
		$validate_email = $GLOBALS['ecs']->url() . 'user.php?act=account_safe&type=validated_email&mail_type=change_paypwd&hash=' . $hash;
		break;

	case 'editmail':
		if (!empty($validated)) {
			$validated = '&validated=1';
		}
		else {
			$validated = '';
		}

		$validate_email = $GLOBALS['ecs']->url() . 'user.php?act=account_safe&type=validated_email&mail_type=editmail' . $validated . '&hash=' . $hash;
		$email = $_SESSION['new_email' . $user_id];
		break;

	default:
		break;
	}

	$GLOBALS['smarty']->assign('user_name', $row['user_name']);
	$GLOBALS['smarty']->assign('validate_email', $validate_email);
	$GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
	$GLOBALS['smarty']->assign('send_date', date($GLOBALS['_CFG']['date_format']));
	$content = $GLOBALS['smarty']->fetch('str:' . $template['template_content']);

	if (send_mail($row['user_name'], $email, $template['template_subject'], $content, $template['is_html'])) {
		return true;
	}
	else {
		return false;
	}
}

function register_hash($operation, $key)
{
	if ($operation == 'encode') {
		$user_id = intval($key);
		$sql = 'SELECT reg_time ' . ' FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
		$reg_time = $GLOBALS['db']->getOne($sql);
		$hash = substr(md5($user_id . $GLOBALS['_CFG']['hash_code'] . $reg_time), 16, 4);
		return base64_encode($user_id . ',' . $hash);
	}
	else {
		$hash = base64_decode(trim($key));
		$row = explode(',', $hash);

		if (count($row) != 2) {
			return 0;
		}

		$user_id = intval($row[0]);
		$salt = trim($row[1]);
		if ($user_id <= 0 || strlen($salt) != 4) {
			return 0;
		}

		$sql = 'SELECT reg_time ' . ' FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
		$reg_time = $GLOBALS['db']->getOne($sql);
		$pre_salt = substr(md5($user_id . $GLOBALS['_CFG']['hash_code'] . $reg_time), 16, 4);

		if ($pre_salt == $salt) {
			return $user_id;
		}
		else {
			return 0;
		}
	}
}

function admin_registered($adminname)
{
	$res = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('admin_user') . (' WHERE user_name = \'' . $adminname . '\''));
	return $res;
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
