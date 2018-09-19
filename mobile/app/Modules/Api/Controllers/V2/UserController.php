<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Api\Controllers\V2;

class UserController extends \App\Modules\Api\Controllers\Controller
{
	public function __construct()
	{
		parent::__construct();
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With, Origin, Accept');
	}

	public function actionSignIn()
	{
		$payload = file_get_contents('php://input');
		$payload = json_decode($payload, true);
		$user = array('username' => $payload['username'], 'password' => $payload['password']);
		if ($user['username'] == 'demo' && $user['password'] == 'demo123') {
			$user['uid'] = 100;
			$token = $this->encode($user);
			$this->result(array('token' => $token));
		}
		else {
			$this->result($user, 1, '用户名或密码错误');
		}
	}

	public function actionSignUp()
	{
		$username = I('username');
		$password = I('password');
		$res = array();
		$this->result($res);
	}

	public function actionInfo()
	{
		$payload = file_get_contents('php://input');
		$payload = json_decode($payload, true);
		$token = $payload['token'];
		$res = $this->decode($token);

		if ($res === false) {
			$this->result($token, 1, 'Token验证数据异常');
		}
		else {
			$this->result($res);
		}
	}
}

?>
