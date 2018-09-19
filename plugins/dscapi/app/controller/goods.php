<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\controller;

class goods extends \app\model\goodsModel
{
	private $table;
	private $alias;
	private $goods_select = array();
	private $select;
	private $seller_id = 0;
	private $brand_id = 0;
	private $cat_id = 0;
	private $user_cat = 0;
	private $goods_id = 0;
	private $goods_sn = '';
	private $bar_code = '';
	private $w_id = 0;
	private $a_id = 0;
	private $region_id = 0;
	private $region_sn = '';
	private $img_id = 0;
	private $attr_id = 0;
	private $goods_attr_id = 0;
	private $tid = '';
	private $seller_type = 0;
	private $format = 'json';
	private $page_size = 10;
	private $page = 1;
	private $wehre_val;
	private $goodsLangList;
	private $sort_by;
	private $sort_order;

	public function __construct($where = array())
	{
		$this->goods($where);
		$this->wehre_val = array('seller_id' => $this->seller_id, 'brand_id' => $this->brand_id, 'cat_id' => $this->cat_id, 'user_cat' => $this->user_cat, 'goods_id' => $this->goods_id, 'goods_sn' => $this->goods_sn, 'bar_code' => $this->bar_code, 'w_id' => $this->w_id, 'a_id' => $this->a_id, 'region_id' => $this->region_id, 'region_sn' => $this->region_sn, 'img_id' => $this->img_id, 'attr_id' => $this->attr_id, 'goods_attr_id' => $this->goods_attr_id, 'tid' => $this->tid, 'seller_type' => $this->seller_type);
		$this->where = \app\model\goodsModel::get_where($this->wehre_val);
		$this->select = \app\func\base::get_select_field($this->goods_select);
	}

	public function goods($where = array())
	{
		$this->seller_type = $where['seller_type'];
		$this->table = $where['table'];
		$this->seller_id = $where['seller_id'];
		$this->brand_id = $where['brand_id'];
		$this->cat_id = $where['cat_id'];
		$this->user_cat = $where['user_cat'];
		$this->goods_id = $where['goods_id'];
		$this->goods_sn = $where['goods_sn'];
		$this->bar_code = $where['bar_code'];
		$this->w_id = $where['w_id'];
		$this->a_id = $where['a_id'];
		$this->region_id = $where['region_id'];
		$this->region_sn = $where['region_sn'];
		$this->img_id = $where['img_id'];
		$this->attr_id = $where['attr_id'];
		$this->goods_attr_id = $where['goods_attr_id'];
		$this->tid = $where['tid'];
		$this->goods_select = $where['goods_select'];
		$this->format = $where['format'];
		$this->page_size = $where['page_size'];
		$this->page = $where['page'];
		$this->sort_by = $where['sort_by'];
		$this->sort_order = $where['sort_order'];
		$this->goodsLangList = \languages\goodsLang::lang_goods_request();
	}

	public function get_goods_list($table)
	{
		$this->table = $table['goods'];
		$result = \app\model\goodsModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\goodsModel::get_list_common_data($result, $this->page_size, $this->page, $this->goodsLangList, $this->format);
		return $result;
	}

	public function get_goods_info($table)
	{
		$this->table = $table['goods'];
		$result = \app\model\goodsModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\goodsModel::get_info_common_data_fs($result, $this->goodsLangList, $this->format);
		}
		else {
			$result = \app\model\goodsModel::get_info_common_data_f($this->goodsLangList, $this->format);
		}

