<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class OrderGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'order_goods';
	protected $primaryKey = 'rec_id';
	public $timestamps = false;
	protected $fillable = array('order_id', 'user_id', 'cart_recid', 'goods_id', 'goods_name', 'goods_sn', 'product_id', 'goods_number', 'market_price', 'goods_price', 'goods_attr', 'send_number', 'is_real', 'extension_code', 'parent_id', 'is_gift', 'model_attr', 'goods_attr_id', 'ru_id', 'shopping_fee', 'warehouse_id', 'area_id', 'is_single', 'freight', 'tid', 'shipping_fee', 'drp_money', 'is_distribution', 'commission_rate', 'stages_qishu', 'product_sn', 'is_reality', 'is_return', 'is_fast');
	protected $guarded = array();

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getCartRecid()
	{
		return $this->cart_recid;
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

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getIsGift()
	{
		return $this->is_gift;
	}

	public function getModelAttr()
	{
		return $this->model_attr;
	}

	public function getGoodsAttrId()
	{
		return $this->goods_attr_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getShoppingFee()
	{
		return $this->shopping_fee;
	}

	public function getWarehouseId()
	{
		return $this->warehouse_id;
	}

	public function getAreaId()
	{
		return $this->area_id;
	}

	public function getIsSingle()
	{
		return $this->is_single;
	}

	public function getFreight()
	{
		return $this->freight;
	}

	public function getTid()
	{
		return $this->tid;
	}

	public function getShippingFee()
	{
		return $this->shipping_fee;
	}

	public function getDrpMoney()
	{
		return $this->drp_money;
	}

	public function getIsDistribution()
	{
		return $this->is_distribution;
	}

	public function getCommissionRate()
	{
		return $this->commission_rate;
	}

	public function getStagesQishu()
	{
		return $this->stages_qishu;
	}

	public function getProductSn()
	{
		return $this->product_sn;
	}

	public function getIsReality()
	{
		return $this->is_reality;
	}

	public function getIsReturn()
	{
		return $this->is_return;
	}

	public function getIsFast()
	{
		return $this->is_fast;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setCartRecid($value)
	{
		$this->cart_recid = $value;
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

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setIsGift($value)
	{
		$this->is_gift = $value;
		return $this;
	}

	public function setModelAttr($value)
	{
		$this->model_attr = $value;
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

	public function setShoppingFee($value)
	{
		$this->shopping_fee = $value;
		return $this;
	}

	public function setWarehouseId($value)
	{
		$this->warehouse_id = $value;
		return $this;
	}

	public function setAreaId($value)
	{
		$this->area_id = $value;
		return $this;
	}

	public function setIsSingle($value)
	{
		$this->is_single = $value;
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

	public function setShippingFee($value)
	{
		$this->shipping_fee = $value;
		return $this;
	}

	public function setDrpMoney($value)
	{
		$this->drp_money = $value;
		return $this;
	}

	public function setIsDistribution($value)
	{
		$this->is_distribution = $value;
		return $this;
	}

	public function setCommissionRate($value)
	{
		$this->commission_rate = $value;
		return $this;
	}

	public function setStagesQishu($value)
	{
		$this->stages_qishu = $value;
		return $this;
	}

	public function setProductSn($value)
	{
		$this->product_sn = $value;
		return $this;
	}

	public function setIsReality($value)
	{
		$this->is_reality = $value;
		return $this;
	}

	public function setIsReturn($value)
	{
		$this->is_return = $value;
		return $this;
	}

	public function setIsFast($value)
	{
		$this->is_fast = $value;
		return $this;
	}
}

?>
