<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class GoodsCat extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_cat';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'cat_id');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}
}

?>
