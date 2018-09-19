<?php
//zend by 商创网络 Q Q:123456    禁止倒卖 一经发现停止任何服务
namespace App\Models;

class User extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'users';
	protected $primaryKey = 'user_id';
	public $timestamps = false;
	protected $fillable = array('aite_id', 'email', 'user_name', 'nick_name', 'password', 'question', 'answer', 'sex', 'birthday', 'user_money', 'frozen_money', 'pay_points', 'rank_points', 'address_id', 'reg_time', 'last_login', 'last_time', 'last_ip', 'visit_count', 'user_rank', 'is_special', 'ec_salt', 'salt', 'drp_parent_id', 'parent_id', 'flag', 'alias', 'msn', 'qq', 'office_phone', 'home_phone', 'mobile_phone', 'is_validated', 'credit_line', 'passwd_question', 'passwd_answer', 'user_picture', 'old_user_picture', 'report_time');
	protected $guarded = array();

	public function getUserPictureAttribute()
	{
		return empty($this->attributes['user_picture']) ? 'themes/ecmoban_dsc2017/images/avatar.png' : $this->attributes['user_picture'];
	}

	public function getAiteId()
	{
		return $this->aite_id;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getNickName()
	{
		return $this->nick_name;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getQuestion()
	{
		return $this->question;
	}

	public function getAnswer()
	{
		return $this->answer;
	}

	public function getSex()
	{
		return $this->sex;
	}

	public function getBirthday()
	{
		return $this->birthday;
	}

	public function getUserMoney()
	{
		return $this->user_money;
	}

	public function getFrozenMoney()
	{
		return $this->frozen_money;
	}

	public function getPayPoints()
	{
		return $this->pay_points;
	}

	public function getRankPoints()
	{
		return $this->rank_points;
	}

	public function getAddressId()
	{
		return $this->address_id;
	}

	public function getRegTime()
	{
		return $this->reg_time;
	}

	public function getLastLogin()
	{
		return $this->last_login;
	}

	public function getLastTime()
	{
		return $this->last_time;
	}

	public function getLastIp()
	{
		return $this->last_ip;
	}

	public function getVisitCount()
	{
		return $this->visit_count;
	}

	public function getUserRank()
	{
		return $this->user_rank;
	}

	public function getIsSpecial()
	{
		return $this->is_special;
	}

	public function getEcSalt()
	{
		return $this->ec_salt;
	}

	public function getSalt()
	{
		return $this->salt;
	}

	public function getDrpParentId()
	{
		return $this->drp_parent_id;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getFlag()
	{
		return $this->flag;
	}

	public function getAlias()
	{
		return $this->alias;
	}

	public function getMsn()
	{
		return $this->msn;
	}

	public function getQq()
	{
		return $this->qq;
	}

	public function getOfficePhone()
	{
		return $this->office_phone;
	}

	public function getHomePhone()
	{
		return $this->home_phone;
	}

	public function getMobilePhone()
	{
		return $this->mobile_phone;
	}

	public function getIsValidated()
	{
		return $this->is_validated;
	}

	public function getCreditLine()
	{
		return $this->credit_line;
	}

	public function getPasswdQuestion()
	{
		return $this->passwd_question;
	}

	public function getPasswdAnswer()
	{
		return $this->passwd_answer;
	}

	public function getUserPicture()
	{
		return $this->user_picture;
	}

	public function getOldUserPicture()
	{
		return $this->old_user_picture;
	}

	public function getReportTime()
	{
		return $this->report_time;
	}

	public function setAiteId($value)
	{
		$this->aite_id = $value;
		return $this;
	}

	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}

	public function setUserName($value)
	{
		$this->user_name = $value;
		return $this;
	}

	public function setNickName($value)
	{
		$this->nick_name = $value;
		return $this;
	}

	public function setPassword($value)
	{
		$this->password = $value;
		return $this;
	}

	public function setQuestion($value)
	{
		$this->question = $value;
		return $this;
	}

	public function setAnswer($value)
	{
		$this->answer = $value;
		return $this;
	}

	public function setSex($value)
	{
		$this->sex = $value;
		return $this;
	}

	public function setBirthday($value)
	{
		$this->birthday = $value;
		return $this;
	}

	public function setUserMoney($value)
	{
		$this->user_money = $value;
		return $this;
	}

	public function setFrozenMoney($value)
	{
		$this->frozen_money = $value;
		return $this;
	}

	public function setPayPoints($value)
	{
		$this->pay_points = $value;
		return $this;
	}

	public function setRankPoints($value)
	{
		$this->rank_points = $value;
		return $this;
	}

	public function setAddressId($value)
	{
		$this->address_id = $value;
		return $this;
	}

	public function setRegTime($value)
	{
		$this->reg_time = $value;
		return $this;
	}

	public function setLastLogin($value)
	{
		$this->last_login = $value;
		return $this;
	}

	public function setLastTime($value)
	{
		$this->last_time = $value;
		return $this;
	}

	public function setLastIp($value)
	{
		$this->last_ip = $value;
		return $this;
	}

	public function setVisitCount($value)
	{
		$this->visit_count = $value;
		return $this;
	}

	public function setUserRank($value)
	{
		$this->user_rank = $value;
		return $this;
	}

	public function setIsSpecial($value)
	{
		$this->is_special = $value;
		return $this;
	}

	public function setEcSalt($value)
	{
		$this->ec_salt = $value;
		return $this;
	}

	public function setSalt($value)
	{
		$this->salt = $value;
		return $this;
	}

	public function setDrpParentId($value)
	{
		$this->drp_parent_id = $value;
		return $this;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setFlag($value)
	{
		$this->flag = $value;
		return $this;
	}

	public function setAlias($value)
	{
		$this->alias = $value;
		return $this;
	}

	public function setMsn($value)
	{
		$this->msn = $value;
		return $this;
	}

	public function setQq($value)
	{
		$this->qq = $value;
		return $this;
	}

	public function setOfficePhone($value)
	{
		$this->office_phone = $value;
		return $this;
	}

	public function setHomePhone($value)
	{
		$this->home_phone = $value;
		return $this;
	}

	public function setMobilePhone($value)
	{
		$this->mobile_phone = $value;
		return $this;
	}

	public function setIsValidated($value)
	{
		$this->is_validated = $value;
		return $this;
	}

	public function setCreditLine($value)
	{
		$this->credit_line = $value;
		return $this;
	}

	public function setPasswdQuestion($value)
	{
		$this->passwd_question = $value;
		return $this;
	}

	public function setPasswdAnswer($value)
	{
		$this->passwd_answer = $value;
		return $this;
	}

	public function setUserPicture($value)
	{
		$this->user_picture = $value;
		return $this;
	}

	public function setOldUserPicture($value)
	{
		$this->old_user_picture = $value;
		return $this;
	}

	public function setReportTime($value)
	{
		$this->report_time = $value;
		return $this;
	}
}

?>
