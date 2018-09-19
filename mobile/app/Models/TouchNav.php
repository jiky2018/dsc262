<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class TouchNav extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'touch_nav';
	public $timestamps = false;
	protected $fillable = array('ctype', 'cid', 'name', 'ifshow', 'vieworder', 'opennew', 'url', 'type', 'pic');
	protected $guarded = array();

	public function getCtype()
	{
		return $this->ctype;
	}

	public function getCid()
	{
		return $this->cid;
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

	public function getPic()
	{
		return $this->pic;
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

	public function setPic($value)
	{
		$this->pic = $value;
		return $this;
	}
}

?>
