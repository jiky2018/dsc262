<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class BaitiaoLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'baitiao_log';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	protected $fillable = array('baitiao_id', 'user_id', 'use_date', 'repay_date', 'order_id', 'repayed_date', 'is_repay', 'is_stages', 'stages_total', 'stages_one_price', 'yes_num', 'is_refund', 'pay_num');
	protected $guarded = array();

	public function getBaitiaoId()
	{
		return $this->baitiao_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getUseDate()
	{
		return $this->use_date;
	}

	public function getRepayDate()
	{
		return $this->repay_date;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getRepayedDate()
	{
		return $this->repayed_date;
	}

	public function getIsRepay()
	{
		return $this->is_repay;
	}

	public function getIsStages()
	{
		return $this->is_stages;
	}

	public function getStagesTotal()
	{
		return $this->stages_total;
	}

	public function getStagesOnePrice()
	{
		return $this->stages_one_price;
	}

	public function getYesNum()
	{
		return $this->yes_num;
	}

	public function getIsRefund()
	{
		return $this->is_refund;
	}

	public function getPayNum()
	{
		return $this->pay_num;
	}

	public function setBaitiaoId($value)
	{
		$this->baitiao_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setUseDate($value)
	{
		$this->use_date = $value;
		return $this;
	}

	public function setRepayDate($value)
	{
		$this->repay_date = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setRepayedDate($value)
	{
		$this->repayed_date = $value;
		return $this;
	}

	public function setIsRepay($value)
	{
		$this->is_repay = $value;
		return $this;
	}

	public function setIsStages($value)
	{
		$this->is_stages = $value;
		return $this;
	}

	public function setStagesTotal($value)
	{
		$this->stages_total = $value;
		return $this;
	}

	public function setStagesOnePrice($value)
	{
		$this->stages_one_price = $value;
		return $this;
	}

	public function setYesNum($value)
	{
		$this->yes_num = $value;
		return $this;
	}

	public function setIsRefund($value)
	{
		$this->is_refund = $value;
		return $this;
	}

	public function setPayNum($value)
	{
		$this->pay_num = $value;
		return $this;
	}
}

?>
