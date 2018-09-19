<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatRedpackAdvertice extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_redpack_advertice';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'market_id', 'icon', 'content', 'url');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getMarketId()
	{
		return $this->market_id;
	}

	public function getIcon()
	{
		return $this->icon;
	}

	public function getContent()
	{
		return $this->content;
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

	public function setMarketId($value)
	{
		$this->market_id = $value;
		return $this;
	}

	public function setIcon($value)
	{
		$this->icon = $value;
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
}

?>
