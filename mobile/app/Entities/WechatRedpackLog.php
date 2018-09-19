<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatRedpackLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_redpack_log';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'market_id', 'hb_type', 'openid', 'hassub', 'money', 'time', 'mch_billno', 'mch_id', 'wxappid', 'bill_type', 'notify_data');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getMarketId()
	{
		return $this->market_id;
	}

	public function getHbType()
	{
		return $this->hb_type;
	}

	public function getOpenid()
	{
		return $this->openid;
	}

	public function getHassub()
	{
		return $this->hassub;
	}

	public function getMoney()
	{
		return $this->money;
	}

	public function getTime()
	{
		return $this->time;
	}

	public function getMchBillno()
	{
		return $this->mch_billno;
	}

	public function getMchId()
	{
		return $this->mch_id;
	}

	public function getWxappid()
	{
		return $this->wxappid;
	}

	public function getBillType()
	{
		return $this->bill_type;
	}

	public function getNotifyData()
	{
		return $this->notify_data;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setMarketId($value)
	{
		$this->market_id = $value;
		return $this;
	}

	public function setHbType($value)
	{
		$this->hb_type = $value;
		return $this;
	}

	public function setOpenid($value)
	{
		$this->openid = $value;
		return $this;
	}

	public function setHassub($value)
	{
		$this->hassub = $value;
		return $this;
	}

	public function setMoney($value)
	{
		$this->money = $value;
		return $this;
	}

	public function setTime($value)
	{
		$this->time = $value;
		return $this;
	}

	public function setMchBillno($value)
	{
		$this->mch_billno = $value;
		return $this;
	}

	public function setMchId($value)
	{
		$this->mch_id = $value;
		return $this;
	}

	public function setWxappid($value)
	{
		$this->wxappid = $value;
		return $this;
	}

	public function setBillType($value)
	{
		$this->bill_type = $value;
		return $this;
	}

	public function setNotifyData($value)
	{
		$this->notify_data = $value;
		return $this;
	}
}

?>
