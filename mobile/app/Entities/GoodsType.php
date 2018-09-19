<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class GoodsType extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_type';
	protected $primaryKey = 'cat_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'suppliers_id', 'cat_name', 'enabled', 'attr_group', 'c_id');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getSuppliersId()
	{
		return $this->suppliers_id;
	}

	public function getCatName()
	{
		return $this->cat_name;
	}

	public function getEnabled()
	{
		return $this->enabled;
	}

	public function getAttrGroup()
	{
		return $this->attr_group;
	}

	public function getCId()
	{
		return $this->c_id;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setSuppliersId($value)
	{
		$this->suppliers_id = $value;
		return $this;
	}

	public function setCatName($value)
	{
		$this->cat_name = $value;
		return $this;
	}

	public function setEnabled($value)
	{
		$this->enabled = $value;
		return $this;
	}

	public function setAttrGroup($value)
	{
		$this->attr_group = $value;
		return $this;
	}

	public function setCId($value)
	{
		$this->c_id = $value;
		return $this;
	}
}

?>
