<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ErrorLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'error_log';
	public $timestamps = false;
	protected $fillable = array('info', 'file', 'time');
	protected $guarded = array();

	public function getInfo()
	{
		return $this->info;
	}

	public function getFile()
	{
		return $this->file;
	}

	public function getTime()
	{
		return $this->time;
	}

	public function setInfo($value)
	{
		$this->info = $value;
		return $this;
	}

	public function setFile($value)
	{
		$this->file = $value;
		return $this;
	}

	public function setTime($value)
	{
		$this->time = $value;
		return $this;
	}
}

?>
