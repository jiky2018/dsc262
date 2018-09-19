<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class IntelligentWeight extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'intelligent_weight';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'goods_number', 'return_number', 'user_number', 'goods_comment_number', 'merchants_comment_number', 'user_attention_number');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getGoodsNumber()
	{
		return $this->goods_number;
	}

	public function getReturnNumber()
	{
		return $this->return_number;
	}

	public function getUserNumber()
	{
		return $this->user_number;
	}

	public function getGoodsCommentNumber()
	{
		return $this->goods_comment_number;
	}

	public function getMerchantsCommentNumber()
	{
		return $this->merchants_comment_number;
	}

	public function getUserAttentionNumber()
	{
		return $this->user_attention_number;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setGoodsNumber($value)
	{
		$this->goods_number = $value;
		return $this;
	}

	public function setReturnNumber($value)
	{
		$this->return_number = $value;
		return $this;
	}

	public function setUserNumber($value)
	{
		$this->user_number = $value;
		return $this;
	}

	public function setGoodsCommentNumber($value)
	{
		$this->goods_comment_number = $value;
		return $this;
	}

	public function setMerchantsCommentNumber($value)
	{
		$this->merchants_comment_number = $value;
		return $this;
	}

	public function setUserAttentionNumber($value)
	{
		$this->user_attention_number = $value;
		return $this;
	}
}

?>
