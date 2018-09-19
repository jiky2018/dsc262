<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function createObjectDir($ossClient, $bucket)
{
	try {
		$ossClient->createObjectDir($bucket, 'dir');
	}
	catch (\OSS\Core\OssException $e) {
		printf('createObjectDir' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('createObjectDir' . ': OK' . "\n");
}

function putObject($ossClient, $bucket)
{
	$object = 'oss-php-sdk-test/upload-test-object-name.txt';
	$content = file_get_contents(__FILE__);
	$options = array();

	try {
		$ossClient->putObject($bucket, $object, $content, $options);
	}
	catch (\OSS\Core\OssException $e) {
		printf('putObject' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('putObject' . ': OK' . "\n");
}

function uploadFile($ossClient, $bucket)
{
	$object = 'oss-php-sdk-test/upload-test-object-name.txt';
	$filePath = __FILE__;
	$options = array();

	try {
		$ossClient->uploadFile($bucket, $object, $filePath, $options);
	}
	catch (\OSS\Core\OssException $e) {
		printf('uploadFile' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('uploadFile' . ': OK' . "\n");
}

function listObjects($ossClient, $bucket)
{
	$prefix = 'oss-php-sdk-test/';
	$delimiter = '/';
	$nextMarker = '';
	$maxkeys = 1000;
	$options = array('delimiter' => $delimiter, 'prefix' => $prefix, 'max-keys' => $maxkeys, 'marker' => $nextMarker);

	try {
		$listObjectInfo = $ossClient->listObjects($bucket, $options);
	}
	catch (\OSS\Core\OssException $e) {
		printf('listObjects' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('listObjects' . ': OK' . "\n");
	$objectList = $listObjectInfo->getObjectList();
	$prefixList = $listObjectInfo->getPrefixList();

	if (!empty($objectList)) {
		print("objectList:\n");

		foreach ($objectList as $objectInfo) {
			print($objectInfo->getKey() . "\n");
		}
	}

	if (!empty($prefixList)) {
		print("prefixList: \n");

		foreach ($prefixList as $prefixInfo) {
			print($prefixInfo->getPrefix() . "\n");
		}
	}
}

function listAllObjects($ossClient, $bucket)
{
	for ($i = 0; $i < 100; $i += 1) {
		$ossClient->putObject($bucket, 'dir/obj' . strval($i), 'hi');
		$ossClient->createObjectDir($bucket, 'dir/obj' . strval($i));
	}

	$prefix = 'dir/';
	$delimiter = '/';
	$nextMarker = '';
	$maxkeys = 30;

	while (true) {
		$options = array('delimiter' => $delimiter, 'prefix' => $prefix, 'max-keys' => $maxkeys, 'marker' => $nextMarker);
		var_dump($options);

		try {
			$listObjectInfo = $ossClient->listObjects($bucket, $options);
		}
		catch (\OSS\Core\OssException $e) {
			printf('listAllObjects' . ": FAILED\n");
			printf($e->getMessage() . "\n");
			return NULL;
		}

		$nextMarker = $listObjectInfo->getNextMarker();
		$listObject = $listObjectInfo->getObjectList();
		$listPrefix = $listObjectInfo->getPrefixList();
		var_dump(count($listObject));
		var_dump(count($listPrefix));

		if ($nextMarker === '') {
			break;
		}
	}
}

function getObject($ossClient, $bucket)
{
	$object = 'oss-php-sdk-test/upload-test-object-name.txt';
	$options = array();

	try {
		$content = $ossClient->getObject($bucket, $object, $options);
	}
	catch (\OSS\Core\OssException $e) {
		printf('getObject' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getObject' . ': OK' . "\n");

	if (file_get_contents(__FILE__) === $content) {
		print('getObject' . ': FileContent checked OK' . "\n");
	}
	else {
		print('getObject' . ': FileContent checked FAILED' . "\n");
	}
}

function getObjectToLocalFile($ossClient, $bucket)
{
	$object = 'oss-php-sdk-test/upload-test-object-name.txt';
	$localfile = 'upload-test-object-name.txt';
	$options = array(\OSS\OssClient::OSS_FILE_DOWNLOAD => $localfile);

	try {
		$ossClient->getObject($bucket, $object, $options);
	}
	catch (\OSS\Core\OssException $e) {
		printf('getObjectToLocalFile' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getObjectToLocalFile' . ': OK, please check localfile: \'upload-test-object-name.txt\'' . "\n");

	if (file_get_contents($localfile) === file_get_contents(__FILE__)) {
		print('getObjectToLocalFile' . ': FileContent checked OK' . "\n");
	}
	else {
		print('getObjectToLocalFile' . ': FileContent checked FAILED' . "\n");
	}

	if (file_exists($localfile)) {
		unlink($localfile);
	}
}

function copyObject($ossClient, $bucket)
{
	$fromBucket = $bucket;
	$fromObject = 'oss-php-sdk-test/upload-test-object-name.txt';
	$toBucket = $bucket;
	$toObject = $fromObject . '.copy';
	$options = array();

	try {
		$ossClient->copyObject($fromBucket, $fromObject, $toBucket, $toObject, $options);
	}
	catch (\OSS\Core\OssException $e) {
		printf('copyObject' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('copyObject' . ': OK' . "\n");
}

function modifyMetaForObject($ossClient, $bucket)
{
	$fromBucket = $bucket;
	$fromObject = 'oss-php-sdk-test/upload-test-object-name.txt';
	$toBucket = $bucket;
	$toObject = $fromObject;
	$copyOptions = array(
		\OSS\OssClient::OSS_HEADERS => array('Cache-Control' => 'max-age=60', 'Content-Disposition' => 'attachment; filename="xxxxxx"')
		);

	try {
		$ossClient->copyObject($fromBucket, $fromObject, $toBucket, $toObject, $copyOptions);
	}
	catch (\OSS\Core\OssException $e) {
		printf('modifyMetaForObject' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('modifyMetaForObject' . ': OK' . "\n");
}

function getObjectMeta($ossClient, $bucket)
{
	$object = 'oss-php-sdk-test/upload-test-object-name.txt';

	try {
		$objectMeta = $ossClient->getObjectMeta($bucket, $object);
	}
	catch (\OSS\Core\OssException $e) {
		printf('getObjectMeta' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getObjectMeta' . ': OK' . "\n");
	if (isset($objectMeta[strtolower('Content-Disposition')]) && ('attachment; filename="xxxxxx"' === $objectMeta[strtolower('Content-Disposition')])) {
		print('getObjectMeta' . ': ObjectMeta checked OK' . "\n");
	}
	else {
		print('getObjectMeta' . ': ObjectMeta checked FAILED' . "\n");
	}
}

function deleteObject($ossClient, $bucket)
{
	$object = 'oss-php-sdk-test/upload-test-object-name.txt';

	try {
		$ossClient->deleteObject($bucket, $object);
	}
	catch (\OSS\Core\OssException $e) {
		printf('deleteObject' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('deleteObject' . ': OK' . "\n");
}

function deleteObjects($ossClient, $bucket)
{
	$objects = array();
	$objects[] = 'oss-php-sdk-test/upload-test-object-name.txt';
	$objects[] = 'oss-php-sdk-test/upload-test-object-name.txt.copy';

	try {
		$ossClient->deleteObjects($bucket, $objects);
	}
	catch (\OSS\Core\OssException $e) {
		printf('deleteObjects' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('deleteObjects' . ': OK' . "\n");
}

function doesObjectExist($ossClient, $bucket)
{
	$object = 'oss-php-sdk-test/upload-test-object-name.txt';

	try {
		$exist = $ossClient->doesObjectExist($bucket, $object);
	}
	catch (\OSS\Core\OssException $e) {
		printf('doesObjectExist' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('doesObjectExist' . ': OK' . "\n");
	var_dump($exist);
}

require_once __DIR__ . '/Common.php';
$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();

if (is_null($ossClient)) {
	exit(1);
}

$ossClient->putObject($bucket, 'b.file', 'hi, oss');
Common::println('b.file is created');
$ossClient->uploadFile($bucket, 'c.file', __FILE__);
Common::println('c.file is created');
$content = $ossClient->getObject($bucket, 'b.file');
Common::println('b.file is fetched, the content is: ' . $content);
$options = array(\OSS\OssClient::OSS_FILE_DOWNLOAD => './c.file.localcopy');
$ossClient->getObject($bucket, 'c.file', $options);
Common::println('b.file is fetched to the local file: c.file.localcopy');
$ossClient->copyObject($bucket, 'c.file', $bucket, 'c.file.copy');
Common::println('c.file is copied to c.file.copy');
$doesExist = $ossClient->doesObjectExist($bucket, 'c.file.copy');
Common::println('file c.file.copy exist? ' . ($doesExist ? 'yes' : 'no'));
$ossClient->deleteObject($bucket, 'c.file.copy');
Common::println('c.file.copy is deleted');
$doesExist = $ossClient->doesObjectExist($bucket, 'c.file.copy');
Common::println('file c.file.copy exist? ' . ($doesExist ? 'yes' : 'no'));
$ossClient->deleteObjects($bucket, array('b.file', 'c.file'));
Common::println('b.file, c.file are deleted');
sleep(2);
unlink('c.file.localcopy');
listObjects($ossClient, $bucket);
listAllObjects($ossClient, $bucket);
createObjectDir($ossClient, $bucket);
putObject($ossClient, $bucket);
uploadFile($ossClient, $bucket);
getObject($ossClient, $bucket);
getObjectToLocalFile($ossClient, $bucket);
copyObject($ossClient, $bucket);
modifyMetaForObject($ossClient, $bucket);
getObjectMeta($ossClient, $bucket);
deleteObject($ossClient, $bucket);
deleteObjects($ossClient, $bucket);
doesObjectExist($ossClient, $bucket);

?>
