<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class PayCard extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'pay_card';
	public $timestamps = false;
	protected $fillable = array('card_number', 'card_psd', 'user_id', 'used_time', 'status', 'c_id');
	protected $guarded = array();

	public function getCardNumber()
	{
		return $this->card_number;
	}

	public function getCardPsd()
	{
		return $this->card_psd;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getUsedTime()
	{
		return $this->used_time;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getCId()
	{
		return $this->c_id;
	}

	public function setCardNumber($value)
	{
		$this->card_number = $value;
		return $this;
	}

	public function setCardPsd($value)
	{
		$this->card_psd = $value;
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

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setCId($value)
	{
		$this->c_id = $value;
		return $this;
	}
}

?>
