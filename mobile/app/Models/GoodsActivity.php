<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class GoodsActivity extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_activity';
	protected $primaryKey = 'act_id';
	public $timestamps = false;
	protected $fillable = array('act_name', 'user_id', 'act_desc', 'activity_thumb', 'act_promise', 'act_ensure', 'act_type', 'goods_id', 'product_id', 'goods_name', 'start_time', 'end_time', 'is_finished', 'ext_info', 'is_hot', 'review_status', 'review_content', 'is_new');
	protected $guarded = array();

	public function getActName()
	{
		return $this->act_name;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getActDesc()
	{
		return $this->act_desc;
	}

	public function getActivityThumb()
	{
		return $this->activity_thumb;
	}

	public function getActPromise()
	{
		return $this->act_promise;
	}

	public function getActEnsure()
	{
		return $this->act_ensure;
	}

	public function getActType()
	{
		return $this->act_type;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getProductId()
	{
		return $this->product_id;
	}

	public function getGoodsName()
	{
		return $this->goods_name;
	}

	public function getStartTime()
	{
		return $this->start_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function getIsFinished()
	{
		return $this->is_finished;
	}

	public function getExtInfo()
	{
		return $this->ext_info;
	}

	public function getIsHot()
	{
		return $this->is_hot;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewContent()
	{
		return $this->review_content;
	}

	public function getIsNew()
	{
		return $this->is_new;
	}

	public function setActName($value)
	{
		$this->act_name = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setActDesc($value)
	{
		$this->act_desc = $value;
		return $this;
	}

	public function setActivityThumb($value)
	{
		$this->activity_thumb = $value;
		return $this;
	}

	public function setActPromise($value)
	{
		$this->act_promise = $value;
		return $this;
	}

	public function setActEnsure($value)
	{
		$this->act_ensure = $value;
		return $this;
	}

	public function setActType($value)
	{
		$this->act_type = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setProductId($value)
	{
		$this->product_id = $value;
		return $this;
	}

	public function setGoodsName($value)
	{
		$this->goods_name = $value;
		return $this;
	}

	public function setStartTime($value)
	{
		$this->start_time = $value;
		return $this;
	}

	public function setEndTime($value)
	{
		$this->end_time = $value;
		return $this;
	}

	public function setIsFinished($value)
	{
		$this->is_finished = $value;
		return $this;
	}

	public function setExtInfo($value)
	{
		$this->ext_info = $value;
		return $this;
	}

	public function setIsHot($value)
	{
		$this->is_hot = $value;
		return $this;
	}

	public function setReviewStatus($value)
	{
		$this->review_status = $value;
		return $this;
	}

	public function setReviewContent($value)
	{
		$this->review_content = $value;
		return $this;
	}

	public function setIsNew($value)
	{
		$this->is_new = $value;
		return $this;
	}
}

?>
