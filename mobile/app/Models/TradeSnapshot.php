<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class TradeSnapshot extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'trade_snapshot';
	protected $primaryKey = 'trade_id';
	public $timestamps = false;
	protected $fillable = array('order_sn', 'user_id', 'goods_id', 'goods_name', 'goods_sn', 'shop_price', 'goods_number', 'shipping_fee', 'rz_shopName', 'goods_weight', 'add_time', 'goods_attr', 'goods_attr_id', 'ru_id', 'goods_desc', 'goods_img', 'snapshot_time');
	protected $guarded = array();

	public function getOrderSn()
	{
		return $this->order_sn;
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

	public function getGoodsSn()
	{
		return $this->goods_sn;
	}

	public function getShopPrice()
	{
		return $this->shop_price;
	}

	public function getGoodsNumber()
	{
		return $this->goods_number;
	}

	public function getShippingFee()
	{
		return $this->shipping_fee;
	}

	public function getRzShopName()
	{
		return $this->rz_shopName;
	}

	public function getGoodsWeight()
	{
		return $this->goods_weight;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getGoodsAttr()
	{
		return $this->goods_attr;
	}

	public function getGoodsAttrId()
	{
		return $this->goods_attr_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getGoodsDesc()
	{
		return $this->goods_desc;
	}

	public function getGoodsImg()
	{
		return $this->goods_img;
	}

	public function getSnapshotTime()
	{
		return $this->snapshot_time;
	}

	public function setOrderSn($value)
	{
		$this->order_sn = $value;
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

	public function setGoodsSn($value)
	{
		$this->goods_sn = $value;
		return $this;
	}

	public function setShopPrice($value)
	{
		$this->shop_price = $value;
		return $this;
	}

	public function setGoodsNumber($value)
	{
		$this->goods_number = $value;
		return $this;
	}

	public function setShippingFee($value)
	{
		$this->shipping_fee = $value;
		return $this;
	}

	public function setRzShopName($value)
	{
		$this->rz_shopName = $value;
		return $this;
	}

	public function setGoodsWeight($value)
	{
		$this->goods_weight = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setGoodsAttr($value)
	{
		$this->goods_attr = $value;
		return $this;
	}

	public function setGoodsAttrId($value)
	{
		$this->goods_attr_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setGoodsDesc($value)
	{
		$this->goods_desc = $value;
		return $this;
	}

	public function setGoodsImg($value)
	{
		$this->goods_img = $value;
		return $this;
	}

	public function setSnapshotTime($value)
	{
		$this->snapshot_time = $value;
		return $this;
	}
}

?>
