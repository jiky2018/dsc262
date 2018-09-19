<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class ZcGoods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'zc_goods';
	public $timestamps = false;
	protected $fillable = array('pid', 'limit', 'backer_num', 'price', 'shipping_fee', 'content', 'img', 'return_time', 'backer_list');
	protected $guarded = array();

	public function getPid()
	{
		return $this->pid;
	}

	public function getLimit()
	{
		return $this->limit;
	}

	public function getBackerNum()
	{
		return $this->backer_num;
	}

	public function getPrice()
	{
		return $this->price;
	}

	public function getShippingFee()
	{
		return $this->shipping_fee;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getImg()
	{
		return $this->img;
	}

	public function getReturnTime()
	{
		return $this->return_time;
	}

	public function getBackerList()
	{
		return $this->backer_list;
	}

	public function setPid($value)
	{
		$this->pid = $value;
		return $this;
	}

	public function setLimit($value)
	{
		$this->limit = $value;
		return $this;
	}

	public function setBackerNum($value)
	{
		$this->backer_num = $value;
		return $this;
	}

	public function setPrice($value)
	{
		$this->price = $value;
		return $this;
	}

	public function setShippingFee($value)
	{
		$this->shipping_fee = $value;
		return $this;
	}

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setImg($value)
	{
		$this->img = $value;
		return $this;
	}

	public function setReturnTime($value)
	{
		$this->return_time = $value;
		return $this;
	}

	public function setBackerList($value)
	{
		$this->backer_list = $value;
		return $this;
	}
}

?>
