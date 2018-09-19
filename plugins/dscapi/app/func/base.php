<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\func;

class base
{
	public function __construct()
	{
	}

	static public function get_select_field($select = array(), $alias = '')
	{
		if ($select && is_array($select)) {
			if ($alias) {
				foreach ($select as $key => $row) {
					$select[$key] = $alias . $row;
				}
			}

			$select = implode(', ', $select);
		}
		else if (empty($select)) {
			$select = $alias . '*';
		}

		return $select;
	}

	static public function get_where($val = 0, $field = '', $conditions = '')
	{
		$where = '';

		if ($val != -1) {
			if ($val && $field) {
				$where .= ' AND ' . $field . base::db_create_in($val);
			}
		}

		$where .= $conditions;
		return $where;
	}

	static public function get_where_time($val = 0, $field = '', $time_type = 0, $conditions = '')
	{
		$where = '';

		if ($val != -1) {
			if ($val && $field) {
				if ($time_type == 1) {
					$where .= ' AND ' . $field . (' <= \'' . $val . '\'');
				}
				else {
					$where .= ' AND ' . $field . (' >= \'' . $val . '\'');
				}
			}
		}

		$where .= $conditions;
		return $where;
	}

	static public function get_join_on($join_on, $alias)
	{
		$alias = explode(',', $alias);
		$arr = array();

		foreach ($join_on as $key => $row) {
			if (0 < $key) {
				$row = explode('|', $row);
				$arr[$key] = ' ON ' . $alias[$key - 1] . '.' . $row[0] . ' =' . $alias[$key] . '.' . $row[1];
			}
			else {
				$arr[$key] = '';
			}
		}

		return $arr;
	}

	static public function get_join_table($table = '', $join_on = '', $select = '', $where = 1, $result_type = 0)
	{
		foreach ($table as $key => $row) {
			if ($key == 0) {
				$left .= $GLOBALS['ecs']->table($row['table']) . ' AS ' . $row['alias'] . ',';
			}
			else {
				$left .= ' LEFT JOIN ' . $GLOBALS['ecs']->table($row['table']) . ' AS ' . $row['alias'] . ',';
			}

			$alias .= $row['alias'] . ',';
		}

		$join_on = base::get_join_on($join_on, $alias);
		$left = explode(',', substr($left, 0, -1));
		$alias = explode(',', substr($alias, 0, -1));
		$sql = '';

		foreach ($left as $key => $row) {
			foreach ($join_on as $akey => $arow) {
				if ($key == $akey) {
					$sql .= $row . $arow;
				}
			}
		}

		if ($select == '*') {
			$select = '';

			foreach ($alias as $key => $row) {
				$select .= $row . '.*' . ',';
			}

			$select = substr($select, 0, -1);
		}

		$sql = 'SELECT ' . $select . ' FROM ' . $sql . (' WHERE ' . $where);

		if ($result_type == 1) {
			return $GLOBALS['db']->getAll($sql);
		}
		else if ($result_type == 2) {
			return $GLOBALS['db']->getRow($sql);
		}
		else {
			return $GLOBALS['db']->getOne($sql);
		}
	}

	static public function get_alias_table($table = '', $k = 0)
	{
		$alias = '';

		foreach ($table as $key => $row) {
			$alias .= substr($row, 0, 1);
		}

		return $alias . $k;
	}

	static public function get_intval($id)
	{
		if (isset($id) && !empty($id)) {
			$exid = explode(',', $id);

			if (1 < count($exid)) {
				$id = self::addslashes_deep($exid);
			}
			else {
				$id = intval($id);
			}
		}
		else {
			$id = 0;
		}

		return $id;
	}

	static public function get_addslashes($id)
	{
		if (isset($id) && !empty($id)) {
			$exid = explode(',', $id);

			if (1 < count($exid)) {
				$id = self::addslashes_deep($exid);
			}
			else {
				$id = self::dsc_addslashes($id);
			}
		}
		else {
			$id = 0;
		}

		return $id;
	}

	static public function addslashes_deep($value)
	{
		if (empty($value)) {
			return $value;
		}
		else {
			return is_array($value) ? array_map('addslashes_deep', $value) : self::dsc_addslashes($value);
		}
	}

