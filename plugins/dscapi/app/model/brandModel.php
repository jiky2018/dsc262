<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\model;

abstract class brandModel extends \app\func\common
{
	private $alias_config;

	public function __construct()
	{
		$this->brandModel();
	}

	public function brandModel($table = '')
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
		$where .= \app\func\base::get_where($val['brand_id'], $alias . 'brand_id');
		$where .= \app\func\base::get_where($val['brand_name'], $alias . 'brand_name');
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
		$brandLang = \languages\brandLang::lang_brand_insert();
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $select, 'INSERT');
		$id = $GLOBALS['db']->insert_id();
		$common_data = array('result' => empty($id) ? 'failure' : 'success', 'msg' => empty($id) ? $brandLang['msg_failure']['failure'] : $brandLang['msg_success']['success'], 'error' => empty($id) ? $brandLang['msg_failure']['error'] : $brandLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_update($table, $select, $where, $format)
	{
		$brandLang = \languages\brandLang::lang_brand_update();

		if (strlen($where) != 1) {
			$info = $this->get_select_info($table, '*', $where);

			if (!$info) {
				$common_data = array('result' => 'failure', 'msg' => $brandLang['null_failure']['failure'], 'error' => $brandLang['null_failure']['error'], 'format' => $format);
			}
			else {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $select, 'UPDATE', $where);
				$common_data = array('result' => empty($select) ? 'failure' : 'success', 'msg' => empty($select) ? $brandLang['msg_failure']['failure'] : $brandLang['msg_success']['success'], 'error' => empty($select) ? $brandLang['msg_failure']['error'] : $brandLang['msg_success']['error'], 'format' => $format);
			}
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $brandLang['where_failure']['failure'], 'error' => $brandLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_delete($table, $where, $format)
	{
		$brandLang = \languages\brandLang::lang_brand_delete();

		if (strlen($where) != 1) {
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;
			$GLOBALS['db']->query($sql);
			$common_data = array('result' => 'success', 'msg' => $brandLang['msg_success']['success'], 'error' => $brandLang['msg_success']['error'], 'format' => $format);
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $brandLang['where_failure']['failure'], 'error' => $brandLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_list_common_data($result, $page_size, $page, $brandLang, $format)
	{
		$common_data = array('page_size' => $page_size, 'page' => $page, 'result' => empty($result) ? 'failure' : 'success', 'msg' => empty($result) ? $brandLang['msg_failure']['failure'] : $brandLang['msg_success']['success'], 'error' => empty($result) ? $brandLang['msg_failure']['error'] : $brandLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result, 1);
		return $result;
	}

	public function get_info_common_data_fs($result, $brandLang, $format)
	{
		$common_data = array('result' => empty($result) ? 'failure' : 'success', 'msg' => empty($result) ? $brandLang['msg_failure']['failure'] : $brandLang['msg_success']['success'], 'error' => empty($result) ? $brandLang['msg_failure']['error'] : $brandLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result);
		return $result;
	}

	public function get_info_common_data_f($brandLang, $format)
	{
		$result = array();
		$common_data = array('result' => 'failure', 'msg' => $brandLang['where_failure']['failure'], 'error' => $brandLang['where_failure']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result);
		return $result;
	}
}

?>
