<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class GoodsTransportExtend extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_transport_extend';
	public $timestamps = false;
	protected $fillable = array('tid', 'ru_id', 'admin_id', 'area_id', 'top_area_id', 'sprice');
	protected $guarded = array();

	public function getTid()
	{
		return $this->tid;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function getAreaId()
	{
		return $this->area_id;
	}

	public function getTopAreaId()
	{
		return $this->top_area_id;
	}

	public function getSprice()
	{
		return $this->sprice;
	}

	public function setTid($value)
	{
		$this->tid = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}

	public function setAreaId($value)
	{
		$this->area_id = $value;
		return $this;
	}

	public function setTopAreaId($value)
	{
		$this->top_area_id = $value;
		return $this;
	}

	public function setSprice($value)
	{
		$this->sprice = $value;
		return $this;
	}
}

?>
