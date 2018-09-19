<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';
class OssClientBucketWebsiteTest extends TestOssClientBase
{
	public function testBucket()
	{
		$websiteConfig = new \OSS\Model\WebsiteConfig('index.html', 'error.html');

		try {
			$this->ossClient->putBucketWebsite($this->bucket, $websiteConfig);
		}
		catch (\OSS\Core\OssException $e) {
			var_dump($e->getMessage());
			$this->assertTrue(false);
		}

		try {
			sleep(2);
			$websiteConfig2 = $this->ossClient->getBucketWebsite($this->bucket);
			$this->assertEquals($websiteConfig->serializeToXml(), $websiteConfig2->serializeToXml());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}

		try {
			$this->ossClient->deleteBucketWebsite($this->bucket);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}

		try {
			sleep(1);
			$websiteConfig3 = $this->ossClient->getBucketLogging($this->bucket);
			$this->assertNotEquals($websiteConfig->serializeToXml(), $websiteConfig3->serializeToXml());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}
	}
}

?>
