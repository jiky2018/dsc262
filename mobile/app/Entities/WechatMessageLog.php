<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatMessageLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_message_log';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'fromusername', 'createtime', 'keywords', 'msgtype', 'msgid', 'is_send');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getFromusername()
	{
		return $this->fromusername;
	}

	public function getCreatetime()
	{
		return $this->createtime;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function getMsgtype()
	{
		return $this->msgtype;
	}

	public function getMsgid()
	{
		return $this->msgid;
	}

	public function getIsSend()
	{
		return $this->is_send;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setFromusername($value)
	{
		$this->fromusername = $value;
		return $this;
	}

	public function setCreatetime($value)
	{
		$this->createtime = $value;
		return $this;
	}

	public function setKeywords($value)
	{
		$this->keywords = $value;
		return $this;
	}

	public function setMsgtype($value)
	{
		$this->msgtype = $value;
		return $this;
	}

	public function setMsgid($value)
	{
		$this->msgid = $value;
		return $this;
	}

	public function setIsSend($value)
	{
		$this->is_send = $value;
		return $this;
	}
}

?>
