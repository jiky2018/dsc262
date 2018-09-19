<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\controller;

class user extends \app\model\userModel
{
	private $table;
	private $alias;
	private $user_select = array();
	private $select;
	private $user_id = 0;
	private $user_name = 0;
	private $mobile = '';
	private $rank_id = '';
	private $address_id = '';
	private $format = 'json';
	private $page_size = 10;
	private $page = 1;
	private $wehre_val;
	private $userLangList;
	private $sort_by;
	private $sort_order;

	public function __construct($where = array())
	{
		$this->user($where);
		$this->wehre_val = array('user_id' => $this->user_id, 'user_name' => $this->user_name, 'mobile' => $this->mobile, 'rank_id' => $this->rank_id, 'address_id' => $this->address_id);
		$this->where = \app\model\userModel::get_where($this->wehre_val);
		$this->select = \app\func\base::get_select_field($this->user_select);
	}

	public function user($where = array())
	{
		$this->user_id = $where['user_id'];
		$this->user_name = $where['user_name'];
		$this->mobile = $where['mobile'];
		$this->rank_id = $where['rank_id'];
		$this->address_id = $where['address_id'];
		$this->user_select = $where['user_select'];
		$this->format = $where['format'];
		$this->page_size = $where['page_size'];
		$this->page = $where['page'];
		$this->sort_by = $where['sort_by'];
		$this->sort_order = $where['sort_order'];
		$this->userLangList = \languages\userLang::lang_user_request();
	}

	public function get_user_list($table)
	{
		$this->table = $table['users'];
		$result = \app\model\userModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\userModel::get_list_common_data($result, $this->page_size, $this->page, $this->userLangList, $this->format);
		return $result;
	}

	public function get_user_info($table)
	{
		$this->table = $table['users'];
		$result = \app\model\userModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\userModel::get_info_common_data_fs($result, $this->userLangList, $this->format);
		}
		else {
			$result = \app\model\userModel::get_info_common_data_f($this->userLangList, $this->format);
		}

		return $result;
	}

	public function get_user_insert($table)
	{
		$this->table = $table['users'];
		return \app\model\userModel::get_insert($this->table, $this->user_select, $this->format);
	}

	public function get_user_update($table)
	{
		$this->table = $table['users'];
		$data = $this->user_select;
		$where = '';
		$is_not = 0;

		if ($this->user_id != -1) {
			if (!empty($data['user_name']) && !empty($data['mobile'])) {
				$where = '(user_name = \'' . $data['user_name'] . '\' OR mobile_phone = \'' . $data['mobile'] . '\') AND user_id <> ' . $this->user_id;
			}
			else if (!empty($data['user_name'])) {
				$where = 'user_name = \'' . $data['user_name'] . '\' AND user_id <> ' . $this->user_id;
			}
			else if (!empty($data['mobile'])) {
				$where = 'mobile_phone = \'' . $data['mobile'] . '\' AND user_id <> ' . $this->user_id;
			}
		}
		else {
			if (!empty($data['user_name']) && !empty($data['mobile'])) {
				$where = 'user_name = \'' . $data['user_name'] . '\' OR mobile_phone = \'' . $data['mobile'] . '\'';
			}
			else if (!empty($data['user_name'])) {
				$where = 'user_name = \'' . $data['user_name'] . '\'';
			}
			else if (!empty($data['mobile'])) {
				$where = 'mobile_phone = \'' . $data['mobile'] . '\'';
			}
		}

		if (!empty($where)) {
			$user_id = \app\func\common::get_reference_only($this->table, $where, array('user_id'));

			if ($user_id) {
				$is_not = 1;
			}

			if ($is_not) {
				$userLang = \languages\userLang::lang_user_update();
				$common_data = array('result' => 'failure', 'msg' => $userLang['is_user_name']['failure'], 'error' => $userLang['is_user_name']['error'], 'format' => $this->format);
				\app\func\common::common($common_data);
				return \app\func\common::data_back();
			}
		}

		return \app\model\userModel::get_update($this->table, $this->user_select, $this->where, $this->format);
	}

	public function get_user_delete($table)
	{
		$this->table = $table['users'];
		return \app\model\userModel::get_delete($this->table, $this->where, $this->format);
	}

	public function get_user_rank_list($table)
	{
		$this->table = $table['rank'];
		$result = \app\model\userModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\userModel::get_list_common_data($result, $this->page_size, $this->page, $this->userLangList, $this->format);
		return $result;
	}

	public function get_user_rank_info($table)
	{
		$this->table = $table['rank'];
		$result = \app\model\userModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\userModel::get_info_common_data_fs($result, $this->userLangList, $this->format);
		}
		else {
			$result = \app\model\userModel::get_info_common_data_f($this->userLangList, $this->format);
		}

		return $result;
	}

	public function get_user_rank_insert($table)
	{
		$this->table = $table['rank'];
		return \app\model\userModel::get_insert($this->table, $this->user_select, $this->format);
	}

	public function get_user_rank_update($table)
	{
		$this->table = $table['rank'];
		return \app\model\userModel::get_update($this->table, $this->user_select, $this->where, $this->format);
	}

	public function get_user_rank_delete($table)
	{
		$this->table = $table['address'];
		return \app\model\userModel::get_delete($this->table, $this->where, $this->format);
	}

	public function get_user_address_list($table)
	{
		$this->table = $table['address'];
		$result = \app\model\userModel::get_select_list($this->table, $this->select, $this->where, $this->page_size, $this->page, $this->sort_by, $this->sort_order);
		$result = \app\model\userModel::get_list_common_data($result, $this->page_size, $this->page, $this->userLangList, $this->format);
		return $result;
	}

	public function get_user_address_info($table)
	{
		$this->table = $table['rank'];
		$result = \app\model\userModel::get_select_info($this->table, $this->select, $this->where);

		if (strlen($this->where) != 1) {
			$result = \app\model\userModel::get_info_common_data_fs($result, $this->userLangList, $this->format);
		}
		else {
			$result = \app\model\userModel::get_info_common_data_f($this->userLangList, $this->format);
		}

		return $result;
	}

	public function get_user_address_insert($table)
	{
		$this->table = $table['address'];
		return \app\model\userModel::get_insert($this->table, $this->user_select, $this->format);
	}

	public function get_user_address_update($table)
	{
		$this->table = $table['address'];
		return \app\model\userModel::get_update($this->table, $this->user_select, $this->where, $this->format);
	}

	public function get_user_address_delete($table)
	{
		$this->table = $table['address'];
		return \app\model\userModel::get_delete($this->table, $this->where, $this->format);
	}
}

?>
