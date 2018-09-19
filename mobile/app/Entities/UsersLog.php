<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class UsersLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'users_log';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'admin_id', 'change_time', 'change_type', 'ip_address', 'change_city', 'logon_service');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function getChangeTime()
	{
		return $this->change_time;
	}

	public function getChangeType()
	{
		return $this->change_type;
	}

	public function getIpAddress()
	{
		return $this->ip_address;
	}

	public function getChangeCity()
	{
		return $this->change_city;
	}

	public function getLogonService()
	{
		return $this->logon_service;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}

	public function setChangeTime($value)
	{
		$this->change_time = $value;
		return $this;
	}

	public function setChangeType($value)
	{
		$this->change_type = $value;
		return $this;
	}

	public function setIpAddress($value)
	{
		$this->ip_address = $value;
		return $this;
	}

	public function setChangeCity($value)
	{
		$this->change_city = $value;
		return $this;
	}

	public function setLogonService($value)
	{
		$this->logon_service = $value;
		return $this;
	}
}

?>
