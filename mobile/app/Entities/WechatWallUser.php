<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatWallUser extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_wall_user';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'wall_id', 'nickname', 'sex', 'headimg', 'status', 'addtime', 'checktime', 'openid', 'wechatname', 'headimgurl', 'sign_number');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getWallId()
	{
		return $this->wall_id;
	}

	public function getNickname()
	{
		return $this->nickname;
	}

	public function getSex()
	{
		return $this->sex;
	}

	public function getHeadimg()
	{
		return $this->headimg;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getAddtime()
	{
		return $this->addtime;
	}

	public function getChecktime()
	{
		return $this->checktime;
	}

	public function getOpenid()
	{
		return $this->openid;
	}

	public function getWechatname()
	{
		return $this->wechatname;
	}

	public function getHeadimgurl()
	{
		return $this->headimgurl;
	}

	public function getSignNumber()
	{
		return $this->sign_number;
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

	public function setNickname($value)
	{
		$this->nickname = $value;
		return $this;
	}

	public function setSex($value)
	{
		$this->sex = $value;
		return $this;
	}

	public function setHeadimg($value)
	{
		$this->headimg = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
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

	public function setOpenid($value)
	{
		$this->openid = $value;
		return $this;
	}

	public function setWechatname($value)
	{
		$this->wechatname = $value;
		return $this;
	}

	public function setHeadimgurl($value)
	{
		$this->headimgurl = $value;
		return $this;
	}

	public function setSignNumber($value)
	{
		$this->sign_number = $value;
		return $this;
	}
}

?>
