<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class EmailList extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'email_list';
	public $timestamps = false;
	protected $fillable = array('email', 'stat', 'hash');
	protected $guarded = array();

	public function getEmail()
	{
		return $this->email;
	}

	public function getStat()
	{
		return $this->stat;
	}

	public function getHash()
	{
		return $this->hash;
	}

	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}

	public function setStat($value)
	{
		$this->stat = $value;
		return $this;
	}

	public function setHash($value)
	{
		$this->hash = $value;
		return $this;
	}
}

?>
