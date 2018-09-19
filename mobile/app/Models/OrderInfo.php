<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class OrderInfo extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'order_info';
	protected $primaryKey = 'order_id';
	public $timestamps = false;
	protected $fillable = array('main_order_id', 'order_sn', 'user_id', 'order_status', 'shipping_status', 'pay_status', 'consignee', 'country', 'province', 'city', 'district', 'street', 'address', 'zipcode', 'tel', 'mobile', 'email', 'best_time', 'sign_building', 'postscript', 'shipping_id', 'shipping_name', 'shipping_code', 'shipping_type', 'pay_id', 'pay_name', 'how_oos', 'how_surplus', 'pack_name', 'card_name', 'card_message', 'inv_payee', 'inv_content', 'goods_amount', 'cost_amount', 'shipping_fee', 'insure_fee', 'pay_fee', 'pack_fee', 'card_fee', 'money_paid', 'surplus', 'integral', 'integral_money', 'bonus', 'order_amount', 'return_amount', 'from_ad', 'referer', 'add_time', 'confirm_time', 'pay_time', 'shipping_time', 'confirm_take_time', 'auto_delivery_time', 'pack_id', 'card_id', 'bonus_id', 'invoice_no', 'extension_code', 'extension_id', 'to_buyer', 'pay_note', 'agency_id', 'inv_type', 'tax', 'is_separate', 'parent_id', 'discount', 'discount_all', 'is_delete', 'is_settlement', 'sign_time', 'is_single', 'point_id', 'shipping_dateStr', 'supplier_id', 'froms', 'coupons', 'uc_id', 'is_zc_order', 'zc_goods_id', 'is_frozen', 'drp_is_separate', 'team_id', 'team_parent_id', 'team_user_id', 'team_price', 'chargeoff_status', 'invoice_type', 'vat_id', 'tax_id', 'is_update_sale');
	protected $guarded = array();

	public function goods()
	{
		return self::hasMany('App\\Models\\OrderGoods', 'order_id', 'order_id');
	}

	public function getMainOrderId()
	{
		return $this->main_order_id;
	}

	public function getOrderSn()
	{
		return $this->order_sn;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getOrderStatus()
	{
		return $this->order_status;
	}

	public function getShippingStatus()
	{
		return $this->shipping_status;
	}

	public function getPayStatus()
	{
		return $this->pay_status;
	}

	public function getConsignee()
	{
		return $this->consignee;
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

	public function getStreet()
	{
		return $this->street;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function getZipcode()
	{
		return $this->zipcode;
	}

	public function getTel()
	{
		return $this->tel;
	}

	public function getMobile()
	{
		return $this->mobile;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getBestTime()
	{
		return $this->best_time;
	}

	public function getSignBuilding()
	{
		return $this->sign_building;
	}

	public function getPostscript()
	{
		return $this->postscript;
	}

	public function getShippingId()
	{
		return $this->shipping_id;
	}

	public function getShippingName()
	{
		return $this->shipping_name;
	}

	public function getShippingCode()
	{
		return $this->shipping_code;
	}

	public function getShippingType()
	{
		return $this->shipping_type;
	}

	public function getPayId()
	{
		return $this->pay_id;
	}

	public function getPayName()
	{
		return $this->pay_name;
	}

	public function getHowOos()
	{
		return $this->how_oos;
	}

	public function getHowSurplus()
	{
		return $this->how_surplus;
	}

	public function getPackName()
	{
		return $this->pack_name;
	}

	public function getCardName()
	{
		return $this->card_name;
	}

	public function getCardMessage()
	{
		return $this->card_message;
	}

	public function getInvPayee()
	{
		return $this->inv_payee;
	}

	public function getInvContent()
	{
		return $this->inv_content;
	}

	public function getGoodsAmount()
	{
		return $this->goods_amount;
	}

	public function getCostAmount()
	{
		return $this->cost_amount;
	}

	public function getShippingFee()
	{
		return $this->shipping_fee;
	}

	public function getInsureFee()
	{
		return $this->insure_fee;
	}

	public function getPayFee()
	{
		return $this->pay_fee;
	}

	public function getPackFee()
	{
		return $this->pack_fee;
	}

	public function getCardFee()
	{
		return $this->card_fee;
	}

	public function getMoneyPaid()
	{
		return $this->money_paid;
	}

	public function getSurplus()
	{
		return $this->surplus;
	}

	public function getIntegral()
	{
		return $this->integral;
	}

	public function getIntegralMoney()
	{
		return $this->integral_money;
	}

	public function getBonus()
	{
		return $this->bonus;
	}

	public function getOrderAmount()
	{
		return $this->order_amount;
	}

	public function getReturnAmount()
	{
		return $this->return_amount;
	}

	public function getFromAd()
	{
		return $this->from_ad;
	}

	public function getReferer()
	{
		return $this->referer;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getConfirmTime()
	{
		return $this->confirm_time;
	}

	public function getPayTime()
	{
		return $this->pay_time;
	}

	public function getShippingTime()
	{
		return $this->shipping_time;
	}

	public function getConfirmTakeTime()
	{
		return $this->confirm_take_time;
	}

	public function getAutoDeliveryTime()
	{
		return $this->auto_delivery_time;
	}

	public function getPackId()
	{
		return $this->pack_id;
	}

	public function getCardId()
	{
		return $this->card_id;
	}

	public function getBonusId()
	{
		return $this->bonus_id;
	}

	public function getInvoiceNo()
	{
		return $this->invoice_no;
	}

	public function getExtensionCode()
	{
		return $this->extension_code;
	}

	public function getExtensionId()
	{
		return $this->extension_id;
	}

	public function getToBuyer()
	{
		return $this->to_buyer;
	}

	public function getPayNote()
	{
		return $this->pay_note;
	}

	public function getAgencyId()
	{
		return $this->agency_id;
	}

	public function getInvType()
	{
		return $this->inv_type;
	}

	public function getTax()
	{
		return $this->tax;
	}

	public function getIsSeparate()
	{
		return $this->is_separate;
	}

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getDiscount()
	{
		return $this->discount;
	}

	public function getDiscountAll()
	{
		return $this->discount_all;
	}

	public function getIsDelete()
	{
		return $this->is_delete;
	}

	public function getIsSettlement()
	{
		return $this->is_settlement;
	}

	public function getSignTime()
	{
		return $this->sign_time;
	}

	public function getIsSingle()
	{
		return $this->is_single;
	}

	public function getPointId()
	{
		return $this->point_id;
	}

	public function getShippingDateStr()
	{
		return $this->shipping_dateStr;
	}

	public function getSupplierId()
	{
		return $this->supplier_id;
	}

	public function getFroms()
	{
		return $this->froms;
	}

	public function getCoupons()
	{
		return $this->coupons;
	}

	public function getUcId()
	{
		return $this->uc_id;
	}

	public function getIsZcOrder()
	{
		return $this->is_zc_order;
	}

	public function getZcGoodsId()
	{
		return $this->zc_goods_id;
	}

	public function getIsFrozen()
	{
		return $this->is_frozen;
	}

	public function getDrpIsSeparate()
	{
		return $this->drp_is_separate;
	}

	public function getTeamId()
	{
		return $this->team_id;
	}

	public function getTeamParentId()
	{
		return $this->team_parent_id;
	}

	public function getTeamUserId()
	{
		return $this->team_user_id;
	}

	public function getTeamPrice()
	{
		return $this->team_price;
	}

	public function getChargeoffStatus()
	{
		return $this->chargeoff_status;
	}

	public function getInvoiceType()
	{
		return $this->invoice_type;
	}

	public function getVatId()
	{
		return $this->vat_id;
	}

	public function getTaxId()
	{
		return $this->tax_id;
	}

	public function getIsUpdateSale()
	{
		return $this->is_update_sale;
	}

	public function setMainOrderId($value)
	{
		$this->main_order_id = $value;
		return $this;
	}

	public function setOrderSn($value)
	{
		$this->order_sn = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setOrderStatus($value)
	{
		$this->order_status = $value;
		return $this;
	}

	public function setShippingStatus($value)
	{
		$this->shipping_status = $value;
		return $this;
	}

	public function setPayStatus($value)
	{
		$this->pay_status = $value;
		return $this;
	}

	public function setConsignee($value)
	{
		$this->consignee = $value;
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

	public function setStreet($value)
	{
		$this->street = $value;
		return $this;
	}

	public function setAddress($value)
	{
		$this->address = $value;
		return $this;
	}

	public function setZipcode($value)
	{
		$this->zipcode = $value;
		return $this;
	}

	public function setTel($value)
	{
		$this->tel = $value;
		return $this;
	}

	public function setMobile($value)
	{
		$this->mobile = $value;
		return $this;
	}

	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}

	public function setBestTime($value)
	{
		$this->best_time = $value;
		return $this;
	}

	public function setSignBuilding($value)
	{
		$this->sign_building = $value;
		return $this;
	}

	public function setPostscript($value)
	{
		$this->postscript = $value;
		return $this;
	}

	public function setShippingId($value)
	{
		$this->shipping_id = $value;
		return $this;
	}

	public function setShippingName($value)
	{
		$this->shipping_name = $value;
		return $this;
	}

	public function setShippingCode($value)
	{
		$this->shipping_code = $value;
		return $this;
	}

	public function setShippingType($value)
	{
		$this->shipping_type = $value;
		return $this;
	}

	public function setPayId($value)
	{
		$this->pay_id = $value;
		return $this;
	}

	public function setPayName($value)
	{
		$this->pay_name = $value;
		return $this;
	}

	public function setHowOos($value)
	{
		$this->how_oos = $value;
		return $this;
	}

	public function setHowSurplus($value)
	{
		$this->how_surplus = $value;
		return $this;
	}

	public function setPackName($value)
	{
		$this->pack_name = $value;
		return $this;
	}

	public function setCardName($value)
	{
		$this->card_name = $value;
		return $this;
	}

	public function setCardMessage($value)
	{
		$this->card_message = $value;
		return $this;
	}

	public function setInvPayee($value)
	{
		$this->inv_payee = $value;
		return $this;
	}

	public function setInvContent($value)
	{
		$this->inv_content = $value;
		return $this;
	}

	public function setGoodsAmount($value)
	{
		$this->goods_amount = $value;
		return $this;
	}

	public function setCostAmount($value)
	{
		$this->cost_amount = $value;
		return $this;
	}

	public function setShippingFee($value)
	{
		$this->shipping_fee = $value;
		return $this;
	}

	public function setInsureFee($value)
	{
		$this->insure_fee = $value;
		return $this;
	}

	public function setPayFee($value)
	{
		$this->pay_fee = $value;
		return $this;
	}

	public function setPackFee($value)
	{
		$this->pack_fee = $value;
		return $this;
	}

	public function setCardFee($value)
	{
		$this->card_fee = $value;
		return $this;
	}

	public function setMoneyPaid($value)
	{
		$this->money_paid = $value;
		return $this;
	}

	public function setSurplus($value)
	{
		$this->surplus = $value;
		return $this;
	}

	public function setIntegral($value)
	{
		$this->integral = $value;
		return $this;
	}

	public function setIntegralMoney($value)
	{
		$this->integral_money = $value;
		return $this;
	}

	public function setBonus($value)
	{
		$this->bonus = $value;
		return $this;
	}

	public function setOrderAmount($value)
	{
		$this->order_amount = $value;
		return $this;
	}

	public function setReturnAmount($value)
	{
		$this->return_amount = $value;
		return $this;
	}

	public function setFromAd($value)
	{
		$this->from_ad = $value;
		return $this;
	}

	public function setReferer($value)
	{
		$this->referer = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}

	public function setConfirmTime($value)
	{
		$this->confirm_time = $value;
		return $this;
	}

	public function setPayTime($value)
	{
		$this->pay_time = $value;
		return $this;
	}

	public function setShippingTime($value)
	{
		$this->shipping_time = $value;
		return $this;
	}

	public function setConfirmTakeTime($value)
	{
		$this->confirm_take_time = $value;
		return $this;
	}

	public function setAutoDeliveryTime($value)
	{
		$this->auto_delivery_time = $value;
		return $this;
	}

	public function setPackId($value)
	{
		$this->pack_id = $value;
		return $this;
	}

	public function setCardId($value)
	{
		$this->card_id = $value;
		return $this;
	}

	public function setBonusId($value)
	{
		$this->bonus_id = $value;
		return $this;
	}

	public function setInvoiceNo($value)
	{
		$this->invoice_no = $value;
		return $this;
	}

	public function setExtensionCode($value)
	{
		$this->extension_code = $value;
		return $this;
	}

	public function setExtensionId($value)
	{
		$this->extension_id = $value;
		return $this;
	}

	public function setToBuyer($value)
	{
		$this->to_buyer = $value;
		return $this;
	}

	public function setPayNote($value)
	{
		$this->pay_note = $value;
		return $this;
	}

	public function setAgencyId($value)
	{
		$this->agency_id = $value;
		return $this;
	}

	public function setInvType($value)
	{
		$this->inv_type = $value;
		return $this;
	}

	public function setTax($value)
	{
		$this->tax = $value;
		return $this;
	}

	public function setIsSeparate($value)
	{
		$this->is_separate = $value;
		return $this;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setDiscount($value)
	{
		$this->discount = $value;
		return $this;
	}

	public function setDiscountAll($value)
	{
		$this->discount_all = $value;
		return $this;
	}

	public function setIsDelete($value)
	{
		$this->is_delete = $value;
		return $this;
	}

	public function setIsSettlement($value)
	{
		$this->is_settlement = $value;
		return $this;
	}

	public function setSignTime($value)
	{
		$this->sign_time = $value;
		return $this;
	}

	public function setIsSingle($value)
	{
		$this->is_single = $value;
		return $this;
	}

	public function setPointId($value)
	{
		$this->point_id = $value;
		return $this;
	}

	public function setShippingDateStr($value)
	{
		$this->shipping_dateStr = $value;
		return $this;
	}

	public function setSupplierId($value)
	{
		$this->supplier_id = $value;
		return $this;
	}

	public function setFroms($value)
	{
		$this->froms = $value;
		return $this;
	}

	public function setCoupons($value)
	{
		$this->coupons = $value;
		return $this;
	}

	public function setUcId($value)
	{
		$this->uc_id = $value;
		return $this;
	}

	public function setIsZcOrder($value)
	{
		$this->is_zc_order = $value;
		return $this;
	}

	public function setZcGoodsId($value)
	{
		$this->zc_goods_id = $value;
		return $this;
	}

	public function setIsFrozen($value)
	{
		$this->is_frozen = $value;
		return $this;
	}

	public function setDrpIsSeparate($value)
	{
		$this->drp_is_separate = $value;
		return $this;
	}

	public function setTeamId($value)
	{
		$this->team_id = $value;
		return $this;
	}

	public function setTeamParentId($value)
	{
		$this->team_parent_id = $value;
		return $this;
	}

	public function setTeamUserId($value)
	{
		$this->team_user_id = $value;
		return $this;
	}

	public function setTeamPrice($value)
	{
		$this->team_price = $value;
		return $this;
	}

	public function setChargeoffStatus($value)
	{
		$this->chargeoff_status = $value;
		return $this;
	}

	public function setInvoiceType($value)
	{
		$this->invoice_type = $value;
		return $this;
	}

	public function setVatId($value)
	{
		$this->vat_id = $value;
		return $this;
	}

	public function setTaxId($value)
	{
		$this->tax_id = $value;
		return $this;
	}

	public function setIsUpdateSale($value)
	{
		$this->is_update_sale = $value;
		return $this;
	}
}

?>
