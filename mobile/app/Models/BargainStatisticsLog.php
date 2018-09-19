<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class BargainStatisticsLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'bargain_statistics_log';
	public $timestamps = false;
	protected $fillable = array('bargain_id', 'goods_attr_id', 'user_id', 'final_price', 'add_time', 'count_num', 'status');
	protected $guarded = array();

	public function getBargainId()
	{
		return $this->bargain_id;
	}

	public function getGoodsAttrId()
	{
		return $this->goods_attr_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getFinalPrice()
	{
		return $this->final_price;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getCountNum()
	{
		return $this->count_num;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setBargainId($value)
	{
		$this->bargain_id = $value;
		return $this;
	}

	public function setGoodsAttrId($value)
	{
		$this->goods_attr_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setFinalPrice($value)
	{
		$this->final_price = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setCountNum($value)
	{
		$this->count_num = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}
}

?>
