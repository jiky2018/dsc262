<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Crons extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'crons';
	protected $primaryKey = 'cron_id';
	public $timestamps = false;
	protected $fillable = array('cron_code', 'cron_name', 'cron_desc', 'cron_order', 'cron_config', 'thistime', 'nextime', 'day', 'week', 'hour', 'minute', 'enable', 'run_once', 'allow_ip', 'alow_files');
	protected $guarded = array();

	public function getCronCode()
	{
		return $this->cron_code;
	}

	public function getCronName()
	{
		return $this->cron_name;
	}

	public function getCronDesc()
	{
		return $this->cron_desc;
	}

	public function getCronOrder()
	{
		return $this->cron_order;
	}

	public function getCronConfig()
	{
		return $this->cron_config;
	}

	public function getThistime()
	{
		return $this->thistime;
	}

	public function getNextime()
	{
		return $this->nextime;
	}

	public function getDay()
	{
		return $this->day;
	}

	public function getWeek()
	{
		return $this->week;
	}

	public function getHour()
	{
		return $this->hour;
	}

	public function getMinute()
	{
		return $this->minute;
	}

	public function getEnable()
	{
		return $this->enable;
	}

	public function getRunOnce()
	{
		return $this->run_once;
	}

	public function getAllowIp()
	{
		return $this->allow_ip;
	}

	public function getAlowFiles()
	{
		return $this->alow_files;
	}

	public function setCronCode($value)
	{
		$this->cron_code = $value;
		return $this;
	}

	public function setCronName($value)
	{
		$this->cron_name = $value;
		return $this;
	}

	public function setCronDesc($value)
	{
		$this->cron_desc = $value;
		return $this;
	}

	public function setCronOrder($value)
	{
		$this->cron_order = $value;
		return $this;
	}

	public function setCronConfig($value)
	{
		$this->cron_config = $value;
		return $this;
	}

	public function setThistime($value)
	{
		$this->thistime = $value;
		return $this;
	}

	public function setNextime($value)
	{
		$this->nextime = $value;
		return $this;
	}

	public function setDay($value)
	{
		$this->day = $value;
		return $this;
	}

	public function setWeek($value)
	{
		$this->week = $value;
		return $this;
	}

	public function setHour($value)
	{
		$this->hour = $value;
		return $this;
	}

	public function setMinute($value)
	{
		$this->minute = $value;
		return $this;
	}

	public function setEnable($value)
	{
		$this->enable = $value;
		return $this;
	}

	public function setRunOnce($value)
	{
		$this->run_once = $value;
		return $this;
	}

	public function setAllowIp($value)
	{
		$this->allow_ip = $value;
		return $this;
	}

	public function setAlowFiles($value)
	{
		$this->alow_files = $value;
		return $this;
	}
}

?>
