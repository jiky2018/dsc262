<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class cls_session_memcached
{
	public $db;
	public $session_table = '';
	public $max_life_time = 1800;
	public $session_name = '';
	public $session_id = '';
	public $session_expiry = '';
	public $session_md5 = '';
	public $session_cookie_path = '/';
	public $session_cookie_domain = '';
	public $session_cookie_secure = false;
	public $_ip = '';
	public $_time = 0;

	public function __construct(&$db, $session_table, $session_data_table, $session_name = 'ECS_ID', $session_id = '')
	{
		$GLOBALS['_SESSION'] = array();

		if (!empty($GLOBALS['cookie_path'])) {
			$this->session_cookie_path = $GLOBALS['cookie_path'];
		}
		else {
			$this->session_cookie_path = '/';
		}

		if (!empty($GLOBALS['cookie_domain'])) {
			$this->session_cookie_domain = $GLOBALS['cookie_domain'];
		}
		else {
			$this->session_cookie_domain = '';
		}

		if (!empty($GLOBALS['cookie_secure'])) {
			$this->session_cookie_secure = $GLOBALS['cookie_secure'];
		}
		else {
			$this->session_cookie_secure = false;
		}

		$this->session_name = $session_name;
		$this->session_table = $session_table;
		$this->session_data_table = $session_data_table;
		$this->db = &$db;
		$this->cache = $GLOBALS['cache'];
		$this->_ip = real_ip();
		if (($session_id == '') && !empty($_COOKIE[$this->session_name])) {
			$this->session_id = $_COOKIE[$this->session_name];
		}
		else {
			$this->session_id = $session_id;
		}

		if ($this->session_id) {
			$tmp_session_id = substr($this->session_id, 0, 32);

			if ($this->gen_session_key($tmp_session_id) == substr($this->session_id, 32)) {
				$this->session_id = $tmp_session_id;
			}
			else {
				$this->session_id = '';
			}
		}

		$this->_time = time();

		if ($this->session_id) {
			$this->load_session();
		}
		else {
			$this->gen_session_id();
			setcookie($this->session_name, $this->session_id . $this->gen_session_key($this->session_id), 0, $this->session_cookie_path, $this->session_cookie_domain, $this->session_cookie_secure);
		}

		register_shutdown_function(array(&$this, 'close_session'));
	}

	public function gen_session_id()
	{
		$this->session_id = md5(uniqid(mt_rand(), true));
		return $this->insert_session();
	}

	public function gen_session_key($session_id)
	{
		static $ip = '';

		if ($ip == '') {
			$ip = substr($this->_ip, 0, strrpos($this->_ip, '.'));
		}

		return sprintf('%08x', crc32(ROOT_PATH . $ip . $session_id));
	}

	public function insert_session()
	{
		return $this->cache->set($this->session_id, array('expiry' => $this->_time, 'ip' => $this->_ip, 'data' => 'a:0:{}'), $this->max_life_time);
	}

	public function load_session()
	{
		$session = $this->cache->get($this->session_id);

		if (empty($session)) {
			$this->insert_session();
			$this->session_expiry = 0;
			$this->session_md5 = '40cd750bba9870f18aada2478b24840a';
			$GLOBALS['_SESSION'] = array();
		}
		else {
			if (!empty($session['data']) && (($this->_time - $session['expiry']) <= $this->max_life_time)) {
				$this->session_expiry = $session['expiry'];
				$this->session_md5 = md5($session['data']);
				$GLOBALS['_SESSION'] = unserialize(stripslashes($session['data']));
			}
			else {
				$this->session_expiry = 0;
				$this->session_md5 = '40cd750bba9870f18aada2478b24840a';
				$GLOBALS['_SESSION'] = array();
			}
		}
	}

	public function update_session()
	{
		$adminid = (!empty($GLOBALS['_SESSION']['admin_id']) ? intval($GLOBALS['_SESSION']['admin_id']) : 0);
		$userid = (!empty($GLOBALS['_SESSION']['user_id']) ? intval($GLOBALS['_SESSION']['user_id']) : 0);
		$data = serialize($GLOBALS['_SESSION']);
		$this->_time = time();
		if (($this->session_md5 == md5($data)) && ($this->_time < ($this->session_expiry + 10))) {
			return true;
		}

		$data = addslashes($data);
		return $this->cache->replace($this->session_id, array('expiry' => $this->_time, 'ip' => $this->_ip, 'userid' => $userid, 'adminid' => $adminid, 'data' => $data), $this->max_life_time);
	}

	public function close_session()
	{
		$this->update_session();
		return true;
	}

	public function delete_spec_admin_session($adminid)
	{
		if (!empty($GLOBALS['_SESSION']['admin_id']) && $adminid) {
			return true;
		}
		else {
			return false;
		}
	}

	public function destroy_session()
	{
		$GLOBALS['_SESSION'] = array();
		setcookie($this->session_name, $this->session_id, 1, $this->session_cookie_path, $this->session_cookie_domain, $this->session_cookie_secure);

		if (!empty($GLOBALS['ecs'])) {
			$this->db->query('DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE session_id = \'' . $this->session_id . '\'');
		}

		return $this->cache->rm($this->session_id);
	}

	public function get_session_id()
	{
		return $this->session_id;
	}

	public function get_users_count()
	{
		return 0;
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
