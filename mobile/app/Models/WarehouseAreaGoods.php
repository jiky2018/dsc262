<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WarehouseAreaGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'warehouse_area_goods';
	protected $primaryKey = 'a_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'goods_id', 'region_id', 'city_id', 'region_sn', 'region_number', 'region_price', 'region_promote_price', 'region_sort', 'add_time', 'last_update', 'give_integral', 'rank_integral', 'pay_integral');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getRegionId()
	{
		return $this->region_id;
	}

	public function getCityId()
	{
		return $this->city_id;
	}

	public function getRegionSn()
	{
		return $this->region_sn;
	}

	public function getRegionNumber()
	{
		return $this->region_number;
	}

	public function getRegionPrice()
	{
		return $this->region_price;
	}

	public function getRegionPromotePrice()
	{
		return $this->region_promote_price;
	}

	public function getRegionSort()
	{
		return $this->region_sort;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getLastUpdate()
	{
		return $this->last_update;
	}

	public function getGiveIntegral()
	{
		return $this->give_integral;
	}

	public function getRankIntegral()
	{
		return $this->rank_integral;
	}

	public function getPayIntegral()
	{
		return $this->pay_integral;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setRegionId($value)
	{
		$this->region_id = $value;
		return $this;
	}

	public function setCityId($value)
	{
		$this->city_id = $value;
		return $this;
	}

	public function setRegionSn($value)
	{
		$this->region_sn = $value;
		return $this;
	}

	public function setRegionNumber($value)
	{
		$this->region_number = $value;
		return $this;
	}

	public function setRegionPrice($value)
	{
		$this->region_price = $value;
		return $this;
	}

	public function setRegionPromotePrice($value)
	{
		$this->region_promote_price = $value;
		return $this;
	}

	public function setRegionSort($value)
	{
		$this->region_sort = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setLastUpdate($value)
	{
		$this->last_update = $value;
		return $this;
	}

	public function setGiveIntegral($value)
	{
		$this->give_integral = $value;
		return $this;
	}

	public function setRankIntegral($value)
	{
		$this->rank_integral = $value;
		return $this;
	}

	public function setPayIntegral($value)
	{
		$this->pay_integral = $value;
		return $this;
	}
}

?>
