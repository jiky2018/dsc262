<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ExchangeGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'exchange_goods';
	protected $primaryKey = 'eid';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'review_status', 'review_content', 'user_id', 'exchange_integral', 'market_integral', 'is_exchange', 'is_hot', 'is_best');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewContent()
	{
		return $this->review_content;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getExchangeIntegral()
	{
		return $this->exchange_integral;
	}

	public function getMarketIntegral()
	{
		return $this->market_integral;
	}

	public function getIsExchange()
	{
		return $this->is_exchange;
	}

	public function getIsHot()
	{
		return $this->is_hot;
	}

	public function getIsBest()
	{
		return $this->is_best;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
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

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setExchangeIntegral($value)
	{
		$this->exchange_integral = $value;
		return $this;
	}

	public function setMarketIntegral($value)
	{
		$this->market_integral = $value;
		return $this;
	}

	public function setIsExchange($value)
	{
		$this->is_exchange = $value;
		return $this;
	}

	public function setIsHot($value)
	{
		$this->is_hot = $value;
		return $this;
	}

	public function setIsBest($value)
	{
		$this->is_best = $value;
		return $this;
	}
}

?>
