<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class UploadPartResultTest extends \PHPUnit_Framework_TestCase
{
	private $validHeader = array('etag' => '7265F4D211B56873A381D321F586E4A9');
	private $invalidHeader = array();

	public function testParseValidHeader()
	{
		$response = new \OSS\Http\ResponseCore($this->validHeader, '', 200);
		$result = new \OSS\Result\UploadPartResult($response);
		$eTag = $result->getData();
		$this->assertEquals('7265F4D211B56873A381D321F586E4A9', $eTag);
	}

	public function testParseInvalidHeader()
	{
		$response = new \OSS\Http\ResponseCore($this->invalidHeader, '', 200);

		try {
			new \OSS\Result\UploadPartResult($response);
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('cannot get ETag', $e->getMessage());
		}
	}
}

?>