	static public function get_request_filter($get = '', $type = 0)
	{
		if ($get && $type) {
			foreach ($get as $key => $row) {
				$preg = '/<script[\\s\\S]*?<\\/script>/i';
				if ($row && !is_array($row)) {
					$lower_row = strtolower($row);
					$lower_row = !empty($lower_row) ? preg_replace($preg, '', stripslashes($lower_row)) : '';

					if (strpos($lower_row, '</script>') !== false) {
						$get[$key] = compile_str($lower_row);
					}
					else if (strpos($lower_row, 'alert') !== false) {
						$get[$key] = '';
					}
					else {
						if (strpos($lower_row, 'updatexml') !== false || strpos($lower_row, 'extractvalue') !== false || strpos($lower_row, 'floor') !== false) {
							$get[$key] = '';
						}
						else {
							$get[$key] = make_semiangle($row);
						}
					}
				}
				else {
					$get[$key] = $row;
				}
			}
		}
		else if ($_REQUEST) {
			foreach ($_REQUEST as $key => $row) {
				$preg = '/<script[\\s\\S]*?<\\/script>/i';
				if ($row && !is_array($row)) {
					$lower_row = strtolower($row);
					$lower_row = !empty($lower_row) ? preg_replace($preg, '', stripslashes($lower_row)) : '';

					if (strpos($lower_row, '</script>') !== false) {
						$_REQUEST[$key] = compile_str($lower_row);
					}
					else if (strpos($lower_row, 'alert') !== false) {
						$_REQUEST[$key] = '';
					}
					else {
						if (strpos($lower_row, 'updatexml') !== false || strpos($lower_row, 'extractvalue') !== false || strpos($lower_row, 'floor') !== false) {
							$_REQUEST[$key] = '';
						}
						else {
							$_REQUEST[$key] = make_semiangle($row);
						}
					}
				}
				else {
					$_REQUEST[$key] = $row;
				}
			}
		}

		if ($get && $type == 1) {
			$_POST = $get;
			return $_POST;
		}
		else {
			if ($get && $type == 2) {
				$_GET = $get;
				return $_GET;
			}
			else {
				return $_REQUEST;
			}
		}
	}

	static public function dsc_addslashes($str = '', $type = 1)
	{
		if ($str) {
			$str = self::get_filter_str_array($str, $type);
			$str = self::get_del_str_comma($str);
		}

		return $str;
	}

	static public function get_filter_str_array($str, $type = 0)
	{
		$str_arr = array('order_id');

		if (!empty($str)) {
			$ex_rec = explode(',', $str);

			if (count($ex_rec) <= 1) {
				if ($type == 1) {
					$str = addslashes($str);
					$str = strtolower($str);
					if (strpos($str, 'updatexml') !== false || strpos($str, 'extractvalue') !== false || strpos($str, 'floor') !== false) {
						$str = '';
					}
					else {
						if ((strpos($str, 'or') !== false || strpos($str, 'and') !== false) && !in_array($str, $str_arr)) {
							$str = '';
						}
					}
				}
				else {
					$str = intval($str);
				}
			}
			else {
				foreach ($ex_rec as $key => $row) {
					if ($type == 1) {
						$row = addslashes($row);
						$row = strtolower($row);
						if (strpos($row, 'updatexml') !== false || strpos($row, 'extractvalue') !== false || strpos($row, 'floor') !== false) {
							$row = '';
						}
						else {
							if ((strpos($row, 'or') !== false || strpos($row, 'and') !== false) && !in_array($str, $str_arr)) {
								$row = '';
							}
						}

						$ex_rec[$key] = $row;
					}
					else {
						$ex_rec[$key] = intval($row);
					}
				}

				$str = implode(',', $ex_rec);
			}
		}

		return $str;
	}

	static public function get_del_str_comma($str = '')
	{
		if ($str && is_array($str)) {
			return $str;
		}
		else {
			if ($str) {
				$str = str_replace(',,', ',', $str);
				$str1 = substr($str, 0, 1);
				$str2 = substr($str, str_len($str) - 1);
				if ($str1 === ',' && $str2 !== ',') {
					$str = substr($str, 1);
				}
				else {
					if ($str1 !== ',' && $str2 === ',') {
						$str = substr($str, 0, -1);
					}
					else {
						if ($str1 === ',' && $str2 === ',') {
							$str = substr($str, 1);
							$str = substr($str, 0, -1);
						}
					}
				}
			}

			return $str;
		}
	}

	static public function get_link_seller_brand($brand_id = 0, $type = 0)
	{
		if ($type == 1) {
			$sql = 'SELECT GROUP_CONCAT(bid) AS brand_id FROM ' . $GLOBALS['ecs']->table('link_brand') . (' WHERE brand_id = \'' . $brand_id . '\'');
		}
		else {
			$sql = 'SELECT GROUP_CONCAT(brand_id) AS brand_id FROM ' . $GLOBALS['ecs']->table('link_brand') . (' WHERE bid = \'' . $brand_id . '\'');
		}

		return $GLOBALS['db']->getRow($sql);
	}

	static public function db_create_in($item_list, $field_name = '', $not = '')
	{
		if (!empty($not)) {
			$not = ' ' . $not;
		}

		if (empty($item_list)) {
			return $field_name . $not . ' IN (\'\') ';
		}
		else {
			if (!is_array($item_list)) {
				$item_list = explode(',', $item_list);
			}

			$item_list = array_unique($item_list);
			$item_list_tmp = '';

			foreach ($item_list as $item) {
				if ($item !== '') {
					$item = self::dsc_addslashes($item);
					$item_list_tmp .= $item_list_tmp ? ',\'' . $item . '\'' : '\'' . $item . '\'';
				}
			}

			if (empty($item_list_tmp)) {
				return $field_name . $not . ' IN (\'\') ';
			}
			else {
				return $field_name . $not . ' IN (' . $item_list_tmp . ') ';
			}
		}
	}

	static public function get_interface_file($dirname, $interface)
	{
		$arr = array();

		foreach ($interface as $key => $row) {
			$arr[$key] = $dirname . '/plugins/dscapi/interface/' . $row . '.php';
		}

		return $arr;
	}

	static public function __callStatic($method, $arguments)
	{
		return call_user_func_array(array(self, $method), $arguments);
	}
}


?>
