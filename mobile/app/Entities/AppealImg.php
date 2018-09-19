<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class AppealImg extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'appeal_img';
	protected $primaryKey = 'img_id';
	public $timestamps = false;
	protected $fillable = array('order_id', 'complaint_id', 'ru_id', 'img_file');
	protected $guarded = array();

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getComplaintId()
	{
		return $this->complaint_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getImgFile()
	{
		return $this->img_file;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setComplaintId($value)
	{
		$this->complaint_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setImgFile($value)
	{
		$this->img_file = $value;
		return $this;
	}
}

?>
