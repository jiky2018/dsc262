<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';
class OssClientBucketRefererTest extends TestOssClientBase
{
	public function testBucket()
	{
		$refererConfig = new \OSS\Model\RefererConfig();
		$refererConfig->addReferer('http://www.aliyun.com');

		try {
			$this->ossClient->putBucketReferer($this->bucket, $refererConfig);
		}
		catch (\OSS\Core\OssException $e) {
			var_dump($e->getMessage());
			$this->assertTrue(false);
		}

		try {
			sleep(5);
			$refererConfig2 = $this->ossClient->getBucketReferer($this->bucket);
			$this->assertEquals($refererConfig->serializeToXml(), $refererConfig2->serializeToXml());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}

		try {
			$nullRefererConfig = new \OSS\Model\RefererConfig();
			$nullRefererConfig->setAllowEmptyReferer(false);
			$this->ossClient->putBucketReferer($this->bucket, $nullRefererConfig);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}

		try {
			sleep(5);
			$refererConfig3 = $this->ossClient->getBucketLogging($this->bucket);
			$this->assertNotEquals($refererConfig->serializeToXml(), $refererConfig3->serializeToXml());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}
	}
}

?>
