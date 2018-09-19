<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class GoodsTransportTpl extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_transport_tpl';
	public $timestamps = false;
	protected $fillable = array('tpl_name', 'tid', 'user_id', 'shipping_id', 'region_id', 'configure', 'admin_id');
	protected $guarded = array();

	public function getTplName()
	{
		return $this->tpl_name;
	}

	public function getTid()
	{
		return $this->tid;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getShippingId()
	{
		return $this->shipping_id;
	}

	public function getRegionId()
	{
		return $this->region_id;
	}

	public function getConfigure()
	{
		return $this->configure;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function setTplName($value)
	{
		$this->tpl_name = $value;
		return $this;
	}

	public function setTid($value)
	{
		$this->tid = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setShippingId($value)
	{
		$this->shipping_id = $value;
		return $this;
	}

	public function setRegionId($value)
	{
		$this->region_id = $value;
		return $this;
	}

	public function setConfigure($value)
	{
		$this->configure = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}
}

?>
