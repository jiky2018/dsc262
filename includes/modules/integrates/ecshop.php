<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

if (isset($set_modules) && $set_modules == true) {
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['code'] = 'ecshop';
	$modules[$i]['name'] = 'ECSHOP';
	$modules[$i]['version'] = '2.0';
	$modules[$i]['author'] = 'ECMOBAN R&D TEAM';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	return NULL;
}

require_once ROOT_PATH . 'includes/modules/integrates/integrate.php';
class ecshop extends integrate
{
	public $is_ecshop = 1;

	public function __construct($cfg)
	{
		parent::__construct(array());
		$this->user_table = 'users';
		$this->field_id = 'user_id';
		$this->ec_salt = 'ec_salt';
		$this->field_name = 'user_name';
		$this->field_pass = 'password';
		$this->field_email = 'email';
		$this->field_phone = 'mobile_phone';
		$this->field_gender = 'sex';
		$this->field_bday = 'birthday';
		$this->field_reg_date = 'reg_time';
		$this->need_sync = false;
		$this->is_ecshop = 1;
	}

	public function check_user($username, $password = '')
	{
		if ($this->charset != 'UTF8') {
			$username = ecs_iconv('UTF8', $this->charset, $username);
		}

		$is_email = get_is_email($username);
		$is_phone = get_is_phone($username);
		$is_name = 0;

		if ($is_email) {
			$field_name = 'email = \'' . $username . '\'';
		}
		else if ($is_phone) {
			$is_name = 1;
			$field_name = 'mobile_phone = \'' . $username . '\'';
		}
		else {
			$field_name = 'user_name = \'' . $username . '\'';
		}

		$row = $this->check_field_name($field_name);

		if (empty($row)) {
			if ($is_name == 1) {
				$field = 'user_name = \'' . $username . '\'';
				$row = $this->check_field_name($field);

				if (empty($row)) {
					return 0;
				}
			}
			else {
				return 0;
			}
		}

		if (empty($row['salt'])) {
			if (!empty($password) && $row['password'] != $this->compile_password(array('password' => $password, 'ec_salt' => $row['ec_salt']))) {
				return 0;
			}
			else {
				if (empty($row['ec_salt'])) {
					$ec_salt = rand(1, 9999);
					$new_password = md5(md5($password) . $ec_salt);
					$sql = 'UPDATE ' . $this->table($this->user_table) . 'SET password = \'' . $new_password . '\',ec_salt = \'' . $ec_salt . '\'' . ' WHERE user_id = \'' . $row['user_id'] . '\'';
					$this->db->query($sql);
				}

				return $row['user_id'];
			}
		}
		else {
			$encrypt_type = substr($row['salt'], 0, 1);
			$encrypt_salt = substr($row['salt'], 1);
			$encrypt_password = '';

			switch ($encrypt_type) {
			case ENCRYPT_ZC:
				$encrypt_password = md5($encrypt_salt . $password);
				break;

			case ENCRYPT_UC:
				$encrypt_password = md5(md5($password) . $encrypt_salt);
				break;

			default:
				$encrypt_password = '';
			}

			if (!empty($password) && $row['password'] != $encrypt_password) {
				return 0;
			}

			$sql = 'UPDATE ' . $this->table($this->user_table) . ' SET password = \'' . $this->compile_password(array('password' => $password)) . '\', salt=\'\'' . (' WHERE user_id = \'' . $row['user_id'] . '\'');
			$this->db->query($sql);
			return $row['user_id'];
		}
	}

	private function check_field_name($field_name, $alias = '')
	{
		if (!empty($alias)) {
			$as = ' AS ' . $alias;
			$alias = $alias . '.';
		}
		else {
			$as = '';
		}

		$sql = 'SELECT ' . $alias . 'user_id, ' . $alias . 'password, ' . $alias . 'salt, ' . $alias . 'ec_salt ' . ' FROM ' . $this->table($this->user_table) . $as . ' WHERE ' . $field_name;
		$row = $this->db->getRow($sql);
		return $row;
	}
}

?>
