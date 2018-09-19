<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class MerchantsStepsImg extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_steps_img';
	protected $primaryKey = 'gid';
	public $timestamps = false;
	protected $fillable = array('tid', 'steps_img');
	protected $guarded = array();

	public function getTid()
	{
		return $this->tid;
	}

	public function getStepsImg()
	{
		return $this->steps_img;
	}

	public function setTid($value)
	{
		$this->tid = $value;
		return $this;
	}

	public function setStepsImg($value)
	{
		$this->steps_img = $value;
		return $this;
	}
}

?>
