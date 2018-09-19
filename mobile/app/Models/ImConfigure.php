<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class ImConfigure extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'im_configure';
	public $timestamps = false;
	protected $fillable = array('ser_id', 'type', 'content', 'is_on');
	protected $guarded = array();

	public function getSerId()
	{
		return $this->ser_id;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getIsOn()
	{
		return $this->is_on;
	}

	public function setSerId($value)
	{
		$this->ser_id = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setIsOn($value)
	{
		$this->is_on = $value;
		return $this;
	}
}

?>
