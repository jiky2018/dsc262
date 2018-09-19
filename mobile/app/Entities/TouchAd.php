<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class TouchAd extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'touch_ad';
	protected $primaryKey = 'ad_id';
	public $timestamps = false;
	protected $fillable = array('position_id', 'media_type', 'ad_name', 'ad_link', 'link_color', 'ad_code', 'start_time', 'end_time', 'link_man', 'link_email', 'link_phone', 'click_count', 'enabled', 'is_new', 'is_hot', 'is_best', 'public_ruid', 'ad_type', 'goods_name');
	protected $guarded = array();

	public function getPositionId()
	{
		return $this->position_id;
	}

	public function getMediaType()
	{
		return $this->media_type;
	}

	public function getAdName()
	{
		return $this->ad_name;
	}

	public function getAdLink()
	{
		return $this->ad_link;
	}

	public function getLinkColor()
	{
		return $this->link_color;
	}

	public function getAdCode()
	{
		return $this->ad_code;
	}

	public function getStartTime()
	{
		return $this->start_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function getLinkMan()
	{
		return $this->link_man;
	}

	public function getLinkEmail()
	{
		return $this->link_email;
	}

	public function getLinkPhone()
	{
		return $this->link_phone;
	}

	public function getClickCount()
	{
		return $this->click_count;
	}

	public function getEnabled()
	{
		return $this->enabled;
	}

	public function getIsNew()
	{
		return $this->is_new;
	}

	public function getIsHot()
	{
		return $this->is_hot;
	}

	public function getIsBest()
	{
		return $this->is_best;
	}

	public function getPublicRuid()
	{
		return $this->public_ruid;
	}

	public function getAdType()
	{
		return $this->ad_type;
	}

	public function getGoodsName()
	{
		return $this->goods_name;
	}

	public function setPositionId($value)
	{
		$this->position_id = $value;
		return $this;
	}

	public function setMediaType($value)
	{
		$this->media_type = $value;
		return $this;
	}

	public function setAdName($value)
	{
		$this->ad_name = $value;
		return $this;
	}

	public function setAdLink($value)
	{
		$this->ad_link = $value;
		return $this;
	}

	public function setLinkColor($value)
	{
		$this->link_color = $value;
		return $this;
	}

	public function setAdCode($value)
	{
		$this->ad_code = $value;
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

	public function setLinkMan($value)
	{
		$this->link_man = $value;
		return $this;
	}

	public function setLinkEmail($value)
	{
		$this->link_email = $value;
		return $this;
	}

	public function setLinkPhone($value)
	{
		$this->link_phone = $value;
		return $this;
	}

	public function setClickCount($value)
	{
		$this->click_count = $value;
		return $this;
	}

	public function setEnabled($value)
	{
		$this->enabled = $value;
		return $this;
	}

	public function setIsNew($value)
	{
		$this->is_new = $value;
		return $this;
	}

	public function setIsHot($value)
	{
		$this->is_hot = $value;
		return $this;
	}

	public function setIsBest($value)
	{
		$this->is_best = $value;
		return $this;
	}

	public function setPublicRuid($value)
	{
		$this->public_ruid = $value;
		return $this;
	}

	public function setAdType($value)
	{
		$this->ad_type = $value;
		return $this;
	}

	public function setGoodsName($value)
	{
		$this->goods_name = $value;
		return $this;
	}
}

?>
