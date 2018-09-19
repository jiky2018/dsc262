<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\controller;

class brand extends \app\model\brandModel
{
	private $table;
	private $alias;
	private $brand_select = array();
	private $select;
	private $brand_id = 0;
	private $brand_name = '';
	private $format = 'json';
	private $page_size = 10;
	private $page = 1;
	private $wehre_val;
	private $brandLangList;
	private $sort_by;
	private $sort_order;

	public function __construct($where = array())
	{
		$this->brand($where);
		$this->wehre_val = array('brand_id' => $this->brand_id, 'brand_name' => $this->brand_name);
		$this->where = \app\model\brandModel::get_where($this->wehre_val);
		$this->select = \app\func\base::get_select_field($this->brand_select);
	}

	public function brand($where = array())
	{
		$this->brand_id = $where['brand_id'];
		$this->brand_name = $where['brand_name'];
		$this->brand_select = $where['brand_select'];
		$this->format = $where['format'];
		$this->page_size = $where['page_size'];
		$this->page = $where['page'];
		$this->sort_by = $where['sort_by'];
		$this->sort_order = $where['sort_order'];
		$this->brandLangList = \languages\brandLang::lang_brand_request();
	}

	public function get_brand_list($table)
	{
		$this->table = $table['brand'];
		$result = \app\model\brandModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\brandModel::get_list_common_data($result, $this->page_size, $this->page, $this->brandLangList, $this->format);
		return $result;
	}

	public function get_brand_info($table)
	{
		$this->table = $table['brand'];
		$result = \app\model\brandModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\brandModel::get_info_common_data_fs($result, $this->brandLangList, $this->format);
		}
		else {
			$result = \app\model\brandModel::get_info_common_data_f($this->brandLangList, $this->format);
		}

		return $result;
	}

	public function get_brand_insert($table)
	{
		$this->table = $table['brand'];
		return \app\model\brandModel::get_insert($this->table, $this->brand_select, $this->format);
	}

	public function get_brand_update($table)
	{
		$this->table = $table['brand'];
		return \app\model\brandModel::get_update($this->table, $this->brand_select, $this->where, $this->format);
	}

	public function get_brand_delete($table)
	{
		$this->table = $table['brand'];
		return \app\model\brandModel::get_delete($this->table, $this->where, $this->format);
	}
}

?>
