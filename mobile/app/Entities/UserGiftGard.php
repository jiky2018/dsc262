<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class UserGiftGard extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'user_gift_gard';
	protected $primaryKey = 'gift_gard_id';
	public $timestamps = false;
	protected $fillable = array('gift_sn', 'gift_password', 'user_id', 'goods_id', 'user_time', 'express_no', 'gift_id', 'address', 'consignee_name', 'mobile', 'status', 'config_goods_id', 'is_delete', 'shipping_time');
	protected $guarded = array();

	public function getGiftSn()
	{
		return $this->gift_sn;
	}

	public function getGiftPassword()
	{
		return $this->gift_password;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getUserTime()
	{
		return $this->user_time;
	}

	public function getExpressNo()
	{
		return $this->express_no;
	}

	public function getGiftId()
	{
		return $this->gift_id;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function getConsigneeName()
	{
		return $this->consignee_name;
	}

	public function getMobile()
	{
		return $this->mobile;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getConfigGoodsId()
	{
		return $this->config_goods_id;
	}

	public function getIsDelete()
	{
		return $this->is_delete;
	}

	public function getShippingTime()
	{
		return $this->shipping_time;
	}

	public function setGiftSn($value)
	{
		$this->gift_sn = $value;
		return $this;
	}

	public function setGiftPassword($value)
	{
		$this->gift_password = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setUserTime($value)
	{
		$this->user_time = $value;
		return $this;
	}

	public function setExpressNo($value)
	{
		$this->express_no = $value;
		return $this;
	}

	public function setGiftId($value)
	{
		$this->gift_id = $value;
		return $this;
	}

	public function setAddress($value)
	{
		$this->address = $value;
		return $this;
	}

	public function setConsigneeName($value)
	{
		$this->consignee_name = $value;
		return $this;
	}

	public function setMobile($value)
	{
		$this->mobile = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setConfigGoodsId($value)
	{
		$this->config_goods_id = $value;
		return $this;
	}

	public function setIsDelete($value)
	{
		$this->is_delete = $value;
		return $this;
	}

	public function setShippingTime($value)
	{
		$this->shipping_time = $value;
		return $this;
	}
}

?>
