<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class GetLifecycleResultTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<LifecycleConfiguration>\n<Rule>\n<ID>delete obsoleted files</ID>\n<Prefix>obsoleted/</Prefix>\n<Status>Enabled</Status>\n<Expiration><Days>3</Days></Expiration>\n</Rule>\n<Rule>\n<ID>delete temporary files</ID>\n<Prefix>temporary/</Prefix>\n<Status>Enabled</Status>\n<Expiration><Date>2022-10-12T00:00:00.000Z</Date></Expiration>\n<Expiration2><Date>2022-10-12T00:00:00.000Z</Date></Expiration2>\n</Rule>\n</LifecycleConfiguration>";

	public function testParseValidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 200);
		$result = new \OSS\Result\GetLifecycleResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$lifecycleConfig = $result->getData();
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($lifecycleConfig->serializeToXml()));
	}

	private function cleanXml($xml)
	{
		return str_replace("\n", '', str_replace("\r", '', $xml));
	}

	public function testInvalidResponse()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 300);

		try {
			new \OSS\Result\GetLifecycleResult($response);
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
		}
	}
}

?>
