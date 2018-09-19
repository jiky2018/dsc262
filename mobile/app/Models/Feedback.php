<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Feedback extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'feedback';
	protected $primaryKey = 'msg_id';
	public $timestamps = false;
	protected $fillable = array('parent_id', 'user_id', 'user_name', 'user_email', 'msg_title', 'msg_type', 'msg_status', 'msg_content', 'msg_time', 'message_img', 'order_id', 'msg_area');
	protected $guarded = array();

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getUserEmail()
	{
		return $this->user_email;
	}

	public function getMsgTitle()
	{
		return $this->msg_title;
	}

	public function getMsgType()
	{
		return $this->msg_type;
	}

	public function getMsgStatus()
	{
		return $this->msg_status;
	}

	public function getMsgContent()
	{
		return $this->msg_content;
	}

	public function getMsgTime()
	{
		return $this->msg_time;
	}

	public function getMessageImg()
	{
		return $this->message_img;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getMsgArea()
	{
		return $this->msg_area;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setUserName($value)
	{
		$this->user_name = $value;
		return $this;
	}

	public function setUserEmail($value)
	{
		$this->user_email = $value;
		return $this;
	}

	public function setMsgTitle($value)
	{
		$this->msg_title = $value;
		return $this;
	}

	public function setMsgType($value)
	{
		$this->msg_type = $value;
		return $this;
	}

	public function setMsgStatus($value)
	{
		$this->msg_status = $value;
		return $this;
	}

	public function setMsgContent($value)
	{
		$this->msg_content = $value;
		return $this;
	}

	public function setMsgTime($value)
	{
		$this->msg_time = $value;
		return $this;
	}

	public function setMessageImg($value)
	{
		$this->message_img = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setMsgArea($value)
	{
		$this->msg_area = $value;
		return $this;
	}
}

?>
