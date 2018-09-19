<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ConnectUser extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'connect_user';
	public $timestamps = false;
	protected $fillable = array('connect_code', 'user_id', 'is_admin', 'open_id', 'refresh_token', 'access_token', 'profile', 'create_at', 'expires_in', 'expires_at');
	protected $guarded = array();

	public function getConnectCode()
	{
		return $this->connect_code;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getIsAdmin()
	{
		return $this->is_admin;
	}

	public function getOpenId()
	{
		return $this->open_id;
	}

	public function getRefreshToken()
	{
		return $this->refresh_token;
	}

	public function getAccessToken()
	{
		return $this->access_token;
	}

	public function getProfile()
	{
		return $this->profile;
	}

	public function getCreateAt()
	{
		return $this->create_at;
	}

	public function getExpiresIn()
	{
		return $this->expires_in;
	}

	public function getExpiresAt()
	{
		return $this->expires_at;
	}

	public function setConnectCode($value)
	{
		$this->connect_code = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setIsAdmin($value)
	{
		$this->is_admin = $value;
		return $this;
	}

	public function setOpenId($value)
	{
		$this->open_id = $value;
		return $this;
	}

	public function setRefreshToken($value)
	{
		$this->refresh_token = $value;
		return $this;
	}

	public function setAccessToken($value)
	{
		$this->access_token = $value;
		return $this;
	}

	public function setProfile($value)
	{
		$this->profile = $value;
		return $this;
	}

	public function setCreateAt($value)
	{
		$this->create_at = $value;
		return $this;
	}

	public function setExpiresIn($value)
	{
		$this->expires_in = $value;
		return $this;
	}

	public function setExpiresAt($value)
	{
		$this->expires_at = $value;
		return $this;
	}
}

?>
