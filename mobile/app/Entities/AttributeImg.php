<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class AttributeImg extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'attribute_img';
	public $timestamps = false;
	protected $fillable = array('attr_id', 'attr_values', 'attr_img', 'attr_site');
	protected $guarded = array();

	public function getAttrId()
	{
		return $this->attr_id;
	}

	public function getAttrValues()
	{
		return $this->attr_values;
	}

	public function getAttrImg()
	{
		return $this->attr_img;
	}

	public function getAttrSite()
	{
		return $this->attr_site;
	}

	public function setAttrId($value)
	{
		$this->attr_id = $value;
		return $this;
	}

	public function setAttrValues($value)
	{
		$this->attr_values = $value;
		return $this;
	}

	public function setAttrImg($value)
	{
		$this->attr_img = $value;
		return $this;
	}

	public function setAttrSite($value)
	{
		$this->attr_site = $value;
		return $this;
	}
}

?>
