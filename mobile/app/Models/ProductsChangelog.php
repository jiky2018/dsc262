<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ProductsChangelog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'products_changelog';
	protected $primaryKey = 'product_id';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'goods_attr', 'product_sn', 'bar_code', 'product_number', 'product_price', 'product_market_price', 'product_promote_price', 'product_warn_number', 'warehouse_id', 'area_id', 'city_id', 'admin_id');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getGoodsAttr()
	{
		return $this->goods_attr;
	}

	public function getProductSn()
	{
		return $this->product_sn;
	}

	public function getBarCode()
	{
		return $this->bar_code;
	}

	public function getProductNumber()
	{
		return $this->product_number;
	}

	public function getProductPrice()
	{
		return $this->product_price;
	}

	public function getProductMarketPrice()
	{
		return $this->product_market_price;
	}

	public function getProductPromotePrice()
	{
		return $this->product_promote_price;
	}

	public function getProductWarnNumber()
	{
		return $this->product_warn_number;
	}

	public function getWarehouseId()
	{
		return $this->warehouse_id;
	}

	public function getAreaId()
	{
		return $this->area_id;
	}

	public function getCityId()
	{
		return $this->city_id;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setGoodsAttr($value)
	{
		$this->goods_attr = $value;
		return $this;
	}

	public function setProductSn($value)
	{
		$this->product_sn = $value;
		return $this;
	}

	public function setBarCode($value)
	{
		$this->bar_code = $value;
		return $this;
	}

	public function setProductNumber($value)
	{
		$this->product_number = $value;
		return $this;
	}

	public function setProductPrice($value)
	{
		$this->product_price = $value;
		return $this;
	}

	public function setProductMarketPrice($value)
	{
		$this->product_market_price = $value;
		return $this;
	}

	public function setProductPromotePrice($value)
	{
		$this->product_promote_price = $value;
		return $this;
	}

	public function setProductWarnNumber($value)
	{
		$this->product_warn_number = $value;
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

	public function setCityId($value)
	{
		$this->city_id = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}
}

?>
