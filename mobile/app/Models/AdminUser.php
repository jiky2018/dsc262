<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class AdminUser extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'admin_user';
	protected $primaryKey = 'user_id';
	public $timestamps = false;
	protected $fillable = array('user_name', 'parent_id', 'ru_id', 'rs_id', 'email', 'password', 'ec_salt', 'add_time', 'last_login', 'last_ip', 'action_list', 'nav_list', 'lang_type', 'agency_id', 'suppliers_id', 'todolist', 'role_id', 'major_brand', 'admin_user_img', 'recently_cat');
	protected $guarded = array();

	public function service()
	{
		return $this->hasOne('App\\Models\\ImService', 'user_id', 'user_id');
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getRsId()
	{
		return $this->rs_id;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getEcSalt()
	{
		return $this->ec_salt;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getLastLogin()
	{
		return $this->last_login;
	}

	public function getLastIp()
	{
		return $this->last_ip;
	}

	public function getActionList()
	{
		return $this->action_list;
	}

	public function getNavList()
	{
		return $this->nav_list;
	}

	public function getLangType()
	{
		return $this->lang_type;
	}

	public function getAgencyId()
	{
		return $this->agency_id;
	}

	public function getSuppliersId()
	{
		return $this->suppliers_id;
	}

	public function getTodolist()
	{
		return $this->todolist;
	}

	public function getRoleId()
	{
		return $this->role_id;
	}

	public function getMajorBrand()
	{
		return $this->major_brand;
	}

	public function getAdminUserImg()
	{
		return $this->admin_user_img;
	}

	public function getRecentlyCat()
	{
		return $this->recently_cat;
	}

	public function setUserName($value)
	{
		$this->user_name = $value;
		return $this;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setRsId($value)
	{
		$this->rs_id = $value;
		return $this;
	}

	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}

	public function setPassword($value)
	{
		$this->password = $value;
		return $this;
	}

	public function setEcSalt($value)
	{
		$this->ec_salt = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setLastLogin($value)
	{
		$this->last_login = $value;
		return $this;
	}

	public function setLastIp($value)
	{
		$this->last_ip = $value;
		return $this;
	}

	public function setActionList($value)
	{
		$this->action_list = $value;
		return $this;
	}

	public function setNavList($value)
	{
		$this->nav_list = $value;
		return $this;
	}

	public function setLangType($value)
	{
		$this->lang_type = $value;
		return $this;
	}

	public function setAgencyId($value)
	{
		$this->agency_id = $value;
		return $this;
	}

	public function setSuppliersId($value)
	{
		$this->suppliers_id = $value;
		return $this;
	}

	public function setTodolist($value)
	{
		$this->todolist = $value;
		return $this;
	}

	public function setRoleId($value)
	{
		$this->role_id = $value;
		return $this;
	}

	public function setMajorBrand($value)
	{
		$this->major_brand = $value;
		return $this;
	}

	public function setAdminUserImg($value)
	{
		$this->admin_user_img = $value;
		return $this;
	}

	public function setRecentlyCat($value)
	{
		$this->recently_cat = $value;
		return $this;
	}
}

?>
