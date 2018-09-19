<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class HttpTest extends \PHPUnit_Framework_TestCase
{
	public function testResponseCore()
	{
		$res = new \OSS\Http\ResponseCore(null, '', 500);
		$this->assertFalse($res->isOK());
		$this->assertTrue($res->isOK(500));
	}

	public function testGet()
	{
		$httpCore = new \OSS\Http\RequestCore('http://www.baidu.com');
		$httpResponse = $httpCore->send_request();
		$this->assertNotNull($httpResponse);
	}

	public function testSetProxyAndTimeout()
	{
		$httpCore = new \OSS\Http\RequestCore('http://www.baidu.com');
		$httpCore->set_proxy('1.0.2.1:8888');
		$httpCore->connect_timeout = 1;

		try {
			$httpResponse = $httpCore->send_request();
			$this->assertTrue(false);
		}
		catch (\OSS\Http\RequestCore_Exception $e) {
		}
	}

	public function testSendMultiRequest()
	{
		$httpCore = new \OSS\Http\RequestCore('http://www.baidu.com');
		$ch1 = curl_init('http://www.baidu.com');
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
		$ch2 = curl_init('http://cn.bing.com');
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		@$result = $httpCore->send_multi_request(array($ch1, $ch2));
		$this->assertNotNull($result);
	}

	public function testGetParseTrue()
	{
		$httpCore = new \OSS\Http\RequestCore('http://www.baidu.com');
		$httpCore->curlopts = array(CURLOPT_HEADER => true);
		$url = $httpCore->send_request(true);

		foreach ($httpCore->get_response_header() as $key => $value) {
			$this->assertEquals($httpCore->get_response_header($key), $value);
		}

		$this->assertNotNull($url);
	}

	public function testParseResponse()
	{
		$httpCore = new \OSS\Http\RequestCore('http://www.baidu.com');
		$response = $httpCore->send_request();
		$parsed = $httpCore->process_response(null, $response);
		$this->assertNotNull($parsed);
	}

	public function testExceptionGet()
	{
		$httpCore = null;
		$exception = false;

		try {
			$httpCore = new \OSS\Http\RequestCore('http://www.notexistsitexx.com');
			$httpCore->set_body('');
			$httpCore->set_method('GET');
			$httpCore->connect_timeout = 10;
			$httpCore->timeout = 10;
			$res = $httpCore->send_request();
		}
		catch (\OSS\Http\RequestCore_Exception $e) {
			$exception = true;
		}

		$this->assertTrue($exception);
	}
}

?>
