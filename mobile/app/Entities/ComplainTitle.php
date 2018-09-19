<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ComplainTitle extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'complain_title';
	protected $primaryKey = 'title_id';
	public $timestamps = false;
	protected $fillable = array('title_name', 'title_desc', 'is_show');
	protected $guarded = array();

	public function getTitleName()
	{
		return $this->title_name;
	}

	public function getTitleDesc()
	{
		return $this->title_desc;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function setTitleName($value)
	{
		$this->title_name = $value;
		return $this;
	}

	public function setTitleDesc($value)
	{
		$this->title_desc = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}
}

?>
