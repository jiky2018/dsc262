<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function putBucketLogging($ossClient, $bucket)
{
	$option = array();
	$targetBucket = $bucket;
	$targetPrefix = 'access.log';

	try {
		$ossClient->putBucketLogging($bucket, $targetBucket, $targetPrefix, $option);
	}
	catch (\OSS\Core\OssException $e) {
		printf('putBucketLogging' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('putBucketLogging' . ': OK' . "\n");
}

function getBucketLogging($ossClient, $bucket)
{
	$loggingConfig = NULL;
	$options = array();

	try {
		$loggingConfig = $ossClient->getBucketLogging($bucket, $options);
	}
	catch (\OSS\Core\OssException $e) {
		printf('getBucketLogging' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getBucketLogging' . ': OK' . "\n");
	print($loggingConfig->serializeToXml() . "\n");
}

function deleteBucketLogging($ossClient, $bucket)
{
	try {
		$ossClient->deleteBucketLogging($bucket);
	}
	catch (\OSS\Core\OssException $e) {
		printf('deleteBucketLogging' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('deleteBucketLogging' . ': OK' . "\n");
}

require_once __DIR__ . '/Common.php';
$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();

if (is_null($ossClient)) {
	exit(1);
}

$ossClient->putBucketLogging($bucket, $bucket, 'access.log', array());
Common::println('bucket ' . $bucket . ' lifecycleConfig created');
$loggingConfig = $ossClient->getBucketLogging($bucket, array());
Common::println('bucket ' . $bucket . ' lifecycleConfig fetched:' . $loggingConfig->serializeToXml());
$loggingConfig = $ossClient->getBucketLogging($bucket, array());
Common::println('bucket ' . $bucket . ' lifecycleConfig deleted');
putBucketLogging($ossClient, $bucket);
getBucketLogging($ossClient, $bucket);
deleteBucketLogging($ossClient, $bucket);
getBucketLogging($ossClient, $bucket);

?>
