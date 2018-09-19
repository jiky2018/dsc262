<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class SellerShopslide extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_shopslide';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'img_url', 'img_link', 'img_desc', 'img_order', 'slide_type', 'is_show', 'seller_theme', 'install_img');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getImgUrl()
	{
		return $this->img_url;
	}

	public function getImgLink()
	{
		return $this->img_link;
	}

	public function getImgDesc()
	{
		return $this->img_desc;
	}

	public function getImgOrder()
	{
		return $this->img_order;
	}

	public function getSlideType()
	{
		return $this->slide_type;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function getSellerTheme()
	{
		return $this->seller_theme;
	}

	public function getInstallImg()
	{
		return $this->install_img;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setImgUrl($value)
	{
		$this->img_url = $value;
		return $this;
	}

	public function setImgLink($value)
	{
		$this->img_link = $value;
		return $this;
	}

	public function setImgDesc($value)
	{
		$this->img_desc = $value;
		return $this;
	}

	public function setImgOrder($value)
	{
		$this->img_order = $value;
		return $this;
	}

	public function setSlideType($value)
	{
		$this->slide_type = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}

	public function setSellerTheme($value)
	{
		$this->seller_theme = $value;
		return $this;
	}

	public function setInstallImg($value)
	{
		$this->install_img = $value;
		return $this;
	}
}

?>
