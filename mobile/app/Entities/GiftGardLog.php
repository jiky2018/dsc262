<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class GiftGardLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'gift_gard_log';
	public $timestamps = false;
	protected $fillable = array('admin_id', 'gift_gard_id', 'delivery_status', 'addtime', 'handle_type');
	protected $guarded = array();

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function getGiftGardId()
	{
		return $this->gift_gard_id;
	}

	public function getDeliveryStatus()
	{
		return $this->delivery_status;
	}

	public function getAddtime()
	{
		return $this->addtime;
	}

	public function getHandleType()
	{
		return $this->handle_type;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}

	public function setGiftGardId($value)
	{
		$this->gift_gard_id = $value;
		return $this;
	}

	public function setDeliveryStatus($value)
	{
		$this->delivery_status = $value;
		return $this;
	}

	public function setAddtime($value)
	{
		$this->addtime = $value;
		return $this;
	}

	public function setHandleType($value)
	{
		$this->handle_type = $value;
		return $this;
	}
}

?>
