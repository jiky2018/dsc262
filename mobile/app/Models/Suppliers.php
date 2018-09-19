<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Suppliers extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'suppliers';
	protected $primaryKey = 'suppliers_id';
	public $timestamps = false;
	protected $fillable = array('suppliers_name', 'suppliers_desc', 'is_check');
	protected $guarded = array();

	public function getSuppliersName()
	{
		return $this->suppliers_name;
	}

	public function getSuppliersDesc()
	{
		return $this->suppliers_desc;
	}

	public function getIsCheck()
	{
		return $this->is_check;
	}

	public function setSuppliersName($value)
	{
		$this->suppliers_name = $value;
		return $this;
	}

	public function setSuppliersDesc($value)
	{
		$this->suppliers_desc = $value;
		return $this;
	}

	public function setIsCheck($value)
	{
		$this->is_check = $value;
		return $this;
	}
}

?>
