<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
defined('IN_ECTOUCH') || exit('Deny Access');
class ucenter extends \App\Libraries\Integrate
{
	public function __construct($cfg)
	{
		parent::__construct(array());
		$this->user_table = 'users';
		$this->field_id = 'user_id';
		$this->field_name = 'user_name';
		$this->field_pass = 'password';
		$this->field_email = 'email';
		$this->field_gender = 'sex';
		$this->field_bday = 'birthday';
		$this->field_reg_date = 'reg_time';
		$this->need_sync = false;
		$this->is_ecshop = 1;
		if (!defined('UC_CONNECT') && isset($cfg['uc_id']) && isset($cfg['db_host']) && isset($cfg['db_user']) && isset($cfg['db_name'])) {
			if (strpos($cfg['db_pre'], '`' . $cfg['db_name'] . '`') === 0) {
				$db_pre = $cfg['db_pre'];
			}
			else {
				$db_pre = '`' . $cfg['db_name'] . '`.' . $cfg['db_pre'];
			}

			define('UC_CONNECT', isset($cfg['uc_connect']) ? $cfg['uc_connect'] : '');
			define('UC_DBHOST', isset($cfg['db_host']) ? $cfg['db_host'] : '');
			define('UC_DBUSER', isset($cfg['db_user']) ? $cfg['db_user'] : '');
			define('UC_DBPW', isset($cfg['db_pass']) ? $cfg['db_pass'] : '');
			define('UC_DBNAME', isset($cfg['db_name']) ? $cfg['db_name'] : '');
			define('UC_DBCHARSET', isset($cfg['db_charset']) ? $cfg['db_charset'] : '');
			define('UC_DBTABLEPRE', $db_pre);
			define('UC_DBCONNECT', '0');
			define('UC_KEY', isset($cfg['uc_key']) ? $cfg['uc_key'] : '');
			define('UC_API', isset($cfg['uc_url']) ? $cfg['uc_url'] : '');
			define('UC_CHARSET', isset($cfg['uc_charset']) ? $cfg['uc_charset'] : '');
			define('UC_IP', isset($cfg['uc_ip']) ? $cfg['uc_ip'] : '');
			define('UC_APPID', isset($cfg['uc_id']) ? $cfg['uc_id'] : '');
			define('UC_PPP', '20');
		}
	}

	public function login($username, $password, $remember = NULL)
	{
		list($uid, $uname, $pwd, $email, $repeat) = uc_call('uc_user_login', array($username, $password));
		$uname = addslashes($uname);

		if (0 < $uid) {
			$result = $this->db->getRow('SELECT user_id,ec_salt FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_name=\'' . $username . '\'');
			$name_exist = $result['user_id'];

			if (empty($result['ec_salt'])) {
				$user_exist = $this->db->getOne('SELECT user_id FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_name=\'' . $username . '\' AND password = \'' . MD5($password) . '\'');

				if (!empty($user_exist)) {
					$ec_salt = rand(1, 9999);
					$this->db->query('UPDATE ' . $GLOBALS['ecs']->table('users') . 'SET `password`=\'' . MD5(MD5($password) . $ec_salt) . '\',`ec_salt`=\'' . $ec_salt . '\' WHERE user_id = \'' . $uid . '\'');
				}
			}
			else {
				$user_exist = $this->db->getOne('SELECT user_id FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_name=\'' . $username . '\' AND password = \'' . MD5(MD5($password) . $result['ec_salt']) . '\'');
			}

			if (empty($user_exist)) {
				if (empty($name_exist)) {
					$reg_date = time();
					$ip = real_ip();
					$password = $this->compile_password(array('password' => $password));
					$this->db->query('INSERT INTO ' . $GLOBALS['ecs']->table('users') . '(`user_id`, `email`, `user_name`, `password`, `reg_time`, `last_login`, `last_ip`) VALUES (\'' . $uid . '\', \'' . $email . '\', \'' . $uname . '\', \'' . $password . '\', \'' . $reg_date . '\', \'' . $reg_date . '\', \'' . $ip . '\')');
				}
				else {
					if (empty($result['ec_salt'])) {
						$result['ec_salt'] = 0;
					}

					$this->db->query('UPDATE ' . $GLOBALS['ecs']->table('users') . 'SET `password`=\'' . MD5(MD5($password) . $result['ec_salt']) . '\',`ec_salt`=\'' . $result['ec_salt'] . '\' WHERE user_id = \'' . $uid . '\'');
				}
			}

			$this->set_session($uname);
			$this->set_cookie($uname);
			$this->ucdata = uc_call('uc_user_synlogin', array($uid));
			return true;
		}
		else if ($uid == -1) {
			$this->error = ERR_INVALID_USERNAME;
			return false;
		}
		else if ($uid == -2) {
			$this->error = ERR_INVALID_PASSWORD;
			return false;
		}
		else {
			return false;
		}
	}

