<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Single extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'single';
	protected $primaryKey = 'single_id';
	public $timestamps = false;
	protected $fillable = array('order_id', 'single_name', 'single_description', 'single_like', 'user_name', 'is_audit', 'order_sn', 'addtime', 'goods_name', 'goods_id', 'user_id', 'order_time', 'comment_id', 'single_ip', 'cat_id', 'integ', 'single_browse_num', 'cover');
	protected $guarded = array();

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getSingleName()
	{
		return $this->single_name;
	}

	public function getSingleDescription()
	{
		return $this->single_description;
	}

	public function getSingleLike()
	{
		return $this->single_like;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getIsAudit()
	{
		return $this->is_audit;
	}

	public function getOrderSn()
	{
		return $this->order_sn;
	}

	public function getAddtime()
	{
		return $this->addtime;
	}

	public function getGoodsName()
	{
		return $this->goods_name;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getOrderTime()
	{
		return $this->order_time;
	}

	public function getCommentId()
	{
		return $this->comment_id;
	}

	public function getSingleIp()
	{
		return $this->single_ip;
	}

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getInteg()
	{
		return $this->integ;
	}

	public function getSingleBrowseNum()
	{
		return $this->single_browse_num;
	}

	public function getCover()
	{
		return $this->cover;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setSingleName($value)
	{
		$this->single_name = $value;
		return $this;
	}

	public function setSingleDescription($value)
	{
		$this->single_description = $value;
		return $this;
	}

	public function setSingleLike($value)
	{
		$this->single_like = $value;
		return $this;
	}

	public function setUserName($value)
	{
		$this->user_name = $value;
		return $this;
	}

	public function setIsAudit($value)
	{
		$this->is_audit = $value;
		return $this;
	}

	public function setOrderSn($value)
	{
		$this->order_sn = $value;
		return $this;
	}

	public function setAddtime($value)
	{
		$this->addtime = $value;
		return $this;
	}

	public function setGoodsName($value)
	{
		$this->goods_name = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setOrderTime($value)
	{
		$this->order_time = $value;
		return $this;
	}

	public function setCommentId($value)
	{
		$this->comment_id = $value;
		return $this;
	}

	public function setSingleIp($value)
	{
		$this->single_ip = $value;
		return $this;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setInteg($value)
	{
		$this->integ = $value;
		return $this;
	}

	public function setSingleBrowseNum($value)
	{
		$this->single_browse_num = $value;
		return $this;
	}

	public function setCover($value)
	{
		$this->cover = $value;
		return $this;
	}
}

?>
