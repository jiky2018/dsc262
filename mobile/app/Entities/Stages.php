<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Stages extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'stages';
	protected $primaryKey = 'stages_id';
	public $timestamps = false;
	protected $fillable = array('order_sn', 'stages_total', 'stages_one_price', 'yes_num', 'create_date', 'repay_date');
	protected $guarded = array();

	public function getOrderSn()
	{
		return $this->order_sn;
	}

	public function getStagesTotal()
	{
		return $this->stages_total;
	}

	public function getStagesOnePrice()
	{
		return $this->stages_one_price;
	}

	public function getYesNum()
	{
		return $this->yes_num;
	}

	public function getCreateDate()
	{
		return $this->create_date;
	}

	public function getRepayDate()
	{
		return $this->repay_date;
	}

	public function setOrderSn($value)
	{
		$this->order_sn = $value;
		return $this;
	}

	public function setStagesTotal($value)
	{
		$this->stages_total = $value;
		return $this;
	}

	public function setStagesOnePrice($value)
	{
		$this->stages_one_price = $value;
		return $this;
	}

	public function setYesNum($value)
	{
		$this->yes_num = $value;
		return $this;
	}

	public function setCreateDate($value)
	{
		$this->create_date = $value;
		return $this;
	}

	public function setRepayDate($value)
	{
		$this->repay_date = $value;
		return $this;
	}
}

?>
