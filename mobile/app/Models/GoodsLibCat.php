<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class GoodsLibCat extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_lib_cat';
	protected $primaryKey = 'cat_id';
	public $timestamps = false;
	protected $fillable = array('parent_id', 'cat_name', 'is_show', 'sort_order');
	protected $guarded = array();

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getCatName()
	{
		return $this->cat_name;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setCatName($value)
	{
		$this->cat_name = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}
}

?>
