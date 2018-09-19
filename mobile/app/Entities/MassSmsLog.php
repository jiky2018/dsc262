<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MassSmsLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'mass_sms_log';
	public $timestamps = false;
	protected $fillable = array('template_id', 'user_id', 'send_status', 'last_send');
	protected $guarded = array();

	public function getTemplateId()
	{
		return $this->template_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getSendStatus()
	{
		return $this->send_status;
	}

	public function getLastSend()
	{
		return $this->last_send;
	}

	public function setTemplateId($value)
	{
		$this->template_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setSendStatus($value)
	{
		$this->send_status = $value;
		return $this;
	}

	public function setLastSend($value)
	{
		$this->last_send = $value;
		return $this;
	}
}

?>
