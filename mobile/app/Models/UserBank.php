<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class UserBank extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'user_bank';
	public $timestamps = false;
	protected $fillable = array('bank_name', 'bank_card', 'bank_region', 'bank_user_name', 'user_id');
	protected $guarded = array();

	public function getBankName()
	{
		return $this->bank_name;
	}

	public function getBankCard()
	{
		return $this->bank_card;
	}

	public function getBankRegion()
	{
		return $this->bank_region;
	}

	public function getBankUserName()
	{
		return $this->bank_user_name;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function setBankName($value)
	{
		$this->bank_name = $value;
		return $this;
	}

	public function setBankCard($value)
	{
		$this->bank_card = $value;
		return $this;
	}

	public function setBankRegion($value)
	{
		$this->bank_region = $value;
		return $this;
	}

	public function setBankUserName($value)
	{
		$this->bank_user_name = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}
}

?>
