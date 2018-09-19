<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MassSmsTemplate extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'mass_sms_template';
	public $timestamps = false;
	protected $fillable = array('temp_id', 'temp_content', 'content', 'add_time', 'set_sign', 'signature');
	protected $guarded = array();

	public function getTempId()
	{
		return $this->temp_id;
	}

	public function getTempContent()
	{
		return $this->temp_content;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getSetSign()
	{
		return $this->set_sign;
	}

	public function getSignature()
	{
		return $this->signature;
	}

	public function setTempId($value)
	{
		$this->temp_id = $value;
		return $this;
	}

	public function setTempContent($value)
	{
		$this->temp_content = $value;
		return $this;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setSetSign($value)
	{
		$this->set_sign = $value;
		return $this;
	}

	public function setSignature($value)
	{
		$this->signature = $value;
		return $this;
	}
}

?>
