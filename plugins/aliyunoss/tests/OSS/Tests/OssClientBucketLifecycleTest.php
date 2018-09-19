<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';
class OssClientBucketLifecycleTest extends TestOssClientBase
{
	public function testBucket()
	{
		$lifecycleConfig = new \OSS\Model\LifecycleConfig();
		$actions = array();
		$actions[] = new \OSS\Model\LifecycleAction('Expiration', 'Days', 3);
		$lifecycleRule = new \OSS\Model\LifecycleRule('delete obsoleted files', 'obsoleted/', 'Enabled', $actions);
		$lifecycleConfig->addRule($lifecycleRule);
		$actions = array();
		$actions[] = new \OSS\Model\LifecycleAction('Expiration', 'Date', '2022-10-12T00:00:00.000Z');
		$lifecycleRule = new \OSS\Model\LifecycleRule('delete temporary files', 'temporary/', 'Enabled', $actions);
		$lifecycleConfig->addRule($lifecycleRule);

		try {
			$this->ossClient->putBucketLifecycle($this->bucket, $lifecycleConfig);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}

		try {
			sleep(5);
			$lifecycleConfig2 = $this->ossClient->getBucketLifecycle($this->bucket);
			$this->assertEquals($lifecycleConfig->serializeToXml(), $lifecycleConfig2->serializeToXml());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}

		try {
			$this->ossClient->deleteBucketLifecycle($this->bucket);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}

		try {
			sleep(3);
			$lifecycleConfig3 = $this->ossClient->getBucketLifecycle($this->bucket);
			$this->assertNotEquals($lifecycleConfig->serializeToXml(), $lifecycleConfig3->serializeToXml());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}
	}
}

?>
