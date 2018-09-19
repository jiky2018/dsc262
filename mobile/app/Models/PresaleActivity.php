<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class PresaleActivity extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'presale_activity';
	protected $primaryKey = 'act_id';
	public $timestamps = false;
	protected $fillable = array('act_name', 'cat_id', 'user_id', 'goods_id', 'goods_name', 'act_desc', 'deposit', 'start_time', 'end_time', 'pay_start_time', 'pay_end_time', 'is_finished', 'review_status', 'review_content', 'pre_num');
	protected $guarded = array();

	public function getActName()
	{
		return $this->act_name;
	}

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getGoodsName()
	{
		return $this->goods_name;
	}

	public function getActDesc()
	{
		return $this->act_desc;
	}

	public function getDeposit()
	{
		return $this->deposit;
	}

	public function getStartTime()
	{
		return $this->start_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function getPayStartTime()
	{
		return $this->pay_start_time;
	}

	public function getPayEndTime()
	{
		return $this->pay_end_time;
	}

	public function getIsFinished()
	{
		return $this->is_finished;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewContent()
	{
		return $this->review_content;
	}

	public function getPreNum()
	{
		return $this->pre_num;
	}

	public function setActName($value)
	{
		$this->act_name = $value;
		return $this;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setGoodsName($value)
	{
		$this->goods_name = $value;
		return $this;
	}

	public function setActDesc($value)
	{
		$this->act_desc = $value;
		return $this;
	}

	public function setDeposit($value)
	{
		$this->deposit = $value;
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

	public function setPayStartTime($value)
	{
		$this->pay_start_time = $value;
		return $this;
	}

	public function setPayEndTime($value)
	{
		$this->pay_end_time = $value;
		return $this;
	}

	public function setIsFinished($value)
	{
		$this->is_finished = $value;
		return $this;
	}

	public function setReviewStatus($value)
	{
		$this->review_status = $value;
		return $this;
	}

	public function setReviewContent($value)
	{
		$this->review_content = $value;
		return $this;
	}

	public function setPreNum($value)
	{
		$this->pre_num = $value;
		return $this;
	}
}

?>
