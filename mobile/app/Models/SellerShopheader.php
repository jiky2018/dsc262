<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class SellerShopheader extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_shopheader';
	public $timestamps = false;
	protected $fillable = array('content', 'headtype', 'headbg_img', 'shop_color', 'seller_theme', 'ru_id');
	protected $guarded = array();

	public function getContent()
	{
		return $this->content;
	}

	public function getHeadtype()
	{
		return $this->headtype;
	}

	public function getHeadbgImg()
	{
		return $this->headbg_img;
	}

	public function getShopColor()
	{
		return $this->shop_color;
	}

	public function getSellerTheme()
	{
		return $this->seller_theme;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setHeadtype($value)
	{
		$this->headtype = $value;
		return $this;
	}

	public function setHeadbgImg($value)
	{
		$this->headbg_img = $value;
		return $this;
	}

	public function setShopColor($value)
	{
		$this->shop_color = $value;
		return $this;
	}

	public function setSellerTheme($value)
	{
		$this->seller_theme = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}
}

?>
