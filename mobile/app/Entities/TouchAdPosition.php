<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class TouchAdPosition extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'touch_ad_position';
	protected $primaryKey = 'position_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'position_name', 'ad_width', 'ad_height', 'position_desc', 'position_style', 'is_public', 'theme', 'tc_id', 'tc_type', 'ad_type');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getPositionName()
	{
		return $this->position_name;
	}

	public function getAdWidth()
	{
		return $this->ad_width;
	}

	public function getAdHeight()
	{
		return $this->ad_height;
	}

	public function getPositionDesc()
	{
		return $this->position_desc;
	}

	public function getPositionStyle()
	{
		return $this->position_style;
	}

	public function getIsPublic()
	{
		return $this->is_public;
	}

	public function getTheme()
	{
		return $this->theme;
	}

	public function getTcId()
	{
		return $this->tc_id;
	}

	public function getTcType()
	{
		return $this->tc_type;
	}

	public function getAdType()
	{
		return $this->ad_type;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setPositionName($value)
	{
		$this->position_name = $value;
		return $this;
	}

	public function setAdWidth($value)
	{
		$this->ad_width = $value;
		return $this;
	}

	public function setAdHeight($value)
	{
		$this->ad_height = $value;
		return $this;
	}

	public function setPositionDesc($value)
	{
		$this->position_desc = $value;
		return $this;
	}

	public function setPositionStyle($value)
	{
		$this->position_style = $value;
		return $this;
	}

	public function setIsPublic($value)
	{
		$this->is_public = $value;
		return $this;
	}

	public function setTheme($value)
	{
		$this->theme = $value;
		return $this;
	}

	public function setTcId($value)
	{
		$this->tc_id = $value;
		return $this;
	}

	public function setTcType($value)
	{
		$this->tc_type = $value;
		return $this;
	}

	public function setAdType($value)
	{
		$this->ad_type = $value;
		return $this;
	}
}

?>
