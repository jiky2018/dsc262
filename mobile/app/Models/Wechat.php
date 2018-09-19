<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Wechat extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat';
	public $timestamps = false;
	protected $fillable = array('name', 'orgid', 'weixin', 'token', 'appid', 'appsecret', 'encodingaeskey', 'type', 'oauth_status', 'secret_key', 'oauth_redirecturi', 'oauth_count', 'time', 'sort', 'status', 'default_wx', 'ru_id');
	protected $guarded = array();

	public function getName()
	{
		return $this->name;
	}

	public function getOrgid()
	{
		return $this->orgid;
	}

	public function getWeixin()
	{
		return $this->weixin;
	}

	public function getToken()
	{
		return $this->token;
	}

	public function getAppid()
	{
		return $this->appid;
	}

	public function getAppsecret()
	{
		return $this->appsecret;
	}

	public function getEncodingaeskey()
	{
		return $this->encodingaeskey;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getOauthStatus()
	{
		return $this->oauth_status;
	}

	public function getSecretKey()
	{
		return $this->secret_key;
	}

	public function getOauthRedirecturi()
	{
		return $this->oauth_redirecturi;
	}

	public function getOauthCount()
	{
		return $this->oauth_count;
	}

	public function getTime()
	{
		return $this->time;
	}

	public function getSort()
	{
		return $this->sort;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getDefaultWx()
	{
		return $this->default_wx;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}

	public function setOrgid($value)
	{
		$this->orgid = $value;
		return $this;
	}

	public function setWeixin($value)
	{
		$this->weixin = $value;
		return $this;
	}

	public function setToken($value)
	{
		$this->token = $value;
		return $this;
	}

	public function setAppid($value)
	{
		$this->appid = $value;
		return $this;
	}

	public function setAppsecret($value)
	{
		$this->appsecret = $value;
		return $this;
	}

	public function setEncodingaeskey($value)
	{
		$this->encodingaeskey = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setOauthStatus($value)
	{
		$this->oauth_status = $value;
		return $this;
	}

	public function setSecretKey($value)
	{
		$this->secret_key = $value;
		return $this;
	}

	public function setOauthRedirecturi($value)
	{
		$this->oauth_redirecturi = $value;
		return $this;
	}

	public function setOauthCount($value)
	{
		$this->oauth_count = $value;
		return $this;
	}

	public function setTime($value)
	{
		$this->time = $value;
		return $this;
	}

	public function setSort($value)
	{
		$this->sort = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setDefaultWx($value)
	{
		$this->default_wx = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}
}

?>
