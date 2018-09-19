<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Attribute extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'attribute';
	protected $primaryKey = 'attr_id';
	public $timestamps = false;
	protected $fillable = array('cat_id', 'attr_name', 'attr_cat_type', 'attr_input_type', 'attr_type', 'attr_values', 'color_values', 'attr_index', 'sort_order', 'is_linked', 'attr_group', 'attr_input_category', 'cloud_attr_id');
	protected $guarded = array();

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getAttrName()
	{
		return $this->attr_name;
	}

	public function getAttrCatType()
	{
		return $this->attr_cat_type;
	}

	public function getAttrInputType()
	{
		return $this->attr_input_type;
	}

	public function getAttrType()
	{
		return $this->attr_type;
	}

	public function getAttrValues()
	{
		return $this->attr_values;
	}

	public function getColorValues()
	{
		return $this->color_values;
	}

	public function getAttrIndex()
	{
		return $this->attr_index;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getIsLinked()
	{
		return $this->is_linked;
	}

	public function getAttrGroup()
	{
		return $this->attr_group;
	}

	public function getAttrInputCategory()
	{
		return $this->attr_input_category;
	}

	public function getCloudAttrId()
	{
		return $this->cloud_attr_id;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setAttrName($value)
	{
		$this->attr_name = $value;
		return $this;
	}

	public function setAttrCatType($value)
	{
		$this->attr_cat_type = $value;
		return $this;
	}

	public function setAttrInputType($value)
	{
		$this->attr_input_type = $value;
		return $this;
	}

	public function setAttrType($value)
	{
		$this->attr_type = $value;
		return $this;
	}

	public function setAttrValues($value)
	{
		$this->attr_values = $value;
		return $this;
	}

	public function setColorValues($value)
	{
		$this->color_values = $value;
		return $this;
	}

	public function setAttrIndex($value)
	{
		$this->attr_index = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setIsLinked($value)
	{
		$this->is_linked = $value;
		return $this;
	}

	public function setAttrGroup($value)
	{
		$this->attr_group = $value;
		return $this;
	}

	public function setAttrInputCategory($value)
	{
		$this->attr_input_category = $value;
		return $this;
	}

	public function setCloudAttrId($value)
	{
		$this->cloud_attr_id = $value;
		return $this;
	}
}

?>
