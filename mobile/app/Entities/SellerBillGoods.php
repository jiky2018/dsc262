<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class SellerBillGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_bill_goods';
	public $timestamps = false;
	protected $fillable = array('rec_id', 'order_id', 'goods_id', 'cat_id', 'proportion', 'goods_price', 'dis_amount', 'goods_number', 'goods_attr', 'drp_money', 'commission_rate');
	protected $guarded = array();

	public function getRecId()
	{
		return $this->rec_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getProportion()
	{
		return $this->proportion;
	}

	public function getGoodsPrice()
	{
		return $this->goods_price;
	}

	public function getDisAmount()
	{
		return $this->dis_amount;
	}

	public function getGoodsNumber()
	{
		return $this->goods_number;
	}

	public function getGoodsAttr()
	{
		return $this->goods_attr;
	}

	public function getDrpMoney()
	{
		return $this->drp_money;
	}

	public function getCommissionRate()
	{
		return $this->commission_rate;
	}

	public function setRecId($value)
	{
		$this->rec_id = $value;
		return $this;
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

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setProportion($value)
	{
		$this->proportion = $value;
		return $this;
	}

	public function setGoodsPrice($value)
	{
		$this->goods_price = $value;
		return $this;
	}

	public function setDisAmount($value)
	{
		$this->dis_amount = $value;
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

	public function setDrpMoney($value)
	{
		$this->drp_money = $value;
		return $this;
	}

	public function setCommissionRate($value)
	{
		$this->commission_rate = $value;
		return $this;
	}
}

?>
