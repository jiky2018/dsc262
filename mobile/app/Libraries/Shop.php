<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Libraries;

class Shop
{
	public $db_name = '';
	public $prefix = 'ecs_';

	public function __construct($db_name, $prefix)
	{
		$this->db_name = $db_name;
		$this->prefix = $prefix;
	}

	public function table($str)
	{
		return '`' . $this->db_name . '`.`' . $this->prefix . $str . '`';
	}

	public function compile_password($pass)
	{
		return md5($pass);
	}

	public function get_domain()
	{
		$protocol = $this->http();

		if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
		}
		else if (isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		}
		else {
			if (isset($_SERVER['SERVER_PORT'])) {
				$port = ':' . $_SERVER['SERVER_PORT'];
				if (((':80' == $port) && ('http://' == $protocol)) || ((':443' == $port) && ('https://' == $protocol))) {
					$port = '';
				}
			}
			else {
				$port = '';
			}

			if (isset($_SERVER['SERVER_NAME'])) {
				$host = $_SERVER['SERVER_NAME'] . $port;
			}
			else if (isset($_SERVER['SERVER_ADDR'])) {
				$host = $_SERVER['SERVER_ADDR'] . $port;
			}
		}

		return $protocol . $host;
	}

	public function url()
	{
		$curr = (strpos(PHP_SELF, ADMIN_PATH . '/') !== false ? preg_replace('/(.*)(' . ADMIN_PATH . ')(\\/?)(.)*/i', '\\1', dirname(PHP_SELF)) : dirname(PHP_SELF));
		$root = str_replace('\\', '/', $curr);

		if (substr($root, -1) != '/') {
			$root .= '/';
		}

		return $this->get_domain() . $root;
	}

	public function http()
	{
		return isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off') ? 'https://' : 'http://';
	}

	public function data_dir($sid = 0)
	{
		if (empty($sid)) {
			$s = 'data';
		}
		else {
			$s = 'user_files/';
			$s .= ceil($sid / 3000) . '/';
			$s .= $sid % 3000;
		}

		return $s;
	}

	public function image_dir($sid = 0)
	{
		if (empty($sid)) {
			$s = 'images';
		}
		else {
			$s = 'user_files/';
			$s .= ceil($sid / 3000) . '/';
			$s .= ($sid % 3000) . '/';
			$s .= 'images';
		}

		return $s;
	}

	public function get_select_find_in_set($is_db = 0, $select_id, $select_array = array(), $where = '', $table = '', $id = '', $replace = '')
	{
		if ($replace) {
			$replace = 'REPLACE (' . $id . ',\'' . $replace . '\',\',\')';
		}
		else {
			$replace = $id;
		}

		if ($select_array && is_array($select_array)) {
			$select = implode(',', $select_array);
		}
		else {
			$select = '*';
		}

		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE find_in_set(\'' . $select_id . '\', ' . $replace . ') ' . $where;

		if ($is_db == 1) {
			return $GLOBALS['db']->getAll($sql);
		}
		else if ($is_db == 2) {
			return $GLOBALS['db']->getRow($sql);
		}
		else {
			return $GLOBALS['db']->getOne($sql, true);
		}
	}
}


?>
