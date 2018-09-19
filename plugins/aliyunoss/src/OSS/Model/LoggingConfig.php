<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class LoggingConfig implements XmlConfig
{
	private $targetBucket = '';
	private $targetPrefix = '';

	public function __construct($targetBucket = NULL, $targetPrefix = NULL)
	{
		$this->targetBucket = $targetBucket;
		$this->targetPrefix = $targetPrefix;
	}

	public function parseFromXml($strXml)
	{
		$xml = simplexml_load_string($strXml);

		if (!isset($xml->LoggingEnabled)) {
			return NULL;
		}

		foreach ($xml->LoggingEnabled as $status) {
			foreach ($status as $key => $value) {
				if ($key === 'TargetBucket') {
					$this->targetBucket = strval($value);
				}
				else if ($key === 'TargetPrefix') {
					$this->targetPrefix = strval($value);
				}
			}

			break;
		}
	}

	public function serializeToXml()
	{
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><BucketLoggingStatus></BucketLoggingStatus>');
		if (isset($this->targetBucket) && isset($this->targetPrefix)) {
			$loggingEnabled = $xml->addChild('LoggingEnabled');
			$loggingEnabled->addChild('TargetBucket', $this->targetBucket);
			$loggingEnabled->addChild('TargetPrefix', $this->targetPrefix);
		}

		return $xml->asXML();
	}

	public function __toString()
	{
		return $this->serializeToXml();
	}

	public function getTargetBucket()
	{
		return $this->targetBucket;
	}

	public function getTargetPrefix()
	{
		return $this->targetPrefix;
	}
}

?>
