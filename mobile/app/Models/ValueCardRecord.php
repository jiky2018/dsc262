<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ValueCardRecord extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'value_card_record';
	protected $primaryKey = 'rid';
	public $timestamps = false;
	protected $fillable = array('vc_id', 'order_id', 'use_val', 'add_val', 'record_time');
	protected $guarded = array();

	public function getVcId()
	{
		return $this->vc_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getUseVal()
	{
		return $this->use_val;
	}

	public function getAddVal()
	{
		return $this->add_val;
	}

	public function getRecordTime()
	{
		return $this->record_time;
	}

	public function setVcId($value)
	{
		$this->vc_id = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setUseVal($value)
	{
		$this->use_val = $value;
		return $this;
	}

	public function setAddVal($value)
	{
		$this->add_val = $value;
		return $this;
	}

	public function setRecordTime($value)
	{
		$this->record_time = $value;
		return $this;
	}
}

?>
