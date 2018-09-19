<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ZcProject extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'zc_project';
	public $timestamps = false;
	protected $fillable = array('cat_id', 'title', 'init_id', 'start_time', 'end_time', 'amount', 'join_money', 'join_num', 'focus_num', 'prais_num', 'title_img', 'details', 'describe', 'risk_instruction', 'img', 'is_best');
	protected $guarded = array();

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getInitId()
	{
		return $this->init_id;
	}

	public function getStartTime()
	{
		return $this->start_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function getJoinMoney()
	{
		return $this->join_money;
	}

	public function getJoinNum()
	{
		return $this->join_num;
	}

	public function getFocusNum()
	{
		return $this->focus_num;
	}

	public function getPraisNum()
	{
		return $this->prais_num;
	}

	public function getTitleImg()
	{
		return $this->title_img;
	}

	public function getDetails()
	{
		return $this->details;
	}

	public function getDescribe()
	{
		return $this->describe;
	}

	public function getRiskInstruction()
	{
		return $this->risk_instruction;
	}

	public function getImg()
	{
		return $this->img;
	}

	public function getIsBest()
	{
		return $this->is_best;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setTitle($value)
	{
		$this->title = $value;
		return $this;
	}

	public function setInitId($value)
	{
		$this->init_id = $value;
		return $this;
	}

	public function setStartTime($value)
	{
		$this->start_time = $value;
		return $this;
	}

	public function setEndTime($value)
	{
		$this->end_time = $value;
		return $this;
	}

	public function setAmount($value)
	{
		$this->amount = $value;
		return $this;
	}

	public function setJoinMoney($value)
	{
		$this->join_money = $value;
		return $this;
	}

	public function setJoinNum($value)
	{
		$this->join_num = $value;
		return $this;
	}

	public function setFocusNum($value)
	{
		$this->focus_num = $value;
		return $this;
	}

	public function setPraisNum($value)
	{
		$this->prais_num = $value;
		return $this;
	}

	public function setTitleImg($value)
	{
		$this->title_img = $value;
		return $this;
	}

	public function setDetails($value)
	{
		$this->details = $value;
		return $this;
	}

	public function setDescribe($value)
	{
		$this->describe = $value;
		return $this;
	}

	public function setRiskInstruction($value)
	{
		$this->risk_instruction = $value;
		return $this;
	}

	public function setImg($value)
	{
		$this->img = $value;
		return $this;
	}

	public function setIsBest($value)
	{
		$this->is_best = $value;
		return $this;
	}
}

?>
