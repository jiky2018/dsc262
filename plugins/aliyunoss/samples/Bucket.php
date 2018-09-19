<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function createBucket($ossClient, $bucket)
{
	try {
		$ossClient->createBucket($bucket, \OSS\OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
	}
	catch (\OSS\Core\OssException $e) {
		printf('createBucket' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('createBucket' . ': OK' . "\n");
}

function doesBucketExist($ossClient, $bucket)
{
	try {
		$res = $ossClient->doesBucketExist($bucket);
	}
	catch (\OSS\Core\OssException $e) {
		printf('doesBucketExist' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	if ($res === true) {
		print('doesBucketExist' . ': OK' . "\n");
	}
	else {
		print('doesBucketExist' . ': FAILED' . "\n");
	}
}

function deleteBucket($ossClient, $bucket)
{
	try {
		$ossClient->deleteBucket($bucket);
	}
	catch (\OSS\Core\OssException $e) {
		printf('deleteBucket' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('deleteBucket' . ': OK' . "\n");
}

function putBucketAcl($ossClient, $bucket)
{
	$acl = \OSS\OssClient::OSS_ACL_TYPE_PRIVATE;

	try {
		$ossClient->putBucketAcl($bucket, $acl);
	}
	catch (\OSS\Core\OssException $e) {
		printf('putBucketAcl' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('putBucketAcl' . ': OK' . "\n");
}

function getBucketAcl($ossClient, $bucket)
{
	try {
		$res = $ossClient->getBucketAcl($bucket);
	}
	catch (\OSS\Core\OssException $e) {
		printf('getBucketAcl' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getBucketAcl' . ': OK' . "\n");
	print('acl: ' . $res);
}

function listBuckets($ossClient)
{
	$bucketList = NULL;

	try {
		$bucketListInfo = $ossClient->listBuckets();
	}
	catch (\OSS\Core\OssException $e) {
		printf('listBuckets' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('listBuckets' . ': OK' . "\n");
	$bucketList = $bucketListInfo->getBucketList();

	foreach ($bucketList as $bucket) {
		print($bucket->getLocation() . '	' . $bucket->getName() . '	' . $bucket->getCreatedate() . "\n");
	}
}

require_once __DIR__ . '/Common.php';
$ossClient = Common::getOssClient();

if (is_null($ossClient)) {
	exit(1);
}

$bucket = Common::getBucketName();
$ossClient->createBucket($bucket, \OSS\OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
Common::println('bucket ' . $bucket . ' created');
$doesExist = $ossClient->doesBucketExist($bucket);
Common::println('bucket ' . $bucket . ' exist? ' . ($doesExist ? 'yes' : 'no'));
$bucketListInfo = $ossClient->listBuckets();
$ossClient->putBucketAcl($bucket, \OSS\OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
Common::println('bucket ' . $bucket . ' acl put');
$acl = $ossClient->getBucketAcl($bucket);
Common::println('bucket ' . $bucket . ' acl get: ' . $acl);
createBucket($ossClient, $bucket);
doesBucketExist($ossClient, $bucket);
deleteBucket($ossClient, $bucket);
putBucketAcl($ossClient, $bucket);
getBucketAcl($ossClient, $bucket);
listBuckets($ossClient);

?>
