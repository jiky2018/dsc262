<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class SellerGrade extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_grade';
	public $timestamps = false;
	protected $fillable = array('grade_name', 'goods_sun', 'seller_temp', 'favorable_rate', 'give_integral', 'rank_integral', 'pay_integral', 'white_bar', 'grade_introduce', 'entry_criteria', 'grade_img', 'is_open', 'is_default');
	protected $guarded = array();

	public function getGradeName()
	{
		return $this->grade_name;
	}

	public function getGoodsSun()
	{
		return $this->goods_sun;
	}

	public function getSellerTemp()
	{
		return $this->seller_temp;
	}

	public function getFavorableRate()
	{
		return $this->favorable_rate;
	}

	public function getGiveIntegral()
	{
		return $this->give_integral;
	}

	public function getRankIntegral()
	{
		return $this->rank_integral;
	}

	public function getPayIntegral()
	{
		return $this->pay_integral;
	}

	public function getWhiteBar()
	{
		return $this->white_bar;
	}

	public function getGradeIntroduce()
	{
		return $this->grade_introduce;
	}

	public function getEntryCriteria()
	{
		return $this->entry_criteria;
	}

	public function getGradeImg()
	{
		return $this->grade_img;
	}

	public function getIsOpen()
	{
		return $this->is_open;
	}

	public function getIsDefault()
	{
		return $this->is_default;
	}

	public function setGradeName($value)
	{
		$this->grade_name = $value;
		return $this;
	}

	public function setGoodsSun($value)
	{
		$this->goods_sun = $value;
		return $this;
	}

	public function setSellerTemp($value)
	{
		$this->seller_temp = $value;
		return $this;
	}

	public function setFavorableRate($value)
	{
		$this->favorable_rate = $value;
		return $this;
	}

	public function setGiveIntegral($value)
	{
		$this->give_integral = $value;
		return $this;
	}

	public function setRankIntegral($value)
	{
		$this->rank_integral = $value;
		return $this;
	}

	public function setPayIntegral($value)
	{
		$this->pay_integral = $value;
		return $this;
	}

	public function setWhiteBar($value)
	{
		$this->white_bar = $value;
		return $this;
	}

	public function setGradeIntroduce($value)
	{
		$this->grade_introduce = $value;
		return $this;
	}

	public function setEntryCriteria($value)
	{
		$this->entry_criteria = $value;
		return $this;
	}

	public function setGradeImg($value)
	{
		$this->grade_img = $value;
		return $this;
	}

	public function setIsOpen($value)
	{
		$this->is_open = $value;
		return $this;
	}

	public function setIsDefault($value)
	{
		$this->is_default = $value;
		return $this;
	}
}

?>
