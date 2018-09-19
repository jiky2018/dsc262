<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class StoreGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'store_goods';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'store_id', 'ru_id', 'goods_number', 'extend_goods_number');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getStoreId()
	{
		return $this->store_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getGoodsNumber()
	{
		return $this->goods_number;
	}

	public function getExtendGoodsNumber()
	{
		return $this->extend_goods_number;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setStoreId($value)
	{
		$this->store_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setGoodsNumber($value)
	{
		$this->goods_number = $value;
		return $this;
	}

	public function setExtendGoodsNumber($value)
	{
		$this->extend_goods_number = $value;
		return $this;
	}
}

?>
