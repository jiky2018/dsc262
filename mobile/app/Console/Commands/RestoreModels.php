<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Console\Commands;

class RestoreModels extends \Illuminate\Console\Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
	protected $signature = 'app:restore';
	/**
     * The console command description.
     *
     * @var string
     */
	protected $description = 'restore all models';

	public function handle()
	{
		$path = base_path('app/Models/*');
		$files = glob($path);

		foreach ($files as $file) {
			$name = basename($file, '.php');
			$content = file_get_contents($file);
			$content = str_replace('Class Dsc', 'Class ', $content);
			$content = str_replace('class Dsc', 'class ', $content);
			$content = str_replace('table = \'dsc_', 'table = \'', $content);
			$content = str_replace('	public $timestamps', '    public $timestamps', $content);
			file_put_contents($file, $content);
			rename($file, dirname($file) . '/' . str_replace('Dsc', '', $name) . '.php');
		}
	}
}

?>
