<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class SeckillTimeBucket extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seckill_time_bucket';
	public $timestamps = false;
	protected $fillable = array('begin_time', 'end_time', 'title');
	protected $guarded = array();

	public function getBeginTime()
	{
		return $this->begin_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function setBeginTime($value)
	{
		$this->begin_time = $value;
		return $this;
	}

	public function setEndTime($value)
	{
		$this->end_time = $value;
		return $this;
	}

	public function setTitle($value)
	{
		$this->title = $value;
		return $this;
	}
}

?>
