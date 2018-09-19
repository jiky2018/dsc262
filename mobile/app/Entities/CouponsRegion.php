<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class CouponsRegion extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'coupons_region';
	protected $primaryKey = 'cf_id';
	public $timestamps = false;
	protected $fillable = array('cou_id', 'region_list');
	protected $guarded = array();

	public function getCouId()
	{
		return $this->cou_id;
	}

	public function getRegionList()
	{
		return $this->region_list;
	}

	public function setCouId($value)
	{
		$this->cou_id = $value;
		return $this;
	}

	public function setRegionList($value)
	{
		$this->region_list = $value;
		return $this;
	}
}

?>
