<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class DiscussCircle extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'discuss_circle';
	protected $primaryKey = 'dis_id';
	public $timestamps = false;
	protected $fillable = array('dis_browse_num', 'review_status', 'review_content', 'like_num', 'parent_id', 'quote_id', 'goods_id', 'user_id', 'order_id', 'dis_type', 'dis_title', 'dis_text', 'add_time', 'user_name');
	protected $guarded = array();

	public function getDisBrowseNum()
	{
		return $this->dis_browse_num;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewContent()
	{
		return $this->review_content;
	}

	public function getLikeNum()
	{
		return $this->like_num;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getQuoteId()
	{
		return $this->quote_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getDisType()
	{
		return $this->dis_type;
	}

	public function getDisTitle()
	{
		return $this->dis_title;
	}

	public function getDisText()
	{
		return $this->dis_text;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function setDisBrowseNum($value)
	{
		$this->dis_browse_num = $value;
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

	public function setLikeNum($value)
	{
		$this->like_num = $value;
		return $this;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setQuoteId($value)
	{
		$this->quote_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setDisType($value)
	{
		$this->dis_type = $value;
		return $this;
	}

	public function setDisTitle($value)
	{
		$this->dis_title = $value;
		return $this;
	}

	public function setDisText($value)
	{
		$this->dis_text = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setUserName($value)
	{
		$this->user_name = $value;
		return $this;
	}
}

?>
