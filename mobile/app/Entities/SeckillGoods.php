<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class SeckillGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seckill_goods';
	public $timestamps = false;
	protected $fillable = array('sec_id', 'tb_id', 'goods_id', 'sec_price', 'sec_num', 'sec_limit');
	protected $guarded = array();

	public function getSecId()
	{
		return $this->sec_id;
	}

	public function getTbId()
	{
		return $this->tb_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getSecPrice()
	{
		return $this->sec_price;
	}

	public function getSecNum()
	{
		return $this->sec_num;
	}

	public function getSecLimit()
	{
		return $this->sec_limit;
	}

	public function setSecId($value)
	{
		$this->sec_id = $value;
		return $this;
	}

	public function setTbId($value)
	{
		$this->tb_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setSecPrice($value)
	{
		$this->sec_price = $value;
		return $this;
	}

	public function setSecNum($value)
	{
		$this->sec_num = $value;
		return $this;
	}

	public function setSecLimit($value)
	{
		$this->sec_limit = $value;
		return $this;
	}
}

?>
