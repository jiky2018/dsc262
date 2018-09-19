<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class AuctionLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'auction_log';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	protected $fillable = array('act_id', 'bid_user', 'bid_price', 'bid_time');
	protected $guarded = array();

	public function getActId()
	{
		return $this->act_id;
	}

	public function getBidUser()
	{
		return $this->bid_user;
	}

	public function getBidPrice()
	{
		return $this->bid_price;
	}

	public function getBidTime()
	{
		return $this->bid_time;
	}

	public function setActId($value)
	{
		$this->act_id = $value;
		return $this;
	}

	public function setBidUser($value)
	{
		$this->bid_user = $value;
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
