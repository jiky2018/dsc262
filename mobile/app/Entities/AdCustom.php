<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class AdCustom extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'ad_custom';
	protected $primaryKey = 'ad_id';
	public $timestamps = false;
	protected $fillable = array('ad_type', 'ad_name', 'add_time', 'content', 'url', 'ad_status');
	protected $guarded = array();

	public function getAdType()
	{
		return $this->ad_type;
	}

	public function getAdName()
	{
		return $this->ad_name;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getAdStatus()
	{
		return $this->ad_status;
	}

	public function setAdType($value)
	{
		$this->ad_type = $value;
		return $this;
	}

	public function setAdName($value)
	{
		$this->ad_name = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setUrl($value)
	{
		$this->url = $value;
		return $this;
	}

	public function setAdStatus($value)
	{
		$this->ad_status = $value;
		return $this;
	}
}

?>
