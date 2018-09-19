<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Stats extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'stats';
	public $timestamps = false;
	protected $fillable = array('access_time', 'ip_address', 'visit_times', 'browser', 'system', 'language', 'area', 'referer_domain', 'referer_path', 'access_url');
	protected $guarded = array();

	public function getAccessTime()
	{
		return $this->access_time;
	}

	public function getIpAddress()
	{
		return $this->ip_address;
	}

	public function getVisitTimes()
	{
		return $this->visit_times;
	}

	public function getBrowser()
	{
		return $this->browser;
	}

	public function getSystem()
	{
		return $this->system;
	}

	public function getLanguage()
	{
		return $this->language;
	}

	public function getArea()
	{
		return $this->area;
	}

	public function getRefererDomain()
	{
		return $this->referer_domain;
	}

	public function getRefererPath()
	{
		return $this->referer_path;
	}

	public function getAccessUrl()
	{
		return $this->access_url;
	}

	public function setAccessTime($value)
	{
		$this->access_time = $value;
		return $this;
	}

	public function setIpAddress($value)
	{
		$this->ip_address = $value;
		return $this;
	}

	public function setVisitTimes($value)
	{
		$this->visit_times = $value;
		return $this;
	}

	public function setBrowser($value)
	{
		$this->browser = $value;
		return $this;
	}

	public function setSystem($value)
	{
		$this->system = $value;
		return $this;
	}

	public function setLanguage($value)
	{
		$this->language = $value;
		return $this;
	}

	public function setArea($value)
	{
		$this->area = $value;
		return $this;
	}

	public function setRefererDomain($value)
	{
		$this->referer_domain = $value;
		return $this;
	}

	public function setRefererPath($value)
	{
		$this->referer_path = $value;
		return $this;
	}

	public function setAccessUrl($value)
	{
		$this->access_url = $value;
		return $this;
	}
}

?>
