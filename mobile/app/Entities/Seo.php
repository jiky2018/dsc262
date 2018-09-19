<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Seo extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seo';
	public $timestamps = false;
	protected $fillable = array('title', 'keywords', 'description', 'type');
	protected $guarded = array();

	public function getTitle()
	{
		return $this->title;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setTitle($value)
	{
		$this->title = $value;
		return $this;
	}

	public function setKeywords($value)
	{
		$this->keywords = $value;
		return $this;
	}

	public function setDescription($value)
	{
		$this->description = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}
}

?>
