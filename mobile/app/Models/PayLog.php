<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class PayLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'pay_log';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	protected $fillable = array('order_id', 'order_amount', 'order_type', 'is_paid', 'openid', 'transid');
	protected $guarded = array();

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getOrderAmount()
	{
		return $this->order_amount;
	}

	public function getOrderType()
	{
		return $this->order_type;
	}

	public function getIsPaid()
	{
		return $this->is_paid;
	}

	public function getOpenid()
	{
		return $this->openid;
	}

	public function getTransid()
	{
		return $this->transid;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setOrderAmount($value)
	{
		$this->order_amount = $value;
		return $this;
	}

	public function setOrderType($value)
	{
		$this->order_type = $value;
		return $this;
	}

	public function setIsPaid($value)
	{
		$this->is_paid = $value;
		return $this;
	}

	public function setOpenid($value)
	{
		$this->openid = $value;
		return $this;
	}

	public function setTransid($value)
	{
		$this->transid = $value;
		return $this;
	}
}

?>
