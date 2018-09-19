<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class OrderCloud extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'order_cloud';
	public $timestamps = false;
	protected $fillable = array('apiordersn', 'goods_id', 'user_id', 'totalprice', 'rec_id', 'parentordersn', 'cloud_orderid', 'cloud_detailed_id');
	protected $guarded = array();

	public function getApiordersn()
	{
		return $this->apiordersn;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getTotalprice()
	{
		return $this->totalprice;
	}

	public function getRecId()
	{
		return $this->rec_id;
	}

	public function getParentordersn()
	{
		return $this->parentordersn;
	}

	public function getCloudOrderid()
	{
		return $this->cloud_orderid;
	}

	public function getCloudDetailedId()
	{
		return $this->cloud_detailed_id;
	}

	public function setApiordersn($value)
	{
		$this->apiordersn = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setTotalprice($value)
	{
		$this->totalprice = $value;
		return $this;
	}

	public function setRecId($value)
	{
		$this->rec_id = $value;
		return $this;
	}

	public function setParentordersn($value)
	{
		$this->parentordersn = $value;
		return $this;
	}

	public function setCloudOrderid($value)
	{
		$this->cloud_orderid = $value;
		return $this;
	}

	public function setCloudDetailedId($value)
	{
		$this->cloud_detailed_id = $value;
		return $this;
	}
}

?>
