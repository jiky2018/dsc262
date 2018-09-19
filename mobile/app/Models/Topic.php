<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Topic extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'topic';
	public $timestamps = false;
	protected $fillable = array('topic_id', 'user_id', 'title', 'intro', 'start_time', 'end_time', 'data', 'template', 'css', 'topic_img', 'title_pic', 'base_style', 'htmls', 'keywords', 'description', 'review_status', 'review_content');
	protected $guarded = array();

	public function getTopicId()
	{
		return $this->topic_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getIntro()
	{
		return $this->intro;
	}

	public function getStartTime()
	{
		return $this->start_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getTemplate()
	{
		return $this->template;
	}

	public function getCss()
	{
		return $this->css;
	}

	public function getTopicImg()
	{
		return $this->topic_img;
	}

	public function getTitlePic()
	{
		return $this->title_pic;
	}

	public function getBaseStyle()
	{
		return $this->base_style;
	}

	public function getHtmls()
	{
		return $this->htmls;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewContent()
	{
		return $this->review_content;
	}

	public function setTopicId($value)
	{
		$this->topic_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setTitle($value)
	{
		$this->title = $value;
		return $this;
	}

	public function setIntro($value)
	{
		$this->intro = $value;
		return $this;
	}

	public function setStartTime($value)
	{
		$this->start_time = $value;
		return $this;
	}

	public function setEndTime($value)
	{
		$this->end_time = $value;
		return $this;
	}

	public function setData($value)
	{
		$this->data = $value;
		return $this;
	}

	public function setTemplate($value)
	{
		$this->template = $value;
		return $this;
	}

	public function setCss($value)
	{
		$this->css = $value;
		return $this;
	}

	public function setTopicImg($value)
	{
		$this->topic_img = $value;
		return $this;
	}

	public function setTitlePic($value)
	{
		$this->title_pic = $value;
		return $this;
	}

	public function setBaseStyle($value)
	{
		$this->base_style = $value;
		return $this;
	}

	public function setHtmls($value)
	{
		$this->htmls = $value;
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

	public function setReviewStatus($value)
	{
		$this->review_status = $value;
		return $this;
	}

	public function setReviewContent($value)
	{
		$this->review_content = $value;
		return $this;
	}
}

?>
