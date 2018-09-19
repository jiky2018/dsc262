<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsGrade extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_grade';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'grade_id', 'add_time', 'year_num', 'amount');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getGradeId()
	{
		return $this->grade_id;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getYearNum()
	{
		return $this->year_num;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setGradeId($value)
	{
		$this->grade_id = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setYearNum($value)
	{
		$this->year_num = $value;
		return $this;
	}

	public function setAmount($value)
	{
		$this->amount = $value;
		return $this;
	}
}

?>
