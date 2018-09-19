<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class ListObjectsResultTest extends \PHPUnit_Framework_TestCase
{
	private $validXml1 = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ListBucketResult>\n  <Name>testbucket-hf</Name>\n  <Prefix></Prefix>\n  <Marker></Marker>\n  <MaxKeys>1000</MaxKeys>\n  <Delimiter>/</Delimiter>\n  <IsTruncated>false</IsTruncated>\n  <CommonPrefixes>\n    <Prefix>oss-php-sdk-test/</Prefix>\n  </CommonPrefixes>\n  <CommonPrefixes>\n    <Prefix>test/</Prefix>\n  </CommonPrefixes>\n</ListBucketResult>";
	private $validXml2 = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ListBucketResult>\n  <Name>testbucket-hf</Name>\n  <Prefix>oss-php-sdk-test/</Prefix>\n  <Marker>xx</Marker>\n  <MaxKeys>1000</MaxKeys>\n  <Delimiter>/</Delimiter>\n  <IsTruncated>false</IsTruncated>\n  <Contents>\n    <Key>oss-php-sdk-test/upload-test-object-name.txt</Key>\n    <LastModified>2015-11-18T03:36:00.000Z</LastModified>\n    <ETag>\"89B9E567E7EB8815F2F7D41851F9A2CD\"</ETag>\n    <Type>Normal</Type>\n    <Size>13115</Size>\n    <StorageClass>Standard</StorageClass>\n    <Owner>\n      <ID>cname_user</ID>\n      <DisplayName>cname_user</DisplayName>\n    </Owner>\n  </Contents>\n</ListBucketResult>";
	private $validXmlWithEncodedKey = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ListBucketResult>\n  <Name>testbucket-hf</Name>\n  <EncodingType>url</EncodingType>\n  <Prefix>php%2Fprefix</Prefix>\n  <Marker>php%2Fmarker</Marker>\n  <NextMarker>php%2Fnext-marker</NextMarker>\n  <MaxKeys>1000</MaxKeys>\n  <Delimiter>%2F</Delimiter>\n  <IsTruncated>true</IsTruncated>\n  <Contents>\n    <Key>php/a%2Bb</Key>\n    <LastModified>2015-11-18T03:36:00.000Z</LastModified>\n    <ETag>\"89B9E567E7EB8815F2F7D41851F9A2CD\"</ETag>\n    <Type>Normal</Type>\n    <Size>13115</Size>\n    <StorageClass>Standard</StorageClass>\n    <Owner>\n      <ID>cname_user</ID>\n      <DisplayName>cname_user</DisplayName>\n    </Owner>\n  </Contents>\n</ListBucketResult>";

	public function testParseValidXml1()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml1, 200);
		$result = new \OSS\Result\ListObjectsResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$objectListInfo = $result->getData();
		$this->assertEquals(2, count($objectListInfo->getPrefixList()));
		$this->assertEquals(0, count($objectListInfo->getObjectList()));
		$this->assertEquals('testbucket-hf', $objectListInfo->getBucketName());
		$this->assertEquals('', $objectListInfo->getPrefix());
		$this->assertEquals('', $objectListInfo->getMarker());
		$this->assertEquals(1000, $objectListInfo->getMaxKeys());
		$this->assertEquals('/', $objectListInfo->getDelimiter());
		$this->assertEquals('false', $objectListInfo->getIsTruncated());
		$this->assertEquals('oss-php-sdk-test/', $objectListInfo->getPrefixList()[0]->getPrefix());
		$this->assertEquals('test/', $objectListInfo->getPrefixList()[1]->getPrefix());
	}

	public function testParseValidXml2()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml2, 200);
		$result = new \OSS\Result\ListObjectsResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$objectListInfo = $result->getData();
		$this->assertEquals(0, count($objectListInfo->getPrefixList()));
		$this->assertEquals(1, count($objectListInfo->getObjectList()));
		$this->assertEquals('testbucket-hf', $objectListInfo->getBucketName());
		$this->assertEquals('oss-php-sdk-test/', $objectListInfo->getPrefix());
		$this->assertEquals('xx', $objectListInfo->getMarker());
		$this->assertEquals(1000, $objectListInfo->getMaxKeys());
		$this->assertEquals('/', $objectListInfo->getDelimiter());
		$this->assertEquals('false', $objectListInfo->getIsTruncated());
		$this->assertEquals('oss-php-sdk-test/upload-test-object-name.txt', $objectListInfo->getObjectList()[0]->getKey());
		$this->assertEquals('2015-11-18T03:36:00.000Z', $objectListInfo->getObjectList()[0]->getLastModified());
		$this->assertEquals('"89B9E567E7EB8815F2F7D41851F9A2CD"', $objectListInfo->getObjectList()[0]->getETag());
		$this->assertEquals('Normal', $objectListInfo->getObjectList()[0]->getType());
		$this->assertEquals(13115, $objectListInfo->getObjectList()[0]->getSize());
		$this->assertEquals('Standard', $objectListInfo->getObjectList()[0]->getStorageClass());
	}

	public function testParseValidXmlWithEncodedKey()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXmlWithEncodedKey, 200);
		$result = new \OSS\Result\ListObjectsResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$objectListInfo = $result->getData();
		$this->assertEquals(0, count($objectListInfo->getPrefixList()));
		$this->assertEquals(1, count($objectListInfo->getObjectList()));
		$this->assertEquals('testbucket-hf', $objectListInfo->getBucketName());
		$this->assertEquals('php/prefix', $objectListInfo->getPrefix());
		$this->assertEquals('php/marker', $objectListInfo->getMarker());
		$this->assertEquals('php/next-marker', $objectListInfo->getNextMarker());
		$this->assertEquals(1000, $objectListInfo->getMaxKeys());
		$this->assertEquals('/', $objectListInfo->getDelimiter());
		$this->assertEquals('true', $objectListInfo->getIsTruncated());
		$this->assertEquals('php/a+b', $objectListInfo->getObjectList()[0]->getKey());
		$this->assertEquals('2015-11-18T03:36:00.000Z', $objectListInfo->getObjectList()[0]->getLastModified());
		$this->assertEquals('"89B9E567E7EB8815F2F7D41851F9A2CD"', $objectListInfo->getObjectList()[0]->getETag());
		$this->assertEquals('Normal', $objectListInfo->getObjectList()[0]->getType());
		$this->assertEquals(13115, $objectListInfo->getObjectList()[0]->getSize());
		$this->assertEquals('Standard', $objectListInfo->getObjectList()[0]->getStorageClass());
	}
}

?>
