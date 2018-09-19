<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class StoreOrder extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'store_order';
	public $timestamps = false;
	protected $fillable = array('order_id', 'store_id', 'ru_id', 'is_grab_order', 'grab_store_list', 'pick_code', 'take_time');
	protected $guarded = array();

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getStoreId()
	{
		return $this->store_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getIsGrabOrder()
	{
		return $this->is_grab_order;
	}

	public function getGrabStoreList()
	{
		return $this->grab_store_list;
	}

	public function getPickCode()
	{
		return $this->pick_code;
	}

	public function getTakeTime()
	{
		return $this->take_time;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setStoreId($value)
	{
		$this->store_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setIsGrabOrder($value)
	{
		$this->is_grab_order = $value;
		return $this;
	}

	public function setGrabStoreList($value)
	{
		$this->grab_store_list = $value;
		return $this;
	}

	public function setPickCode($value)
	{
		$this->pick_code = $value;
		return $this;
	}

	public function setTakeTime($value)
	{
		$this->take_time = $value;
		return $this;
	}
}

?>
