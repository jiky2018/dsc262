<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsStepsFieldsCentent extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_steps_fields_centent';
	public $timestamps = false;
	protected $fillable = array('tid', 'textFields', 'fieldsDateType', 'fieldsLength', 'fieldsNotnull', 'fieldsFormName', 'fieldsCoding', 'fieldsForm', 'fields_sort', 'will_choose');
	protected $guarded = array();

	public function getTid()
	{
		return $this->tid;
	}

	public function getTextFields()
	{
		return $this->textFields;
	}

	public function getFieldsDateType()
	{
		return $this->fieldsDateType;
	}

	public function getFieldsLength()
	{
		return $this->fieldsLength;
	}

	public function getFieldsNotnull()
	{
		return $this->fieldsNotnull;
	}

	public function getFieldsFormName()
	{
		return $this->fieldsFormName;
	}

	public function getFieldsCoding()
	{
		return $this->fieldsCoding;
	}

	public function getFieldsForm()
	{
		return $this->fieldsForm;
	}

	public function getFieldsSort()
	{
		return $this->fields_sort;
	}

	public function getWillChoose()
	{
		return $this->will_choose;
	}

	public function setTid($value)
	{
		$this->tid = $value;
		return $this;
	}

	public function setTextFields($value)
	{
		$this->textFields = $value;
		return $this;
	}

	public function setFieldsDateType($value)
	{
		$this->fieldsDateType = $value;
		return $this;
	}

	public function setFieldsLength($value)
	{
		$this->fieldsLength = $value;
		return $this;
	}

	public function setFieldsNotnull($value)
	{
		$this->fieldsNotnull = $value;
		return $this;
	}

	public function setFieldsFormName($value)
	{
		$this->fieldsFormName = $value;
		return $this;
	}

	public function setFieldsCoding($value)
	{
		$this->fieldsCoding = $value;
		return $this;
	}

	public function setFieldsForm($value)
	{
		$this->fieldsForm = $value;
		return $this;
	}

	public function setFieldsSort($value)
	{
		$this->fields_sort = $value;
		return $this;
	}

	public function setWillChoose($value)
	{
		$this->will_choose = $value;
		return $this;
	}
}

?>
