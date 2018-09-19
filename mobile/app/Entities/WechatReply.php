<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatReply extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_reply';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'type', 'content', 'media_id', 'rule_name', 'add_time', 'reply_type');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getMediaId()
	{
		return $this->media_id;
	}

	public function getRuleName()
	{
		return $this->rule_name;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getReplyType()
	{
		return $this->reply_type;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setMediaId($value)
	{
		$this->media_id = $value;
		return $this;
	}

	public function setRuleName($value)
	{
		$this->rule_name = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setReplyType($value)
	{
		$this->reply_type = $value;
		return $this;
	}
}

?>
