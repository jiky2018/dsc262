<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class WechatUserTag extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_user_tag';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'tag_id', 'openid');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getTagId()
	{
		return $this->tag_id;
	}

	public function getOpenid()
	{
		return $this->openid;
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

	public function setOpenid($value)
	{
		$this->openid = $value;
		return $this;
	}
}

?>
