<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class ListPartsResultTest extends \PHPUnit_Framework_TestCase
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ListPartsResult xmlns=\"http://doc.oss-cn-hangzhou.aliyuncs.com\">\n    <Bucket>multipart_upload</Bucket>\n    <Key>multipart.data</Key>\n    <UploadId>0004B999EF5A239BB9138C6227D69F95</UploadId>\n    <NextPartNumberMarker>5</NextPartNumberMarker>\n    <MaxParts>1000</MaxParts>\n    <IsTruncated>false</IsTruncated>\n    <Part>\n        <PartNumber>1</PartNumber>\n        <LastModified>2012-02-23T07:01:34.000Z</LastModified>\n        <ETag>&quot;3349DC700140D7F86A078484278075A9&quot;</ETag>\n        <Size>6291456</Size>\n    </Part>\n    <Part>\n        <PartNumber>2</PartNumber>\n        <LastModified>2012-02-23T07:01:12.000Z</LastModified>\n        <ETag>&quot;3349DC700140D7F86A078484278075A9&quot;</ETag>\n        <Size>6291456</Size>\n    </Part>\n    <Part>\n        <PartNumber>5</PartNumber>\n        <LastModified>2012-02-23T07:02:03.000Z</LastModified>\n        <ETag>&quot;7265F4D211B56873A381D321F586E4A9&quot;</ETag>\n        <Size>1024</Size>\n    </Part>\n</ListPartsResult>";

	public function testParseValidXml()
	{
		$response = new \OSS\Http\ResponseCore(array(), $this->validXml, 200);
		$result = new \OSS\Result\ListPartsResult($response);
		$listPartsInfo = $result->getData();
		$this->assertEquals('multipart_upload', $listPartsInfo->getBucket());
		$this->assertEquals('multipart.data', $listPartsInfo->getKey());
		$this->assertEquals('0004B999EF5A239BB9138C6227D69F95', $listPartsInfo->getUploadId());
		$this->assertEquals(5, $listPartsInfo->getNextPartNumberMarker());
		$this->assertEquals(1000, $listPartsInfo->getMaxParts());
		$this->assertEquals('false', $listPartsInfo->getIsTruncated());
		$this->assertEquals(3, count($listPartsInfo->getListPart()));
		$this->assertEquals(1, $listPartsInfo->getListPart()[0]->getPartNumber());
		$this->assertEquals('2012-02-23T07:01:34.000Z', $listPartsInfo->getListPart()[0]->getLastModified());
		$this->assertEquals('"3349DC700140D7F86A078484278075A9"', $listPartsInfo->getListPart()[0]->getETag());
		$this->assertEquals(6291456, $listPartsInfo->getListPart()[0]->getSize());
	}
}

?>
