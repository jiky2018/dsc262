<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Category extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'category';
	protected $primaryKey = 'cat_id';
	public $timestamps = false;
	protected $fillable = array('cat_name', 'keywords', 'cat_desc', 'parent_id', 'sort_order', 'template_file', 'measure_unit', 'show_in_nav', 'style', 'is_show', 'grade', 'filter_attr', 'is_top_style', 'top_style_tpl', 'style_icon', 'cat_icon', 'is_top_show', 'category_links', 'category_topic', 'pinyin_keyword', 'cat_alias_name', 'commission_rate', 'touch_icon', 'cate_title', 'cate_keywords', 'cate_description');
	protected $guarded = array();

	public function goods()
	{
		return self::hasMany('App\\Models\\Goods', 'cat_id', 'cat_id');
	}

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

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getTemplateFile()
	{
		return $this->template_file;
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

	public function getIsShow()
	{
		return $this->is_show;
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

	public function getStyleIcon()
	{
		return $this->style_icon;
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

	public function getCommissionRate()
	{
		return $this->commission_rate;
	}

	public function getTouchIcon()
	{
		return $this->touch_icon;
	}

	public function getCateTitle()
	{
		return $this->cate_title;
	}

	public function getCateKeywords()
	{
		return $this->cate_keywords;
	}

	public function getCateDescription()
	{
		return $this->cate_description;
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

	public function setTemplateFile($value)
	{
		$this->template_file = $value;
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

	public function setIsShow($value)
	{
		$this->is_show = $value;
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

	public function setCommissionRate($value)
	{
		$this->commission_rate = $value;
		return $this;
	}

	public function setTouchIcon($value)
	{
		$this->touch_icon = $value;
		return $this;
	}

	public function setCateTitle($value)
	{
		$this->cate_title = $value;
		return $this;
	}

	public function setCateKeywords($value)
	{
		$this->cate_keywords = $value;
		return $this;
	}

	public function setCateDescription($value)
	{
		$this->cate_description = $value;
		return $this;
	}
}

?>
