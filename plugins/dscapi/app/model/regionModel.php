<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\model;

abstract class regionModel extends \app\func\common
{
	private $alias_config;

	public function __construct()
	{
		$this->regionModel();
	}

	public function regionModel($table = '')
	{
		$this->alias_config = array('region' => 'r');

		if ($table) {
			return $this->alias_config[$table];
		}
		else {
			return $this->alias_config;
		}
	}

	public function get_where($val = array(), $alias = '')
	{
		$where = 1;
		$where .= \app\func\base::get_where($val['region_id'], $alias . 'region_id');
		$where .= \app\func\base::get_where($val['parent_id'], $alias . 'parent_id');
		$where .= \app\func\base::get_where($val['region_name'], $alias . 'region_name');
		$where .= \app\func\base::get_where($val['region_type'], $alias . 'region_type');
		return $where;
	}

	public function get_select_list($table, $select, $where, $page_size, $page, $sort_by, $sort_order)
	{
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;
		$result['record_count'] = $GLOBALS['db']->getOne($sql);

		if ($sort_by) {
			$where .= ' ORDER BY ' . $sort_by . ' ' . $sort_order . ' ';
		}

		$where .= ' LIMIT ' . ($page - 1) * $page_size . (',' . $page_size);
		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;
		$result['list'] = $GLOBALS['db']->getAll($sql);
		return $result;
	}

	public function get_select_info($table, $select, $where)
	{
		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where . ' LIMIT 1';
		$result = $GLOBALS['db']->getRow($sql);
		return $result;
	}

	public function get_insert($table, $select, $format)
	{
		$regionLang = \languages\regionLang::lang_region_insert();
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $select, 'INSERT');
		$id = $GLOBALS['db']->insert_id();
		$common_data = array('result' => empty($id) ? 'failure' : 'success', 'msg' => empty($id) ? $regionLang['msg_failure']['failure'] : $regionLang['msg_success']['success'], 'error' => empty($id) ? $regionLang['msg_failure']['error'] : $regionLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_update($table, $select, $where, $format)
	{
		$regionLang = \languages\regionLang::lang_region_update();

		if (strlen($where) != 1) {
			$info = $this->get_select_info($table, '*', $where);

			if (!$info) {
				$common_data = array('result' => 'failure', 'msg' => $regionLang['null_failure']['failure'], 'error' => $regionLang['null_failure']['error'], 'format' => $format);
			}
			else {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $select, 'UPDATE', $where);
				$common_data = array('result' => empty($select) ? 'failure' : 'success', 'msg' => empty($select) ? $regionLang['msg_failure']['failure'] : $regionLang['msg_success']['success'], 'error' => empty($select) ? $regionLang['msg_failure']['error'] : $regionLang['msg_success']['error'], 'format' => $format);
			}
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $regionLang['where_failure']['failure'], 'error' => $regionLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_delete($table, $where, $format)
	{
		$regionLang = \languages\regionLang::lang_region_delete();

		if (strlen($where) != 1) {
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;
			$GLOBALS['db']->query($sql);
			$common_data = array('result' => 'success', 'msg' => $regionLang['msg_success']['success'], 'error' => $regionLang['msg_success']['error'], 'format' => $format);
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $regionLang['where_failure']['failure'], 'error' => $regionLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_list_common_data($result, $page_size, $page, $regionLang, $format)
	{
		$common_data = array('page_size' => $page_size, 'page' => $page, 'result' => empty($result) ? 'failure' : 'success', 'msg' => empty($result) ? $regionLang['msg_failure']['failure'] : $regionLang['msg_success']['success'], 'error' => empty($result) ? $regionLang['msg_failure']['error'] : $regionLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result, 1);
		return $result;
	}

	public function get_info_common_data_fs($result, $regionLang, $format)
	{
		$common_data = array('result' => empty($result) ? 'failure' : 'success', 'msg' => empty($result) ? $regionLang['msg_failure']['failure'] : $regionLang['msg_success']['success'], 'error' => empty($result) ? $regionLang['msg_failure']['error'] : $regionLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result);
		return $result;
	}

	public function get_info_common_data_f($regionLang, $format)
	{
		$result = array();
		$common_data = array('result' => 'failure', 'msg' => $regionLang['where_failure']['failure'], 'error' => $regionLang['where_failure']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result);
		return $result;
	}
}

?>
