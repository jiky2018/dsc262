<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ShippingArea extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'shipping_area';
	protected $primaryKey = 'shipping_area_id';
	public $timestamps = false;
	protected $fillable = array('shipping_area_name', 'shipping_id', 'configure', 'ru_id');
	protected $guarded = array();

	public function shipping()
	{
		return $this->hasOne('App\\Models\\Shipping', 'shipping_id', 'shipping_id');
	}

	public function getShippingAreaName()
	{
		return $this->shipping_area_name;
	}

	public function getShippingId()
	{
		return $this->shipping_id;
	}

	public function getConfigure()
	{
		return $this->configure;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function setShippingAreaName($value)
	{
		$this->shipping_area_name = $value;
		return $this;
	}

	public function setShippingId($value)
	{
		$this->shipping_id = $value;
		return $this;
	}

	public function setConfigure($value)
	{
		$this->configure = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}
}

?>
