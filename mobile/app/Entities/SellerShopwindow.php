<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class SellerShopwindow extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_shopwindow';
	public $timestamps = false;
	protected $fillable = array('win_type', 'win_goods_type', 'win_order', 'win_goods', 'win_name', 'win_color', 'win_img', 'win_img_link', 'ru_id', 'is_show', 'win_custom', 'seller_theme');
	protected $guarded = array();

	public function getWinType()
	{
		return $this->win_type;
	}

	public function getWinGoodsType()
	{
		return $this->win_goods_type;
	}

	public function getWinOrder()
	{
		return $this->win_order;
	}

	public function getWinGoods()
	{
		return $this->win_goods;
	}

	public function getWinName()
	{
		return $this->win_name;
	}

	public function getWinColor()
	{
		return $this->win_color;
	}

	public function getWinImg()
	{
		return $this->win_img;
	}

	public function getWinImgLink()
	{
		return $this->win_img_link;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function getWinCustom()
	{
		return $this->win_custom;
	}

	public function getSellerTheme()
	{
		return $this->seller_theme;
	}

	public function setWinType($value)
	{
		$this->win_type = $value;
		return $this;
	}

	public function setWinGoodsType($value)
	{
		$this->win_goods_type = $value;
		return $this;
	}

	public function setWinOrder($value)
	{
		$this->win_order = $value;
		return $this;
	}

	public function setWinGoods($value)
	{
		$this->win_goods = $value;
		return $this;
	}

	public function setWinName($value)
	{
		$this->win_name = $value;
		return $this;
	}

	public function setWinColor($value)
	{
		$this->win_color = $value;
		return $this;
	}

	public function setWinImg($value)
	{
		$this->win_img = $value;
		return $this;
	}

	public function setWinImgLink($value)
	{
		$this->win_img_link = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}

	public function setWinCustom($value)
	{
		$this->win_custom = $value;
		return $this;
	}

	public function setSellerTheme($value)
	{
		$this->seller_theme = $value;
		return $this;
	}
}

?>
