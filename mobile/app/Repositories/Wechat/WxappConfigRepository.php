<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Wechat;

class WxappConfigRepository
{
	public function getWxappConfig()
	{
		$wxappConfig = \Illuminate\Support\Facades\Cache::get('wxapp_config');

		if (empty($wxappConfig)) {
			$wxappConfig = \App\Models\WxappConfig::get()->toArray();
			\Illuminate\Support\Facades\Cache::put('wxapp_config', $wxappConfig, 60);
		}

		return $wxappConfig;
	}

	public function getWxappConfigByCode($code)
	{
		$wxappConfig = $this->getWxappConfig();

		foreach ($wxappConfig as $v) {
			return $v[$code];
		}
	}

	public function getTemplateInfo($code)
	{
		return \App\Models\WxappTemplate::select('*')->where('wx_code', $code)->first()->toArray();
	}
}


?>
