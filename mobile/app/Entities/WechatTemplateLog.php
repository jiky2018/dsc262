<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatTemplateLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_template_log';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'msgid', 'code', 'openid', 'data', 'url', 'status');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getMsgid()
	{
		return $this->msgid;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getOpenid()
	{
		return $this->openid;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setMsgid($value)
	{
		$this->msgid = $value;
		return $this;
	}

	public function setCode($value)
	{
		$this->code = $value;
		return $this;
	}

	public function setOpenid($value)
	{
		$this->openid = $value;
		return $this;
	}

	public function setData($value)
	{
		$this->data = $value;
		return $this;
	}

	public function setUrl($value)
	{
		$this->url = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}
}

?>
