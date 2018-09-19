<?php
//zend by QQ:123456  商创-网络  禁止倒卖 一经发现停止任何服务
function register($username, $password, $email, $other = array())
{
	if (!empty($GLOBALS['_CFG']['shop_reg_closed'])) {
		$GLOBALS['err']->add(L('shop_register_closed'));
	}

	if (empty($username)) {
		$GLOBALS['err']->add(L('username_empty'));
	}
	else if (preg_match('/\'\\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username)) {
		$GLOBALS['err']->add(sprintf(L('username_invalid'), htmlspecialchars($username)));
	}

	if (empty($email)) {
		$GLOBALS['err']->add(L('email_empty'));
	}
	else if (!is_email($email)) {
		$GLOBALS['err']->add(sprintf(L('email_invalid'), htmlspecialchars($email)));
	}

	if (0 < $GLOBALS['err']->error_no) {
		return false;
	}

	if (admin_registered($username)) {
		$GLOBALS['err']->add(sprintf(L('username_exist'), $username));
		return false;
	}

	if (!$GLOBALS['user']->add_user($username, $password, $email)) {
		if ($GLOBALS['user']->error == ERR_INVALID_USERNAME) {
			$GLOBALS['err']->add(sprintf(L('username_invalid'), $username));
		}
		else if ($GLOBALS['user']->error == ERR_USERNAME_NOT_ALLOW) {
			$GLOBALS['err']->add(sprintf(L('username_not_allow'), $username));
		}
		else if ($GLOBALS['user']->error == ERR_USERNAME_EXISTS) {
			$GLOBALS['err']->add(sprintf(L('username_exist'), $username));
		}
		else if ($GLOBALS['user']->error == ERR_INVALID_EMAIL) {
			$GLOBALS['err']->add(sprintf(L('email_invalid'), $email));
		}
		else if ($GLOBALS['user']->error == ERR_EMAIL_NOT_ALLOW) {
			$GLOBALS['err']->add(sprintf(L('email_not_allow'), $email));
		}
		else if ($GLOBALS['user']->error == ERR_EMAIL_EXISTS) {
			$GLOBALS['err']->add(sprintf(L('email_exist'), $email));
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
			log_account_change($_SESSION['user_id'], 0, 0, $GLOBALS['_CFG']['register_points'], $GLOBALS['_CFG']['register_points'], L('register_points'));
		}

		$cou_id = $GLOBALS['db']->getOne('SELECT cou_id FROM ' . $GLOBALS['ecs']->table('coupons') . ' WHERE cou_type =1');

		if (!empty($cou_id)) {
			register_coupons($_SESSION['user_id']);
		}

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
							log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, sprintf(L('register_affiliate'), $_SESSION['user_id'], $username));
						}
					}
					else {
						log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, L('register_affiliate'));
					}
				}

				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET parent_id = ' . $up_uid . ' WHERE user_id = ' . $_SESSION['user_id'];
				$GLOBALS['db']->query($sql);
			}
		}

		if (is_dir(APP_DRP_PATH)) {
			$affiliate = get_drp_affiliate_config();
			if (isset($affiliate['on']) && $affiliate['on'] == 1) {
				$up_drpid = get_drp_affiliate();

				if ($up_drpid) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET drp_parent_id = ' . $up_drpid . ' WHERE user_id = ' . $_SESSION['user_id'];
					$GLOBALS['db']->query($sql);
				}
			}

			$register = dao('drp_config')->field('value')->where(array('code' => 'register'))->find();

			if ($register['value'] == 1) {
				$data['shop_name'] = $_SESSION['user_name'] . '店铺';
				$data['real_name'] = $_SESSION['user_name'];
				$data['create_time'] = gmtime();
				$data['audit'] = 1;
				$data['status'] = 1;
				$data['type'] = 0;
				$data['user_id'] = $_SESSION['user_id'];
				dao('drp_shop')->data($data)->add();
			}

			if (0 < $other['drp_parent_id']) {
				$time = gmtime();
				$pushData = array(
					'keyword1' => array('value' => $_SESSION['user_id'], 'color' => '#173177'),
					'keyword2' => array('value' => date('Y-m-d', $time), 'color' => '#173177'),
					'remark'   => array('value' => $other['nick_name'] . '新会员加入', 'color' => '#173177')
					);
				$url = __HOST__ . url('drp/user/index');
				push_template('OPENTM202967310', $pushData, $url, $other['drp_parent_id']);
			}
		}

		$other_key_array = array('aite_id', 'nick_name', 'sex', 'user_picture', 'msn', 'qq', 'office_phone', 'home_phone', 'mobile_phone', 'parent_id', 'drp_parent_id');
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

		dao('users')->data($update_data)->where(array('user_id' => $_SESSION['user_id']))->save();
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
		$GLOBALS['err']->add(L('not_login'));
	}

	if ($GLOBALS['user']->edit_password($user_id, $old_password, $new_password, $code)) {
		return true;
	}
	else {
		$GLOBALS['err']->add(L('edit_password_failure'));
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
		return false;
	}

	session('maildata', array('email' => $email, 'code' => md5($code)));
	$template = $user_name . "您好！<br>\r\n\t\t\t\t<br>\r\n\t\t\t\t您的验证码是:<h3>" . $code . "</h3> 20分钟有效<br>\r\n\t\t\t\t<br>\r\n\t\t\t\t请及时输入，以完成操作。<br>\r\n\t\t\t\t<br>\r\n\t\t\t\t" . $shop_name . "<br>\r\n\t\t\t\t" . $send_date;
	$GLOBALS['smarty']->assign('code', $code);
	$GLOBALS['smarty']->assign('user_name', $user_name);
	$GLOBALS['smarty']->assign('shop_name', C('shop.shop_name'));
	$GLOBALS['smarty']->assign('send_date', date('Y-m-d'));
	$content = $GLOBALS['smarty']->fetch('', $template);

	if (send_mail($user_name, $email, '发送验证码', $content, 1)) {
		return true;
	}
	else {
		return false;
	}
}

function uid($email = '')
{
	if (!empty($email)) {
		$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('users') . ('WHERE email = \'' . $email . '\'');
		$row = $GLOBALS['db']->getRow($sql);
		return $row['user_id'];
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
	$content = $GLOBALS['smarty']->fetch('', $template['template_content']);

	if (send_mail($row['user_name'], $row['email'], $template['template_subject'], $content, $template['is_html'])) {
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


?>
