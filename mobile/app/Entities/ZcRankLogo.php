<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ZcRankLogo extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'zc_rank_logo';
	public $timestamps = false;
	protected $fillable = array('logo_name', 'img', 'logo_intro');
	protected $guarded = array();

	public function getLogoName()
	{
		return $this->logo_name;
	}

	public function getImg()
	{
		return $this->img;
	}

	public function getLogoIntro()
	{
		return $this->logo_intro;
	}

	public function setLogoName($value)
	{
		$this->logo_name = $value;
		return $this;
	}

	public function setImg($value)
	{
		$this->img = $value;
		return $this;
	}

	public function setLogoIntro($value)
	{
		$this->logo_intro = $value;
		return $this;
	}
}

?>
