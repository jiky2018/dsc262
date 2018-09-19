<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ZcInitiator extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'zc_initiator';
	public $timestamps = false;
	protected $fillable = array('name', 'company', 'img', 'intro', 'describe', 'rank');
	protected $guarded = array();

	public function getName()
	{
		return $this->name;
	}

	public function getCompany()
	{
		return $this->company;
	}

	public function getImg()
	{
		return $this->img;
	}

	public function getIntro()
	{
		return $this->intro;
	}

	public function getDescribe()
	{
		return $this->describe;
	}

	public function getRank()
	{
		return $this->rank;
	}

	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}

	public function setCompany($value)
	{
		$this->company = $value;
		return $this;
	}

	public function setImg($value)
	{
		$this->img = $value;
		return $this;
	}

	public function setIntro($value)
	{
		$this->intro = $value;
		return $this;
	}

	public function setDescribe($value)
	{
		$this->describe = $value;
		return $this;
	}

	public function setRank($value)
	{
		$this->rank = $value;
		return $this;
	}
}

?>
