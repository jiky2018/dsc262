<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WechatPoint extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_point';
	public $timestamps = false;
	protected $fillable = array('log_id', 'wechat_id', 'openid', 'keywords', 'createtime');
	protected $guarded = array();

	public function getLogId()
	{
		return $this->log_id;
	}

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getOpenid()
	{
		return $this->openid;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function getCreatetime()
	{
		return $this->createtime;
	}

	public function setLogId($value)
	{
		$this->log_id = $value;
		return $this;
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

	public function setKeywords($value)
	{
		$this->keywords = $value;
		return $this;
	}

	public function setCreatetime($value)
	{
		$this->createtime = $value;
		return $this;
	}
}

?>
