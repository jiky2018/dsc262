<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\controller;

class warehouse extends \app\model\warehouseModel
{
	private $table;
	private $alias;
	private $warehouse_select = array();
	private $select;
	private $region_id = 0;
	private $region_code = 0;
	private $parent_id = 0;
	private $region_name = '';
	private $region_type = 0;
	private $format = 'json';
	private $page_size = 10;
	private $page = 1;
	private $wehre_val;
	private $warehouseLangList;
	private $sort_by;
	private $sort_order;

	public function __construct($where = array())
	{
		$this->warehouse($where);
		$this->wehre_val = array('region_id' => $this->region_id, 'region_code' => $this->region_code, 'parent_id' => $this->parent_id, 'region_type' => $this->region_type, 'region_name' => $this->region_name);
		$this->where = \app\model\warehouseModel::get_where($this->wehre_val);
		$this->select = \app\func\base::get_select_field($this->warehouse_select);
	}

	public function warehouse($where = array())
	{
		$this->region_id = $where['region_id'];
		$this->region_code = $where['region_code'];
		$this->parent_id = $where['parent_id'];
		$this->region_type = $where['region_type'];
		$this->region_name = $where['region_name'];
		$this->warehouse_select = $where['warehouse_select'];
		$this->format = $where['format'];
		$this->page_size = $where['page_size'];
		$this->page = $where['page'];
		$this->sort_by = $where['sort_by'];
		$this->sort_order = $where['sort_order'];
		$this->warehouseLangList = \languages\warehouseLang::lang_warehouse_request();
	}

	public function get_warehouse_list($table)
	{
		$this->table = $table['warehouse'];
		$result = \app\model\warehouseModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\warehouseModel::get_list_common_data($result, $this->page_size, $this->page, $this->warehouseLangList, $this->format);
		return $result;
	}

	public function get_warehouse_info($table)
	{
		$this->table = $table['warehouse'];
		$result = \app\model\warehouseModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\warehouseModel::get_info_common_data_fs($result, $this->warehouseLangList, $this->format);
		}
		else {
			$result = \app\model\warehouseModel::get_info_common_data_f($this->warehouseLangList, $this->format);
		}

		return $result;
	}

	public function get_warehouse_insert($table)
	{
		$this->table = $table['warehouse'];
		return \app\model\warehouseModel::get_insert($this->table, $this->warehouse_select, $this->format);
	}

	public function get_warehouse_update($table)
	{
		$this->table = $table['warehouse'];
		return \app\model\warehouseModel::get_update($this->table, $this->warehouse_select, $this->where, $this->format);
	}

	public function get_warehouse_delete($table)
	{
		$this->table = $table['warehouse'];
		return \app\model\warehouseModel::get_delete($this->table, $this->where, $this->format);
	}
}

?>
