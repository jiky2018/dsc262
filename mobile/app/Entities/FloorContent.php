<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class FloorContent extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'floor_content';
	protected $primaryKey = 'fb_id';
	public $timestamps = false;
	protected $fillable = array('filename', 'region', 'id_name', 'brand_id', 'brand_name', 'theme');
	protected $guarded = array();

	public function getFilename()
	{
		return $this->filename;
	}

	public function getRegion()
	{
		return $this->region;
	}

	public function getIdName()
	{
		return $this->id_name;
	}

	public function getBrandId()
	{
		return $this->brand_id;
	}

	public function getBrandName()
	{
		return $this->brand_name;
	}

	public function getTheme()
	{
		return $this->theme;
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

	public function setIdName($value)
	{
		$this->id_name = $value;
		return $this;
	}

	public function setBrandId($value)
	{
		$this->brand_id = $value;
		return $this;
	}

	public function setBrandName($value)
	{
		$this->brand_name = $value;
		return $this;
	}

	public function setTheme($value)
	{
		$this->theme = $value;
		return $this;
	}
}

?>