	public function logout()
	{
		$this->set_cookie();
		$this->set_session();
		$this->ucdata = uc_call('uc_user_synlogout');
		return true;
	}

	public function add_user($username, $password, $email, $gender = -1, $bday = 0, $reg_date = 0, $md5password = '')
	{
		if ($this->check_user($username)) {
			$this->error = ERR_USERNAME_EXISTS;
			return false;
		}

		$uid = uc_call('uc_user_register', array($username, $password, $email));

		if ($uid <= 0) {
			if ($uid == -1) {
				$this->error = ERR_INVALID_USERNAME;
				return false;
			}
			else if ($uid == -2) {
				$this->error = ERR_USERNAME_NOT_ALLOW;
				return false;
			}
			else if ($uid == -3) {
				$this->error = ERR_USERNAME_EXISTS;
				return false;
			}
			else if ($uid == -4) {
				$this->error = ERR_INVALID_EMAIL;
				return false;
			}
			else if ($uid == -5) {
				$this->error = ERR_EMAIL_NOT_ALLOW;
				return false;
			}
			else if ($uid == -6) {
				$this->error = ERR_EMAIL_EXISTS;
				return false;
			}
			else {
				return false;
			}
		}
		else {
			$reg_date = time();
			$ip = real_ip();
			$password = $this->compile_password(array('password' => $password));
			$this->db->query('INSERT INTO ' . $GLOBALS['ecs']->table('users') . '(`user_id`, `email`, `user_name`, `password`, `reg_time`, `last_login`, `last_ip`) VALUES (\'' . $uid . '\', \'' . $email . '\', \'' . $username . '\', \'' . $password . '\', \'' . $reg_date . '\', \'' . $reg_date . '\', \'' . $ip . '\')');
			return true;
		}
	}

	public function check_user($username, $password = NULL)
	{
		$userdata = uc_call('uc_user_checkname', array($username));

		if ($userdata == 1) {
			return false;
		}
		else {
			return true;
		}
	}

	public function check_email($email)
	{
		if (!empty($email)) {
			$email_exist = uc_call('uc_user_checkemail', array($email));

			if ($email_exist == 1) {
				return false;
			}
			else {
				$this->error = ERR_EMAIL_EXISTS;
				return true;
			}
		}

		return true;
	}

	public function edit_user($cfg, $forget_pwd = '0')
	{
		$real_username = $cfg['username'];
		$cfg['username'] = addslashes($cfg['username']);
		$set_str = '';
		$valarr = array('email' => 'email', 'gender' => 'sex', 'bday' => 'birthday');

		foreach ($cfg as $key => $val) {
			if (($key == 'username') || ($key == 'password') || ($key == 'old_password')) {
				continue;
			}

			$set_str .= $valarr[$key] . '=' . '\'' . $val . '\',';
		}

		$set_str = substr($set_str, 0, -1);

		if (!empty($set_str)) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET ' . $set_str . '  WHERE user_name = \'' . $cfg['username'] . '\'';
			$GLOBALS['db']->query($sql);
			$flag = true;
		}

		if (!empty($cfg['email'])) {
			$ucresult = uc_call('uc_user_edit', array($cfg['username'], '', '', $cfg['email'], 1));

			if (0 < $ucresult) {
				$flag = true;
			}
			else if ($ucresult == -4) {
				$this->error = ERR_INVALID_EMAIL;
				return false;
			}
			else if ($ucresult == -5) {
				$this->error = ERR_INVALID_EMAIL;
				return false;
			}
			else if ($ucresult == -6) {
				$this->error = ERR_EMAIL_EXISTS;
				return false;
			}
			else if ($ucresult < 0) {
				return false;
			}
		}

		if (!empty($cfg['old_password']) && !empty($cfg['password']) && ($forget_pwd == 0)) {
			$ucresult = uc_call('uc_user_edit', array($real_username, $cfg['old_password'], $cfg['password'], ''));

			if (0 < $ucresult) {
				return true;
			}
			else {
				$this->error = ERR_INVALID_PASSWORD;
				return false;
			}
		}
		else {
			if (!empty($cfg['password']) && ($forget_pwd == 1)) {
				$ucresult = uc_call('uc_user_edit', array($real_username, '', $cfg['password'], '', '1'));

				if (0 < $ucresult) {
					$flag = true;
				}
			}
		}

