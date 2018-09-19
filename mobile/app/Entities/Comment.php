<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Comment extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'comment';
	protected $primaryKey = 'comment_id';
	public $timestamps = false;
	protected $fillable = array('comment_type', 'id_value', 'email', 'user_name', 'content', 'comment_rank', 'comment_server', 'comment_delivery', 'add_time', 'ip_address', 'status', 'parent_id', 'user_id', 'ru_id', 'single_id', 'order_id', 'rec_id', 'goods_tag', 'useful', 'useful_user', 'use_ip', 'dis_id', 'like_num', 'dis_browse_num');
	protected $guarded = array();

	public function getCommentType()
	{
		return $this->comment_type;
	}

	public function getIdValue()
	{
		return $this->id_value;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getUserName()
	{
		return $this->user_name;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getCommentRank()
	{
		return $this->comment_rank;
	}

	public function getCommentServer()
	{
		return $this->comment_server;
	}

	public function getCommentDelivery()
	{
		return $this->comment_delivery;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getIpAddress()
	{
		return $this->ip_address;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getSingleId()
	{
		return $this->single_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getRecId()
	{
		return $this->rec_id;
	}

	public function getGoodsTag()
	{
		return $this->goods_tag;
	}

	public function getUseful()
	{
		return $this->useful;
	}

	public function getUsefulUser()
	{
		return $this->useful_user;
	}

	public function getUseIp()
	{
		return $this->use_ip;
	}

	public function getDisId()
	{
		return $this->dis_id;
	}

	public function getLikeNum()
	{
		return $this->like_num;
	}

	public function getDisBrowseNum()
	{
		return $this->dis_browse_num;
	}

	public function setCommentType($value)
	{
		$this->comment_type = $value;
		return $this;
	}

	public function setIdValue($value)
	{
		$this->id_value = $value;
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

	public function setContent($value)
	{
		$this->content = $value;
		return $this;
	}

	public function setCommentRank($value)
	{
		$this->comment_rank = $value;
		return $this;
	}

	public function setCommentServer($value)
	{
		$this->comment_server = $value;
		return $this;
	}

	public function setCommentDelivery($value)
	{
		$this->comment_delivery = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setIpAddress($value)
	{
		$this->ip_address = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setSingleId($value)
	{
		$this->single_id = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setRecId($value)
	{
		$this->rec_id = $value;
		return $this;
	}

	public function setGoodsTag($value)
	{
		$this->goods_tag = $value;
		return $this;
	}

	public function setUseful($value)
	{
		$this->useful = $value;
		return $this;
	}

	public function setUsefulUser($value)
	{
		$this->useful_user = $value;
		return $this;
	}

	public function setUseIp($value)
	{
		$this->use_ip = $value;
		return $this;
	}

	public function setDisId($value)
	{
		$this->dis_id = $value;
		return $this;
	}

	public function setLikeNum($value)
	{
		$this->like_num = $value;
		return $this;
	}

	public function setDisBrowseNum($value)
	{
		$this->dis_browse_num = $value;
		return $this;
	}
}

?>
