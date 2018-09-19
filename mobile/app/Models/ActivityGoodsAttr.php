<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ActivityGoodsAttr extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'activity_goods_attr';
	public $timestamps = false;
	protected $fillable = array('bargain_id', 'goods_id', 'product_id', 'target_price', 'type');
	protected $guarded = array();

	public function getBargainId()
	{
		return $this->bargain_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getProductId()
	{
		return $this->product_id;
	}

	public function getTargetPrice()
	{
		return $this->target_price;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setBargainId($value)
	{
		$this->bargain_id = $value;
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

	public function setTargetPrice($value)
	{
		$this->target_price = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}
}

?>
