<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class GoodsTransport extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_transport';
	protected $primaryKey = 'tid';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'type', 'freight_type', 'title', 'shipping_title', 'free_money', 'update_time');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getFreightType()
	{
		return $this->freight_type;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getShippingTitle()
	{
		return $this->shipping_title;
	}

	public function getFreeMoney()
	{
		return $this->free_money;
	}

	public function getUpdateTime()
	{
		return $this->update_time;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setFreightType($value)
	{
		$this->freight_type = $value;
		return $this;
	}

	public function setTitle($value)
	{
		$this->title = $value;
		return $this;
	}

	public function setShippingTitle($value)
	{
		$this->shipping_title = $value;
		return $this;
	}

	public function setFreeMoney($value)
	{
		$this->free_money = $value;
		return $this;
	}

	public function setUpdateTime($value)
	{
		$this->update_time = $value;
		return $this;
	}
}

?>
