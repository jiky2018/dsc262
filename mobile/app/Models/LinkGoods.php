<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class LinkGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'link_goods';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'link_goods_id', 'is_double', 'admin_id');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getLinkGoodsId()
	{
		return $this->link_goods_id;
	}

	public function getIsDouble()
	{
		return $this->is_double;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setLinkGoodsId($value)
	{
		$this->link_goods_id = $value;
		return $this;
	}

	public function setIsDouble($value)
	{
		$this->is_double = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}
}

?>
