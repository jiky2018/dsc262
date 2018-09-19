<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ReturnAction extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'return_action';
	protected $primaryKey = 'action_id';
	public $timestamps = false;
	protected $fillable = array('ret_id', 'action_user', 'return_status', 'refound_status', 'action_place', 'action_note', 'log_time');
	protected $guarded = array();

	public function getRetId()
	{
		return $this->ret_id;
	}

	public function getActionUser()
	{
		return $this->action_user;
	}

	public function getReturnStatus()
	{
		return $this->return_status;
	}

	public function getRefoundStatus()
	{
		return $this->refound_status;
	}

	public function getActionPlace()
	{
		return $this->action_place;
	}

	public function getActionNote()
	{
		return $this->action_note;
	}

	public function getLogTime()
	{
		return $this->log_time;
	}

	public function setRetId($value)
	{
		$this->ret_id = $value;
		return $this;
	}

	public function setActionUser($value)
	{
		$this->action_user = $value;
		return $this;
	}

	public function setReturnStatus($value)
	{
		$this->return_status = $value;
		return $this;
	}

	public function setRefoundStatus($value)
	{
		$this->refound_status = $value;
		return $this;
	}

	public function setActionPlace($value)
	{
		$this->action_place = $value;
		return $this;
	}

	public function setActionNote($value)
	{
		$this->action_note = $value;
		return $this;
	}

	public function setLogTime($value)
	{
		$this->log_time = $value;
		return $this;
	}
}

?>
