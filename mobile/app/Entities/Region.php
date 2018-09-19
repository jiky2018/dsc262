<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Region extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'region';
	protected $primaryKey = 'region_id';
	public $timestamps = false;
	protected $fillable = array('parent_id', 'region_name', 'region_type', 'agency_id');
	protected $guarded = array();

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getRegionName()
	{
		return $this->region_name;
	}

	public function getRegionType()
	{
		return $this->region_type;
	}

	public function getAgencyId()
	{
		return $this->agency_id;
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
