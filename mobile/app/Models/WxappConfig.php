<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WxappConfig extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wxapp_config';
	public $timestamps = false;
	protected $fillable = array('wx_appname', 'wx_appid', 'wx_appsecret', 'wx_mch_id', 'wx_mch_key', 'token_secret', 'add_time', 'status');
	protected $guarded = array();

	public function getWxAppname()
	{
		return $this->wx_appname;
	}

	public function getWxAppid()
	{
		return $this->wx_appid;
	}

	public function getWxAppsecret()
	{
		return $this->wx_appsecret;
	}

	public function getWxMchId()
	{
		return $this->wx_mch_id;
	}

	public function getWxMchKey()
	{
		return $this->wx_mch_key;
	}

	public function getTokenSecret()
	{
		return $this->token_secret;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setWxAppname($value)
	{
		$this->wx_appname = $value;
		return $this;
	}

	public function setWxAppid($value)
	{
		$this->wx_appid = $value;
		return $this;
	}

	public function setWxAppsecret($value)
	{
		$this->wx_appsecret = $value;
		return $this;
	}

	public function setWxMchId($value)
	{
		$this->wx_mch_id = $value;
		return $this;
	}

	public function setWxMchKey($value)
	{
		$this->wx_mch_key = $value;
		return $this;
	}

	public function setTokenSecret($value)
	{
		$this->token_secret = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}
}

?>
