<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ImMessage extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'im_message';
	public $timestamps = false;
	protected $fillable = array('from_user_id', 'to_user_id', 'dialog_id', 'message', 'add_time', 'user_type', 'status');
	protected $guarded = array();

	public function getFromUserId()
	{
		return $this->from_user_id;
	}

	public function getToUserId()
	{
		return $this->to_user_id;
	}

	public function getDialogId()
	{
		return $this->dialog_id;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getUserType()
	{
		return $this->user_type;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setFromUserId($value)
	{
		$this->from_user_id = $value;
		return $this;
	}

	public function setToUserId($value)
	{
		$this->to_user_id = $value;
		return $this;
	}

	public function setDialogId($value)
	{
		$this->dialog_id = $value;
		return $this;
	}

	public function setMessage($value)
	{
		$this->message = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setUserType($value)
	{
		$this->user_type = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}
}

?>
