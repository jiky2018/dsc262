<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class WebsiteConfig implements XmlConfig
{
	private $indexDocument = '';
	private $errorDocument = '';

	public function __construct($indexDocument = '', $errorDocument = '')
	{
		$this->indexDocument = $indexDocument;
		$this->errorDocument = $errorDocument;
	}

	public function parseFromXml($strXml)
	{
		$xml = simplexml_load_string($strXml);
		if (isset($xml->IndexDocument) && isset($xml->IndexDocument->Suffix)) {
			$this->indexDocument = strval($xml->IndexDocument->Suffix);
		}

		if (isset($xml->ErrorDocument) && isset($xml->ErrorDocument->Key)) {
			$this->errorDocument = strval($xml->ErrorDocument->Key);
		}
	}

	public function serializeToXml()
	{
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><WebsiteConfiguration></WebsiteConfiguration>');
		$index_document_part = $xml->addChild('IndexDocument');
		$error_document_part = $xml->addChild('ErrorDocument');
		$index_document_part->addChild('Suffix', $this->indexDocument);
		$error_document_part->addChild('Key', $this->errorDocument);
		return $xml->asXML();
	}

	public function getIndexDocument()
	{
		return $this->indexDocument;
	}

	public function getErrorDocument()
	{
		return $this->errorDocument;
	}
}

?>
