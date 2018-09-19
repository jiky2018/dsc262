<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class TouchPageView extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'touch_page_view';
	public $timestamps = true;
	protected $fillable = array('ru_id', 'type', 'page_id', 'title', 'keywords', 'description', 'data', 'pic', 'thumb_pic', 'create_at', 'update_at', 'default', 'review_status', 'is_show');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getPageId()
	{
		return $this->page_id;
	}

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

	public function getData()
	{
		return $this->data;
	}

	public function getPic()
	{
		return $this->pic;
	}

	public function getThumbPic()
	{
		return $this->thumb_pic;
	}

	public function getCreateAt()
	{
		return $this->create_at;
	}

	public function getUpdateAt()
	{
		return $this->update_at;
	}

	public function getDefault()
	{
		return $this->default;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setPageId($value)
	{
		$this->page_id = $value;
		return $this;
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

	public function setData($value)
	{
		$this->data = $value;
		return $this;
	}

	public function setPic($value)
	{
		$this->pic = $value;
		return $this;
	}

	public function setThumbPic($value)
	{
		$this->thumb_pic = $value;
		return $this;
	}

	public function setCreateAt($value)
	{
		$this->create_at = $value;
		return $this;
	}

	public function setUpdateAt($value)
	{
		$this->update_at = $value;
		return $this;
	}

	public function setDefault($value)
	{
		$this->default = $value;
		return $this;
	}

	public function setReviewStatus($value)
	{
		$this->review_status = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}
}

?>
