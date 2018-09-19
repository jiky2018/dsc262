<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Article extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'article';
	protected $primaryKey = 'article_id';
	public $timestamps = false;
	protected $fillable = array('cat_id', 'title', 'content', 'author', 'author_email', 'keywords', 'article_type', 'is_open', 'add_time', 'file_url', 'open_type', 'link', 'description', 'sort_order');
	protected $guarded = array();
	protected $hidden = array('article_id', 'cat_id', 'is_open', 'open_type', 'author_email', 'article_type');
	protected $appends = array('id', 'url', 'album', 'amity_time');

	public function extend()
	{
		return $this->hasOne('App\\Models\\ArticleExtend', 'article_id', 'article_id');
	}

	public function comment()
	{
		return $this->hasMany('App\\Models\\Comment', 'id_value', 'article_id');
	}

	public function goods()
	{
		return $this->belongsToMany('App\\Models\\Goods', 'goods_article', 'article_id', 'goods_id');
	}

	public function getAddTimeAttribute()
	{
		return local_date('Y-m-d', $this->attributes['add_time']);
	}

	public function getAmityTimeAttribute()
	{
		return friendlyDate(strtotime(local_date('Y-m-d H:i:s', $this->attributes['add_time'])), 'moremohu');
	}

	public function getIdAttribute()
	{
		return $this->attributes['article_id'];
	}

	public function getAlbumAttribute()
	{
		$pattern = '/<[img|IMG].*?src=[\\\'|"](.*?(?:[\\.gif|\\.jpg|\\.png|\\.bmp|\\.jpeg]))[\\\'|"].*?[\\/]?>/';
		preg_match_all($pattern, $this->attributes['content'], $match);
		$album = array();

		if (0 < count($match[1])) {
			foreach ($match[1] as $img) {
				if (strtolower(substr($img, 0, 4)) != 'http') {
					$realpath = mb_substr($img, stripos($img, 'images/'));
					$album[] = get_image_path($realpath);
				}
				else {
					$album[] = $img;
				}
			}
		}

		if (3 < count($album)) {
			$album = array_slice($album, 0, 3);
		}

		return $album;
	}

	public function getUrlAttribute()
	{
		return url('article/index/detail', array('id' => $this->attributes['article_id']));
	}

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
