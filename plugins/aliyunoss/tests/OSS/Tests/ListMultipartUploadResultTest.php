<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class ListMultipartUploadResultTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ListMultipartUploadsResult xmlns=\"http://doc.oss-cn-hangzhou.aliyuncs.com\">\n    <Bucket>oss-example</Bucket>\n    <KeyMarker>xx</KeyMarker>\n    <UploadIdMarker>3</UploadIdMarker>\n    <NextKeyMarker>oss.avi</NextKeyMarker>\n    <NextUploadIdMarker>0004B99B8E707874FC2D692FA5D77D3F</NextUploadIdMarker>\n    <Delimiter>x</Delimiter>\n    <Prefix>xx</Prefix>\n    <MaxUploads>1000</MaxUploads>\n    <IsTruncated>false</IsTruncated>\n    <Upload>\n        <Key>multipart.data</Key>\n        <UploadId>0004B999EF518A1FE585B0C9360DC4C8</UploadId>\n        <Initiated>2012-02-23T04:18:23.000Z</Initiated>\n    </Upload>\n    <Upload>\n        <Key>multipart.data</Key>\n        <UploadId>0004B999EF5A239BB9138C6227D69F95</UploadId>\n        <Initiated>2012-02-23T04:18:23.000Z</Initiated>\n    </Upload>\n    <Upload>\n        <Key>oss.avi</Key>\n        <UploadId>0004B99B8E707874FC2D692FA5D77D3F</UploadId>\n        <Initiated>2012-02-23T06:14:27.000Z</Initiated>\n    </Upload>\n</ListMultipartUploadsResult>";
	private $validXmlWithEncodedKey = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ListMultipartUploadsResult xmlns=\"http://doc.oss-cn-hangzhou.aliyuncs.com\">\n    <Bucket>oss-example</Bucket>\n    <EncodingType>url</EncodingType>\n    <KeyMarker>php%2Bkey-marker</KeyMarker>\n    <UploadIdMarker>3</UploadIdMarker>\n    <NextKeyMarker>php%2Bnext-key-marker</NextKeyMarker>\n    <NextUploadIdMarker>0004B99B8E707874FC2D692FA5D77D3F</NextUploadIdMarker>\n    <Delimiter>%2F</Delimiter>\n    <Prefix>php%2Bprefix</Prefix>\n    <MaxUploads>1000</MaxUploads>\n    <IsTruncated>true</IsTruncated>\n    <Upload>\n        <Key>php%2Bkey-1</Key>\n        <UploadId>0004B999EF518A1FE585B0C9360DC4C8</UploadId>\n        <Initiated>2012-02-23T04:18:23.000Z</Initiated>\n    </Upload>\n    <Upload>\n        <Key>php%2Bkey-2</Key>\n        <UploadId>0004B999EF5A239BB9138C6227D69F95</UploadId>\n        <Initiated>2012-02-23T04:18:23.000Z</Initiated>\n    </Upload>\n    <Upload>\n        <Key>php%2Bkey-3</Key>\n        <UploadId>0004B99B8E707874FC2D692FA5D77D3F</UploadId>\n        <Initiated>2012-02-23T06:14:27.000Z</Initiated>\n    </Upload>\n</ListMultipartUploadsResult>";

	public function testParseValidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 200);
		$result = new \OSS\Result\ListMultipartUploadResult($response);
		$listMultipartUploadInfo = $result->getData();
		$this->assertEquals('oss-example', $listMultipartUploadInfo->getBucket());
		$this->assertEquals('xx', $listMultipartUploadInfo->getKeyMarker());
		$this->assertEquals(3, $listMultipartUploadInfo->getUploadIdMarker());
		$this->assertEquals('oss.avi', $listMultipartUploadInfo->getNextKeyMarker());
		$this->assertEquals('0004B99B8E707874FC2D692FA5D77D3F', $listMultipartUploadInfo->getNextUploadIdMarker());
		$this->assertEquals('x', $listMultipartUploadInfo->getDelimiter());
		$this->assertEquals('xx', $listMultipartUploadInfo->getPrefix());
		$this->assertEquals(1000, $listMultipartUploadInfo->getMaxUploads());
		$this->assertEquals('false', $listMultipartUploadInfo->getIsTruncated());
		$this->assertEquals('multipart.data', $listMultipartUploadInfo->getUploads()[0]->getKey());
		$this->assertEquals('0004B999EF518A1FE585B0C9360DC4C8', $listMultipartUploadInfo->getUploads()[0]->getUploadId());
		$this->assertEquals('2012-02-23T04:18:23.000Z', $listMultipartUploadInfo->getUploads()[0]->getInitiated());
	}

	public function testParseValidXmlWithEncodedKey()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXmlWithEncodedKey, 200);
		$result = new \OSS\Result\ListMultipartUploadResult($response);
		$listMultipartUploadInfo = $result->getData();
		$this->assertEquals('oss-example', $listMultipartUploadInfo->getBucket());
		$this->assertEquals('php+key-marker', $listMultipartUploadInfo->getKeyMarker());
		$this->assertEquals('php+next-key-marker', $listMultipartUploadInfo->getNextKeyMarker());
		$this->assertEquals(3, $listMultipartUploadInfo->getUploadIdMarker());
		$this->assertEquals('0004B99B8E707874FC2D692FA5D77D3F', $listMultipartUploadInfo->getNextUploadIdMarker());
		$this->assertEquals('/', $listMultipartUploadInfo->getDelimiter());
		$this->assertEquals('php+prefix', $listMultipartUploadInfo->getPrefix());
		$this->assertEquals(1000, $listMultipartUploadInfo->getMaxUploads());
		$this->assertEquals('true', $listMultipartUploadInfo->getIsTruncated());
		$this->assertEquals('php+key-1', $listMultipartUploadInfo->getUploads()[0]->getKey());
		$this->assertEquals('0004B999EF518A1FE585B0C9360DC4C8', $listMultipartUploadInfo->getUploads()[0]->getUploadId());
		$this->assertEquals('2012-02-23T04:18:23.000Z', $listMultipartUploadInfo->getUploads()[0]->getInitiated());
	}
}

?>
