<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class VirtualCard extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'virtual_card';
	protected $primaryKey = 'card_id';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'card_sn', 'card_password', 'add_date', 'end_date', 'is_saled', 'order_sn', 'crc32');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getCardSn()
	{
		return $this->card_sn;
	}

	public function getCardPassword()
	{
		return $this->card_password;
	}

	public function getAddDate()
	{
		return $this->add_date;
	}

	public function getEndDate()
	{
		return $this->end_date;
	}

	public function getIsSaled()
	{
		return $this->is_saled;
	}

	public function getOrderSn()
	{
		return $this->order_sn;
	}

	public function getCrc32()
	{
		return $this->crc32;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setCardSn($value)
	{
		$this->card_sn = $value;
		return $this;
	}

	public function setCardPassword($value)
	{
		$this->card_password = $value;
		return $this;
	}

	public function setAddDate($value)
	{
		$this->add_date = $value;
		return $this;
	}

	public function setEndDate($value)
	{
		$this->end_date = $value;
		return $this;
	}

	public function setIsSaled($value)
	{
		$this->is_saled = $value;
		return $this;
	}

	public function setOrderSn($value)
	{
		$this->order_sn = $value;
		return $this;
	}

	public function setCrc32($value)
	{
		$this->crc32 = $value;
		return $this;
	}
}

?>
