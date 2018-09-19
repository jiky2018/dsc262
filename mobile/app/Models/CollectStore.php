<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class CollectStore extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'collect_store';
	protected $primaryKey = 'rec_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'ru_id', 'add_time', 'is_attention');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getIsAttention()
	{
		return $this->is_attention;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setIsAttention($value)
	{
		$this->is_attention = $value;
		return $this;
	}
}

?>
