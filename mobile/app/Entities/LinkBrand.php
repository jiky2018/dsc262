<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class LinkBrand extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'link_brand';
	public $timestamps = false;
	protected $fillable = array('bid', 'brand_id');
	protected $guarded = array();

	public function getBid()
	{
		return $this->bid;
	}

	public function getBrandId()
	{
		return $this->brand_id;
	}

	public function setBid($value)
	{
		$this->bid = $value;
		return $this;
	}

	public function setBrandId($value)
	{
		$this->brand_id = $value;
		return $this;
	}
}

?>
