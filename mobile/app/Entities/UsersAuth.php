<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class UsersAuth extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'users_auth';
	public $timestamps = false;
	protected $fillable = array('user_id', 'user_name', 'identity_type', 'identifier', 'credential', 'verified', 'add_time', 'update_time');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getIdentityType()
	{
		return $this->identity_type;
	}

	public function getIdentifier()
	{
		return $this->identifier;
	}

	public function getCredential()
	{
		return $this->credential;
	}

	public function getVerified()
	{
		return $this->verified;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getUpdateTime()
	{
		return $this->update_time;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setUserName($value)
	{
		$this->user_name = $value;
		return $this;
	}

	public function setIdentityType($value)
	{
		$this->identity_type = $value;
		return $this;
	}

	public function setIdentifier($value)
	{
		$this->identifier = $value;
		return $this;
	}

	public function setCredential($value)
	{
		$this->credential = $value;
		return $this;
	}

	public function setVerified($value)
	{
		$this->verified = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setUpdateTime($value)
	{
		$this->update_time = $value;
		return $this;
	}
}

?>
