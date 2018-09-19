<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class EntryCriteria extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'entry_criteria';
	public $timestamps = false;
	protected $fillable = array('parent_id', 'criteria_name', 'charge', 'standard_name', 'type', 'is_mandatory', 'option_value', 'data_type', 'is_cumulative');
	protected $guarded = array();

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getCriteriaName()
	{
		return $this->criteria_name;
	}

	public function getCharge()
	{
		return $this->charge;
	}

	public function getStandardName()
	{
		return $this->standard_name;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getIsMandatory()
	{
		return $this->is_mandatory;
	}

	public function getOptionValue()
	{
		return $this->option_value;
	}

	public function getDataType()
	{
		return $this->data_type;
	}

	public function getIsCumulative()
	{
		return $this->is_cumulative;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setCriteriaName($value)
	{
		$this->criteria_name = $value;
		return $this;
	}

	public function setCharge($value)
	{
		$this->charge = $value;
		return $this;
	}

	public function setStandardName($value)
	{
		$this->standard_name = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setIsMandatory($value)
	{
		$this->is_mandatory = $value;
		return $this;
	}

	public function setOptionValue($value)
	{
		$this->option_value = $value;
		return $this;
	}

	public function setDataType($value)
	{
		$this->data_type = $value;
		return $this;
	}

	public function setIsCumulative($value)
	{
		$this->is_cumulative = $value;
		return $this;
	}
}

?>
