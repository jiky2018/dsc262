<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Complaint extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'complaint';
	protected $primaryKey = 'complaint_id';
	public $timestamps = false;
	protected $fillable = array('order_id', 'order_sn', 'user_id', 'user_name', 'ru_id', 'shop_name', 'title_id', 'complaint_content', 'add_time', 'complaint_handle_time', 'admin_id', 'appeal_messg', 'appeal_time', 'end_handle_time', 'end_admin_id', 'end_handle_messg', 'complaint_state', 'complaint_active');
	protected $guarded = array();

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getOrderSn()
	{
		return $this->order_sn;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getShopName()
	{
		return $this->shop_name;
	}

	public function getTitleId()
	{
		return $this->title_id;
	}

	public function getComplaintContent()
	{
		return $this->complaint_content;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getComplaintHandleTime()
	{
		return $this->complaint_handle_time;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function getAppealMessg()
	{
		return $this->appeal_messg;
	}

	public function getAppealTime()
	{
		return $this->appeal_time;
	}

	public function getEndHandleTime()
	{
		return $this->end_handle_time;
	}

	public function getEndAdminId()
	{
		return $this->end_admin_id;
	}

	public function getEndHandleMessg()
	{
		return $this->end_handle_messg;
	}

	public function getComplaintState()
	{
		return $this->complaint_state;
	}

	public function getComplaintActive()
	{
		return $this->complaint_active;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setOrderSn($value)
	{
		$this->order_sn = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setUserName($value)
	{
		$this->user_name = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setShopName($value)
	{
		$this->shop_name = $value;
		return $this;
	}

	public function setTitleId($value)
	{
		$this->title_id = $value;
		return $this;
	}

	public function setComplaintContent($value)
	{
		$this->complaint_content = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setComplaintHandleTime($value)
	{
		$this->complaint_handle_time = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}

	public function setAppealMessg($value)
	{
		$this->appeal_messg = $value;
		return $this;
	}

	public function setAppealTime($value)
	{
		$this->appeal_time = $value;
		return $this;
	}

	public function setEndHandleTime($value)
	{
		$this->end_handle_time = $value;
		return $this;
	}

	public function setEndAdminId($value)
	{
		$this->end_admin_id = $value;
		return $this;
	}

	public function setEndHandleMessg($value)
	{
		$this->end_handle_messg = $value;
		return $this;
	}

	public function setComplaintState($value)
	{
		$this->complaint_state = $value;
		return $this;
	}

	public function setComplaintActive($value)
	{
		$this->complaint_active = $value;
		return $this;
	}
}

?>
