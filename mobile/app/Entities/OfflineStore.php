<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class OfflineStore extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'offline_store';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'stores_user', 'stores_pwd', 'stores_name', 'country', 'province', 'city', 'district', 'stores_address', 'stores_tel', 'stores_opening_hours', 'stores_traffic_line', 'stores_img', 'is_confirm', 'add_time', 'ec_salt');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getStoresUser()
	{
		return $this->stores_user;
	}

	public function getStoresPwd()
	{
		return $this->stores_pwd;
	}

	public function getStoresName()
	{
		return $this->stores_name;
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

	public function getStoresAddress()
	{
		return $this->stores_address;
	}

	public function getStoresTel()
	{
		return $this->stores_tel;
	}

	public function getStoresOpeningHours()
	{
		return $this->stores_opening_hours;
	}

	public function getStoresTrafficLine()
	{
		return $this->stores_traffic_line;
	}

	public function getStoresImg()
	{
		return $this->stores_img;
	}

	public function getIsConfirm()
	{
		return $this->is_confirm;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getEcSalt()
	{
		return $this->ec_salt;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setStoresUser($value)
	{
		$this->stores_user = $value;
		return $this;
	}

	public function setStoresPwd($value)
	{
		$this->stores_pwd = $value;
		return $this;
	}

	public function setStoresName($value)
	{
		$this->stores_name = $value;
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

	public function setStoresAddress($value)
	{
		$this->stores_address = $value;
		return $this;
	}

	public function setStoresTel($value)
	{
		$this->stores_tel = $value;
		return $this;
	}

	public function setStoresOpeningHours($value)
	{
		$this->stores_opening_hours = $value;
		return $this;
	}

	public function setStoresTrafficLine($value)
	{
		$this->stores_traffic_line = $value;
		return $this;
	}

	public function setStoresImg($value)
	{
		$this->stores_img = $value;
		return $this;
	}

	public function setIsConfirm($value)
	{
		$this->is_confirm = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setEcSalt($value)
	{
		$this->ec_salt = $value;
		return $this;
	}
}

?>
