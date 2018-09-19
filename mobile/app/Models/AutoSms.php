<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class AutoSms extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'auto_sms';
	protected $primaryKey = 'item_id';
	public $timestamps = false;
	protected $fillable = array('item_type', 'user_id', 'ru_id', 'order_id', 'add_time');
	protected $guarded = array();

	public function getItemType()
	{
		return $this->item_type;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setItemType($value)
	{
		$this->item_type = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
