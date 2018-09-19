<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatUser extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_user';
	protected $primaryKey = 'uid';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'subscribe', 'openid', 'nickname', 'sex', 'city', 'country', 'province', 'language', 'headimgurl', 'subscribe_time', 'remark', 'privilege', 'unionid', 'groupid', 'ect_uid', 'bein_kefu', 'parent_id', 'drp_parent_id', 'from', 'subscribe_scene', 'qr_scene', 'qr_scene_str');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getSubscribe()
	{
		return $this->subscribe;
	}

	public function getOpenid()
	{
		return $this->openid;
	}

	public function getNickname()
	{
		return $this->nickname;
	}

	public function getSex()
	{
		return $this->sex;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function getCountry()
	{
		return $this->country;
	}

	public function getProvince()
	{
		return $this->province;
	}

	public function getLanguage()
	{
		return $this->language;
	}

	public function getHeadimgurl()
	{
		return $this->headimgurl;
	}

	public function getSubscribeTime()
	{
		return $this->subscribe_time;
	}

	public function getRemark()
	{
		return $this->remark;
	}

	public function getPrivilege()
	{
		return $this->privilege;
	}

	public function getUnionid()
	{
		return $this->unionid;
	}

	public function getGroupid()
	{
		return $this->groupid;
	}

	public function getEctUid()
	{
		return $this->ect_uid;
	}

	public function getBeinKefu()
	{
		return $this->bein_kefu;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getDrpParentId()
	{
		return $this->drp_parent_id;
	}

	public function getFrom()
	{
		return $this->from;
	}

	public function getSubscribeScene()
	{
		return $this->subscribe_scene;
	}

	public function getQrScene()
	{
		return $this->qr_scene;
	}

	public function getQrSceneStr()
	{
		return $this->qr_scene_str;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setSubscribe($value)
	{
		$this->subscribe = $value;
		return $this;
	}

	public function setOpenid($value)
	{
		$this->openid = $value;
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

	public function setCity($value)
	{
		$this->city = $value;
		return $this;
	}

	public function setCountry($value)
	{
		$this->country = $value;
		return $this;
	}

	public function setProvince($value)
	{
		$this->province = $value;
		return $this;
	}

	public function setLanguage($value)
	{
		$this->language = $value;
		return $this;
	}

	public function setHeadimgurl($value)
	{
		$this->headimgurl = $value;
		return $this;
	}

	public function setSubscribeTime($value)
	{
		$this->subscribe_time = $value;
		return $this;
	}

	public function setRemark($value)
	{
		$this->remark = $value;
		return $this;
	}

	public function setPrivilege($value)
	{
		$this->privilege = $value;
		return $this;
	}

	public function setUnionid($value)
	{
		$this->unionid = $value;
		return $this;
	}

	public function setGroupid($value)
	{
		$this->groupid = $value;
		return $this;
	}

	public function setEctUid($value)
	{
		$this->ect_uid = $value;
		return $this;
	}

	public function setBeinKefu($value)
	{
		$this->bein_kefu = $value;
		return $this;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setDrpParentId($value)
	{
		$this->drp_parent_id = $value;
		return $this;
	}

	public function setFrom($value)
	{
		$this->from = $value;
		return $this;
	}

	public function setSubscribeScene($value)
	{
		$this->subscribe_scene = $value;
		return $this;
	}

	public function setQrScene($value)
	{
		$this->qr_scene = $value;
		return $this;
	}

	public function setQrSceneStr($value)
	{
		$this->qr_scene_str = $value;
		return $this;
	}
}

?>
