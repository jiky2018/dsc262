<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Patches\R20170901;

class R20170901 implements \App\Patches\Factory\PatchInterface
{
	/**
     * @var array
     */
	private $convert = array();

	public function updateDatabaseOptionally()
	{
		return false;
	}

	public function updateFiles()
	{
		$root_path = ROOT_PATH;
		if (!stristr(PHP_OS, 'WIN') && !APP_DEBUG) {
			$backup_path = $root_path . 'storage/backup';

			if (!is_dir($backup_path)) {
				mkdir($backup_path);
			}

			$version_path = $root_path . 'storage/backup/' . VERSION;

			if (!is_dir($version_path)) {
				mkdir($version_path);
			}

			$old_list = array('app/api', 'app/behavior', 'app/classes', 'app/console', 'app/contracts', 'app/custom', 'app/events', 'app/exceptions', 'app/extensions', 'app/helpers', 'app/http', 'app/jobs', 'app/libraries', 'app/listeners', 'app/models', 'app/modules', 'app/notifications', 'app/notify', 'app/presenters', 'app/providers', 'app/repositories', 'app/services', 'app/support', 'app/transformer');

			foreach ($old_list as $item) {
				if (is_dir($root_path . $item)) {
					if (!is_dir($version_path . '/' . $item)) {
						mkdir($version_path . '/' . $item, 511, true);
					}

					copy_dir($root_path . $item, $version_path . '/' . $item);
					del_dir($root_path . $item);
				}
			}
		}

		if (stristr(PHP_OS, 'WIN')) {
			$list = array('app/api' => true, 'app/contracts' => true, 'app/custom' => true, 'app/http' => true, 'app/repositories' => true, 'app' => false);

			foreach ($list as $key => $value) {
				$this->getConvert($root_path . $key, $value);
			}

			$this->transform($this->convert);
			$convert = glob($root_path . 'app/Modules/*/Language/*');
			$this->transform($convert, false);
		}

		if (!APP_DEBUG) {
			$list = array('resources/electron', 'resources/program', 'resources/vuejs');

			foreach ($list as $item) {
				if (is_dir($root_path . $item)) {
					del_dir($root_path . $item);
				}
			}
		}
	}

	private function getConvert($item, $recursion = false)
	{
		$list = glob($item . '/*');

		foreach ($list as $vo) {
			if (is_dir($vo)) {
				if ($recursion) {
					$this->getConvert($vo, $recursion);
				}

				$this->convert[] = $vo;
			}
		}
	}

	private function transform($convertList, $ucfirst = true)
	{
		foreach ($convertList as $item) {
			if ($ucfirst) {
				$name = dirname($item) . '/' . ucfirst(basename($item));
			}
			else {
				$name = dirname($item) . '/' . strtolower(basename($item));
			}

			rename($item, $name);
		}
	}
}

?>
