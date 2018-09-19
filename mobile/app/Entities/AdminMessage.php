<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class AdminMessage extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'admin_message';
	protected $primaryKey = 'message_id';
	public $timestamps = false;
	protected $fillable = array('sender_id', 'receiver_id', 'sent_time', 'read_time', 'readed', 'deleted', 'title', 'message');
	protected $guarded = array();

	public function getSenderId()
	{
		return $this->sender_id;
	}

	public function getReceiverId()
	{
		return $this->receiver_id;
	}

	public function getSentTime()
	{
		return $this->sent_time;
	}

	public function getReadTime()
	{
		return $this->read_time;
	}

	public function getReaded()
	{
		return $this->readed;
	}

	public function getDeleted()
	{
		return $this->deleted;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function setSenderId($value)
	{
		$this->sender_id = $value;
		return $this;
	}

	public function setReceiverId($value)
	{
		$this->receiver_id = $value;
		return $this;
	}

	public function setSentTime($value)
	{
		$this->sent_time = $value;
		return $this;
	}

	public function setReadTime($value)
	{
		$this->read_time = $value;
		return $this;
	}

	public function setReaded($value)
	{
		$this->readed = $value;
		return $this;
	}

	public function setDeleted($value)
	{
		$this->deleted = $value;
		return $this;
	}

	public function setTitle($value)
	{
		$this->title = $value;
		return $this;
	}

	public function setMessage($value)
	{
		$this->message = $value;
		return $this;
	}
}

?>
