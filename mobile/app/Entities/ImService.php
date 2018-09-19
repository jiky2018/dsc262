<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ImService extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'im_service';
	public $timestamps = false;
	protected $fillable = array('user_id', 'user_name', 'nick_name', 'post_desc', 'login_time', 'chat_status', 'status');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getNickName()
	{
		return $this->nick_name;
	}

	public function getPostDesc()
	{
		return $this->post_desc;
	}

	public function getLoginTime()
	{
		return $this->login_time;
	}

	public function getChatStatus()
	{
		return $this->chat_status;
	}

	public function getStatus()
	{
		return $this->status;
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

	public function setNickName($value)
	{
		$this->nick_name = $value;
		return $this;
	}

	public function setPostDesc($value)
	{
		$this->post_desc = $value;
		return $this;
	}

	public function setLoginTime($value)
	{
		$this->login_time = $value;
		return $this;
	}

	public function setChatStatus($value)
	{
		$this->chat_status = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}
}

?>
