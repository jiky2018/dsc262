<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class AclResultTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" ?>\n<AccessControlPolicy>\n    <Owner>\n        <ID>00220120222</ID>\n        <DisplayName>user_example</DisplayName>\n    </Owner>\n    <AccessControlList>\n        <Grant>public-read</Grant>\n    </AccessControlList>\n</AccessControlPolicy>";
	private $invalidXml = "<?xml version=\"1.0\" ?>\n<AccessControlPolicy>\n</AccessControlPolicy>";

	public function testParseValidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 200);
		$result = new \OSS\Result\AclResult($response);
		$this->assertEquals('public-read', $result->getData());
	}

	public function testParseNullXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), '', 200);

		try {
			new \OSS\Result\AclResult($response);
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('body is null', $e->getMessage());
		}
	}

	public function testParseInvalidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->invalidXml, 200);

		try {
			new \OSS\Result\AclResult($response);
			$this->assertFalse(true);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('xml format exception', $e->getMessage());
		}
	}
}

?>
