<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class SellerTemplateApply extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_template_apply';
	protected $primaryKey = 'apply_id';
	public $timestamps = false;
	protected $fillable = array('apply_sn', 'ru_id', 'temp_id', 'temp_code', 'pay_status', 'apply_status', 'total_amount', 'pay_fee', 'add_time', 'pay_time', 'pay_id');
	protected $guarded = array();

	public function getApplySn()
	{
		return $this->apply_sn;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getTempId()
	{
		return $this->temp_id;
	}

	public function getTempCode()
	{
		return $this->temp_code;
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

	public function getPayFee()
	{
		return $this->pay_fee;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getPayTime()
	{
		return $this->pay_time;
	}

	public function getPayId()
	{
		return $this->pay_id;
	}

	public function setApplySn($value)
	{
		$this->apply_sn = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setTempId($value)
	{
		$this->temp_id = $value;
		return $this;
	}

	public function setTempCode($value)
	{
		$this->temp_code = $value;
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

	public function setPayFee($value)
	{
		$this->pay_fee = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
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
}

?>
