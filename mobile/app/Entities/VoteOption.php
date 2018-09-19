<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class VoteOption extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'vote_option';
	protected $primaryKey = 'option_id';
	public $timestamps = false;
	protected $fillable = array('vote_id', 'option_name', 'option_count', 'option_order');
	protected $guarded = array();

	public function getVoteId()
	{
		return $this->vote_id;
	}

	public function getOptionName()
	{
		return $this->option_name;
	}

	public function getOptionCount()
	{
		return $this->option_count;
	}

	public function getOptionOrder()
	{
		return $this->option_order;
	}

	public function setVoteId($value)
	{
		$this->vote_id = $value;
		return $this;
	}

	public function setOptionName($value)
	{
		$this->option_name = $value;
		return $this;
	}

	public function setOptionCount($value)
	{
		$this->option_count = $value;
		return $this;
	}

	public function setOptionOrder($value)
	{
		$this->option_order = $value;
		return $this;
	}
}

?>
