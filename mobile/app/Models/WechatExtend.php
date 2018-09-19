<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WechatExtend extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_extend';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'name', 'keywords', 'command', 'config', 'type', 'enable', 'author', 'website');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
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

	public function getConfig()
	{
		return $this->config;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getEnable()
	{
		return $this->enable;
	}

	public function getAuthor()
	{
		return $this->author;
	}

	public function getWebsite()
	{
		return $this->website;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
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

	public function setConfig($value)
	{
		$this->config = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setEnable($value)
	{
		$this->enable = $value;
		return $this;
	}

	public function setAuthor($value)
	{
		$this->author = $value;
		return $this;
	}

	public function setWebsite($value)
	{
		$this->website = $value;
		return $this;
	}
}

?>
