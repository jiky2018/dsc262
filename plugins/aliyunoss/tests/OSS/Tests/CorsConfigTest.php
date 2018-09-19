<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class CorsConfigTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<CORSConfiguration>\n<CORSRule>\n<AllowedOrigin>http://www.b.com</AllowedOrigin>\n<AllowedOrigin>http://www.a.com</AllowedOrigin>\n<AllowedOrigin>http://www.a.com</AllowedOrigin>\n<AllowedMethod>GET</AllowedMethod>\n<AllowedMethod>PUT</AllowedMethod>\n<AllowedMethod>POST</AllowedMethod>\n<AllowedHeader>x-oss-test</AllowedHeader>\n<AllowedHeader>x-oss-test2</AllowedHeader>\n<AllowedHeader>x-oss-test2</AllowedHeader>\n<AllowedHeader>x-oss-test3</AllowedHeader>\n<ExposeHeader>x-oss-test1</ExposeHeader>\n<ExposeHeader>x-oss-test1</ExposeHeader>\n<ExposeHeader>x-oss-test2</ExposeHeader>\n<MaxAgeSeconds>10</MaxAgeSeconds>\n</CORSRule>\n<CORSRule>\n<AllowedOrigin>http://www.b.com</AllowedOrigin>\n<AllowedMethod>GET</AllowedMethod>\n<AllowedHeader>x-oss-test</AllowedHeader>\n<ExposeHeader>x-oss-test1</ExposeHeader>\n<MaxAgeSeconds>110</MaxAgeSeconds>\n</CORSRule>\n</CORSConfiguration>";
	private $validXml2 = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<CORSConfiguration>\n<CORSRule>\n<AllowedOrigin>http://www.b.com</AllowedOrigin>\n<AllowedOrigin>http://www.a.com</AllowedOrigin>\n<AllowedOrigin>http://www.a.com</AllowedOrigin>\n<AllowedMethod>GET</AllowedMethod>\n<AllowedMethod>PUT</AllowedMethod>\n<AllowedMethod>POST</AllowedMethod>\n<AllowedHeader>x-oss-test</AllowedHeader>\n<AllowedHeader>x-oss-test2</AllowedHeader>\n<AllowedHeader>x-oss-test2</AllowedHeader>\n<AllowedHeader>x-oss-test3</AllowedHeader>\n<ExposeHeader>x-oss-test1</ExposeHeader>\n<ExposeHeader>x-oss-test1</ExposeHeader>\n<ExposeHeader>x-oss-test2</ExposeHeader>\n<MaxAgeSeconds>10</MaxAgeSeconds>\n</CORSRule>\n</CORSConfiguration>";

	public function testParseValidXml()
	{
		$corsConfig = new \OSS\Model\CorsConfig();
		$corsConfig->parseFromXml($this->validXml);
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($corsConfig->serializeToXml()));
		$this->assertNotNull($corsConfig->getRules());
		$this->assertNotNull($corsConfig->getRules()[0]->getAllowedHeaders());
		$this->assertNotNull($corsConfig->getRules()[0]->getAllowedMethods());
		$this->assertNotNull($corsConfig->getRules()[0]->getAllowedOrigins());
		$this->assertNotNull($corsConfig->getRules()[0]->getExposeHeaders());
		$this->assertNotNull($corsConfig->getRules()[0]->getMaxAgeSeconds());
	}

	public function testParseValidXml2()
	{
		$corsConfig = new \OSS\Model\CorsConfig();
		$corsConfig->parseFromXml($this->validXml2);
		$this->assertEquals($this->cleanXml($this->validXml2), $this->cleanXml($corsConfig->serializeToXml()));
	}

	public function testCreateCorsConfigFromMoreThan10Rules()
	{
		$corsConfig = new \OSS\Model\CorsConfig();
		$rule = new \OSS\Model\CorsRule();

		for ($i = 0; $i < \OSS\Model\CorsConfig::OSS_MAX_RULES; $i += 1) {
			$corsConfig->addRule($rule);
		}

		try {
			$corsConfig->addRule($rule);
			$this->assertFalse(true);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals($e->getMessage(), 'num of rules in the config exceeds self::OSS_MAX_RULES: ' . strval(\OSS\Model\CorsConfig::OSS_MAX_RULES));
		}
	}

	public function testCreateCorsConfigParamAbsent()
	{
		$corsConfig = new \OSS\Model\CorsConfig();
		$rule = new \OSS\Model\CorsRule();
		$corsConfig->addRule($rule);

		try {
			$xml = $corsConfig->serializeToXml();
			$this->assertFalse(true);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals($e->getMessage(), 'maxAgeSeconds is not set in the Rule');
		}
	}

	public function testCreateCorsConfigFromScratch()
	{
		$corsConfig = new \OSS\Model\CorsConfig();
		$rule = new \OSS\Model\CorsRule();
		$rule->addAllowedHeader('x-oss-test');
		$rule->addAllowedHeader('x-oss-test2');
		$rule->addAllowedHeader('x-oss-test2');
		$rule->addAllowedHeader('x-oss-test3');
		$rule->addAllowedOrigin('http://www.b.com');
		$rule->addAllowedOrigin('http://www.a.com');
		$rule->addAllowedOrigin('http://www.a.com');
		$rule->addAllowedMethod('GET');
		$rule->addAllowedMethod('PUT');
		$rule->addAllowedMethod('POST');
		$rule->addExposeHeader('x-oss-test1');
		$rule->addExposeHeader('x-oss-test1');
		$rule->addExposeHeader('x-oss-test2');
		$rule->setMaxAgeSeconds(10);
		$corsConfig->addRule($rule);
		$this->assertEquals($this->cleanXml($this->validXml2), $this->cleanXml($corsConfig->serializeToXml()));
		$this->assertEquals($this->cleanXml($this->validXml2), $this->cleanXml(strval($corsConfig)));
	}

	private function cleanXml($xml)
	{
		return str_replace("\n", '', str_replace("\r", '', $xml));
	}
}

?>
