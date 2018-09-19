<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsCategoryTemporarydate extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_category_temporarydate';
	protected $primaryKey = 'ct_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'cat_id', 'parent_id', 'cat_name', 'parent_name', 'is_add');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getCatName()
	{
		return $this->cat_name;
	}

	public function getParentName()
	{
		return $this->parent_name;
	}

	public function getIsAdd()
	{
		return $this->is_add;
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

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setCatName($value)
	{
		$this->cat_name = $value;
		return $this;
	}

	public function setParentName($value)
	{
		$this->parent_name = $value;
		return $this;
	}

	public function setIsAdd($value)
	{
		$this->is_add = $value;
		return $this;
	}
}

?>
