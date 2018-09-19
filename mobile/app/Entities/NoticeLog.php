<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class NoticeLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'notice_log';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'email', 'send_ok', 'send_type', 'send_time');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getSendOk()
	{
		return $this->send_ok;
	}

	public function getSendType()
	{
		return $this->send_type;
	}

	public function getSendTime()
	{
		return $this->send_time;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}

	public function setSendOk($value)
	{
		$this->send_ok = $value;
		return $this;
	}

	public function setSendType($value)
	{
		$this->send_type = $value;
		return $this;
	}

	public function setSendTime($value)
	{
		$this->send_time = $value;
		return $this;
	}
}

?>
