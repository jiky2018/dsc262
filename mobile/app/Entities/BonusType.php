<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class BonusType extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'bonus_type';
	protected $primaryKey = 'type_id';
	public $timestamps = false;
	protected $fillable = array('type_name', 'user_id', 'type_money', 'send_type', 'usebonus_type', 'min_amount', 'max_amount', 'send_start_date', 'send_end_date', 'use_start_date', 'use_end_date', 'min_goods_amount', 'review_status', 'review_content');
	protected $guarded = array();

	public function getTypeName()
	{
		return $this->type_name;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getTypeMoney()
	{
		return $this->type_money;
	}

	public function getSendType()
	{
		return $this->send_type;
	}

	public function getUsebonusType()
	{
		return $this->usebonus_type;
	}

	public function getMinAmount()
	{
		return $this->min_amount;
	}

	public function getMaxAmount()
	{
		return $this->max_amount;
	}

	public function getSendStartDate()
	{
		return $this->send_start_date;
	}

	public function getSendEndDate()
	{
		return $this->send_end_date;
	}

	public function getUseStartDate()
	{
		return $this->use_start_date;
	}

	public function getUseEndDate()
	{
		return $this->use_end_date;
	}

	public function getMinGoodsAmount()
	{
		return $this->min_goods_amount;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewContent()
	{
		return $this->review_content;
	}

	public function setTypeName($value)
	{
		$this->type_name = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setTypeMoney($value)
	{
		$this->type_money = $value;
		return $this;
	}

	public function setSendType($value)
	{
		$this->send_type = $value;
		return $this;
	}

	public function setUsebonusType($value)
	{
		$this->usebonus_type = $value;
		return $this;
	}

	public function setMinAmount($value)
	{
		$this->min_amount = $value;
		return $this;
	}

	public function setMaxAmount($value)
	{
		$this->max_amount = $value;
		return $this;
	}

	public function setSendStartDate($value)
	{
		$this->send_start_date = $value;
		return $this;
	}

	public function setSendEndDate($value)
	{
		$this->send_end_date = $value;
		return $this;
	}

	public function setUseStartDate($value)
	{
		$this->use_start_date = $value;
		return $this;
	}

	public function setUseEndDate($value)
	{
		$this->use_end_date = $value;
		return $this;
	}

	public function setMinGoodsAmount($value)
	{
		$this->min_goods_amount = $value;
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
}

?>