		return $result;
	}

	public function get_goods_insert($table)
	{
		$this->table = $table['goods'];
		return \app\model\goodsModel::get_insert($this->table, $this->goods_select, $this->format);
	}

	public function get_goods_update($table)
	{
		$this->table = $table['goods'];
		return \app\model\goodsModel::get_update($this->table, $this->goods_select, $this->where, $this->format);
	}

	public function get_goods_delete($table)
	{
		$this->table = $table['goods'];
		return \app\model\goodsModel::get_delete($this->table, $this->where, $this->format);
	}

	public function get_goods_warehouse_list($table)
	{
		$this->table = $table['warehouse'];
		$result = \app\model\goodsModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\goodsModel::get_list_common_data($result, $this->page_size, $this->page, $this->goodsLangList, $this->format);
		return $result;
	}

	public function get_goods_warehouse_info($table)
	{
		$this->table = $table['warehouse'];
		$result = \app\model\goodsModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\goodsModel::get_info_common_data_fs($result, $this->goodsLangList, $this->format);
		}
		else {
			$result = \app\model\goodsModel::get_info_common_data_f($this->goodsLangList, $this->format);
		}

		return $result;
	}

	public function get_goods_warehouse_insert($table)
	{
		$this->table = $table['warehouse'];
		return \app\model\goodsModel::get_insert($this->table, $this->goods_select, $this->format);
	}

	public function get_goods_warehouse_update($table)
	{
		$this->table = $table['warehouse'];
		return \app\model\goodsModel::get_update($this->table, $this->goods_select, $this->where, $this->format);
	}

	public function get_goods_warehouse_delete($table)
	{
		$this->table = $table['warehouse'];
		return \app\model\goodsModel::get_delete($this->table, $this->where, $this->format);
	}

	public function get_goods_area_list($table)
	{
		$this->table = $table['area'];
		$result = \app\model\goodsModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\goodsModel::get_list_common_data($result, $this->page_size, $this->page, $this->goodsLangList, $this->format);
		return $result;
	}

	public function get_goods_area_info($table)
	{
		$this->table = $table['area'];
		$result = \app\model\goodsModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\goodsModel::get_info_common_data_fs($result, $this->goodsLangList, $this->format);
		}
		else {
			$result = \app\model\goodsModel::get_info_common_data_f($this->goodsLangList, $this->format);
		}

		return $result;
	}

	public function get_goods_area_insert($table)
	{
		$this->table = $table['area'];
		return \app\model\goodsModel::get_insert($this->table, $this->goods_select, $this->format);
	}

	public function get_goods_area_update($table)
	{
		$this->table = $table['area'];
		return \app\model\goodsModel::get_update($this->table, $this->goods_select, $this->where, $this->format);
	}

	public function get_goods_area_delete($table)
	{
		$this->table = $table['area'];
		return \app\model\goodsModel::get_delete($this->table, $this->where, $this->format);
	}

	public function get_goods_gallery_list($table)
	{
		$this->table = $table['gallery'];
		$result = \app\model\goodsModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\goodsModel::get_list_common_data($result, $this->page_size, $this->page, $this->goodsLangList, $this->format);
		return $result;
	}

	public function get_goods_gallery_info($table)
	{
		$this->table = $table['gallery'];
		$result = \app\model\goodsModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\goodsModel::get_info_common_data_fs($result, $this->goodsLangList, $this->format);
		}
		else {
			$result = \app\model\goodsModel::get_info_common_data_f($this->goodsLangList, $this->format);
		}

		return $result;
	}

	public function get_goods_gallery_insert($table)
	{
		$this->table = $table['gallery'];
		return \app\model\goodsModel::get_insert($this->table, $this->goods_select, $this->format);
	}

	public function get_goods_gallery_update($table)
	{
		$this->table = $table['gallery'];
		return \app\model\goodsModel::get_update($this->table, $this->goods_select, $this->where, $this->format);
	}

	public function get_goods_gallery_delete($table)
	{
		$this->table = $table['gallery'];
		return \app\model\goodsModel::get_delete($this->table, $this->where, $this->format);
	}

	public function get_goods_attr_list($table)
	{
		$this->table = $table['attr'];
		$result = \app\model\goodsModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\goodsModel::get_list_common_data($result, $this->page_size, $this->page, $this->goodsLangList, $this->format);
		return $result;
	}

	public function get_goods_attr_info($table)
	{
		$this->table = $table['attr'];
		$result = \app\model\goodsModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\goodsModel::get_info_common_data_fs($result, $this->goodsLangList, $this->format);
		}
		else {
			$result = \app\model\goodsModel::get_info_common_data_f($this->goodsLangList, $this->format);
		}

		return $result;
	}

	public function get_goods_attr_insert($table)
	{
		$this->table = $table['attr'];
		return \app\model\goodsModel::get_insert($this->table, $this->goods_select, $this->format);
	}

	public function get_goods_attr_update($table)
	{
		$this->table = $table['attr'];
		return \app\model\goodsModel::get_update($this->table, $this->goods_select, $this->where, $this->format);
	}

	public function get_goods_attr_delete($table)
	{
		$this->table = $table['attr'];
		return \app\model\goodsModel::get_delete($this->table, $this->where, $this->format);
	}

	public function get_goods_freight_list($table)
	{
		if ($this->seller_id != -1) {
			$this->where = 'gt.ru_id = ' . $this->seller_id . ' GROUP BY gt.tid';
		}

		$join_on = array('', 'tid|tid', 'tid|tid');
		$this->table = $table;
		$result = \app\model\goodsModel::get_join_select_list($this->table, $this->select, $this->where, $join_on);
		$result = \app\model\goodsModel::get_list_common_data($result, $this->page_size, $this->page, $this->goodsLangList, $this->format);
		return $result;
	}

	public function get_goods_freight_info($table)
	{
		if ($this->tid != -1) {
			$this->where = 'gt.tid = ' . $this->tid . ' GROUP BY gt.tid';
		}

		$join_on = array('', 'tid|tid', 'tid|tid');
		$this->table = $table;
		$result = \app\model\goodsModel::get_join_select_info($this->table, $this->select, $this->where, $join_on);

		if (strlen($this->where) != 1) {
			$result = \app\model\goodsModel::get_info_common_data_fs($result, $this->goodsLangList, $this->format);
		}
		else {
			$result = \app\model\goodsModel::get_info_common_data_f($this->goodsLangList, $this->format);
		}

		return $result;
	}

	public function get_goods_freight_insert($table)
	{
		$this->table = $table;
		return \app\model\goodsModel::get_more_insert($this->table, $this->goods_select, $this->format);
	}

	public function get_goods_freight_update($table)
	{
		$this->table = $table;
		return \app\model\goodsModel::get_more_update($this->table, $this->goods_select, $this->where, $this->format);
	}

	public function get_goods_freight_delete($table)
	{
		$this->table = $table;
		return \app\model\goodsModel::get_more_delete($this->table, $this->where, $this->format);
	}

	public function get_goods_batchinsert($table)
	{
		$this->table = $table['goods'];
		return \app\model\goodsModel::get_goods_batch_insert($this->table, $this->goods_select, $this->format);
	}

	public function get_goods_notification_update($table)
	{
		$this->table = $table;
		return \app\model\goodsModel::get_goodsnotification_update($this->table, $this->goods_select, $this->format);
	}
}

?>
