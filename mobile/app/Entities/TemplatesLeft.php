<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class TemplatesLeft extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'templates_left';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'seller_templates', 'bg_color', 'img_file', 'if_show', 'bgrepeat', 'align', 'type', 'theme', 'fileurl');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getSellerTemplates()
	{
		return $this->seller_templates;
	}

	public function getBgColor()
	{
		return $this->bg_color;
	}

	public function getImgFile()
	{
		return $this->img_file;
	}

	public function getIfShow()
	{
		return $this->if_show;
	}

	public function getBgrepeat()
	{
		return $this->bgrepeat;
	}

	public function getAlign()
	{
		return $this->align;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getTheme()
	{
		return $this->theme;
	}

	public function getFileurl()
	{
		return $this->fileurl;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setSellerTemplates($value)
	{
		$this->seller_templates = $value;
		return $this;
	}

	public function setBgColor($value)
	{
		$this->bg_color = $value;
		return $this;
	}

	public function setImgFile($value)
	{
		$this->img_file = $value;
		return $this;
	}

	public function setIfShow($value)
	{
		$this->if_show = $value;
		return $this;
	}

	public function setBgrepeat($value)
	{
		$this->bgrepeat = $value;
		return $this;
	}

	public function setAlign($value)
	{
		$this->align = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setTheme($value)
	{
		$this->theme = $value;
		return $this;
	}

	public function setFileurl($value)
	{
		$this->fileurl = $value;
		return $this;
	}
}

?>
