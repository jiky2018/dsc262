<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class TeamGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'team_goods';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'team_price', 'team_num', 'validity_time', 'limit_num', 'astrict_num', 'tc_id', 'is_audit', 'is_team', 'sort_order', 'team_desc', 'isnot_aduit_reason');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getTeamPrice()
	{
		return $this->team_price;
	}

	public function getTeamNum()
	{
		return $this->team_num;
	}

	public function getValidityTime()
	{
		return $this->validity_time;
	}

	public function getLimitNum()
	{
		return $this->limit_num;
	}

	public function getAstrictNum()
	{
		return $this->astrict_num;
	}

	public function getTcId()
	{
		return $this->tc_id;
	}

	public function getIsAudit()
	{
		return $this->is_audit;
	}

	public function getIsTeam()
	{
		return $this->is_team;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getTeamDesc()
	{
		return $this->team_desc;
	}

	public function getIsnotAduitReason()
	{
		return $this->isnot_aduit_reason;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setTeamPrice($value)
	{
		$this->team_price = $value;
		return $this;
	}

	public function setTeamNum($value)
	{
		$this->team_num = $value;
		return $this;
	}

	public function setValidityTime($value)
	{
		$this->validity_time = $value;
		return $this;
	}

	public function setLimitNum($value)
	{
		$this->limit_num = $value;
		return $this;
	}

	public function setAstrictNum($value)
	{
		$this->astrict_num = $value;
		return $this;
	}

	public function setTcId($value)
	{
		$this->tc_id = $value;
		return $this;
	}

	public function setIsAudit($value)
	{
		$this->is_audit = $value;
		return $this;
	}

	public function setIsTeam($value)
	{
		$this->is_team = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setTeamDesc($value)
	{
		$this->team_desc = $value;
		return $this;
	}

	public function setIsnotAduitReason($value)
	{
		$this->isnot_aduit_reason = $value;
		return $this;
	}
}

?>
