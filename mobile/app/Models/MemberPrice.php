<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class MemberPrice extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'member_price';
	protected $primaryKey = 'price_id';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'user_rank', 'user_price');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getUserRank()
	{
		return $this->user_rank;
	}

	public function getUserPrice()
	{
		return $this->user_price;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setUserRank($value)
	{
		$this->user_rank = $value;
		return $this;
	}

	public function setUserPrice($value)
	{
		$this->user_price = $value;
		return $this;
	}
}

?>
