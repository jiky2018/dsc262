<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class OrderPrintSetting extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'order_print_setting';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'specification', 'printer', 'width', 'is_default', 'sort_order');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getSpecification()
	{
		return $this->specification;
	}

	public function getPrinter()
	{
		return $this->printer;
	}

	public function getWidth()
	{
		return $this->width;
	}

	public function getIsDefault()
	{
		return $this->is_default;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setSpecification($value)
	{
		$this->specification = $value;
		return $this;
	}

	public function setPrinter($value)
	{
		$this->printer = $value;
		return $this;
	}

	public function setWidth($value)
	{
		$this->width = $value;
		return $this;
	}

	public function setIsDefault($value)
	{
		$this->is_default = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}
}

?>
