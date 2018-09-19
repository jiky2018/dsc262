<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class GoodsReportTitle extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_report_title';
	protected $primaryKey = 'title_id';
	public $timestamps = false;
	protected $fillable = array('type_id', 'title_name', 'is_show');
	protected $guarded = array();

	public function getTypeId()
	{
		return $this->type_id;
	}

	public function getTitleName()
	{
		return $this->title_name;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function setTypeId($value)
	{
		$this->type_id = $value;
		return $this;
	}

	public function setTitleName($value)
	{
		$this->title_name = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}
}

?>
