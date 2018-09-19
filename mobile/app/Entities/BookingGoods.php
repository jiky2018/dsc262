<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class BookingGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'booking_goods';
	protected $primaryKey = 'rec_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'email', 'link_man', 'tel', 'goods_id', 'goods_desc', 'goods_number', 'booking_time', 'is_dispose', 'dispose_user', 'dispose_time', 'dispose_note');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getLinkMan()
	{
		return $this->link_man;
	}

	public function getTel()
	{
		return $this->tel;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getGoodsDesc()
	{
		return $this->goods_desc;
	}

	public function getGoodsNumber()
	{
		return $this->goods_number;
	}

	public function getBookingTime()
	{
		return $this->booking_time;
	}

	public function getIsDispose()
	{
		return $this->is_dispose;
	}

	public function getDisposeUser()
	{
		return $this->dispose_user;
	}

	public function getDisposeTime()
	{
		return $this->dispose_time;
	}

	public function getDisposeNote()
	{
		return $this->dispose_note;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}

	public function setLinkMan($value)
	{
		$this->link_man = $value;
		return $this;
	}

	public function setTel($value)
	{
		$this->tel = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setGoodsDesc($value)
	{
		$this->goods_desc = $value;
		return $this;
	}

	public function setGoodsNumber($value)
	{
		$this->goods_number = $value;
		return $this;
	}

	public function setBookingTime($value)
	{
		$this->booking_time = $value;
		return $this;
	}

	public function setIsDispose($value)
	{
		$this->is_dispose = $value;
		return $this;
	}

	public function setDisposeUser($value)
	{
		$this->dispose_user = $value;
		return $this;
	}

	public function setDisposeTime($value)
	{
		$this->dispose_time = $value;
		return $this;
	}

	public function setDisposeNote($value)
	{
		$this->dispose_note = $value;
		return $this;
	}
}

?>
