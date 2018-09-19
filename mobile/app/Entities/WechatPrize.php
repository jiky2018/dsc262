<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatPrize extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_prize';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'openid', 'prize_name', 'issue_status', 'winner', 'dateline', 'prize_type', 'activity_type', 'market_id');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getOpenid()
	{
		return $this->openid;
	}

	public function getPrizeName()
	{
		return $this->prize_name;
	}

	public function getIssueStatus()
	{
		return $this->issue_status;
	}

	public function getWinner()
	{
		return $this->winner;
	}

	public function getDateline()
	{
		return $this->dateline;
	}

	public function getPrizeType()
	{
		return $this->prize_type;
	}

	public function getActivityType()
	{
		return $this->activity_type;
	}

	public function getMarketId()
	{
		return $this->market_id;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setOpenid($value)
	{
		$this->openid = $value;
		return $this;
	}

	public function setPrizeName($value)
	{
		$this->prize_name = $value;
		return $this;
	}

	public function setIssueStatus($value)
	{
		$this->issue_status = $value;
		return $this;
	}

	public function setWinner($value)
	{
		$this->winner = $value;
		return $this;
	}

	public function setDateline($value)
	{
		$this->dateline = $value;
		return $this;
	}

	public function setPrizeType($value)
	{
		$this->prize_type = $value;
		return $this;
	}

	public function setActivityType($value)
	{
		$this->activity_type = $value;
		return $this;
	}

	public function setMarketId($value)
	{
		$this->market_id = $value;
		return $this;
	}
}

?>
