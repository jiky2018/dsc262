<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\ShopConfig;

class ShopConfigRepository
{
	public function getShopConfig()
	{
		$shopConfig = \Illuminate\Support\Facades\Cache::get('shop_config');

		if (empty($shopConfig)) {
			$shopConfig = \App\Models\ShopConfig::get()->toArray();
			\Illuminate\Support\Facades\Cache::put('shop_config', $shopConfig, 60);
		}

		return $shopConfig;
	}

	public function getShopConfigByCode($code)
	{
		$shopConfig = $this->getShopConfig();

		foreach ($shopConfig as $v) {
			if ($v['code'] == $code) {
				return $v['value'];
			}
		}
	}

	public function getOssConfig()
	{
		return \App\Models\OssConfigure::where('is_use', 1)->first()->toArray();
	}
}


?>
