<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class InitiateMultipartUploadResultTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<InitiateMultipartUploadResult xmlns=\"http://doc.oss-cn-hangzhou.aliyuncs.com\">\n    <Bucket> multipart_upload</Bucket>\n    <Key>multipart.data</Key>\n    <UploadId>0004B9894A22E5B1888A1E29F8236E2D</UploadId>\n</InitiateMultipartUploadResult>";
	private $invalidXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<InitiateMultipartUploadResult xmlns=\"http://doc.oss-cn-hangzhou.aliyuncs.com\">\n    <Bucket> multipart_upload</Bucket>\n    <Key>multipart.data</Key>\n</InitiateMultipartUploadResult>";

	public function testParseValidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 200);
		$result = new \OSS\Result\InitiateMultipartUploadResult($response);
		$this->assertEquals('0004B9894A22E5B1888A1E29F8236E2D', $result->getData());
	}

	public function testParseInvalidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->invalidXml, 200);

		try {
			$result = new \OSS\Result\InitiateMultipartUploadResult($response);
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
		}
	}
}

?>
