<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class DrpType extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'drp_type';
	public $timestamps = false;
	protected $fillable = array('user_id', 'cat_id', 'goods_id', 'type', 'add_time');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
