<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class QrpayLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'qrpay_log';
	public $timestamps = false;
	protected $fillable = array('pay_order_sn', 'pay_amount', 'qrpay_id', 'ru_id', 'pay_user_id', 'openid', 'payment_code', 'trade_no', 'notify_data', 'pay_status', 'is_settlement', 'pay_desc', 'add_time');
	protected $guarded = array();

	public function getPayOrderSn()
	{
		return $this->pay_order_sn;
	}

	public function getPayAmount()
	{
		return $this->pay_amount;
	}

	public function getQrpayId()
	{
		return $this->qrpay_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getPayUserId()
	{
		return $this->pay_user_id;
	}

	public function getOpenid()
	{
		return $this->openid;
	}

	public function getPaymentCode()
	{
		return $this->payment_code;
	}

	public function getTradeNo()
	{
		return $this->trade_no;
	}

	public function getNotifyData()
	{
		return $this->notify_data;
	}

	public function getPayStatus()
	{
		return $this->pay_status;
	}

	public function getIsSettlement()
	{
		return $this->is_settlement;
	}

	public function getPayDesc()
	{
		return $this->pay_desc;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setPayOrderSn($value)
	{
		$this->pay_order_sn = $value;
		return $this;
	}

	public function setPayAmount($value)
	{
		$this->pay_amount = $value;
		return $this;
	}

	public function setQrpayId($value)
	{
		$this->qrpay_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setPayUserId($value)
	{
		$this->pay_user_id = $value;
		return $this;
	}

	public function setOpenid($value)
	{
		$this->openid = $value;
		return $this;
	}

	public function setPaymentCode($value)
	{
		$this->payment_code = $value;
		return $this;
	}

	public function setTradeNo($value)
	{
		$this->trade_no = $value;
		return $this;
	}

	public function setNotifyData($value)
	{
		$this->notify_data = $value;
		return $this;
	}

	public function setPayStatus($value)
	{
		$this->pay_status = $value;
		return $this;
	}

	public function setIsSettlement($value)
	{
		$this->is_settlement = $value;
		return $this;
	}

	public function setPayDesc($value)
	{
		$this->pay_desc = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
