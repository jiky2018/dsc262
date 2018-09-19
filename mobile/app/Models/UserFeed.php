<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class UserFeed extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'user_feed';
	protected $primaryKey = 'feed_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'value_id', 'goods_id', 'feed_type', 'is_feed');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getValueId()
	{
		return $this->value_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getFeedType()
	{
		return $this->feed_type;
	}

	public function getIsFeed()
	{
		return $this->is_feed;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setValueId($value)
	{
		$this->value_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setFeedType($value)
	{
		$this->feed_type = $value;
		return $this;
	}

	public function setIsFeed($value)
	{
		$this->is_feed = $value;
		return $this;
	}
}

?>
