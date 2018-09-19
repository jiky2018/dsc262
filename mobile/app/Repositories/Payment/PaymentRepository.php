<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Payment;

class PaymentRepository
{
	public function paymentList()
	{
		$payment = \App\Models\Payment::select('pay_id', 'pay_code', 'pay_name', 'pay_fee', 'pay_desc', 'pay_config', 'is_cod')->where('enabled', 1)->get()->toArray();
		return $payment;
	}
}


?>
