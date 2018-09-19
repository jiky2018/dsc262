<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Tag extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'tag';
	protected $primaryKey = 'tag_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'goods_id', 'tag_words');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getTagWords()
	{
		return $this->tag_words;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setTagWords($value)
	{
		$this->tag_words = $value;
		return $this;
	}
}

?>
