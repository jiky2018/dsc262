<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class cls_sql_dump
{
	public $max_size = 2097152;
	public $is_short = false;
	public $offset = 300;
	public $dump_sql = '';
	public $sql_num = 0;
	public $error_msg = '';
	public $db;

	public function __construct(&$db = '', $max_size = 0)
	{
		$this->cls_sql_dump($db, $max_size);
	}

	public function cls_sql_dump(&$db, $max_size = 0)
	{
		$this->db = &$db;

		if (0 < $max_size) {
			$this->max_size = $max_size;
		}
	}

	public function get_table_df($table, $add_drop = false)
	{
		if ($add_drop) {
			$table_df = 'DROP TABLE IF EXISTS `' . $table . "`;\r\n";
		}
		else {
			$table_df = '';
		}

		$tmp_arr = $this->db->getRow('SHOW CREATE TABLE `' . $table . '`');
		$tmp_sql = $tmp_arr['Create Table'];
		$tmp_sql = substr($tmp_sql, 0, strrpos($tmp_sql, ')') + 1);

		if ('4.1' <= $this->db->version()) {
			$table_df .= $tmp_sql . ' ENGINE=MyISAM DEFAULT CHARSET=' . str_replace('-', '', EC_CHARSET) . ";\r\n";
		}
		else {
			$table_df .= $tmp_sql . " TYPE=MyISAM;\r\n";
		}

		return $table_df;
	}

	public function get_table_data($table, $pos)
	{
		$post_pos = $pos;
		$total = $this->db->getOne('SELECT COUNT(*) FROM ' . $table);
		if ($total == 0 || $total <= $pos) {
			return -1;
		}

		$cycle_time = ceil(($total - $pos) / $this->offset);

		for ($i = 0; $i < $cycle_time; $i++) {
			$data = $this->db->getAll('SELECT * FROM ' . $table . ' LIMIT ' . ($this->offset * $i + $pos) . ', ' . $this->offset);
			$data_count = count($data);
			$fields = array_keys($data[0]);
			$start_sql = 'INSERT INTO `' . $table . '` ( `' . implode('`, `', $fields) . '` ) VALUES ';

			for ($j = 0; $j < $data_count; $j++) {
				$record = array_map('dump_escape_string', $data[$j]);
				$record = array_map('dump_null_string', $record);

				if ($this->is_short) {
					if ($post_pos == $total - 1) {
						$tmp_dump_sql = ' ( \'' . implode('\', \'', $record) . "' );\r\n";
					}
					else if ($j == $data_count - 1) {
						$tmp_dump_sql = ' ( \'' . implode('\', \'', $record) . "' );\r\n";
					}
					else {
						$tmp_dump_sql = ' ( \'' . implode('\', \'', $record) . "' ),\r\n";
					}

					if ($post_pos == $pos) {
						$tmp_dump_sql = $start_sql . "\r\n" . $tmp_dump_sql;
					}
					else if ($j == 0) {
						$tmp_dump_sql = $start_sql . "\r\n" . $tmp_dump_sql;
					}
				}
				else {
					$tmp_dump_sql = $start_sql . ' (\'' . implode('\', \'', $record) . "');\r\n";
				}

				$tmp_str_pos = strpos($tmp_dump_sql, 'NULL');
				$tmp_dump_sql = empty($tmp_str_pos) ? $tmp_dump_sql : substr($tmp_dump_sql, 0, $tmp_str_pos - 1) . 'NULL' . substr($tmp_dump_sql, $tmp_str_pos + 5);

				if ($this->max_size - 32 < strlen($this->dump_sql) + strlen($tmp_dump_sql)) {
					if ($this->sql_num == 0) {
						$this->dump_sql .= $tmp_dump_sql;
						$this->sql_num++;
						$post_pos++;

						if ($post_pos == $total) {
							return -1;
						}
					}

					return $post_pos;
				}
				else {
					$this->dump_sql .= $tmp_dump_sql;
					$this->sql_num++;
					$post_pos++;
				}
			}
		}

		return -1;
	}

	public function dump_table($path, $vol)
	{
		$tables = $this->get_tables_list($path);

		if ($tables === false) {
			return false;
		}

		if (empty($tables)) {
			return $tables;
		}

		$this->dump_sql = $this->make_head($vol);

		foreach ($tables as $table => $pos) {
			if ($pos == -1) {
				$table_df = $this->get_table_df($table, true);

				if ($this->max_size - 32 < strlen($this->dump_sql) + strlen($table_df)) {
					if ($this->sql_num == 0) {
						$this->dump_sql .= $table_df;
						$this->sql_num += 2;
						$tables[$table] = 0;
					}

					break;
				}
				else {
					$this->dump_sql .= $table_df;
					$this->sql_num += 2;
					$pos = 0;
				}
			}

			$post_pos = $this->get_table_data($table, $pos);

			if ($post_pos == -1) {
				unset($tables[$table]);
			}
			else {
				$tables[$table] = $post_pos;
				break;
			}
		}

		$this->dump_sql .= '-- END ecshop v2.x SQL Dump Program ';
		$this->put_tables_list($path, $tables);
		return $tables;
	}

	public function make_head($vol)
	{
		$sys_info['os'] = PHP_OS;
		$sys_info['web_server'] = $GLOBALS['ecs']->get_domain();
		$sys_info['php_ver'] = PHP_VERSION;
		$sys_info['mysql_ver'] = $this->db->version();
		$sys_info['date'] = date('Y-m-d H:i:s');
		$head = "-- ecshop v2.x SQL Dump Program\r\n" . '-- ' . $sys_info['web_server'] . "\r\n" . "-- \r\n" . '-- DATE : ' . $sys_info['date'] . "\r\n" . '-- MYSQL SERVER VERSION : ' . $sys_info['mysql_ver'] . "\r\n" . '-- PHP VERSION : ' . $sys_info['php_ver'] . "\r\n" . '-- ECShop VERSION : ' . VERSION . "\r\n" . '-- Vol : ' . $vol . "\r\n";
		return $head;
	}

	static public function get_head($path)
	{
		$sql_info = array('date' => '', 'mysql_ver' => '', 'php_ver' => 0, 'ecs_ver' => '', 'vol' => 0);
		$fp = fopen($path, 'rb');
		$str = fread($fp, 250);
		fclose($fp);
		$arr = explode("\n", $str);

		foreach ($arr as $val) {
			$pos = strpos($val, ':');

			if (0 < $pos) {
				$type = trim(substr($val, 0, $pos), "-\n\r\t ");
				$value = trim(substr($val, $pos + 1), "/\n\r\t ");

				if ($type == 'DATE') {
					$sql_info['date'] = $value;
				}
				else if ($type == 'MYSQL SERVER VERSION') {
					$sql_info['mysql_ver'] = $value;
				}
				else if ($type == 'PHP VERSION') {
					$sql_info['php_ver'] = $value;
				}
				else if ($type == 'ECShop VERSION') {
					$sql_info['ecs_ver'] = $value;
				}
				else if ($type == 'Vol') {
					$sql_info['vol'] = $value;
				}
			}
		}

		return $sql_info;
	}

	public function get_tables_list($path)
	{
		if (!file_exists($path)) {
			$this->error_msg = $path . ' is not exists';
			return false;
		}

		$arr = array();
		$str = @file_get_contents($path);

		if (!empty($str)) {
			$tmp_arr = explode("\n", $str);

			foreach ($tmp_arr as $val) {
				$val = trim($val, "\r;");

				if (!empty($val)) {
					list($table, $count) = explode(':', $val);
					$arr[$table] = $count;
				}
			}
		}

		return $arr;
	}

	public function put_tables_list($path, $arr)
	{
		if (is_array($arr)) {
			$str = '';

			foreach ($arr as $key => $val) {
				$str .= $key . ':' . $val . ";\r\n";
			}

			if (@file_put_contents($path, $str)) {
				return true;
			}
			else {
				$this->error_msg = 'Can not write ' . $path;
				return false;
			}
		}
		else {
			$this->error_msg = 'It need a array';
			return false;
		}
	}

	static public function get_random_name()
	{
		$str = date('Ymd');

		for ($i = 0; $i < 6; $i++) {
			$str .= chr(mt_rand(97, 122));
		}

		return $str;
	}

	public function errorMsg()
	{
		return $this->error_msg;
	}
}

function dump_escape_string($str)
{
	return $GLOBALS['db']->escape_string($str);
}

function dump_null_string($str)
{
	if (!isset($str) || is_null($str)) {
		$str = 'NULL';
	}

	return $str;
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
