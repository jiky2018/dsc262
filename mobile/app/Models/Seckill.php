<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Seckill extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seckill';
	protected $primaryKey = 'sec_id';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'acti_title', 'begin_time', 'is_putaway', 'acti_time', 'add_time', 'review_status');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getActiTitle()
	{
		return $this->acti_title;
	}

	public function getBeginTime()
	{
		return $this->begin_time;
	}

	public function getIsPutaway()
	{
		return $this->is_putaway;
	}

	public function getActiTime()
	{
		return $this->acti_time;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setActiTitle($value)
	{
		$this->acti_title = $value;
		return $this;
	}

	public function setBeginTime($value)
	{
		$this->begin_time = $value;
		return $this;
	}

	public function setIsPutaway($value)
	{
		$this->is_putaway = $value;
		return $this;
	}

	public function setActiTime($value)
	{
		$this->acti_time = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setReviewStatus($value)
	{
		$this->review_status = $value;
		return $this;
	}
}

?>
