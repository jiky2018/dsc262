<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ShopConfig extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'shop_config';
	public $timestamps = false;
	protected $fillable = array('parent_id', 'code', 'type', 'store_range', 'store_dir', 'value', 'sort_order', 'shop_group');
	protected $guarded = array();

	public function getParentId()
	{
		return $this->parent_id;
	}

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

	public function getStoreDir()
	{
		return $this->store_dir;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getShopGroup()
	{
		return $this->shop_group;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
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

	public function setStoreDir($value)
	{
		$this->store_dir = $value;
		return $this;
	}

	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setShopGroup($value)
	{
		$this->shop_group = $value;
		return $this;
	}
}

?>
