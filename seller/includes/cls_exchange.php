<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
class exchange
{
	public $table;
	public $db;
	public $id;
	public $name;
	public $error_msg;

	public function __construct($table, &$db, $id, $name)
	{
		$this->table = $table;
		$this->db = &$db;
		$this->id = $id;
		$this->name = $name;
		$this->error_msg = '';
	}

	public function is_only($col, $name, $id = 0, $where = '', $table = '', $idType = '')
	{
		if (empty($table)) {
			$table = $this->table;
		}

		if (empty($idType)) {
			$idType = $this->id;
		}

		$sql = 'SELECT COUNT(*) FROM ' . $table . (' WHERE ' . $col . ' = \'' . $name . '\'');
		$sql .= empty($id) ? '' : ' AND ' . $idType . (' <> \'' . $id . '\'');
		$sql .= empty($where) ? '' : ' AND ' . $where;
		return $this->db->getOne($sql) == 0;
	}

	public function num($col, $name, $id = 0, $where = '')
	{
		$sql = 'SELECT COUNT(*) FROM ' . $this->table . (' WHERE ' . $col . ' = \'' . $name . '\'');
		$sql .= empty($id) ? '' : ' AND ' . $this->id . (' != \'' . $id . '\' ');
		$sql .= empty($where) ? '' : ' AND ' . $where;
		return $this->db->getOne($sql);
	}

	public function edit($set, $id, $table = '', $idType = '')
	{
		if (empty($table)) {
			$table = $this->table;
		}
		else {
			$table = $GLOBALS['ecs']->table($table);
		}

		if (empty($idType)) {
			$idType = $this->id;
		}

		$sql = 'UPDATE ' . $table . ' SET ' . $set . ' WHERE ' . $idType . (' = \'' . $id . '\'');

		if ($this->db->query($sql)) {
			return true;
		}
		else {
			return false;
		}
	}

	public function get_name($id, $name = '')
	{
		if (empty($name)) {
			$name = $this->name;
		}

		$sql = 'SELECT `' . $name . '` FROM ' . $this->table . (' WHERE ' . $this->id . ' = \'' . $id . '\'');
		return $this->db->getOne($sql);
	}

	public function drop($id, $table = '', $idType = '')
	{
		if (empty($table)) {
			$table = $this->table;
		}
		else {
			$table = $GLOBALS['ecs']->table($table);
		}

		if (empty($idType)) {
			$idType = $this->id;
		}

		$sql = 'DELETE FROM ' . $table . ' WHERE ' . $idType . (' = \'' . $id . '\'');
		return $this->db->query($sql);
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
