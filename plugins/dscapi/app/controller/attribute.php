<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\controller;

class attribute extends \app\model\attributeModel
{
	private $table;
	private $alias;
	private $attribute_select = array();
	private $select;
	private $attribute_id = 0;
	private $parent_id = 0;
	private $attribute_name = '';
	private $attribute_type = 0;
	private $format = 'json';
	private $page_size = 10;
	private $page = 1;
	private $wehre_val;
	private $attributeLangList;
	private $seller_type = 0;
	private $sort_by;
	private $sort_order;

	public function __construct($where = array())
	{
		$this->attribute($where);
		$this->wehre_val = array('seller_type' => $this->seller_type, 'seller_id' => $this->seller_id, 'attr_id' => $this->attr_id, 'cat_id' => $this->cat_id, 'attr_name' => $this->attr_name, 'attr_type' => $this->attr_type);
		$this->where = \app\model\attributeModel::get_where($this->wehre_val);
		$this->select = \app\func\base::get_select_field($this->attribute_select);
	}

	public function attribute($where = array())
	{
		$this->seller_type = $where['seller_type'];
		$this->seller_id = $where['seller_id'];
		$this->attr_id = $where['attr_id'];
		$this->cat_id = $where['cat_id'];
		$this->attr_name = $where['attr_name'];
		$this->attr_type = $where['attr_type'];
		$this->attribute_select = $where['attribute_select'];
		$this->format = $where['format'];
		$this->page_size = $where['page_size'];
		$this->page = $where['page'];
		$this->sort_by = $where['sort_by'];
		$this->sort_order = $where['sort_order'];
		$this->attributeLangList = \languages\attributeLang::lang_attribute_request();
	}

	public function get_goodstype_list($table)
	{
		$this->table = $table['goodstype'];
		$result = \app\model\attributeModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\attributeModel::get_list_common_data($result, $this->page_size, $this->page, $this->attributeLangList, $this->format);
		return $result;
	}

	public function get_goodstype_info($table)
	{
		$this->table = $table['goodstype'];
		$result = \app\model\attributeModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\attributeModel::get_info_common_data_fs($result, $this->attributeLangList, $this->format);
		}
		else {
			$result = \app\model\attributeModel::get_info_common_data_f($this->attributeLangList, $this->format);
		}

		return $result;
	}

	public function get_goodstype_insert($table)
	{
		$this->table = $table['goodstype'];
		return \app\model\attributeModel::get_insert($this->table, $this->attribute_select, $this->format);
	}

	public function get_goodstype_update($table)
	{
		$this->table = $table['goodstype'];
		return \app\model\attributeModel::get_update($this->table, $this->attribute_select, $this->where, $this->format);
	}

	public function get_goodstype_delete($table)
	{
		$this->table = $table['attribute'];
		return \app\model\attributeModel::get_delete($this->table, $this->where, $this->format);
	}

	public function get_attribute_list($table)
	{
		$this->table = $table['attribute'];
		$result = \app\model\attributeModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\attributeModel::get_list_common_data($result, $this->page_size, $this->page, $this->attributeLangList, $this->format);
		return $result;
	}

	public function get_attribute_info($table)
	{
		$this->table = $table['attribute'];
		$result = \app\model\attributeModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\attributeModel::get_info_common_data_fs($result, $this->attributeLangList, $this->format);
		}
		else {
			$result = \app\model\attributeModel::get_info_common_data_f($this->attributeLangList, $this->format);
		}

		return $result;
	}

	public function get_attribute_insert($table)
	{
		$this->table = $table['attribute'];
		return \app\model\attributeModel::get_insert($this->table, $this->attribute_select, $this->format);
	}

	public function get_attribute_update($table)
	{
		$this->table = $table['attribute'];
		return \app\model\attributeModel::get_update($this->table, $this->attribute_select, $this->where, $this->format);
	}

	public function get_attribute_delete($table)
	{
		$this->table = $table['attribute'];
		return \app\model\attributeModel::get_delete($this->table, $this->where, $this->format);
	}
}

?>
