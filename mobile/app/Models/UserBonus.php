<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class UserBonus extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'user_bonus';
	protected $primaryKey = 'bonus_id';
	public $timestamps = false;
	protected $fillable = array('bonus_type_id', 'bonus_sn', 'bonus_password', 'user_id', 'used_time', 'order_id', 'emailed', 'bind_time');
	protected $guarded = array();

	public function getBonusTypeId()
	{
		return $this->bonus_type_id;
	}

	public function getBonusSn()
	{
		return $this->bonus_sn;
	}

	public function getBonusPassword()
	{
		return $this->bonus_password;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getUsedTime()
	{
		return $this->used_time;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getEmailed()
	{
		return $this->emailed;
	}

	public function getBindTime()
	{
		return $this->bind_time;
	}

	public function setBonusTypeId($value)
	{
		$this->bonus_type_id = $value;
		return $this;
	}

	public function setBonusSn($value)
	{
		$this->bonus_sn = $value;
		return $this;
	}

	public function setBonusPassword($value)
	{
		$this->bonus_password = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setUsedTime($value)
	{
		$this->used_time = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setEmailed($value)
	{
		$this->emailed = $value;
		return $this;
	}

	public function setBindTime($value)
	{
		$this->bind_time = $value;
		return $this;
	}
}

?>
