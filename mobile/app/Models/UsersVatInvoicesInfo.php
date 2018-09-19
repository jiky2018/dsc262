<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class UsersVatInvoicesInfo extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'users_vat_invoices_info';
	public $timestamps = false;
	protected $fillable = array('user_id', 'company_name', 'company_address', 'tax_id', 'company_telephone', 'bank_of_deposit', 'bank_account', 'consignee_name', 'consignee_mobile_phone', 'consignee_address', 'audit_status', 'add_time', 'country', 'province', 'city', 'district');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getCompanyName()
	{
		return $this->company_name;
	}

	public function getCompanyAddress()
	{
		return $this->company_address;
	}

	public function getTaxId()
	{
		return $this->tax_id;
	}

	public function getCompanyTelephone()
	{
		return $this->company_telephone;
	}

	public function getBankOfDeposit()
	{
		return $this->bank_of_deposit;
	}

	public function getBankAccount()
	{
		return $this->bank_account;
	}

	public function getConsigneeName()
	{
		return $this->consignee_name;
	}

	public function getConsigneeMobilePhone()
	{
		return $this->consignee_mobile_phone;
	}

	public function getConsigneeAddress()
	{
		return $this->consignee_address;
	}

	public function getAuditStatus()
	{
		return $this->audit_status;
	}

	public function getAddTime()
	{
		return $this->add_time;
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

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setCompanyName($value)
	{
		$this->company_name = $value;
		return $this;
	}

	public function setCompanyAddress($value)
	{
		$this->company_address = $value;
		return $this;
	}

	public function setTaxId($value)
	{
		$this->tax_id = $value;
		return $this;
	}

	public function setCompanyTelephone($value)
	{
		$this->company_telephone = $value;
		return $this;
	}

	public function setBankOfDeposit($value)
	{
		$this->bank_of_deposit = $value;
		return $this;
	}

	public function setBankAccount($value)
	{
		$this->bank_account = $value;
		return $this;
	}

	public function setConsigneeName($value)
	{
		$this->consignee_name = $value;
		return $this;
	}

	public function setConsigneeMobilePhone($value)
	{
		$this->consignee_mobile_phone = $value;
		return $this;
	}

	public function setConsigneeAddress($value)
	{
		$this->consignee_address = $value;
		return $this;
	}

	public function setAuditStatus($value)
	{
		$this->audit_status = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
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
}

?>
