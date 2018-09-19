<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WholesaleOrderInfo extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wholesale_order_info';
	protected $primaryKey = 'order_id';
	public $timestamps = false;
	protected $fillable = array('main_order_id', 'order_sn', 'user_id', 'order_status', 'consignee', 'country', 'province', 'city', 'district', 'street', 'address', 'mobile', 'email', 'postscript', 'inv_payee', 'inv_content', 'order_amount', 'add_time', 'extension_code', 'inv_type', 'tax', 'is_delete', 'invoice_type', 'vat_id', 'tax_id', 'pay_id', 'pay_status', 'pay_time', 'pay_fee');
	protected $guarded = array();

	public function getMainOrderId()
	{
		return $this->main_order_id;
	}

	public function getOrderSn()
	{
		return $this->order_sn;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getOrderStatus()
	{
		return $this->order_status;
	}

	public function getConsignee()
	{
		return $this->consignee;
	}

	public function getCountry()
	{
		return $this->country;
	}

	public function getProvince()
	{
		return $this->province;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function getDistrict()
	{
		return $this->district;
	}

	public function getStreet()
	{
		return $this->street;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function getMobile()
	{
		return $this->mobile;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getPostscript()
	{
		return $this->postscript;
	}

	public function getInvPayee()
	{
		return $this->inv_payee;
	}

	public function getInvContent()
	{
		return $this->inv_content;
	}

	public function getOrderAmount()
	{
		return $this->order_amount;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getExtensionCode()
	{
		return $this->extension_code;
	}

	public function getInvType()
	{
		return $this->inv_type;
	}

	public function getTax()
	{
		return $this->tax;
	}

	public function getIsDelete()
	{
		return $this->is_delete;
	}

	public function getInvoiceType()
	{
		return $this->invoice_type;
	}

	public function getVatId()
	{
		return $this->vat_id;
	}

	public function getTaxId()
	{
		return $this->tax_id;
	}

	public function getPayId()
	{
		return $this->pay_id;
	}

	public function getPayStatus()
	{
		return $this->pay_status;
	}

	public function getPayTime()
	{
		return $this->pay_time;
	}

	public function getPayFee()
	{
		return $this->pay_fee;
	}

	public function setMainOrderId($value)
	{
		$this->main_order_id = $value;
		return $this;
	}

	public function setOrderSn($value)
	{
		$this->order_sn = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setOrderStatus($value)
	{
		$this->order_status = $value;
		return $this;
	}

	public function setConsignee($value)
	{
		$this->consignee = $value;
		return $this;
	}

	public function setCountry($value)
	{
		$this->country = $value;
		return $this;
	}

	public function setProvince($value)
	{
		$this->province = $value;
		return $this;
	}

	public function setCity($value)
	{
		$this->city = $value;
		return $this;
	}

	public function setDistrict($value)
	{
		$this->district = $value;
		return $this;
	}

	public function setStreet($value)
	{
		$this->street = $value;
		return $this;
	}

	public function setAddress($value)
	{
		$this->address = $value;
		return $this;
	}

	public function setMobile($value)
	{
		$this->mobile = $value;
		return $this;
	}

	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}

	public function setPostscript($value)
	{
		$this->postscript = $value;
		return $this;
	}

	public function setInvPayee($value)
	{
		$this->inv_payee = $value;
		return $this;
	}

	public function setInvContent($value)
	{
		$this->inv_content = $value;
		return $this;
	}

	public function setOrderAmount($value)
	{
		$this->order_amount = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setExtensionCode($value)
	{
		$this->extension_code = $value;
		return $this;
	}

	public function setInvType($value)
	{
		$this->inv_type = $value;
		return $this;
	}

	public function setTax($value)
	{
		$this->tax = $value;
		return $this;
	}

	public function setIsDelete($value)
	{
		$this->is_delete = $value;
		return $this;
	}

	public function setInvoiceType($value)
	{
		$this->invoice_type = $value;
		return $this;
	}

	public function setVatId($value)
	{
		$this->vat_id = $value;
		return $this;
	}

	public function setTaxId($value)
	{
		$this->tax_id = $value;
		return $this;
	}

	public function setPayId($value)
	{
		$this->pay_id = $value;
		return $this;
	}

	public function setPayStatus($value)
	{
		$this->pay_status = $value;
		return $this;
	}

	public function setPayTime($value)
	{
		$this->pay_time = $value;
		return $this;
	}

	public function setPayFee($value)
	{
		$this->pay_fee = $value;
		return $this;
	}
}

?>
