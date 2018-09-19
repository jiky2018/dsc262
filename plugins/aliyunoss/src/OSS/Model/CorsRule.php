<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class CorsRule
{
	private $allowedHeaders = array();
	private $allowedOrigins = array();
	private $allowedMethods = array();
	private $exposeHeaders = array();
	private $maxAgeSeconds;

	public function addAllowedOrigin($allowedOrigin)
	{
		if (!empty($allowedOrigin)) {
			$this->allowedOrigins[] = $allowedOrigin;
		}
	}

	public function addAllowedMethod($allowedMethod)
	{
		if (!empty($allowedMethod)) {
			$this->allowedMethods[] = $allowedMethod;
		}
	}

	public function addAllowedHeader($allowedHeader)
	{
		if (!empty($allowedHeader)) {
			$this->allowedHeaders[] = $allowedHeader;
		}
	}

	public function addExposeHeader($exposeHeader)
	{
		if (!empty($exposeHeader)) {
			$this->exposeHeaders[] = $exposeHeader;
		}
	}

	public function getMaxAgeSeconds()
	{
		return $this->maxAgeSeconds;
	}

	public function setMaxAgeSeconds($maxAgeSeconds)
	{
		$this->maxAgeSeconds = $maxAgeSeconds;
	}

	public function getAllowedHeaders()
	{
		return $this->allowedHeaders;
	}

	public function getAllowedOrigins()
	{
		return $this->allowedOrigins;
	}

	public function getAllowedMethods()
	{
		return $this->allowedMethods;
	}

	public function getExposeHeaders()
	{
		return $this->exposeHeaders;
	}

	public function appendToXml(&$xmlRule)
	{
		if (!isset($this->maxAgeSeconds)) {
			throw new \OSS\Core\OssException('maxAgeSeconds is not set in the Rule');
		}

		foreach ($this->allowedOrigins as $allowedOrigin) {
			$xmlRule->addChild(CorsConfig::OSS_CORS_ALLOWED_ORIGIN, $allowedOrigin);
		}

		foreach ($this->allowedMethods as $allowedMethod) {
			$xmlRule->addChild(CorsConfig::OSS_CORS_ALLOWED_METHOD, $allowedMethod);
		}

		foreach ($this->allowedHeaders as $allowedHeader) {
			$xmlRule->addChild(CorsConfig::OSS_CORS_ALLOWED_HEADER, $allowedHeader);
		}

		foreach ($this->exposeHeaders as $exposeHeader) {
			$xmlRule->addChild(CorsConfig::OSS_CORS_EXPOSE_HEADER, $exposeHeader);
		}

		$xmlRule->addChild(CorsConfig::OSS_CORS_MAX_AGE_SECONDS, strval($this->maxAgeSeconds));
	}
}


?>
