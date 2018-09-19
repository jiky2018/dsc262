<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class SellerDomain extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_domain';
	public $timestamps = false;
	protected $fillable = array('domain_name', 'ru_id', 'is_enable', 'validity_time');
	protected $guarded = array();

	public function getDomainName()
	{
		return $this->domain_name;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getIsEnable()
	{
		return $this->is_enable;
	}

	public function getValidityTime()
	{
		return $this->validity_time;
	}

	public function setDomainName($value)
	{
		$this->domain_name = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setIsEnable($value)
	{
		$this->is_enable = $value;
		return $this;
	}

	public function setValidityTime($value)
	{
		$this->validity_time = $value;
		return $this;
	}
}

?>
