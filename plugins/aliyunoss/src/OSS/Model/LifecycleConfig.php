<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class LifecycleConfig implements XmlConfig
{
	/**
     * @var LifecycleRule[]
     */
	private $rules;

	public function parseFromXml($strXml)
	{
		$this->rules = array();
		$xml = simplexml_load_string($strXml);

		if (!isset($xml->Rule)) {
			return NULL;
		}

		$this->rules = array();

		foreach ($xml->Rule as $rule) {
			$id = strval($rule->ID);
			$prefix = strval($rule->Prefix);
			$status = strval($rule->Status);
			$actions = array();

			foreach ($rule as $key => $value) {
				if (($key === 'ID') || ($key === 'Prefix') || ($key === 'Status')) {
					continue;
				}

				$action = $key;
				$timeSpec = null;
				$timeValue = null;

				foreach ($value as $timeSpecKey => $timeValueValue) {
					$timeSpec = $timeSpecKey;
					$timeValue = strval($timeValueValue);
				}

				$actions[] = new LifecycleAction($action, $timeSpec, $timeValue);
			}

			$this->rules[] = new LifecycleRule($id, $prefix, $status, $actions);
		}

		return NULL;
	}

	public function serializeToXml()
	{
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><LifecycleConfiguration></LifecycleConfiguration>');

		foreach ($this->rules as $rule) {
			$xmlRule = $xml->addChild('Rule');
			$rule->appendToXml($xmlRule);
		}

		return $xml->asXML();
	}

	public function addRule($lifecycleRule)
	{
		if (!isset($lifecycleRule)) {
			throw new \OSS\Core\OssException('lifecycleRule is null');
		}

		$this->rules[] = $lifecycleRule;
	}

	public function __toString()
	{
		return $this->serializeToXml();
	}

	public function getRules()
	{
		return $this->rules;
	}
}

?>
