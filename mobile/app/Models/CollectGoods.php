<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class CollectGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'collect_goods';
	protected $primaryKey = 'rec_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'goods_id', 'add_time', 'is_attention');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getIsAttention()
	{
		return $this->is_attention;
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

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setIsAttention($value)
	{
		$this->is_attention = $value;
		return $this;
	}
}

?>
