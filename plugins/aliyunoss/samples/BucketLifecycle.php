<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function putBucketLifecycle($ossClient, $bucket)
{
	$lifecycleConfig = new \OSS\Model\LifecycleConfig();
	$actions = array();
	$actions[] = new \OSS\Model\LifecycleAction(\OSS\OssClient::OSS_LIFECYCLE_EXPIRATION, \OSS\OssClient::OSS_LIFECYCLE_TIMING_DAYS, 3);
	$lifecycleRule = new \OSS\Model\LifecycleRule('delete obsoleted files', 'obsoleted/', 'Enabled', $actions);
	$lifecycleConfig->addRule($lifecycleRule);
	$actions = array();
	$actions[] = new \OSS\Model\LifecycleAction(\OSS\OssClient::OSS_LIFECYCLE_EXPIRATION, \OSS\OssClient::OSS_LIFECYCLE_TIMING_DATE, '2022-10-12T00:00:00.000Z');
	$lifecycleRule = new \OSS\Model\LifecycleRule('delete temporary files', 'temporary/', 'Enabled', $actions);
	$lifecycleConfig->addRule($lifecycleRule);

	try {
		$ossClient->putBucketLifecycle($bucket, $lifecycleConfig);
	}
	catch (\OSS\Core\OssException $e) {
		printf('putBucketLifecycle' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('putBucketLifecycle' . ': OK' . "\n");
}

function getBucketLifecycle($ossClient, $bucket)
{
	$lifecycleConfig = NULL;

	try {
		$lifecycleConfig = $ossClient->getBucketLifecycle($bucket);
	}
	catch (\OSS\Core\OssException $e) {
		printf('getBucketLifecycle' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getBucketLifecycle' . ': OK' . "\n");
	print($lifecycleConfig->serializeToXml() . "\n");
}

function deleteBucketLifecycle($ossClient, $bucket)
{
	try {
		$ossClient->deleteBucketLifecycle($bucket);
	}
	catch (\OSS\Core\OssException $e) {
		printf('deleteBucketLifecycle' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('deleteBucketLifecycle' . ': OK' . "\n");
}

require_once __DIR__ . '/Common.php';
$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();

if (is_null($ossClient)) {
	exit(1);
}

$lifecycleConfig = new \OSS\Model\LifecycleConfig();
$actions = array();
$actions[] = new \OSS\Model\LifecycleAction('Expiration', 'Days', 3);
$lifecycleRule = new \OSS\Model\LifecycleRule('delete obsoleted files', 'obsoleted/', 'Enabled', $actions);
$lifecycleConfig->addRule($lifecycleRule);
$ossClient->putBucketLifecycle($bucket, $lifecycleConfig);
Common::println('bucket ' . $bucket . ' lifecycleConfig created:' . $lifecycleConfig->serializeToXml());
$lifecycleConfig = $ossClient->getBucketLifecycle($bucket);
Common::println('bucket ' . $bucket . ' lifecycleConfig fetched:' . $lifecycleConfig->serializeToXml());
$ossClient->deleteBucketLifecycle($bucket);
Common::println('bucket ' . $bucket . ' lifecycleConfig deleted');
putBucketLifecycle($ossClient, $bucket);
getBucketLifecycle($ossClient, $bucket);
deleteBucketLifecycle($ossClient, $bucket);
getBucketLifecycle($ossClient, $bucket);

?>
