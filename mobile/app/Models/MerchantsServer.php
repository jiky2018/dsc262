<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class MerchantsServer extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_server';
	protected $primaryKey = 'server_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'suppliers_desc', 'suppliers_percent', 'commission_model', 'bill_freeze_day', 'cycle', 'day_number', 'bill_time');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getSuppliersDesc()
	{
		return $this->suppliers_desc;
	}

	public function getSuppliersPercent()
	{
		return $this->suppliers_percent;
	}

	public function getCommissionModel()
	{
		return $this->commission_model;
	}

	public function getBillFreezeDay()
	{
		return $this->bill_freeze_day;
	}

	public function getCycle()
	{
		return $this->cycle;
	}

	public function getDayNumber()
	{
		return $this->day_number;
	}

	public function getBillTime()
	{
		return $this->bill_time;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setSuppliersDesc($value)
	{
		$this->suppliers_desc = $value;
		return $this;
	}

	public function setSuppliersPercent($value)
	{
		$this->suppliers_percent = $value;
		return $this;
	}

	public function setCommissionModel($value)
	{
		$this->commission_model = $value;
		return $this;
	}

	public function setBillFreezeDay($value)
	{
		$this->bill_freeze_day = $value;
		return $this;
	}

	public function setCycle($value)
	{
		$this->cycle = $value;
		return $this;
	}

	public function setDayNumber($value)
	{
		$this->day_number = $value;
		return $this;
	}

	public function setBillTime($value)
	{
		$this->bill_time = $value;
		return $this;
	}
}

?>
