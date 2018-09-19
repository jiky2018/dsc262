<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Tests;

class OssUtilTest extends \PHPUnit_Framework_TestCase
{
	public function testIsChinese()
	{
		$this->assertEquals(\OSS\Core\OssUtil::chkChinese('hello,world'), 0);
		$str = '你好,这里是卖咖啡!';
		$strGBK = \OSS\Core\OssUtil::encodePath($str);
		$this->assertEquals(\OSS\Core\OssUtil::chkChinese($str), 1);
		$this->assertEquals(\OSS\Core\OssUtil::chkChinese($strGBK), 1);
	}

	public function testIsGB2312()
	{
		$str = '你好,这里是卖咖啡!';
		$this->assertFalse(\OSS\Core\OssUtil::isGb2312($str));
	}

	public function testCheckChar()
	{
		$str = '你好,这里是卖咖啡!';
		$this->assertFalse(\OSS\Core\OssUtil::checkChar($str));
		$this->assertTrue(\OSS\Core\OssUtil::checkChar(iconv('UTF-8', 'GB2312//IGNORE', $str)));
	}

	public function testIsIpFormat()
	{
		$this->assertTrue(\OSS\Core\OssUtil::isIPFormat('10.101.160.147'));
		$this->assertTrue(\OSS\Core\OssUtil::isIPFormat('12.12.12.34'));
		$this->assertTrue(\OSS\Core\OssUtil::isIPFormat('12.12.12.12'));
		$this->assertTrue(\OSS\Core\OssUtil::isIPFormat('255.255.255.255'));
		$this->assertTrue(\OSS\Core\OssUtil::isIPFormat('0.1.1.1'));
		$this->assertFalse(\OSS\Core\OssUtil::isIPFormat('0.1.1.x'));
		$this->assertFalse(\OSS\Core\OssUtil::isIPFormat('0.1.1.256'));
		$this->assertFalse(\OSS\Core\OssUtil::isIPFormat('256.1.1.1'));
		$this->assertFalse(\OSS\Core\OssUtil::isIPFormat('0.1.1.0.1'));
		$this->assertTrue(\OSS\Core\OssUtil::isIPFormat('10.10.10.10:123'));
	}

	public function testToQueryString()
	{
		$option = array('a' => 'b');
		$this->assertEquals('a=b', \OSS\Core\OssUtil::toQueryString($option));
	}

	public function testSReplace()
	{
		$str = '<>&\'"';
		$this->assertEquals('&amp;lt;&amp;gt;&amp;&apos;&quot;', \OSS\Core\OssUtil::sReplace($str));
	}

	public function testCheckChinese()
	{
		$str = '你好,这里是卖咖啡!';
		$this->assertEquals(\OSS\Core\OssUtil::chkChinese($str), 1);

		if (\OSS\Core\OssUtil::isWin()) {
			$strGB = \OSS\Core\OssUtil::encodePath($str);
			$this->assertEquals($str, iconv('GB2312', 'UTF-8', $strGB));
		}
	}

	public function testValidateOption()
	{
		$option = 'string';

		try {
			\OSS\Core\OssUtil::validateOptions($option);
			$this->assertFalse(true);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('string:option must be array', $e->getMessage());
		}

		$option = null;

		try {
			\OSS\Core\OssUtil::validateOptions($option);
			$this->assertTrue(true);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertFalse(true);
		}
	}

	public function testCreateDeleteObjectsXmlBody()
	{
		$xml = '<?xml version="1.0" encoding="utf-8"?><Delete><Quiet>true</Quiet><Object><Key>obj1</Key></Object></Delete>';
		$a = array('obj1');
		$this->assertEquals($xml, $this->cleanXml(\OSS\Core\OssUtil::createDeleteObjectsXmlBody($a, 'true')));
	}

