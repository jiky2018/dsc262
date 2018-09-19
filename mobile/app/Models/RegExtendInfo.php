<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class RegExtendInfo extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'reg_extend_info';
	protected $primaryKey = 'Id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'reg_field_id', 'content');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getRegFieldId()
	{
		return $this->reg_field_id;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setRegFieldId($value)
	{
		$this->reg_field_id = $value;
		return $this;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}
}

?>
