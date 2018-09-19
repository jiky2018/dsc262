<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsRegionArea extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_region_area';
	protected $primaryKey = 'ra_id';
	public $timestamps = false;
	protected $fillable = array('ra_name', 'ra_sort', 'add_time', 'up_titme');
	protected $guarded = array();

	public function getRaName()
	{
		return $this->ra_name;
	}

	public function getRaSort()
	{
		return $this->ra_sort;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getUpTitme()
	{
		return $this->up_titme;
	}

	public function setRaName($value)
	{
		$this->ra_name = $value;
		return $this;
	}

	public function setRaSort($value)
	{
		$this->ra_sort = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setUpTitme($value)
	{
		$this->up_titme = $value;
		return $this;
	}
}

?>
