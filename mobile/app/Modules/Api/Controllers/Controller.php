<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Api\Controllers;

class Controller extends \Think\Controller\RestController
{
	protected $config;

	public function __construct()
	{
		parent::__construct();
		$this->config = require CONF_PATH . 'jwt.php';
	}

	protected function result($data, $code = 0, $msg = 'success', $type = 'json')
	{
		$result = array('code' => $code, 'msg' => $msg, 'time' => date('Y-m-d H:i:s'), 'data' => $data);
		$this->response($result, $type);
	}

	protected function encode($payload = array())
	{
		$time = time();
		$token = array('iss' => $this->config['iss'], 'aud' => $this->config['aud'], 'iat' => $time, 'nbf' => $time, 'exp' => $time + $this->config['exp'], 'payload' => $payload);
		return \Firebase\JWT\JWT::encode($token, $this->config['secret']);
	}

	protected function decode($jwt = '')
	{
		try {
			$token = (array) \Firebase\JWT\JWT::decode($jwt, $this->config['secret'], array($this->config['alg']));
		}
		catch (\Exception $e) {
			return false;
		}

		return $token['payload'];
	}
}

?>
