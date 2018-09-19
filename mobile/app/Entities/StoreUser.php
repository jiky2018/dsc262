<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class StoreUser extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'store_user';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'store_id', 'parent_id', 'stores_user', 'stores_pwd', 'tel', 'email', 'store_action', 'add_time', 'ec_salt', 'store_user_img');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getStoreId()
	{
		return $this->store_id;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getStoresUser()
	{
		return $this->stores_user;
	}

	public function getStoresPwd()
	{
		return $this->stores_pwd;
	}

	public function getTel()
	{
		return $this->tel;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getStoreAction()
	{
		return $this->store_action;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getEcSalt()
	{
		return $this->ec_salt;
	}

	public function getStoreUserImg()
	{
		return $this->store_user_img;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setStoreId($value)
	{
		$this->store_id = $value;
		return $this;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setStoresUser($value)
	{
		$this->stores_user = $value;
		return $this;
	}

	public function setStoresPwd($value)
	{
		$this->stores_pwd = $value;
		return $this;
	}

	public function setTel($value)
	{
		$this->tel = $value;
		return $this;
	}

	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}

	public function setStoreAction($value)
	{
		$this->store_action = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setEcSalt($value)
	{
		$this->ec_salt = $value;
		return $this;
	}

	public function setStoreUserImg($value)
	{
		$this->store_user_img = $value;
		return $this;
	}
}

?>
