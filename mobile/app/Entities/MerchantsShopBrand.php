<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsShopBrand extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_shop_brand';
	protected $primaryKey = 'bid';
	public $timestamps = false;
	protected $fillable = array('user_id', 'bank_name_letter', 'brandName', 'brandFirstChar', 'brandLogo', 'brandType', 'brand_operateType', 'brandEndTime', 'brandEndTime_permanent', 'site_url', 'brand_desc', 'sort_order', 'is_show', 'is_delete', 'major_business', 'audit_status', 'add_time');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getBankNameLetter()
	{
		return $this->bank_name_letter;
	}

	public function getBrandName()
	{
		return $this->brandName;
	}

	public function getBrandFirstChar()
	{
		return $this->brandFirstChar;
	}

	public function getBrandLogo()
	{
		return $this->brandLogo;
	}

	public function getBrandType()
	{
		return $this->brandType;
	}

	public function getBrandOperateType()
	{
		return $this->brand_operateType;
	}

	public function getBrandEndTime()
	{
		return $this->brandEndTime;
	}

	public function getBrandEndTimePermanent()
	{
		return $this->brandEndTime_permanent;
	}

	public function getSiteUrl()
	{
		return $this->site_url;
	}

	public function getBrandDesc()
	{
		return $this->brand_desc;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function getIsDelete()
	{
		return $this->is_delete;
	}

	public function getMajorBusiness()
	{
		return $this->major_business;
	}

	public function getAuditStatus()
	{
		return $this->audit_status;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setBankNameLetter($value)
	{
		$this->bank_name_letter = $value;
		return $this;
	}

	public function setBrandName($value)
	{
		$this->brandName = $value;
		return $this;
	}

	public function setBrandFirstChar($value)
	{
		$this->brandFirstChar = $value;
		return $this;
	}

	public function setBrandLogo($value)
	{
		$this->brandLogo = $value;
		return $this;
	}

	public function setBrandType($value)
	{
		$this->brandType = $value;
		return $this;
	}

	public function setBrandOperateType($value)
	{
		$this->brand_operateType = $value;
		return $this;
	}

	public function setBrandEndTime($value)
	{
		$this->brandEndTime = $value;
		return $this;
	}

	public function setBrandEndTimePermanent($value)
	{
		$this->brandEndTime_permanent = $value;
		return $this;
	}

	public function setSiteUrl($value)
	{
		$this->site_url = $value;
		return $this;
	}

	public function setBrandDesc($value)
	{
		$this->brand_desc = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}

	public function setIsDelete($value)
	{
		$this->is_delete = $value;
		return $this;
	}

	public function setMajorBusiness($value)
	{
		$this->major_business = $value;
		return $this;
	}

	public function setAuditStatus($value)
	{
		$this->audit_status = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
