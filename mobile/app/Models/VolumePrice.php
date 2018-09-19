<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class VolumePrice extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'volume_price';
	public $timestamps = false;
	protected $fillable = array('price_type', 'goods_id', 'volume_number', 'volume_price');
	protected $guarded = array();

	public function getPriceType()
	{
		return $this->price_type;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getVolumeNumber()
	{
		return $this->volume_number;
	}

	public function getVolumePrice()
	{
		return $this->volume_price;
	}

	public function setPriceType($value)
	{
		$this->price_type = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setVolumeNumber($value)
	{
		$this->volume_number = $value;
		return $this;
	}

	public function setVolumePrice($value)
	{
		$this->volume_price = $value;
		return $this;
	}
}

?>
