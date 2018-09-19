<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class DrpShop extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'drp_shop';
	public $timestamps = false;
	protected $fillable = array('user_id', 'shop_name', 'real_name', 'mobile', 'qq', 'shop_img', 'shop_portrait', 'cat_id', 'create_time', 'isbuy', 'audit', 'status', 'shop_money', 'shop_points', 'type', 'credit_id');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getShopName()
	{
		return $this->shop_name;
	}

	public function getRealName()
	{
		return $this->real_name;
	}

	public function getMobile()
	{
		return $this->mobile;
	}

	public function getQq()
	{
		return $this->qq;
	}

	public function getShopImg()
	{
		return $this->shop_img;
	}

	public function getShopPortrait()
	{
		return $this->shop_portrait;
	}

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getCreateTime()
	{
		return $this->create_time;
	}

	public function getIsbuy()
	{
		return $this->isbuy;
	}

	public function getAudit()
	{
		return $this->audit;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getShopMoney()
	{
		return $this->shop_money;
	}

	public function getShopPoints()
	{
		return $this->shop_points;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getCreditId()
	{
		return $this->credit_id;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setShopName($value)
	{
		$this->shop_name = $value;
		return $this;
	}

	public function setRealName($value)
	{
		$this->real_name = $value;
		return $this;
	}

	public function setMobile($value)
	{
		$this->mobile = $value;
		return $this;
	}

	public function setQq($value)
	{
		$this->qq = $value;
		return $this;
	}

	public function setShopImg($value)
	{
		$this->shop_img = $value;
		return $this;
	}

	public function setShopPortrait($value)
	{
		$this->shop_portrait = $value;
		return $this;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setCreateTime($value)
	{
		$this->create_time = $value;
		return $this;
	}

	public function setIsbuy($value)
	{
		$this->isbuy = $value;
		return $this;
	}

	public function setAudit($value)
	{
		$this->audit = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setShopMoney($value)
	{
		$this->shop_money = $value;
		return $this;
	}

	public function setShopPoints($value)
	{
		$this->shop_points = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setCreditId($value)
	{
		$this->credit_id = $value;
		return $this;
	}
}

?>
