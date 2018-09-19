<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services\Wxpay;

class WxResponse
{
	public $key;
	public $parameters;
	public $debugInfo;

	public function __construct()
	{
		$this->gateUrl = 'https://wpay.tenpay.com/wx_pub/v1.0/wx_app_api.cgi';
		$this->key = '';
		$this->parameters = array();
		$this->debugInfo = '';
	}

	public function getKey()
	{
		return $this->key;
	}

	public function setKey($key)
	{
		$this->key = $key;
	}

	public function getParameter($parameter)
	{
		return $this->parameters[$parameter];
	}

	public function setParameter($parameter, $parameterValue)
	{
		$this->parameters[$parameter] = $parameterValue;
	}

	public function clearParameter()
	{
		return $this->$parameters->RemoveAll;
	}

	public function getAllParameters()
	{
		return $this->parameters;
	}

	public function xmlToArray($xml)
	{
		$xmlObj = simplexml_load_string($xml, 'SimpleXMLIterator', LIBXML_NOCDATA);
		$arr = array();
		$xmlObj->rewind();

		while (1) {
			if (!is_object($xmlObj->current())) {
				break;
			}

			$arr[$xmlObj->key()] = $xmlObj->current()->__toString();
			$xmlObj->next();
		}

		return $arr;
	}

	public function isTenpaySign()
	{
		$signPars = '';
		ksort($this->parameters);

		foreach ($this->parameters as $k => $v) {
			if (('sign' != $k) && ('' != $v)) {
				$signPars .= $k . '=' . $v . '&';
			}
		}

		$signPars .= 'key=' . $this->getKey();
		$sign = strtolower(md5($signPars));
		$tenpaySign = strtolower($this->getParameter('sign'));
		return $sign == $tenpaySign;
	}

	public function getDebugInfo()
	{
		return $this->debugInfo;
	}

	public function setDebugInfo($debug)
	{
		$this->debugInfo = $debug;
	}

	public function getSucessXml()
	{
		$xml = '<xml>';
		$xml .= '<return_code><![CDATA[SUCCESS]]></return_code>';
		$xml .= '<return_msg><![CDATA[OK]]></return_msg>';
		$xml .= '</xml>';
		return $xml;
	}

	public function getFailXml()
	{
		$xml = '<xml>';
		$xml .= '<return_code><![CDATA[FAIL]]></return_code>';
		$xml .= '<return_msg><![CDATA[OK]]></return_msg>';
		$xml .= '</xml>';
		return $xml;
	}
}


?>
