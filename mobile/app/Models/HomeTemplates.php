<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class HomeTemplates extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'home_templates';
	protected $primaryKey = 'temp_id';
	public $timestamps = false;
	protected $fillable = array('rs_id', 'code', 'is_enable', 'theme');
	protected $guarded = array();

	public function getRsId()
	{
		return $this->rs_id;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getIsEnable()
	{
		return $this->is_enable;
	}

	public function getTheme()
	{
		return $this->theme;
	}

	public function setRsId($value)
	{
		$this->rs_id = $value;
		return $this;
	}

	public function setCode($value)
	{
		$this->code = $value;
		return $this;
	}

	public function setIsEnable($value)
	{
		$this->is_enable = $value;
		return $this;
	}

	public function setTheme($value)
	{
		$this->theme = $value;
		return $this;
	}
}

?>
