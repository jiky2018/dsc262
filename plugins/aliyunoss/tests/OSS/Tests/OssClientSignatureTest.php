<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';
class OssClientSignatureTest extends TestOssClientBase
{
	public function testGetSignedUrlForGettingObject()
	{
		$object = 'a.file';
		$this->ossClient->putObject($this->bucket, $object, file_get_contents(__FILE__));
		$timeout = 3600;

		try {
			$signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertFalse(true);
		}

		sleep(5);
		$request = new \OSS\Http\RequestCore($signedUrl);
		$request->set_method('GET');
		$request->add_header('Content-Type', '');
		$request->send_request();
		$res = new \OSS\Http\ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
		$this->assertEquals(file_get_contents(__FILE__), $res->body);
	}

	public function testGetSignedUrlForPuttingObject()
	{
		$object = 'a.file';
		$timeout = 3600;

		try {
			$signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, 'PUT');
			$content = file_get_contents(__FILE__);
			sleep(5);
			$request = new \OSS\Http\RequestCore($signedUrl);
			$request->set_method('PUT');
			$request->add_header('Content-Type', '');
			$request->add_header('Content-Length', strlen($content));
			$request->set_body($content);
			$request->send_request();
			$res = new \OSS\Http\ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
			$this->assertTrue($res->isOK());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertFalse(true);
		}
	}

	public function testGetSignedUrlForPuttingObjectFromFile()
	{
		$file = __FILE__;
		$object = 'a.file';
		$timeout = 3600;
		$options = array('Content-Type' => 'txt');

		try {
			$signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, 'PUT', $options);
			sleep(5);
			$request = new \OSS\Http\RequestCore($signedUrl);
			$request->set_method('PUT');
			$request->add_header('Content-Type', 'txt');
			$request->set_read_file($file);
			$request->set_read_stream_size(filesize($file));
			$request->send_request();
			$res = new \OSS\Http\ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
			$this->assertTrue($res->isOK());
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertFalse(true);
		}
	}

	public function tearDown()
	{
		$this->ossClient->deleteObject($this->bucket, 'a.file');
	}

	public function setUp()
	{
		parent::setUp();
		$object = 'a.file';
		$content = file_get_contents(__FILE__);
		$options = array(
			\OSS\OssClient::OSS_LENGTH  => strlen($content),
			\OSS\OssClient::OSS_HEADERS => array('Expires' => 'Fri, 28 Feb 2020 05:38:42 GMT', 'Cache-Control' => 'no-cache', 'Content-Disposition' => 'attachment;filename=oss_download.log', 'Content-Encoding' => 'utf-8', 'Content-Language' => 'zh-CN', 'x-oss-server-side-encryption' => 'AES256', 'x-oss-meta-self-define-title' => 'user define meta info')
			);

		try {
			$this->ossClient->putObject($this->bucket, $object, $content, $options);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertFalse(true);
		}
	}
}

?>
