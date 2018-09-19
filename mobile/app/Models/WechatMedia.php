<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WechatMedia extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_media';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'title', 'command', 'author', 'is_show', 'digest', 'content', 'link', 'file', 'size', 'file_name', 'thumb', 'add_time', 'edit_time', 'type', 'article_id', 'sort');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getCommand()
	{
		return $this->command;
	}

	public function getAuthor()
	{
		return $this->author;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function getDigest()
	{
		return $this->digest;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getLink()
	{
		return $this->link;
	}

	public function getFile()
	{
		return $this->file;
	}

	public function getSize()
	{
		return $this->size;
	}

	public function getFileName()
	{
		return $this->file_name;
	}

	public function getThumb()
	{
		return $this->thumb;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getEditTime()
	{
		return $this->edit_time;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getArticleId()
	{
		return $this->article_id;
	}

	public function getSort()
	{
		return $this->sort;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setTitle($value)
	{
		$this->title = $value;
		return $this;
	}

	public function setCommand($value)
	{
		$this->command = $value;
		return $this;
	}

	public function setAuthor($value)
	{
		$this->author = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}

	public function setDigest($value)
	{
		$this->digest = $value;
		return $this;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setLink($value)
	{
		$this->link = $value;
		return $this;
	}

	public function setFile($value)
	{
		$this->file = $value;
		return $this;
	}

	public function setSize($value)
	{
		$this->size = $value;
		return $this;
	}

	public function setFileName($value)
	{
		$this->file_name = $value;
		return $this;
	}

	public function setThumb($value)
	{
		$this->thumb = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setEditTime($value)
	{
		$this->edit_time = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setArticleId($value)
	{
		$this->article_id = $value;
		return $this;
	}

	public function setSort($value)
	{
		$this->sort = $value;
		return $this;
	}
}

?>
