<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Plugins extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'plugins';
	protected $primaryKey = 'code';
	public $timestamps = false;
	protected $fillable = array('version', 'library', 'assign', 'install_date');
	protected $guarded = array();

	public function getVersion()
	{
		return $this->version;
	}

	public function getLibrary()
	{
		return $this->library;
	}

	public function getAssign()
	{
		return $this->assign;
	}

	public function getInstallDate()
	{
		return $this->install_date;
	}

	public function setVersion($value)
	{
		$this->version = $value;
		return $this;
	}

	public function setLibrary($value)
	{
		$this->library = $value;
		return $this;
	}

	public function setAssign($value)
	{
		$this->assign = $value;
		return $this;
	}

	public function setInstallDate($value)
	{
		$this->install_date = $value;
		return $this;
	}
}

?>
