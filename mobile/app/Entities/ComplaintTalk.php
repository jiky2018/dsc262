<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ComplaintTalk extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'complaint_talk';
	protected $primaryKey = 'talk_id';
	public $timestamps = false;
	protected $fillable = array('complaint_id', 'talk_member_id', 'talk_member_name', 'talk_member_type', 'talk_content', 'talk_state', 'admin_id', 'talk_time', 'view_state');
	protected $guarded = array();

	public function getComplaintId()
	{
		return $this->complaint_id;
	}

	public function getTalkMemberId()
	{
		return $this->talk_member_id;
	}

	public function getTalkMemberName()
	{
		return $this->talk_member_name;
	}

	public function getTalkMemberType()
	{
		return $this->talk_member_type;
	}

	public function getTalkContent()
	{
		return $this->talk_content;
	}

	public function getTalkState()
	{
		return $this->talk_state;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function getTalkTime()
	{
		return $this->talk_time;
	}

	public function getViewState()
	{
		return $this->view_state;
	}

	public function setComplaintId($value)
	{
		$this->complaint_id = $value;
		return $this;
	}

	public function setTalkMemberId($value)
	{
		$this->talk_member_id = $value;
		return $this;
	}

	public function setTalkMemberName($value)
	{
		$this->talk_member_name = $value;
		return $this;
	}

	public function setTalkMemberType($value)
	{
		$this->talk_member_type = $value;
		return $this;
	}

	public function setTalkContent($value)
	{
		$this->talk_content = $value;
		return $this;
	}

	public function setTalkState($value)
	{
		$this->talk_state = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}

	public function setTalkTime($value)
	{
		$this->talk_time = $value;
		return $this;
	}

	public function setViewState($value)
	{
		$this->view_state = $value;
		return $this;
	}
}

?>
