<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class RegionWarehouse extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'region_warehouse';
	protected $primaryKey = 'region_id';
	public $timestamps = false;
	protected $fillable = array('regionId', 'parent_id', 'region_name', 'region_code', 'region_type', 'agency_id');
	protected $guarded = array();

	public function getRegionId()
	{
		return $this->regionId;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getRegionName()
	{
		return $this->region_name;
	}

	public function getRegionCode()
	{
		return $this->region_code;
	}

	public function getRegionType()
	{
		return $this->region_type;
	}

	public function getAgencyId()
	{
		return $this->agency_id;
	}

	public function setRegionId($value)
	{
		$this->regionId = $value;
		return $this;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setRegionName($value)
	{
		$this->region_name = $value;
		return $this;
	}

	public function setRegionCode($value)
	{
		$this->region_code = $value;
		return $this;
	}

	public function setRegionType($value)
	{
		$this->region_type = $value;
		return $this;
	}

	public function setAgencyId($value)
	{
		$this->agency_id = $value;
		return $this;
	}
}

?>
