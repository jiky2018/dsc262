<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class SellerCommissionBill extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_commission_bill';
	public $timestamps = false;
	protected $fillable = array('seller_id', 'bill_sn', 'order_amount', 'shipping_amount', 'return_amount', 'return_shippingfee', 'proportion', 'commission_model', 'gain_commission', 'should_amount', 'actual_amount', 'chargeoff_time', 'settleaccounts_time', 'start_time', 'end_time', 'chargeoff_status', 'bill_cycle', 'bill_apply', 'apply_note', 'apply_time', 'operator', 'check_status', 'reject_note', 'check_time', 'frozen_money', 'frozen_data', 'frozen_time');
	protected $guarded = array();

	public function getSellerId()
	{
		return $this->seller_id;
	}

	public function getBillSn()
	{
		return $this->bill_sn;
	}

	public function getOrderAmount()
	{
		return $this->order_amount;
	}

	public function getShippingAmount()
	{
		return $this->shipping_amount;
	}

	public function getReturnAmount()
	{
		return $this->return_amount;
	}

	public function getReturnShippingfee()
	{
		return $this->return_shippingfee;
	}

	public function getProportion()
	{
		return $this->proportion;
	}

	public function getCommissionModel()
	{
		return $this->commission_model;
	}

	public function getGainCommission()
	{
		return $this->gain_commission;
	}

	public function getShouldAmount()
	{
		return $this->should_amount;
	}

	public function getActualAmount()
	{
		return $this->actual_amount;
	}

	public function getChargeoffTime()
	{
		return $this->chargeoff_time;
	}

	public function getSettleaccountsTime()
	{
		return $this->settleaccounts_time;
	}

	public function getStartTime()
	{
		return $this->start_time;
	}

	public function getEndTime()
	{
		return $this->end_time;
	}

	public function getChargeoffStatus()
	{
		return $this->chargeoff_status;
	}

	public function getBillCycle()
	{
		return $this->bill_cycle;
	}

	public function getBillApply()
	{
		return $this->bill_apply;
	}

	public function getApplyNote()
	{
		return $this->apply_note;
	}

	public function getApplyTime()
	{
		return $this->apply_time;
	}

	public function getOperator()
	{
		return $this->operator;
	}

	public function getCheckStatus()
	{
		return $this->check_status;
	}

	public function getRejectNote()
	{
		return $this->reject_note;
	}

	public function getCheckTime()
	{
		return $this->check_time;
	}

	public function getFrozenMoney()
	{
		return $this->frozen_money;
	}

	public function getFrozenData()
	{
		return $this->frozen_data;
	}

	public function getFrozenTime()
	{
		return $this->frozen_time;
	}

	public function setSellerId($value)
	{
		$this->seller_id = $value;
		return $this;
	}

	public function setBillSn($value)
	{
		$this->bill_sn = $value;
		return $this;
	}

	public function setOrderAmount($value)
	{
		$this->order_amount = $value;
		return $this;
	}

	public function setShippingAmount($value)
	{
		$this->shipping_amount = $value;
		return $this;
	}

	public function setReturnAmount($value)
	{
		$this->return_amount = $value;
		return $this;
	}

	public function setReturnShippingfee($value)
	{
		$this->return_shippingfee = $value;
		return $this;
	}

	public function setProportion($value)
	{
		$this->proportion = $value;
		return $this;
	}

	public function setCommissionModel($value)
	{
		$this->commission_model = $value;
		return $this;
	}

	public function setGainCommission($value)
	{
		$this->gain_commission = $value;
		return $this;
	}

	public function setShouldAmount($value)
	{
		$this->should_amount = $value;
		return $this;
	}

	public function setActualAmount($value)
	{
		$this->actual_amount = $value;
		return $this;
	}

	public function setChargeoffTime($value)
	{
		$this->chargeoff_time = $value;
		return $this;
	}

	public function setSettleaccountsTime($value)
	{
		$this->settleaccounts_time = $value;
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

	public function setChargeoffStatus($value)
	{
		$this->chargeoff_status = $value;
		return $this;
	}

	public function setBillCycle($value)
	{
		$this->bill_cycle = $value;
		return $this;
	}

	public function setBillApply($value)
	{
		$this->bill_apply = $value;
		return $this;
	}

	public function setApplyNote($value)
	{
		$this->apply_note = $value;
		return $this;
	}

	public function setApplyTime($value)
	{
		$this->apply_time = $value;
		return $this;
	}

	public function setOperator($value)
	{
		$this->operator = $value;
		return $this;
	}

	public function setCheckStatus($value)
	{
		$this->check_status = $value;
		return $this;
	}

	public function setRejectNote($value)
	{
		$this->reject_note = $value;
		return $this;
	}

	public function setCheckTime($value)
	{
		$this->check_time = $value;
		return $this;
	}

	public function setFrozenMoney($value)
	{
		$this->frozen_money = $value;
		return $this;
	}

	public function setFrozenData($value)
	{
		$this->frozen_data = $value;
		return $this;
	}

	public function setFrozenTime($value)
	{
		$this->frozen_time = $value;
		return $this;
	}
}

?>
