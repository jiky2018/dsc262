<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Payment extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'payment';
	protected $primaryKey = 'pay_id';
	public $timestamps = false;
	protected $fillable = array('pay_code', 'pay_name', 'pay_fee', 'pay_desc', 'pay_order', 'pay_config', 'enabled', 'is_cod', 'is_online');
	protected $guarded = array();

	public function getPayCode()
	{
		return $this->pay_code;
	}

	public function getPayName()
	{
		return $this->pay_name;
	}

	public function getPayFee()
	{
		return $this->pay_fee;
	}

	public function getPayDesc()
	{
		return $this->pay_desc;
	}

	public function getPayOrder()
	{
		return $this->pay_order;
	}

	public function getPayConfig()
	{
		return $this->pay_config;
	}

	public function getEnabled()
	{
		return $this->enabled;
	}

	public function getIsCod()
	{
		return $this->is_cod;
	}

	public function getIsOnline()
	{
		return $this->is_online;
	}

	public function setPayCode($value)
	{
		$this->pay_code = $value;
		return $this;
	}

	public function setPayName($value)
	{
		$this->pay_name = $value;
		return $this;
	}

	public function setPayFee($value)
	{
		$this->pay_fee = $value;
		return $this;
	}

	public function setPayDesc($value)
	{
		$this->pay_desc = $value;
		return $this;
	}

	public function setPayOrder($value)
	{
		$this->pay_order = $value;
		return $this;
	}

	public function setPayConfig($value)
	{
		$this->pay_config = $value;
		return $this;
	}

	public function setEnabled($value)
	{
		$this->enabled = $value;
		return $this;
	}

	public function setIsCod($value)
	{
		$this->is_cod = $value;
		return $this;
	}

	public function setIsOnline($value)
	{
		$this->is_online = $value;
		return $this;
	}
}

?>
