<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class Common
{
	const endpoint = Config::OSS_ENDPOINT;
	const accessKeyId = Config::OSS_ACCESS_ID;
	const accessKeySecret = Config::OSS_ACCESS_KEY;
	const bucket = Config::OSS_TEST_BUCKET;

	static public function getOssClient()
	{
		try {
			$ossClient = new \OSS\OssClient(self::accessKeyId, self::accessKeySecret, self::endpoint, false);
		}
		catch (\OSS\Core\OssException $e) {
			printf('getOssClient' . "creating OssClient instance: FAILED\n");
			printf($e->getMessage() . "\n");
			return NULL;
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
			$message = $e->getMessage();

			if (\OSS\Core\OssUtil::startsWith($message, 'http status: 403')) {
				echo 'Please Check your AccessKeyId and AccessKeySecret' . "\n";
				exit(0);
			}
			else if (strpos($message, 'BucketAlreadyExists') !== false) {
				echo 'Bucket already exists. Please check whether the bucket belongs to you, or it was visited with correct endpoint. ' . "\n";
				exit(0);
			}

			printf('createBucket' . ": FAILED\n");
			printf($e->getMessage() . "\n");
			return NULL;
		}

		print('createBucket' . ': OK' . "\n");
	}

	static public function println($message)
	{
		if (!empty($message)) {
			echo strval($message) . "\n";
		}
	}
}

if (is_file(__DIR__ . '/../autoload.php')) {
	require_once __DIR__ . '/../autoload.php';
}

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
	require_once __DIR__ . '/../vendor/autoload.php';
}

require_once __DIR__ . '/Config.php';
Common::createBucket();

?>
