<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ReturnImages extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'return_images';
	public $timestamps = false;
	protected $fillable = array('rg_id', 'rec_id', 'user_id', 'img_file', 'add_time');
	protected $guarded = array();

	public function getRgId()
	{
		return $this->rg_id;
	}

	public function getRecId()
	{
		return $this->rec_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getImgFile()
	{
		return $this->img_file;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setRgId($value)
	{
		$this->rg_id = $value;
		return $this;
	}

	public function setRecId($value)
	{
		$this->rec_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setImgFile($value)
	{
		$this->img_file = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
