<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsPercent extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_percent';
	protected $primaryKey = 'percent_id';
	public $timestamps = false;
	protected $fillable = array('percent_value', 'sort_order', 'add_time');
	protected $guarded = array();

	public function getPercentValue()
	{
		return $this->percent_value;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setPercentValue($value)
	{
		$this->percent_value = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
