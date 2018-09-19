<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatWallMsg extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_wall_msg';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'wall_id', 'user_id', 'content', 'addtime', 'checktime', 'status');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getWallId()
	{
		return $this->wall_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getAddtime()
	{
		return $this->addtime;
	}

	public function getChecktime()
	{
		return $this->checktime;
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

	public function setWallId($value)
	{
		$this->wall_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setAddtime($value)
	{
		$this->addtime = $value;
		return $this;
	}

	public function setChecktime($value)
	{
		$this->checktime = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}
}

?>
