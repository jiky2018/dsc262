<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ImDialog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'im_dialog';
	public $timestamps = false;
	protected $fillable = array('customer_id', 'services_id', 'goods_id', 'store_id', 'start_time', 'end_time', 'origin', 'status');
	protected $guarded = array();

	public function getCustomerId()
	{
		return $this->customer_id;
	}

	public function getServicesId()
	{
		return $this->services_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getStoreId()
	{
		return $this->store_id;
	}

	public function getStartTime()
	{
		return $this->start_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function getOrigin()
	{
		return $this->origin;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setCustomerId($value)
	{
		$this->customer_id = $value;
		return $this;
	}

	public function setServicesId($value)
	{
		$this->services_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setStoreId($value)
	{
		$this->store_id = $value;
		return $this;
	}

	public function setStartTime($value)
	{
		$this->start_time = $value;
		return $this;
	}

	public function setEndTime($value)
	{
		$this->end_time = $value;
		return $this;
	}

	public function setOrigin($value)
	{
		$this->origin = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}
}

?>
