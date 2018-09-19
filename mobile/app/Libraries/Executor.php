<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Libraries;

class Executor
{
	/**
     * 记录程序执行过程中最后产生的那条错误信息
     *
     * @access  public
     * @var     string $error
     */
	public $error = '';
	/**
     * 存储将被忽略的错误号，这些错误不会记录在$error属性中，
     * 但仍然会记录在错误日志文件当中。
     *
     * @access  private
     * @var     array $ignored_errors
     */
	public $ignored_errors = array();
	/**
     * MySQL对象
     *
     * @access  private
     * @var     object $db
     */
	public $db = '';
	/**
     * 数据库字符编码
     *
     * @access   private
     * @var      string $charset
     */
	public $db_charset = '';
	/**
     * 替换前表前缀
     *
     * @access  private
     * @var     string $source_prefix
     */
	public $source_prefix = '';
	/**
     * 替换后表前缀
     *
     * @access  private
     * @var     string $target_prefix
     */
	public $target_prefix = '';
	/**
     * 当发生错误时，程序将把日志记录在该指定的文件中
     *
     * @access  private
     * @var     string $log_path
     */
	public $log_path = '';
	/**
     * 开启此选项后，程序将进行智能化地查询操作，即使重复运行本程序，也不会引起数据库的查询冲突。这点在浏览器
     * 和服务器之间进行通讯时是非常有必要的，因为网络很有可能在您不经意间发生中断。不过，由于用到了大量的正则
     * 表达式，开启该选项后将非常耗费服务器的资源。
     *
     * @access  private
     * @var     boolean $auto_match
     */
	public $auto_match = false;
	/**
     * 记录当前正在执行的SQL文件名
     *
     * @access  private
     * @var     string $current_file
     */
	public $current_file = 'Not a file, but a string.';

	public function __construct($db, $charset = 'gbk', $sprefix = 'ecs_', $tprefix = 'ecs_', $log_path = '', $auto_match = false, $ignored_errors = array())
	{
		$this->db = $db;
		$this->db_charset = $charset;
		$this->source_prefix = $sprefix;
		$this->target_prefix = $tprefix;
		$this->log_path = $log_path;
		$this->auto_match = $auto_match;
		$this->ignored_errors = $ignored_errors;
	}

	public function run_all($sql_files)
	{
		if (!is_array($sql_files)) {
			return false;
		}

		foreach ($sql_files as $sql_file) {
			$query_items = $this->parse_sql_file($sql_file);

			if (!$query_items) {
				continue;
			}

			foreach ($query_items as $query_item) {
				if (!$query_item) {
					continue;
				}

				if (!$this->query($query_item)) {
					return false;
				}
			}
		}

		return true;
	}

	public function parse_sql_file($file_path)
	{
		if (!file_exists($file_path)) {
			return false;
		}

		$this->current_file = $file_path;
		$sql = implode('', file($file_path));
		$sql = $this->remove_comment($sql);
		$sql = trim($sql);

		if (!$sql) {
			return false;
		}

		$sql = $this->replace_prefix($sql);
		$sql = str_replace("\r", '', $sql);
		$query_items = explode(";\n", $sql);
		return $query_items;
	}

	public function query($query_item)
	{
		$query_item = trim($query_item);

		if (!$query_item) {
			return false;
		}

		if (preg_match('/^\\s*CREATE\\s+TABLE\\s*/i', $query_item)) {
			if (!$this->create_table($query_item)) {
				return false;
			}
		}
		else {
			if ($this->auto_match && preg_match('/^\\s*ALTER\\s+TABLE\\s*/i', $query_item)) {
				if (!$this->alter_table($query_item)) {
					return false;
				}
			}
			else if (!$this->do_other($query_item)) {
				return false;
			}
		}

		return true;
	}

	public function remove_comment($sql)
	{
		$sql = preg_replace('/^\\s*(?:--|#).*/m', '', $sql);
		$sql = preg_replace('/^\\s*\\/\\*.*?\\*\\//ms', '', $sql);
		return $sql;
	}

	public function replace_prefix($sql)
	{
		$keywords = 'CREATE\\s+TABLE(?:\\s+IF\\s+NOT\\s+EXISTS)?|' . 'DROP\\s+TABLE(?:\\s+IF\\s+EXISTS)?|' . 'ALTER\\s+TABLE|' . 'UPDATE|' . 'REPLACE\\s+INTO|' . 'DELETE\\s+FROM|' . 'INSERT\\s+INTO';
		$pattern = '/(' . $keywords . ')(\\s*)`?' . $this->source_prefix . '(\\w+)`?(\\s*)/i';
		$replacement = '\\1\\2`' . $this->target_prefix . '\\3`\\4';
		$sql = preg_replace($pattern, $replacement, $sql);
		$pattern = '/(UPDATE.*?WHERE)(\\s*)`?' . $this->source_prefix . '(\\w+)`?(\\s*\\.)/i';
		$replacement = '\\1\\2`' . $this->target_prefix . '\\3`\\4';
		$sql = preg_replace($pattern, $replacement, $sql);
		return $sql;
	}

