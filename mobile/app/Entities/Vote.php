<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Vote extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'vote';
	protected $primaryKey = 'vote_id';
	public $timestamps = false;
	protected $fillable = array('vote_name', 'start_time', 'end_time', 'can_multi', 'vote_count');
	protected $guarded = array();

	public function getVoteName()
	{
		return $this->vote_name;
	}

	public function getStartTime()
	{
		return $this->start_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function getCanMulti()
	{
		return $this->can_multi;
	}

	public function getVoteCount()
	{
		return $this->vote_count;
	}

	public function setVoteName($value)
	{
		$this->vote_name = $value;
		return $this;
	}

	public function setStartTime($value)
	{
		$this->start_time = $value;
		return $this;
	}

	public function setEndTime($value)
	{
		$this->end_time = $value;
		return $this;
	}

	public function setCanMulti($value)
	{
		$this->can_multi = $value;
		return $this;
	}

	public function setVoteCount($value)
	{
		$this->vote_count = $value;
		return $this;
	}
}

?>