	public function testCreateCompleteMultipartUploadXmlBody()
	{
		$xml = '<?xml version="1.0" encoding="utf-8"?><CompleteMultipartUpload><Part><PartNumber>2</PartNumber><ETag>xx</ETag></Part></CompleteMultipartUpload>';
		$a = array(
			array('PartNumber' => 2, 'ETag' => 'xx')
			);
		$this->assertEquals($this->cleanXml(\OSS\Core\OssUtil::createCompleteMultipartUploadXmlBody($a)), $xml);
	}

	public function testValidateBucket()
	{
		$this->assertTrue(\OSS\Core\OssUtil::validateBucket('xxx'));
		$this->assertFalse(\OSS\Core\OssUtil::validateBucket('XXXqwe123'));
		$this->assertFalse(\OSS\Core\OssUtil::validateBucket('XX'));
		$this->assertFalse(\OSS\Core\OssUtil::validateBucket('/X'));
		$this->assertFalse(\OSS\Core\OssUtil::validateBucket(''));
	}

	public function testValidateObject()
	{
		$this->assertTrue(\OSS\Core\OssUtil::validateObject('xxx'));
		$this->assertTrue(\OSS\Core\OssUtil::validateObject('xxx23'));
		$this->assertTrue(\OSS\Core\OssUtil::validateObject('12321-xxx'));
		$this->assertTrue(\OSS\Core\OssUtil::validateObject('x'));
		$this->assertFalse(\OSS\Core\OssUtil::validateObject('/aa'));
		$this->assertFalse(\OSS\Core\OssUtil::validateObject('\\aa'));
		$this->assertFalse(\OSS\Core\OssUtil::validateObject(''));
	}

	public function testStartWith()
	{
		$this->assertTrue(\OSS\Core\OssUtil::startsWith('xxab', 'xx'), true);
	}

	public function testReadDir()
	{
		$list = \OSS\Core\OssUtil::readDir('./src', '.|..|.svn|.git', true);
		$this->assertNotNull($list);
	}

	public function testIsWin()
	{
	}

	public function testGetMd5SumForFile()
	{
		$this->assertEquals(\OSS\Core\OssUtil::getMd5SumForFile(__FILE__, 0, filesize(__FILE__) - 1), base64_encode(md5(file_get_contents(__FILE__), true)));
	}

	public function testGenerateFile()
	{
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'generatedFile.txt';
		\OSS\Core\OssUtil::generateFile($path, 1024 * 1024);
		$this->assertEquals(filesize($path), 1024 * 1024);
		unlink($path);
	}

	public function testThrowOssExceptionWithMessageIfEmpty()
	{
		$null = null;

		try {
			\OSS\Core\OssUtil::throwOssExceptionWithMessageIfEmpty($null, 'xx');
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('xx', $e->getMessage());
		}
	}

	public function testThrowOssExceptionWithMessageIfEmpty2()
	{
		$null = '';

		try {
			\OSS\Core\OssUtil::throwOssExceptionWithMessageIfEmpty($null, 'xx');
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('xx', $e->getMessage());
		}
	}

	public function testValidContent()
	{
		$null = '';

		try {
			\OSS\Core\OssUtil::validateContent($null);
			$this->assertTrue(false);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('http body content is invalid', $e->getMessage());
		}

		$notnull = 'x';

		try {
			\OSS\Core\OssUtil::validateContent($notnull);
			$this->assertTrue(true);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertEquals('http body content is invalid', $e->getMessage());
		}
	}

	public function testThrowOssExceptionWithMessageIfEmpty3()
	{
		$null = 'xx';

		try {
			\OSS\Core\OssUtil::throwOssExceptionWithMessageIfEmpty($null, 'xx');
			$this->assertTrue(True);
		}
		catch (\OSS\Core\OssException $e) {
			$this->assertTrue(false);
		}
	}

	private function cleanXml($xml)
	{
		return str_replace("\n", '', str_replace("\r", '', $xml));
	}
}

?>
