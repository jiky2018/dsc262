<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WholesalePurchaseGood extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wholesale_purchase_goods';
	protected $primaryKey = 'goods_id';
	public $timestamps = false;
	protected $fillable = array('purchase_id', 'cat_id', 'goods_name', 'goods_number', 'goods_price', 'goods_img', 'remarks');
	protected $guarded = array();

	public function getPurchaseId()
	{
		return $this->purchase_id;
	}

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getGoodsName()
	{
		return $this->goods_name;
	}

	public function getGoodsNumber()
	{
		return $this->goods_number;
	}

	public function getGoodsPrice()
	{
		return $this->goods_price;
	}

	public function getGoodsImg()
	{
		return $this->goods_img;
	}

	public function getRemarks()
	{
		return $this->remarks;
	}

	public function setPurchaseId($value)
	{
		$this->purchase_id = $value;
		return $this;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setGoodsName($value)
	{
		$this->goods_name = $value;
		return $this;
	}

	public function setGoodsNumber($value)
	{
		$this->goods_number = $value;
		return $this;
	}

	public function setGoodsPrice($value)
	{
		$this->goods_price = $value;
		return $this;
	}

	public function setGoodsImg($value)
	{
		$this->goods_img = $value;
		return $this;
	}

	public function setRemarks($value)
	{
		$this->remarks = $value;
		return $this;
	}
}

?>
