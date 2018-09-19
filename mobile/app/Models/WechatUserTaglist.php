<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WechatUserTaglist extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_user_taglist';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'tag_id', 'name', 'count', 'sort');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getTagId()
	{
		return $this->tag_id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getCount()
	{
		return $this->count;
	}

	public function getSort()
	{
		return $this->sort;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setTagId($value)
	{
		$this->tag_id = $value;
		return $this;
	}

	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}

	public function setCount($value)
	{
		$this->count = $value;
		return $this;
	}

	public function setSort($value)
	{
		$this->sort = $value;
		return $this;
	}
}

?>
