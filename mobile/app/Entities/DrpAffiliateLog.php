<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class DrpAffiliateLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'drp_affiliate_log';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	protected $fillable = array('order_id', 'time', 'user_id', 'user_name', 'money', 'point', 'separate_type');
	protected $guarded = array();

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getTime()
	{
		return $this->time;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getMoney()
	{
		return $this->money;
	}

	public function getPoint()
	{
		return $this->point;
	}

	public function getSeparateType()
	{
		return $this->separate_type;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setTime($value)
	{
		$this->time = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setUserName($value)
	{
		$this->user_name = $value;
		return $this;
	}

	public function setMoney($value)
	{
		$this->money = $value;
		return $this;
	}

	public function setPoint($value)
	{
		$this->point = $value;
		return $this;
	}

	public function setSeparateType($value)
	{
		$this->separate_type = $value;
		return $this;
	}
}

?>
