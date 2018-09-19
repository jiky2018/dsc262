<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services\Wxpay;

class WxPay
{
	/** Token获取网关地址*/
	public $tokenUrl;
	public $gateUrl;
	public $unifiedorderUrl;
	/** 商户参数 */
	public $app_id;
	public $partner_key;
	public $app_secret;
	public $app_key;
	/**  Token */
	public $Token;
	/** debug信息 */
	public $debugInfo;

	public function __construct()
	{
		$this->tokenUrl = 'https://api.weixin.qq.com/cgi-bin/token';
		$this->gateUrl = 'https://api.weixin.qq.com/pay/genprepay';
		$this->notifyUrl = 'https://gw.tenpay.com/gateway/simpleverifynotifyid.xml';
		$this->unifiedorderUrl = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
	}

	public function init($appid, $appsecret, $partnerkey)
	{
		$this->debugInfo = '';
		$this->Token = '';
		$this->app_id = $appid;
		$this->partner_key = $partnerkey;
		$this->app_secret = $appsecret;
	}

	public function getDebugInfo()
	{
		$res = $this->debugInfo;
		$this->debugInfo = '';
		return $res;
	}

	public function httpSend($url, $method, $data)
	{
		$client = new TenpayHttpClient();
		$client->setReqContent($url);
		$client->setMethod($method);
		$client->setReqBody($data);
		$res = '';

		if ($client->call()) {
			$res = $client->getResContent();
		}

		$this->_setDebugInfo('Req Url:' . $url);
		$this->_setDebugInfo('Req data:' . $data);
		$this->_setDebugInfo('Res Content:' . $res);
		return $res;
	}

	public function GetToken()
	{
		if (Cache::has('weixin_access_token')) {
			if ($this->Token = Cache::get('weixin_access_token')) {
				return $this->Token;
			}
		}

		$url = $this->tokenUrl . '?grant_type=client_credential&appid=' . $this->app_id . '&secret=' . $this->app_secret;
		$json = $this->httpSend($url, 'GET', '');

		if ($json != '') {
			$tk = json_decode($json);

			if ($tk->access_token != '') {
				$this->Token = $tk->access_token;
				$expires_in = $tk->expires_in;
				Cache::put('weixin_access_token', $this->Token, $expires_in);
			}
			else {
				$this->Token = '';
			}
		}

		$this->_setDebugInfo('tokenUrl:' . $url);
		$this->_setDebugInfo('tokenRes jsonContent:' . $json);
		return $this->Token;
	}

	public function createMd5Sign($signParams)
	{
		$signPars = '';
		ksort($signParams);

		foreach ($signParams as $k => $v) {
			if (($v != '') && ('sign' != $k)) {
				$signPars .= $k . '=' . $v . '&';
			}
		}

		$signPars .= 'key=' . $this->partner_key;
		$sign = strtoupper(md5($signPars));
		$this->_setDebugInfo('md5签名:' . $signPars . ' => sign:' . $sign);
		return $sign;
	}

	public function genPackage($packageParams)
	{
		$sign = $this->createMd5Sign($packageParams);
		$reqPars = '';

		foreach ($packageParams as $k => $v) {
			$reqPars .= $k . '=' . URLencode($v) . '&';
		}

		$reqPars = $reqPars . 'sign=' . $sign;
		return $reqPars;
	}

	public function createSHA1Sign($packageParams)
	{
		$signPars = '';
		ksort($packageParams);

		foreach ($packageParams as $k => $v) {
			if ($signPars == '') {
				$signPars = $signPars . $k . '=' . $v;
			}
			else {
				$signPars = $signPars . '&' . $k . '=' . $v;
			}
		}

		$sign = SHA1($signPars);
		$this->_setDebugInfo('sha1:' . $signPars . '=>' . $sign);
		return $sign;
	}

	public function sendPrepay($packageParams)
	{
		$prepayid = null;
		$smlStr = $this->arrayToXml($packageParams);
		$url = $this->unifiedorderUrl;
		$res = $this->httpSend($url, 'POST', $smlStr);
		$res = $this->xmlToArray($res);
		if (($res['return_code'] == 'SUCCESS') && ($res['result_code'] == 'SUCCESS') && $this->verifySignResponse($res)) {
			return $res['prepay_id'];
		}

		if ($res['return_code'] == 'FAIL') {
			throw new \Exception('提交预支付交易单失败:' . $res['return_msg']);
		}

		throw new \Exception('提交预支付交易单失败，' . $res['err_code'] . ':' . $res['err_code']);
	}

	protected function arrayToXml($params)
	{
		$xml = '<xml>';

		foreach ($params as $key => $value) {
			$xml .= '<' . $key . '>';
			$xml .= '<![CDATA[' . $value . ']]>';
			$xml .= '</' . $key . '>';
		}

		$xml .= '</xml>';
		return $xml;
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

	protected function xmlToArray($xml)
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

	protected function verifySignResponse($arr)
	{
		$tmpArr = $arr;
		unset($tmpArr['sign']);
		ksort($tmpArr);
		$str = '';

		foreach ($tmpArr as $key => $value) {
			if ($value) {
				$str .= $key . '=' . $value . '&';
			}
		}

		$str .= 'key=' . $this->partner_key;

		if ($arr['sign'] == $this->signMd5($str)) {
			return true;
		}

		return false;
	}

	protected function signMd5($str)
	{
		$sign = md5($str);
		return strtoupper($sign);
	}

	public function _setDebugInfo($debugInfo)
	{
		$this->debugInfo = PHP_EOL . $this->debugInfo . $debugInfo . PHP_EOL;
	}
}


?>
