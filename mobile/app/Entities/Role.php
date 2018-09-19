<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Role extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'role';
	protected $primaryKey = 'role_id';
	public $timestamps = false;
	protected $fillable = array('role_name', 'action_list', 'role_describe');
	protected $guarded = array();

	public function getRoleName()
	{
		return $this->role_name;
	}

	public function getActionList()
	{
		return $this->action_list;
	}

	public function getRoleDescribe()
	{
		return $this->role_describe;
	}

	public function setRoleName($value)
	{
		$this->role_name = $value;
		return $this;
	}

	public function setActionList($value)
	{
		$this->action_list = $value;
		return $this;
	}

	public function setRoleDescribe($value)
	{
		$this->role_describe = $value;
		return $this;
	}
}

?>
