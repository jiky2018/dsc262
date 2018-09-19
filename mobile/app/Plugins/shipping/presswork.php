<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class presswork
{
	/**
     * 配置信息
     */
	public $configure;

	public function __construct($cfg = array())
	{
		foreach ($cfg as $key => $val) {
			$this->configure[$val['name']] = $val['value'];
		}
	}

	public function calculate($goods_weight, $goods_amount)
	{
		if ((0 < $this->configure['free_money']) && ($this->configure['free_money'] <= $goods_amount)) {
			return 0;
		}
		else {
			$fee = ($goods_weight * 4) + 3.3999999999999999;

			if (0.10000000000000001 < $goods_weight) {
				$fee += ceil(($goods_weight - 0.10000000000000001) / 0.10000000000000001) * 0.40000000000000002;
			}

			return $fee;
		}
	}

	public function query($invoice_sn)
	{
		$str = '<a class="btn-default-new tracking-btn" href="https://m.kuaidi100.com/result.jsp?nu=' . $invoice_sn . '">订单跟踪</a>';
		return $str;
	}

	public function api($invoice_sn = '')
	{
		$proxy = new \App\Http\Proxy\ShippingProxy();
		return $proxy->getExpress('presswork', $invoice_sn);
	}

	public function calculate_insure($total_price, $insure_rate)
	{
		$total_price = ceil($total_price);
		$price = $total_price * $insure_rate;

		if ($price < 1) {
			$price = 1;
		}

		return ceil($price);
	}
}


?>
