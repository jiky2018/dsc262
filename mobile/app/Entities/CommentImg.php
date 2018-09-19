<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class CommentImg extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'comment_img';
	public $timestamps = false;
	protected $fillable = array('user_id', 'order_id', 'rec_id', 'goods_id', 'comment_id', 'comment_img', 'img_thumb', 'cont_desc');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getRecId()
	{
		return $this->rec_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getCommentId()
	{
		return $this->comment_id;
	}

	public function getCommentImg()
	{
		return $this->comment_img;
	}

	public function getImgThumb()
	{
		return $this->img_thumb;
	}

	public function getContDesc()
	{
		return $this->cont_desc;
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

	public function setRecId($value)
	{
		$this->rec_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setCommentId($value)
	{
		$this->comment_id = $value;
		return $this;
	}

	public function setCommentImg($value)
	{
		$this->comment_img = $value;
		return $this;
	}

	public function setImgThumb($value)
	{
		$this->img_thumb = $value;
		return $this;
	}

	public function setContDesc($value)
	{
		$this->cont_desc = $value;
		return $this;
	}
}

?>
