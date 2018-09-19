<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ReturnGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'return_goods';
	protected $primaryKey = 'rg_id';
	public $timestamps = false;
	protected $fillable = array('rec_id', 'ret_id', 'goods_id', 'product_id', 'product_sn', 'goods_name', 'brand_name', 'goods_sn', 'is_real', 'goods_attr', 'attr_id', 'return_type', 'return_number', 'out_attr', 'return_attr_id', 'refound');
	protected $guarded = array();

	public function getRecId()
	{
		return $this->rec_id;
	}

	public function getRetId()
	{
		return $this->ret_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getProductId()
	{
		return $this->product_id;
	}

	public function getProductSn()
	{
		return $this->product_sn;
	}

	public function getGoodsName()
	{
		return $this->goods_name;
	}

	public function getBrandName()
	{
		return $this->brand_name;
	}

	public function getGoodsSn()
	{
		return $this->goods_sn;
	}

	public function getIsReal()
	{
		return $this->is_real;
	}

	public function getGoodsAttr()
	{
		return $this->goods_attr;
	}

	public function getAttrId()
	{
		return $this->attr_id;
	}

	public function getReturnType()
	{
		return $this->return_type;
	}

	public function getReturnNumber()
	{
		return $this->return_number;
	}

	public function getOutAttr()
	{
		return $this->out_attr;
	}

	public function getReturnAttrId()
	{
		return $this->return_attr_id;
	}

	public function getRefound()
	{
		return $this->refound;
	}

	public function setRecId($value)
	{
		$this->rec_id = $value;
		return $this;
	}

	public function setRetId($value)
	{
		$this->ret_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setProductId($value)
	{
		$this->product_id = $value;
		return $this;
	}

	public function setProductSn($value)
	{
		$this->product_sn = $value;
		return $this;
	}

	public function setGoodsName($value)
	{
		$this->goods_name = $value;
		return $this;
	}

	public function setBrandName($value)
	{
		$this->brand_name = $value;
		return $this;
	}

	public function setGoodsSn($value)
	{
		$this->goods_sn = $value;
		return $this;
	}

	public function setIsReal($value)
	{
		$this->is_real = $value;
		return $this;
	}

	public function setGoodsAttr($value)
	{
		$this->goods_attr = $value;
		return $this;
	}

	public function setAttrId($value)
	{
		$this->attr_id = $value;
		return $this;
	}

	public function setReturnType($value)
	{
		$this->return_type = $value;
		return $this;
	}

	public function setReturnNumber($value)
	{
		$this->return_number = $value;
		return $this;
	}

	public function setOutAttr($value)
	{
		$this->out_attr = $value;
		return $this;
	}

	public function setReturnAttrId($value)
	{
		$this->return_attr_id = $value;
		return $this;
	}

	public function setRefound($value)
	{
		$this->refound = $value;
		return $this;
	}
}

?>
