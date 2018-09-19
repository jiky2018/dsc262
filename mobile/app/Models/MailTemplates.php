<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class MailTemplates extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'mail_templates';
	protected $primaryKey = 'template_id';
	public $timestamps = false;
	protected $fillable = array('template_code', 'is_html', 'template_subject', 'template_content', 'last_modify', 'last_send', 'type');
	protected $guarded = array();

	public function getTemplateCode()
	{
		return $this->template_code;
	}

	public function getIsHtml()
	{
		return $this->is_html;
	}

	public function getTemplateSubject()
	{
		return $this->template_subject;
	}

	public function getTemplateContent()
	{
		return $this->template_content;
	}

	public function getLastModify()
	{
		return $this->last_modify;
	}

	public function getLastSend()
	{
		return $this->last_send;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setTemplateCode($value)
	{
		$this->template_code = $value;
		return $this;
	}

	public function setIsHtml($value)
	{
		$this->is_html = $value;
		return $this;
	}

	public function setTemplateSubject($value)
	{
		$this->template_subject = $value;
		return $this;
	}

	public function setTemplateContent($value)
	{
		$this->template_content = $value;
		return $this;
	}

	public function setLastModify($value)
	{
		$this->last_modify = $value;
		return $this;
	}

	public function setLastSend($value)
	{
		$this->last_send = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}
}

?>
