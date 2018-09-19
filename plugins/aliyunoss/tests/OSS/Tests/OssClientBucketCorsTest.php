<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';
class OssClientBucketCorsTest extends TestOssClientBase
{
	public function testBucket()
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
		$rule = new \OSS\Model\CorsRule();
		$rule->addAllowedHeader('x-oss-test');
		$rule->addAllowedMethod('GET');
		$rule->addAllowedOrigin('http://www.b.com');
		$rule->addExposeHeader('x-oss-test1');
		$rule->setMaxAgeSeconds(110);
		$corsConfig->addRule($rule);

		try {
			$this->ossClient->putBucketCors($this->bucket, $corsConfig);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertFalse(True);
		}

		try {
			$object = 'cors/test.txt';
			$this->ossClient->putObject($this->bucket, $object, file_get_contents(__FILE__));
			$headers = $this->ossClient->optionsObject($this->bucket, $object, 'http://www.a.com', 'GET', '', null);
			$this->assertNotEmpty($headers);
		}
		catch (\OSS\Core\OssException $e) {
			var_dump($e->getMessage());
		}

		try {
			sleep(1);
			$corsConfig2 = $this->ossClient->getBucketCors($this->bucket);
			$this->assertNotNull($corsConfig2);
			$this->assertEquals($corsConfig->serializeToXml(), $corsConfig2->serializeToXml());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertFalse(True);
		}

		try {
			$this->ossClient->deleteBucketCors($this->bucket);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertFalse(True);
		}

		try {
			sleep(5);
			$corsConfig3 = $this->ossClient->getBucketCors($this->bucket);
			$this->assertNotNull($corsConfig3);
			$this->assertNotEquals($corsConfig->serializeToXml(), $corsConfig3->serializeToXml());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertFalse(True);
		}
	}
}

?>
