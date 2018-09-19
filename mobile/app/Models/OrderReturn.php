<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class OrderReturn extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'order_return';
	protected $primaryKey = 'ret_id';
	public $timestamps = false;
	protected $fillable = array('return_sn', 'goods_id', 'user_id', 'rec_id', 'order_id', 'order_sn', 'credentials', 'maintain', 'back', 'goods_attr', 'exchange', 'return_type', 'attr_val', 'cause_id', 'apply_time', 'return_time', 'should_return', 'actual_return', 'return_shipping_fee', 'return_brief', 'remark', 'country', 'province', 'city', 'district', 'street', 'addressee', 'phone', 'address', 'zipcode', 'is_check', 'return_status', 'refound_status', 'back_shipping_name', 'back_other_shipping', 'back_invoice_no', 'out_shipping_name', 'out_invoice_no', 'agree_apply', 'chargeoff_status', 'activation_number', 'refund_type');
	protected $guarded = array();

	public function getReturnSn()
	{
		return $this->return_sn;
	}

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getRecId()
	{
		return $this->rec_id;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getOrderSn()
	{
		return $this->order_sn;
	}

	public function getCredentials()
	{
		return $this->credentials;
	}

	public function getMaintain()
	{
		return $this->maintain;
	}

	public function getBack()
	{
		return $this->back;
	}

	public function getGoodsAttr()
	{
		return $this->goods_attr;
	}

	public function getExchange()
	{
		return $this->exchange;
	}

	public function getReturnType()
	{
		return $this->return_type;
	}

	public function getAttrVal()
	{
		return $this->attr_val;
	}

	public function getCauseId()
	{
		return $this->cause_id;
	}

	public function getApplyTime()
	{
		return $this->apply_time;
	}

	public function getReturnTime()
	{
		return $this->return_time;
	}

	public function getShouldReturn()
	{
		return $this->should_return;
	}

	public function getActualReturn()
	{
		return $this->actual_return;
	}

	public function getReturnShippingFee()
	{
		return $this->return_shipping_fee;
	}

	public function getReturnBrief()
	{
		return $this->return_brief;
	}

	public function getRemark()
	{
		return $this->remark;
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

	public function getAddressee()
	{
		return $this->addressee;
	}

	public function getPhone()
	{
		return $this->phone;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function getZipcode()
	{
		return $this->zipcode;
	}

	public function getIsCheck()
	{
		return $this->is_check;
	}

	public function getReturnStatus()
	{
		return $this->return_status;
	}

	public function getRefoundStatus()
	{
		return $this->refound_status;
	}

	public function getBackShippingName()
	{
		return $this->back_shipping_name;
	}

	public function getBackOtherShipping()
	{
		return $this->back_other_shipping;
	}

	public function getBackInvoiceNo()
	{
		return $this->back_invoice_no;
	}

	public function getOutShippingName()
	{
		return $this->out_shipping_name;
	}

	public function getOutInvoiceNo()
	{
		return $this->out_invoice_no;
	}

	public function getAgreeApply()
	{
		return $this->agree_apply;
	}

	public function getChargeoffStatus()
	{
		return $this->chargeoff_status;
	}

	public function getActivationNumber()
	{
		return $this->activation_number;
	}

	public function getRefundType()
	{
		return $this->refund_type;
	}

	public function setReturnSn($value)
	{
		$this->return_sn = $value;
		return $this;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setRecId($value)
	{
		$this->rec_id = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setOrderSn($value)
	{
		$this->order_sn = $value;
		return $this;
	}

	public function setCredentials($value)
	{
		$this->credentials = $value;
		return $this;
	}

	public function setMaintain($value)
	{
		$this->maintain = $value;
		return $this;
	}

	public function setBack($value)
	{
		$this->back = $value;
		return $this;
	}

	public function setGoodsAttr($value)
	{
		$this->goods_attr = $value;
		return $this;
	}

	public function setExchange($value)
	{
		$this->exchange = $value;
		return $this;
	}

	public function setReturnType($value)
	{
		$this->return_type = $value;
		return $this;
	}

	public function setAttrVal($value)
	{
		$this->attr_val = $value;
		return $this;
	}

	public function setCauseId($value)
	{
		$this->cause_id = $value;
		return $this;
	}

	public function setApplyTime($value)
	{
		$this->apply_time = $value;
		return $this;
	}

	public function setReturnTime($value)
	{
		$this->return_time = $value;
		return $this;
	}

	public function setShouldReturn($value)
	{
		$this->should_return = $value;
		return $this;
	}

	public function setActualReturn($value)
	{
		$this->actual_return = $value;
		return $this;
	}

	public function setReturnShippingFee($value)
	{
		$this->return_shipping_fee = $value;
		return $this;
	}

	public function setReturnBrief($value)
	{
		$this->return_brief = $value;
		return $this;
	}

	public function setRemark($value)
	{
		$this->remark = $value;
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

	public function setAddressee($value)
	{
		$this->addressee = $value;
		return $this;
	}

	public function setPhone($value)
	{
		$this->phone = $value;
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

	public function setIsCheck($value)
	{
		$this->is_check = $value;
		return $this;
	}

	public function setReturnStatus($value)
	{
		$this->return_status = $value;
		return $this;
	}

	public function setRefoundStatus($value)
	{
		$this->refound_status = $value;
		return $this;
	}

	public function setBackShippingName($value)
	{
		$this->back_shipping_name = $value;
		return $this;
	}

	public function setBackOtherShipping($value)
	{
		$this->back_other_shipping = $value;
		return $this;
	}

	public function setBackInvoiceNo($value)
	{
		$this->back_invoice_no = $value;
		return $this;
	}

	public function setOutShippingName($value)
	{
		$this->out_shipping_name = $value;
		return $this;
	}

	public function setOutInvoiceNo($value)
	{
		$this->out_invoice_no = $value;
		return $this;
	}

	public function setAgreeApply($value)
	{
		$this->agree_apply = $value;
		return $this;
	}

	public function setChargeoffStatus($value)
	{
		$this->chargeoff_status = $value;
		return $this;
	}

	public function setActivationNumber($value)
	{
		$this->activation_number = $value;
		return $this;
	}

	public function setRefundType($value)
	{
		$this->refund_type = $value;
		return $this;
	}
}

?>
