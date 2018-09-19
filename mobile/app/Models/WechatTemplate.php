<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WechatTemplate extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_template';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'template_id', 'code', 'content', 'template', 'title', 'add_time', 'status');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getTemplateId()
	{
		return $this->template_id;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getTemplate()
	{
		return $this->template;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getAddTime()
	{
		return $this->add_time;
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

	public function setTemplateId($value)
	{
		$this->template_id = $value;
		return $this;
	}

	public function setCode($value)
	{
		$this->code = $value;
		return $this;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setTemplate($value)
	{
		$this->template = $value;
		return $this;
	}

	public function setTitle($value)
	{
		$this->title = $value;
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
