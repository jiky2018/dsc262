<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class UsersType extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'users_type';
	protected $primaryKey = 'user_id';
	public $timestamps = false;
	protected $fillable = array('enterprise_personal', 'companyname', 'contactname', 'companyaddress', 'industry', 'surname', 'givenname', 'agreement', 'country', 'province', 'city', 'district');
	protected $guarded = array();

	public function getEnterprisePersonal()
	{
		return $this->enterprise_personal;
	}

	public function getCompanyname()
	{
		return $this->companyname;
	}

	public function getContactname()
	{
		return $this->contactname;
	}

	public function getCompanyaddress()
	{
		return $this->companyaddress;
	}

	public function getIndustry()
	{
		return $this->industry;
	}

	public function getSurname()
	{
		return $this->surname;
	}

	public function getGivenname()
	{
		return $this->givenname;
	}

	public function getAgreement()
	{
		return $this->agreement;
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

	public function setEnterprisePersonal($value)
	{
		$this->enterprise_personal = $value;
		return $this;
	}

	public function setCompanyname($value)
	{
		$this->companyname = $value;
		return $this;
	}

	public function setContactname($value)
	{
		$this->contactname = $value;
		return $this;
	}

	public function setCompanyaddress($value)
	{
		$this->companyaddress = $value;
		return $this;
	}

	public function setIndustry($value)
	{
		$this->industry = $value;
		return $this;
	}

	public function setSurname($value)
	{
		$this->surname = $value;
		return $this;
	}

	public function setGivenname($value)
	{
		$this->givenname = $value;
		return $this;
	}

	public function setAgreement($value)
	{
		$this->agreement = $value;
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
