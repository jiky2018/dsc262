<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ReturnCause extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'return_cause';
	protected $primaryKey = 'cause_id';
	public $timestamps = false;
	protected $fillable = array('cause_name', 'parent_id', 'sort_order', 'is_show');
	protected $guarded = array();

	public function getCauseName()
	{
		return $this->cause_name;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function setCauseName($value)
	{
		$this->cause_name = $value;
		return $this;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}
}

?>
