<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Api\Controllers\V2;

class HomeController extends \App\Modules\Api\Controllers\Controller
{
	public function actionIndex()
	{
		$user = array('uid' => 100, 'username' => 'test123');
		$token = $this->encode($user);
		$res = $this->decode('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiIiLCJhdWQiOiIiLCJpYXQiOjE1MjU5NDI3MTQsIm5iZiI6MTUyNTk0MjcxNCwiZXhwIjoxNTI4NTM0NzE0LCJwYXlsb2FkIjp7InVpZCI6MTAwLCJ1c2VybmFtZSI6InRlc3QxMjMifX0.RRB7ADWd0x7JmF1WguvcFcCOaPUs-8h7op1AgrMcUWc');
		$this->result(array('token' => $token, 'data' => $res));
	}
}

?>
