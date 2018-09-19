<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function putBucketCors($ossClient, $bucket)
{
	$corsConfig = new \OSS\Model\CorsConfig();
	$rule = new \OSS\Model\CorsRule();
	$rule->addAllowedHeader('x-oss-header');
	$rule->addAllowedOrigin('http://www.b.com');
	$rule->addAllowedMethod('POST');
	$rule->setMaxAgeSeconds(10);
	$corsConfig->addRule($rule);

	try {
		$ossClient->putBucketCors($bucket, $corsConfig);
	}
	catch (\OSS\Core\OssException $e) {
		printf('putBucketCors' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('putBucketCors' . ': OK' . "\n");
}

function getBucketCors($ossClient, $bucket)
{
	$corsConfig = NULL;

	try {
		$corsConfig = $ossClient->getBucketCors($bucket);
	}
	catch (\OSS\Core\OssException $e) {
		printf('getBucketCors' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getBucketCors' . ': OK' . "\n");
	print($corsConfig->serializeToXml() . "\n");
}

function deleteBucketCors($ossClient, $bucket)
{
	try {
		$ossClient->deleteBucketCors($bucket);
	}
	catch (\OSS\Core\OssException $e) {
		printf('deleteBucketCors' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('deleteBucketCors' . ': OK' . "\n");
}

require_once __DIR__ . '/Common.php';
$ossClient = Common::getOssClient();

if (is_null($ossClient)) {
	exit(1);
}

$bucket = Common::getBucketName();
$corsConfig = new \OSS\Model\CorsConfig();
$rule = new \OSS\Model\CorsRule();
$rule->addAllowedHeader('x-oss-header');
$rule->addAllowedOrigin('http://www.b.com');
$rule->addAllowedMethod('POST');
$rule->setMaxAgeSeconds(10);
$corsConfig->addRule($rule);
$ossClient->putBucketCors($bucket, $corsConfig);
Common::println('bucket ' . $bucket . ' corsConfig created:' . $corsConfig->serializeToXml());
$corsConfig = $ossClient->getBucketCors($bucket);
Common::println('bucket ' . $bucket . ' corsConfig fetched:' . $corsConfig->serializeToXml());
$ossClient->deleteBucketCors($bucket);
Common::println('bucket ' . $bucket . ' corsConfig deleted');
putBucketCors($ossClient, $bucket);
getBucketCors($ossClient, $bucket);
deleteBucketCors($ossClient, $bucket);
getBucketCors($ossClient, $bucket);

?>
