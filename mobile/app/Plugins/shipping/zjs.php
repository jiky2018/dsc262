<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class zjs
{
	/**
     * 配置信息参数
     */
	public $configure;

	public function __construct($cfg = array())
	{
		foreach ($cfg as $key => $val) {
			$this->configure[$val['name']] = $val['value'];
		}
	}

	public function calculate($goods_weight, $goods_amount, $goods_number)
	{
		if ((0 < $this->configure['free_money']) && ($this->configure['free_money'] <= $goods_amount)) {
			return 0;
		}
		else {
			@$fee = $this->configure['base_fee'];
			$this->configure['fee_compute_mode'] = !empty($this->configure['fee_compute_mode']) ? $this->configure['fee_compute_mode'] : 'by_weight';

			if ($this->configure['fee_compute_mode'] == 'by_number') {
				$fee = $goods_number * $this->configure['item_fee'];
			}
			else if (1 < $goods_weight) {
				$fee += ceil($goods_weight - 1) * $this->configure['step_fee'];
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
		return $proxy->getExpress('zhaijisong', $invoice_sn);
	}
}


?>
