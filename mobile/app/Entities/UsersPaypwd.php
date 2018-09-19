<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class UsersPaypwd extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'users_paypwd';
	protected $primaryKey = 'paypwd_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'ec_salt', 'pay_password', 'pay_online', 'user_surplus', 'user_point', 'baitiao', 'gift_card');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getEcSalt()
	{
		return $this->ec_salt;
	}

	public function getPayPassword()
	{
		return $this->pay_password;
	}

	public function getPayOnline()
	{
		return $this->pay_online;
	}

	public function getUserSurplus()
	{
		return $this->user_surplus;
	}

	public function getUserPoint()
	{
		return $this->user_point;
	}

	public function getBaitiao()
	{
		return $this->baitiao;
	}

	public function getGiftCard()
	{
		return $this->gift_card;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setEcSalt($value)
	{
		$this->ec_salt = $value;
		return $this;
	}

	public function setPayPassword($value)
	{
		$this->pay_password = $value;
		return $this;
	}

	public function setPayOnline($value)
	{
		$this->pay_online = $value;
		return $this;
	}

	public function setUserSurplus($value)
	{
		$this->user_surplus = $value;
		return $this;
	}

	public function setUserPoint($value)
	{
		$this->user_point = $value;
		return $this;
	}

	public function setBaitiao($value)
	{
		$this->baitiao = $value;
		return $this;
	}

	public function setGiftCard($value)
	{
		$this->gift_card = $value;
		return $this;
	}
}

?>