	public function get_table_name($query_item, $query_type = '')
	{
		$pattern = '';
		$matches = array();
		$table_name = '';
		if (!$query_type && preg_match('/^\\s*(\\w+)/', $query_item, $matches)) {
			$query_type = $matches[1];
		}

		$query_type = strtoupper($query_type);

		switch ($query_type) {
		case 'ALTER':
			$pattern = '/^\\s*ALTER\\s+TABLE\\s*`?(\\w+)/i';
			break;

		case 'CREATE':
			$pattern = '/^\\s*CREATE\\s+TABLE(?:\\s+IF\\s+NOT\\s+EXISTS)?\\s*`?(\\w+)/i';
			break;

		case 'DROP':
			$pattern = '/^\\s*DROP\\s+TABLE(?:\\s+IF\\s+EXISTS)?\\s*`?(\\w+)/i';
			break;

		case 'INSERT':
			$pattern = '/^\\s*INSERT\\s+INTO\\s*`?(\\w+)/i';
			break;

		case 'REPLACE':
			$pattern = '/^\\s*REPLACE\\s+INTO\\s*`?(\\w+)/i';
			break;

		case 'UPDATE':
			$pattern = '/^\\s*UPDATE\\s*`?(\\w+)/i';
			break;

		default:
			return false;
		}

		if (!preg_match($pattern, $query_item, $matches)) {
			return false;
		}

		$table_name = $matches[1];
		return $table_name;
	}

	public function get_spec_query_item($file_path, $pos)
	{
		$query_items = $this->parse_sql_file($file_path);
		if (empty($query_items) || empty($query_items[$pos])) {
			return false;
		}

		return $query_items[$pos];
	}

	public function create_table($query_item)
	{
		$pattern = '/^\\s*(CREATE\\s+TABLE[^(]+\\(.*\\))(.*)$/is';

		if (!preg_match($pattern, $query_item, $matches)) {
			return false;
		}

		$main = $matches[1];
		$postfix = $matches[2];
		$pattern = '/.*(?:ENGINE|TYPE)\\s*=\\s*([a-z]+).*$/is';
		$type = (preg_match($pattern, $postfix, $matches) ? $matches[1] : 'MYISAM');
		$pattern = '/.*(AUTO_INCREMENT\\s*=\\s*\\d+).*$/is';
		$auto_incr = (preg_match($pattern, $postfix, $matches) ? $matches[1] : '');
		$postfix = ('4.1' < $this->db->version() ? ' ENGINE=' . $type . ' DEFAULT CHARACTER SET ' . $this->db_charset : ' TYPE=' . $type);
		$postfix .= ' ' . $auto_incr;
		$sql = $main . $postfix;

		if (!$this->db->query($sql, 'SILENT')) {
			$this->handle_error($sql);
			return false;
		}

		return true;
	}

	public function alter_table($query_item)
	{
		$table_name = $this->get_table_name($query_item, 'ALTER');

		if (!$table_name) {
			return false;
		}

		$result = $this->parse_change_query($query_item, $table_name);
		if ($result[0] && !$this->db->query($result[0], 'SILENT')) {
			$this->handle_error($result[0]);
			return false;
		}

		if (!$result[1]) {
			return true;
		}

		$result = $this->parse_drop_column_query($result[1], $table_name);
		if ($result[0] && !$this->db->query($result[0], 'SILENT')) {
			$this->handle_error($result[0]);
			return false;
		}

		if (!$result[1]) {
			return true;
		}

		$result = $this->parse_add_column_query($result[1], $table_name);
		if ($result[0] && !$this->db->query($result[0], 'SILENT')) {
			$this->handle_error($result[0]);
			return false;
		}

		if (!$result[1]) {
			return true;
		}

		$result = $this->parse_drop_index_query($result[1], $table_name);
		if ($result[0] && !$this->db->query($result[0], 'SILENT')) {
			$this->handle_error($result[0]);
			return false;
		}

		if (!$result[1]) {
			return true;
		}

		$result = $this->parse_add_index_query($result[1], $table_name);
		if ($result[0] && !$this->db->query($result[0], 'SILENT')) {
			$this->handle_error($result[0]);
			return false;
		}

		if ($result[1] && !$this->db->query($result[1], 'SILENT')) {
			$this->handle_error($result[1]);
			return false;
		}

		return true;
	}

