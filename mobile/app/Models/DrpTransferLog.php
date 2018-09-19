<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class DrpTransferLog extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'drp_transfer_log';
	public $timestamps = false;
	protected $fillable = array('user_id', 'money', 'add_time');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getMoney()
	{
		return $this->money;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setMoney($value)
	{
		$this->money = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
