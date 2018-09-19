<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Console\Commands;

class ProjectRelease extends \Illuminate\Console\Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
	protected $signature = 'app:lite {type=free}';
	/**
     * The console command description.
     *
     * @var string
     */
	protected $description = 'release the project';
	/**
     * root path.
     *
     * @var string
     */
	private $base_path = '';

	public function handle()
	{
		$type = $this->argument('type');
		$this->base_path = base_path();
		$free = array('app/Modules/Touchim/*', 'app/Plugins/connect/facebook.php', 'app/Plugins/payment/paypal.php', 'resources/docs/*', 'resources/client/*', 'resources/views/touchim/*', 'scripts/*', 'tests/*', '.bowerrc', '.gitattributes', '.gitignore', '.php_cs.dist', 'bower.json', 'CHANGELOG.md', 'composer.json', 'package.json', 'README.md', 'webpack.mix.js');
		$basic = array('app/Console/Commands/CustomerService.php', 'app/Modules/Chat/Controllers/AdminController.php', 'app/Modules/Chat/Controllers/AdminpController.php', 'app/Modules/Chat/Controllers/IndexController.php', 'app/Modules/Chat/Controllers/LoginController.php', 'app/Modules/Chat/Models/Kefu.php', 'app/Modules/Chat/Views/*', 'app/Modules/Purchase/*', 'app/Modules/Qrpay/*', 'app/Modules/Wechat/*', 'app/Plugins/payment/wxpay.php', 'app/Extensions/Wechat.php', 'app/Extensions/Wxapp.php', 'app/Extensions/WorkerEvent.php', 'database/*', 'public/css/console_wechat.css', 'public/css/console_wechat_seller.css', 'public/assets/wechat/*', 'public/assets/qrpay/*', 'public/fonts/wechat/*', 'public/css/wechat/*', 'public/css/wechat.css', 'public/css/wechat.min.css', 'artisan', 'respond_wxh5.php', 'resources/views/purchase/*', 'resources/views/respond/index.wxh5.html');
		$advanced = array('app/Modules/Bargain/*', 'app/Modules/Drp/*', 'app/Modules/Team/*', 'public/css/console_team.css', 'public/css/team.css', 'public/css/team.min.css');

		if ($type == 'free') {
			$allfile = array_merge($free, $basic, $advanced);
		}
		else if ($type == 'basic') {
			$allfile = array_merge($free, $advanced);
		}
		else {
			$allfile = $free;
		}

		foreach ($allfile as $vo) {
			$this->delete($vo);
		}

		$docs_file = glob($this->base_path . '/app/Modules/*/Docs');

		foreach ($docs_file as $vo) {
			$this->del_dir($vo);
		}
	}

	private function delete($file = '')
	{
		$suffix = substr($file, -2);

		if ($suffix == '/*') {
			$this->del_dir($this->base_path . '/' . substr($file, 0, -1));
		}
		else if ($suffix == '_*') {
			$this->del_pre($this->base_path . '/' . substr($file, 0, -1));
		}
		else {
			@unlink($this->base_path . '/' . $file);
		}
	}

	private function del_dir($dir)
	{
		if (!is_dir($dir)) {
			return false;
		}

		$handle = opendir($dir);

		while (($file = readdir($handle)) !== false) {
			if (($file != '.') && ($file != '..')) {
				is_dir($dir . '/' . $file) ? $this->del_dir($dir . '/' . $file) : @unlink($dir . '/' . $file);
			}
		}

		if (readdir($handle) == false) {
			closedir($handle);
			@rmdir($dir);
		}
	}

	private function del_pre($files)
	{
		$dir = dirname($files);
		$handle = opendir($dir);

		while (($file = readdir($handle)) !== false) {
			if (($file != '.') && ($file != '..')) {
				$prefix = basename($files);
				$FP = stripos($file, $prefix);

				if ($FP === 0) {
					@unlink($dir . '/' . $file);
				}
			}
		}

		closedir($handle);
	}
}

?>
