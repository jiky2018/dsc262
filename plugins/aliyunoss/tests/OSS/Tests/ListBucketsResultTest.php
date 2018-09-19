<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class ListBucketsResultTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ListAllMyBucketsResult>\n  <Owner>\n    <ID>ut_test_put_bucket</ID>\n    <DisplayName>ut_test_put_bucket</DisplayName>\n  </Owner>\n  <Buckets>\n    <Bucket>\n      <Location>oss-cn-hangzhou-a</Location>\n      <Name>xz02tphky6fjfiuc0</Name>\n      <CreationDate>2014-05-15T11:18:32.000Z</CreationDate>\n    </Bucket>\n    <Bucket>\n      <Location>oss-cn-hangzhou-a</Location>\n      <Name>xz02tphky6fjfiuc1</Name>\n      <CreationDate>2014-05-15T11:18:32.000Z</CreationDate>\n    </Bucket>\n  </Buckets>\n</ListAllMyBucketsResult>";
	private $nullXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ListAllMyBucketsResult>\n  <Owner>\n    <ID>ut_test_put_bucket</ID>\n    <DisplayName>ut_test_put_bucket</DisplayName>\n  </Owner>\n  <Buckets>\n  </Buckets>\n</ListAllMyBucketsResult>";

	public function testParseValidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 200);
		$result = new \OSS\Result\ListBucketsResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$bucketListInfo = $result->getData();
		$this->assertEquals(2, count($bucketListInfo->getBucketList()));
	}

	public function testParseNullXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->nullXml, 200);
		$result = new \OSS\Result\ListBucketsResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$bucketListInfo = $result->getData();
		$this->assertEquals(0, count($bucketListInfo->getBucketList()));
	}

	public function test403()
	{
		$errorHeader = array('x-oss-request-id' => '1a2b-3c4d');
		$errorBody = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Error>\n  <Code>NoSuchBucket</Code>\n  <Message>The specified bucket does not exist.</Message>\n  <RequestId>566B870D207FB3044302EB0A</RequestId>\n  <HostId>hello.oss-test.aliyun-inc.com</HostId>\n  <BucketName>hello</BucketName>\n</Error>";
		$response = new \OSS\Http\ResponseCore($errorHeader, $errorBody, 403);

		try {
			new \OSS\Result\ListBucketsResult($response);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals($e->getMessage(), 'NoSuchBucket: The specified bucket does not exist. RequestId: 1a2b-3c4d');
			$this->assertEquals($e->getHTTPStatus(), '403');
			$this->assertEquals($e->getRequestId(), '1a2b-3c4d');
			$this->assertEquals($e->getErrorCode(), 'NoSuchBucket');
			$this->assertEquals($e->getErrorMessage(), 'The specified bucket does not exist.');
			$this->assertEquals($e->getDetails(), $errorBody);
		}
	}
}

?>
