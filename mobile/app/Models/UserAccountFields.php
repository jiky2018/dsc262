<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class UserAccountFields extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'user_account_fields';
	public $timestamps = false;
	protected $fillable = array('user_id', 'account_id', 'bank_number', 'real_name');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getAccountId()
	{
		return $this->account_id;
	}

	public function getBankNumber()
	{
		return $this->bank_number;
	}

	public function getRealName()
	{
		return $this->real_name;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setAccountId($value)
	{
		$this->account_id = $value;
		return $this;
	}

	public function setBankNumber($value)
	{
		$this->bank_number = $value;
		return $this;
	}

	public function setRealName($value)
	{
		$this->real_name = $value;
		return $this;
	}
}

?>
