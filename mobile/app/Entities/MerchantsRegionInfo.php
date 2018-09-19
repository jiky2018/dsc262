<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsRegionInfo extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_region_info';
	public $timestamps = false;
	protected $fillable = array('ra_id', 'region_id');
	protected $guarded = array();

	public function getRaId()
	{
		return $this->ra_id;
	}

	public function getRegionId()
	{
		return $this->region_id;
	}

	public function setRaId($value)
	{
		$this->ra_id = $value;
		return $this;
	}

	public function setRegionId($value)
	{
		$this->region_id = $value;
		return $this;
	}
}

?>
