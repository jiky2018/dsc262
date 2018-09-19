<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class TemporaryFiles extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'temporary_files';
	public $timestamps = false;
	protected $fillable = array('type', 'path', 'add_time', 'identity', 'user_id');
	protected $guarded = array();

	public function getType()
	{
		return $this->type;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getIdentity()
	{
		return $this->identity;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setPath($value)
	{
		$this->path = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setIdentity($value)
	{
		$this->identity = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}
}

?>
