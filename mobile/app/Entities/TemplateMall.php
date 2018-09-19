<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class TemplateMall extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'template_mall';
	protected $primaryKey = 'temp_id';
	public $timestamps = false;
	protected $fillable = array('temp_file', 'temp_mode', 'temp_cost', 'temp_code', 'add_time', 'sales_volume');
	protected $guarded = array();

	public function getTempFile()
	{
		return $this->temp_file;
	}

	public function getTempMode()
	{
		return $this->temp_mode;
	}

	public function getTempCost()
	{
		return $this->temp_cost;
	}

	public function getTempCode()
	{
		return $this->temp_code;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getSalesVolume()
	{
		return $this->sales_volume;
	}

	public function setTempFile($value)
	{
		$this->temp_file = $value;
		return $this;
	}

	public function setTempMode($value)
	{
		$this->temp_mode = $value;
		return $this;
	}

	public function setTempCost($value)
	{
		$this->temp_cost = $value;
		return $this;
	}

	public function setTempCode($value)
	{
		$this->temp_code = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setSalesVolume($value)
	{
		$this->sales_volume = $value;
		return $this;
	}
}

?>
