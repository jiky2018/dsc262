<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class KdniaoEorderConfig extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'kdniao_eorder_config';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'shipping_id', 'shipper_code', 'customer_name', 'customer_pwd', 'month_code', 'send_site', 'pay_type', 'template_size');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getShippingId()
	{
		return $this->shipping_id;
	}

	public function getShipperCode()
	{
		return $this->shipper_code;
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

	public function getPayType()
	{
		return $this->pay_type;
	}

	public function getTemplateSize()
	{
		return $this->template_size;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setShippingId($value)
	{
		$this->shipping_id = $value;
		return $this;
	}

	public function setShipperCode($value)
	{
		$this->shipper_code = $value;
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

	public function setPayType($value)
	{
		$this->pay_type = $value;
		return $this;
	}

	public function setTemplateSize($value)
	{
		$this->template_size = $value;
		return $this;
	}
}

?>
