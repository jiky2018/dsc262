<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function multiuploadFile($ossClient, $bucket)
{
	$object = 'test/multipart-test.txt';
	$file = __FILE__;
	$options = array();

	try {
		$ossClient->multiuploadFile($bucket, $object, $file, $options);
	}
	catch (\OSS\Core\OssException $e) {
		printf('multiuploadFile' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('multiuploadFile' . ':  OK' . "\n");
}

function putObjectByRawApis($ossClient, $bucket)
{
	$object = 'test/multipart-test.txt';

	try {
		$uploadId = $ossClient->initiateMultipartUpload($bucket, $object);
	}
	catch (\OSS\Core\OssException $e) {
		printf('putObjectByRawApis' . ": initiateMultipartUpload FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('putObjectByRawApis' . ': initiateMultipartUpload OK' . "\n");
	$partSize = 10 * 1024 * 1024;
	$uploadFile = __FILE__;
	$uploadFileSize = filesize($uploadFile);
	$pieces = $ossClient->generateMultiuploadParts($uploadFileSize, $partSize);
	$responseUploadPart = array();
	$uploadPosition = 0;
	$isCheckMd5 = true;

	foreach ($pieces as $i => $piece) {
		$fromPos = $uploadPosition + (int) $piece[$ossClient::OSS_SEEK_TO];
		$toPos = ((int) $piece[$ossClient::OSS_LENGTH] + $fromPos) - 1;
		$upOptions = array($ossClient::OSS_FILE_UPLOAD => $uploadFile, $ossClient::OSS_PART_NUM => $i + 1, $ossClient::OSS_SEEK_TO => $fromPos, $ossClient::OSS_LENGTH => ($toPos - $fromPos) + 1, $ossClient::OSS_CHECK_MD5 => $isCheckMd5);

		if ($isCheckMd5) {
			$contentMd5 = \OSS\Core\OssUtil::getMd5SumForFile($uploadFile, $fromPos, $toPos);
			$upOptions[$ossClient::OSS_CONTENT_MD5] = $contentMd5;
		}

		try {
			$responseUploadPart[] = $ossClient->uploadPart($bucket, $object, $uploadId, $upOptions);
		}
		catch (\OSS\Core\OssException $e) {
			printf('putObjectByRawApis' . ': initiateMultipartUpload, uploadPart - part#' . $i . " FAILED\n");
			printf($e->getMessage() . "\n");
			return NULL;
		}

		printf('putObjectByRawApis' . ': initiateMultipartUpload, uploadPart - part#' . $i . " OK\n");
	}

	$uploadParts = array();

	foreach ($responseUploadPart as $i => $eTag) {
		$uploadParts[] = array('PartNumber' => $i + 1, 'ETag' => $eTag);
	}

	try {
		$ossClient->completeMultipartUpload($bucket, $object, $uploadId, $uploadParts);
	}
	catch (\OSS\Core\OssException $e) {
		printf('putObjectByRawApis' . ": completeMultipartUpload FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	printf('putObjectByRawApis' . ": completeMultipartUpload OK\n");
}

function uploadDir($ossClient, $bucket)
{
	$localDirectory = '.';
	$prefix = 'samples/codes';

	try {
		$ossClient->uploadDir($bucket, $prefix, $localDirectory);
	}
	catch (\OSS\Core\OssException $e) {
		printf('uploadDir' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	printf('uploadDir' . ": completeMultipartUpload OK\n");
}

function listMultipartUploads($ossClient, $bucket)
{
	$options = array('max-uploads' => 100, 'key-marker' => '', 'prefix' => '', 'upload-id-marker' => '');

	try {
		$listMultipartUploadInfo = $ossClient->listMultipartUploads($bucket, $options);
	}
	catch (\OSS\Core\OssException $e) {
		printf('listMultipartUploads' . ": listMultipartUploads FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	printf('listMultipartUploads' . ": listMultipartUploads OK\n");
	$listUploadInfo = $listMultipartUploadInfo->getUploads();
	var_dump($listUploadInfo);
}

require_once __DIR__ . '/Common.php';
$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();

if (is_null($ossClient)) {
	exit(1);
}

$ossClient->multiuploadFile($bucket, 'file.php', __FILE__, array());
Common::println('local file ' . __FILE__ . ' is uploaded to the bucket ' . $bucket . ', file.php');
$ossClient->uploadDir($bucket, 'targetdir', __DIR__);
Common::println('local dir ' . __DIR__ . ' is uploaded to the bucket ' . $bucket . ', targetdir/');
$listMultipartUploadInfo = $ossClient->listMultipartUploads($bucket, array());
multiuploadFile($ossClient, $bucket);
putObjectByRawApis($ossClient, $bucket);
uploadDir($ossClient, $bucket);
listMultipartUploads($ossClient, $bucket);

?>
