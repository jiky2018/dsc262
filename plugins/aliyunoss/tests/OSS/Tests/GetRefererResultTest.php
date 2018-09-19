<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class GetRefererResultTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<RefererConfiguration>\n<AllowEmptyReferer>true</AllowEmptyReferer>\n<RefererList>\n<Referer>http://www.aliyun.com</Referer>\n<Referer>https://www.aliyun.com</Referer>\n<Referer>http://www.*.com</Referer>\n<Referer>https://www.?.aliyuncs.com</Referer>\n</RefererList>\n</RefererConfiguration>";

	public function testParseValidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 200);
		$result = new \OSS\Result\GetRefererResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$refererConfig = $result->getData();
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($refererConfig->serializeToXml()));
	}

	private function cleanXml($xml)
	{
		return str_replace("\n", '', str_replace("\r", '', $xml));
	}

	public function testInvalidResponse()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 300);

		try {
			new \OSS\Result\GetRefererResult($response);
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
		}
	}
}

?>
