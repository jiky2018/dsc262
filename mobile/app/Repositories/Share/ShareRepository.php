<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Share;

class ShareRepository
{
	protected $share;

	public function token($app, $secret)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $app . '&secret=' . $secret;
		$token = file_get_contents($url);
		return $token;
	}
}


?>
