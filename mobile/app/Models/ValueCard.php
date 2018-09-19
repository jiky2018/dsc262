<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ValueCard extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'value_card';
	protected $primaryKey = 'vid';
	public $timestamps = false;
	protected $fillable = array('tid', 'value_card_sn', 'value_card_password', 'user_id', 'vc_value', 'card_money', 'bind_time', 'end_time');
	protected $guarded = array();

	public function getTid()
	{
		return $this->tid;
	}

	public function getValueCardSn()
	{
		return $this->value_card_sn;
	}

	public function getValueCardPassword()
	{
		return $this->value_card_password;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getVcValue()
	{
		return $this->vc_value;
	}

	public function getCardMoney()
	{
		return $this->card_money;
	}

	public function getBindTime()
	{
		return $this->bind_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function setTid($value)
	{
		$this->tid = $value;
		return $this;
	}

	public function setValueCardSn($value)
	{
		$this->value_card_sn = $value;
		return $this;
	}

	public function setValueCardPassword($value)
	{
		$this->value_card_password = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setVcValue($value)
	{
		$this->vc_value = $value;
		return $this;
	}

	public function setCardMoney($value)
	{
		$this->card_money = $value;
		return $this;
	}

	public function setBindTime($value)
	{
		$this->bind_time = $value;
		return $this;
	}

	public function setEndTime($value)
	{
		$this->end_time = $value;
		return $this;
	}
}

?>
