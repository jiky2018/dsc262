<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WxappTemplate extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wxapp_template';
	public $timestamps = false;
	protected $fillable = array('wx_wechat_id', 'wx_template_id', 'wx_code', 'wx_content', 'wx_template', 'wx_keyword_id', 'wx_title', 'add_time', 'status');
	protected $guarded = array();

	public function getWxWechatId()
	{
		return $this->wx_wechat_id;
	}

	public function getWxTemplateId()
	{
		return $this->wx_template_id;
	}

	public function getWxCode()
	{
		return $this->wx_code;
	}

	public function getWxContent()
	{
		return $this->wx_content;
	}

	public function getWxTemplate()
	{
		return $this->wx_template;
	}

	public function getWxKeywordId()
	{
		return $this->wx_keyword_id;
	}

	public function getWxTitle()
	{
		return $this->wx_title;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setWxWechatId($value)
	{
		$this->wx_wechat_id = $value;
		return $this;
	}

	public function setWxTemplateId($value)
	{
		$this->wx_template_id = $value;
		return $this;
	}

	public function setWxCode($value)
	{
		$this->wx_code = $value;
		return $this;
	}

	public function setWxContent($value)
	{
		$this->wx_content = $value;
		return $this;
	}

	public function setWxTemplate($value)
	{
		$this->wx_template = $value;
		return $this;
	}

	public function setWxKeywordId($value)
	{
		$this->wx_keyword_id = $value;
		return $this;
	}

	public function setWxTitle($value)
	{
		$this->wx_title = $value;
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
