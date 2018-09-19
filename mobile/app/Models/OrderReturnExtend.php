<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class OrderReturnExtend extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'order_return_extend';
	public $timestamps = false;
	protected $fillable = array('ret_id', 'return_number', 'aftersn');
	protected $guarded = array();

	public function getRetId()
	{
		return $this->ret_id;
	}

	public function getReturnNumber()
	{
		return $this->return_number;
	}

	public function getAftersn()
	{
		return $this->aftersn;
	}

	public function setRetId($value)
	{
		$this->ret_id = $value;
		return $this;
	}

	public function setReturnNumber($value)
	{
		$this->return_number = $value;
		return $this;
	}

	public function setAftersn($value)
	{
		$this->aftersn = $value;
		return $this;
	}
}

?>
