<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WarehouseFreight extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'warehouse_freight';
	public $timestamps = false;
	protected $fillable = array('user_id', 'warehouse_id', 'shipping_id', 'region_id', 'configure');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getWarehouseId()
	{
		return $this->warehouse_id;
	}

	public function getShippingId()
	{
		return $this->shipping_id;
	}

	public function getRegionId()
	{
		return $this->region_id;
	}

	public function getConfigure()
	{
		return $this->configure;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setWarehouseId($value)
	{
		$this->warehouse_id = $value;
		return $this;
	}

	public function setShippingId($value)
	{
		$this->shipping_id = $value;
		return $this;
	}

	public function setRegionId($value)
	{
		$this->region_id = $value;
		return $this;
	}

	public function setConfigure($value)
	{
		$this->configure = $value;
		return $this;
	}
}

?>
