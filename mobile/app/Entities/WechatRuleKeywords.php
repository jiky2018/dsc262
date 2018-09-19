<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class WechatRuleKeywords extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'wechat_rule_keywords';
	public $timestamps = false;
	protected $fillable = array('wechat_id', 'rid', 'rule_keywords');
	protected $guarded = array();

	public function getWechatId()
	{
		return $this->wechat_id;
	}

	public function getRid()
	{
		return $this->rid;
	}

	public function getRuleKeywords()
	{
		return $this->rule_keywords;
	}

	public function setWechatId($value)
	{
		$this->wechat_id = $value;
		return $this;
	}

	public function setRid($value)
	{
		$this->rid = $value;
		return $this;
	}

	public function setRuleKeywords($value)
	{
		$this->rule_keywords = $value;
		return $this;
	}
}

?>
