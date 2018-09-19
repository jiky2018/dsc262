<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WechatMassHistory extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_mass_history';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'media_id', 'type', 'status', 'send_time', 'msg_id', 'totalcount', 'filtercount', 'sentcount', 'errorcount');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getMediaId()
	{
		return $this->media_id;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getSendTime()
	{
		return $this->send_time;
	}

	public function getMsgId()
	{
		return $this->msg_id;
	}

	public function getTotalcount()
	{
		return $this->totalcount;
	}

	public function getFiltercount()
	{
		return $this->filtercount;
	}

	public function getSentcount()
	{
		return $this->sentcount;
	}

	public function getErrorcount()
	{
		return $this->errorcount;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setMediaId($value)
	{
		$this->media_id = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setSendTime($value)
	{
		$this->send_time = $value;
		return $this;
	}

	public function setMsgId($value)
	{
		$this->msg_id = $value;
		return $this;
	}

	public function setTotalcount($value)
	{
		$this->totalcount = $value;
		return $this;
	}

	public function setFiltercount($value)
	{
		$this->filtercount = $value;
		return $this;
	}

	public function setSentcount($value)
	{
		$this->sentcount = $value;
		return $this;
	}

	public function setErrorcount($value)
	{
		$this->errorcount = $value;
		return $this;
	}
}

?>
