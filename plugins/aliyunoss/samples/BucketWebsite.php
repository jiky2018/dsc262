<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function putBucketWebsite($ossClient, $bucket)
{
	$websiteConfig = new \OSS\Model\WebsiteConfig('index.html', 'error.html');

	try {
		$ossClient->putBucketWebsite($bucket, $websiteConfig);
	}
	catch (\OSS\Core\OssException $e) {
		printf('putBucketWebsite' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('putBucketWebsite' . ': OK' . "\n");
}

function getBucketWebsite($ossClient, $bucket)
{
	$websiteConfig = NULL;

	try {
		$websiteConfig = $ossClient->getBucketWebsite($bucket);
	}
	catch (\OSS\Core\OssException $e) {
		printf('getBucketWebsite' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getBucketWebsite' . ': OK' . "\n");
	print($websiteConfig->serializeToXml() . "\n");
}

function deleteBucketWebsite($ossClient, $bucket)
{
	try {
		$ossClient->deleteBucketWebsite($bucket);
	}
	catch (\OSS\Core\OssException $e) {
		printf('deleteBucketWebsite' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('deleteBucketWebsite' . ': OK' . "\n");
}

require_once __DIR__ . '/Common.php';
$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();

if (is_null($ossClient)) {
	exit(1);
}

$websiteConfig = new \OSS\Model\WebsiteConfig('index.html', 'error.html');
$ossClient->putBucketWebsite($bucket, $websiteConfig);
Common::println('bucket ' . $bucket . ' websiteConfig created:' . $websiteConfig->serializeToXml());
$websiteConfig = $ossClient->getBucketWebsite($bucket);
Common::println('bucket ' . $bucket . ' websiteConfig fetched:' . $websiteConfig->serializeToXml());
$ossClient->deleteBucketWebsite($bucket);
Common::println('bucket ' . $bucket . ' websiteConfig deleted');
putBucketWebsite($ossClient, $bucket);
getBucketWebsite($ossClient, $bucket);
deleteBucketWebsite($ossClient, $bucket);
getBucketWebsite($ossClient, $bucket);

?>
