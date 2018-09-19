<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class AccountLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'account_log';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'user_money', 'deposit_fee', 'frozen_money', 'rank_points', 'pay_points', 'change_time', 'change_desc', 'change_type');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getUserMoney()
	{
		return $this->user_money;
	}

	public function getDepositFee()
	{
		return $this->deposit_fee;
	}

	public function getFrozenMoney()
	{
		return $this->frozen_money;
	}

	public function getRankPoints()
	{
		return $this->rank_points;
	}

	public function getPayPoints()
	{
		return $this->pay_points;
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

	public function setDepositFee($value)
	{
		$this->deposit_fee = $value;
		return $this;
	}

	public function setFrozenMoney($value)
	{
		$this->frozen_money = $value;
		return $this;
	}

	public function setRankPoints($value)
	{
		$this->rank_points = $value;
		return $this;
	}

	public function setPayPoints($value)
	{
		$this->pay_points = $value;
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
