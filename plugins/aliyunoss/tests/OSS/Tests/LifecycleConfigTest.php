<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class LifecycleConfigTest extends \PHPUnit_Framework_TestCase
{
	private $validLifecycle = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<LifecycleConfiguration>\n<Rule>\n<ID>delete obsoleted files</ID>\n<Prefix>obsoleted/</Prefix>\n<Status>Enabled</Status>\n<Expiration><Days>3</Days></Expiration>\n</Rule>\n<Rule>\n<ID>delete temporary files</ID>\n<Prefix>temporary/</Prefix>\n<Status>Enabled</Status>\n<Expiration><Date>2022-10-12T00:00:00.000Z</Date></Expiration>\n<Expiration2><Date>2022-10-12T00:00:00.000Z</Date></Expiration2>\n</Rule>\n</LifecycleConfiguration>";
	private $validLifecycle2 = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<LifecycleConfiguration>\n<Rule><ID>delete temporary files</ID>\n<Prefix>temporary/</Prefix>\n<Status>Enabled</Status>\n<Expiration><Date>2022-10-12T00:00:00.000Z</Date></Expiration>\n<Expiration2><Date>2022-10-12T00:00:00.000Z</Date></Expiration2>\n</Rule>\n</LifecycleConfiguration>";
	private $nullLifecycle = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<LifecycleConfiguration/>";

	public function testConstructValidConfig()
	{
		$lifecycleConfig = new \OSS\Model\LifecycleConfig();
		$actions = array();
		$actions[] = new \OSS\Model\LifecycleAction('Expiration', 'Days', 3);
		$lifecycleRule = new \OSS\Model\LifecycleRule('delete obsoleted files', 'obsoleted/', 'Enabled', $actions);
		$lifecycleConfig->addRule($lifecycleRule);
		$actions = array();
		$actions[] = new \OSS\Model\LifecycleAction('Expiration', 'Date', '2022-10-12T00:00:00.000Z');
		$actions[] = new \OSS\Model\LifecycleAction('Expiration2', 'Date', '2022-10-12T00:00:00.000Z');
		$lifecycleRule = new \OSS\Model\LifecycleRule('delete temporary files', 'temporary/', 'Enabled', $actions);
		$lifecycleConfig->addRule($lifecycleRule);

		try {
			$lifecycleConfig->addRule(null);
			$this->assertFalse(true);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('lifecycleRule is null', $e->getMessage());
		}

		$this->assertEquals($this->cleanXml(strval($lifecycleConfig)), $this->cleanXml($this->validLifecycle));
	}

	public function testParseValidXml()
	{
		$lifecycleConfig = new \OSS\Model\LifecycleConfig();
		$lifecycleConfig->parseFromXml($this->validLifecycle);
		$this->assertEquals($this->cleanXml($lifecycleConfig->serializeToXml()), $this->cleanXml($this->validLifecycle));
		$this->assertEquals(2, count($lifecycleConfig->getRules()));
		$rules = $lifecycleConfig->getRules();
		$this->assertEquals('delete temporary files', $rules[1]->getId());
	}

	public function testParseValidXml2()
	{
		$lifecycleConfig = new \OSS\Model\LifecycleConfig();
		$lifecycleConfig->parseFromXml($this->validLifecycle2);
		$this->assertEquals($this->cleanXml($lifecycleConfig->serializeToXml()), $this->cleanXml($this->validLifecycle2));
		$this->assertEquals(1, count($lifecycleConfig->getRules()));
		$rules = $lifecycleConfig->getRules();
		$this->assertEquals('delete temporary files', $rules[0]->getId());
	}

	public function testParseNullXml()
	{
		$lifecycleConfig = new \OSS\Model\LifecycleConfig();
		$lifecycleConfig->parseFromXml($this->nullLifecycle);
		$this->assertEquals($this->cleanXml($lifecycleConfig->serializeToXml()), $this->cleanXml($this->nullLifecycle));
		$this->assertEquals(0, count($lifecycleConfig->getRules()));
	}

	public function testLifecycleRule()
	{
		$lifecycleRule = new \OSS\Model\LifecycleRule('x', 'x', 'x', array('x'));
		$lifecycleRule->setId('id');
		$lifecycleRule->setPrefix('prefix');
		$lifecycleRule->setStatus('Enabled');
		$lifecycleRule->setActions(array());
		$this->assertEquals('id', $lifecycleRule->getId());
		$this->assertEquals('prefix', $lifecycleRule->getPrefix());
		$this->assertEquals('Enabled', $lifecycleRule->getStatus());
		$this->assertEmpty($lifecycleRule->getActions());
	}

	public function testLifecycleAction()
	{
		$action = new \OSS\Model\LifecycleAction('x', 'x', 'x');
		$this->assertEquals($action->getAction(), 'x');
		$this->assertEquals($action->getTimeSpec(), 'x');
		$this->assertEquals($action->getTimeValue(), 'x');
		$action->setAction('y');
		$action->setTimeSpec('y');
		$action->setTimeValue('y');
		$this->assertEquals($action->getAction(), 'y');
		$this->assertEquals($action->getTimeSpec(), 'y');
		$this->assertEquals($action->getTimeValue(), 'y');
	}

	private function cleanXml($xml)
	{
		return str_replace("\n", '', str_replace("\r", '', $xml));
	}
}

?>
