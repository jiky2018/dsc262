<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class SnatchLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'snatch_log';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	protected $fillable = array('snatch_id', 'user_id', 'bid_price', 'bid_time');
	protected $guarded = array();

	public function getSnatchId()
	{
		return $this->snatch_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getBidPrice()
	{
		return $this->bid_price;
	}

	public function getBidTime()
	{
		return $this->bid_time;
	}

	public function setSnatchId($value)
	{
		$this->snatch_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setBidPrice($value)
	{
		$this->bid_price = $value;
		return $this;
	}

	public function setBidTime($value)
	{
		$this->bid_time = $value;
		return $this;
	}
}

?>
