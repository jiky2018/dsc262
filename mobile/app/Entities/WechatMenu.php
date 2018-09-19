<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatMenu extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_menu';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'pid', 'name', 'type', 'key', 'url', 'sort', 'status');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getPid()
	{
		return $this->pid;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getSort()
	{
		return $this->sort;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setPid($value)
	{
		$this->pid = $value;
		return $this;
	}

	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}

	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}

	public function setKey($value)
	{
		$this->key = $value;
		return $this;
	}

	public function setUrl($value)
	{
		$this->url = $value;
		return $this;
	}

	public function setSort($value)
	{
		$this->sort = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}
}

?>
