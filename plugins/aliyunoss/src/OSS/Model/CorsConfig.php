<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class CorsConfig implements XmlConfig
{
	const OSS_CORS_ALLOWED_ORIGIN = 'AllowedOrigin';
	const OSS_CORS_ALLOWED_METHOD = 'AllowedMethod';
	const OSS_CORS_ALLOWED_HEADER = 'AllowedHeader';
	const OSS_CORS_EXPOSE_HEADER = 'ExposeHeader';
	const OSS_CORS_MAX_AGE_SECONDS = 'MaxAgeSeconds';
	const OSS_MAX_RULES = 10;

	/**
     * orsRule列表
     *
     * @var CorsRule[]
     */
	private $rules = array();

	public function __construct()
	{
		$this->rules = array();
	}

	public function getRules()
	{
		return $this->rules;
	}

	public function addRule($rule)
	{
		if (self::OSS_MAX_RULES <= count($this->rules)) {
			throw new \OSS\Core\OssException('num of rules in the config exceeds self::OSS_MAX_RULES: ' . strval(self::OSS_MAX_RULES));
		}

		$this->rules[] = $rule;
	}

	public function parseFromXml($strXml)
	{
		$xml = simplexml_load_string($strXml);

		if (!isset($xml->CORSRule)) {
			return NULL;
		}

		foreach ($xml->CORSRule as $rule) {
			$corsRule = new CorsRule();

			foreach ($rule as $key => $value) {
				if ($key === self::OSS_CORS_ALLOWED_HEADER) {
					$corsRule->addAllowedHeader(strval($value));
				}
				else if ($key === self::OSS_CORS_ALLOWED_METHOD) {
					$corsRule->addAllowedMethod(strval($value));
				}
				else if ($key === self::OSS_CORS_ALLOWED_ORIGIN) {
					$corsRule->addAllowedOrigin(strval($value));
				}
				else if ($key === self::OSS_CORS_EXPOSE_HEADER) {
					$corsRule->addExposeHeader(strval($value));
				}
				else if ($key === self::OSS_CORS_MAX_AGE_SECONDS) {
					$corsRule->setMaxAgeSeconds(strval($value));
				}
			}

			$this->addRule($corsRule);
		}

		return NULL;
	}

	public function serializeToXml()
	{
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><CORSConfiguration></CORSConfiguration>');

		foreach ($this->rules as $rule) {
			$xmlRule = $xml->addChild('CORSRule');
			$rule->appendToXml($xmlRule);
		}

		return $xml->asXML();
	}

	public function __toString()
	{
		return $this->serializeToXml();
	}
}

?>
