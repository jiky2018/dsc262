<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatShareCount extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_share_count';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'openid', 'share_type', 'link', 'share_time');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getOpenid()
	{
		return $this->openid;
	}

	public function getShareType()
	{
		return $this->share_type;
	}

	public function getLink()
	{
		return $this->link;
	}

	public function getShareTime()
	{
		return $this->share_time;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setOpenid($value)
	{
		$this->openid = $value;
		return $this;
	}

	public function setShareType($value)
	{
		$this->share_type = $value;
		return $this;
	}

	public function setLink($value)
	{
		$this->link = $value;
		return $this;
	}

	public function setShareTime($value)
	{
		$this->share_time = $value;
		return $this;
	}
}

?>
