<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class OrderDelayed extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'order_delayed';
	protected $primaryKey = 'delayed_id';
	public $timestamps = false;
	protected $fillable = array('order_id', 'apply_day', 'apply_time', 'review_status', 'review_time', 'review_admin');
	protected $guarded = array();

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getApplyDay()
	{
		return $this->apply_day;
	}

	public function getApplyTime()
	{
		return $this->apply_time;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewTime()
	{
		return $this->review_time;
	}

	public function getReviewAdmin()
	{
		return $this->review_admin;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setApplyDay($value)
	{
		$this->apply_day = $value;
		return $this;
	}

	public function setApplyTime($value)
	{
		$this->apply_time = $value;
		return $this;
	}

	public function setReviewStatus($value)
	{
		$this->review_status = $value;
		return $this;
	}

	public function setReviewTime($value)
	{
		$this->review_time = $value;
		return $this;
	}

	public function setReviewAdmin($value)
	{
		$this->review_admin = $value;
		return $this;
	}
}

?>
