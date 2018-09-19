<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function getSignedUrlForGettingObject($ossClient, $bucket)
{
	$object = 'test/test-signature-test-upload-and-download.txt';
	$timeout = 3600;

	try {
		$signedUrl = $ossClient->signUrl($bucket, $object, $timeout);
	}
	catch (\OSS\Core\OssException $e) {
		printf('getSignedUrlForGettingObject' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getSignedUrlForGettingObject' . ': signedUrl: ' . $signedUrl . "\n");
	$request = new \OSS\Http\RequestCore($signedUrl);
	$request->set_method('GET');
	$request->add_header('Content-Type', '');
	$request->send_request();
	$res = new \OSS\Http\ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());

	if ($res->isOK()) {
		print('getSignedUrlForGettingObject' . ': OK' . "\n");
	}
	else {
		print('getSignedUrlForGettingObject' . ': FAILED' . "\n");
	}
}

function getSignedUrlForPuttingObject($ossClient, $bucket)
{
	$object = 'test/test-signature-test-upload-and-download.txt';
	$timeout = 3600;
	$options = NULL;

	try {
		$signedUrl = $ossClient->signUrl($bucket, $object, $timeout, 'PUT');
	}
	catch (\OSS\Core\OssException $e) {
		printf('getSignedUrlForPuttingObject' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getSignedUrlForPuttingObject' . ': signedUrl: ' . $signedUrl . "\n");
	$content = file_get_contents(__FILE__);
	$request = new \OSS\Http\RequestCore($signedUrl);
	$request->set_method('PUT');
	$request->add_header('Content-Type', '');
	$request->add_header('Content-Length', strlen($content));
	$request->set_body($content);
	$request->send_request();
	$res = new \OSS\Http\ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());

	if ($res->isOK()) {
		print('getSignedUrlForPuttingObject' . ': OK' . "\n");
	}
	else {
		print('getSignedUrlForPuttingObject' . ': FAILED' . "\n");
	}
}

function getSignedUrlForPuttingObjectFromFile($ossClient, $bucket)
{
	$file = __FILE__;
	$object = 'test/test-signature-test-upload-and-download.txt';
	$timeout = 3600;
	$options = array('Content-Type' => 'txt');

	try {
		$signedUrl = $ossClient->signUrl($bucket, $object, $timeout, 'PUT', $options);
	}
	catch (\OSS\Core\OssException $e) {
		printf('getSignedUrlForPuttingObjectFromFile' . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return NULL;
	}

	print('getSignedUrlForPuttingObjectFromFile' . ': signedUrl: ' . $signedUrl . "\n");
	$request = new \OSS\Http\RequestCore($signedUrl);
	$request->set_method('PUT');
	$request->add_header('Content-Type', 'txt');
	$request->set_read_file($file);
	$request->set_read_stream_size(filesize($file));
	$request->send_request();
	$res = new \OSS\Http\ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());

	if ($res->isOK()) {
		print('getSignedUrlForPuttingObjectFromFile' . ': OK' . "\n");
	}
	else {
		print('getSignedUrlForPuttingObjectFromFile' . ': FAILED' . "\n");
	}
}

require_once __DIR__ . '/Common.php';
$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();

if (is_null($ossClient)) {
	exit(1);
}

$ossClient->uploadFile($bucket, 'a.file', __FILE__);
$signedUrl = $ossClient->signUrl($bucket, 'a.file', 3600);
Common::println($signedUrl);
$signedUrl = $ossClient->signUrl($bucket, 'a.file', '3600', 'PUT');
Common::println($signedUrl);
$signedUrl = $ossClient->signUrl($bucket, 'a.file', 3600, 'PUT', array('Content-Type' => 'txt'));
Common::println($signedUrl);
getSignedUrlForPuttingObject($ossClient, $bucket);
getSignedUrlForPuttingObjectFromFile($ossClient, $bucket);
getSignedUrlForGettingObject($ossClient, $bucket);

?>
