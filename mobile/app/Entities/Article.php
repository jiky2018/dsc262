<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Article extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'article';
	protected $primaryKey = 'article_id';
	public $timestamps = false;
	protected $fillable = array('cat_id', 'title', 'content', 'author', 'author_email', 'keywords', 'article_type', 'is_open', 'add_time', 'file_url', 'open_type', 'link', 'description', 'sort_order');
	protected $guarded = array();

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getAuthor()
	{
		return $this->author;
	}

	public function getAuthorEmail()
	{
		return $this->author_email;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function getArticleType()
	{
		return $this->article_type;
	}

	public function getIsOpen()
	{
		return $this->is_open;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getFileUrl()
	{
		return $this->file_url;
	}

	public function getOpenType()
	{
		return $this->open_type;
	}

	public function getLink()
	{
		return $this->link;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setTitle($value)
	{
		$this->title = $value;
		return $this;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setAuthor($value)
	{
		$this->author = $value;
		return $this;
	}

	public function setAuthorEmail($value)
	{
		$this->author_email = $value;
		return $this;
	}

	public function setKeywords($value)
	{
		$this->keywords = $value;
		return $this;
	}

	public function setArticleType($value)
	{
		$this->article_type = $value;
		return $this;
	}

	public function setIsOpen($value)
	{
		$this->is_open = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setFileUrl($value)
	{
		$this->file_url = $value;
		return $this;
	}

	public function setOpenType($value)
	{
		$this->open_type = $value;
		return $this;
	}

	public function setLink($value)
	{
		$this->link = $value;
		return $this;
	}

	public function setDescription($value)
	{
		$this->description = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}
}

?>
