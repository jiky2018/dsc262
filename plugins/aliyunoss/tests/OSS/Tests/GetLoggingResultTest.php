<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class GetLoggingResultTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<BucketLoggingStatus>\n<LoggingEnabled>\n<TargetBucket>TargetBucket</TargetBucket>\n<TargetPrefix>TargetPrefix</TargetPrefix>\n</LoggingEnabled>\n</BucketLoggingStatus>";

	public function testParseValidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 200);
		$result = new \OSS\Result\GetLoggingResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$loggingConfig = $result->getData();
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($loggingConfig->serializeToXml()));
		$this->assertEquals('TargetBucket', $loggingConfig->getTargetBucket());
		$this->assertEquals('TargetPrefix', $loggingConfig->getTargetPrefix());
	}

	private function cleanXml($xml)
	{
		return str_replace("\n", '', str_replace("\r", '', $xml));
	}

	public function testInvalidResponse()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 300);

		try {
			new \OSS\Result\GetLoggingResult($response);
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
		}
	}
}

?>
