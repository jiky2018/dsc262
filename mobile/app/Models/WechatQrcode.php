<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WechatQrcode extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_qrcode';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'type', 'expire_seconds', 'scene_id', 'username', 'function', 'ticket', 'qrcode_url', 'endtime', 'scan_num', 'status', 'sort');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getExpireSeconds()
	{
		return $this->expire_seconds;
	}

	public function getSceneId()
	{
		return $this->scene_id;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getFunction()
	{
		return $this->function;
	}

	public function getTicket()
	{
		return $this->ticket;
	}

	public function getQrcodeUrl()
	{
		return $this->qrcode_url;
	}

	public function getEndtime()
	{
		return $this->endtime;
	}

	public function getScanNum()
	{
		return $this->scan_num;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getSort()
	{
		return $this->sort;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setExpireSeconds($value)
	{
		$this->expire_seconds = $value;
		return $this;
	}

	public function setSceneId($value)
	{
		$this->scene_id = $value;
		return $this;
	}

	public function setUsername($value)
	{
		$this->username = $value;
		return $this;
	}

	public function setFunction($value)
	{
		$this->function = $value;
		return $this;
	}

	public function setTicket($value)
	{
		$this->ticket = $value;
		return $this;
	}

	public function setQrcodeUrl($value)
	{
		$this->qrcode_url = $value;
		return $this;
	}

	public function setEndtime($value)
	{
		$this->endtime = $value;
		return $this;
	}

	public function setScanNum($value)
	{
		$this->scan_num = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setSort($value)
	{
		$this->sort = $value;
		return $this;
	}
}

?>
