<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class CartCombo extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'cart_combo';
	protected $primaryKey = 'rec_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'session_id', 'goods_id', 'goods_sn', 'product_id', 'group_id', 'goods_name', 'market_price', 'goods_price', 'goods_number', 'goods_attr', 'img_flie', 'is_real', 'extension_code', 'parent_id', 'rec_type', 'is_gift', 'is_shipping', 'can_handsel', 'goods_attr_id', 'warehouse_id', 'area_id', 'model_attr', 'add_time', 'commission_rate');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getSessionId()
	{
		return $this->session_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getGoodsSn()
	{
		return $this->goods_sn;
	}

	public function getProductId()
	{
		return $this->product_id;
	}

	public function getGroupId()
	{
		return $this->group_id;
	}

	public function getGoodsName()
	{
		return $this->goods_name;
	}

	public function getMarketPrice()
	{
		return $this->market_price;
	}

	public function getGoodsPrice()
	{
		return $this->goods_price;
	}

	public function getGoodsNumber()
	{
		return $this->goods_number;
	}

	public function getGoodsAttr()
	{
		return $this->goods_attr;
	}

	public function getImgFlie()
	{
		return $this->img_flie;
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

	public function getRecType()
	{
		return $this->rec_type;
	}

	public function getIsGift()
	{
		return $this->is_gift;
	}

	public function getIsShipping()
	{
		return $this->is_shipping;
	}

	public function getCanHandsel()
	{
		return $this->can_handsel;
	}

	public function getGoodsAttrId()
	{
		return $this->goods_attr_id;
	}

	public function getWarehouseId()
	{
		return $this->warehouse_id;
	}

	public function getAreaId()
	{
		return $this->area_id;
	}

	public function getModelAttr()
	{
		return $this->model_attr;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getCommissionRate()
	{
		return $this->commission_rate;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setSessionId($value)
	{
		$this->session_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
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

	public function setGroupId($value)
	{
		$this->group_id = $value;
		return $this;
	}

	public function setGoodsName($value)
	{
		$this->goods_name = $value;
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

	public function setGoodsNumber($value)
	{
		$this->goods_number = $value;
		return $this;
	}

	public function setGoodsAttr($value)
	{
		$this->goods_attr = $value;
		return $this;
	}

	public function setImgFlie($value)
	{
		$this->img_flie = $value;
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

	public function setRecType($value)
	{
		$this->rec_type = $value;
		return $this;
	}

	public function setIsGift($value)
	{
		$this->is_gift = $value;
		return $this;
	}

	public function setIsShipping($value)
	{
		$this->is_shipping = $value;
		return $this;
	}

	public function setCanHandsel($value)
	{
		$this->can_handsel = $value;
		return $this;
	}

	public function setGoodsAttrId($value)
	{
		$this->goods_attr_id = $value;
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

	public function setModelAttr($value)
	{
		$this->model_attr = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setCommissionRate($value)
	{
		$this->commission_rate = $value;
		return $this;
	}
}

?>
