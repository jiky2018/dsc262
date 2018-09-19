<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ZcProgress extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'zc_progress';
	public $timestamps = false;
	protected $fillable = array('pid', 'progress', 'add_time', 'img');
	protected $guarded = array();

	public function getPid()
	{
		return $this->pid;
	}

	public function getProgress()
	{
		return $this->progress;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getImg()
	{
		return $this->img;
	}

	public function setPid($value)
	{
		$this->pid = $value;
		return $this;
	}

	public function setProgress($value)
	{
		$this->progress = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setImg($value)
	{
		$this->img = $value;
		return $this;
	}
}

?>
