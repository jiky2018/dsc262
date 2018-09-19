<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class LinkGoodsDesc extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'link_goods_desc';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'ru_id', 'desc_name', 'goods_desc', 'review_status', 'review_content');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getDescName()
	{
		return $this->desc_name;
	}

	public function getGoodsDesc()
	{
		return $this->goods_desc;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewContent()
	{
		return $this->review_content;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setDescName($value)
	{
		$this->desc_name = $value;
		return $this;
	}

	public function setGoodsDesc($value)
	{
		$this->goods_desc = $value;
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
