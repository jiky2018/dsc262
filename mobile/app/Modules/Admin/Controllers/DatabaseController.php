<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Admin\Controllers;

class DatabaseController extends \App\Modules\Base\Controllers\BackendController
{
	public function actionIndex()
	{
		$this->admin_priv('db_backup');
		$obj = \Think\Db::getInstance(C());
		$cache_id = md5('tables' . C('DB_NAME'));
		$tables = S($cache_id);

		if ($tables === false) {
			$tables = $obj->getTables();
			S($cache_id, $tables);
		}

		foreach ($tables as $key => $table_name) {
			$status = 0;
			$fields = $obj->getFields($table_name);

			foreach ($fields as $val) {
				if ($val['primary'] == false && $val['autoinc'] == false && $val['notnull'] == true && $val['default'] === null) {
					$status = 1;
				}
			}

			$new_tables[] = array('table_name' => $table_name, 'status' => $status);
		}

		$this->assign('tables', $new_tables);
		$this->display();
	}

	public function actionAll()
	{
		$table_num = 0;
		$field_num = 0;
		$obj = \Think\Db::getInstance(C());
		$cache_id = md5('tables' . C('DB_NAME'));
		$tables = S($cache_id);

		if ($tables === false) {
			$tables = $obj->getTables();
			S($cache_id, $tables);
		}

		foreach ($tables as $table_name) {
			$fields = $obj->getFields($table_name);
			$field_info = $this->getFieldInfo($table_name);
			$fields = array_merge_recursive($field_info, $fields);

			foreach ($fields as $val) {
				if ($val['primary'] == false && $val['autoinc'] == false && $val['notnull'] == true && $val['default'] === null) {
					$this->editField($table_name, $val);
					$field_num++;
				}
			}

			$table_num++;
		}

		$message = 0 < $field_num ? '成功更新 ' . $table_num . '个表，共' . $field_num . '条字段！' : '没有字段需要更新';
		$this->message($message, url('index'));
	}

	public function actionOne()
	{
		$table_name = I('get.table', '', array('htmlspecialchars', 'trim'));
		$field_num = 0;
		$obj = \Think\Db::getInstance(C());
		$fields = $obj->getFields($table_name);
		$field_info = $this->getFieldInfo($table_name);
		$fields = array_merge_recursive($field_info, $fields);

		foreach ($fields as $val) {
			if ($val['primary'] == false && $val['autoinc'] == false && $val['notnull'] == true && $val['default'] === null) {
				$this->editField($table_name, $val);
				$field_num++;
			}
		}

		$message = 0 < $field_num ? '成功更新 ' . $table_name . '表' . $field_num . '条字段！' : '没有字段需要更新';
		$this->message($message, url('index'));
	}

	private function editField($table, $val)
	{
		$sql = 'ALTER TABLE `' . $table . '` MODIFY COLUMN `' . $val['name'] . '` ';
		$sql .= $this->filterFieldInfo($val);
		return M()->execute($sql);
	}

	private function filterFieldInfo($val)
	{
		if (strpos($val['type'], 'int') !== false || strpos($val['type'], 'decimal') !== false || strpos($val['type'], 'float') !== false || strpos($val['type'], 'time') !== false) {
			$sql .= $val['type'] . ' NOT NULL DEFAULT 0 ';
		}

		if (strpos($val['type'], 'varchar') !== false || strpos($val['type'], 'char') !== false) {
			$sql .= $val['type'] . ' NOT NULL DEFAULT \'\' ';
		}

		if (strpos($val['type'], 'text') !== false) {
			$sql .= $val['type'] . ' ';
		}

		$val['comment'] = empty($val['comment']) ? '' : ' COMMENT \'' . $val['comment'] . '\' ';
		$sql .= $val['comment'];
		return $sql;
	}

	private function getFieldInfo($table)
	{
		$sql = 'SELECT column_name,column_comment FROM information_schema.columns WHERE table_name = \'' . $table . '\' AND table_schema = \'' . C('db_name') . '\'';
		$result = M()->query($sql);
		$info = array();

		foreach ($result as $key => $val) {
			$info[$val['column_name']] = array('comment' => $val['column_comment']);
		}

		return $info;
	}
}

?>
