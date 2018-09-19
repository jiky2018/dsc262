<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class SellerApplyInfo extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_apply_info';
	protected $primaryKey = 'apply_id';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'grade_id', 'apply_sn', 'pay_status', 'apply_status', 'total_amount', 'payable_amount', 'refund_price', 'back_price', 'fee_num', 'pay_fee', 'entry_criteria', 'add_time', 'is_confirm', 'pay_time', 'pay_id', 'is_paid', 'confirm_time', 'reply_seller', 'valid');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getGradeId()
	{
		return $this->grade_id;
	}

	public function getApplySn()
	{
		return $this->apply_sn;
	}

	public function getPayStatus()
	{
		return $this->pay_status;
	}

	public function getApplyStatus()
	{
		return $this->apply_status;
	}

	public function getTotalAmount()
	{
		return $this->total_amount;
	}

	public function getPayableAmount()
	{
		return $this->payable_amount;
	}

	public function getRefundPrice()
	{
		return $this->refund_price;
	}

	public function getBackPrice()
	{
		return $this->back_price;
	}

	public function getFeeNum()
	{
		return $this->fee_num;
	}

	public function getPayFee()
	{
		return $this->pay_fee;
	}

	public function getEntryCriteria()
	{
		return $this->entry_criteria;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getIsConfirm()
	{
		return $this->is_confirm;
	}

	public function getPayTime()
	{
		return $this->pay_time;
	}

	public function getPayId()
	{
		return $this->pay_id;
	}

	public function getIsPaid()
	{
		return $this->is_paid;
	}

	public function getConfirmTime()
	{
		return $this->confirm_time;
	}

	public function getReplySeller()
	{
		return $this->reply_seller;
	}

	public function getValid()
	{
		return $this->valid;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setGradeId($value)
	{
		$this->grade_id = $value;
		return $this;
	}

	public function setApplySn($value)
	{
		$this->apply_sn = $value;
		return $this;
	}

	public function setPayStatus($value)
	{
		$this->pay_status = $value;
		return $this;
	}

	public function setApplyStatus($value)
	{
		$this->apply_status = $value;
		return $this;
	}

	public function setTotalAmount($value)
	{
		$this->total_amount = $value;
		return $this;
	}

	public function setPayableAmount($value)
	{
		$this->payable_amount = $value;
		return $this;
	}

	public function setRefundPrice($value)
	{
		$this->refund_price = $value;
		return $this;
	}

	public function setBackPrice($value)
	{
		$this->back_price = $value;
		return $this;
	}

	public function setFeeNum($value)
	{
		$this->fee_num = $value;
		return $this;
	}

	public function setPayFee($value)
	{
		$this->pay_fee = $value;
		return $this;
	}

	public function setEntryCriteria($value)
	{
		$this->entry_criteria = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setIsConfirm($value)
	{
		$this->is_confirm = $value;
		return $this;
	}

	public function setPayTime($value)
	{
		$this->pay_time = $value;
		return $this;
	}

	public function setPayId($value)
	{
		$this->pay_id = $value;
		return $this;
	}

	public function setIsPaid($value)
	{
		$this->is_paid = $value;
		return $this;
	}

	public function setConfirmTime($value)
	{
		$this->confirm_time = $value;
		return $this;
	}

	public function setReplySeller($value)
	{
		$this->reply_seller = $value;
		return $this;
	}

	public function setValid($value)
	{
		$this->valid = $value;
		return $this;
	}
}

?>
