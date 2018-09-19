<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class OpenApi extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'open_api';
	public $timestamps = false;
	protected $fillable = array('name', 'app_key', 'action_code', 'is_open', 'add_time');
	protected $guarded = array();

	public function getName()
	{
		return $this->name;
	}

	public function getAppKey()
	{
		return $this->app_key;
	}

	public function getActionCode()
	{
		return $this->action_code;
	}

	public function getIsOpen()
	{
		return $this->is_open;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}

	public function setAppKey($value)
	{
		$this->app_key = $value;
		return $this;
	}

	public function setActionCode($value)
	{
		$this->action_code = $value;
		return $this;
	}

	public function setIsOpen($value)
	{
		$this->is_open = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
