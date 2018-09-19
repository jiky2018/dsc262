<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ZcCategory extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'zc_category';
	protected $primaryKey = 'cat_id';
	public $timestamps = false;
	protected $fillable = array('cat_name', 'keywords', 'measure_unit', 'show_in_nav', 'style', 'grade', 'filter_attr', 'is_top_style', 'top_style_tpl', 'cat_icon', 'is_top_show', 'category_links', 'category_topic', 'pinyin_keyword', 'cat_alias_name', 'template_file', 'cat_desc', 'parent_id', 'sort_order', 'is_show');
	protected $guarded = array();

	public function getCatName()
	{
		return $this->cat_name;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function getMeasureUnit()
	{
		return $this->measure_unit;
	}

	public function getShowInNav()
	{
		return $this->show_in_nav;
	}

	public function getStyle()
	{
		return $this->style;
	}

	public function getGrade()
	{
		return $this->grade;
	}

	public function getFilterAttr()
	{
		return $this->filter_attr;
	}

	public function getIsTopStyle()
	{
		return $this->is_top_style;
	}

	public function getTopStyleTpl()
	{
		return $this->top_style_tpl;
	}

	public function getCatIcon()
	{
		return $this->cat_icon;
	}

	public function getIsTopShow()
	{
		return $this->is_top_show;
	}

	public function getCategoryLinks()
	{
		return $this->category_links;
	}

	public function getCategoryTopic()
	{
		return $this->category_topic;
	}

	public function getPinyinKeyword()
	{
		return $this->pinyin_keyword;
	}

	public function getCatAliasName()
	{
		return $this->cat_alias_name;
	}

	public function getTemplateFile()
	{
		return $this->template_file;
	}

	public function getCatDesc()
	{
		return $this->cat_desc;
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

	public function setMeasureUnit($value)
	{
		$this->measure_unit = $value;
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

	public function setGrade($value)
	{
		$this->grade = $value;
		return $this;
	}

	public function setFilterAttr($value)
	{
		$this->filter_attr = $value;
		return $this;
	}

	public function setIsTopStyle($value)
	{
		$this->is_top_style = $value;
		return $this;
	}

	public function setTopStyleTpl($value)
	{
		$this->top_style_tpl = $value;
		return $this;
	}

	public function setCatIcon($value)
	{
		$this->cat_icon = $value;
		return $this;
	}

	public function setIsTopShow($value)
	{
		$this->is_top_show = $value;
		return $this;
	}

	public function setCategoryLinks($value)
	{
		$this->category_links = $value;
		return $this;
	}

	public function setCategoryTopic($value)
	{
		$this->category_topic = $value;
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

	public function setTemplateFile($value)
	{
		$this->template_file = $value;
		return $this;
	}

	public function setCatDesc($value)
	{
		$this->cat_desc = $value;
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
