<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class WebsiteConfigTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<WebsiteConfiguration>\n<IndexDocument>\n<Suffix>index.html</Suffix>\n</IndexDocument>\n<ErrorDocument>\n<Key>errorDocument.html</Key>\n</ErrorDocument>\n</WebsiteConfiguration>";
	private $nullXml = '<?xml version="1.0" encoding="utf-8"?><WebsiteConfiguration><IndexDocument><Suffix/></IndexDocument><ErrorDocument><Key/></ErrorDocument></WebsiteConfiguration>';
	private $nullXml2 = '<?xml version="1.0" encoding="utf-8"?><WebsiteConfiguration><IndexDocument><Suffix></Suffix></IndexDocument><ErrorDocument><Key></Key></ErrorDocument></WebsiteConfiguration>';

	public function testParseValidXml()
	{
		$websiteConfig = new \OSS\Model\WebsiteConfig('index');
		$websiteConfig->parseFromXml($this->validXml);
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($websiteConfig->serializeToXml()));
	}

	public function testParsenullXml()
	{
		$websiteConfig = new \OSS\Model\WebsiteConfig();
		$websiteConfig->parseFromXml($this->nullXml);
		$this->assertTrue(($this->cleanXml($this->nullXml) === $this->cleanXml($websiteConfig->serializeToXml())) || ($this->cleanXml($this->nullXml2) === $this->cleanXml($websiteConfig->serializeToXml())));
	}

	public function testWebsiteConstruct()
	{
		$websiteConfig = new \OSS\Model\WebsiteConfig('index.html', 'errorDocument.html');
		$this->assertEquals('index.html', $websiteConfig->getIndexDocument());
		$this->assertEquals('errorDocument.html', $websiteConfig->getErrorDocument());
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($websiteConfig->serializeToXml()));
	}

	private function cleanXml($xml)
	{
		return str_replace("\n", '', str_replace("\r", '', $xml));
	}
}

?>
