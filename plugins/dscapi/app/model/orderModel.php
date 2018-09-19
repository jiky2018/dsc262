<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\model;

abstract class orderModel extends \app\func\common
{
	const OS_UNCONFIRMED = 0;
	const OS_CONFIRMED = 1;
	const OS_CANCELED = 2;
	const OS_INVALID = 3;
	const OS_RETURNED = 4;
	const OS_SPLITED = 5;
	const OS_SPLITING_PART = 6;
	const OS_RETURNED_PART = 7;
	const OS_ONLY_REFOUND = 8;
	const PAY_ORDER = 0;
	const PAY_SURPLUS = 1;
	const PAY_APPLYGRADE = 2;
	const PAY_TOPUP = 3;
	const PAY_APPLYTEMP = 4;
	const PAY_WHOLESALE = 5;
	const SS_UNSHIPPED = 0;
	const SS_SHIPPED = 1;
	const SS_RECEIVED = 2;
	const SS_PREPARING = 3;
	const SS_SHIPPED_PART = 4;
	const SS_SHIPPED_ING = 5;
	const OS_SHIPPED_PART = 6;
	const PS_UNPAYED = 0;
	const PS_PAYING = 1;
	const PS_PAYED = 2;
	const PS_PAYED_PART = 3;
	const PS_REFOUND = 4;

	private $alias;

	public function __construct()
	{
	}

	public function get_where($val = array(), $alias = '')
	{
		$where = 1;
		$conditions = '';
		if (0 < $val['seller_id'] || 0 < $val['mobile']) {
			$conditions .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_info') . (' AS oi2 WHERE oi2.main_order_id = ' . $alias . 'order_id) = 0');
		}

