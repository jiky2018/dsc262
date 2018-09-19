<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ComplaintImg extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'complaint_img';
	protected $primaryKey = 'img_id';
	public $timestamps = false;
	protected $fillable = array('order_id', 'complaint_id', 'user_id', 'img_file');
	protected $guarded = array();

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getComplaintId()
	{
		return $this->complaint_id;
	}

	public function getUserId()
	{
		return $this->user_id;
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

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setImgFile($value)
	{
		$this->img_file = $value;
		return $this;
	}
}

?>
