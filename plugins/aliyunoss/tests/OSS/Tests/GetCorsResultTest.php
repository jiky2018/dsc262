<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class GetCorsResultTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<CORSConfiguration>\n<CORSRule>\n<AllowedOrigin>http://www.b.com</AllowedOrigin>\n<AllowedOrigin>http://www.a.com</AllowedOrigin>\n<AllowedOrigin>http://www.a.com</AllowedOrigin>\n<AllowedMethod>GET</AllowedMethod>\n<AllowedMethod>PUT</AllowedMethod>\n<AllowedMethod>POST</AllowedMethod>\n<AllowedHeader>x-oss-test</AllowedHeader>\n<AllowedHeader>x-oss-test2</AllowedHeader>\n<AllowedHeader>x-oss-test2</AllowedHeader>\n<AllowedHeader>x-oss-test3</AllowedHeader>\n<ExposeHeader>x-oss-test1</ExposeHeader>\n<ExposeHeader>x-oss-test1</ExposeHeader>\n<ExposeHeader>x-oss-test2</ExposeHeader>\n<MaxAgeSeconds>10</MaxAgeSeconds>\n</CORSRule>\n<CORSRule>\n<AllowedOrigin>http://www.b.com</AllowedOrigin>\n<AllowedMethod>GET</AllowedMethod>\n<AllowedHeader>x-oss-test</AllowedHeader>\n<ExposeHeader>x-oss-test1</ExposeHeader>\n<MaxAgeSeconds>110</MaxAgeSeconds>\n</CORSRule>\n</CORSConfiguration>";

	public function testParseValidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 200);
		$result = new \OSS\Result\GetCorsResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$corsConfig = $result->getData();
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($corsConfig->serializeToXml()));
	}

	private function cleanXml($xml)
	{
		return str_replace("\n", '', str_replace("\r", '', $xml));
	}

	public function testInvalidResponse()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 300);

		try {
			new \OSS\Result\GetCorsResult($response);
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
		}
	}
}

?>
