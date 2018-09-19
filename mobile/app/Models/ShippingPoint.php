<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ShippingPoint extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'shipping_point';
	public $timestamps = false;
	protected $fillable = array('shipping_area_id', 'name', 'user_name', 'mobile', 'address', 'img_url', 'anchor', 'line');
	protected $guarded = array();

	public function getShippingAreaId()
	{
		return $this->shipping_area_id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getMobile()
	{
		return $this->mobile;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function getImgUrl()
	{
		return $this->img_url;
	}

	public function getAnchor()
	{
		return $this->anchor;
	}

	public function getLine()
	{
		return $this->line;
	}

	public function setShippingAreaId($value)
	{
		$this->shipping_area_id = $value;
		return $this;
	}

	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}

	public function setUserName($value)
	{
		$this->user_name = $value;
		return $this;
	}

	public function setMobile($value)
	{
		$this->mobile = $value;
		return $this;
	}

	public function setAddress($value)
	{
		$this->address = $value;
		return $this;
	}

	public function setImgUrl($value)
	{
		$this->img_url = $value;
		return $this;
	}

	public function setAnchor($value)
	{
		$this->anchor = $value;
		return $this;
	}

	public function setLine($value)
	{
		$this->line = $value;
		return $this;
	}
}

?>
