<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Goods extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods';
	protected $primaryKey = 'goods_id';
	public $timestamps = false;
	protected $fillable = array('cat_id', 'user_cat', 'user_id', 'goods_sn', 'bar_code', 'goods_name', 'goods_name_style', 'click_count', 'brand_id', 'provider_name', 'goods_number', 'goods_weight', 'default_shipping', 'market_price', 'cost_price', 'shop_price', 'promote_price', 'promote_start_date', 'promote_end_date', 'warn_number', 'keywords', 'goods_brief', 'goods_desc', 'desc_mobile', 'goods_thumb', 'goods_img', 'original_img', 'is_real', 'extension_code', 'is_on_sale', 'is_alone_sale', 'is_shipping', 'integral', 'add_time', 'sort_order', 'is_delete', 'is_best', 'is_new', 'is_hot', 'is_promote', 'is_volume', 'is_fullcut', 'bonus_type_id', 'last_update', 'goods_type', 'seller_note', 'give_integral', 'rank_integral', 'suppliers_id', 'is_check', 'store_hot', 'store_new', 'store_best', 'group_number', 'is_xiangou', 'xiangou_start_date', 'xiangou_end_date', 'xiangou_num', 'review_status', 'review_content', 'goods_shipai', 'comments_number', 'sales_volume', 'comment_num', 'model_price', 'model_inventory', 'model_attr', 'largest_amount', 'pinyin_keyword', 'goods_product_tag', 'goods_tag', 'stages', 'stages_rate', 'freight', 'shipping_fee', 'tid', 'goods_unit', 'goods_cause', 'dis_commission', 'is_distribution', 'commission_rate', 'from_seller', 'user_brand', 'product_table', 'product_id', 'product_price', 'is_show', 'product_promote_price', 'cloud_id', 'cloud_goodsname', 'goods_video');
	protected $guarded = array();

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getUserCat()
	{
		return $this->user_cat;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getGoodsSn()
	{
		return $this->goods_sn;
	}

	public function getBarCode()
	{
		return $this->bar_code;
	}

	public function getGoodsName()
	{
		return $this->goods_name;
	}

	public function getGoodsNameStyle()
	{
		return $this->goods_name_style;
	}

	public function getClickCount()
	{
		return $this->click_count;
	}

	public function getBrandId()
	{
		return $this->brand_id;
	}

	public function getProviderName()
	{
		return $this->provider_name;
	}

	public function getGoodsNumber()
	{
		return $this->goods_number;
	}

	public function getGoodsWeight()
	{
		return $this->goods_weight;
	}

	public function getDefaultShipping()
	{
		return $this->default_shipping;
	}

	public function getMarketPrice()
	{
		return $this->market_price;
	}

	public function getCostPrice()
	{
		return $this->cost_price;
	}

	public function getShopPrice()
	{
		return $this->shop_price;
	}

	public function getPromotePrice()
	{
		return $this->promote_price;
	}

	public function getPromoteStartDate()
	{
		return $this->promote_start_date;
	}

	public function getPromoteEndDate()
	{
		return $this->promote_end_date;
	}

	public function getWarnNumber()
	{
		return $this->warn_number;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function getGoodsBrief()
	{
		return $this->goods_brief;
	}

	public function getGoodsDesc()
	{
		return $this->goods_desc;
	}

	public function getDescMobile()
	{
		return $this->desc_mobile;
	}

	public function getGoodsThumb()
	{
		return $this->goods_thumb;
	}

	public function getGoodsImg()
	{
		return $this->goods_img;
	}

	public function getOriginalImg()
	{
		return $this->original_img;
	}

	public function getIsReal()
	{
		return $this->is_real;
	}

	public function getExtensionCode()
	{
		return $this->extension_code;
	}

	public function getIsOnSale()
	{
		return $this->is_on_sale;
	}

	public function getIsAloneSale()
	{
		return $this->is_alone_sale;
	}

	public function getIsShipping()
	{
		return $this->is_shipping;
	}

	public function getIntegral()
	{
		return $this->integral;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getIsDelete()
	{
		return $this->is_delete;
	}

	public function getIsBest()
	{
		return $this->is_best;
	}

	public function getIsNew()
	{
		return $this->is_new;
	}

	public function getIsHot()
	{
		return $this->is_hot;
	}

	public function getIsPromote()
	{
		return $this->is_promote;
	}

	public function getIsVolume()
	{
		return $this->is_volume;
	}

	public function getIsFullcut()
	{
		return $this->is_fullcut;
	}

	public function getBonusTypeId()
	{
		return $this->bonus_type_id;
	}

	public function getLastUpdate()
	{
		return $this->last_update;
	}

	public function getGoodsType()
	{
		return $this->goods_type;
	}

	public function getSellerNote()
	{
		return $this->seller_note;
	}

	public function getGiveIntegral()
	{
		return $this->give_integral;
	}

	public function getRankIntegral()
	{
		return $this->rank_integral;
	}

	public function getSuppliersId()
	{
		return $this->suppliers_id;
	}

	public function getIsCheck()
	{
		return $this->is_check;
	}

	public function getStoreHot()
	{
		return $this->store_hot;
	}

	public function getStoreNew()
	{
		return $this->store_new;
	}

	public function getStoreBest()
	{
		return $this->store_best;
	}

	public function getGroupNumber()
	{
		return $this->group_number;
	}

	public function getIsXiangou()
	{
		return $this->is_xiangou;
	}

	public function getXiangouStartDate()
	{
		return $this->xiangou_start_date;
	}

	public function getXiangouEndDate()
	{
		return $this->xiangou_end_date;
	}

	public function getXiangouNum()
	{
		return $this->xiangou_num;
	}

	public function getReviewStatus()
	{
		return $this->review_status;
	}

	public function getReviewContent()
	{
		return $this->review_content;
	}

	public function getGoodsShipai()
	{
		return $this->goods_shipai;
	}

	public function getCommentsNumber()
	{
		return $this->comments_number;
	}

	public function getSalesVolume()
	{
		return $this->sales_volume;
	}

	public function getCommentNum()
	{
		return $this->comment_num;
	}

	public function getModelPrice()
	{
		return $this->model_price;
	}

	public function getModelInventory()
	{
		return $this->model_inventory;
	}

	public function getModelAttr()
	{
		return $this->model_attr;
	}

	public function getLargestAmount()
	{
		return $this->largest_amount;
	}

	public function getPinyinKeyword()
	{
		return $this->pinyin_keyword;
	}

	public function getGoodsProductTag()
	{
		return $this->goods_product_tag;
	}

	public function getGoodsTag()
	{
		return $this->goods_tag;
	}

	public function getStages()
	{
		return $this->stages;
	}

	public function getStagesRate()
	{
		return $this->stages_rate;
	}

	public function getFreight()
	{
		return $this->freight;
	}

	public function getShippingFee()
	{
		return $this->shipping_fee;
	}

	public function getTid()
	{
		return $this->tid;
	}

	public function getGoodsUnit()
	{
		return $this->goods_unit;
	}

	public function getGoodsCause()
	{
		return $this->goods_cause;
	}

	public function getDisCommission()
	{
		return $this->dis_commission;
	}

	public function getIsDistribution()
	{
		return $this->is_distribution;
	}

	public function getCommissionRate()
	{
		return $this->commission_rate;
	}

	public function getFromSeller()
	{
		return $this->from_seller;
	}

	public function getUserBrand()
	{
		return $this->user_brand;
	}

	public function getProductTable()
	{
		return $this->product_table;
	}

	public function getProductId()
	{
		return $this->product_id;
	}

	public function getProductPrice()
	{
		return $this->product_price;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function getProductPromotePrice()
	{
		return $this->product_promote_price;
	}

	public function getCloudId()
	{
		return $this->cloud_id;
	}

	public function getCloudGoodsname()
	{
		return $this->cloud_goodsname;
	}

	public function getGoodsVideo()
	{
		return $this->goods_video;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setUserCat($value)
	{
		$this->user_cat = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setGoodsSn($value)
	{
		$this->goods_sn = $value;
		return $this;
	}

	public function setBarCode($value)
	{
		$this->bar_code = $value;
		return $this;
	}

	public function setGoodsName($value)
	{
		$this->goods_name = $value;
		return $this;
	}

	public function setGoodsNameStyle($value)
	{
		$this->goods_name_style = $value;
		return $this;
	}

	public function setClickCount($value)
	{
		$this->click_count = $value;
		return $this;
	}

	public function setBrandId($value)
	{
		$this->brand_id = $value;
		return $this;
	}

	public function setProviderName($value)
	{
		$this->provider_name = $value;
		return $this;
	}

	public function setGoodsNumber($value)
	{
		$this->goods_number = $value;
		return $this;
	}

	public function setGoodsWeight($value)
	{
		$this->goods_weight = $value;
		return $this;
	}

	public function setDefaultShipping($value)
	{
		$this->default_shipping = $value;
		return $this;
	}

	public function setMarketPrice($value)
	{
		$this->market_price = $value;
		return $this;
	}

	public function setCostPrice($value)
	{
		$this->cost_price = $value;
		return $this;
	}

	public function setShopPrice($value)
	{
		$this->shop_price = $value;
		return $this;
	}

	public function setPromotePrice($value)
	{
		$this->promote_price = $value;
		return $this;
	}

	public function setPromoteStartDate($value)
	{
		$this->promote_start_date = $value;
		return $this;
	}

	public function setPromoteEndDate($value)
	{
		$this->promote_end_date = $value;
		return $this;
	}

	public function setWarnNumber($value)
	{
		$this->warn_number = $value;
		return $this;
	}

	public function setKeywords($value)
	{
		$this->keywords = $value;
		return $this;
	}

	public function setGoodsBrief($value)
	{
		$this->goods_brief = $value;
		return $this;
	}

	public function setGoodsDesc($value)
	{
		$this->goods_desc = $value;
		return $this;
	}

	public function setDescMobile($value)
	{
		$this->desc_mobile = $value;
		return $this;
	}

	public function setGoodsThumb($value)
	{
		$this->goods_thumb = $value;
		return $this;
	}

	public function setGoodsImg($value)
	{
		$this->goods_img = $value;
		return $this;
	}

	public function setOriginalImg($value)
	{
		$this->original_img = $value;
		return $this;
	}

	public function setIsReal($value)
	{
		$this->is_real = $value;
		return $this;
	}

	public function setExtensionCode($value)
	{
		$this->extension_code = $value;
		return $this;
	}

	public function setIsOnSale($value)
	{
		$this->is_on_sale = $value;
		return $this;
	}

	public function setIsAloneSale($value)
	{
		$this->is_alone_sale = $value;
		return $this;
	}

	public function setIsShipping($value)
	{
		$this->is_shipping = $value;
		return $this;
	}

	public function setIntegral($value)
	{
		$this->integral = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setIsDelete($value)
	{
		$this->is_delete = $value;
		return $this;
	}

	public function setIsBest($value)
	{
		$this->is_best = $value;
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

	public function setIsPromote($value)
	{
		$this->is_promote = $value;
		return $this;
	}

	public function setIsVolume($value)
	{
		$this->is_volume = $value;
		return $this;
	}

	public function setIsFullcut($value)
	{
		$this->is_fullcut = $value;
		return $this;
	}

	public function setBonusTypeId($value)
	{
		$this->bonus_type_id = $value;
		return $this;
	}

	public function setLastUpdate($value)
	{
		$this->last_update = $value;
		return $this;
	}

	public function setGoodsType($value)
	{
		$this->goods_type = $value;
		return $this;
	}

	public function setSellerNote($value)
	{
		$this->seller_note = $value;
		return $this;
	}

	public function setGiveIntegral($value)
	{
		$this->give_integral = $value;
		return $this;
	}

	public function setRankIntegral($value)
	{
		$this->rank_integral = $value;
		return $this;
	}

	public function setSuppliersId($value)
	{
		$this->suppliers_id = $value;
		return $this;
	}

	public function setIsCheck($value)
	{
		$this->is_check = $value;
		return $this;
	}

	public function setStoreHot($value)
	{
		$this->store_hot = $value;
		return $this;
	}

	public function setStoreNew($value)
	{
		$this->store_new = $value;
		return $this;
	}

	public function setStoreBest($value)
	{
		$this->store_best = $value;
		return $this;
	}

	public function setGroupNumber($value)
	{
		$this->group_number = $value;
		return $this;
	}

	public function setIsXiangou($value)
	{
		$this->is_xiangou = $value;
		return $this;
	}

	public function setXiangouStartDate($value)
	{
		$this->xiangou_start_date = $value;
		return $this;
	}

	public function setXiangouEndDate($value)
	{
		$this->xiangou_end_date = $value;
		return $this;
	}

	public function setXiangouNum($value)
	{
		$this->xiangou_num = $value;
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

	public function setGoodsShipai($value)
	{
		$this->goods_shipai = $value;
		return $this;
	}

	public function setCommentsNumber($value)
	{
		$this->comments_number = $value;
		return $this;
	}

	public function setSalesVolume($value)
	{
		$this->sales_volume = $value;
		return $this;
	}

	public function setCommentNum($value)
	{
		$this->comment_num = $value;
		return $this;
	}

	public function setModelPrice($value)
	{
		$this->model_price = $value;
		return $this;
	}

	public function setModelInventory($value)
	{
		$this->model_inventory = $value;
		return $this;
	}

	public function setModelAttr($value)
	{
		$this->model_attr = $value;
		return $this;
	}

	public function setLargestAmount($value)
	{
		$this->largest_amount = $value;
		return $this;
	}

	public function setPinyinKeyword($value)
	{
		$this->pinyin_keyword = $value;
		return $this;
	}

	public function setGoodsProductTag($value)
	{
		$this->goods_product_tag = $value;
		return $this;
	}

	public function setGoodsTag($value)
	{
		$this->goods_tag = $value;
		return $this;
	}

	public function setStages($value)
	{
		$this->stages = $value;
		return $this;
	}

	public function setStagesRate($value)
	{
		$this->stages_rate = $value;
		return $this;
	}

	public function setFreight($value)
	{
		$this->freight = $value;
		return $this;
	}

	public function setShippingFee($value)
	{
		$this->shipping_fee = $value;
		return $this;
	}

	public function setTid($value)
	{
		$this->tid = $value;
		return $this;
	}

	public function setGoodsUnit($value)
	{
		$this->goods_unit = $value;
		return $this;
	}

	public function setGoodsCause($value)
	{
		$this->goods_cause = $value;
		return $this;
	}

	public function setDisCommission($value)
	{
		$this->dis_commission = $value;
		return $this;
	}

	public function setIsDistribution($value)
	{
		$this->is_distribution = $value;
		return $this;
	}

	public function setCommissionRate($value)
	{
		$this->commission_rate = $value;
		return $this;
	}

	public function setFromSeller($value)
	{
		$this->from_seller = $value;
		return $this;
	}

	public function setUserBrand($value)
	{
		$this->user_brand = $value;
		return $this;
	}

	public function setProductTable($value)
	{
		$this->product_table = $value;
		return $this;
	}

	public function setProductId($value)
	{
		$this->product_id = $value;
		return $this;
	}

	public function setProductPrice($value)
	{
		$this->product_price = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}

	public function setProductPromotePrice($value)
	{
		$this->product_promote_price = $value;
		return $this;
	}

	public function setCloudId($value)
	{
		$this->cloud_id = $value;
		return $this;
	}

	public function setCloudGoodsname($value)
	{
		$this->cloud_goodsname = $value;
		return $this;
	}

	public function setGoodsVideo($value)
	{
		$this->goods_video = $value;
		return $this;
	}
}

?>
