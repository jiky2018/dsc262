<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsStepsTitle extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_steps_title';
	protected $primaryKey = 'tid';
	public $timestamps = false;
	protected $fillable = array('fields_steps', 'fields_titles', 'steps_style', 'titles_annotation', 'fields_special', 'special_type');
	protected $guarded = array();

	public function getFieldsSteps()
	{
		return $this->fields_steps;
	}

	public function getFieldsTitles()
	{
		return $this->fields_titles;
	}

	public function getStepsStyle()
	{
		return $this->steps_style;
	}

	public function getTitlesAnnotation()
	{
		return $this->titles_annotation;
	}

	public function getFieldsSpecial()
	{
		return $this->fields_special;
	}

	public function getSpecialType()
	{
		return $this->special_type;
	}

	public function setFieldsSteps($value)
	{
		$this->fields_steps = $value;
		return $this;
	}

	public function setFieldsTitles($value)
	{
		$this->fields_titles = $value;
		return $this;
	}

	public function setStepsStyle($value)
	{
		$this->steps_style = $value;
		return $this;
	}

	public function setTitlesAnnotation($value)
	{
		$this->titles_annotation = $value;
		return $this;
	}

	public function setFieldsSpecial($value)
	{
		$this->fields_special = $value;
		return $this;
	}

	public function setSpecialType($value)
	{
		$this->special_type = $value;
		return $this;
	}
}

?>
