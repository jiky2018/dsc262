<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class DeliveryOrder extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'delivery_order';
	protected $primaryKey = 'delivery_id';
	public $timestamps = false;
	protected $fillable = array('delivery_sn', 'order_sn', 'order_id', 'invoice_no', 'add_time', 'shipping_id', 'shipping_name', 'user_id', 'action_user', 'consignee', 'address', 'country', 'province', 'city', 'district', 'sign_building', 'email', 'zipcode', 'tel', 'mobile', 'best_time', 'postscript', 'how_oos', 'insure_fee', 'shipping_fee', 'update_time', 'suppliers_id', 'status', 'agency_id', 'is_zc_order');
	protected $guarded = array();

	public function getDeliverySn()
	{
		return $this->delivery_sn;
	}

	public function getOrderSn()
	{
		return $this->order_sn;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function getInvoiceNo()
	{
		return $this->invoice_no;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function getShippingId()
	{
		return $this->shipping_id;
	}

	public function getShippingName()
	{
		return $this->shipping_name;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getActionUser()
	{
		return $this->action_user;
	}

	public function getConsignee()
	{
		return $this->consignee;
	}

	public function getAddress()
	{
		return $this->address;
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

	public function getSignBuilding()
	{
		return $this->sign_building;
	}

	public function getEmail()
	{
		return $this->email;
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

	public function getBestTime()
	{
		return $this->best_time;
	}

	public function getPostscript()
	{
		return $this->postscript;
	}

	public function getHowOos()
	{
		return $this->how_oos;
	}

	public function getInsureFee()
	{
		return $this->insure_fee;
	}

	public function getShippingFee()
	{
		return $this->shipping_fee;
	}

	public function getUpdateTime()
	{
		return $this->update_time;
	}

	public function getSuppliersId()
	{
		return $this->suppliers_id;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getAgencyId()
	{
		return $this->agency_id;
	}

	public function getIsZcOrder()
	{
		return $this->is_zc_order;
	}

	public function setDeliverySn($value)
	{
		$this->delivery_sn = $value;
		return $this;
	}

	public function setOrderSn($value)
	{
		$this->order_sn = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setInvoiceNo($value)
	{
		$this->invoice_no = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
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

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setActionUser($value)
	{
		$this->action_user = $value;
		return $this;
	}

	public function setConsignee($value)
	{
		$this->consignee = $value;
		return $this;
	}

	public function setAddress($value)
	{
		$this->address = $value;
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

	public function setSignBuilding($value)
	{
		$this->sign_building = $value;
		return $this;
	}

	public function setEmail($value)
	{
		$this->email = $value;
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

	public function setBestTime($value)
	{
		$this->best_time = $value;
		return $this;
	}

	public function setPostscript($value)
	{
		$this->postscript = $value;
		return $this;
	}

	public function setHowOos($value)
	{
		$this->how_oos = $value;
		return $this;
	}

	public function setInsureFee($value)
	{
		$this->insure_fee = $value;
		return $this;
	}

	public function setShippingFee($value)
	{
		$this->shipping_fee = $value;
		return $this;
	}

	public function setUpdateTime($value)
	{
		$this->update_time = $value;
		return $this;
	}

	public function setSuppliersId($value)
	{
		$this->suppliers_id = $value;
		return $this;
	}

	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}

	public function setAgencyId($value)
	{
		$this->agency_id = $value;
		return $this;
	}

	public function setIsZcOrder($value)
	{
		$this->is_zc_order = $value;
		return $this;
	}
}

?>
