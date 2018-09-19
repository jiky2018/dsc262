<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsShopInformation extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_shop_information';
	protected $primaryKey = 'shop_id';
	public $timestamps = false;
	protected $fillable = array('user_id', 'region_id', 'shoprz_type', 'subShoprz_type', 'shop_expireDateStart', 'shop_expireDateEnd', 'shop_permanent', 'authorizeFile', 'shop_hypermarketFile', 'shop_categoryMain', 'user_shopMain_category', 'shoprz_brandName', 'shop_class_keyWords', 'shopNameSuffix', 'rz_shopName', 'hopeLoginName', 'merchants_message', 'allow_number', 'steps_audit', 'merchants_audit', 'review_goods', 'sort_order', 'store_score', 'is_street', 'is_IM', 'self_run', 'shop_close');
	protected $guarded = array();

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getRegionId()
	{
		return $this->region_id;
	}

	public function getShoprzType()
	{
		return $this->shoprz_type;
	}

	public function getSubShoprzType()
	{
		return $this->subShoprz_type;
	}

	public function getShopExpireDateStart()
	{
		return $this->shop_expireDateStart;
	}

	public function getShopExpireDateEnd()
	{
		return $this->shop_expireDateEnd;
	}

	public function getShopPermanent()
	{
		return $this->shop_permanent;
	}

	public function getAuthorizeFile()
	{
		return $this->authorizeFile;
	}

	public function getShopHypermarketFile()
	{
		return $this->shop_hypermarketFile;
	}

	public function getShopCategoryMain()
	{
		return $this->shop_categoryMain;
	}

	public function getUserShopMainCategory()
	{
		return $this->user_shopMain_category;
	}

	public function getShoprzBrandName()
	{
		return $this->shoprz_brandName;
	}

	public function getShopClassKeyWords()
	{
		return $this->shop_class_keyWords;
	}

	public function getShopNameSuffix()
	{
		return $this->shopNameSuffix;
	}

	public function getRzShopName()
	{
		return $this->rz_shopName;
	}

	public function getHopeLoginName()
	{
		return $this->hopeLoginName;
	}

	public function getMerchantsMessage()
	{
		return $this->merchants_message;
	}

	public function getAllowNumber()
	{
		return $this->allow_number;
	}

	public function getStepsAudit()
	{
		return $this->steps_audit;
	}

	public function getMerchantsAudit()
	{
		return $this->merchants_audit;
	}

	public function getReviewGoods()
	{
		return $this->review_goods;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getStoreScore()
	{
		return $this->store_score;
	}

	public function getIsStreet()
	{
		return $this->is_street;
	}

	public function getIsIM()
	{
		return $this->is_IM;
	}

	public function getSelfRun()
	{
		return $this->self_run;
	}

	public function getShopClose()
	{
		return $this->shop_close;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setRegionId($value)
	{
		$this->region_id = $value;
		return $this;
	}

	public function setShoprzType($value)
	{
		$this->shoprz_type = $value;
		return $this;
	}

	public function setSubShoprzType($value)
	{
		$this->subShoprz_type = $value;
		return $this;
	}

	public function setShopExpireDateStart($value)
	{
		$this->shop_expireDateStart = $value;
		return $this;
	}

	public function setShopExpireDateEnd($value)
	{
		$this->shop_expireDateEnd = $value;
		return $this;
	}

	public function setShopPermanent($value)
	{
		$this->shop_permanent = $value;
		return $this;
	}

	public function setAuthorizeFile($value)
	{
		$this->authorizeFile = $value;
		return $this;
	}

	public function setShopHypermarketFile($value)
	{
		$this->shop_hypermarketFile = $value;
		return $this;
	}

	public function setShopCategoryMain($value)
	{
		$this->shop_categoryMain = $value;
		return $this;
	}

	public function setUserShopMainCategory($value)
	{
		$this->user_shopMain_category = $value;
		return $this;
	}

	public function setShoprzBrandName($value)
	{
		$this->shoprz_brandName = $value;
		return $this;
	}

	public function setShopClassKeyWords($value)
	{
		$this->shop_class_keyWords = $value;
		return $this;
	}

	public function setShopNameSuffix($value)
	{
		$this->shopNameSuffix = $value;
		return $this;
	}

	public function setRzShopName($value)
	{
		$this->rz_shopName = $value;
		return $this;
	}

	public function setHopeLoginName($value)
	{
		$this->hopeLoginName = $value;
		return $this;
	}

	public function setMerchantsMessage($value)
	{
		$this->merchants_message = $value;
		return $this;
	}

	public function setAllowNumber($value)
	{
		$this->allow_number = $value;
		return $this;
	}

	public function setStepsAudit($value)
	{
		$this->steps_audit = $value;
		return $this;
	}

	public function setMerchantsAudit($value)
	{
		$this->merchants_audit = $value;
		return $this;
	}

	public function setReviewGoods($value)
	{
		$this->review_goods = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setStoreScore($value)
	{
		$this->store_score = $value;
		return $this;
	}

	public function setIsStreet($value)
	{
		$this->is_street = $value;
		return $this;
	}

	public function setIsIM($value)
	{
		$this->is_IM = $value;
		return $this;
	}

	public function setSelfRun($value)
	{
		$this->self_run = $value;
		return $this;
	}

	public function setShopClose($value)
	{
		$this->shop_close = $value;
		return $this;
	}
}

?>
