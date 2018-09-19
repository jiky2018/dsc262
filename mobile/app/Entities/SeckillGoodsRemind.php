<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class SeckillGoodsRemind extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seckill_goods_remind';
	protected $primaryKey = 'r_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'sec_goods_id', 'add_time');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getSecGoodsId()
	{
		return $this->sec_goods_id;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setSecGoodsId($value)
	{
		$this->sec_goods_id = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
