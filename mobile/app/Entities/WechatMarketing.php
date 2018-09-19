<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatMarketing extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_marketing';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'marketing_type', 'name', 'keywords', 'command', 'description', 'starttime', 'endtime', 'addtime', 'logo', 'background', 'config', 'support', 'status', 'qrcode', 'url');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getMarketingType()
	{
		return $this->marketing_type;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function getCommand()
	{
		return $this->command;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getStarttime()
	{
		return $this->starttime;
	}

	public function getEndtime()
	{
		return $this->endtime;
	}

	public function getAddtime()
	{
		return $this->addtime;
	}

	public function getLogo()
	{
		return $this->logo;
	}

	public function getBackground()
	{
		return $this->background;
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function getSupport()
	{
		return $this->support;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getQrcode()
	{
		return $this->qrcode;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setMarketingType($value)
	{
		$this->marketing_type = $value;
		return $this;
	}

	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}

	public function setKeywords($value)
	{
		$this->keywords = $value;
		return $this;
	}

	public function setCommand($value)
	{
		$this->command = $value;
		return $this;
	}

	public function setDescription($value)
	{
		$this->description = $value;
		return $this;
	}

	public function setStarttime($value)
	{
		$this->starttime = $value;
		return $this;
	}

	public function setEndtime($value)
	{
		$this->endtime = $value;
		return $this;
	}

	public function setAddtime($value)
	{
		$this->addtime = $value;
		return $this;
	}

	public function setLogo($value)
	{
		$this->logo = $value;
		return $this;
	}

	public function setBackground($value)
	{
		$this->background = $value;
		return $this;
	}

	public function setConfig($value)
	{
		$this->config = $value;
		return $this;
	}

	public function setSupport($value)
	{
		$this->support = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setQrcode($value)
	{
		$this->qrcode = $value;
		return $this;
	}

	public function setUrl($value)
	{
		$this->url = $value;
		return $this;
	}
}

?>
