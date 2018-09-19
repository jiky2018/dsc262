<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';
class OssClientBucketTest extends TestOssClientBase
{
	public function testBucketWithInvalidName()
	{
		try {
			$this->ossClient->createBucket('s');
			$this->assertFalse(true);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('"s"bucket name is invalid', $e->getMessage());
		}
	}

	public function testBucketWithInvalidACL()
	{
		try {
			$this->ossClient->createBucket($this->bucket, 'invalid');
			$this->assertFalse(true);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('invalid:acl is invalid(private,public-read,public-read-write)', $e->getMessage());
		}
	}

	public function testBucket()
	{
		$this->ossClient->createBucket($this->bucket, \OSS\OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
		$bucketListInfo = $this->ossClient->listBuckets();
		$this->assertNotNull($bucketListInfo);
		$bucketList = $bucketListInfo->getBucketList();
		$this->assertTrue(is_array($bucketList));
		$this->assertGreaterThan(0, count($bucketList));
		$this->ossClient->putBucketAcl($this->bucket, \OSS\OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
		$this->assertEquals($this->ossClient->getBucketAcl($this->bucket), \OSS\OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
		$this->assertTrue($this->ossClient->doesBucketExist($this->bucket));
		$this->assertFalse($this->ossClient->doesBucketExist($this->bucket . '-notexist'));

		try {
			$this->ossClient->deleteBucket($this->bucket);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('BucketNotEmpty', $e->getErrorCode());
			$this->assertEquals('409', $e->getHTTPStatus());
		}
	}
}

?>
