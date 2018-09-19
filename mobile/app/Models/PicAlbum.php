<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class PicAlbum extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'pic_album';
	protected $primaryKey = 'pic_id';
	public $timestamps = false;
	protected $fillable = array('pic_name', 'album_id', 'pic_file', 'pic_thumb', 'pic_image', 'pic_size', 'pic_spec', 'ru_id', 'add_time');
	protected $guarded = array();

	public function getPicName()
	{
		return $this->pic_name;
	}

	public function getAlbumId()
	{
		return $this->album_id;
	}

	public function getPicFile()
	{
		return $this->pic_file;
	}

	public function getPicThumb()
	{
		return $this->pic_thumb;
	}

	public function getPicImage()
	{
		return $this->pic_image;
	}

	public function getPicSize()
	{
		return $this->pic_size;
	}

	public function getPicSpec()
	{
		return $this->pic_spec;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setPicName($value)
	{
		$this->pic_name = $value;
		return $this;
	}

	public function setAlbumId($value)
	{
		$this->album_id = $value;
		return $this;
	}

	public function setPicFile($value)
	{
		$this->pic_file = $value;
		return $this;
	}

	public function setPicThumb($value)
	{
		$this->pic_thumb = $value;
		return $this;
	}

	public function setPicImage($value)
	{
		$this->pic_image = $value;
		return $this;
	}

	public function setPicSize($value)
	{
		$this->pic_size = $value;
		return $this;
	}

	public function setPicSpec($value)
	{
		$this->pic_spec = $value;
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
}

?>
