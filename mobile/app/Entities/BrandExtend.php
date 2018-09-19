<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class BrandExtend extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'brand_extend';
	public $timestamps = false;
	protected $fillable = array('brand_id', 'is_recommend');
	protected $guarded = array();

	public function getBrandId()
	{
		return $this->brand_id;
	}

	public function getIsRecommend()
	{
		return $this->is_recommend;
	}

	public function setBrandId($value)
	{
		$this->brand_id = $value;
		return $this;
	}

	public function setIsRecommend($value)
	{
		$this->is_recommend = $value;
		return $this;
	}
}

?>
