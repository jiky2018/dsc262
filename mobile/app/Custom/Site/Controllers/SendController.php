<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Custom\Site\Controllers;

class SendController extends \App\Modules\Site\Controllers\IndexController
{
	public function actionTest()
	{
		$message = array('code' => '1234', 'product' => 'sitename');
		$res = send_sms('18801828888', 'sms_signin', $message);

		if ($res !== true) {
			exit($res);
		}

		$res = send_mail('xxx', 'wanglin@ecmoban.com', 'title', 'content');

		if ($res !== true) {
			exit($res);
		}
	}
}

?>
