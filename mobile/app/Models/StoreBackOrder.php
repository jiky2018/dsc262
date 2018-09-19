<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class StoreBackOrder extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'store_back_order';
	public $timestamps = false;
	protected $fillable = array('store_id', 'order_id');
	protected $guarded = array();

	public function getStoreId()
	{
		return $this->store_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function setStoreId($value)
	{
		$this->store_id = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}
}

?>
