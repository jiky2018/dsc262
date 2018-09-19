<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class SellerShopinfo extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_shopinfo';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'shop_name', 'shop_title', 'shop_keyword', 'country', 'province', 'city', 'district', 'shop_address', 'seller_email', 'kf_qq', 'kf_ww', 'meiqia', 'kf_type', 'kf_tel', 'site_head', 'mobile', 'shop_logo', 'logo_thumb', 'street_thumb', 'brand_thumb', 'notice', 'street_desc', 'shop_header', 'shop_color', 'shop_style', 'shop_close', 'apply', 'is_street', 'remark', 'seller_theme', 'win_goods_type', 'store_style', 'check_sellername', 'shopname_audit', 'shipping_id', 'shipping_date', 'longitude', 'tengxun_key', 'latitude', 'kf_appkey', 'kf_touid', 'kf_logo', 'kf_welcomeMsg', 'kf_secretkey', 'user_menu', 'kf_im_switch', 'seller_money', 'frozen_money', 'credit_money', 'seller_templates', 'templates_mode', 'js_appkey', 'js_appsecret', 'print_type', 'kdniao_printer', 'business_practice', 'review_status', 'review_content');
	protected $guarded = array();

	public function MerchantsShopInformation()
	{
		return self::belongsTo('App\\Models\\MerchantsShopInformation', 'ru_id', 'user_id');
	}

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getShopName()
	{
		return $this->shop_name;
	}

	public function getShopTitle()
	{
		return $this->shop_title;
	}

	public function getShopKeyword()
	{
		return $this->shop_keyword;
	}

	public function getCountry()
	{
		return $this->country;
	}

	public function getProvince()
	{
		return $this->province;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function getDistrict()
	{
		return $this->district;
	}

	public function getShopAddress()
	{
		return $this->shop_address;
	}

	public function getSellerEmail()
	{
		return $this->seller_email;
	}

	public function getKfQq()
	{
		return $this->kf_qq;
	}

	public function getKfWw()
	{
		return $this->kf_ww;
	}

	public function getMeiqia()
	{
		return $this->meiqia;
	}

	public function getKfType()
	{
		return $this->kf_type;
	}

	public function getKfTel()
	{
		return $this->kf_tel;
	}

	public function getSiteHead()
	{
		return $this->site_head;
	}

	public function getMobile()
	{
		return $this->mobile;
	}

	public function getShopLogo()
	{
		return $this->shop_logo;
	}

	public function getLogoThumb()
	{
		return $this->logo_thumb;
	}

	public function getStreetThumb()
	{
		return $this->street_thumb;
	}

	public function getBrandThumb()
	{
		return $this->brand_thumb;
	}

	public function getNotice()
	{
		return $this->notice;
	}

	public function getStreetDesc()
	{
		return $this->street_desc;
	}

	public function getShopHeader()
	{
		return $this->shop_header;
	}

	public function getShopColor()
	{
		return $this->shop_color;
	}

	public function getShopStyle()
	{
		return $this->shop_style;
	}

	public function getShopClose()
	{
		return $this->shop_close;
	}

	public function getApply()
	{
		return $this->apply;
	}

	public function getIsStreet()
	{
		return $this->is_street;
	}

	public function getRemark()
	{
		return $this->remark;
	}

	public function getSellerTheme()
	{
		return $this->seller_theme;
	}

	public function getWinGoodsType()
	{
		return $this->win_goods_type;
	}

	public function getStoreStyle()
	{
		return $this->store_style;
	}

	public function getCheckSellername()
	{
		return $this->check_sellername;
	}

	public function getShopnameAudit()
	{
		return $this->shopname_audit;
	}

	public function getShippingId()
	{
		return $this->shipping_id;
	}

	public function getShippingDate()
	{
		return $this->shipping_date;
	}

	public function getLongitude()
	{
		return $this->longitude;
	}

	public function getTengxunKey()
	{
		return $this->tengxun_key;
	}

	public function getLatitude()
	{
		return $this->latitude;
	}

	public function getKfAppkey()
	{
		return $this->kf_appkey;
	}

	public function getKfTouid()
	{
		return $this->kf_touid;
	}

	public function getKfLogo()
	{
		return $this->kf_logo;
	}

	public function getKfWelcomeMsg()
	{
		return $this->kf_welcomeMsg;
	}

	public function getKfSecretkey()
	{
		return $this->kf_secretkey;
	}

	public function getUserMenu()
	{
		return $this->user_menu;
	}

	public function getKfImSwitch()
	{
		return $this->kf_im_switch;
	}

	public function getSellerMoney()
	{
		return $this->seller_money;
	}

	public function getFrozenMoney()
	{
		return $this->frozen_money;
	}

	public function getCreditMoney()
	{
		return $this->credit_money;
	}

	public function getSellerTemplates()
	{
		return $this->seller_templates;
	}

	public function getTemplatesMode()
	{
		return $this->templates_mode;
	}

	public function getJsAppkey()
	{
		return $this->js_appkey;
	}

	public function getJsAppsecret()
	{
		return $this->js_appsecret;
	}

	public function getPrintType()
	{
		return $this->print_type;
	}

	public function getKdniaoPrinter()
	{
		return $this->kdniao_printer;
	}

	public function getBusinessPractice()
	{
		return $this->business_practice;
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

	public function setShopName($value)
	{
		$this->shop_name = $value;
		return $this;
	}

	public function setShopTitle($value)
	{
		$this->shop_title = $value;
		return $this;
	}

	public function setShopKeyword($value)
	{
		$this->shop_keyword = $value;
		return $this;
	}

	public function setCountry($value)
	{
		$this->country = $value;
		return $this;
	}

	public function setProvince($value)
	{
		$this->province = $value;
		return $this;
	}

	public function setCity($value)
	{
		$this->city = $value;
		return $this;
	}

	public function setDistrict($value)
	{
		$this->district = $value;
		return $this;
	}

	public function setShopAddress($value)
	{
		$this->shop_address = $value;
		return $this;
	}

	public function setSellerEmail($value)
	{
		$this->seller_email = $value;
		return $this;
	}

	public function setKfQq($value)
	{
		$this->kf_qq = $value;
		return $this;
	}

	public function setKfWw($value)
	{
		$this->kf_ww = $value;
		return $this;
	}

	public function setMeiqia($value)
	{
		$this->meiqia = $value;
		return $this;
	}

	public function setKfType($value)
	{
		$this->kf_type = $value;
		return $this;
	}

	public function setKfTel($value)
	{
		$this->kf_tel = $value;
		return $this;
	}

	public function setSiteHead($value)
	{
		$this->site_head = $value;
		return $this;
	}

	public function setMobile($value)
	{
		$this->mobile = $value;
		return $this;
	}

	public function setShopLogo($value)
	{
		$this->shop_logo = $value;
		return $this;
	}

	public function setLogoThumb($value)
	{
		$this->logo_thumb = $value;
		return $this;
	}

	public function setStreetThumb($value)
	{
		$this->street_thumb = $value;
		return $this;
	}

	public function setBrandThumb($value)
	{
		$this->brand_thumb = $value;
		return $this;
	}

	public function setNotice($value)
	{
		$this->notice = $value;
		return $this;
	}

	public function setStreetDesc($value)
	{
		$this->street_desc = $value;
		return $this;
	}

	public function setShopHeader($value)
	{
		$this->shop_header = $value;
		return $this;
	}

	public function setShopColor($value)
	{
		$this->shop_color = $value;
		return $this;
	}

	public function setShopStyle($value)
	{
		$this->shop_style = $value;
		return $this;
	}

	public function setShopClose($value)
	{
		$this->shop_close = $value;
		return $this;
	}

	public function setApply($value)
	{
		$this->apply = $value;
		return $this;
	}

	public function setIsStreet($value)
	{
		$this->is_street = $value;
		return $this;
	}

	public function setRemark($value)
	{
		$this->remark = $value;
		return $this;
	}

	public function setSellerTheme($value)
	{
		$this->seller_theme = $value;
		return $this;
	}

	public function setWinGoodsType($value)
	{
		$this->win_goods_type = $value;
		return $this;
	}

	public function setStoreStyle($value)
	{
		$this->store_style = $value;
		return $this;
	}

	public function setCheckSellername($value)
	{
		$this->check_sellername = $value;
		return $this;
	}

	public function setShopnameAudit($value)
	{
		$this->shopname_audit = $value;
		return $this;
	}

	public function setShippingId($value)
	{
		$this->shipping_id = $value;
		return $this;
	}

	public function setShippingDate($value)
	{
		$this->shipping_date = $value;
		return $this;
	}

	public function setLongitude($value)
	{
		$this->longitude = $value;
		return $this;
	}

	public function setTengxunKey($value)
	{
		$this->tengxun_key = $value;
		return $this;
	}

	public function setLatitude($value)
	{
		$this->latitude = $value;
		return $this;
	}

	public function setKfAppkey($value)
	{
		$this->kf_appkey = $value;
		return $this;
	}

	public function setKfTouid($value)
	{
		$this->kf_touid = $value;
		return $this;
	}

	public function setKfLogo($value)
	{
		$this->kf_logo = $value;
		return $this;
	}

	public function setKfWelcomeMsg($value)
	{
		$this->kf_welcomeMsg = $value;
		return $this;
	}

	public function setKfSecretkey($value)
	{
		$this->kf_secretkey = $value;
		return $this;
	}

	public function setUserMenu($value)
	{
		$this->user_menu = $value;
		return $this;
	}

	public function setKfImSwitch($value)
	{
		$this->kf_im_switch = $value;
		return $this;
	}

	public function setSellerMoney($value)
	{
		$this->seller_money = $value;
		return $this;
	}

	public function setFrozenMoney($value)
	{
		$this->frozen_money = $value;
		return $this;
	}

	public function setCreditMoney($value)
	{
		$this->credit_money = $value;
		return $this;
	}

	public function setSellerTemplates($value)
	{
		$this->seller_templates = $value;
		return $this;
	}

	public function setTemplatesMode($value)
	{
		$this->templates_mode = $value;
		return $this;
	}

	public function setJsAppkey($value)
	{
		$this->js_appkey = $value;
		return $this;
	}

	public function setJsAppsecret($value)
	{
		$this->js_appsecret = $value;
		return $this;
	}

	public function setPrintType($value)
	{
		$this->print_type = $value;
		return $this;
	}

	public function setKdniaoPrinter($value)
	{
		$this->kdniao_printer = $value;
		return $this;
	}

	public function setBusinessPractice($value)
	{
		$this->business_practice = $value;
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
