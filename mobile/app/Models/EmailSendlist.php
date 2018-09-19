<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class EmailSendlist extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'email_sendlist';
	public $timestamps = false;
	protected $fillable = array('email', 'template_id', 'email_content', 'error', 'pri', 'last_send');
	protected $guarded = array();

	public function getEmail()
	{
		return $this->email;
	}

	public function getTemplateId()
	{
		return $this->template_id;
	}

	public function getEmailContent()
	{
		return $this->email_content;
	}

	public function getError()
	{
		return $this->error;
	}

	public function getPri()
	{
		return $this->pri;
	}

	public function getLastSend()
	{
		return $this->last_send;
	}

	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}

	public function setTemplateId($value)
	{
		$this->template_id = $value;
		return $this;
	}

	public function setEmailContent($value)
	{
		$this->email_content = $value;
		return $this;
	}

	public function setError($value)
	{
		$this->error = $value;
		return $this;
	}

	public function setPri($value)
	{
		$this->pri = $value;
		return $this;
	}

	public function setLastSend($value)
	{
		$this->last_send = $value;
		return $this;
	}
}

?>
