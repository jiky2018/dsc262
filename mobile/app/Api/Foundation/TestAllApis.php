<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Foundation;

class TestAllApis
{
	private $apis;
	private $gateway;
	private $logger;

	public function __construct()
	{
		$this->gateway = 'http://10.10.10.145/dsc/mobile/?app=api';
		$this->setApis();
	}

	public function test()
	{
		$post = $this->apis;
		$result = array();
		$log = ApiLogger::init('api', 'debug');

		foreach ($post as $v) {
			if (empty($v) || empty($v['method'])) {
				continue;
			}

			if (($res = $this->doTest($v)) === true) {
				$result[$v['method']] = array('code' => 'success');
				$log->info('接口信息：' . $v['method'] . ' msg:' . $res['msg']);
				$log->notice('接口提醒：' . $v['method'] . ' msg:' . $res['msg']);
			}
			else {
				$result[$v['method']] = array('code' => 'fail', 'msg' => $res['msg']);
				$log->error('接口错误：' . $v['method'] . ' msg:' . $res['msg']);
				$log->debug(array('wer' => 'werwrwr'));
			}
		}

		return $result;
	}

	private function doTest($postData)
	{
		$response = \App\Extensions\Http::doPost($this->gateway, $postData);
		$response = json_decode($response);

		if (is_object($response)) {
			$response = (array) $response;
		}

		$code = (string) $response['code'];

		if ('0' === $code) {
			return true;
		}
		else {
			return $response;
		}
	}

	public function addApis(array $post)
	{
		foreach ($this->apis as $v) {
			if ($v['method'] == $post['method']) {
				return false;
			}
		}

		$this->apis[] = $post;
	}

	public function getApis()
	{
		return $this->apis;
	}

	public function getGateway()
	{
		return $this->gateway;
	}

	private function setApis()
	{
		$this->apis = array(
	array('method' => 'ecapi.shop.get', 'id' => '1'),
	array('method' => 'ecapi.category.list'),
	array('method' => 'ecapi.category.get', 'id' => '1'),
	array('method' => 'ecapi.brand.list'),
	array('method' => 'ecapi.brand.get', 'id' => '1')
	);
	}
}


?>
