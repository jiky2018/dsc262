<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class GoodsTransportExpress extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_transport_express';
	public $timestamps = false;
	protected $fillable = array('tid', 'ru_id', 'admin_id', 'shipping_id', 'shipping_fee');
	protected $guarded = array();

	public function getTid()
	{
		return $this->tid;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function getShippingId()
	{
		return $this->shipping_id;
	}

	public function getShippingFee()
	{
		return $this->shipping_fee;
	}

	public function setTid($value)
	{
		$this->tid = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}

	public function setShippingId($value)
	{
		$this->shipping_id = $value;
		return $this;
	}

	public function setShippingFee($value)
	{
		$this->shipping_fee = $value;
		return $this;
	}
}

?>