	public function parse_change_query($query_item, $table_name = '')
	{
		$result = array('', $query_item);

		if (!$table_name) {
			$table_name = $this->get_table_name($query_item, 'ALTER');
		}

		$matches = array();
		$pattern = '/\\s*CHANGE\\s*`?(\\w+)`?\\s*`?(\\w+)`?([^,(]+\\([^,]+?(?:,[^,)]+)*\\)[^,]+|[^,;]+)\\s*,?/i';

		if (preg_match_all($pattern, $query_item, $matches, PREG_SET_ORDER)) {
			$fields = $this->get_fields($table_name);
			$num = count($matches);
			$sql = '';

			for ($i = 0; $i < $num; $i++) {
				if (in_array($matches[$i][1], $fields)) {
					$sql .= $matches[$i][0];
				}
				else if (in_array($matches[$i][2], $fields)) {
					$sql .= 'CHANGE ' . $matches[$i][2] . ' ' . $matches[$i][2] . ' ' . $matches[$i][3] . ',';
				}
				else {
					$sql .= 'ADD ' . $matches[$i][2] . ' ' . $matches[$i][3] . ',';
					$sql = preg_replace('/(\\s+AUTO_INCREMENT)/i', '\\1 PRIMARY KEY', $sql);
				}
			}

			$sql = 'ALTER TABLE ' . $table_name . ' ' . $sql;
			$result[0] = preg_replace('/\\s*,\\s*$/', '', $sql);
			$result[0] = $this->insert_charset($result[0]);
			$result[1] = preg_replace($pattern, '', $query_item);
			$result[1] = $this->has_other_query($result[1]) ? $result[1] : '';
		}

		return $result;
	}

	public function parse_drop_column_query($query_item, $table_name = '')
	{
		$result = array('', $query_item);

		if (!$table_name) {
			$table_name = $this->get_table_name($query_item, 'ALTER');
		}

		$matches = array();
		$pattern = '/\\s*DROP(?:\\s+COLUMN)?(?!\\s+(?:INDEX|PRIMARY))\\s*`?(\\w+)`?\\s*,?/i';

		if (preg_match_all($pattern, $query_item, $matches, PREG_SET_ORDER)) {
			$fields = $this->get_fields($table_name);
			$num = count($matches);
			$sql = '';

			for ($i = 0; $i < $num; $i++) {
				if (in_array($matches[$i][1], $fields)) {
					$sql .= 'DROP ' . $matches[$i][1] . ',';
				}
			}

			if ($sql) {
				$sql = 'ALTER TABLE ' . $table_name . ' ' . $sql;
				$result[0] = preg_replace('/\\s*,\\s*$/', '', $sql);
			}

			$result[1] = preg_replace($pattern, '', $query_item);
			$result[1] = $this->has_other_query($result[1]) ? $result[1] : '';
		}

		return $result;
	}

	public function parse_add_column_query($query_item, $table_name = '')
	{
		$result = array('', $query_item);

		if (!$table_name) {
			$table_name = $this->get_table_name($query_item, 'ALTER');
		}

		$matches = array();
		$pattern = '/\\s*ADD(?:\\s+COLUMN)?(?!\\s+(?:INDEX|UNIQUE|PRIMARY))\\s*(`?(\\w+)`?(?:[^,(]+\\([^,]+?(?:,[^,)]+)*\\)[^,]+|[^,;]+))\\s*,?/i';

		if (preg_match_all($pattern, $query_item, $matches, PREG_SET_ORDER)) {
			$fields = $this->get_fields($table_name);
			$mysql_ver = $this->db->version();
			$num = count($matches);
			$sql = '';

			for ($i = 0; $i < $num; $i++) {
				if (in_array($matches[$i][2], $fields)) {
					if ($mysql_ver < '4.0.1') {
						$matches[$i][1] = preg_replace('/\\s*(?:AFTER|FIRST)\\s*.*$/i', '', $matches[$i][1]);
					}

					$sql .= 'CHANGE ' . $matches[$i][2] . ' ' . $matches[$i][1] . ',';
				}
				else {
					$sql .= 'ADD ' . $matches[$i][1] . ',';
				}
			}

			$sql = 'ALTER TABLE ' . $table_name . ' ' . $sql;
			$result[0] = preg_replace('/\\s*,\\s*$/', '', $sql);
			$result[0] = $this->insert_charset($result[0]);
			$result[1] = preg_replace($pattern, '', $query_item);
			$result[1] = $this->has_other_query($result[1]) ? $result[1] : '';
		}

		return $result;
	}

