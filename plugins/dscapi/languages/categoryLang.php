<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace languages;

class categoryLang
{
	static private $lang_update_conf;
	static private $lang_insert_conf;

	public function __construct()
	{
	}

	static public function lang_category_request()
	{
		self::$lang_insert_conf = array(
			'msg_success'   => array('success' => '成功获取数据', 'error' => 0),
			'msg_failure'   => array('failure' => '数据为空值', 'error' => 1),
			'where_failure' => array('failure' => '条件为空', 'error' => 2)
			);
		return self::$lang_insert_conf;
	}

	static public function lang_category_insert()
	{
		self::$lang_insert_conf = array(
			'msg_success' => array('success' => '数据提交成功', 'error' => 0),
			'msg_failure' => array('failure' => '数据提交失败', 'error' => 1)
			);
		return self::$lang_insert_conf;
	}

	static public function lang_category_update()
	{
		self::$lang_update_conf = array(
			'msg_success'   => array('success' => '数据更新成功', 'error' => 0),
			'msg_failure'   => array('failure' => '数据为空', 'error' => 1),
			'where_failure' => array('failure' => '条件为空', 'error' => 2),
			'null_failure'  => array('failure' => '数据不存在', 'error' => 4)
			);
		return self::$lang_update_conf;
	}

	static public function lang_category_delete()
	{
		self::$lang_update_conf = array(
			'msg_success'   => array('success' => '删除成功', 'error' => 0),
			'msg_failure'   => array('failure' => '删除失败', 'error' => 1),
			'where_failure' => array('failure' => '条件为空', 'error' => 2)
			);
		return self::$lang_update_conf;
	}

	static public function __callStatic($method, $arguments)
	{
		return call_user_func_array(array(self, $method), $arguments);
	}
}


?>
