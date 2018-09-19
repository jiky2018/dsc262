<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class TouchAuth extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'touch_auth';
	public $timestamps = false;
	protected $fillable = array('auth_config', 'type', 'sort', 'status');
	protected $guarded = array();

	public function getAuthConfig()
	{
		return $this->auth_config;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getSort()
	{
		return $this->sort;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setAuthConfig($value)
	{
		$this->auth_config = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setSort($value)
	{
		$this->sort = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}
}

?>
