<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class LinkAreaGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'link_area_goods';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'region_id', 'ru_id');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getRegionId()
	{
		return $this->region_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setRegionId($value)
	{
		$this->region_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}
}

?>
