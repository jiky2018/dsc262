<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class AutoManage extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'auto_manage';
	public $timestamps = false;
	protected $fillable = array('item_id', 'type', 'starttime', 'endtime');
	protected $guarded = array();

	public function getItemId()
	{
		return $this->item_id;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getStarttime()
	{
		return $this->starttime;
	}

	public function getEndtime()
	{
		return $this->endtime;
	}

	public function setItemId($value)
	{
		$this->item_id = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setStarttime($value)
	{
		$this->starttime = $value;
		return $this;
	}

	public function setEndtime($value)
	{
		$this->endtime = $value;
		return $this;
	}
}

?>
