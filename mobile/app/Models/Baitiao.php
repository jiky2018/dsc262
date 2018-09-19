<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Baitiao extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'baitiao';
	protected $primaryKey = 'baitiao_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'amount', 'repay_term', 'over_repay_trem', 'add_time');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function getRepayTerm()
	{
		return $this->repay_term;
	}

	public function getOverRepayTrem()
	{
		return $this->over_repay_trem;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setAmount($value)
	{
		$this->amount = $value;
		return $this;
	}

	public function setRepayTerm($value)
	{
		$this->repay_term = $value;
		return $this;
	}

	public function setOverRepayTrem($value)
	{
		$this->over_repay_trem = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
