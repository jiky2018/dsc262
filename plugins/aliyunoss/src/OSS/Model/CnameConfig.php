<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class CnameConfig implements XmlConfig
{
	const OSS_MAX_RULES = 10;

	private $cnameList = array();

	public function __construct()
	{
		$this->cnameList = array();
	}

	public function getCnames()
	{
		return $this->cnameList;
	}

	public function addCname($cname)
	{
		if (self::OSS_MAX_RULES <= count($this->cnameList)) {
			throw new \OSS\Core\OssException('num of cname in the config exceeds self::OSS_MAX_RULES: ' . strval(self::OSS_MAX_RULES));
		}

		$this->cnameList[] = array('Domain' => $cname);
	}

	public function parseFromXml($strXml)
	{
		$xml = simplexml_load_string($strXml);

		if (!isset($xml->Cname)) {
			return NULL;
		}

		foreach ($xml->Cname as $entry) {
			$cname = array();

			foreach ($entry as $key => $value) {
				$cname[strval($key)] = strval($value);
			}

			$this->cnameList[] = $cname;
		}
	}

	public function serializeToXml()
	{
		$strXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<BucketCnameConfiguration>\n</BucketCnameConfiguration>";
		$xml = new \SimpleXMLElement($strXml);

		foreach ($this->cnameList as $cname) {
			$node = $xml->addChild('Cname');

			foreach ($cname as $key => $value) {
				$node->addChild($key, $value);
			}
		}

		return $xml->asXML();
	}

	public function __toString()
	{
		return $this->serializeToXml();
	}
}

?>
