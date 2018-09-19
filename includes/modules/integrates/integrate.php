<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class integrate
{
	public $db_host = '';
	public $db_name = '';
	public $db_user = '';
	public $db_pass = '';
	public $prefix = '';
	public $charset = '';
	public $cookie_domain = '';
	public $cookie_path = '/';
	public $user_table = '';
	public $field_id = '';
	public $field_name = '';
	public $field_pass = '';
	public $field_email = '';
	public $field_phone = '';
	public $field_gender = '';
	public $field_bday = '';
	public $field_reg_date = '';
	public $need_sync = true;
	public $error = 0;
	public $db;

	public function __construct($cfg)
	{
		$this->charset = isset($cfg['db_charset']) ? $cfg['db_charset'] : 'UTF8';
		$this->prefix = isset($cfg['prefix']) ? $cfg['prefix'] : '';
		$this->db_name = isset($cfg['db_name']) ? $cfg['db_name'] : '';
		$this->cookie_domain = isset($cfg['cookie_domain']) ? $cfg['cookie_domain'] : '';
		$this->cookie_path = isset($cfg['cookie_path']) ? $cfg['cookie_path'] : '/';
		$this->need_sync = true;
		$quiet = empty($cfg['quiet']) ? 0 : 1;

		if (empty($cfg['db_host'])) {
			$this->db_name = $GLOBALS['ecs']->db_name;
			$this->prefix = $GLOBALS['ecs']->prefix;
			$this->db = &$GLOBALS['db'];
		}
		else if (empty($cfg['is_latin1'])) {
			$this->db = new cls_mysql($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name'], $this->charset, NULL, $quiet);
		}
		else {
			$this->db = new cls_mysql($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name'], 'latin1', NULL, $quiet);
		}

		if (!is_resource($this->db->link_id)) {
			$this->error = 1;
		}
		else {
			$this->error = $this->db->errno();
		}
	}

	public function login($username, $password, $remember = NULL, $is_oath = 1)
	{
		if (0 < $this->check_user($username, $password)) {
			if ($this->need_sync) {
				$this->sync($username, $password);
			}

			if ($is_oath == 1) {
				$this->set_session($username);
				$this->set_cookie($username, $remember);
			}

			return true;
		}
		else {
			return false;
		}
	}

	public function logout()
	{
		$this->set_cookie();
		$this->set_session();
	}

	public function add_user($username, $password, $registerMode_info, $gender = -1, $bday = 0, $reg_date = 0, $md5password = '')
	{
		if (0 < $this->check_user($username)) {
			$this->error = ERR_USERNAME_EXISTS;
			return false;
		}

		if (empty($registerMode_info['register_mode'])) {
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $this->field_email . (' = \'' . $registerMode_info['email'] . '\'');

			if (0 < $this->db->getOne($sql, true)) {
				$this->error = ERR_EMAIL_EXISTS;
				return false;
			}
		}
		else {
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $this->field_phone . (' = \'' . $registerMode_info['mobile_phone'] . '\'');

			if (0 < $this->db->getOne($sql, true)) {
				$this->error = ERR_PHONE_EXISTS;
				return false;
			}
		}

		$post_username = $username;

		if ($md5password) {
			$post_password = $this->compile_password(array('md5password' => $md5password));
		}
		else {
			$post_password = $this->compile_password(array('password' => $password));
		}

		$fields = array($this->field_name, $this->field_phone, $this->field_email, $this->field_pass);
		$values = array($post_username, $registerMode_info['mobile_phone'], $registerMode_info['email'], $post_password);

		if (-1 < $gender) {
			$fields[] = $this->field_gender;
			$values[] = $gender;
		}

		if ($bday) {
			$fields[] = $this->field_bday;
			$values[] = $bday;
		}

		if ($reg_date) {
			$fields[] = $this->field_reg_date;
			$values[] = $reg_date;
		}

		$sql = 'INSERT INTO ' . $this->table($this->user_table) . ' (' . implode(',', $fields) . ')' . ' VALUES (\'' . implode('\', \'', $values) . '\')';
		$this->db->query($sql);

		if ($this->need_sync) {
			$this->sync($username, $password);
		}

		return true;
	}

	public function edit_user($cfg, $forget_pwd = '0')
	{
		if (empty($cfg['username'])) {
			return false;
		}
		else {
			$cfg['post_username'] = $cfg['username'];
		}

		$values = array();
		if (!empty($cfg['password']) && empty($cfg['md5password'])) {
			$cfg['md5password'] = md5($cfg['password']);
		}

		if (!empty($cfg['md5password']) && $this->field_pass != 'NULL') {
			$values[] = $this->field_pass . '=\'' . $this->compile_password(array('md5password' => $cfg['md5password'])) . '\'';
		}

		if (!empty($cfg['email']) && $this->field_email != 'NULL') {
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $this->field_email . (' = \'' . $cfg['email'] . '\' ') . ' AND ' . $this->field_id . (' != \'' . $cfg['user_id'] . '\'');

			if (0 < $this->db->getOne($sql, true)) {
				$this->error = ERR_EMAIL_EXISTS;
				return false;
			}

			$sql = 'SELECT count(*)' . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $this->field_email . (' = \'' . $cfg['email'] . '\' ');

			if ($this->db->getOne($sql, true) == 0) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET is_validated = 0 WHERE user_name = \'' . $cfg['post_username'] . '\'');
				$this->db->query($sql);
			}

			$values[] = $this->field_email . '=\'' . $cfg['email'] . '\'';
		}

		if (!empty($cfg['mobile_phone']) && $this->field_phone != 'NULL') {
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $this->field_phone . (' = \'' . $cfg['mobile_phone'] . '\' ') . ' AND ' . $this->field_id . (' != \'' . $cfg['user_id'] . '\'');

			if (0 < $this->db->getOne($sql, true)) {
				$this->error = ERR_PHONE_EXISTS;
				return false;
			}

			$values[] = $this->field_phone . '=\'' . $cfg['mobile_phone'] . '\'';
		}

		if (isset($cfg['gender']) && $this->field_gender != 'NULL') {
			$values[] = $this->field_gender . '=\'' . $cfg['gender'] . '\'';
		}

		if (!empty($cfg['bday']) && $this->field_bday != 'NULL') {
			$values[] = $this->field_bday . '=\'' . $cfg['bday'] . '\'';
		}

		if ($values) {
			$sql = 'UPDATE ' . $this->table($this->user_table) . ' SET ' . implode(', ', $values) . ' WHERE ' . $this->field_id . '=\'' . $cfg['user_id'] . '\' LIMIT 1';
			$this->db->query($sql);

			if ($this->need_sync) {
				if (empty($cfg['md5password'])) {
					$this->sync($cfg['username']);
				}
				else {
					$this->sync($cfg['username'], '', $cfg['md5password']);
				}
			}
		}

		return true;
	}

	public function remove_user($id)
	{
		$post_id = $id;
		if ($this->need_sync || isset($this->is_ecshop) && $this->is_ecshop) {
			$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE ';
			$sql .= is_array($post_id) ? db_create_in($post_id, 'user_name') : 'user_name=\'' . $post_id . '\' LIMIT 1';
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
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('users_auth') . ' WHERE ' . db_create_in($col, 'user_id');
				$GLOBALS['db']->query($sql);
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('connect_user') . ' WHERE ' . db_create_in($col, 'user_id');
				$GLOBALS['db']->query($sql);

				if (file_exists(MOBILE_WECHAT)) {
					$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('wechat_user') . ' WHERE ' . db_create_in($col, 'ect_uid');
					$GLOBALS['db']->query($sql);
				}

				if (file_exists(MOBILE_DRP)) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET drp_parent_id = 0 WHERE ' . db_create_in($col, 'drp_parent_id');
					$GLOBALS['db']->query($sql);
					$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('drp_affiliate_log') . ' WHERE ' . db_create_in($col, 'user_id');
					$GLOBALS['db']->query($sql);
					$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('drp_log') . ' WHERE ' . db_create_in($col, 'user_id');
					$GLOBALS['db']->query($sql);
					$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('drp_shop') . ' WHERE ' . db_create_in($col, 'user_id');
					$GLOBALS['db']->query($sql);
					$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('drp_type') . ' WHERE ' . db_create_in($col, 'user_id');
					$GLOBALS['db']->query($sql);
					$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('drp_transfer_log') . ' WHERE ' . db_create_in($col, 'user_id');
					$GLOBALS['db']->query($sql);
				}
			}
		}

		if (isset($this->ecshop) && $this->ecshop) {
			return NULL;
		}

		$sql = 'DELETE FROM ' . $this->table($this->user_table) . ' WHERE ';

		if (is_array($post_id)) {
			$sql .= db_create_in($post_id, $this->field_name);
		}
		else {
			$sql .= $this->field_name . '=\'' . $post_id . '\' LIMIT 1';
		}

		$this->db->query($sql);
	}

	public function get_profile_by_name($username)
	{
		$post_username = $username;
		$sql = 'SELECT ' . $this->field_id . ' AS user_id,' . $this->field_name . ' AS user_name,' . $this->field_email . ' AS email,' . $this->field_gender . ' AS sex,' . $this->field_bday . ' AS birthday,' . $this->field_reg_date . ' AS reg_time, ' . $this->field_pass . ' AS password ' . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $this->field_name . ('=\'' . $post_username . '\'');
		$row = $this->db->getRow($sql);
		return $row;
	}

	public function get_profile_by_id($id)
	{
		$sql = 'SELECT ' . $this->field_id . ' AS user_id,' . $this->field_name . ' AS user_name,' . $this->field_email . ' AS email,' . $this->field_gender . ' AS sex,' . $this->field_bday . ' AS birthday,' . $this->field_reg_date . ' AS reg_time, ' . $this->field_pass . ' AS password ' . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $this->field_id . ('=\'' . $id . '\'');
		$row = $this->db->getRow($sql);
		return $row;
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

	public function check_user($username, $password = '')
	{
		$is_email = get_is_email($username);
		$is_phone = get_is_phone($username);
		$is_name = 0;

		if ($is_email) {
			$field_name = $this->field_email . (' = \'' . $username . '\'');
		}
		else if ($is_phone) {
			$is_name = 1;
			$field_name = $this->field_phone . (' = \'' . $username . '\'');
		}
		else {
			$field_name = $this->field_name . (' = \'' . $username . '\'');
		}

		$row = $this->check_field_name($field_name);

		if (empty($row)) {
			if ($is_name == 1) {
				$field = $this->field_name . (' = \'' . $username . '\'');
				$row = $this->check_field_name($field);

				if ($row) {
					$field_name = $this->field_name . (' = \'' . $username . '\'');
				}
				else {
					$field_name = $this->field_phone . (' = \'' . $username . '\'');
				}
			}
		}

		$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $field_name . ' AND ' . $this->field_pass . ' =\'' . $this->compile_password(array('password' => $password)) . '\'';
		return $this->db->getOne($sql);
	}

	public function check_email($email)
	{
		if (!empty($email)) {
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $this->field_email . (' = \'' . $email . '\' ');

			if (0 < $this->db->getOne($sql, true)) {
				$this->error = ERR_EMAIL_EXISTS;
				return true;
			}

			return false;
		}
	}

	public function check_mobile_phone($phone)
	{
		if (!empty($phone)) {
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $this->field_phone . (' = \'' . $phone . '\' ');

			if (0 < $this->db->getOne($sql, true)) {
				$this->error = ERR_PHONE_EXISTS;
				return true;
			}

			return false;
		}
	}

	public function check_cookie()
	{
		return '';
	}

	public function set_cookie($username = '', $remember = NULL, $is_oath = 1, $unionid = '')
	{
		$where = '';
		$select = '';
		$leftJoin = '';

		if ($unionid) {
			$where = ' conu.open_id = \'' . $unionid . '\'';
			$select = ', conu.open_id';
			$leftJoin = ' LEFT JOIN ' . $GLOBALS['ecs']->table('connect_user') . ' AS conu ON u.user_id = conu.user_id';
		}

		if (empty($username)) {
			$time = gmtime() - 3600;
			setcookie('ECS[user_id]', '', $time, $this->cookie_path, $this->cookie_domain);
			setcookie('ECS[password]', '', $time, $this->cookie_path, $this->cookie_domain);
		}
		else if ($remember) {
			if ($is_oath == 1) {
				$time = gmtime() + 3600 * 24 * 15;
				$is_email = get_is_email($username);
				$is_phone = get_is_phone($username);

				if ($is_email) {
					$field_name = 'u.email = \'' . $username . '\'';
				}
				else if ($is_phone) {
					$field_name = 'u.mobile_phone = \'' . $username . '\'';
				}
				else {
					$field_name = 'u.user_name = \'' . $username . '\'';
				}

				$row = $this->check_field_name($field_name, 'u');

				if (empty($row)) {
					if ($is_name == 1) {
						$field = 'u.user_name = \'' . $username . '\'';
						$row = $this->check_field_name($field, 'u');

						if ($row) {
							$field_name = 'u.user_name = \'' . $username . '\'';
						}
						else {
							$field_name = 'u.mobile_phone = \'' . $username . '\'';
						}
					}
				}

				if (empty($unionid)) {
					$where = $field_name;
				}

				$sql = 'SELECT u.user_id, u.user_name, u.password, u.email ' . $select . ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . $leftJoin . (' WHERE ' . $where . ' LIMIT 1');
				$row = $GLOBALS['db']->getRow($sql);
			}
			else {
				if (empty($unionid)) {
					$where = ' u.user_name = \'' . $username . '\'';
				}

				$sql = 'SELECT u.user_id, u.user_name, u.password, u.email ' . $select . ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . $leftJoin . (' WHERE ' . $where . ' LIMIT 1');
				$row = $GLOBALS['db']->getRow($sql);
			}

			if ($row) {
				setcookie('ECS[username]', $row['user_name'], $time, $this->cookie_path, $this->cookie_domain);
				setcookie('ECS[user_id]', $row['user_id'], $time, $this->cookie_path, $this->cookie_domain);
				setcookie('ECS[password]', $row['password'], $time, $this->cookie_path, $this->cookie_domain);
			}
		}
	}

	public function set_session($username = '', $is_oath = 1, $unionid = '')
	{
		$where = '';
		$select = '';
		$leftJoin = '';

		if ($unionid) {
			$where = ' conu.open_id = \'' . $unionid . '\'';
			$select = ', conu.open_id';
			$leftJoin = ' LEFT JOIN ' . $GLOBALS['ecs']->table('connect_user') . ' AS conu ON u.user_id = conu.user_id';
		}

		if (empty($username)) {
			$GLOBALS['sess']->destroy_session();
		}
		else {
			if ($is_oath == 1) {
				$is_email = get_is_email($username);
				$is_phone = get_is_phone($username);
				$is_name = 0;

				if ($is_email) {
					$field_name = 'u.email = \'' . $username . '\'';
				}
				else if ($is_phone) {
					$is_name = 1;
					$field_name = 'u.mobile_phone = \'' . $username . '\'';
				}
				else {
					$field_name = 'u.user_name = \'' . $username . '\'';
				}

				$row = $this->check_field_name($field_name, 'u');

				if (empty($row)) {
					if ($is_name == 1) {
						$field = 'u.user_name = \'' . $username . '\'';
						$row = $this->check_field_name($field, 'u');

						if ($row) {
							$field_name = 'u.user_name = \'' . $username . '\'';
						}
						else {
							$field_name = 'u.mobile_phone = \'' . $username . '\'';
						}
					}
				}

				if (empty($unionid)) {
					$where = $field_name;
				}

				$sql = 'SELECT u.user_id, u.user_name, u.password, u.email ' . $select . ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . $leftJoin . (' WHERE ' . $where . ' LIMIT 1');
				$row = $GLOBALS['db']->getRow($sql);
			}
			else {
				if (empty($unionid)) {
					$where = ' u.user_name = \'' . $username . '\'';
				}

				$sql = 'SELECT u.user_id, u.user_name, u.password, u.email ' . $select . ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . $leftJoin . (' WHERE ' . $where . ' LIMIT 1');
				$row = $GLOBALS['db']->getRow($sql);
			}

			if ($row) {
				$_SESSION['user_id'] = $row['user_id'];
				$_SESSION['user_name'] = $row['user_name'];
				$_SESSION['email'] = $row['email'];
			}
		}
	}

	public function table($str)
	{
		return '`' . $this->db_name . '`.`' . $this->prefix . $str . '`';
	}

	public function compile_password($cfg)
	{
		if (isset($cfg['password'])) {
			$cfg['md5password'] = md5($cfg['password']);
		}

		if (empty($cfg['type'])) {
			$cfg['type'] = PWD_MD5;
		}

		switch ($cfg['type']) {
		case PWD_MD5:
			if (!empty($cfg['ec_salt'])) {
				return md5($cfg['md5password'] . $cfg['ec_salt']);
			}
			else {
				return $cfg['md5password'];
			}
		case PWD_PRE_SALT:
			if (empty($cfg['salt'])) {
				$cfg['salt'] = '';
			}

			return md5($cfg['salt'] . $cfg['md5password']);
		case PWD_SUF_SALT:
			if (empty($cfg['salt'])) {
				$cfg['salt'] = '';
			}

			return md5($cfg['md5password'] . $cfg['salt']);
		default:
			return '';
		}
	}

	public function sync($username, $password = '', $md5password = '')
	{
		if (!empty($password) && empty($md5password)) {
			$md5password = md5($password);
		}

		$main_profile = $this->get_profile_by_name($username);

		if (empty($main_profile)) {
			return false;
		}

		$sql = 'SELECT user_name, email, password, sex, birthday' . ' FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_name = \'' . $username . '\'');
		$profile = $GLOBALS['db']->getRow($sql);

		if (empty($profile)) {
			if (empty($md5password)) {
				$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('users') . '(user_name, email, sex, birthday, reg_time)' . (' VALUES(\'' . $username . '\', \'') . $main_profile['email'] . '\',\'' . $main_profile['sex'] . '\',\'' . $main_profile['birthday'] . '\',\'' . $main_profile['reg_time'] . '\')';
			}
			else {
				$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('users') . '(user_name, email, sex, birthday, reg_time, password)' . (' VALUES(\'' . $username . '\', \'') . $main_profile['email'] . '\',\'' . $main_profile['sex'] . '\',\'' . $main_profile['birthday'] . '\',\'' . $main_profile['reg_time'] . ('\', \'' . $md5password . '\')');
			}

			$GLOBALS['db']->query($sql);
			return true;
		}
		else {
			$values = array();

			if ($main_profile['email'] != $profile['email']) {
				$values[] = 'email=\'' . $main_profile['email'] . '\'';
			}

			if ($main_profile['sex'] != $profile['sex']) {
				$values[] = 'sex=\'' . $main_profile['sex'] . '\'';
			}

			if ($main_profile['birthday'] != $profile['birthday']) {
				$values[] = 'birthday=\'' . $main_profile['birthday'] . '\'';
			}

			if (!empty($md5password) && $md5password != $profile['password']) {
				$values[] = 'password=\'' . $md5password . '\'';
			}

			if (empty($values)) {
				return true;
			}
			else {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET ' . implode(', ', $values) . (' WHERE user_name=\'' . $username . '\'');
				$GLOBALS['db']->query($sql);
				return true;
			}
		}
	}

	public function get_points_name()
	{
		return array();
	}

	public function get_points($username)
	{
		$credits = $this->get_points_name();
		$fileds = array_keys($credits);

		if ($fileds) {
			$sql = 'SELECT ' . $this->field_id . ', ' . implode(', ', $fileds) . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $this->field_name . ('=\'' . $username . '\'');
			$row = $this->db->getRow($sql);
			return $row;
		}
		else {
			return false;
		}
	}

	public function set_points($username, $credits)
	{
		$user_set = array_keys($credits);
		$points_set = array_keys($this->get_points_name());
		$set = array_intersect($user_set, $points_set);

		if ($set) {
			$tmp = array();

			foreach ($set as $credit) {
				$tmp[] = $credit . '=' . $credit . '+' . $credits[$credit];
			}

			$sql = 'UPDATE ' . $this->table($this->user_table) . ' SET ' . implode(', ', $tmp) . ' WHERE ' . $this->field_name . (' = \'' . $username . '\'');
			$this->db->query($sql);
		}

		return true;
	}

	public function get_user_info($username)
	{
		return $this->get_profile_by_name($username);
	}

	public function test_conflict($user_list)
	{
		if (empty($user_list)) {
			return array();
		}

		$sql = 'SELECT ' . $this->field_name . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . db_create_in($user_list, $this->field_name);
		$user_list = $this->db->getCol($sql);
		return $user_list;
	}

	private function check_field_name($field_name, $alias = '')
	{
		if (!empty($alias)) {
			$as = ' AS ' . $alias;
			$alias = $alias . '.';
		}

		$sql = 'SELECT ' . $alias . 'user_id, ' . $alias . 'password, ' . $alias . 'salt, ' . $alias . 'ec_salt ' . ' FROM ' . $this->table($this->user_table) . $as . ' WHERE ' . $field_name;
		$row = $this->db->getRow($sql);
		return $row;
	}
}


?>
