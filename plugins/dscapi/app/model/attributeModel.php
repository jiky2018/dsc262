<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\model;

abstract class attributeModel extends \app\func\common
{
	private $alias_config;

	public function __construct()
	{
		$this->attributeModel();
	}

	public function attributeModel($table = '')
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

		if ($val['seller_type']) {
			$where .= \app\func\base::get_where($val['seller_id'], $alias . 'ru_id');
		}
		else {
			$where .= \app\func\base::get_where($val['seller_id'], $alias . 'user_id');
		}

		$where .= \app\func\base::get_where($val['attr_id'], $alias . 'attr_id');
		$where .= \app\func\base::get_where($val['cat_id'], $alias . 'cat_id');
		$where .= \app\func\base::get_where($val['attr_name'], $alias . 'attr_name');
		$where .= \app\func\base::get_where($val['attr_type'], $alias . 'attr_type');
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
		$attributeLang = \languages\attributeLang::lang_region_insert();
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $select, 'INSERT');
		$id = $GLOBALS['db']->insert_id();
		$common_data = array('result' => empty($id) ? 'failure' : 'success', 'msg' => empty($id) ? $attributeLang['msg_failure']['failure'] : $attributeLang['msg_success']['success'], 'error' => empty($id) ? $attributeLang['msg_failure']['error'] : $attributeLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_update($table, $select, $where, $format)
	{
		$attributeLang = \languages\attributeLang::lang_region_update();

		if (strlen($where) != 1) {
			$info = $this->get_select_info($table, '*', $where);

			if (!$info) {
				$common_data = array('result' => 'failure', 'msg' => $attributeLang['null_failure']['failure'], 'error' => $attributeLang['null_failure']['error'], 'format' => $format);
			}
			else {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $select, 'UPDATE', $where);
				$common_data = array('result' => empty($select) ? 'failure' : 'success', 'msg' => empty($select) ? $attributeLang['msg_failure']['failure'] : $attributeLang['msg_success']['success'], 'error' => empty($select) ? $attributeLang['msg_failure']['error'] : $attributeLang['msg_success']['error'], 'format' => $format);
			}
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $attributeLang['where_failure']['failure'], 'error' => $attributeLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_delete($table, $where, $format)
	{
		$attributeLang = \languages\attributeLang::lang_region_delete();

		if (strlen($where) != 1) {
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;
			$GLOBALS['db']->query($sql);
			$common_data = array('result' => 'success', 'msg' => $attributeLang['msg_success']['success'], 'error' => $attributeLang['msg_success']['error'], 'format' => $format);
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $attributeLang['where_failure']['failure'], 'error' => $attributeLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_list_common_data($result, $page_size, $page, $attributeLang, $format)
	{
		$common_data = array('page_size' => $page_size, 'page' => $page, 'result' => empty($result) ? 'failure' : 'success', 'msg' => empty($result) ? $attributeLang['msg_failure']['failure'] : $attributeLang['msg_success']['success'], 'error' => empty($result) ? $attributeLang['msg_failure']['error'] : $attributeLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result, 1);
		return $result;
	}

	public function get_info_common_data_fs($result, $attributeLang, $format)
	{
		$common_data = array('result' => empty($result) ? 'failure' : 'success', 'msg' => empty($result) ? $attributeLang['msg_failure']['failure'] : $attributeLang['msg_success']['success'], 'error' => empty($result) ? $attributeLang['msg_failure']['error'] : $attributeLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result);
		return $result;
	}

	public function get_info_common_data_f($attributeLang, $format)
	{
		$result = array();
		$common_data = array('result' => 'failure', 'msg' => $attributeLang['where_failure']['failure'], 'error' => $attributeLang['where_failure']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result);
		return $result;
	}
}

?>
