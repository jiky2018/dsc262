<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Behavior;

class UpgradeBehavior
{
	private $store;

	public function run()
	{
		$release = ROOT_PATH . 'storage/logs/.' . VERSION;

		if (!file_exists($release)) {
			$this->store = new \App\Patches\Factory\Store();
			$this->store->run();
			require ROOT_PATH . 'storage/clean.php';
			file_put_contents($release, VERSION);
		}
	}
}


?>
