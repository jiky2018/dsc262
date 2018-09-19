<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class UserAddress extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'user_address';
	protected $primaryKey = 'address_id';
	public $timestamps = false;
	protected $fillable = array('address_name', 'user_id', 'consignee', 'email', 'country', 'province', 'city', 'district', 'street', 'address', 'zipcode', 'tel', 'mobile', 'sign_building', 'best_time', 'audit', 'userUp_time');
	protected $guarded = array();

	public function getAddressName()
	{
		return $this->address_name;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getConsignee()
	{
		return $this->consignee;
	}

	public function getEmail()
	{
		return $this->email;
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

	public function getZipcode()
	{
		return $this->zipcode;
	}

	public function getTel()
	{
		return $this->tel;
	}

	public function getMobile()
	{
		return $this->mobile;
	}

	public function getSignBuilding()
	{
		return $this->sign_building;
	}

	public function getBestTime()
	{
		return $this->best_time;
	}

	public function getAudit()
	{
		return $this->audit;
	}

	public function getUserUpTime()
	{
		return $this->userUp_time;
	}

	public function setAddressName($value)
	{
		$this->address_name = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setConsignee($value)
	{
		$this->consignee = $value;
		return $this;
	}

	public function setEmail($value)
	{
		$this->email = $value;
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

	public function setZipcode($value)
	{
		$this->zipcode = $value;
		return $this;
	}

	public function setTel($value)
	{
		$this->tel = $value;
		return $this;
	}

	public function setMobile($value)
	{
		$this->mobile = $value;
		return $this;
	}

	public function setSignBuilding($value)
	{
		$this->sign_building = $value;
		return $this;
	}

	public function setBestTime($value)
	{
		$this->best_time = $value;
		return $this;
	}

	public function setAudit($value)
	{
		$this->audit = $value;
		return $this;
	}

	public function setUserUpTime($value)
	{
		$this->userUp_time = $value;
		return $this;
	}
}

?>
