<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class MerchantsAccountLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_account_log';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'user_money', 'frozen_money', 'change_time', 'change_desc', 'change_type');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getUserMoney()
	{
		return $this->user_money;
	}

	public function getFrozenMoney()
	{
		return $this->frozen_money;
	}

	public function getChangeTime()
	{
		return $this->change_time;
	}

	public function getChangeDesc()
	{
		return $this->change_desc;
	}

	public function getChangeType()
	{
		return $this->change_type;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setUserMoney($value)
	{
		$this->user_money = $value;
		return $this;
	}

	public function setFrozenMoney($value)
	{
		$this->frozen_money = $value;
		return $this;
	}

	public function setChangeTime($value)
	{
		$this->change_time = $value;
		return $this;
	}

	public function setChangeDesc($value)
	{
		$this->change_desc = $value;
		return $this;
	}

	public function setChangeType($value)
	{
		$this->change_type = $value;
		return $this;
	}
}

?>
