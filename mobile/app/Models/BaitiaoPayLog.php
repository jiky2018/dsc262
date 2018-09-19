<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class BaitiaoPayLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'baitiao_pay_log';
	public $timestamps = false;
	protected $fillable = array('baitiao_id', 'log_id', 'stages_num', 'stages_price', 'is_pay', 'pay_id', 'pay_code', 'add_time', 'pay_time');
	protected $guarded = array();

	public function getBaitiaoId()
	{
		return $this->baitiao_id;
	}

	public function getLogId()
	{
		return $this->log_id;
	}

	public function getStagesNum()
	{
		return $this->stages_num;
	}

	public function getStagesPrice()
	{
		return $this->stages_price;
	}

	public function getIsPay()
	{
		return $this->is_pay;
	}

	public function getPayId()
	{
		return $this->pay_id;
	}

	public function getPayCode()
	{
		return $this->pay_code;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getPayTime()
	{
		return $this->pay_time;
	}

	public function setBaitiaoId($value)
	{
		$this->baitiao_id = $value;
		return $this;
	}

	public function setLogId($value)
	{
		$this->log_id = $value;
		return $this;
	}

	public function setStagesNum($value)
	{
		$this->stages_num = $value;
		return $this;
	}

	public function setStagesPrice($value)
	{
		$this->stages_price = $value;
		return $this;
	}

	public function setIsPay($value)
	{
		$this->is_pay = $value;
		return $this;
	}

	public function setPayId($value)
	{
		$this->pay_id = $value;
		return $this;
	}

	public function setPayCode($value)
	{
		$this->pay_code = $value;
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
}

?>
