<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function putBucketReferer($ossClient, $bucket)
{
	$refererConfig = new \OSS\Model\RefererConfig();
	$refererConfig->setAllowEmptyReferer(true);
	$refererConfig->addReferer('www.aliiyun.com');
	$refererConfig->addReferer('www.aliiyuncs.com');

	try {
		$ossClient->putBucketReferer($bucket, $refererConfig);
	}
	catch (\OSS\Core\OssException $e) {
		printf('putBucketReferer' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('putBucketReferer' . ': OK' . "\n");
}

function getBucketReferer($ossClient, $bucket)
{
	$refererConfig = NULL;

	try {
		$refererConfig = $ossClient->getBucketReferer($bucket);
	}
	catch (\OSS\Core\OssException $e) {
		printf('getBucketReferer' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getBucketReferer' . ': OK' . "\n");
	print($refererConfig->serializeToXml() . "\n");
}

function deleteBucketReferer($ossClient, $bucket)
{
	$refererConfig = new \OSS\Model\RefererConfig();

	try {
		$ossClient->putBucketReferer($bucket, $refererConfig);
	}
	catch (\OSS\Core\OssException $e) {
		printf('deleteBucketReferer' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('deleteBucketReferer' . ': OK' . "\n");
}

require_once __DIR__ . '/Common.php';
$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();

if (is_null($ossClient)) {
	exit(1);
}

$refererConfig = new \OSS\Model\RefererConfig();
$refererConfig->setAllowEmptyReferer(true);
$refererConfig->addReferer('www.aliiyun.com');
$refererConfig->addReferer('www.aliiyuncs.com');
$ossClient->putBucketReferer($bucket, $refererConfig);
Common::println('bucket ' . $bucket . ' refererConfig created:' . $refererConfig->serializeToXml());
$refererConfig = $ossClient->getBucketReferer($bucket);
Common::println('bucket ' . $bucket . ' refererConfig fetched:' . $refererConfig->serializeToXml());
$refererConfig = new \OSS\Model\RefererConfig();
$ossClient->putBucketReferer($bucket, $refererConfig);
Common::println('bucket ' . $bucket . ' refererConfig deleted');
putBucketReferer($ossClient, $bucket);
getBucketReferer($ossClient, $bucket);
deleteBucketReferer($ossClient, $bucket);
getBucketReferer($ossClient, $bucket);

?>
