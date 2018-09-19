<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class SingleSunImages extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'single_sun_images';
	public $timestamps = false;
	protected $fillable = array('user_id', 'order_id', 'goods_id', 'img_file', 'img_thumb', 'cont_desc', 'comment_id', 'img_type');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getImgFile()
	{
		return $this->img_file;
	}

	public function getImgThumb()
	{
		return $this->img_thumb;
	}

	public function getContDesc()
	{
		return $this->cont_desc;
	}

	public function getCommentId()
	{
		return $this->comment_id;
	}

	public function getImgType()
	{
		return $this->img_type;
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

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setImgFile($value)
	{
		$this->img_file = $value;
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

	public function setCommentId($value)
	{
		$this->comment_id = $value;
		return $this;
	}

	public function setImgType($value)
	{
		$this->img_type = $value;
		return $this;
	}
}

?>
