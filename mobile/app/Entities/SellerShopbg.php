<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class SellerShopbg extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_shopbg';
	public $timestamps = false;
	protected $fillable = array('bgimg', 'bgrepeat', 'bgcolor', 'show_img', 'is_custom', 'ru_id', 'seller_theme');
	protected $guarded = array();

	public function getBgimg()
	{
		return $this->bgimg;
	}

	public function getBgrepeat()
	{
		return $this->bgrepeat;
	}

	public function getBgcolor()
	{
		return $this->bgcolor;
	}

	public function getShowImg()
	{
		return $this->show_img;
	}

	public function getIsCustom()
	{
		return $this->is_custom;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getSellerTheme()
	{
		return $this->seller_theme;
	}

	public function setBgimg($value)
	{
		$this->bgimg = $value;
		return $this;
	}

	public function setBgrepeat($value)
	{
		$this->bgrepeat = $value;
		return $this;
	}

	public function setBgcolor($value)
	{
		$this->bgcolor = $value;
		return $this;
	}

	public function setShowImg($value)
	{
		$this->show_img = $value;
		return $this;
	}

	public function setIsCustom($value)
	{
		$this->is_custom = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setSellerTheme($value)
	{
		$this->seller_theme = $value;
		return $this;
	}
}

?>
