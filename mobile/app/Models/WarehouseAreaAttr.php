<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WarehouseAreaAttr extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'warehouse_area_attr';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'goods_attr_id', 'area_id', 'attr_price', 'admin_id');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getGoodsAttrId()
	{
		return $this->goods_attr_id;
	}

	public function getAreaId()
	{
		return $this->area_id;
	}

	public function getAttrPrice()
	{
		return $this->attr_price;
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

	public function setGoodsAttrId($value)
	{
		$this->goods_attr_id = $value;
		return $this;
	}

	public function setAreaId($value)
	{
		$this->area_id = $value;
		return $this;
	}

	public function setAttrPrice($value)
	{
		$this->attr_price = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}
}

?>
