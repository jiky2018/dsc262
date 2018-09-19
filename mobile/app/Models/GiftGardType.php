<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class GiftGardType extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'gift_gard_type';
	protected $primaryKey = 'gift_id';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'gift_name', 'gift_menory', 'gift_min_menory', 'gift_start_date', 'gift_end_date', 'gift_number', 'review_status', 'review_content');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getGiftName()
	{
		return $this->gift_name;
	}

	public function getGiftMenory()
	{
		return $this->gift_menory;
	}

	public function getGiftMinMenory()
	{
		return $this->gift_min_menory;
	}

	public function getGiftStartDate()
	{
		return $this->gift_start_date;
	}

	public function getGiftEndDate()
	{
		return $this->gift_end_date;
	}

	public function getGiftNumber()
	{
		return $this->gift_number;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewContent()
	{
		return $this->review_content;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setGiftName($value)
	{
		$this->gift_name = $value;
		return $this;
	}

	public function setGiftMenory($value)
	{
		$this->gift_menory = $value;
		return $this;
	}

	public function setGiftMinMenory($value)
	{
		$this->gift_min_menory = $value;
		return $this;
	}

	public function setGiftStartDate($value)
	{
		$this->gift_start_date = $value;
		return $this;
	}

	public function setGiftEndDate($value)
	{
		$this->gift_end_date = $value;
		return $this;
	}

	public function setGiftNumber($value)
	{
		$this->gift_number = $value;
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
}

?>
