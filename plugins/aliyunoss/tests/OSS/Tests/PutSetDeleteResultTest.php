<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class ResultTest extends \PHPUnit_Framework_TestCase
{
	public function testNullResponse()
	{
		$response = null;

		try {
			new \OSS\Result\PutSetDeleteResult($response);
			$this->assertFalse(true);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('raw response is null', $e->getMessage());
		}
	}

	public function testOkResponse()
	{
		$response = new \OSS\Http\ResponseCore(array(), '', 200);
		$result = new \OSS\Result\PutSetDeleteResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
	}

	public function testFailResponse()
	{
		$response = new \OSS\Http\ResponseCore(array(), '', 301);

		try {
			new \OSS\Result\PutSetDeleteResult($response);
			$this->assertFalse(true);
		}
		catch (\OSS\Core\OssException $e) {
		}
	}

	public function setUp()
	{
	}

	public function tearDown()
	{
	}
}

?>
