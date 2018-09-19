<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class StoreProducts extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'store_products';
	protected $primaryKey = 'product_id';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'goods_attr', 'product_sn', 'product_number', 'ru_id', 'store_id');
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

	public function getProductNumber()
	{
		return $this->product_number;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getStoreId()
	{
		return $this->store_id;
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

	public function setProductNumber($value)
	{
		$this->product_number = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setStoreId($value)
	{
		$this->store_id = $value;
		return $this;
	}
}

?>
