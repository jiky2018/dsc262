<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class GroupGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'group_goods';
	public $timestamps = false;
	protected $fillable = array('parent_id', 'goods_id', 'goods_price', 'admin_id', 'group_id');
	protected $guarded = array();

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getGoodsPrice()
	{
		return $this->goods_price;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function getGroupId()
	{
		return $this->group_id;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setGoodsPrice($value)
	{
		$this->goods_price = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}

	public function setGroupId($value)
	{
		$this->group_id = $value;
		return $this;
	}
}

?>
