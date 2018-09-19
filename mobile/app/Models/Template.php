<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Template extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'template';
	public $timestamps = false;
	protected $fillable = array('filename', 'region', 'library', 'sort_order', 'number', 'type', 'theme', 'remarks', 'floor_tpl');
	protected $guarded = array();

	public function getFilename()
	{
		return $this->filename;
	}

	public function getRegion()
	{
		return $this->region;
	}

	public function getLibrary()
	{
		return $this->library;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getNumber()
	{
		return $this->number;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getTheme()
	{
		return $this->theme;
	}

	public function getRemarks()
	{
		return $this->remarks;
	}

	public function getFloorTpl()
	{
		return $this->floor_tpl;
	}

	public function setFilename($value)
	{
		$this->filename = $value;
		return $this;
	}

	public function setRegion($value)
	{
		$this->region = $value;
		return $this;
	}

	public function setLibrary($value)
	{
		$this->library = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setNumber($value)
	{
		$this->number = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setTheme($value)
	{
		$this->theme = $value;
		return $this;
	}

	public function setRemarks($value)
	{
		$this->remarks = $value;
		return $this;
	}

	public function setFloorTpl($value)
	{
		$this->floor_tpl = $value;
		return $this;
	}
}

?>
