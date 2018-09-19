<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class UserAccount extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'user_account';
	public $timestamps = false;
	protected $fillable = array('user_id', 'admin_user', 'amount', 'deposit_fee', 'add_time', 'paid_time', 'admin_note', 'user_note', 'process_type', 'payment', 'pay_id', 'is_paid', 'complaint_details', 'complaint_imges', 'complaint_time');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getAdminUser()
	{
		return $this->admin_user;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function getDepositFee()
	{
		return $this->deposit_fee;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getPaidTime()
	{
		return $this->paid_time;
	}

	public function getAdminNote()
	{
		return $this->admin_note;
	}

	public function getUserNote()
	{
		return $this->user_note;
	}

	public function getProcessType()
	{
		return $this->process_type;
	}

	public function getPayment()
	{
		return $this->payment;
	}

	public function getPayId()
	{
		return $this->pay_id;
	}

	public function getIsPaid()
	{
		return $this->is_paid;
	}

	public function getComplaintDetails()
	{
		return $this->complaint_details;
	}

	public function getComplaintImges()
	{
		return $this->complaint_imges;
	}

	public function getComplaintTime()
	{
		return $this->complaint_time;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setAdminUser($value)
	{
		$this->admin_user = $value;
		return $this;
	}

	public function setAmount($value)
	{
		$this->amount = $value;
		return $this;
	}

	public function setDepositFee($value)
	{
		$this->deposit_fee = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setPaidTime($value)
	{
		$this->paid_time = $value;
		return $this;
	}

	public function setAdminNote($value)
	{
		$this->admin_note = $value;
		return $this;
	}

	public function setUserNote($value)
	{
		$this->user_note = $value;
		return $this;
	}

	public function setProcessType($value)
	{
		$this->process_type = $value;
		return $this;
	}

	public function setPayment($value)
	{
		$this->payment = $value;
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

	public function setComplaintDetails($value)
	{
		$this->complaint_details = $value;
		return $this;
	}

	public function setComplaintImges($value)
	{
		$this->complaint_imges = $value;
		return $this;
	}

	public function setComplaintTime($value)
	{
		$this->complaint_time = $value;
		return $this;
	}
}

?>
