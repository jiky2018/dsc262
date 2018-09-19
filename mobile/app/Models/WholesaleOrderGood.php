<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WholesaleOrderGood extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wholesale_order_goods';
	protected $primaryKey = 'rec_id';
	public $timestamps = false;
	protected $fillable = array('order_id', 'goods_id', 'goods_name', 'goods_sn', 'product_id', 'goods_number', 'market_price', 'goods_price', 'goods_attr', 'send_number', 'is_real', 'extension_code', 'goods_attr_id', 'ru_id', 'shipping_fee', 'freight', 'tid');
	protected $guarded = array();

	public function getOrderId()
	{
		return $this->order_id;
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

	public function getProductId()
	{
		return $this->product_id;
	}

	public function getGoodsNumber()
	{
		return $this->goods_number;
	}

	public function getMarketPrice()
	{
		return $this->market_price;
	}

	public function getGoodsPrice()
	{
		return $this->goods_price;
	}

	public function getGoodsAttr()
	{
		return $this->goods_attr;
	}

	public function getSendNumber()
	{
		return $this->send_number;
	}

	public function getIsReal()
	{
		return $this->is_real;
	}

	public function getExtensionCode()
	{
		return $this->extension_code;
	}

	public function getGoodsAttrId()
	{
		return $this->goods_attr_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
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

	public function setOrderId($value)
	{
		$this->order_id = $value;
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

	public function setProductId($value)
	{
		$this->product_id = $value;
		return $this;
	}

	public function setGoodsNumber($value)
	{
		$this->goods_number = $value;
		return $this;
	}

	public function setMarketPrice($value)
	{
		$this->market_price = $value;
		return $this;
	}

	public function setGoodsPrice($value)
	{
		$this->goods_price = $value;
		return $this;
	}

	public function setGoodsAttr($value)
	{
		$this->goods_attr = $value;
		return $this;
	}

	public function setSendNumber($value)
	{
		$this->send_number = $value;
		return $this;
	}

	public function setIsReal($value)
	{
		$this->is_real = $value;
		return $this;
	}

	public function setExtensionCode($value)
	{
		$this->extension_code = $value;
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
