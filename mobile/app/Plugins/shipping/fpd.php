<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class fpd
{
	/**
     * 配置信息
     */
	public $configure;

	public function __construct($cfg = array())
	{
	}

	public function calculate($goods_weight, $goods_amount)
	{
		return 0;
	}

	public function query($invoice_sn)
	{
		$str = '<a class="btn-default-new tracking-btn" href="https://m.kuaidi100.com/result.jsp?nu=' . $invoice_sn . '">订单跟踪</a>';
		return $str;
	}

	public function api($invoice_sn = '')
	{
		return false;
	}
}


?>
