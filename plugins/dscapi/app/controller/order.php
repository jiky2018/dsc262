<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\controller;

class order extends \app\model\orderModel
{
	private $table;
	private $alias = '';
	private $order_select = array();
	private $select;
	private $seller_id = 0;
	private $order_id = 0;
	private $order_sn = 0;
	private $start_add_time = 0;
	private $end_add_time = 0;
	private $start_confirm_time = 0;
	private $end_confirm_time = 0;
	private $start_pay_time = 0;
	private $end_pay_time = 0;
	private $start_shipping_time = 0;
	private $end_shipping_time = 0;
	private $start_take_time = 0;
	private $end_take_time = 0;
	private $order_status = 0;
	private $shipping_status = 0;
	private $pay_status = 0;
	private $mobile = 0;
	private $goods_sn = '';
	private $goods_id = 0;
	private $rec_id = 0;
	private $format = 'json';
	private $page_size = 10;
	private $page = 1;
	private $wehre_val;
	private $goodsLangList;
	private $sort_by = 'order_id';
	private $sort_order;

	public function __construct($where = array())
	{
		$this->order($where);
		$this->wehre_val = array('seller_id' => $this->seller_id, 'order_id' => $this->order_id, 'order_sn' => $this->order_sn, 'mobile' => $this->mobile, 'rec_id' => $this->rec_id, 'goods_sn' => $this->goods_sn, 'goods_id' => $this->goods_id, 'start_add_time' => $this->start_add_time, 'end_add_time' => $this->end_add_time, 'start_confirm_time' => $this->start_confirm_time, 'end_confirm_time' => $this->end_confirm_time, 'start_pay_time' => $this->start_pay_time, 'end_pay_time' => $this->end_pay_time, 'start_shipping_time' => $this->start_shipping_time, 'end_shipping_time' => $this->end_shipping_time, 'start_take_time' => $this->start_take_time, 'end_take_time' => $this->end_take_time, 'order_status' => $this->order_status, 'shipping_status' => $this->shipping_status, 'pay_status' => $this->pay_status);
		if (0 < $this->seller_id || 0 < $this->mobile || (-1 < $this->start_take_time || -1 < $this->end_take_time)) {
			$this->alias = 'o.';
		}

		$this->where = \app\model\orderModel::get_where($this->wehre_val, $this->alias);
		$this->select = \app\func\base::get_select_field($this->order_select);
	}

	public function order($where = array())
	{
		$this->seller_id = $where['seller_id'];
		$this->order_id = $where['order_id'];
		$this->order_sn = $where['order_sn'];
		$this->start_add_time = $where['start_add_time'];
		$this->end_add_time = $where['end_add_time'];
		$this->start_confirm_time = $where['start_confirm_time'];
		$this->end_confirm_time = $where['end_confirm_time'];
		$this->start_pay_time = $where['start_pay_time'];
		$this->end_pay_time = $where['end_pay_time'];
		$this->start_shipping_time = $where['start_shipping_time'];
		$this->end_shipping_time = $where['end_shipping_time'];
		$this->start_take_time = $where['start_take_time'];
		$this->end_take_time = $where['end_take_time'];
		$this->order_status = $where['order_status'];
		$this->shipping_status = $where['shipping_status'];
		$this->pay_status = $where['pay_status'];
		$this->mobile = $where['mobile'];
		$this->rec_id = $where['rec_id'];
		$this->goods_sn = $where['goods_sn'];
		$this->goods_id = $where['goods_id'];
		$this->order_select = $where['order_select'];
		$this->format = $where['format'];
		$this->page_size = $where['page_size'];
		$this->page = $where['page'];
		$this->sort_by = !empty($where['sort_by']) ? $where['sort_by'] : $this->sort_by;
		$this->sort_order = $where['sort_order'];
		$this->goodsLangList = \languages\orderLang::lang_order_request();
	}

	public function get_order_list($table)
	{
		$this->table = $table['order'];
		$result = \app\model\orderModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order, $this->alias);
		$result = \app\model\orderModel::get_list_common_data($result, $this->page_size, $this->page, $this->goodsLangList, $this->format);
		return $result;
	}

	public function get_order_info($table)
	{
		$this->table = $table['order'];
		$result = \app\model\orderModel::get_select_info($this->table, $this->select, $this->where, $this->alias);

		if (strlen($this->where) != 1) {
			$result = \app\model\orderModel::get_info_common_data_fs($result, $this->goodsLangList, $this->format);
		}
		else {
			$result = \app\model\orderModel::get_info_common_data_f($this->goodsLangList, $this->format);
		}

		return $result;
	}

	public function get_order_insert($table)
	{
		$this->table = $table['order'];
		return \app\model\orderModel::get_insert($this->table, $this->order_select, $this->format);
	}

	public function get_order_update($table)
	{
		$this->table = $table['order'];
		return \app\model\orderModel::get_update($this->table, $this->order_select, $this->where, $this->format);
	}

	public function get_order_delete($table)
	{
		$this->table = $table['order'];
		return \app\model\orderModel::get_delete($this->table, $this->where, $this->format);
	}

	public function get_order_goods_list($table)
	{
		$this->table = $table['goods'];
		$result = \app\model\orderModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\orderModel::get_list_common_data($result, $this->page_size, $this->page, $this->goodsLangList, $this->format);
		return $result;
	}

	public function get_order_goods_info($table)
	{
		$this->table = $table['goods'];
		$result = \app\model\orderModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\orderModel::get_info_common_data_fs($result, $this->goodsLangList, $this->format);
		}
		else {
			$result = \app\model\orderModel::get_info_common_data_f($this->goodsLangList, $this->format);
		}

		return $result;
	}

	public function get_order_goods_insert($table)
	{
		$this->table = $table['goods'];
		return \app\model\orderModel::get_insert($this->table, $this->order_select, $this->format);
	}

	public function get_order_goods_update($table)
	{
		$this->table = $table['goods'];
		return \app\model\orderModel::get_update($this->table, $this->order_select, $this->where, $this->format);
	}

	public function get_order_goods_delete($table)
	{
		$this->table = $table['goods'];
		return \app\model\orderModel::get_delete($this->table, $this->where, $this->format);
	}

	public function get_order_confirmorder($table)
	{
		$this->table = $table['goods'];
		return \app\model\orderModel::get_confirmorder($this->table, $this->order_select, $this->format);
	}
}

?>
