<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WholesaleCat extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wholesale_cat';
	protected $primaryKey = 'cat_id';
	public $timestamps = false;
	protected $fillable = array('cat_name', 'keywords', 'cat_desc', 'show_in_nav', 'style', 'is_show', 'style_icon', 'cat_icon', 'pinyin_keyword', 'cat_alias_name', 'parent_id', 'sort_order');
	protected $guarded = array();

	public function getCatName()
	{
		return $this->cat_name;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function getCatDesc()
	{
		return $this->cat_desc;
	}

	public function getShowInNav()
	{
		return $this->show_in_nav;
	}

	public function getStyle()
	{
		return $this->style;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function getStyleIcon()
	{
		return $this->style_icon;
	}

	public function getCatIcon()
	{
		return $this->cat_icon;
	}

	public function getPinyinKeyword()
	{
		return $this->pinyin_keyword;
	}

	public function getCatAliasName()
	{
		return $this->cat_alias_name;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function setCatName($value)
	{
		$this->cat_name = $value;
		return $this;
	}

	public function setKeywords($value)
	{
		$this->keywords = $value;
		return $this;
	}

	public function setCatDesc($value)
	{
		$this->cat_desc = $value;
		return $this;
	}

	public function setShowInNav($value)
	{
		$this->show_in_nav = $value;
		return $this;
	}

	public function setStyle($value)
	{
		$this->style = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}

	public function setStyleIcon($value)
	{
		$this->style_icon = $value;
		return $this;
	}

	public function setCatIcon($value)
	{
		$this->cat_icon = $value;
		return $this;
	}

	public function setPinyinKeyword($value)
	{
		$this->pinyin_keyword = $value;
		return $this;
	}

	public function setCatAliasName($value)
	{
		$this->cat_alias_name = $value;
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
}

?>
