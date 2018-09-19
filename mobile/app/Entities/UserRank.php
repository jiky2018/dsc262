<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class UserRank extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'user_rank';
	protected $primaryKey = 'rank_id';
	public $timestamps = false;
	protected $fillable = array('rank_name', 'min_points', 'max_points', 'discount', 'show_price', 'special_rank');
	protected $guarded = array();

	public function getRankName()
	{
		return $this->rank_name;
	}

	public function getMinPoints()
	{
		return $this->min_points;
	}

	public function getMaxPoints()
	{
		return $this->max_points;
	}

	public function getDiscount()
	{
		return $this->discount;
	}

	public function getShowPrice()
	{
		return $this->show_price;
	}

	public function getSpecialRank()
	{
		return $this->special_rank;
	}

	public function setRankName($value)
	{
		$this->rank_name = $value;
		return $this;
	}

	public function setMinPoints($value)
	{
		$this->min_points = $value;
		return $this;
	}

	public function setMaxPoints($value)
	{
		$this->max_points = $value;
		return $this;
	}

	public function setDiscount($value)
	{
		$this->discount = $value;
		return $this;
	}

	public function setShowPrice($value)
	{
		$this->show_price = $value;
		return $this;
	}

	public function setSpecialRank($value)
	{
		$this->special_rank = $value;
		return $this;
	}
}

?>
