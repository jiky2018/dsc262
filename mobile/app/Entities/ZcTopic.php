<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ZcTopic extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'zc_topic';
	protected $primaryKey = 'topic_id';
	public $timestamps = false;
	protected $fillable = array('parent_topic_id', 'reply_topic_id', 'topic_status', 'topic_content', 'user_id', 'pid', 'add_time');
	protected $guarded = array();

	public function getParentTopicId()
	{
		return $this->parent_topic_id;
	}

	public function getReplyTopicId()
	{
		return $this->reply_topic_id;
	}

	public function getTopicStatus()
	{
		return $this->topic_status;
	}

	public function getTopicContent()
	{
		return $this->topic_content;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getPid()
	{
		return $this->pid;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setParentTopicId($value)
	{
		$this->parent_topic_id = $value;
		return $this;
	}

	public function setReplyTopicId($value)
	{
		$this->reply_topic_id = $value;
		return $this;
	}

	public function setTopicStatus($value)
	{
		$this->topic_status = $value;
		return $this;
	}

	public function setTopicContent($value)
	{
		$this->topic_content = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setPid($value)
	{
		$this->pid = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
