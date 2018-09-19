<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class RefererConfig implements XmlConfig
{
	private $allowEmptyReferer = true;
	private $refererList = array();

	public function parseFromXml($strXml)
	{
		$xml = simplexml_load_string($strXml);

		if (!isset($xml->AllowEmptyReferer)) {
			return NULL;
		}

		if (!isset($xml->RefererList)) {
			return NULL;
		}

		$this->allowEmptyReferer = (strval($xml->AllowEmptyReferer) === 'TRUE') || (strval($xml->AllowEmptyReferer) === 'true') ? true : false;

		foreach ($xml->RefererList->Referer as $key => $refer) {
			$this->refererList[] = strval($refer);
		}
	}

	public function serializeToXml()
	{
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><RefererConfiguration></RefererConfiguration>');

		if ($this->allowEmptyReferer) {
			$xml->addChild('AllowEmptyReferer', 'true');
		}
		else {
			$xml->addChild('AllowEmptyReferer', 'false');
		}

		$refererList = $xml->addChild('RefererList');

		foreach ($this->refererList as $referer) {
			$refererList->addChild('Referer', $referer);
		}

		return $xml->asXML();
	}

	public function __toString()
	{
		return $this->serializeToXml();
	}

	public function setAllowEmptyReferer($allowEmptyReferer)
	{
		$this->allowEmptyReferer = $allowEmptyReferer;
	}

	public function addReferer($referer)
	{
		$this->refererList[] = $referer;
	}

	public function isAllowEmptyReferer()
	{
		return $this->allowEmptyReferer;
	}

	public function getRefererList()
	{
		return $this->refererList;
	}
}

?>
