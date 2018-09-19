<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Payment;

class PayLogRepository
{
	public function insert_pay_log($id, $amount, $type = PAY_SURPLUS, $is_paid = 0)
	{
		$payLog = new \App\Models\PayLog();
		$payLog->order_id = $id;
		$payLog->order_amount = $amount;
		$payLog->order_type = $type;
		$payLog->is_paid = $is_paid;
		$payLog->save();
		return $payLog->log_id;
	}

	public function pay_log_info($oid, $type = PAY_SURPLUS)
	{
		return \App\Models\PayLog::where('order_id', $oid)->where('order_type', $type)->select('log_id')->first()->toArray();
	}
}


?>
