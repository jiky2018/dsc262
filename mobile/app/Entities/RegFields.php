<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class RegFields extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'reg_fields';
	public $timestamps = false;
	protected $fillable = array('reg_field_name', 'dis_order', 'display', 'type', 'is_need');
	protected $guarded = array();

	public function getRegFieldName()
	{
		return $this->reg_field_name;
	}

	public function getDisOrder()
	{
		return $this->dis_order;
	}

	public function getDisplay()
	{
		return $this->display;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getIsNeed()
	{
		return $this->is_need;
	}

	public function setRegFieldName($value)
	{
		$this->reg_field_name = $value;
		return $this;
	}

	public function setDisOrder($value)
	{
		$this->dis_order = $value;
		return $this;
	}

	public function setDisplay($value)
	{
		$this->display = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setIsNeed($value)
	{
		$this->is_need = $value;
		return $this;
	}
}

?>
