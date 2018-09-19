<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class DrpConfig extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'drp_config';
	public $timestamps = false;
	protected $fillable = array('code', 'type', 'store_range', 'value', 'name', 'warning', 'sort_order');
	protected $guarded = array();

	public function getCode()
	{
		return $this->code;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getStoreRange()
	{
		return $this->store_range;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getWarning()
	{
		return $this->warning;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function setCode($value)
	{
		$this->code = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setStoreRange($value)
	{
		$this->store_range = $value;
		return $this;
	}

	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}

	public function setWarning($value)
	{
		$this->warning = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}
}

?>
