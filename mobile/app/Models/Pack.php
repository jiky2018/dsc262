<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Pack extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'pack';
	protected $primaryKey = 'pack_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'pack_name', 'pack_img', 'pack_fee', 'free_money', 'pack_desc');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getPackName()
	{
		return $this->pack_name;
	}

	public function getPackImg()
	{
		return $this->pack_img;
	}

	public function getPackFee()
	{
		return $this->pack_fee;
	}

	public function getFreeMoney()
	{
		return $this->free_money;
	}

	public function getPackDesc()
	{
		return $this->pack_desc;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setPackName($value)
	{
		$this->pack_name = $value;
		return $this;
	}

	public function setPackImg($value)
	{
		$this->pack_img = $value;
		return $this;
	}

	public function setPackFee($value)
	{
		$this->pack_fee = $value;
		return $this;
	}

	public function setFreeMoney($value)
	{
		$this->free_money = $value;
		return $this;
	}

	public function setPackDesc($value)
	{
		$this->pack_desc = $value;
		return $this;
	}
}

?>
