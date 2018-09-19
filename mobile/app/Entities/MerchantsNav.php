<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsNav extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_nav';
	public $timestamps = false;
	protected $fillable = array('ctype', 'cid', 'cat_id', 'name', 'ifshow', 'vieworder', 'opennew', 'url', 'type', 'ru_id');
	protected $guarded = array();

	public function getCtype()
	{
		return $this->ctype;
	}

	public function getCid()
	{
		return $this->cid;
	}

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getIfshow()
	{
		return $this->ifshow;
	}

	public function getVieworder()
	{
		return $this->vieworder;
	}

	public function getOpennew()
	{
		return $this->opennew;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function setCtype($value)
	{
		$this->ctype = $value;
		return $this;
	}

	public function setCid($value)
	{
		$this->cid = $value;
		return $this;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}

	public function setIfshow($value)
	{
		$this->ifshow = $value;
		return $this;
	}

	public function setVieworder($value)
	{
		$this->vieworder = $value;
		return $this;
	}

	public function setOpennew($value)
	{
		$this->opennew = $value;
		return $this;
	}

	public function setUrl($value)
	{
		$this->url = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}
}

?>
