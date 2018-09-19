<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Shipping extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'shipping';
	protected $primaryKey = 'shipping_id';
	public $timestamps = false;
	protected $fillable = array('shipping_code', 'shipping_name', 'shipping_desc', 'insure', 'support_cod', 'enabled', 'shipping_print', 'print_bg', 'config_lable', 'print_model', 'shipping_order', 'customer_name', 'customer_pwd', 'month_code', 'send_site');
	protected $guarded = array();

	public function getShippingCode()
	{
		return $this->shipping_code;
	}

	public function getShippingName()
	{
		return $this->shipping_name;
	}

	public function getShippingDesc()
	{
		return $this->shipping_desc;
	}

	public function getInsure()
	{
		return $this->insure;
	}

	public function getSupportCod()
	{
		return $this->support_cod;
	}

	public function getEnabled()
	{
		return $this->enabled;
	}

	public function getShippingPrint()
	{
		return $this->shipping_print;
	}

	public function getPrintBg()
	{
		return $this->print_bg;
	}

	public function getConfigLable()
	{
		return $this->config_lable;
	}

	public function getPrintModel()
	{
		return $this->print_model;
	}

	public function getShippingOrder()
	{
		return $this->shipping_order;
	}

	public function getCustomerName()
	{
		return $this->customer_name;
	}

	public function getCustomerPwd()
	{
		return $this->customer_pwd;
	}

	public function getMonthCode()
	{
		return $this->month_code;
	}

	public function getSendSite()
	{
		return $this->send_site;
	}

	public function setShippingCode($value)
	{
		$this->shipping_code = $value;
		return $this;
	}

	public function setShippingName($value)
	{
		$this->shipping_name = $value;
		return $this;
	}

	public function setShippingDesc($value)
	{
		$this->shipping_desc = $value;
		return $this;
	}

	public function setInsure($value)
	{
		$this->insure = $value;
		return $this;
	}

	public function setSupportCod($value)
	{
		$this->support_cod = $value;
		return $this;
	}

	public function setEnabled($value)
	{
		$this->enabled = $value;
		return $this;
	}

	public function setShippingPrint($value)
	{
		$this->shipping_print = $value;
		return $this;
	}

	public function setPrintBg($value)
	{
		$this->print_bg = $value;
		return $this;
	}

	public function setConfigLable($value)
	{
		$this->config_lable = $value;
		return $this;
	}

	public function setPrintModel($value)
	{
		$this->print_model = $value;
		return $this;
	}

	public function setShippingOrder($value)
	{
		$this->shipping_order = $value;
		return $this;
	}

	public function setCustomerName($value)
	{
		$this->customer_name = $value;
		return $this;
	}

	public function setCustomerPwd($value)
	{
		$this->customer_pwd = $value;
		return $this;
	}

	public function setMonthCode($value)
	{
		$this->month_code = $value;
		return $this;
	}

	public function setSendSite($value)
	{
		$this->send_site = $value;
		return $this;
	}
}

?>
