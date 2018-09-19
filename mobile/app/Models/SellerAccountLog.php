<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class SellerAccountLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_account_log';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	protected $fillable = array('admin_id', 'real_id', 'ru_id', 'order_id', 'amount', 'frozen_money', 'certificate_img', 'deposit_mode', 'log_type', 'apply_sn', 'pay_id', 'pay_time', 'admin_note', 'add_time', 'seller_note', 'is_paid', 'percent_value');
	protected $guarded = array();

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function getRealId()
	{
		return $this->real_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function getFrozenMoney()
	{
		return $this->frozen_money;
	}

	public function getCertificateImg()
	{
		return $this->certificate_img;
	}

	public function getDepositMode()
	{
		return $this->deposit_mode;
	}

	public function getLogType()
	{
		return $this->log_type;
	}

	public function getApplySn()
	{
		return $this->apply_sn;
	}

	public function getPayId()
	{
		return $this->pay_id;
	}

	public function getPayTime()
	{
		return $this->pay_time;
	}

	public function getAdminNote()
	{
		return $this->admin_note;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getSellerNote()
	{
		return $this->seller_note;
	}

	public function getIsPaid()
	{
		return $this->is_paid;
	}

	public function getPercentValue()
	{
		return $this->percent_value;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}

	public function setRealId($value)
	{
		$this->real_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setAmount($value)
	{
		$this->amount = $value;
		return $this;
	}

	public function setFrozenMoney($value)
	{
		$this->frozen_money = $value;
		return $this;
	}

	public function setCertificateImg($value)
	{
		$this->certificate_img = $value;
		return $this;
	}

	public function setDepositMode($value)
	{
		$this->deposit_mode = $value;
		return $this;
	}

	public function setLogType($value)
	{
		$this->log_type = $value;
		return $this;
	}

	public function setApplySn($value)
	{
		$this->apply_sn = $value;
		return $this;
	}

	public function setPayId($value)
	{
		$this->pay_id = $value;
		return $this;
	}

	public function setPayTime($value)
	{
		$this->pay_time = $value;
		return $this;
	}

	public function setAdminNote($value)
	{
		$this->admin_note = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setSellerNote($value)
	{
		$this->seller_note = $value;
		return $this;
	}

	public function setIsPaid($value)
	{
		$this->is_paid = $value;
		return $this;
	}

	public function setPercentValue($value)
	{
		$this->percent_value = $value;
		return $this;
	}
}

?>
