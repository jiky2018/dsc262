<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class GetWebsiteResultTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<WebsiteConfiguration>\n<IndexDocument>\n<Suffix>index.html</Suffix>\n</IndexDocument>\n<ErrorDocument>\n<Key>errorDocument.html</Key>\n</ErrorDocument>\n</WebsiteConfiguration>";

	public function testParseValidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 200);
		$result = new \OSS\Result\GetWebsiteResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$websiteConfig = $result->getData();
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($websiteConfig->serializeToXml()));
	}

	private function cleanXml($xml)
	{
		return str_replace("\n", '', str_replace("\r", '', $xml));
	}

	public function testInvalidResponse()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 300);

		try {
			new \OSS\Result\GetWebsiteResult($response);
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
		}
	}
}

?>
