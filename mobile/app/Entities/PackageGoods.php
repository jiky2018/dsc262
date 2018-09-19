<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class PackageGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'package_goods';
	public $timestamps = false;
	protected $fillable = array('package_id', 'goods_id', 'product_id', 'goods_number', 'admin_id');
	protected $guarded = array();

	public function getPackageId()
	{
		return $this->package_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getProductId()
	{
		return $this->product_id;
	}

	public function getGoodsNumber()
	{
		return $this->goods_number;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function setPackageId($value)
	{
		$this->package_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setProductId($value)
	{
		$this->product_id = $value;
		return $this;
	}

	public function setGoodsNumber($value)
	{
		$this->goods_number = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}
}

?>
