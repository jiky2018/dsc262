<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class BargainStatistics extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'bargain_statistics';
	public $timestamps = false;
	protected $fillable = array('bs_id', 'user_id', 'subtract_price', 'add_time');
	protected $guarded = array();

	public function getBsId()
	{
		return $this->bs_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getSubtractPrice()
	{
		return $this->subtract_price;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setBsId($value)
	{
		$this->bs_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setSubtractPrice($value)
	{
		$this->subtract_price = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
