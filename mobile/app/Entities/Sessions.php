<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Sessions extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'sessions';
	protected $primaryKey = 'sesskey';
	public $timestamps = false;
	protected $fillable = array('expiry', 'userid', 'adminid', 'ip', 'user_name', 'user_rank', 'discount', 'email', 'data');
	protected $guarded = array();

	public function getExpiry()
	{
		return $this->expiry;
	}

	public function getUserid()
	{
		return $this->userid;
	}

	public function getAdminid()
	{
		return $this->adminid;
	}

	public function getIp()
	{
		return $this->ip;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getUserRank()
	{
		return $this->user_rank;
	}

	public function getDiscount()
	{
		return $this->discount;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getData()
	{
		return $this->data;
	}

	public function setExpiry($value)
	{
		$this->expiry = $value;
		return $this;
	}

	public function setUserid($value)
	{
		$this->userid = $value;
		return $this;
	}

	public function setAdminid($value)
	{
		$this->adminid = $value;
		return $this;
	}

	public function setIp($value)
	{
		$this->ip = $value;
		return $this;
	}

	public function setUserName($value)
	{
		$this->user_name = $value;
		return $this;
	}

	public function setUserRank($value)
	{
		$this->user_rank = $value;
		return $this;
	}

	public function setDiscount($value)
	{
		$this->discount = $value;
		return $this;
	}

	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}

	public function setData($value)
	{
		$this->data = $value;
		return $this;
	}
}

?>
