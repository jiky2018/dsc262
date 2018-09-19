<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class QrpayDiscounts extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'qrpay_discounts';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'min_amount', 'discount_amount', 'max_discount_amount', 'status', 'add_time');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getMinAmount()
	{
		return $this->min_amount;
	}

	public function getDiscountAmount()
	{
		return $this->discount_amount;
	}

	public function getMaxDiscountAmount()
	{
		return $this->max_discount_amount;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setMinAmount($value)
	{
		$this->min_amount = $value;
		return $this;
	}

	public function setDiscountAmount($value)
	{
		$this->discount_amount = $value;
		return $this;
	}

	public function setMaxDiscountAmount($value)
	{
		$this->max_discount_amount = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
