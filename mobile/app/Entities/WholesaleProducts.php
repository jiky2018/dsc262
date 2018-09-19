<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WholesaleProducts extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wholesale_products';
	protected $primaryKey = 'product_id';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'goods_attr', 'product_sn', 'product_number', 'admin_id');
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

	public function setProductNumber($value)
	{
		$this->product_number = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}
}

?>
