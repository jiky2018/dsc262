<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class QrpayTag extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'qrpay_tag';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'tag_name', 'self_qrpay_num', 'fixed_qrpay_num', 'add_time');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getTagName()
	{
		return $this->tag_name;
	}

	public function getSelfQrpayNum()
	{
		return $this->self_qrpay_num;
	}

	public function getFixedQrpayNum()
	{
		return $this->fixed_qrpay_num;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setTagName($value)
	{
		$this->tag_name = $value;
		return $this;
	}

	public function setSelfQrpayNum($value)
	{
		$this->self_qrpay_num = $value;
		return $this;
	}

	public function setFixedQrpayNum($value)
	{
		$this->fixed_qrpay_num = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
