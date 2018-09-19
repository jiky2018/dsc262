<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ArticleCat extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'article_cat';
	protected $primaryKey = 'cat_id';
	public $timestamps = false;
	protected $fillable = array('cat_name', 'cat_type', 'keywords', 'cat_desc', 'sort_order', 'show_in_nav', 'parent_id');
	protected $guarded = array();
	protected $hidden = array('cat_id', 'cat_type');
	protected $appends = array('id', 'url');

	public function article()
	{
		return $this->belongsTo('App\\Models\\Article', 'cat_id', 'cat_id');
	}

	public function getIdAttribute()
	{
		return $this->attributes['cat_id'];
	}

	public function getUrlAttribute()
	{
		return url('article/index/index', array('cat_id' => $this->attributes['cat_id']));
	}

	public function getCatName()
	{
		return $this->cat_name;
	}

	public function getCatType()
	{
		return $this->cat_type;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function getCatDesc()
	{
		return $this->cat_desc;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getShowInNav()
	{
		return $this->show_in_nav;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function setCatName($value)
	{
		$this->cat_name = $value;
		return $this;
	}

	public function setCatType($value)
	{
		$this->cat_type = $value;
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

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setShowInNav($value)
	{
		$this->show_in_nav = $value;
		return $this;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}
}

?>
