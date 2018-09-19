<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class balance
{
	public function get_code($order, $payment)
	{
		$action = url('user/order/surpluspay', array('type' => $order['extension_code']));
		$order_amount = $order['order_amount'];
		$order_id = $order['order_id'];
		$button = '         <form id="pay_form" name="pay_form" action="' . $action . "\" method=\"post\">\r\n             <input type=\"hidden\" name=\"order_id\"  value=\"" . $order_id . "\" />\r\n             <input type=\"hidden\" name=\"surplus\"  value=\"" . $order_amount . "\" />\r\n           <input type=\"submit\" type=\"hidden\" value=\"余额支付\" class=\"btn btn-info ect-btn-info ect-colorf ect-bg c-btn3  box-flex btn-submit\">\r\n        </form>";
		return $button;
	}

	public function response()
	{
		return NULL;
	}
}

defined('IN_ECTOUCH') || exit('Deny Access');

?>
