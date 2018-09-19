<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class CartUserInfo extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'cart_user_info';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'user_id', 'shipping_type', 'shipping_id');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getShippingType()
	{
		return $this->shipping_type;
	}

	public function getShippingId()
	{
		return $this->shipping_id;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setShippingType($value)
	{
		$this->shipping_type = $value;
		return $this;
	}

	public function setShippingId($value)
	{
		$this->shipping_id = $value;
		return $this;
	}
}

?>
