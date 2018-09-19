<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class ExistResultTest extends \PHPUnit_Framework_TestCase
{
	public function testParseValid200()
	{
		$response = new \OSS\Http\ResponseCore(array(), '', 200);
		$result = new \OSS\Result\ExistResult($response);
		$this->assertTrue($result->isOK());
		$this->assertEquals($result->getData(), true);
	}

	public function testParseInvalid404()
	{
		$response = new \OSS\Http\ResponseCore(array(), '', 404);
		$result = new \OSS\Result\ExistResult($response);
		$this->assertTrue($result->isOK());
		$this->assertEquals($result->getData(), false);
	}

	public function testInvalidResponse()
	{
		$response = new \OSS\Http\ResponseCore(array(), '', 300);

		try {
			new \OSS\Result\ExistResult($response);
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
		}
	}
}

?>
