<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class OrderPrintSize extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'order_print_size';
	public $timestamps = false;
	protected $fillable = array('type', 'specification', 'width', 'height', 'size', 'description');
	protected $guarded = array();

	public function getType()
	{
		return $this->type;
	}

	public function getSpecification()
	{
		return $this->specification;
	}

	public function getWidth()
	{
		return $this->width;
	}

	public function getHeight()
	{
		return $this->height;
	}

	public function getSize()
	{
		return $this->size;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setSpecification($value)
	{
		$this->specification = $value;
		return $this;
	}

	public function setWidth($value)
	{
		$this->width = $value;
		return $this;
	}

	public function setHeight($value)
	{
		$this->height = $value;
		return $this;
	}

	public function setSize($value)
	{
		$this->size = $value;
		return $this;
	}

	public function setDescription($value)
	{
		$this->description = $value;
		return $this;
	}
}

?>
