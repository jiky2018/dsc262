<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class PayCardType extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'pay_card_type';
	protected $primaryKey = 'type_id';
	public $timestamps = false;
	protected $fillable = array('type_name', 'type_money', 'type_prefix', 'use_end_date');
	protected $guarded = array();

	public function getTypeName()
	{
		return $this->type_name;
	}

	public function getTypeMoney()
	{
		return $this->type_money;
	}

	public function getTypePrefix()
	{
		return $this->type_prefix;
	}

	public function getUseEndDate()
	{
		return $this->use_end_date;
	}

	public function setTypeName($value)
	{
		$this->type_name = $value;
		return $this;
	}

	public function setTypeMoney($value)
	{
		$this->type_money = $value;
		return $this;
	}

	public function setTypePrefix($value)
	{
		$this->type_prefix = $value;
		return $this;
	}

	public function setUseEndDate($value)
	{
		$this->use_end_date = $value;
		return $this;
	}
}

?>
