<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class AreaRegion extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'area_region';
	public $timestamps = false;
	protected $fillable = array('shipping_area_id', 'region_id', 'ru_id');
	protected $guarded = array();

	public function getShippingAreaId()
	{
		return $this->shipping_area_id;
	}

	public function getRegionId()
	{
		return $this->region_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function setShippingAreaId($value)
	{
		$this->shipping_area_id = $value;
		return $this;
	}

	public function setRegionId($value)
	{
		$this->region_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}
}

?>
