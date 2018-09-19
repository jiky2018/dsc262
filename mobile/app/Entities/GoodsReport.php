<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class GoodsReport extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_report';
	protected $primaryKey = 'report_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'user_name', 'goods_id', 'goods_name', 'goods_image', 'title_id', 'type_id', 'inform_content', 'add_time', 'report_state', 'handle_type', 'handle_message', 'handle_time', 'admin_id');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getGoodsName()
	{
		return $this->goods_name;
	}

	public function getGoodsImage()
	{
		return $this->goods_image;
	}

	public function getTitleId()
	{
		return $this->title_id;
	}

	public function getTypeId()
	{
		return $this->type_id;
	}

	public function getInformContent()
	{
		return $this->inform_content;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getReportState()
	{
		return $this->report_state;
	}

	public function getHandleType()
	{
		return $this->handle_type;
	}

	public function getHandleMessage()
	{
		return $this->handle_message;
	}

	public function getHandleTime()
	{
		return $this->handle_time;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setUserName($value)
	{
		$this->user_name = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setGoodsName($value)
	{
		$this->goods_name = $value;
		return $this;
	}

	public function setGoodsImage($value)
	{
		$this->goods_image = $value;
		return $this;
	}

	public function setTitleId($value)
	{
		$this->title_id = $value;
		return $this;
	}

	public function setTypeId($value)
	{
		$this->type_id = $value;
		return $this;
	}

	public function setInformContent($value)
	{
		$this->inform_content = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setReportState($value)
	{
		$this->report_state = $value;
		return $this;
	}

	public function setHandleType($value)
	{
		$this->handle_type = $value;
		return $this;
	}

	public function setHandleMessage($value)
	{
		$this->handle_message = $value;
		return $this;
	}

	public function setHandleTime($value)
	{
		$this->handle_time = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}
}

?>
