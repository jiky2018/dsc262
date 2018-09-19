<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class TeamLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'team_log';
	protected $primaryKey = 'team_id';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'start_time', 'status', 'is_show', 't_id');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getStartTime()
	{
		return $this->start_time;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function getTId()
	{
		return $this->t_id;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setStartTime($value)
	{
		$this->start_time = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}

	public function setTId($value)
	{
		$this->t_id = $value;
		return $this;
	}
}

?>
