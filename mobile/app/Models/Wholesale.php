<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Wholesale extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wholesale';
	protected $primaryKey = 'act_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'goods_id', 'wholesale_cat_id', 'goods_name', 'rank_ids', 'goods_price', 'enabled', 'review_status', 'review_content', 'price_model', 'goods_type', 'goods_number', 'moq', 'is_recommend', 'is_promote', 'start_time', 'end_time', 'shipping_fee', 'freight', 'tid');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getWholesaleCatId()
	{
		return $this->wholesale_cat_id;
	}

	public function getGoodsName()
	{
		return $this->goods_name;
	}

	public function getRankIds()
	{
		return $this->rank_ids;
	}

	public function getGoodsPrice()
	{
		return $this->goods_price;
	}

	public function getEnabled()
	{
		return $this->enabled;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewContent()
	{
		return $this->review_content;
	}

	public function getPriceModel()
	{
		return $this->price_model;
	}

	public function getGoodsType()
	{
		return $this->goods_type;
	}

	public function getGoodsNumber()
	{
		return $this->goods_number;
	}

	public function getMoq()
	{
		return $this->moq;
	}

	public function getIsRecommend()
	{
		return $this->is_recommend;
	}

	public function getIsPromote()
	{
		return $this->is_promote;
	}

	public function getStartTime()
	{
		return $this->start_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function getShippingFee()
	{
		return $this->shipping_fee;
	}

	public function getFreight()
	{
		return $this->freight;
	}

	public function getTid()
	{
		return $this->tid;
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

	public function setWholesaleCatId($value)
	{
		$this->wholesale_cat_id = $value;
		return $this;
	}

	public function setGoodsName($value)
	{
		$this->goods_name = $value;
		return $this;
	}

	public function setRankIds($value)
	{
		$this->rank_ids = $value;
		return $this;
	}

	public function setGoodsPrice($value)
	{
		$this->goods_price = $value;
		return $this;
	}

	public function setEnabled($value)
	{
		$this->enabled = $value;
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

	public function setPriceModel($value)
	{
		$this->price_model = $value;
		return $this;
	}

	public function setGoodsType($value)
	{
		$this->goods_type = $value;
		return $this;
	}

	public function setGoodsNumber($value)
	{
		$this->goods_number = $value;
		return $this;
	}

	public function setMoq($value)
	{
		$this->moq = $value;
		return $this;
	}

	public function setIsRecommend($value)
	{
		$this->is_recommend = $value;
		return $this;
	}

	public function setIsPromote($value)
	{
		$this->is_promote = $value;
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

	public function setShippingFee($value)
	{
		$this->shipping_fee = $value;
		return $this;
	}

	public function setFreight($value)
	{
		$this->freight = $value;
		return $this;
	}

	public function setTid($value)
	{
		$this->tid = $value;
		return $this;
	}
}

?>
