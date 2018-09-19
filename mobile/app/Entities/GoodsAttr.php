<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class GoodsAttr extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_attr';
	protected $primaryKey = 'goods_attr_id';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'attr_id', 'attr_value', 'color_value', 'attr_price', 'attr_sort', 'attr_img_flie', 'attr_gallery_flie', 'attr_img_site', 'attr_checked', 'attr_value1', 'lang_flag', 'attr_img', 'attr_thumb', 'img_flag', 'attr_pid', 'admin_id', 'cloud_id');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getAttrId()
	{
		return $this->attr_id;
	}

	public function getAttrValue()
	{
		return $this->attr_value;
	}

	public function getColorValue()
	{
		return $this->color_value;
	}

	public function getAttrPrice()
	{
		return $this->attr_price;
	}

	public function getAttrSort()
	{
		return $this->attr_sort;
	}

	public function getAttrImgFlie()
	{
		return $this->attr_img_flie;
	}

	public function getAttrGalleryFlie()
	{
		return $this->attr_gallery_flie;
	}

	public function getAttrImgSite()
	{
		return $this->attr_img_site;
	}

	public function getAttrChecked()
	{
		return $this->attr_checked;
	}

	public function getAttrValue1()
	{
		return $this->attr_value1;
	}

	public function getLangFlag()
	{
		return $this->lang_flag;
	}

	public function getAttrImg()
	{
		return $this->attr_img;
	}

	public function getAttrThumb()
	{
		return $this->attr_thumb;
	}

	public function getImgFlag()
	{
		return $this->img_flag;
	}

	public function getAttrPid()
	{
		return $this->attr_pid;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function getCloudId()
	{
		return $this->cloud_id;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setAttrId($value)
	{
		$this->attr_id = $value;
		return $this;
	}

	public function setAttrValue($value)
	{
		$this->attr_value = $value;
		return $this;
	}

	public function setColorValue($value)
	{
		$this->color_value = $value;
		return $this;
	}

	public function setAttrPrice($value)
	{
		$this->attr_price = $value;
		return $this;
	}

	public function setAttrSort($value)
	{
		$this->attr_sort = $value;
		return $this;
	}

	public function setAttrImgFlie($value)
	{
		$this->attr_img_flie = $value;
		return $this;
	}

	public function setAttrGalleryFlie($value)
	{
		$this->attr_gallery_flie = $value;
		return $this;
	}

	public function setAttrImgSite($value)
	{
		$this->attr_img_site = $value;
		return $this;
	}

	public function setAttrChecked($value)
	{
		$this->attr_checked = $value;
		return $this;
	}

	public function setAttrValue1($value)
	{
		$this->attr_value1 = $value;
		return $this;
	}

	public function setLangFlag($value)
	{
		$this->lang_flag = $value;
		return $this;
	}

	public function setAttrImg($value)
	{
		$this->attr_img = $value;
		return $this;
	}

	public function setAttrThumb($value)
	{
		$this->attr_thumb = $value;
		return $this;
	}

	public function setImgFlag($value)
	{
		$this->img_flag = $value;
		return $this;
	}

	public function setAttrPid($value)
	{
		$this->attr_pid = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}

	public function setCloudId($value)
	{
		$this->cloud_id = $value;
		return $this;
	}
}

?>
