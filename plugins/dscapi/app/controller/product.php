<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\controller;

class product extends \app\model\productModel
{
	private $table;
	private $alias;
	private $product_select = array();
	private $select;
	private $product_id = 0;
	private $goods_id = 0;
	private $product_sn = '';
	private $bar_code = 0;
	private $warehouse_id = 0;
	private $area_id = 0;
	private $format = 'json';
	private $page_size = 10;
	private $page = 1;
	private $wehre_val;
	private $productLangList;
	private $sort_by;
	private $sort_order;

	public function __construct($where = array())
	{
		$this->product($where);
		$this->wehre_val = array('product_id' => $this->product_id, 'goods_id' => $this->goods_id, 'product_sn' => $this->product_sn, 'bar_code' => $this->bar_code, 'warehouse_id' => $this->warehouse_id, 'area_id' => $this->area_id);
		$this->where = \app\model\productModel::get_where($this->wehre_val);
		$this->select = \app\func\base::get_select_field($this->product_select);
	}

	public function product($where = array())
	{
		$this->product_id = $where['product_id'];
		$this->goods_id = $where['goods_id'];
		$this->product_sn = $where['product_sn'];
		$this->bar_code = $where['bar_code'];
		$this->warehouse_id = $where['warehouse_id'];
		$this->area_id = $where['area_id'];
		$this->product_select = $where['product_select'];
		$this->format = $where['format'];
		$this->page_size = $where['page_size'];
		$this->page = $where['page'];
		$this->sort_by = $where['sort_by'];
		$this->sort_order = $where['sort_order'];
		$this->productLangList = \languages\productLang::lang_product_request();
	}

	public function get_product_list($table)
	{
		$this->table = $table['product'];
		$result = \app\model\productModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\productModel::get_list_common_data($result, $this->page_size, $this->page, $this->productLangList, $this->format);
		return $result;
	}

	public function get_product_info($table)
	{
		$this->table = $table['product'];
		$result = \app\model\productModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\productModel::get_info_common_data_fs($result, $this->productLangList, $this->format);
		}
		else {
			$result = \app\model\productModel::get_info_common_data_f($this->productLangList, $this->format);
		}

		return $result;
	}

	public function get_product_insert($table)
	{
		$this->table = $table['product'];
		return \app\model\productModel::get_insert($this->table, $this->product_select, $this->format);
	}

	public function get_product_update($table)
	{
		$this->table = $table['product'];
		return \app\model\productModel::get_update($this->table, $this->product_select, $this->where, $this->format);
	}

	public function get_product_delete($table)
	{
		$this->table = $table['product'];
		return \app\model\productModel::get_delete($this->table, $this->where, $this->format);
	}
}

?>
