<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatCustomMessage extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_custom_message';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'uid', 'msg', 'send_time', 'is_wechat_admin');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getUid()
	{
		return $this->uid;
	}

	public function getMsg()
	{
		return $this->msg;
	}

	public function getSendTime()
	{
		return $this->send_time;
	}

	public function getIsWechatAdmin()
	{
		return $this->is_wechat_admin;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setUid($value)
	{
		$this->uid = $value;
		return $this;
	}

	public function setMsg($value)
	{
		$this->msg = $value;
		return $this;
	}

	public function setSendTime($value)
	{
		$this->send_time = $value;
		return $this;
	}

	public function setIsWechatAdmin($value)
	{
		$this->is_wechat_admin = $value;
		return $this;
	}
}

?>
