<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class QrpayManage extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'qrpay_manage';
	public $timestamps = false;
	protected $fillable = array('qrpay_name', 'type', 'amount', 'discount_id', 'tag_id', 'qrpay_status', 'ru_id', 'qrpay_code', 'add_time');
	protected $guarded = array();

	public function getQrpayName()
	{
		return $this->qrpay_name;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function getDiscountId()
	{
		return $this->discount_id;
	}

	public function getTagId()
	{
		return $this->tag_id;
	}

	public function getQrpayStatus()
	{
		return $this->qrpay_status;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getQrpayCode()
	{
		return $this->qrpay_code;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setQrpayName($value)
	{
		$this->qrpay_name = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setAmount($value)
	{
		$this->amount = $value;
		return $this;
	}

	public function setDiscountId($value)
	{
		$this->discount_id = $value;
		return $this;
	}

	public function setTagId($value)
	{
		$this->tag_id = $value;
		return $this;
	}

	public function setQrpayStatus($value)
	{
		$this->qrpay_status = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setQrpayCode($value)
	{
		$this->qrpay_code = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
