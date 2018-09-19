<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class FavourableActivity extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'favourable_activity';
	protected $primaryKey = 'act_id';
	public $timestamps = false;
	protected $fillable = array('act_name', 'start_time', 'end_time', 'user_rank', 'act_range', 'act_range_ext', 'min_amount', 'max_amount', 'act_type', 'act_type_ext', 'activity_thumb', 'gift', 'sort_order', 'user_id', 'rs_id', 'userFav_type', 'userFav_type_ext', 'review_status', 'review_content', 'user_range_ext', 'is_user_brand');
	protected $guarded = array();

	public function getActName()
	{
		return $this->act_name;
	}

	public function getStartTime()
	{
		return $this->start_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function getUserRank()
	{
		return $this->user_rank;
	}

	public function getActRange()
	{
		return $this->act_range;
	}

	public function getActRangeExt()
	{
		return $this->act_range_ext;
	}

	public function getMinAmount()
	{
		return $this->min_amount;
	}

	public function getMaxAmount()
	{
		return $this->max_amount;
	}

	public function getActType()
	{
		return $this->act_type;
	}

	public function getActTypeExt()
	{
		return $this->act_type_ext;
	}

	public function getActivityThumb()
	{
		return $this->activity_thumb;
	}

	public function getGift()
	{
		return $this->gift;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getRsId()
	{
		return $this->rs_id;
	}

	public function getUserFavType()
	{
		return $this->userFav_type;
	}

	public function getUserFavTypeExt()
	{
		return $this->userFav_type_ext;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewContent()
	{
		return $this->review_content;
	}

	public function getUserRangeExt()
	{
		return $this->user_range_ext;
	}

	public function getIsUserBrand()
	{
		return $this->is_user_brand;
	}

	public function setActName($value)
	{
		$this->act_name = $value;
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

	public function setUserRank($value)
	{
		$this->user_rank = $value;
		return $this;
	}

	public function setActRange($value)
	{
		$this->act_range = $value;
		return $this;
	}

	public function setActRangeExt($value)
	{
		$this->act_range_ext = $value;
		return $this;
	}

	public function setMinAmount($value)
	{
		$this->min_amount = $value;
		return $this;
	}

	public function setMaxAmount($value)
	{
		$this->max_amount = $value;
		return $this;
	}

	public function setActType($value)
	{
		$this->act_type = $value;
		return $this;
	}

	public function setActTypeExt($value)
	{
		$this->act_type_ext = $value;
		return $this;
	}

	public function setActivityThumb($value)
	{
		$this->activity_thumb = $value;
		return $this;
	}

	public function setGift($value)
	{
		$this->gift = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setRsId($value)
	{
		$this->rs_id = $value;
		return $this;
	}

	public function setUserFavType($value)
	{
		$this->userFav_type = $value;
		return $this;
	}

	public function setUserFavTypeExt($value)
	{
		$this->userFav_type_ext = $value;
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

	public function setUserRangeExt($value)
	{
		$this->user_range_ext = $value;
		return $this;
	}

	public function setIsUserBrand($value)
	{
		$this->is_user_brand = $value;
		return $this;
	}
}

?>
