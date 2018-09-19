<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace languages;

class orderLang
{
	static private $lang_update_conf;
	static private $lang_insert_conf;

	public function __construct()
	{
	}

	static public function lang_order_request()
	{
		self::$lang_insert_conf = array(
			'msg_success'   => array('success' => '成功获取数据', 'error' => 0),
			'msg_failure'   => array('failure' => '数据为空值', 'error' => 1),
			'where_failure' => array('failure' => '条件为空', 'error' => 2)
			);
		return self::$lang_insert_conf;
	}

	static public function lang_order_insert()
	{
		self::$lang_insert_conf = array(
			'msg_success' => array('success' => '数据提交成功', 'error' => 0),
			'msg_failure' => array('failure' => '数据提交失败', 'error' => 1)
			);
		return self::$lang_insert_conf;
	}

	static public function lang_order_update()
	{
		self::$lang_update_conf = array(
			'msg_success'   => array('success' => '数据更新成功', 'error' => 0),
			'msg_failure'   => array('failure' => '数据为空', 'error' => 1),
			'where_failure' => array('failure' => '条件为空', 'error' => 2),
			'null_failure'  => array('failure' => '数据不存在', 'error' => 4)
			);
		return self::$lang_update_conf;
	}

	static public function lang_order_delete()
	{
		self::$lang_update_conf = array(
			'msg_success'   => array('success' => '删除成功', 'error' => 0),
			'msg_failure'   => array('failure' => '删除失败', 'error' => 1),
			'where_failure' => array('failure' => '条件为空', 'error' => 2)
			);
		return self::$lang_update_conf;
	}

	static public function lang_order_confirmorder()
	{
		self::$lang_update_conf = array(
			'msg_success'       => array('code' => 10000, 'message' => '发货成功'),
			'ordersn_failure'   => array('code' => 1, 'message' => '订单号不能为空'),
			'expressno_failure' => array('message' => '快递单号不能为空', 'code' => 2),
			'code_failure'      => array('message' => '快递不能为空', 'code' => 3),
			'shipping_failure'  => array('message' => '快递不支持', 'code' => 4),
			'delivery_failure'  => array('message' => '发货失败，请重试', 'code' => 5),
			'data_null'         => array('message' => '发货信息不能为空', 'code' => 6),
			'conf_message'      => array('split_action_note' => '【商品货号：%s，发货：%s件】', 'order_ship_delivery' => '发货单流水号：【%s】', 'action_user' => '第三方商家操作', 'order_gift_integral' => '订单 %s 赠送的积分')
			);
		return self::$lang_update_conf;
	}

	static public function __callStatic($method, $arguments)
	{
		return call_user_func_array(array(self, $method), $arguments);
	}
}


?>
