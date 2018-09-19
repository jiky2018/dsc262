<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class CommentSeller extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'comment_seller';
	protected $primaryKey = 'sid';
	public $timestamps = false;
	protected $fillable = array('user_id', 'ru_id', 'order_id', 'desc_rank', 'service_rank', 'delivery_rank', 'sender_rank', 'add_time');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getDescRank()
	{
		return $this->desc_rank;
	}

	public function getServiceRank()
	{
		return $this->service_rank;
	}

	public function getDeliveryRank()
	{
		return $this->delivery_rank;
	}

	public function getSenderRank()
	{
		return $this->sender_rank;
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

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setDescRank($value)
	{
		$this->desc_rank = $value;
		return $this;
	}

	public function setServiceRank($value)
	{
		$this->service_rank = $value;
		return $this;
	}

	public function setDeliveryRank($value)
	{
		$this->delivery_rank = $value;
		return $this;
	}

	public function setSenderRank($value)
	{
		$this->sender_rank = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