	public function parse_drop_index_query($query_item, $table_name = '')
	{
		$result = array('', $query_item);

		if (!$table_name) {
			$table_name = $this->get_table_name($query_item, 'ALTER');
		}

		$pattern = '/\\s*DROP\\s+(?:PRIMARY\\s+KEY|INDEX\\s*`?(\\w+)`?)\\s*,?/i';

		if (preg_match_all($pattern, $query_item, $matches, PREG_SET_ORDER)) {
			$indexes = $this->get_indexes($table_name);
			$num = count($matches);
			$sql = '';

			for ($i = 0; $i < $num; $i++) {
				if (empty($matches[$i][1])) {
					$sql .= 'DROP PRIMARY KEY,';
				}
				else if (in_array($matches[$i][1], $indexes)) {
					$sql .= 'DROP INDEX ' . $matches[$i][1] . ',';
				}
			}

			if ($sql) {
				$sql = 'ALTER TABLE ' . $table_name . ' ' . $sql;
				$result[0] = preg_replace('/\\s*,\\s*$/', '', $sql);
			}

			$result[1] = preg_replace($pattern, '', $query_item);
			$result[1] = $this->has_other_query($result[1]) ? $result[1] : '';
		}

		return $result;
	}

	public function parse_add_index_query($query_item, $table_name = '')
	{
		$result = array('', $query_item);

		if (!$table_name) {
			$table_name = $this->get_table_name($query_item, 'ALTER');
		}

		$pattern = '/\\s*ADD\\s+((?:INDEX|UNIQUE|(PRIMARY\\s+KEY))\\s*(?:`?(\\w+)`?)?\\s*\\(\\s*`?(\\w+)`?\\s*(?:,[^,)]+)*\\))\\s*,?/i';

		if (preg_match_all($pattern, $query_item, $matches, PREG_SET_ORDER)) {
			$indexes = $this->get_indexes($table_name);
			$num = count($matches);
			$sql = '';

			for ($i = 0; $i < $num; $i++) {
				$index = (!empty($matches[$i][3]) ? $matches[$i][3] : $matches[$i][4]);
				if (!empty($matches[$i][2]) && in_array('PRIMARY', $indexes)) {
					$sql .= 'DROP PRIMARY KEY,';
				}
				else if (in_array($index, $indexes)) {
					$sql .= 'DROP INDEX ' . $index . ',';
				}

				$sql .= 'ADD ' . $matches[$i][1] . ',';
			}

			$sql = 'ALTER TABLE ' . $table_name . ' ' . $sql;
			$result[0] = preg_replace('/\\s*,\\s*$/', '', $sql);
			$result[1] = preg_replace($pattern, '', $query_item);
			$result[1] = $this->has_other_query($result[1]) ? $result[1] : '';
		}

		return $result;
	}

	public function get_indexes($table_name)
	{
		$indexes = array();
		$result = $this->db->query('SHOW INDEX FROM ' . $table_name, 'SILENT');

		if ($result) {
			while ($row = $this->db->fetchRow($result)) {
				$indexes[] = $row['Key_name'];
			}
		}

		return $indexes;
	}

	public function get_fields($table_name)
	{
		$fields = array();
		$result = $this->db->query('SHOW FIELDS FROM ' . $table_name, 'SILENT');

		if ($result) {
			while ($row = $this->db->fetchRow($result)) {
				$fields[] = $row['Field'];
			}
		}

		return $fields;
	}

	public function has_other_query($sql_string)
	{
		return preg_match('/^\\s*ALTER\\s+TABLE\\s*`\\w+`\\s*\\w+/i', $sql_string);
	}

	public function insert_charset($sql_string)
	{
		if ('4.1' < $this->db->version()) {
			$sql_string = preg_replace('/(TEXT|CHAR\\(.*?\\)|VARCHAR\\(.*?\\))\\s+/i', '\\1 CHARACTER SET ' . $this->db_charset . ' ', $sql_string);
		}

		return $sql_string;
	}

	public function do_other($query_item)
	{
		if (!$this->db->query($query_item, 'SILENT')) {
			$this->handle_error($query_item);
			return false;
		}

		return true;
	}

	public function handle_error($query_item)
	{
		$mysql_error = 'ERROR NO: ' . $this->db->errno() . "\r\nERROR MSG: " . $this->db->error();
		$error_str = "SQL Error:\r\n " . $mysql_error . "\r\n\r\n" . "Query String:\r\n " . $query_item . "\r\n\r\n" . "File Path:\r\n " . $this->current_file . "\r\n\r\n\r\n\r\n";

		if (!in_array($this->db->errno(), $this->ignored_errors)) {
			$this->error = $error_str;
		}

		if ($this->log_path) {
			$f = @fopen($this->log_path, 'ab+');

			if (!$f) {
				return false;
			}

			if (!@fwrite($f, $error_str)) {
				return false;
			}
		}

		return true;
	}
}


?>
