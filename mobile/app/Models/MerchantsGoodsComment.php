<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class MerchantsGoodsComment extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_goods_comment';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'comment_start', 'comment_end', 'comment_last_percent');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getCommentStart()
	{
		return $this->comment_start;
	}

	public function getCommentEnd()
	{
		return $this->comment_end;
	}

	public function getCommentLastPercent()
	{
		return $this->comment_last_percent;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setCommentStart($value)
	{
		$this->comment_start = $value;
		return $this;
	}

	public function setCommentEnd($value)
	{
		$this->comment_end = $value;
		return $this;
	}

	public function setCommentLastPercent($value)
	{
		$this->comment_last_percent = $value;
		return $this;
	}
}

?>
