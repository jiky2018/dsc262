<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ShippingTpl extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'shipping_tpl';
	protected $primaryKey = 'st_id';
	public $timestamps = false;
	protected $fillable = array('shipping_id', 'ru_id', 'print_bg', 'print_model', 'config_lable', 'shipping_print', 'update_time');
	protected $guarded = array();

	public function getShippingId()
	{
		return $this->shipping_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getPrintBg()
	{
		return $this->print_bg;
	}

	public function getPrintModel()
	{
		return $this->print_model;
	}

	public function getConfigLable()
	{
		return $this->config_lable;
	}

	public function getShippingPrint()
	{
		return $this->shipping_print;
	}

	public function getUpdateTime()
	{
		return $this->update_time;
	}

	public function setShippingId($value)
	{
		$this->shipping_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setPrintBg($value)
	{
		$this->print_bg = $value;
		return $this;
	}

	public function setPrintModel($value)
	{
		$this->print_model = $value;
		return $this;
	}

	public function setConfigLable($value)
	{
		$this->config_lable = $value;
		return $this;
	}

	public function setShippingPrint($value)
	{
		$this->shipping_print = $value;
		return $this;
	}

	public function setUpdateTime($value)
	{
		$this->update_time = $value;
		return $this;
	}
}

?>