		return true;
	}

	public function get_profile_by_name($username)
	{
		$sql = 'SELECT user_id, user_name, email, sex, reg_time FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_name=\'' . $username . '\'';
		$row = $this->db->getRow($sql);
		return $row;
	}

	public function check_cookie()
	{
		return '';
	}

	public function get_cookie()
	{
		$id = $this->check_cookie();

		if ($id) {
			if ($this->need_sync) {
				$this->sync($id);
			}

			$this->set_session($id);
			return true;
		}
		else {
			return false;
		}
	}

	public function set_cookie($username = '', $remember = NULL)
	{
		if (empty($username)) {
			cookie('ECS[user_id]', NULL);
			cookie('ECS[password]', NULL);
		}
		else {
			$time = 3600 * 24 * 30;
			cookie('ECS[username]', stripslashes($username));
			$sql = 'SELECT user_id, password FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_name=\'' . $username . '\' LIMIT 1';
			$row = $GLOBALS['db']->getRow($sql);

			if ($row) {
				cookie('ECS[user_id]', $row['user_id'], $time);
				cookie('ECS[password]', $row['password'], $time);
			}
		}
	}

	public function set_session($username = '')
	{
		if (empty($username)) {
			session('[destroy]');
		}
		else {
			$sql = 'SELECT user_id, password, email FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_name=\'' . $username . '\' LIMIT 1';
			$row = $GLOBALS['db']->getRow($sql);

			if ($row) {
				$_SESSION['user_id'] = $row['user_id'];
				$_SESSION['user_name'] = $username;
				$_SESSION['email'] = $row['email'];
			}
		}
	}

	public function get_profile_by_id($id)
	{
		$sql = 'SELECT user_id, user_name, email, sex, birthday, reg_time FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id=\'' . $id . '\'';
		$row = $this->db->getRow($sql);
		return $row;
	}

	public function get_user_info($username)
	{
		return $this->get_profile_by_name($username);
	}

	public function remove_user($id)
	{
		if (is_array($id)) {
			$post_id = array();

			foreach ($id as $val) {
				$post_id[] = $val;
			}
		}
		else {
			$post_id = $id;
		}

		$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE ';
		$sql .= (is_array($post_id) ? db_create_in($post_id, 'user_name') : 'user_name=\'' . $post_id . '\' LIMIT 1');
		$col = $GLOBALS['db']->getCol($sql);

		if ($col) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET parent_id = 0 WHERE ' . db_create_in($col, 'parent_id');
			$GLOBALS['db']->query($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE ' . db_create_in($col, 'user_id');
			$GLOBALS['db']->query($sql);
			$sql = 'SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE ' . db_create_in($col, 'user_id');
			$GLOBALS['db']->query($sql);
			$col_order_id = $GLOBALS['db']->getCol($sql);

			if ($col_order_id) {
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE ' . db_create_in($col_order_id, 'order_id');
				$GLOBALS['db']->query($sql);
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE ' . db_create_in($col_order_id, 'order_id');
				$GLOBALS['db']->query($sql);
			}

			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('booking_goods') . ' WHERE ' . db_create_in($col, 'user_id');
			$GLOBALS['db']->query($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('collect_goods') . ' WHERE ' . db_create_in($col, 'user_id');
			$GLOBALS['db']->query($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('feedback') . ' WHERE ' . db_create_in($col, 'user_id');
			$GLOBALS['db']->query($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('user_address') . ' WHERE ' . db_create_in($col, 'user_id');
			$GLOBALS['db']->query($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' WHERE ' . db_create_in($col, 'user_id');
			$GLOBALS['db']->query($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('user_account') . ' WHERE ' . db_create_in($col, 'user_id');
			$GLOBALS['db']->query($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('tag') . ' WHERE ' . db_create_in($col, 'user_id');
			$GLOBALS['db']->query($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('account_log') . ' WHERE ' . db_create_in($col, 'user_id');
			$GLOBALS['db']->query($sql);
		}

		if (isset($this->ecshop) && $this->ecshop) {
			return NULL;
		}

		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE ';

		if (is_array($post_id)) {
			$sql .= db_create_in($post_id, 'user_name');
		}
		else {
			$sql .= 'user_name=\'' . $post_id . '\' LIMIT 1';
		}

		$this->db->query($sql);
	}

	public function get_points_name()
	{
		return 'ucenter';
	}
}

?>
