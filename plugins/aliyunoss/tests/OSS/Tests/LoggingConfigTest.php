<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class LoggingConfigTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<BucketLoggingStatus>\n<LoggingEnabled>\n<TargetBucket>TargetBucket</TargetBucket>\n<TargetPrefix>TargetPrefix</TargetPrefix>\n</LoggingEnabled>\n</BucketLoggingStatus>";
	private $nullXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<BucketLoggingStatus/>";

	public function testParseValidXml()
	{
		$loggingConfig = new \OSS\Model\LoggingConfig();
		$loggingConfig->parseFromXml($this->validXml);
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml(strval($loggingConfig)));
	}

	public function testConstruct()
	{
		$loggingConfig = new \OSS\Model\LoggingConfig('TargetBucket', 'TargetPrefix');
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($loggingConfig->serializeToXml()));
	}

	public function testFailedConstruct()
	{
		$loggingConfig = new \OSS\Model\LoggingConfig('TargetBucket', null);
		$this->assertEquals($this->cleanXml($this->nullXml), $this->cleanXml($loggingConfig->serializeToXml()));
	}

	private function cleanXml($xml)
	{
		return str_replace("\n", '', str_replace("\r", '', $xml));
	}
}

?>
