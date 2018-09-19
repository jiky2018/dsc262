<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class GoodsInventoryLogs extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_inventory_logs';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'order_id', 'use_storage', 'admin_id', 'number', 'model_inventory', 'model_attr', 'product_id', 'warehouse_id', 'area_id', 'suppliers_id', 'add_time', 'batch_number', 'remark');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getUseStorage()
	{
		return $this->use_storage;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function getNumber()
	{
		return $this->number;
	}

	public function getModelInventory()
	{
		return $this->model_inventory;
	}

	public function getModelAttr()
	{
		return $this->model_attr;
	}

	public function getProductId()
	{
		return $this->product_id;
	}

	public function getWarehouseId()
	{
		return $this->warehouse_id;
	}

	public function getAreaId()
	{
		return $this->area_id;
	}

	public function getSuppliersId()
	{
		return $this->suppliers_id;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getBatchNumber()
	{
		return $this->batch_number;
	}

	public function getRemark()
	{
		return $this->remark;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setUseStorage($value)
	{
		$this->use_storage = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}

	public function setNumber($value)
	{
		$this->number = $value;
		return $this;
	}

	public function setModelInventory($value)
	{
		$this->model_inventory = $value;
		return $this;
	}

	public function setModelAttr($value)
	{
		$this->model_attr = $value;
		return $this;
	}

	public function setProductId($value)
	{
		$this->product_id = $value;
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

	public function setSuppliersId($value)
	{
		$this->suppliers_id = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setBatchNumber($value)
	{
		$this->batch_number = $value;
		return $this;
	}

	public function setRemark($value)
	{
		$this->remark = $value;
		return $this;
	}
}

?>
