<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Api\Controllers;

class IndexController extends \App\Modules\Api\Foundation\Controller
{
	public function actionIndex()
	{
		$this->resp(array('foo' => 'bar'));
	}
}

?>
