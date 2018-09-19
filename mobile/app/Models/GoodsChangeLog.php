<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class GoodsChangeLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_change_log';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'shop_price', 'shipping_fee', 'promote_price', 'member_price', 'volume_price', 'give_integral', 'rank_integral', 'goods_weight', 'is_on_sale', 'user_id', 'handle_time', 'old_record');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getShopPrice()
	{
		return $this->shop_price;
	}

	public function getShippingFee()
	{
		return $this->shipping_fee;
	}

	public function getPromotePrice()
	{
		return $this->promote_price;
	}

	public function getMemberPrice()
	{
		return $this->member_price;
	}

	public function getVolumePrice()
	{
		return $this->volume_price;
	}

	public function getGiveIntegral()
	{
		return $this->give_integral;
	}

	public function getRankIntegral()
	{
		return $this->rank_integral;
	}

	public function getGoodsWeight()
	{
		return $this->goods_weight;
	}

	public function getIsOnSale()
	{
		return $this->is_on_sale;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getHandleTime()
	{
		return $this->handle_time;
	}

	public function getOldRecord()
	{
		return $this->old_record;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setShopPrice($value)
	{
		$this->shop_price = $value;
		return $this;
	}

	public function setShippingFee($value)
	{
		$this->shipping_fee = $value;
		return $this;
	}

	public function setPromotePrice($value)
	{
		$this->promote_price = $value;
		return $this;
	}

	public function setMemberPrice($value)
	{
		$this->member_price = $value;
		return $this;
	}

	public function setVolumePrice($value)
	{
		$this->volume_price = $value;
		return $this;
	}

	public function setGiveIntegral($value)
	{
		$this->give_integral = $value;
		return $this;
	}

	public function setRankIntegral($value)
	{
		$this->rank_integral = $value;
		return $this;
	}

	public function setGoodsWeight($value)
	{
		$this->goods_weight = $value;
		return $this;
	}

	public function setIsOnSale($value)
	{
		$this->is_on_sale = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setHandleTime($value)
	{
		$this->handle_time = $value;
		return $this;
	}

	public function setOldRecord($value)
	{
		$this->old_record = $value;
		return $this;
	}
}

?>
