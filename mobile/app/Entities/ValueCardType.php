<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ValueCardType extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'value_card_type';
	public $timestamps = false;
	protected $fillable = array('name', 'vc_desc', 'vc_value', 'vc_prefix', 'vc_dis', 'vc_limit', 'use_condition', 'use_merchants', 'spec_goods', 'spec_cat', 'vc_indate', 'is_rec', 'add_time');
	protected $guarded = array();

	public function getName()
	{
		return $this->name;
	}

	public function getVcDesc()
	{
		return $this->vc_desc;
	}

	public function getVcValue()
	{
		return $this->vc_value;
	}

	public function getVcPrefix()
	{
		return $this->vc_prefix;
	}

	public function getVcDis()
	{
		return $this->vc_dis;
	}

	public function getVcLimit()
	{
		return $this->vc_limit;
	}

	public function getUseCondition()
	{
		return $this->use_condition;
	}

	public function getUseMerchants()
	{
		return $this->use_merchants;
	}

	public function getSpecGoods()
	{
		return $this->spec_goods;
	}

	public function getSpecCat()
	{
		return $this->spec_cat;
	}

	public function getVcIndate()
	{
		return $this->vc_indate;
	}

	public function getIsRec()
	{
		return $this->is_rec;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}

	public function setVcDesc($value)
	{
		$this->vc_desc = $value;
		return $this;
	}

	public function setVcValue($value)
	{
		$this->vc_value = $value;
		return $this;
	}

	public function setVcPrefix($value)
	{
		$this->vc_prefix = $value;
		return $this;
	}

	public function setVcDis($value)
	{
		$this->vc_dis = $value;
		return $this;
	}

	public function setVcLimit($value)
	{
		$this->vc_limit = $value;
		return $this;
	}

	public function setUseCondition($value)
	{
		$this->use_condition = $value;
		return $this;
	}

	public function setUseMerchants($value)
	{
		$this->use_merchants = $value;
		return $this;
	}

	public function setSpecGoods($value)
	{
		$this->spec_goods = $value;
		return $this;
	}

	public function setSpecCat($value)
	{
		$this->spec_cat = $value;
		return $this;
	}

	public function setVcIndate($value)
	{
		$this->vc_indate = $value;
		return $this;
	}

	public function setIsRec($value)
	{
		$this->is_rec = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
