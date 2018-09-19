<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class Common
{
	const endpoint = \OSS\Tests\Config::OSS_ENDPOINT;
	const accessKeyId = \OSS\Tests\Config::OSS_ACCESS_ID;
	const accessKeySecret = \OSS\Tests\Config::OSS_ACCESS_KEY;
	const bucket = \OSS\Tests\Config::OSS_TEST_BUCKET;

	static public function getOssClient()
	{
		try {
			$ossClient = new \OSS\OssClient(self::accessKeyId, self::accessKeySecret, self::endpoint, false);
		}
		catch (\OSS\Core\OssException $e) {
			printf('getOssClient' . "creating OssClient instance: FAILED\n");
			printf($e->getMessage() . "\n");
			return null;
		}

		return $ossClient;
	}

	static public function getBucketName()
	{
		return self::bucket;
	}

	static public function createBucket()
	{
		$ossClient = self::getOssClient();

		if (is_null($ossClient)) {
			exit(1);
		}

		$bucket = self::getBucketName();
		$acl = \OSS\OssClient::OSS_ACL_TYPE_PUBLIC_READ;

		try {
			$ossClient->createBucket($bucket, $acl);
		}
		catch (\OSS\Core\OssException $e) {
			printf('createBucket' . ": FAILED\n");
			printf($e->getMessage() . "\n");
			return NULL;
		}

		print('createBucket' . ': OK' . "\n");
	}
}

require_once __DIR__ . '/../../../autoload.php';
require_once __DIR__ . '/Config.php';

?>
