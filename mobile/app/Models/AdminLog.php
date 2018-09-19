<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class AdminLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'admin_log';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	protected $fillable = array('log_time', 'user_id', 'log_info', 'ip_address');
	protected $guarded = array();

	public function getLogTime()
	{
		return $this->log_time;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getLogInfo()
	{
		return $this->log_info;
	}

	public function getIpAddress()
	{
		return $this->ip_address;
	}

	public function setLogTime($value)
	{
		$this->log_time = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setLogInfo($value)
	{
		$this->log_info = $value;
		return $this;
	}

	public function setIpAddress($value)
	{
		$this->ip_address = $value;
		return $this;
	}
}

?>