		if ($val['seller_id'] != -1) {
			$conditions .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' AS og WHERE ' . $alias . 'order_id = og.order_id LIMIT 1)') . \app\func\base::db_create_in($val['seller_id']);
			$where .= \app\func\base::get_where(0, '', $conditions);
		}

		$where .= \app\func\base::get_where($val['order_id'], $alias . 'order_id');
		$where .= \app\func\base::get_where($val['order_sn'], $alias . 'order_sn');
		$where .= \app\func\base::get_where_time($val['start_add_time'], $alias . 'add_time');
		$where .= \app\func\base::get_where_time($val['end_add_time'], $alias . 'add_time', 1);
		$where .= \app\func\base::get_where_time($val['start_confirm_time'], $alias . 'confirm_time');
		$where .= \app\func\base::get_where_time($val['end_confirm_time'], $alias . 'confirm_time', 1);
		$where .= \app\func\base::get_where_time($val['start_pay_time'], $alias . 'pay_time');
		$where .= \app\func\base::get_where_time($val['end_pay_time'], $alias . 'pay_time', 1);
		$where .= \app\func\base::get_where_time($val['start_shipping_time'], $alias . 'shipping_time');
		$where .= \app\func\base::get_where_time($val['end_shipping_time'], $alias . 'shipping_time', 1);
		$where .= $this->get_take_time($val['start_take_time'], $val['end_take_time'], $alias);
		$where .= \app\func\base::get_where($val['order_status'], $alias . 'order_status');
		$where .= \app\func\base::get_where($val['shipping_status'], $alias . 'shipping_status');
		$where .= \app\func\base::get_where($val['pay_status'], $alias . 'pay_status');
		$where .= \app\func\base::get_where($val['mobile'], $alias . 'mobile');
		$where .= \app\func\base::get_where($val['rec_id'], $alias . 'rec_id');
		$where .= \app\func\base::get_where($val['goods_sn'], $alias . 'goods_sn');
		$where .= \app\func\base::get_where($val['goods_id'], $alias . 'goods_id');
		return $where;
	}

	private function get_take_time($start_take_time = '', $end_take_time = '', $alias = '')
	{
		$where = '';
		if (!empty($start_take_time) && $start_take_time != -1 || !empty($end_take_time) && $end_take_time != -1) {
			$where_action = '';

			if ($start_take_time) {
				$where_action .= ' AND oa.log_time >= \'' . $start_take_time . '\'';
			}

			if ($end_take_time) {
				$where_action .= ' AND oa.log_time <= \'' . $end_take_time . '\'';
			}

			$where_action .= $this->order_take_query_sql('finished', 'oa.');
			$where .= ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_action') . (' AS oa WHERE ' . $alias . 'order_id = oa.order_id ' . $where_action . ') > 0');
		}

		return $where;
	}

	private function order_take_query_sql($type = 'finished', $alias = '')
	{
		if ($type == 'finished') {
			return ' AND ' . $alias . 'order_status ' . db_create_in(array(self::OS_SPLITED)) . (' AND ' . $alias . 'shipping_status ') . db_create_in(array(self::SS_RECEIVED)) . (' AND ' . $alias . 'pay_status ') . db_create_in(array(self::PS_PAYED)) . ' ';
		}
	}

	public function get_select_list($table, $select, $where, $page_size, $page, $sort_by, $sort_order, $alias = '')
	{
		$table_alias = '';

		if (!empty($alias)) {
			$table_alias = ' AS ' . str_replace('.', '', $alias);
			$sort_by = $alias . $sort_by;
		}

		if ($table == 'order_info') {
			$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table($table) . (' AS oi2 WHERE oi2.main_order_id = ' . $alias . 'order_id) = 0 ');
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table($table) . $table_alias . ' WHERE ' . $where;
		$result['record_count'] = $GLOBALS['db']->getOne($sql);

		if ($sort_by) {
			$where .= ' ORDER BY ' . $sort_by . ' ' . $sort_order . ' ';
		}

		$where .= ' LIMIT ' . ($page - 1) * $page_size . (',' . $page_size);
		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . $table_alias . ' WHERE ' . $where;
		$result['list'] = $GLOBALS['db']->getAll($sql);
		return $result;
	}

	public function get_select_info($table, $select, $where, $alias = '')
	{
		$table_alias = '';

		if (!empty($alias)) {
			$table_alias = ' AS ' . str_replace('.', '', $alias);
		}

		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . $table_alias . ' WHERE ' . $where . ' LIMIT 1';
		$result = $GLOBALS['db']->getRow($sql);
		return $result;
	}

	public function get_insert($table, $select, $format)
	{
		$orderLang = \languages\orderLang::lang_order_insert();
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $select, 'INSERT');
		$id = $GLOBALS['db']->insert_id();
		$common_data = array('result' => empty($id) ? 'failure' : 'success', 'msg' => empty($id) ? $orderLang['msg_failure']['failure'] : $orderLang['msg_success']['success'], 'error' => empty($id) ? $orderLang['msg_failure']['error'] : $orderLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_update($table, $select, $where, $format)
	{
		$orderLang = \languages\orderLang::lang_order_update();

		if (strlen($where) != 1) {
			$info = $this->get_select_info($table, '*', $where);

			if (!$info) {
				$common_data = array('result' => 'failure', 'msg' => $orderLang['null_failure']['failure'], 'error' => $orderLang['null_failure']['error'], 'format' => $format);
			}
			else {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $select, 'UPDATE', $where);
				$common_data = array('result' => empty($select) ? 'failure' : 'success', 'msg' => empty($select) ? $orderLang['msg_failure']['failure'] : $orderLang['msg_success']['success'], 'error' => empty($select) ? $orderLang['msg_failure']['error'] : $orderLang['msg_success']['error'], 'format' => $format);
			}
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $orderLang['where_failure']['failure'], 'error' => $orderLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_delete($table, $where, $format)
	{
		$orderLang = \languages\orderLang::lang_order_delete();

		if (strlen($where) != 1) {
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;
			$GLOBALS['db']->query($sql);
			$common_data = array('result' => 'success', 'msg' => $orderLang['msg_success']['success'], 'error' => $orderLang['msg_success']['error'], 'format' => $format);
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $orderLang['where_failure']['failure'], 'error' => $orderLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_list_common_data($result, $page_size, $page, $orderLang, $format)
	{
		$common_data = array('page_size' => $page_size, 'page' => $page, 'result' => empty($result) ? 'failure' : 'success', 'msg' => empty($result) ? $orderLang['msg_failure']['failure'] : $orderLang['msg_success']['success'], 'error' => empty($result) ? $orderLang['msg_failure']['error'] : $orderLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result, 1);
		return $result;
	}

	public function get_info_common_data_fs($result, $orderLang, $format)
	{
		$common_data = array('result' => empty($result) ? 'failure' : 'success', 'msg' => empty($result) ? $orderLang['msg_failure']['failure'] : $orderLang['msg_success']['success'], 'error' => empty($result) ? $orderLang['msg_failure']['error'] : $orderLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result);
		return $result;
	}

	public function get_info_common_data_f($orderLang, $format)
	{
		$result = array();
		$common_data = array('result' => 'failure', 'msg' => $orderLang['where_failure']['failure'], 'error' => $orderLang['where_failure']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result);
		return $result;
	}

	public function get_confirmorder($table, $order_select, $format)
	{
		$orderLang = \languages\orderLang::lang_order_confirmorder();
		$result = array();

		if (!empty($order_select)) {
			define('GMTIME_UTC', gmtime());

			if ($order_select['orderSn'] == '') {
				$common_data = array('code' => $orderLang['ordersn_failure']['code'], 'message' => $orderLang['ordersn_failure']['message'], 'format' => $format);
				\app\func\common::common($common_data);
				$result = \app\func\common::data_back('', 2);
				return $result;
			}

			if ($order_select['expressNo'] == '') {
				$common_data = array('code' => $orderLang['expressno_failure']['code'], 'message' => $orderLang['expressno_failure']['message'], 'format' => $format);
				\app\func\common::common($common_data);
				$result = \app\func\common::data_back('', 2);
				return $result;
			}

			if ($order_select['expressCode'] == '') {
				$common_data = array('code' => $orderLang['code_failure']['code'], 'message' => $orderLang['code_failure']['message'], 'format' => $format);
				\app\func\common::common($common_data);
				$result = \app\func\common::data_back('', 2);
				return $result;
			}

			$invoice_no = trim($order_select['expressNo']);
			$shipping_info = \app\func\common::get_shipping_info($order_select['expressCode']);

			if (empty($shipping_info)) {
				$common_data = array('code' => $orderLang['shipping_failure']['code'], 'message' => $orderLang['shipping_failure']['message'], 'format' => $format);
				\app\func\common::common($common_data);
				$result = \app\func\common::data_back('', 2);
				return $result;
			}

			$sql = 'SELECT oi.postscript,oi.best_time,oi.mobile,oi.tel,oi.zipcode,oi.email,oi.address,oi.consignee,oi.how_oos,oi.add_time,oi.order_id,oi.user_id,oi.country,oi.province,oi.city,oi.district,oi.agency_id,oi.insure_fee,oi.shipping_fee,oi.order_sn FROM' . $GLOBALS['ecs']->table('order_info') . 'AS oi LEFT JOIN' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON oi.order_id = og.order_id LEFT JOIN' . $GLOBALS['ecs']->table('order_cloud') . ' AS oc ON og.rec_id = oc.rec_id WHERE oc.apiordersn = \'' . $order_select['orderSn'] . '\'';
			$delivery = $GLOBALS['db']->getRow($sql);
			$delivery['shipping_id'] = $shipping_info['shipping_id'];
			$delivery['shipping_name'] = $shipping_info['shipping_name'];
			mt_srand((double) microtime() * 1000000);
			$delivery['delivery_sn'] = date('YmdHi') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
			$delivery_sn = $delivery['delivery_sn'];
			$delivery['action_user'] = $orderLang['conf_message']['action_user'];
			$delivery['update_time'] = GMTIME_UTC;
			$delivery_time = $delivery['update_time'];
			$delivery['status'] = 2;
			$filter_fileds = array('order_sn', 'add_time', 'user_id', 'how_oos', 'shipping_id', 'shipping_fee', 'consignee', 'address', 'country', 'province', 'city', 'district', 'email', 'zipcode', 'tel', 'mobile', 'best_time', 'postscript', 'insure_fee', 'agency_id', 'delivery_sn', 'action_user', 'update_time', 'status', 'order_id', 'shipping_name');
			$_delivery = array();

			foreach ($filter_fileds as $value) {
				$_delivery[$value] = $delivery[$value];
			}

			$query = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('delivery_order'), $_delivery, 'INSERT', '', 'SILENT');
			$delivery_id = $GLOBALS['db']->insert_id();

			if (0 < $delivery_id) {
				$goods_list = array();
				$sql = 'SELECT o.goods_number,o.goods_id,o.product_id,o.goods_name,o.goods_sn, o.goods_attr,g.is_real, IFNULL(b.brand_name, \'\') AS brand_name, p.product_sn ' . 'FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS o ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('products') . ' AS p ON o.product_id = p.product_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON o.goods_id = g.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON g.brand_id = b.brand_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_cloud') . ' AS oc ON o.rec_id = oc.rec_id ' . 'WHERE o.order_id = \'' . $_delivery[order_id] . '\' AND oc.apiordersn = \'' . $order_select['orderSn'] . '\'';
				$goods_list = $GLOBALS['db']->getAll($sql);

				if (!empty($goods_list)) {
					$split_action_note = '';

					foreach ($goods_list as $key => $val) {
						$delivery_goods = array('delivery_id' => $delivery_id, 'goods_id' => $val['goods_id'], 'product_id' => empty($val['product_id']) ? 0 : $val['product_id'], 'product_sn' => $val['product_sn'], 'goods_id' => $val['goods_id'], 'goods_name' => addslashes($val['goods_name']), 'brand_name' => addslashes($val['brand_name']), 'goods_sn' => $val['goods_sn'], 'send_number' => $val['goods_number'], 'parent_id' => 0, 'is_real' => $val['is_real'], 'goods_attr' => addslashes($val['goods_attr']));
						$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('delivery_goods'), $delivery_goods, 'INSERT', '', 'SILENT');
						$split_action_note .= sprintf($orderLang['conf_message']['split_action_note'], $val['goods_sn'], $val['goods_number']) . '<br/>';
						$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . "\r\n\t\t\t\t\t\tSET send_number = '" . $val['goods_number'] . "'\r\n\t\t\t\t\t\tWHERE order_id = '" . $_delivery['order_id'] . "'\r\n\t\t\t\t\t\tAND goods_id = '" . $val['goods_id'] . '\' ';
						$GLOBALS['db']->query($sql);
					}
				}
			}
			else {
				$common_data = array('code' => $orderLang['delivery_failure']['code'], 'message' => $orderLang['delivery_failure']['message'], 'format' => $format);
				\app\func\common::common($common_data);
				$result = \app\func\common::data_back('', 2);
				return $result;
			}
		}
		else {
			$common_data = array('code' => $orderLang['data_null']['code'], 'message' => $orderLang['data_null']['message'], 'format' => $format);
			\app\func\common::common($common_data);
			$result = \app\func\common::data_back('', 2);
			return $result;
		}

		$_note = sprintf($orderLang['conf_message']['order_ship_delivery'], $delivery['delivery_sn']) . '<br/>';
		$sql = 'SELECT COUNT(rec_id) FROM' . $GLOBALS['ecs']->table('order_goods') . 'WHERE order_id = \'' . $_delivery['order_id'] . '\' AND goods_number > send_number';
		$order_finish = $GLOBALS['db']->getOne($sql);
		$shipping_status = SS_SHIPPED_ING;
		$sql = 'SELECT mobile,email,consignee,order_id,order_status,pay_status,insure_fee,pay_id,invoice_no,pay_time,order_sn,extension_id,extension_code,goods_amount,user_id FROM' . $GLOBALS['ecs']->table('order_info') . 'WHERE order_id = \'' . $_delivery['order_id'] . '\'';
		$order = $GLOBALS['db']->getRow($sql);
		if ($order['order_status'] != OS_CONFIRMED && $order['order_status'] != OS_SPLITED && $order['order_status'] != OS_SPLITING_PART) {
			$arr['order_status'] = OS_CONFIRMED;
			$arr['confirm_time'] = GMTIME_UTC;
		}

		$arr['order_status'] = $order_finish ? OS_SPLITED : OS_SPLITING_PART;
		$arr['shipping_status'] = $shipping_status;
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $arr, 'UPDATE', 'order_id = \'' . $_delivery['order_id'] . '\'');
		$action_note = $split_action_note;
		$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('order_action') . ' (order_id, action_user, order_status, shipping_status, pay_status,  action_note, log_time) ' . 'VALUES (\'' . $_delivery['order_id'] . '\',\'' . $orderLang['conf_message']['action_user'] . '\',\'' . $arr['order_status'] . ('\',\'' . $shipping_status . '\',\'') . $order['pay_status'] . ('\',\'' . $action_note . '\',\'') . gmtime() . '\')';
		$GLOBALS['db']->query($sql);
		$order_id = $_delivery['order_id'];
		unset($_delivery);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('delivery_order') . (' WHERE delivery_id = \'' . $delivery_id . '\' LIMIT 0, 1');
		$delivery_order = $GLOBALS['db']->getRow($sql);

		if ($delivery_order) {
			$delivery_order['agency_name'] = '';
			$delivery_order['formated_insure_fee'] = \app\func\common::price_format($delivery_order['insure_fee'], false);
			$delivery_order['formated_shipping_fee'] = \app\func\common::price_format($delivery_order['shipping_fee'], false);
			$delivery_order['formated_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $delivery_order['add_time']);
			$delivery_order['formated_update_time'] = local_date($GLOBALS['_CFG']['time_format'], $delivery_order['update_time']);
		}

		if (0 < $delivery_order['user_id']) {
			$sql = 'SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $delivery_order['user_id'] . '\'';
			$delivery_order['user_name'] = $GLOBALS['db']->getOne($sql);
		}

		$sql = 'SELECT concat(IFNULL(c.region_name, \'\'), \'  \', IFNULL(p.region_name, \'\'), ' . '\'  \', IFNULL(t.region_name, \'\'), \'  \', IFNULL(d.region_name, \'\')) AS region ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS c ON o.country = c.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON o.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON o.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON o.district = d.region_id ' . 'WHERE o.order_id = \'' . $delivery_order['order_id'] . '\'';
		$delivery_order['region'] = $GLOBALS['db']->getOne($sql);
		$order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
		$goods_sql = "SELECT *\r\n\t\t\t  FROM " . $GLOBALS['ecs']->table('delivery_goods') . "\r\n\t\t\t  WHERE delivery_id = " . $delivery_order['delivery_id'];
		$goods_list = $GLOBALS['db']->getAll($goods_sql);
		$_delivery['invoice_no'] = $invoice_no;
		$_delivery['status'] = 0;
		$query = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('delivery_order'), $_delivery, 'UPDATE', 'delivery_id = ' . $delivery_id, 'SILENT');
		$sql = "SELECT COUNT(delivery_id)\r\n                FROM " . $GLOBALS['ecs']->table('delivery_order') . ("\r\n                WHERE order_id = '" . $order_id . "'\r\n                AND status = 2 ");
		$sum = $GLOBALS['db']->getOne($sql);
		$order_finish = 0;

		if (empty($sum)) {
			$order_finish = 1;
		}
		else {
			$sql = "SELECT COUNT(delivery_id)\r\n            FROM " . $GLOBALS['ecs']->table('delivery_order') . ("\r\n            WHERE order_id = '" . $order_id . "'\r\n            AND status <> 1 ");
			$_sum = $GLOBALS['db']->getOne($sql);

			if ($_sum == $sum) {
				$order_finish = -2;
			}
			else {
				$order_finish = -1;
			}
		}

		$shipping_status = $order_finish == 1 ? SS_SHIPPED : SS_SHIPPED_PART;
		$arr['shipping_status'] = $shipping_status;
		$arr['shipping_time'] = GMTIME_UTC;
		$arr['invoice_no'] = $invoice_no;

		if (empty($order['pay_time'])) {
			$arr['pay_time'] = gmtime();
		}

		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $arr, 'UPDATE', 'order_id = \'' . $order_id . '\'');
		$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('order_action') . ' (order_id, action_user, order_status, shipping_status, pay_status,  action_note, log_time) ' . ('VALUES (\'' . $order_id . '\',\'') . $orderLang['conf_message']['action_user'] . '\',\'' . OS_CONFIRMED . ('\',\'' . $shipping_status . '\',\'') . $order['pay_status'] . ('\',\'' . $action_note . '\',\'') . gmtime() . '\')';
		$GLOBALS['db']->query($sql);

		if ($order_finish) {
			if (0 < $order['user_id']) {
				$integral = \app\func\common::integral_to_give($order);
				\app\func\common::log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($orderLang['conf_message']['order_gift_integral'], $order['order_sn']));
				\app\func\common::send_order_bonus($order_id);
				\app\func\common::send_order_coupons($order);
			}

			$cfg = $_CFG['send_ship_email'];

			if ($cfg == '1') {
				$order['invoice_no'] = $invoice_no;
				$sql = 'SELECT template_subject, is_html, template_content FROM ' . $GLOBALS['ecs']->table('mail_templates') . ' WHERE template_code = \'deliver_notice\'';
				$tpl = $GLOBALS['db']->GetRow($sql);
				$GLOBALS['smarty']->assign('order', $order);
				$GLOBALS['smarty']->assign('send_time', local_date($_CFG['time_format']));
				$GLOBALS['smarty']->assign('shop_name', $_CFG['shop_name']);
				$GLOBALS['smarty']->assign('send_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
				$GLOBALS['smarty']->assign('sent_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
				$GLOBALS['smarty']->assign('confirm_url', $GLOBALS['ecs']->url() . 'user.php?act=order_detail&order_id=' . $order['order_id']);
				$GLOBALS['smarty']->assign('send_msg_url', $GLOBALS['ecs']->url() . 'user.php?act=message_list&order_id=' . $order['order_id']);
				$content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
				send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
			}

			if ($GLOBALS['_CFG']['sms_order_shipped'] == '1' && $order['mobile'] != '') {
				$sql = 'SELECT shop_name FROM' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = 0';
				$shop_name = $GLOBALS['db']->getOne($sql);
				$sql = 'SELECT user_name FROM' . $GLOBALS['ecs']->table('users') . 'WHERE user_id = \'' . $order['user_id'] . '\'';
				$user_name = $GLOBALS['db']->getOne($sql);
				$smsParams = array('shop_name' => $shop_name, 'shopname' => $shop_name, 'user_name' => $user_name, 'username' => $user_name, 'consignee' => $order['consignee'], 'order_sn' => $order['order_sn'], 'ordersn' => $order['order_sn'], 'invoice_no' => $invoice_no, 'invoiceno' => $invoice_no, 'mobile_phone' => $order['mobile'], 'mobilephone' => $order['mobile']);

				if ($GLOBALS['_CFG']['sms_type'] == 0) {
					huyi_sms($smsParams, 'sms_order_shipped');
				}
				else if (1 <= $GLOBALS['_CFG']['sms_type']) {
					$result_sms = sms_ali($smsParams, 'sms_order_shipped');

					if ($result_sms) {
						$resp = $GLOBALS['ecs']->ali_yu($result_sms);
					}
				}
			}
		}

		$common_data = array('code' => $orderLang['msg_success']['code'], 'message' => $orderLang['msg_success']['message'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back('', 2);
		return $result;
	}
}

?>
