<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class KdniaoCustomerAccount extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'kdniao_customer_account';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'shipping_id', 'shipper_code', 'station_code', 'station_name', 'apply_id', 'company', 'name', 'tel', 'mobile', 'province_name', 'province_code', 'city_name', 'city_code', 'exp_area_name', 'exp_area_code', 'address', 'dsc_province', 'dsc_city', 'dsc_district');
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

	public function getStationCode()
	{
		return $this->station_code;
	}

	public function getStationName()
	{
		return $this->station_name;
	}

	public function getApplyId()
	{
		return $this->apply_id;
	}

	public function getCompany()
	{
		return $this->company;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getTel()
	{
		return $this->tel;
	}

	public function getMobile()
	{
		return $this->mobile;
	}

	public function getProvinceName()
	{
		return $this->province_name;
	}

	public function getProvinceCode()
	{
		return $this->province_code;
	}

	public function getCityName()
	{
		return $this->city_name;
	}

	public function getCityCode()
	{
		return $this->city_code;
	}

	public function getExpAreaName()
	{
		return $this->exp_area_name;
	}

	public function getExpAreaCode()
	{
		return $this->exp_area_code;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function getDscProvince()
	{
		return $this->dsc_province;
	}

	public function getDscCity()
	{
		return $this->dsc_city;
	}

	public function getDscDistrict()
	{
		return $this->dsc_district;
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

	public function setStationCode($value)
	{
		$this->station_code = $value;
		return $this;
	}

	public function setStationName($value)
	{
		$this->station_name = $value;
		return $this;
	}

	public function setApplyId($value)
	{
		$this->apply_id = $value;
		return $this;
	}

	public function setCompany($value)
	{
		$this->company = $value;
		return $this;
	}

	public function setName($value)
	{
		$this->name = $value;
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

	public function setProvinceName($value)
	{
		$this->province_name = $value;
		return $this;
	}

	public function setProvinceCode($value)
	{
		$this->province_code = $value;
		return $this;
	}

	public function setCityName($value)
	{
		$this->city_name = $value;
		return $this;
	}

	public function setCityCode($value)
	{
		$this->city_code = $value;
		return $this;
	}

	public function setExpAreaName($value)
	{
		$this->exp_area_name = $value;
		return $this;
	}

	public function setExpAreaCode($value)
	{
		$this->exp_area_code = $value;
		return $this;
	}

	public function setAddress($value)
	{
		$this->address = $value;
		return $this;
	}

	public function setDscProvince($value)
	{
		$this->dsc_province = $value;
		return $this;
	}

	public function setDscCity($value)
	{
		$this->dsc_city = $value;
		return $this;
	}

	public function setDscDistrict($value)
	{
		$this->dsc_district = $value;
		return $this;
	}
}

?>
