<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class GoodsLibGallery extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_lib_gallery';
	protected $primaryKey = 'img_id';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'img_url', 'img_desc', 'thumb_url', 'img_original', 'single_id');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getImgUrl()
	{
		return $this->img_url;
	}

	public function getImgDesc()
	{
		return $this->img_desc;
	}

	public function getThumbUrl()
	{
		return $this->thumb_url;
	}

	public function getImgOriginal()
	{
		return $this->img_original;
	}

	public function getSingleId()
	{
		return $this->single_id;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setImgUrl($value)
	{
		$this->img_url = $value;
		return $this;
	}

	public function setImgDesc($value)
	{
		$this->img_desc = $value;
		return $this;
	}

	public function setThumbUrl($value)
	{
		$this->thumb_url = $value;
		return $this;
	}

	public function setImgOriginal($value)
	{
		$this->img_original = $value;
		return $this;
	}

	public function setSingleId($value)
	{
		$this->single_id = $value;
		return $this;
	}
}

?>
