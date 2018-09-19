<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class CollectBrand extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'collect_brand';
	protected $primaryKey = 'rec_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'brand_id', 'add_time', 'ru_id', 'user_brand');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getBrandId()
	{
		return $this->brand_id;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getUserBrand()
	{
		return $this->user_brand;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setBrandId($value)
	{
		$this->brand_id = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setUserBrand($value)
	{
		$this->user_brand = $value;
		return $this;
	}
}

?>
