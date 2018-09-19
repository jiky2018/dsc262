<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class CnameConfigTest extends \PHPUnit_Framework_TestCase
{
	private $xml1 = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<BucketCnameConfiguration>\n  <Cname>\n    <Domain>www.foo.com</Domain>\n    <Status>enabled</Status>\n    <LastModified>20150101</LastModified>\n  </Cname>\n  <Cname>\n    <Domain>bar.com</Domain>\n    <Status>disabled</Status>\n    <LastModified>20160101</LastModified>\n  </Cname>\n</BucketCnameConfiguration>";

	public function testFromXml()
	{
		$cnameConfig = new \OSS\Model\CnameConfig();
		$cnameConfig->parseFromXml($this->xml1);
		$cnames = $cnameConfig->getCnames();
		$this->assertEquals(2, count($cnames));
		$this->assertEquals('www.foo.com', $cnames[0]['Domain']);
		$this->assertEquals('enabled', $cnames[0]['Status']);
		$this->assertEquals('20150101', $cnames[0]['LastModified']);
		$this->assertEquals('bar.com', $cnames[1]['Domain']);
		$this->assertEquals('disabled', $cnames[1]['Status']);
		$this->assertEquals('20160101', $cnames[1]['LastModified']);
	}

	public function testToXml()
	{
		$cnameConfig = new \OSS\Model\CnameConfig();
		$cnameConfig->addCname('www.foo.com');
		$cnameConfig->addCname('bar.com');
		$xml = $cnameConfig->serializeToXml();
		$comp = new \OSS\Model\CnameConfig();
		$comp->parseFromXml($xml);
		$cnames1 = $cnameConfig->getCnames();
		$cnames2 = $comp->getCnames();
		$this->assertEquals(count($cnames1), count($cnames2));
		$this->assertEquals(count($cnames1[0]), count($cnames2[0]));
		$this->assertEquals(1, count($cnames1[0]));
		$this->assertEquals($cnames1[0]['Domain'], $cnames2[0]['Domain']);
	}

	public function testCnameNumberLimit()
	{
		$cnameConfig = new \OSS\Model\CnameConfig();

		for ($i = 0; $i < \OSS\Model\CnameConfig::OSS_MAX_RULES; $i += 1) {
			$cnameConfig->addCname(strval($i) . '.foo.com');
		}

		try {
			$cnameConfig->addCname('www.foo.com');
			$this->assertFalse(true);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals($e->getMessage(), 'num of cname in the config exceeds self::OSS_MAX_RULES: ' . strval(\OSS\Model\CnameConfig::OSS_MAX_RULES));
		}
	}
}

?>
