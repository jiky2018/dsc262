<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class TeamCategory extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'team_category';
	public $timestamps = false;
	protected $fillable = array('name', 'parent_id', 'content', 'tc_img', 'sort_order', 'status');
	protected $guarded = array();

	public function getName()
	{
		return $this->name;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getTcImg()
	{
		return $this->tc_img;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setTcImg($value)
	{
		$this->tc_img = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}
}

?>
