<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';
class OssClientBucketLoggingTest extends TestOssClientBase
{
	public function testBucket()
	{
		$loggingConfig = new \OSS\Model\LoggingConfig($this->bucket, 'prefix');

		try {
			$this->ossClient->putBucketLogging($this->bucket, $this->bucket, 'prefix');
		}
		catch (\OSS\Core\OssException $e) {
			var_dump($e->getMessage());
			$this->assertTrue(false);
		}

		try {
			sleep(2);
			$loggingConfig2 = $this->ossClient->getBucketLogging($this->bucket);
			$this->assertEquals($loggingConfig->serializeToXml(), $loggingConfig2->serializeToXml());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}

		try {
			$this->ossClient->deleteBucketLogging($this->bucket);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}

		try {
			sleep(3);
			$loggingConfig3 = $this->ossClient->getBucketLogging($this->bucket);
			$this->assertNotEquals($loggingConfig->serializeToXml(), $loggingConfig3->serializeToXml());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}
	}
}

?>
