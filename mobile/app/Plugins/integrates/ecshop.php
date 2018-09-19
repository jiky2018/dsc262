<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
defined('IN_ECTOUCH') || exit('Deny Access');
class ecshop extends \App\Libraries\Integrate
{
	private $is_ecshop = 1;

	public function __construct($cfg)
	{
		parent::__construct(array());
		$this->user_table = 'users';
		$this->field_id = 'user_id';
		$this->ec_salt = 'ec_salt';
		$this->field_name = 'user_name';
		$this->field_pass = 'password';
		$this->field_email = 'email';
		$this->field_gender = 'sex';
		$this->field_bday = 'birthday';
		$this->field_reg_date = 'reg_time';
		$this->need_sync = false;
		$this->is_ecshop = 1;
	}

	public function check_user($username, $password = NULL)
	{
		if ($this->charset != 'UTF8') {
			$post_username = ecs_iconv('UTF8', $this->charset, $username);
		}
		else {
			$post_username = $username;
		}

		if ($password === NULL) {
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table($this->user_table) . ' WHERE ' . $this->field_name . '=\'' . $post_username . '\'';
			return $this->db->getOne($sql);
		}
		else {
			$sql = 'SELECT user_id, password, salt,ec_salt ' . ' FROM ' . $this->table($this->user_table) . ' WHERE user_name=\'' . $post_username . '\'';
			$row = $this->db->getRow($sql);
			$ec_salt = $row['ec_salt'];

			if (empty($row)) {
				return 0;
			}

			if (empty($row['salt'])) {
				if ($row['password'] != $this->compile_password(array('password' => $password, 'ec_salt' => $ec_salt))) {
					return 0;
				}
				else {
					if (empty($ec_salt)) {
						$ec_salt = rand(1, 9999);
						$new_password = md5(md5($password) . $ec_salt);
						$sql = 'UPDATE ' . $this->table($this->user_table) . 'SET password= \'' . $new_password . '\',ec_salt=\'' . $ec_salt . '\'' . ' WHERE user_name=\'' . $post_username . '\'';
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

				if ($row['password'] != $encrypt_password) {
					return 0;
				}

				$sql = 'UPDATE ' . $this->table($this->user_table) . ' SET password = \'' . $this->compile_password(array('password' => $password)) . '\', salt=\'\'' . ' WHERE user_id = \'' . $row['user_id'] . '\'';
				$this->db->query($sql);
				return $row['user_id'];
			}
		}
	}
}

?>
