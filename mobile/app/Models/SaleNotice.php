<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class SaleNotice extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'sale_notice';
	public $timestamps = false;
	protected $fillable = array('user_id', 'goods_id', 'cellphone', 'email', 'hopeDiscount', 'status', 'send_type', 'add_time', 'mark');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getCellphone()
	{
		return $this->cellphone;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getHopeDiscount()
	{
		return $this->hopeDiscount;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getSendType()
	{
		return $this->send_type;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getMark()
	{
		return $this->mark;
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

	public function setCellphone($value)
	{
		$this->cellphone = $value;
		return $this;
	}

	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}

	public function setHopeDiscount($value)
	{
		$this->hopeDiscount = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setSendType($value)
	{
		$this->send_type = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setMark($value)
	{
		$this->mark = $value;
		return $this;
	}
}

?>
