<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Behavior;

class SaaSServiceBehavior
{
	public function run()
	{
		$wechat_path = BASE_PATH . 'Modules/Wechat';
		$drp_path = BASE_PATH . 'Modules/Drp';
		$team_path = BASE_PATH . 'Modules/Team';
		$bargain_path = BASE_PATH . 'Modules/Bargain';
		$purchase_path = BASE_PATH . 'Modules/Purchase';
		$demand_path = BASE_PATH . 'Modules/Demand';
/*
		if (file_exists(ROOT_PATH . 'storage/saas_mode.txt')) {
			$site_url = 'aHR0cDovL2Nsb3VkLmRzY21hbGwuY24vaW5kZXgucGhwP2M9c2l0ZSZhPWxldmVsJm1hbGxfZG9tYWluPQ==';
			$site_rsp = \App\Extensions\Http::doGet(base64_decode($site_url) . substr(C('DB_NAME'), 3));
			$site_rsp = json_decode($site_rsp, true);

			if ($site_rsp['code'] == -1) {
				$mall_level = 0;
			}
			else {
				$mall_level = $site_rsp['data']['mall_level'];
			}

			if ($mall_level <= 0) {
				$wechat_path .= time();
				$drp_path .= time();
			}
			else if ($mall_level == 1) {
				$drp_path .= time();
			}
		}
*/
		define('APP_WECHAT_PATH', $wechat_path);
		define('APP_DRP_PATH', $drp_path);
		define('APP_TEAM_PATH', $team_path);
		define('APP_BARGAIN_PATH', $bargain_path);
		define('APP_PURCHASE_PATH', $purchase_path);
		define('APP_DEMAND_PATH', $demand_path);
	}
}


?>
